<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdministrationResource;
use App\Http\Resources\LeaveRequestSummaryResource;
use App\Http\Resources\OvertimeRequestSummaryResource;
use App\Http\Resources\WorkforceEmployeeResource;
use App\Http\Resources\WorkforceOfficialtravelResource;
use App\Models\Administration;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Officialtravel;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EmployeeWorkforceApiController extends Controller
{
    /** Status cuti yang diekspos di API workforce (termasuk yang dibatalkan / cancelled). */
    private const WORKFORCE_LEAVE_STATUSES = ['approved', 'auto_approved', 'closed', 'cancelled'];

    /**
     * Profil karyawan + seluruh administrasi (relasi lengkap untuk integrasi).
     *
     * Query: year (opsional), month (opsional 1–12; hanya bersama year). Jika diisi, dikembalikan di blok period (tidak memfilter data profil).
     */
    public function showFull(Request $request, Employee $employee): JsonResponse
    {
        $period = $this->optionalPeriodFromYearMonthQuery($request);

        $employee->load([
            'administrations' => function ($q) {
                $q->with(['position.department', 'project']);
            },
        ]);

        $payload = [
            'success' => true,
            'data' => [
                'employee' => new WorkforceEmployeeResource($employee),
                'administrations' => AdministrationResource::collection($employee->administrations),
            ],
        ];

        if ($period !== null) {
            $payload['period'] = $period;
        }

        return response()->json($payload);
    }

    /**
     * Sama seperti showFull, dicari lewat NIK administrasi (mis. 13100).
     */
    public function showFullByNik(Request $request, string $nik): JsonResponse
    {
        $employee = $this->employeeFromNik($nik);
        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'No employee found for this NIK',
            ], 404);
        }

        return $this->showFull($request, $employee);
    }

    /**
     * Ringkasan aktivitas: cuti, perjalanan dinas, lembur dalam periode (contoh: Maret 2026).
     *
     * Filter rentang: cuti & LOT memakai approved_at; lembur memakai finished_at.
     * Cuti: approved, auto_approved, closed, cancelled (+ cancellations). LOT: approved & closed. Lembur: finished.
     *
     * Query: year (wajib), month (opsional 1–12). Tanpa month = seluruh tahun.
     */
    public function activityTimeline(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        $year = (int) $validated['year'];
        $month = isset($validated['month']) ? (int) $validated['month'] : null;

        [$from, $to] = $this->boundsForCalendarYearMonth($year, $month);

        $employee->load([
            'administrations' => function ($q) {
                $q->with(['position.department', 'project']);
            },
        ]);

        $leaveRequests = $this->leaveRequestsBetween($employee, $from, $to);
        $officialTravels = $this->officialTravelsBetween($employee, $from, $to);
        $overtimeRequests = $this->overtimeRequestsBetween($employee, $from, $to);

        return response()->json([
            'success' => true,
            'period' => [
                'year' => $year,
                'month' => $month,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'employee' => new WorkforceEmployeeResource($employee),
            'administrations' => AdministrationResource::collection($employee->administrations),
            'summary' => [
                'leave_requests_count' => $leaveRequests->count(),
                'official_travels_count' => $officialTravels->count(),
                'overtime_requests_count' => $overtimeRequests->count(),
            ],
            'leave_requests' => LeaveRequestSummaryResource::collection($leaveRequests),
            'official_travels' => WorkforceOfficialtravelResource::collection($officialTravels),
            'overtime_requests' => OvertimeRequestSummaryResource::collection($overtimeRequests),
        ]);
    }

    public function activityTimelineByNik(Request $request, string $nik): JsonResponse
    {
        $employee = $this->employeeFromNik($nik);
        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'No employee found for this NIK',
            ], 404);
        }

        return $this->activityTimeline($request, $employee);
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
        $employee = $this->employeeFromNik($nik);
        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'No employee found for this NIK',
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
        $employee = $this->employeeFromNik($nik);
        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'No employee found for this NIK',
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
        $employee = $this->employeeFromNik($nik);
        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'No employee found for this NIK',
            ], 404);
        }

        return $this->overtimeRequests($request, $employee);
    }

    private function employeeFromNik(string $nik): ?Employee
    {
        $administration = Administration::where('nik', $nik)->first();

        return $administration?->employee;
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: array{year: int|null, month: int|null, from: string, to: string}}
     */
    private function parseWorkforceDateRange(Request $request): array
    {
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100|required_with:month',
            'month' => 'nullable|integer|min:1|max:12',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

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
        $adminIds = $employee->administrations()->pluck('id')->all();

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
            ->with(['project', 'details' => fn ($q) => $q->orderBy('sort_order'), 'details.administration'])
            ->orderByDesc('finished_at')
            ->get();
    }
}
