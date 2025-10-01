<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveEntitlement;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveReportController extends Controller
{
    /**
     * Display reports index
     */
    public function index()
    {
        return view('reports.leave-index')->with('title', 'Leave Reports');
    }

    /**
     * Display leave summary report
     */
    public function summary(Request $request)
    {
        $query = LeaveEntitlement::with(['employee', 'leaveType'])
            ->select('employee_id', 'leave_type_id', 'period_start', 'period_end')
            ->selectRaw('SUM(entitled_days) as total_entitled')
            ->selectRaw('SUM(taken_days) as total_taken')
            ->selectRaw('SUM(remaining_days) as total_remaining')
            ->groupBy('employee_id', 'leave_type_id', 'period_start', 'period_end');

        // Filter by year
        if ($request->filled('year')) {
            $query->whereYear('period_start', $request->year);
        } else {
            $query->whereYear('period_start', now()->year);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by leave type
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        $summary = $query->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees = Employee::with('administrations')->get();

        return view('reports.leave-summary', compact('summary', 'leaveTypes', 'employees'))->with('title', 'Leave Summary Report');
    }

    /**
     * Display leave usage by project report
     */
    public function byProject(Request $request)
    {
        $query = LeaveRequest::with(['employee.administrations.project', 'leaveType'])
            ->where('status', 'approved');

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        $leaveRequests = $query->get();

        // Group by project
        $projectData = $leaveRequests->groupBy(function ($item) {
            return $item->employee->administrations->first()->project->project_name ?? 'Unknown';
        })->map(function ($requests, $projectName) {
            return [
                'project_name' => $projectName,
                'total_requests' => $requests->count(),
                'total_days' => $requests->sum('total_days'),
                'by_type' => $requests->groupBy('leaveType.name')->map(function ($typeRequests) {
                    return [
                        'count' => $typeRequests->count(),
                        'days' => $typeRequests->sum('total_days')
                    ];
                })
            ];
        });

        return view('reports.leave-by-project', compact('projectData'));
    }

    /**
     * Display leave accumulation report
     */
    public function accumulation(Request $request)
    {
        $query = LeaveEntitlement::with(['employee', 'leaveType'])
            ->where('leave_type_id', function ($query) {
                $query->select('id')
                    ->from('leave_types')
                    ->where('category', 'annual');
            });

        // Filter by year
        if ($request->filled('year')) {
            $query->whereYear('period_start', $request->year);
        } else {
            $query->whereYear('period_start', now()->year);
        }

        $accumulation = $query->get()->map(function ($entitlement) {
            return [
                'employee' => $entitlement->employee->fullname,
                'entitled_days' => $entitlement->entitled_days,
                'taken_days' => $entitlement->taken_days,
                'remaining_days' => $entitlement->remaining_days,
                'utilization_percentage' => $entitlement->entitled_days > 0
                    ? round(($entitlement->taken_days / $entitlement->entitled_days) * 100, 2)
                    : 0
            ];
        });

        return view('reports.leave-accumulation', compact('accumulation'));
    }

    /**
     * Display leave balance report
     */
    public function balance(Request $request)
    {
        $query = LeaveEntitlement::with(['employee', 'leaveType'])
            ->where('remaining_days', '>', 0);

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by leave type
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        $balances = $query->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees = Employee::with('administrations')->get();

        return view('reports.leave-balance', compact('balances', 'leaveTypes', 'employees'));
    }

    /**
     * Export leave data to Excel
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'summary');

        switch ($type) {
            case 'summary':
                return $this->exportSummary($request);
            case 'by_project':
                return $this->exportByProject($request);
            case 'accumulation':
                return $this->exportAccumulation($request);
            case 'balance':
                return $this->exportBalance($request);
            default:
                return back()->withErrors(['error' => 'Invalid export type.']);
        }
    }

    /**
     * Export summary report
     */
    private function exportSummary(Request $request)
    {
        // This will be implemented with Laravel Excel package
        // For now, return a simple response
        return response()->json(['message' => 'Export functionality will be implemented with Laravel Excel']);
    }

    /**
     * Export by project report
     */
    private function exportByProject(Request $request)
    {
        return response()->json(['message' => 'Export functionality will be implemented with Laravel Excel']);
    }

    /**
     * Export accumulation report
     */
    private function exportAccumulation(Request $request)
    {
        return response()->json(['message' => 'Export functionality will be implemented with Laravel Excel']);
    }

    /**
     * Export balance report
     */
    private function exportBalance(Request $request)
    {
        return response()->json(['message' => 'Export functionality will be implemented with Laravel Excel']);
    }

    /**
     * Get leave statistics for dashboard
     */
    public function statistics(Request $request)
    {
        $year = $request->get('year', now()->year);

        $stats = [
            'total_requests' => LeaveRequest::whereYear('created_at', $year)->count(),
            'pending_requests' => LeaveRequest::where('status', 'pending')->count(),
            'approved_requests' => LeaveRequest::where('status', 'approved')->count(),
            'rejected_requests' => LeaveRequest::where('status', 'rejected')->count(),
            'total_leave_days' => LeaveRequest::where('status', 'approved')
                ->whereYear('created_at', $year)
                ->sum('total_days'),
            'by_type' => LeaveRequest::with('leaveType')
                ->where('status', 'approved')
                ->whereYear('created_at', $year)
                ->get()
                ->groupBy('leaveType.name')
                ->map(function ($requests) {
                    return [
                        'count' => $requests->count(),
                        'days' => $requests->sum('total_days')
                    ];
                })
        ];

        return response()->json($stats);
    }

    /**
     * Get employee leave balance for API
     */
    public function getEmployeeLeaveBalance($employeeId)
    {
        $balances = LeaveEntitlement::where('employee_id', $employeeId)
            ->where('remaining_days', '>', 0)
            ->with('leaveType')
            ->get()
            ->map(function ($balance) {
                return [
                    'leave_type_name' => $balance->leaveType->name,
                    'remaining_days' => $balance->remaining_days
                ];
            });

        return response()->json($balances);
    }
}
