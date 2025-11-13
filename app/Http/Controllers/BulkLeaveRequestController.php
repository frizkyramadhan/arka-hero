<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Project;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\Department;
use Illuminate\Support\Str;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use App\Models\ApprovalStage;
use App\Models\Administration;
use App\Models\LeaveEntitlement;
use App\Models\Roster;
use App\Models\RosterDailyStatus;
use Illuminate\Support\Facades\DB;
use App\Services\RosterLeaveService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApprovalPlanController;

class BulkLeaveRequestController extends Controller
{
    protected $rosterLeaveService;

    public function __construct(RosterLeaveService $rosterLeaveService)
    {
        $this->rosterLeaveService = $rosterLeaveService;
    }

    /**
     * Display a listing of bulk leave request batches
     */
    public function index()
    {
        $batches = LeaveRequest::where('is_batch_request', true)
            ->whereNotNull('batch_id')
            ->select(
                'batch_id',
                'bulk_notes',
                DB::raw('MIN(created_at) as created_at'),
                DB::raw('COUNT(*) as total_requests')
            )
            ->groupBy('batch_id', 'bulk_notes')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('leave-requests.bulk-index', compact('batches'))
            ->with('title', 'Bulk Leave Requests');
    }

    /**
     * Show the form for creating a new bulk leave request
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // Get roster-based projects only
        $projects = Project::where('project_status', 1)
            ->where('leave_type', 'roster')
            ->whereHas('administrations', function ($q) {
                $q->where('is_active', 1);
            })
            ->get();

        // Auto-select project if requested or if user has only one project
        $selectedProjectId = $request->get('project_id');
        if (!$selectedProjectId && $user->employee) {
            $userProjects = $user->employee->administrations()
                ->where('is_active', 1)
                ->pluck('project_id')
                ->unique();

            if ($userProjects->count() == 1) {
                $selectedProjectId = $userProjects->first();
            }
        }

        $employeesDue = collect();
        $periodicLeaveType = $this->rosterLeaveService->getPeriodicLeaveType();

        // If project is selected, get employees due for leave
        if ($selectedProjectId) {
            $employeesDue = $this->rosterLeaveService->getEmployeesDueForLeave($selectedProjectId, 14);
        }

        return view('leave-requests.bulk-create', compact(
            'projects',
            'selectedProjectId',
            'employeesDue',
            'periodicLeaveType'
        ))
            ->with('title', 'Create Periodic Leave Request');
    }

    /**
     * Get employees due for leave for a specific project (AJAX)
     */
    public function getEmployeesDue(Request $request)
    {
        try {
            $projectId = $request->get('project_id');
            $daysAhead = $request->get('days_ahead', 14);
            $departmentId = $request->get('department_id'); // Optional: filter by department

            if (!$projectId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Project ID is required'
                ], 400);
            }

            $employeesDue = $this->rosterLeaveService->getEmployeesDueForLeave($projectId, $daysAhead, $departmentId);

            // Format data for response
            $formattedData = $employeesDue->map(function ($item) {
                $administration = $item['administration'] ?? null;
                $employee = $item['employee'] ?? null;
                $position = $administration && $administration->position ? $administration->position : null;
                $department = $position && $position->department ? $position->department : null;

                return [
                    'employee_id' => $item['employee_id'] ?? null,
                    'employee_nik' => $administration && $administration->nik ? $administration->nik : 'N/A',
                    'employee_name' => $employee && $employee->fullname ? $employee->fullname : 'N/A',
                    'position_name' => $position && $position->position_name ? $position->position_name : '-',
                    'department_name' => $department && $department->department_name ? $department->department_name : '-',
                    'roster_note' => $item['roster_note'] ?? null,
                    'off_start_date' => $item['off_start_date'] ? $item['off_start_date']->format('Y-m-d') : null,
                    'off_end_date' => $item['off_end_date'] ? $item['off_end_date']->format('Y-m-d') : null,
                    'off_days' => $item['off_days'] ?? 0,
                    'is_due' => $item['is_due'] ?? false,
                    'days_until_off' => $item['days_until_off'] ?? 0,
                    'administration_id' => $administration && $administration->id ? $administration->id : null
                ];
            });

