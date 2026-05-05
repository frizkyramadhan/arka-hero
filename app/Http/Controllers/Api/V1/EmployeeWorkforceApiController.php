<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveRequestSummaryResource;
use App\Http\Resources\OvertimeRequestSummaryResource;
use App\Http\Resources\WorkforceAdministrationResource;
use App\Http\Resources\WorkforceEmployeeResource;
use App\Http\Resources\WorkforceOfficialtravelResource;
use App\Models\Administration;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Officialtravel;
use App\Models\OvertimeRequest;
use App\Models\OvertimeRequestDetail;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EmployeeWorkforceApiController extends Controller
{
    /** Status cuti yang diekspos di API workforce (termasuk yang dibatalkan / cancelled). */
    private const WORKFORCE_LEAVE_STATUSES = ['approved', 'auto_approved', 'closed', 'cancelled'];

    /**
     * Profil karyawan + administrasi yang eligible untuk konteks workforce (filter NIK/terminasi).
     *
     * Query: year (opsional), month (opsional 1–12; hanya bersama year).
     * Tanpa year/month: hanya administrasi **aktif** (`is_active = 1`).
     * Dengan year/month: administrasi aktif, atau tidak aktif bila **awal rentang ≤ termination_date** (tanggal terminasi terisi).
     */
    public function showFull(Request $request, Employee $employee): JsonResponse
    {
        $period = $this->optionalPeriodFromYearMonthQuery($request);
        $periodFromForScope = $period !== null
            ? Carbon::parse($period['from'])->startOfDay()
            : null;

        $this->applyWorkforceAdministrationScope($employee, $periodFromForScope);

        $payload = [
            'success' => true,
            'data' => [
                'employee' => new WorkforceEmployeeResource($employee),
                'administrations' => WorkforceAdministrationResource::collection($employee->administrations),
            ],
        ];

        if ($period !== null) {
            $payload['period'] = $period;
        }

        return response()->json($payload);
    }

    /**
     * Sama seperti showFull, dicari lewat NIK administrasi (mis. 13100).
     *
     * Tanpa query year/month: hanya NIK **aktif**.
     * Dengan year/month: NIK tidak aktif tetap diizinkan jika **tanggal mulai rentang ≤ termination_date**.
     */
    public function showFullByNik(Request $request, string $nik): JsonResponse
    {
        $periodMeta = $this->optionalPeriodFromYearMonthQuery($request);
        $periodFromForNik = $periodMeta !== null
            ? Carbon::parse($periodMeta['from'])->startOfDay()
            : null;

        $employee = $this->employeeFromNik($nik, $periodFromForNik);
        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => $this->workforceNikUnavailableMessage(),
            ], 404);
        }

        return $this->showFull($request, $employee);
    }

    /**
     * Aktivitas gabungan untuk **semua karyawan** yang memiliki minimal satu dokumen workforce (cuti, LOT,
     * atau lembur selesai) dalam periode tersebut.
     *
     * Query seperti aktivitas tunggal: `year` wajib jika **`period`** tidak digunakan; **`month`** opsional;
     * atau **`period=today`** / **`period=yesterday`** (satu hari kalender aplikasi — menggantikan `year`).
     */
    public function activityTimelineAll(Request $request): JsonResponse
    {
        [$from, $to, $periodPayload] = $this->resolveActivityPeriodBoundaries($request);

        $employeeIds = $this->employeeIdsWithAnyWorkforceDocumentInPeriod($from, $to);

        if ($employeeIds->isEmpty()) {
            return response()->json([
                'success' => true,
                'period' => $periodPayload,
                'count' => 0,
                'aggregate_summary' => [
                    'employees_with_activity' => 0,
                    'leave_requests_total' => 0,
                    'official_travels_total' => 0,
                    'overtime_requests_total' => 0,
                ],
                'data' => [],
            ]);
        }

        $employees = Employee::query()->whereIn('id', $employeeIds->all())->orderBy('fullname')->get();

        $data = [];
        $leaveTotal = 0;
        $lotTotal = 0;
        $otTotal = 0;

        foreach ($employees as $employee) {
            $body = $this->activityPayloadBody($employee, $from, $to);
            $leaveTotal += $body['summary']['leave_requests_count'];
            $lotTotal += $body['summary']['official_travels_count'];
            $otTotal += $body['summary']['overtime_requests_count'];
            $data[] = [
                'employee_id' => (string) $employee->getKey(),
            ] + $body;
        }

        return response()->json([
            'success' => true,
            'period' => $periodPayload,
            'count' => count($data),
            'aggregate_summary' => [
                'employees_with_activity' => count($data),
                'leave_requests_total' => $leaveTotal,
                'official_travels_total' => $lotTotal,
                'overtime_requests_total' => $otTotal,
            ],
            'data' => $data,
        ]);
    }

    /**
     * Semua cuti workforce dalam rentang (semua karyawan). Filter & sumbu tanggal sama seperti
     * `.../employees/{employee}/leave-requests` (`approved_at`, status workforce).
     */
    public function leaveRequestsAll(Request $request): JsonResponse
    {
        [$from, $to, $period] = $this->parseWorkforceDateRange($request);

        $items = LeaveRequest::query()
            ->whereIn('status', self::WORKFORCE_LEAVE_STATUSES)
            ->whereNotNull('approved_at')
            ->where('approved_at', '>=', $from)
            ->where('approved_at', '<=', $to)
            ->with([
                'leaveType',
                'administration',
                'employee',
                'cancellations' => fn ($q) => $q->orderByDesc('requested_at'),
            ])
            ->orderByDesc('approved_at')
            ->get();

        return response()->json([
            'success' => true,
            'period' => $period,
            'range' => ['from' => $period['from'], 'to' => $period['to']],
            'count' => $items->count(),
            'data' => LeaveRequestSummaryResource::collection($items),
        ]);
    }

    /**
     * Semua LOT workforce dalam rentang (`approved_at`, status approved/closed, traveler terpasang).
     */
    public function officialTravelsAll(Request $request): JsonResponse
    {
        [$from, $to, $period] = $this->parseWorkforceDateRange($request);

        $items = Officialtravel::query()
            ->whereIn('status', [Officialtravel::STATUS_APPROVED, Officialtravel::STATUS_CLOSED])
            ->whereHas('traveler', fn ($q) => $q->whereNotNull('employee_id'))
            ->whereNotNull('approved_at')
            ->where('approved_at', '>=', $from)
            ->where('approved_at', '<=', $to)
            ->with([
                'traveler.employee',
                'traveler.position.department',
                'traveler.project',
                'project',
                'transportation',
                'accommodation',
                'details.follower.employee',
                'stops.arrivalChecker',
                'stops.departureChecker',
                'creator',
                'approval_plans' => fn ($q) => $q->orderBy('id'),
                'approval_plans.approver',
            ])
            ->orderByDesc('approved_at')
            ->get();

        return response()->json([
            'success' => true,
            'period' => $period,
            'range' => ['from' => $period['from'], 'to' => $period['to']],
            'count' => $items->count(),
            'data' => WorkforceOfficialtravelResource::collection($items),
        ]);
    }

    /**
     * Semua permintaan lembur selesai dalam rentang (`finished_at`), minimal satu baris detail ber-administrasi.
     */
    public function overtimeRequestsAll(Request $request): JsonResponse
    {
        [$from, $to, $period] = $this->parseWorkforceDateRange($request);

        $items = OvertimeRequest::query()
            ->where('status', OvertimeRequest::STATUS_FINISHED)
            ->whereNotNull('finished_at')
            ->where('finished_at', '>=', $from)
            ->where('finished_at', '<=', $to)
            ->whereHas('details', fn ($q) => $q->whereNotNull('administration_id'))
            ->with(['project', 'details' => fn ($q) => $q->orderBy('sort_order'), 'details.administration.employee'])
            ->orderByDesc('finished_at')
            ->get();

        return response()->json([
            'success' => true,
            'period' => $period,
            'range' => ['from' => $period['from'], 'to' => $period['to']],
            'count' => $items->count(),
            'data' => OvertimeRequestSummaryResource::collection($items),
        ]);
    }

    /**
     * Ringkasan aktivitas: cuti, perjalanan dinas, lembur dalam periode (contoh: Maret 2026).
     *
     * Filter rentang: cuti & LOT memakai approved_at; lembur memakai finished_at.
     * Cuti: approved, auto_approved, closed, cancelled (+ cancellations). LOT: approved & closed. Lembur: finished.
     *
     * Query: `year` (wajib jika **`period`** tidak digunakan), `month` (opsional); atau **`period=today`**
     * / **`period=yesterday`**.
     */
    public function activityTimeline(Request $request, Employee $employee): JsonResponse
    {
        [$from, $to, $periodPayload] = $this->resolveActivityPeriodBoundaries($request);

        $body = $this->activityPayloadBody($employee, $from, $to);

        return response()->json([
            'success' => true,
            'period' => $periodPayload,
        ] + $body);
    }

    /**
     * Timeline aktivitas via NIK; query `period=today|yesterday` sama seperti `activityTimeline`.
     */
    public function activityTimelineByNik(Request $request, string $nik): JsonResponse
    {
        [$from, $to, $periodPayload] = $this->resolveActivityPeriodBoundaries($request);

        $employee = $this->employeeFromNik($nik, $from);
        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => $this->workforceNikUnavailableMessage(),
            ], 404);
        }

        $body = $this->activityPayloadBody($employee, $from, $to);

        return response()->json([
            'success' => true,
            'period' => $periodPayload,
        ] + $body);
    }

    /**
     * Daftar cuti karyawan (filter berdasarkan approved_at dalam rentang).
     *
     * Hanya status approved, auto_approved, closed, cancelled; termasuk pengajuan pembatalan di field cancellations.
     *
     * Query: year (opsional), month (opsional 1–12), atau from & to (Y-m-d).
     * Jika year diisi: rentang kalender (tanpa month = seluruh tahun). Jika tidak: from+to, atau default 90 hari terakhir.
     */
    public function leaveRequests(Request $request, Employee $employee): JsonResponse
    {
        [$from, $to, $period] = $this->parseWorkforceDateRange($request);

        $this->applyWorkforceAdministrationScope($employee, $from);

        $items = $this->leaveRequestsBetween($employee, $from, $to);

        return response()->json([
            'success' => true,
            'period' => $period,
            'range' => ['from' => $period['from'], 'to' => $period['to']],
            'count' => $items->count(),
            'data' => LeaveRequestSummaryResource::collection($items),
        ]);
    }

    public function leaveRequestsByNik(Request $request, string $nik): JsonResponse
    {
        [$from, $to] = array_slice($this->parseWorkforceDateRange($request), 0, 2);

        $employee = $this->employeeFromNik($nik, $from);
        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => $this->workforceNikUnavailableMessage(),
            ], 404);
        }

        return $this->leaveRequests($request, $employee);
    }

    /**
     * Perjalanan dinas (traveler = administrasi milik karyawan). Filter rentang: approved_at.
     *
     * Hanya status approved dan closed.
     *
     * Query: year, month, atau from & to — sama seperti leaveRequests.
     */
    public function officialTravels(Request $request, Employee $employee): JsonResponse
    {
        [$from, $to, $period] = $this->parseWorkforceDateRange($request);

        $this->applyWorkforceAdministrationScope($employee, $from);

        $items = $this->officialTravelsBetween($employee, $from, $to);

        return response()->json([
            'success' => true,
            'period' => $period,
            'range' => ['from' => $period['from'], 'to' => $period['to']],
            'count' => $items->count(),
            'data' => WorkforceOfficialtravelResource::collection($items),
        ]);
    }

    public function officialTravelsByNik(Request $request, string $nik): JsonResponse
    {
        [$from, $to] = array_slice($this->parseWorkforceDateRange($request), 0, 2);

        $employee = $this->employeeFromNik($nik, $from);
        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => $this->workforceNikUnavailableMessage(),
            ], 404);
        }

        return $this->officialTravels($request, $employee);
    }

    /**
     * Lembur: header permintaan lembur (status finished). Filter rentang: finished_at.
     *
     * Query: year, month, atau from & to — sama seperti leaveRequests.
     */
    public function overtimeRequests(Request $request, Employee $employee): JsonResponse
    {
        [$from, $to, $period] = $this->parseWorkforceDateRange($request);

        $this->applyWorkforceAdministrationScope($employee, $from);

        $items = $this->overtimeRequestsBetween($employee, $from, $to);

        return response()->json([
            'success' => true,
            'period' => $period,
            'range' => ['from' => $period['from'], 'to' => $period['to']],
            'count' => $items->count(),
            'data' => OvertimeRequestSummaryResource::collection($items),
        ]);
    }

    public function overtimeRequestsByNik(Request $request, string $nik): JsonResponse
    {
        [$from, $to] = array_slice($this->parseWorkforceDateRange($request), 0, 2);

        $employee = $this->employeeFromNik($nik, $from);
        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => $this->workforceNikUnavailableMessage(),
            ], 404);
        }

        return $this->overtimeRequests($request, $employee);
    }

    /**
     * Konten ringkas aktivitas satu karyawan (tanpa blok `period` / `employee_id`).
     *
     * @return array<string, mixed>
     */
    private function activityPayloadBody(Employee $employee, Carbon $from, Carbon $to): array
    {
        $this->applyWorkforceAdministrationScope($employee, $from);

        $leaveRequests = $this->leaveRequestsBetween($employee, $from, $to);
        $officialTravels = $this->officialTravelsBetween($employee, $from, $to);
        $overtimeRequests = $this->overtimeRequestsBetween($employee, $from, $to);

        return [
            'employee' => new WorkforceEmployeeResource($employee),
            'administrations' => WorkforceAdministrationResource::collection($employee->administrations),
            'summary' => [
                'leave_requests_count' => $leaveRequests->count(),
                'official_travels_count' => $officialTravels->count(),
                'overtime_requests_count' => $overtimeRequests->count(),
            ],
            'leave_requests' => LeaveRequestSummaryResource::collection($leaveRequests),
            'official_travels' => WorkforceOfficialtravelResource::collection($officialTravels),
            'overtime_requests' => OvertimeRequestSummaryResource::collection($overtimeRequests),
        ];
    }

    /** ID karyawan yang punya cuti / LOT / lembur selesai dalam rentang — aturan sama seperti endpoint aktivitas tunggal. */
    private function employeeIdsWithAnyWorkforceDocumentInPeriod(Carbon $from, Carbon $to): Collection
    {
        $leaveIds = LeaveRequest::query()
            ->whereIn('status', self::WORKFORCE_LEAVE_STATUSES)
            ->whereNotNull('approved_at')
            ->where('approved_at', '>=', $from)
            ->where('approved_at', '<=', $to)
            ->distinct()
            ->pluck('employee_id');

        $travelTbl = (new Officialtravel)->getTable();
        $admTbl = (new Administration)->getTable();

        $lotIds = Officialtravel::query()
            ->join($admTbl, "{$travelTbl}.traveler_id", '=', "{$admTbl}.id")
            ->whereIn("{$travelTbl}.status", [
                Officialtravel::STATUS_APPROVED,
                Officialtravel::STATUS_CLOSED,
            ])
            ->whereNotNull("{$travelTbl}.approved_at")
            ->where("{$travelTbl}.approved_at", '>=', $from)
            ->where("{$travelTbl}.approved_at", '<=', $to)
            ->whereNotNull("{$admTbl}.employee_id")
            ->distinct()
            ->pluck("{$admTbl}.employee_id");

        $otTbl = (new OvertimeRequest)->getTable();
        $detailTbl = (new OvertimeRequestDetail)->getTable();

        $otIds = OvertimeRequest::query()
            ->join($detailTbl, "{$otTbl}.id", '=', "{$detailTbl}.overtime_request_id")
            ->join($admTbl, "{$detailTbl}.administration_id", '=', "{$admTbl}.id")
            ->where("{$otTbl}.status", OvertimeRequest::STATUS_FINISHED)
            ->whereNotNull("{$otTbl}.finished_at")
            ->where("{$otTbl}.finished_at", '>=', $from)
            ->where("{$otTbl}.finished_at", '<=', $to)
            ->whereNotNull("{$admTbl}.employee_id")
            ->distinct()
            ->pluck("{$admTbl}.employee_id");

        return collect([$leaveIds, $lotIds, $otIds])
            ->flatten()
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Resolve karyawan dari NIK untuk workforce: aktif selalu allowed; tidak aktif jika ada konteks rentang
     * dan **mulai rentang ≤ termination_date** (terminasi terisi).
     */
    private function employeeFromNik(string $nik, ?Carbon $periodFrom = null): ?Employee
    {
        $administration = Administration::where('nik', $nik)->first();

        if ($administration === null || $administration->employee === null) {
            return null;
        }

        if (! $this->administrationEligibleForWorkforcePeriod($administration, $periodFrom)) {
            return null;
        }

        return $administration->employee;
    }

    /**
     * Aktif: eligible. Tidak aktif: eligible hanya jika ada rentang dan termination_date terisi dan
     * tanggal mulai rentang tidak sepenuhnya setelah terminasi (overlap dengan masa kerja sampai terminasi).
     */
    private function administrationEligibleForWorkforcePeriod(Administration $administration, ?Carbon $periodFrom): bool
    {
        if ((int) $administration->is_active === 1) {
            return true;
        }

        if ($periodFrom === null) {
            return false;
        }

        if ($administration->termination_date === null) {
            return false;
        }

        return $periodFrom->toDateString() <= $administration->termination_date->format('Y-m-d');
    }

    /**
     * Batasi koleksi administrasi pada employee untuk respons/query lembur:
     * tanpa rentang: hanya aktif; dengan rentang: aktif atau tidak aktif yang masih eligible pada rentang tersebut.
     */
    private function applyWorkforceAdministrationScope(Employee $employee, ?Carbon $periodFrom): void
    {
        $admins = $employee->administrations()
            ->with(['position.department', 'project'])
            ->get()
            ->filter(fn (Administration $a) => $this->administrationEligibleForWorkforcePeriod($a, $periodFrom))
            ->values();

        $employee->setRelation('administrations', $admins);
    }

    private function workforceNikUnavailableMessage(): string
    {
        return 'No employee found for this NIK, or the NIK is inactive for the requested period without a matching termination date.';
    }

    /**
     * Rentang aktivitas timeline (tahun+bulan atau shortcut `period=today|yesterday`).
     *
     * @return array{0: Carbon, 1: Carbon, 2: array<string, mixed>}
     */
    private function resolveActivityPeriodBoundaries(Request $request): array
    {
        $validated = $request->validate([
            'period' => 'nullable|string|in:today,yesterday',
            'year' => 'nullable|integer|min:2000|max:2100|required_without:period',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        if ($request->filled('period')) {
            return $this->boundsForWorkforcePresetDay((string) $validated['period']);
        }

        $year = (int) $validated['year'];
        $month = isset($validated['month']) ? (int) $validated['month'] : null;
        [$from, $to] = $this->boundsForCalendarYearMonth($year, $month);

        return [
            $from,
            $to,
            [
                'year' => $year,
                'month' => $month,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
        ];
    }

    /**
     * Satu hari kalender aplikasi untuk `period=today|yesterday`.
     *
     * @return array{0: Carbon, 1: Carbon, 2: array<string, mixed>}
     */
    private function boundsForWorkforcePresetDay(string $preset): array
    {
        $day = match ($preset) {
            'today' => Carbon::today(),
            'yesterday' => Carbon::yesterday(),
            default => Carbon::today(),
        };
        $from = $day->copy()->startOfDay();
        $to = $day->copy()->endOfDay();

        return [
            $from,
            $to,
            [
                'year' => null,
                'month' => null,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'preset' => $preset,
            ],
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: array{year: int|null, month: int|null, from: string, to: string}}
     */
    private function parseWorkforceDateRange(Request $request): array
    {
        $request->validate([
            'period' => 'nullable|string|in:today,yesterday',
            'year' => 'nullable|integer|min:2000|max:2100|required_with:month',
            'month' => 'nullable|integer|min:1|max:12',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        if ($request->filled('period')) {
            [$from, $to, $periodMeta] = $this->boundsForWorkforcePresetDay((string) $request->input('period'));

            return [$from, $to, $periodMeta];
        }

        if ($request->filled('year')) {
            $year = (int) $request->input('year');
            $month = $request->filled('month') ? (int) $request->input('month') : null;
            [$from, $to] = $this->boundsForCalendarYearMonth($year, $month);

            return [$from, $to, [
                'year' => $year,
                'month' => $month,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ]];
        }

        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->input('from'))->startOfDay();
            $to = Carbon::parse($request->input('to'))->endOfDay();

            return [$from, $to, [
                'year' => null,
                'month' => null,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ]];
        }

        $to = Carbon::now()->endOfDay();
        $from = Carbon::now()->subDays(90)->startOfDay();

        return [$from, $to, [
            'year' => null,
            'month' => null,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]];
    }

    /**
     * Period in response when client passes year (and optional month); does not filter profile payload.
     *
     * @return array{year: int, month: int|null, from: string, to: string}|null
     */
    private function optionalPeriodFromYearMonthQuery(Request $request): ?array
    {
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100|required_with:month',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        if (! $request->filled('year')) {
            return null;
        }

        $year = (int) $request->input('year');
        $month = $request->filled('month') ? (int) $request->input('month') : null;
        [$from, $to] = $this->boundsForCalendarYearMonth($year, $month);

        return [
            'year' => $year,
            'month' => $month,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function boundsForCalendarYearMonth(int $year, ?int $month): array
    {
        if ($month !== null) {
            $from = Carbon::create($year, $month, 1)->startOfDay();
            $to = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
        } else {
            $from = Carbon::create($year, 1, 1)->startOfDay();
            $to = Carbon::create($year, 12, 31)->endOfDay();
        }

        return [$from, $to];
    }

    /**
     * Cuti dengan approved_at dalam rentang [from, to] (inklusif, boundary datetime).
     * Hanya approved, auto_approved, closed, dan cancelled; termasuk riwayat pembatalan (partial/full) di relasi cancellations.
     */
    private function leaveRequestsBetween(Employee $employee, Carbon $from, Carbon $to): Collection
    {
        return LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->whereIn('status', self::WORKFORCE_LEAVE_STATUSES)
            ->whereNotNull('approved_at')
            ->where('approved_at', '>=', $from)
            ->where('approved_at', '<=', $to)
            ->with([
                'leaveType',
                'administration',
                'employee',
                'cancellations' => fn ($q) => $q->orderByDesc('requested_at'),
            ])
            ->orderByDesc('approved_at')
            ->get();
    }

    /**
     * LOT dengan approved_at dalam rentang [from, to]. Hanya status approved dan closed.
     */
    private function officialTravelsBetween(Employee $employee, Carbon $from, Carbon $to): Collection
    {
        return Officialtravel::query()
            ->whereIn('status', [Officialtravel::STATUS_APPROVED, Officialtravel::STATUS_CLOSED])
            ->whereHas('traveler', function ($q) use ($employee) {
                $q->where('employee_id', $employee->id);
            })
            ->whereNotNull('approved_at')
            ->where('approved_at', '>=', $from)
            ->where('approved_at', '<=', $to)
            ->with([
                'traveler.employee',
                'traveler.position.department',
                'traveler.project',
                'project',
                'transportation',
                'accommodation',
                'details.follower.employee',
                'stops.arrivalChecker',
                'stops.departureChecker',
                'creator',
                'approval_plans' => fn ($q) => $q->orderBy('id'),
                'approval_plans.approver',
            ])
            ->orderByDesc('approved_at')
            ->get();
    }

    /**
     * Permintaan lembur (status finished) dengan finished_at dalam rentang [from, to] dan detail untuk administrasi karyawan.
     */
    private function overtimeRequestsBetween(Employee $employee, Carbon $from, Carbon $to): Collection
    {
        $adminIds = $employee->administrations->pluck('id')->all();

        if ($adminIds === []) {
            return collect();
        }

        return OvertimeRequest::query()
            ->where('status', OvertimeRequest::STATUS_FINISHED)
            ->whereNotNull('finished_at')
            ->where('finished_at', '>=', $from)
            ->where('finished_at', '<=', $to)
            ->whereHas('details', function ($q) use ($adminIds) {
                $q->whereIn('administration_id', $adminIds);
            })
            ->with(['project', 'details' => fn ($q) => $q->orderBy('sort_order'), 'details.administration.employee'])
            ->orderByDesc('finished_at')
            ->get();
    }
}
