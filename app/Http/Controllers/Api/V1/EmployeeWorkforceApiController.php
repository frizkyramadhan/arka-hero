<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdministrationResource;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\LeaveRequestSummaryResource;
use App\Http\Resources\OfficialtravelResource;
use App\Http\Resources\OvertimeRequestSummaryResource;
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
    /**
     * Profil karyawan + seluruh administrasi (relasi lengkap untuk integrasi).
     */
    public function showFull(Employee $employee): JsonResponse
    {
        $employee->load([
            'religion',
            'administrations' => function ($q) {
                $q->with(['position.department', 'project']);
            },
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => new EmployeeResource($employee),
                'administrations' => AdministrationResource::collection($employee->administrations),
            ],
        ]);
    }

    /**
     * Sama seperti showFull, dicari lewat NIK administrasi (mis. 13100).
     */
    public function showFullByNik(string $nik): JsonResponse
    {
        $employee = $this->employeeFromNik($nik);
        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'No employee found for this NIK',
            ], 404);
        }

        return $this->showFull($employee);
    }

    /**
     * Ringkasan aktivitas: cuti, perjalanan dinas, lembur dalam periode (contoh: Maret 2026).
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

        if ($month !== null) {
            $from = Carbon::create($year, $month, 1)->startOfDay();
            $to = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
        } else {
            $from = Carbon::create($year, 1, 1)->startOfDay();
            $to = Carbon::create($year, 12, 31)->endOfDay();
        }

        $employee->load([
            'religion',
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
            'employee' => new EmployeeResource($employee),
            'administrations' => AdministrationResource::collection($employee->administrations),
            'summary' => [
                'leave_requests_count' => $leaveRequests->count(),
                'official_travels_count' => $officialTravels->count(),
                'overtime_requests_count' => $overtimeRequests->count(),
            ],
            'leave_requests' => LeaveRequestSummaryResource::collection($leaveRequests),
            'official_travels' => OfficialtravelResource::collection($officialTravels),
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
     * Daftar cuti karyawan (filter tanggal overlap dengan rentang).
     *
     * Query: from, to (Y-m-d). Default: 90 hari terakhir.
     */
    public function leaveRequests(Request $request, Employee $employee): JsonResponse
    {
        [$from, $to] = $this->parseRangeOrDefault($request);

        $items = $this->leaveRequestsBetween($employee, $from, $to);

        return response()->json([
            'success' => true,
            'range' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
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
     * Perjalanan dinas (traveler = administrasi milik karyawan).
     */
    public function officialTravels(Request $request, Employee $employee): JsonResponse
    {
        [$from, $to] = $this->parseRangeOrDefault($request);

        $items = $this->officialTravelsBetween($employee, $from, $to);

        return response()->json([
            'success' => true,
            'range' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'count' => $items->count(),
            'data' => OfficialtravelResource::collection($items),
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
     * Lembur: header permintaan lembur yang mencakup baris detail untuk administrasi karyawan ini.
     */
    public function overtimeRequests(Request $request, Employee $employee): JsonResponse
    {
        [$from, $to] = $this->parseRangeOrDefault($request);

        $items = $this->overtimeRequestsBetween($employee, $from, $to);

        return response()->json([
            'success' => true,
            'range' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
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
     * @return array{0: Carbon, 1: Carbon}
     */
    private function parseRangeOrDefault(Request $request): array
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->input('from'))->startOfDay();
            $to = Carbon::parse($request->input('to'))->endOfDay();

            return [$from, $to];
        }

        $to = Carbon::now()->endOfDay();
        $from = Carbon::now()->subDays(90)->startOfDay();

        return [$from, $to];
    }

    /**
     * Cuti yang overlap dengan rentang tanggal [from, to].
     */
    private function leaveRequestsBetween(Employee $employee, Carbon $from, Carbon $to): Collection
    {
        return LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->whereDate('start_date', '<=', $to->toDateString())
            ->whereDate('end_date', '>=', $from->toDateString())
            ->with(['leaveType', 'administration'])
            ->orderBy('start_date')
            ->get();
    }

    /**
     * LOT dengan tanggal dinas utama di dalam rentang (official_travel_date).
     */
    private function officialTravelsBetween(Employee $employee, Carbon $from, Carbon $to): Collection
    {
        return Officialtravel::query()
            ->whereHas('traveler', function ($q) use ($employee) {
                $q->where('employee_id', $employee->id);
            })
            ->whereDate('official_travel_date', '>=', $from->toDateString())
            ->whereDate('official_travel_date', '<=', $to->toDateString())
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
            ->orderBy('official_travel_date', 'desc')
            ->get();
    }

    /**
     * Permintaan lembur pada tanggal dalam rentang yang memiliki detail untuk administrasi karyawan.
     */
    private function overtimeRequestsBetween(Employee $employee, Carbon $from, Carbon $to): Collection
    {
        $adminIds = $employee->administrations()->pluck('id')->all();

        if ($adminIds === []) {
            return collect();
        }

        return OvertimeRequest::query()
            ->whereDate('overtime_date', '>=', $from->toDateString())
            ->whereDate('overtime_date', '<=', $to->toDateString())
            ->whereHas('details', function ($q) use ($adminIds) {
                $q->whereIn('administration_id', $adminIds);
            })
            ->with(['project', 'details' => fn ($q) => $q->orderBy('sort_order'), 'details.administration'])
            ->orderBy('overtime_date', 'desc')
            ->get();
    }
}