            return response()->json([
                'success' => true,
                'employees' => $formattedData
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getEmployeesDue: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load employee data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get departments for a specific project (AJAX)
     */
    public function getDepartmentsByProject(Request $request)
    {
        $projectId = $request->get('project_id');

        if (!$projectId) {
            return response()->json(['error' => 'Project ID is required'], 400);
        }

        $departments = Department::whereHas('positions.administrations', function ($q) use ($projectId) {
            $q->where('project_id', $projectId)
                ->where('is_active', 1);
        })
            ->where('department_status', 1)
            ->orderBy('department_name', 'asc')
            ->get(['id', 'department_name']);

        return response()->json([
            'success' => true,
            'departments' => $departments
        ]);
    }

    /**
     * Get bulk approval preview for selected employees (AJAX)
     * Shows expected approvers based on hierarchical logic for periodic leave
     */
    public function getBulkApprovalPreview(Request $request)
    {
        $employeeIds = $request->get('employee_ids', []);
        $projectId = $request->get('project_id');

        if (empty($employeeIds) || !$projectId) {
            return response()->json([
                'success' => false,
                'message' => 'Employee IDs and Project ID are required'
            ], 400);
        }

        // Get administrations with level info
        $administrations = Administration::with(['position.department', 'level', 'employee'])
            ->whereIn('employee_id', $employeeIds)
            ->where('project_id', $projectId)
            ->where('is_active', 1)
            ->get();

        // Helper function to get approval flow for a specific employee level
        $getApprovalFlowForLevel = function ($employeeLevelOrder, $approversWithLevel, $managerApprover, $directorApprover, $hierarchicalApprover) {
            $approvers = [];

            if ($employeeLevelOrder == 6) {
                // Director level - show configured stages
                $approvers[] = [
                    'order' => 1,
                    'name' => 'Follow configured approval stages',
                    'level' => 'Various',
                    'level_order' => 0, // Special marker for Director
                    'note' => 'Director-level requests follow standard approval flow'
                ];
            } elseif ($employeeLevelOrder == 5) {
                // Manager level - Director only
                if ($directorApprover) {
                    $approvers[] = $directorApprover;
                }
            } elseif ($employeeLevelOrder == 3) {
                // Supervisor (Level 3) - SPT(4) + Manager(5)
                $sptApprover = null;
                foreach ($approversWithLevel as $item) {
                    if ($item['level_order'] == 4) {
                        $sptApprover = [
                            'order' => 1,
                            'name' => $item['approver']->name,
                            'level' => $item['level_name'],
                            'level_order' => $item['level_order']
                        ];
                        break;
                    }
                }
                if ($sptApprover) {
                    $approvers[] = $sptApprover;
                }
                if ($managerApprover) {
                    $approvers[] = $managerApprover;
                }
            } elseif ($employeeLevelOrder == 4) {
                // Superintendent (Level 4) - SPT(4) + Manager(5)
                $sptApprover = null;
                foreach ($approversWithLevel as $item) {
                    if ($item['level_order'] == 4) {
                        $sptApprover = [
                            'order' => 1,
                            'name' => $item['approver']->name,
                            'level' => $item['level_name'],
                            'level_order' => $item['level_order']
                        ];
                        break;
                    }
                }
                if ($sptApprover) {
                    $approvers[] = $sptApprover;
                }
                if ($managerApprover) {
                    $approvers[] = $managerApprover;
                }
            } else {
                // Level 1-2 (Non Staff/Foreman) - SPV(3) or SPT(4) + Manager(5)
                if ($hierarchicalApprover) {
                    $approvers[] = $hierarchicalApprover;
                }
                if ($managerApprover) {
                    $approvers[] = $managerApprover;
                }
            }

            // Create signature for this approval flow
            $signature = collect($approvers)->map(function ($a) {
                // For Director level, use note instead of level_order
                if (isset($a['note'])) {
                    return $a['name'] . '|' . $a['note'];
                }
                return $a['name'] . '|' . ($a['level_order'] ?? 0);
            })->implode(',');

            return [
                'approvers' => $approvers,
                'signature' => $signature
            ];
        };

        // Group by department first to get approval stages
        $departmentApprovers = [];
        $departmentGroups = [];

        foreach ($administrations as $admin) {
            if (!$admin->position || !$admin->position->department || !$admin->level) continue;

            $deptId = $admin->position->department_id;
            $deptName = $admin->position->department->department_name;

            // Cache approval stages per department
            if (!isset($departmentApprovers[$deptId])) {
                // Get approval stages filtered by project and department from approval_stage_details
                $approvalStages = ApprovalStage::where('document_type', 'leave_request')
                    ->whereHas('details', function ($q) use ($projectId, $deptId) {
                        $q->where('project_id', $projectId)
                            ->where('department_id', $deptId);
                    })
                    ->with(['approver.employee.administrations' => function ($query) {
                        $query->where('is_active', 1)
                            ->with('level');
                    }])
                    ->orderBy('approval_order', 'asc')
                    ->get();

                // Collect all approvers with their level info
                $approversWithLevel = [];
                $hierarchicalApprover = null;
                $managerApprover = null;
                $directorApprover = null;

                foreach ($approvalStages as $stage) {
                    $approver = $stage->approver;
                    if (!$approver || !$approver->employee) continue;

                    // Get level from any active administration (not limited to same project)
                    $approverAdmin = $approver->employee->administrations
                        ->where('is_active', 1)
                        ->first();

                    if (!$approverAdmin || !$approverAdmin->level) continue;

                    $approversWithLevel[] = [
                        'approver' => $approver,
                        'admin' => $approverAdmin,
                        'level_order' => $approverAdmin->level->level_order,
                        'level_name' => $approverAdmin->level->name,
                        'approval_order' => $stage->approval_order
                    ];
                }

                // Find SPV (level 3) first
                foreach ($approversWithLevel as $item) {
                    if (!$hierarchicalApprover && $item['level_order'] == 3) {
                        $hierarchicalApprover = [
                            'order' => 1,
                            'name' => $item['approver']->name,
                            'level' => $item['level_name'],
                            'level_order' => $item['level_order']
                        ];
                        break;
                    }
                }

                // If no SPV found, look for SPT(4)
                if (!$hierarchicalApprover) {
                    foreach ($approversWithLevel as $item) {
                        if ($item['level_order'] == 4) {
                            $hierarchicalApprover = [
                                'order' => 1,
                                'name' => $item['approver']->name,
                                'level' => $item['level_name'],
                                'level_order' => $item['level_order']
                            ];
                            break;
                        }
                    }
                }

                // Look for Manager(5)
                foreach ($approversWithLevel as $item) {
                    if (!$managerApprover && $item['level_order'] == 5) {
                        $managerApprover = [
                            'order' => 2,
                            'name' => $item['approver']->name,
                            'level' => $item['level_name'],
                            'level_order' => $item['level_order']
                        ];
                        break;
                    }
                }

                // Look for Director(6)
                foreach ($approversWithLevel as $item) {
                    if (!$directorApprover && $item['level_order'] == 6) {
                        $directorApprover = [
                            'order' => 1,
                            'name' => $item['approver']->name,
                            'level' => $item['level_name'],
                            'level_order' => $item['level_order']
                        ];
                        break;
                    }
                }

                $departmentApprovers[$deptId] = [
                    'approversWithLevel' => $approversWithLevel,
                    'hierarchicalApprover' => $hierarchicalApprover,
                    'managerApprover' => $managerApprover,
                    'directorApprover' => $directorApprover
                ];
            }

            // Get approval flow for this employee's level
            $approvalFlow = $getApprovalFlowForLevel(
                $admin->level->level_order,
                $departmentApprovers[$deptId]['approversWithLevel'],
                $departmentApprovers[$deptId]['managerApprover'],
                $departmentApprovers[$deptId]['directorApprover'],
                $departmentApprovers[$deptId]['hierarchicalApprover']
            );

            // Create unique key: department_id + approval_flow_signature
            $groupKey = $deptId . '_' . md5($approvalFlow['signature']);

            if (!isset($departmentGroups[$groupKey])) {
                $departmentGroups[$groupKey] = [
                    'department_id' => $deptId,
                    'department_name' => $deptName,
                    'employee_count' => 0,
                    'employees' => [],
                    'approvers' => $approvalFlow['approvers'],
                    'level_summary' => []
                ];
            }

            $departmentGroups[$groupKey]['employee_count']++;
            $departmentGroups[$groupKey]['employees'][] = [
                'name' => $admin->employee->fullname ?? 'Unknown',
                'level' => $admin->level->name ?? 'Unknown',
                'level_order' => $admin->level->level_order ?? 0
            ];
        }

        // Calculate level summary for each group
        foreach ($departmentGroups as $groupKey => &$group) {
            // Level summary for display
            $levelCounts = collect($group['employees'])->groupBy('level')->map->count();
            $group['level_summary'] = $levelCounts->map(function ($count, $level) {
                return "$level ($count)";
            })->values()->toArray();
        }

        // Sort approval groups by department name (ascending)
        $sortedGroups = collect($departmentGroups)->sortBy(function ($group) {
            return $group['department_name'];
        })->values()->toArray();

        // Count unique departments (not total groups)
        $uniqueDepartments = collect($departmentGroups)->pluck('department_id')->unique()->count();

        return response()->json([
            'success' => true,
            'approval_groups' => $sortedGroups,
            'total_departments' => $uniqueDepartments,
            'total_employees' => count($employeeIds)
        ]);
    }

    /**
     * Store bulk leave requests
     */
    public function store(Request $request)
    {
        $request->validate([
            'selected_employees' => 'required|array|min:1',
            'selected_employees.*.employee_id' => 'required|exists:employees,id',
            'selected_employees.*.administration_id' => 'required|exists:administrations,id',
            'selected_employees.*.start_date' => 'required|date',
            'selected_employees.*.end_date' => 'required|date|after_or_equal:selected_employees.*.start_date',
            'selected_employees.*.total_days' => 'required|integer|min:1',
            'bulk_notes' => 'nullable|string|max:1000'
        ]);

        $periodicLeaveType = $this->rosterLeaveService->getPeriodicLeaveType();

        if (!$periodicLeaveType) {
            return back()->with('toast_error', 'Periodic leave type not found.');
        }

        DB::beginTransaction();
        try {
            // Generate unique batch ID
            $batchId = 'BULK_' . now()->format('YmdHis') . '_' . Str::upper(Str::random(6));
            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($request->selected_employees as $empData) {
                try {
                    $employee = Employee::find($empData['employee_id']);
                    $administration = Administration::find($empData['administration_id']);

                    if (!$employee || !$administration) {
                        $errors[] = "Employee or administration not found: {$empData['employee_id']}";
                        $failedCount++;
                        continue;
                    }

                    // Note: Periodic leave is roster-based, not entitlement-based
                    // The leave schedule is determined by the employee's roster pattern
                    // No need to check entitlement balance for periodic leave

                    // Calculate period
                    $startDate = Carbon::parse($empData['start_date']);
                    $endDate = Carbon::parse($empData['end_date']);
                    $period = $startDate->format('Y');

                    // Create leave request
                    $leaveRequest = LeaveRequest::create([
                        'employee_id' => $employee->id,
                        'administration_id' => $administration->id,
                        'leave_type_id' => $periodicLeaveType->id,
                        'start_date' => $empData['start_date'],
                        'end_date' => $empData['end_date'],
                        'back_to_work_date' => $endDate->copy()->addDay()->format('Y-m-d'),
                        'reason' => null, // Periodic leave doesn't require reason
                        'total_days' => $empData['total_days'],
                        'status' => 'pending',
                        'leave_period' => $period,
                        'requested_at' => now(),
                        'requested_by' => Auth::id(),
                        'is_batch_request' => true,
                        'batch_id' => $batchId,
                        'bulk_notes' => $request->bulk_notes
                    ]);

                    // Create approval plan for this request
                    $response = app(ApprovalPlanController::class)
                        ->create_approval_plan('leave_request', $leaveRequest->id);

                    if (!$response) {
                        throw new \Exception("Failed to create approval plan for {$employee->fullname}");
                    }

                    // Update roster_daily_status notes for the leave period
                    $this->updateRosterNotes(
                        $administration->id,
                        $startDate,
                        $endDate,
                        $batchId,
                        $request->bulk_notes
                    );

                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to create request for employee {$empData['employee_id']}: {$e->getMessage()}";
                    $failedCount++;
                }
            }

            if ($successCount == 0) {
                DB::rollback();
                return back()
                    ->with('toast_error', 'Failed to create any leave requests. ' . implode(', ', $errors))
                    ->withInput();
            }

            DB::commit();

            $message = "Successfully created {$successCount} leave request(s).";
            if ($failedCount > 0) {
                $message .= " {$failedCount} request(s) failed.";
            }

            return redirect()->route('leave.bulk-requests.show', ['batch_id' => $batchId])
                ->with('toast_success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->with('toast_error', 'Failed to create bulk leave requests: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display a specific bulk leave request batch
     */
    public function show($batchId)
    {
        $leaveRequests = LeaveRequest::where('batch_id', $batchId)
            ->with([
                'employee',
                'administration.position.department',
                'administration.project',
                'leaveType',
                'approvalPlans.approver',
                'requestedBy'
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        if ($leaveRequests->isEmpty()) {
            return redirect()->route('bulk-leave-requests.index')
                ->with('toast_error', 'Batch not found');
        }

        $batchInfo = [
            'batch_id' => $batchId,
            'total_requests' => $leaveRequests->count(),
            'created_at' => $leaveRequests->first()->created_at,
            'bulk_notes' => $leaveRequests->first()->bulk_notes,
            'requested_by' => $leaveRequests->first()->requestedBy
        ];

        // Count by status
        $statusCounts = $leaveRequests->groupBy('status')->map->count();

        return view('leave-requests.bulk-show', compact('leaveRequests', 'batchInfo', 'statusCounts'))
            ->with('title', 'Bulk Leave Request Detail');
    }

    /**
     * Cancel entire bulk leave request batch
     */
    public function cancelBatch(Request $request, $batchId)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $leaveRequests = LeaveRequest::where('batch_id', $batchId)
                ->where('status', 'pending')
                ->get();

            if ($leaveRequests->isEmpty()) {
                return back()->with('toast_error', 'No pending requests found in this batch');
            }

            $cancelledCount = 0;
            foreach ($leaveRequests as $leaveRequest) {
                $leaveRequest->update([
                    'status' => 'cancelled'
                ]);

                // Clear roster notes for cancelled leave request
                $this->clearRosterNotes(
                    $leaveRequest->administration_id,
                    Carbon::parse($leaveRequest->start_date),
                    Carbon::parse($leaveRequest->end_date),
                    $batchId
                );

                $cancelledCount++;
            }

            DB::commit();

            return back()->with('toast_success', "Successfully cancelled {$cancelledCount} leave request(s)");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('toast_error', 'Failed to cancel batch: ' . $e->getMessage());
        }
    }

    /**
     * Update roster daily status notes for leave period
     * Menandai di roster bahwa status cuti sudah dibuatkan leave request
     *
     * @param int $administrationId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $batchId
     * @param string|null $bulkNotes
     * @return void
     */
    private function updateRosterNotes($administrationId, $startDate, $endDate, $batchId, $bulkNotes = null)
    {
        try {
            // Find roster for this administration
            $roster = Roster::where('administration_id', $administrationId)
                ->where('is_active', true)
                ->first();

            if (!$roster) {
                Log::warning("Roster not found for administration: {$administrationId}");
                return;
            }

            // Prepare notes text
            $notesText = "{$batchId}";
            if ($bulkNotes) {
                $notesText .= " - {$bulkNotes}";
            }

            // Update notes for each day in the leave period
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                // Update or create roster daily status with notes
                RosterDailyStatus::where('roster_id', $roster->id)
                    ->where('date', $currentDate->format('Y-m-d'))
                    ->update([
                        'notes' => $notesText
                    ]);

                Log::info("Updated roster notes", [
                    'roster_id' => $roster->id,
                    'date' => $currentDate->format('Y-m-d'),
                    'batch_id' => $batchId,
                    'notes' => $notesText
                ]);

                $currentDate->addDay();
            }
        } catch (\Exception $e) {
            Log::error("Failed to update roster notes: " . $e->getMessage(), [
                'administration_id' => $administrationId,
                'batch_id' => $batchId,
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw exception - this is not critical enough to fail the whole process
        }
    }

    /**
     * Clear roster daily status notes for cancelled leave period
     * Menghapus penanda di roster saat leave request dibatalkan
     *
     * @param int $administrationId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $batchId
     * @return void
     */
    private function clearRosterNotes($administrationId, $startDate, $endDate, $batchId)
    {
        try {
            // Find roster for this administration
            $roster = Roster::where('administration_id', $administrationId)
                ->where('is_active', true)
                ->first();

            if (!$roster) {
                Log::warning("Roster not found for administration: {$administrationId}");
                return;
            }

            // Clear notes for each day in the leave period
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                // Only clear if notes contain the batch_id
                RosterDailyStatus::where('roster_id', $roster->id)
                    ->where('date', $currentDate->format('Y-m-d'))
                    ->where('notes', 'like', "%{$batchId}%")
                    ->update([
                        'notes' => null
                    ]);

                Log::info("Cleared roster notes", [
                    'roster_id' => $roster->id,
                    'date' => $currentDate->format('Y-m-d'),
                    'batch_id' => $batchId
                ]);

                $currentDate->addDay();
            }
        } catch (\Exception $e) {
            Log::error("Failed to clear roster notes: " . $e->getMessage(), [
                'administration_id' => $administrationId,
                'batch_id' => $batchId,
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw exception - this is not critical enough to fail the whole process
        }
    }
}
