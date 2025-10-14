<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveEntitlement;
use App\Models\LeaveType;
use App\Models\LeaveRequestCancellation;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeaveReportController extends Controller
{
    /**
     * Display reports index
     */
    public function index()
    {
        return view('reports.leave-index', [
            'title' => 'Leave Reports',
            'subtitle' => 'HR Leave Analytics & Reports',
        ]);
    }


    /**
     * Display leave usage by project report
     */
    public function byProject(Request $request)
    {
        $query = LeaveRequest::with(['employee.administrations.project', 'leaveType', 'cancellations'])
            ->whereIn('status', ['approved', 'closed']); // Include closed requests

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
            $totalEffectiveDays = $requests->sum(function ($request) {
                return $request->getEffectiveDays();
            });

            $totalCancelledDays = $requests->sum(function ($request) {
                return $request->getTotalCancelledDays();
            });

            return [
                'project_name' => $projectName,
                'total_requests' => $requests->count(),
                'total_days' => $requests->sum('total_days'),
                'effective_days' => $totalEffectiveDays,
                'cancelled_days' => $totalCancelledDays,
                'by_type' => $requests->groupBy('leaveType.name')->map(function ($typeRequests) {
                    $typeEffectiveDays = $typeRequests->sum(function ($request) {
                        return $request->getEffectiveDays();
                    });
                    $typeCancelledDays = $typeRequests->sum(function ($request) {
                        return $request->getTotalCancelledDays();
                    });

                    return [
                        'count' => $typeRequests->count(),
                        'total_days' => $typeRequests->sum('total_days'),
                        'effective_days' => $typeEffectiveDays,
                        'cancelled_days' => $typeCancelledDays,
                        'utilization_rate' => $typeRequests->sum('total_days') > 0
                            ? round(($typeEffectiveDays / $typeRequests->sum('total_days')) * 100, 2)
                            : 0
                    ];
                })
            ];
        });

        return view('reports.leave-by-project', compact('projectData'))->with('title', 'Leave by Project Report');
    }



    /**
     * Export leave data to Excel
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'summary');

        switch ($type) {
            case 'by_project':
                return $this->exportByProject($request);
            default:
                return back()->with(['toast_error' => 'Invalid export type.']);
        }
    }


    /**
     * Export by project report
     */
    private function exportByProject(Request $request)
    {
        $query = LeaveRequest::with(['employee.administrations.project', 'leaveType', 'cancellations'])
            ->whereIn('status', ['approved', 'closed']);

        // Apply same filters as byProject method
        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        $leaveRequests = $query->get();
        $projectData = $leaveRequests->groupBy(function ($item) {
            return $item->employee->administrations->first()->project->project_name ?? 'Unknown';
        });

        $data = collect();
        foreach ($projectData as $projectName => $requests) {
            $totalEffectiveDays = $requests->sum(function ($request) {
                return $request->getEffectiveDays();
            });
            $totalCancelledDays = $requests->sum(function ($request) {
                return $request->getTotalCancelledDays();
            });

            $data->push([
                'Project Name' => $projectName,
                'Total Requests' => $requests->count(),
                'Total Days' => $requests->sum('total_days'),
                'Effective Days' => $totalEffectiveDays,
                'Cancelled Days' => $totalCancelledDays,
                'Utilization Rate %' => $requests->sum('total_days') > 0
                    ? round(($totalEffectiveDays / $requests->sum('total_days')) * 100, 2)
                    : 0
            ]);
        }

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            private $data;
            public function __construct($data)
            {
                $this->data = $data;
            }
            public function collection()
            {
                return $this->data;
            }
            public function headings(): array
            {
                return [
                    'Project Name',
                    'Total Requests',
                    'Total Days',
                    'Effective Days',
                    'Cancelled Days',
                    'Utilization Rate %'
                ];
            }
        }, 'leave_by_project_report.xlsx');
    }

    /**
     * Export accumulation report
     */
    private function exportAccumulation(Request $request)
    {
        $query = LeaveEntitlement::with(['employee', 'leaveType'])
            ->where('leave_type_id', function ($query) {
                $query->select('id')
                    ->from('leave_types')
                    ->where('category', 'annual');
            });

        // Apply same filters as accumulation method
        if ($request->filled('year')) {
            $query->whereYear('period_start', $request->year);
        } else {
            $query->whereYear('period_start', now()->year);
        }

        $data = $query->get()->map(function ($entitlement) {
            $effectiveDays = LeaveRequest::where('employee_id', $entitlement->employee_id)
                ->where('leave_type_id', $entitlement->leave_type_id)
                ->whereBetween('start_date', [$entitlement->period_start, $entitlement->period_end])
                ->where('status', 'approved')
                ->get()
                ->sum(function ($request) {
                    return $request->getEffectiveDays();
                });

            $cancelledDays = LeaveRequest::where('employee_id', $entitlement->employee_id)
                ->where('leave_type_id', $entitlement->leave_type_id)
                ->whereBetween('start_date', [$entitlement->period_start, $entitlement->period_end])
                ->where('status', 'approved')
                ->get()
                ->sum(function ($request) {
                    return $request->getTotalCancelledDays();
                });

            return [
                'Employee Name' => $entitlement->employee->fullname,
                'Withdrawable Days' => $entitlement->withdrawable_days,
                'Deposit Days' => $entitlement->deposit_days,
                'Taken Days' => $entitlement->taken_days,
                'Effective Days' => $effectiveDays,
                'Cancelled Days' => $cancelledDays,
                'Remaining Days' => $entitlement->remaining_days,
                'Utilization %' => $entitlement->withdrawable_days > 0
                    ? round(($effectiveDays / $entitlement->withdrawable_days) * 100, 2)
                    : 0,
                'Cancellation Rate %' => $entitlement->taken_days > 0
                    ? round(($cancelledDays / $entitlement->taken_days) * 100, 2)
                    : 0
            ];
        });

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            private $data;
            public function __construct($data)
            {
                $this->data = $data;
            }
            public function collection()
            {
                return $this->data;
            }
            public function headings(): array
            {
                return [
                    'Employee Name',
                    'Withdrawable Days',
                    'Deposit Days',
                    'Taken Days',
                    'Effective Days',
                    'Cancelled Days',
                    'Remaining Days',
                    'Utilization %',
                    'Cancellation Rate %'
                ];
            }
        }, 'leave_accumulation_report.xlsx');
    }

    /**
     * Export balance report
     */
    private function exportBalance(Request $request)
    {
        $query = LeaveEntitlement::with(['employee', 'leaveType'])
            ->where('remaining_days', '>', 0);

        // Apply same filters as balance method
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        $data = $query->get()->map(function ($entitlement) {
            $effectiveDays = LeaveRequest::where('employee_id', $entitlement->employee_id)
                ->where('leave_type_id', $entitlement->leave_type_id)
                ->whereBetween('start_date', [$entitlement->period_start, $entitlement->period_end])
                ->where('status', 'approved')
                ->get()
                ->sum(function ($request) {
                    return $request->getEffectiveDays();
                });

            $cancelledDays = LeaveRequest::where('employee_id', $entitlement->employee_id)
                ->where('leave_type_id', $entitlement->leave_type_id)
                ->whereBetween('start_date', [$entitlement->period_start, $entitlement->period_end])
                ->where('status', 'approved')
                ->get()
                ->sum(function ($request) {
                    return $request->getTotalCancelledDays();
                });

            $actualRemaining = $entitlement->withdrawable_days - $effectiveDays;

            return [
                'Employee Name' => $entitlement->employee->fullname,
                'Leave Type' => $entitlement->leaveType->name,
                'Period Start' => $entitlement->period_start->format('d M Y'),
                'Period End' => $entitlement->period_end->format('d M Y'),
                'Withdrawable Days' => $entitlement->withdrawable_days,
                'Deposit Days' => $entitlement->deposit_days,
                'Taken Days' => $entitlement->taken_days,
                'Effective Days' => $effectiveDays,
                'Cancelled Days' => $cancelledDays,
                'Remaining Days' => $entitlement->remaining_days,
                'Actual Remaining' => $actualRemaining,
                'Utilization %' => $entitlement->withdrawable_days > 0
                    ? round(($effectiveDays / $entitlement->withdrawable_days) * 100, 2)
                    : 0,
                'Is Accurate' => $entitlement->remaining_days == $actualRemaining ? 'Yes' : 'No'
            ];
        });

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            private $data;
            public function __construct($data)
            {
                $this->data = $data;
            }
            public function collection()
            {
                return $this->data;
            }
            public function headings(): array
            {
                return [
                    'Employee Name',
                    'Leave Type',
                    'Period Start',
                    'Period End',
                    'Withdrawable Days',
                    'Deposit Days',
                    'Taken Days',
                    'Effective Days',
                    'Cancelled Days',
                    'Remaining Days',
                    'Actual Remaining',
                    'Utilization %',
                    'Is Accurate'
                ];
            }
        }, 'leave_balance_report.xlsx');
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

    /**
     * Display leave request monitoring report
     */
    public function monitoring(Request $request)
    {
        $query = LeaveRequest::with(['employee', 'leaveType', 'administration.project', 'cancellations'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by leave type
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->whereHas('administration', function ($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        $leaveRequests = $query->paginate(50);
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees = Employee::with('administrations')->get();
        $projects = Project::where('project_status', 1)->get();

        return view('reports.leave-monitoring', compact('leaveRequests', 'leaveTypes', 'employees', 'projects'))
            ->with('title', 'Leave Request Monitoring Report');
    }

    /**
     * Display leave cancellation report
     */
    public function cancellation(Request $request)
    {
        $query = LeaveRequestCancellation::with(['leaveRequest.employee', 'leaveRequest.leaveType', 'requestedBy'])
            ->orderBy('requested_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('requested_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('requested_at', '<=', $request->end_date);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->whereHas('leaveRequest', function ($q) use ($request) {
                $q->where('employee_id', $request->employee_id);
            });
        }

        $cancellations = $query->paginate(50);
        $employees = Employee::with('administrations')->get();

        return view('reports.leave-cancellation', compact('cancellations', 'employees'))
            ->with('title', 'Leave Cancellation Report');
    }

    /**
     * Display detailed leave entitlement report
     */
    public function entitlementDetailed(Request $request)
    {
        $query = LeaveEntitlement::with(['employee', 'leaveType']);

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

        $entitlements = $query->get()->map(function ($entitlement) {
            $details = $entitlement->getLeaveCalculationDetails();
            return array_merge($details, [
                'employee_name' => $entitlement->employee->fullname,
                'employee_id' => $entitlement->employee_id,
                'leave_type_name' => $entitlement->leaveType->name,
                'period_start' => $entitlement->period_start->format('d M Y'),
                'period_end' => $entitlement->period_end->format('d M Y'),
            ]);
        });

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees = Employee::with('administrations')->get();

        return view('reports.leave-entitlement-detailed', compact('entitlements', 'leaveTypes', 'employees'))
            ->with('title', 'Leave Entitlement Detailed Report');
    }

    /**
     * Display auto conversion tracking report
     */
    public function autoConversion(Request $request)
    {
        $query = LeaveRequest::with(['employee', 'leaveType', 'administration.project'])
            ->whereNotNull('auto_conversion_at')
            ->where('auto_conversion_at', '<=', now()->addDays(7)) // Show upcoming conversions
            ->orderBy('auto_conversion_at', 'asc');

        // Filter by conversion status
        if ($request->filled('conversion_status')) {
            switch ($request->conversion_status) {
                case 'due_soon':
                    $query->where('auto_conversion_at', '<=', now()->addDays(3))
                        ->where('auto_conversion_at', '>', now());
                    break;
                case 'overdue':
                    $query->where('auto_conversion_at', '<', now());
                    break;
                case 'upcoming':
                    $query->where('auto_conversion_at', '>', now());
                    break;
            }
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $autoConversions = $query->get()->map(function ($request) {
            $daysUntilConversion = now()->diffInDays($request->auto_conversion_at, false);

            return [
                'id' => $request->id,
                'employee_name' => $request->employee->fullname,
                'leave_type_name' => $request->leaveType->name,
                'start_date' => $request->start_date->format('d M Y'),
                'end_date' => $request->end_date->format('d M Y'),
                'total_days' => $request->total_days,
                'auto_conversion_at' => $request->auto_conversion_at->format('d M Y H:i'),
                'days_until_conversion' => $daysUntilConversion,
                'conversion_status' => $daysUntilConversion < 0 ? 'overdue' : ($daysUntilConversion <= 3 ? 'due_soon' : 'upcoming'),
                'has_document' => !empty($request->supporting_document),
                'project_name' => $request->administration->project->project_name ?? 'Unknown',
                'created_at' => $request->created_at->format('d M Y'),
            ];
        });

        $employees = Employee::with('administrations')->get();

        return view('reports.leave-auto-conversion', compact('autoConversions', 'employees'))
            ->with('title', 'Auto Conversion Tracking Report');
    }

    /**
     * Export leave monitoring report
     */
    public function exportMonitoring(Request $request)
    {
        $query = LeaveRequest::with(['employee', 'leaveType', 'administration.project', 'cancellations']);

        // Apply same filters as monitoring method
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        $data = $query->get()->map(function ($request) {
            return [
                'Employee Name' => $request->employee->fullname,
                'Leave Type' => $request->leaveType->name,
                'Start Date' => $request->start_date->format('d M Y'),
                'End Date' => $request->end_date->format('d M Y'),
                'Total Days' => $request->total_days,
                'Effective Days' => $request->getEffectiveDays(),
                'Status' => ucfirst($request->status),
                'Project' => $request->administration->project->project_name ?? 'Unknown',
                'Requested At' => $request->created_at->format('d M Y H:i'),
                'Auto Conversion' => $request->auto_conversion_at ? $request->auto_conversion_at->format('d M Y H:i') : '-',
                'Has Document' => !empty($request->supporting_document) ? 'Yes' : 'No',
            ];
        });

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            private $data;
            public function __construct($data)
            {
                $this->data = $data;
            }
            public function collection()
            {
                return $this->data;
            }
            public function headings(): array
            {
                return [
                    'Employee Name',
                    'Leave Type',
                    'Start Date',
                    'End Date',
                    'Total Days',
                    'Effective Days',
                    'Status',
                    'Project',
                    'Requested At',
                    'Auto Conversion',
                    'Has Document'
                ];
            }
        }, 'leave_monitoring_report.xlsx');
    }

    /**
     * Export leave cancellation report
     */
    public function exportCancellation(Request $request)
    {
        $query = LeaveRequestCancellation::with(['leaveRequest.employee', 'leaveRequest.leaveType', 'requestedBy']);

        // Apply same filters as cancellation method
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->where('requested_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('requested_at', '<=', $request->end_date);
        }
        if ($request->filled('employee_id')) {
            $query->whereHas('leaveRequest', function ($q) use ($request) {
                $q->where('employee_id', $request->employee_id);
            });
        }

        $data = $query->get()->map(function ($cancellation) {
            return [
                'Employee Name' => $cancellation->leaveRequest->employee->fullname,
                'Leave Type' => $cancellation->leaveRequest->leaveType->name,
                'Original Start Date' => $cancellation->leaveRequest->start_date->format('d M Y'),
                'Original End Date' => $cancellation->leaveRequest->end_date->format('d M Y'),
                'Original Total Days' => $cancellation->leaveRequest->total_days,
                'Days to Cancel' => $cancellation->days_to_cancel,
                'Cancellation Status' => ucfirst($cancellation->status),
                'Reason' => $cancellation->reason,
                'Requested By' => $cancellation->requestedBy->name ?? '-',
                'Requested At' => $cancellation->requested_at->format('d M Y H:i'),
                'Confirmed At' => $cancellation->confirmed_at ? $cancellation->confirmed_at->format('d M Y H:i') : '-',
            ];
        });

        return Excel::download(new class($data) implements FromCollection, WithHeadings {
            private $data;
            public function __construct($data)
            {
                $this->data = $data;
            }
            public function collection()
            {
                return $this->data;
            }
            public function headings(): array
            {
                return [
                    'Employee Name',
                    'Leave Type',
                    'Original Start Date',
                    'Original End Date',
                    'Original Total Days',
                    'Days to Cancel',
                    'Cancellation Status',
                    'Reason',
                    'Requested By',
                    'Requested At',
                    'Confirmed At'
                ];
            }
        }, 'leave_cancellation_report.xlsx');
    }
}
