<?php

namespace App\Http\Controllers;

use App\Exports\LeaveEntitlementExport;
use App\Imports\LeaveEntitlementImport;
use App\Models\Administration;
use App\Models\Employee;
use App\Models\LeaveEntitlement;
use App\Models\LeaveType;
use App\Models\Project;
use App\Models\User;
use App\Services\AdministrationYearsOfServiceCalculator;
use App\Services\LeaveEntitlementCarryOverService;
use App\Support\UserProject;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class LeaveEntitlementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:leave-entitlements.show')->only('index', 'show', 'data', 'showEmployee', 'getAvailableLeaveTypes', 'getLeaveCalculationDetailsAjax', 'exportTemplate');
        // showLeaveCalculationDetails can be accessed by both admin (leave-entitlements.show) and personal users (personal.leave.view-entitlements)
        // Permission check is handled inside the method - allow either permission
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (! $user->can('leave-entitlements.show') && ! $user->can('personal.leave.view-entitlements')) {
                abort(403, 'Unauthorized access.');
            }

            return $next($request);
        })->only('showLeaveCalculationDetails');
        $this->middleware('permission:leave-entitlements.create')->only('create', 'store', 'generateProjectEntitlements', 'generateSelectedProjectEntitlements', 'importTemplate');
        $this->middleware('permission:leave-entitlements.edit')->only('edit', 'update', 'editEmployee', 'updateEmployee', 'importTemplate');
        $this->middleware('permission:leave-entitlements.delete')->only('destroy', 'clearAllEntitlements', 'deletePeriodEntitlements');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $projects = UserProject::projectsForSelect();

            $selectedProject = null;
            $showAllProjects = false;

            if ($request->filled('project_id')) {
                if ($request->project_id === 'all') {
                    $showAllProjects = true;
                } else {
                    if (! UserProject::canAccessProjectId((int) $request->project_id)) {
                        return redirect()
                            ->route('leave.entitlements.index')
                            ->with('toast_error', 'Anda tidak memiliki akses ke proyek tersebut.')
                            ->with('alert_title', 'Akses ditolak')
                            ->with('alert_type', 'warning');
                    }
                    $selectedProject = Project::findOrFail($request->project_id);
                }
            }

            return view('leave-entitlements.index', compact(
                'projects',
                'selectedProject',
                'showAllProjects'
            ))->with('title', 'Leave Entitlement Management');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * DataTable server-side processing for employee entitlements
     */
    public function data(Request $request)
    {
        try {
            $projectId = $request->get('project_id');

            if (! $projectId) {
                return response()->json([
                    'draw' => intval($request->get('draw')),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]);
            }

            // Get specific leave types for display: Sakit (paid) and Izin Tanpa Upah (unpaid)
            $sickLeaveType = LeaveType::where('category', 'paid')
                ->where('name', 'LIKE', '%Sakit%')
                ->where('is_active', true)
                ->first();

            $unpaidLeaveType = LeaveType::where('category', 'unpaid')
                ->where('is_active', true)
                ->first();

            if ($projectId === 'all') {
                // Employees in active administrations, limited to user_project assignment
                $query = $this->getAllProjectsEmployeesQuery();
                UserProject::scopeToAssignedProjects($query, 'administrations.project_id');
            } else {
                $project = Project::findOrFail($projectId);
                if (! UserProject::canAccessProjectId((int) $project->id)) {
                    return response()->json([
                        'draw' => intval($request->get('draw')),
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => [],
                        'error' => 'Forbidden',
                    ], 403);
                }
                $query = $this->getProjectEmployeesQuery($project);
            }

            // Get total records count
            $totalRecords = $query->count();

            // Apply search filter
            $searchValue = $request->get('search')['value'] ?? '';
            if (! empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('administrations.nik', 'LIKE', "%{$searchValue}%")
                        ->orWhere('employees.fullname', 'LIKE', "%{$searchValue}%")
                        ->orWhere('positions.position_name', 'LIKE', "%{$searchValue}%")
                        ->orWhere('levels.name', 'LIKE', "%{$searchValue}%");
                });
            }

            // Get filtered records count
            $filteredRecords = $query->count();

            // Apply ordering
            $orderColumn = $request->get('order')[0]['column'] ?? 1; // Default to NIK column (index 1)
            $orderDir = $request->get('order')[0]['dir'] ?? 'asc';

            // Define columns mapping for ordering
            $columns = [
                0 => 'DT_RowIndex', // No
                1 => 'administrations.nik', // NIK
                2 => 'employees.fullname', // Nama
                3 => 'positions.position_name', // Position
                4 => 'projects.project_code', // Project (only for all projects)
                5 => 'administrations.doh', // DOH
                6 => 'annual', // Annual
                7 => 'sick', // Sakit
                8 => 'unpaid', // Ijin Tanpa Upah
                9 => 'lsl', // LSL
                10 => 'actions', // Actions
            ];

            $orderColumnName = $columns[$orderColumn] ?? 'administrations.nik';

            // Special handling for NIK column to sort numerically
            if ($orderColumnName === 'administrations.nik') {
                $query->orderByRaw("CAST(administrations.nik AS UNSIGNED) {$orderDir}");
            } elseif (in_array($orderColumnName, ['administrations.nik', 'employees.fullname', 'administrations.doh', 'levels.name', 'projects.project_code'])) {
                // Only sort by database columns, not computed columns
                $query->orderBy($orderColumnName, $orderDir);
            } else {
                // For computed columns (paid, unpaid, annual, lsl, periodic, actions), default to NIK
                $query->orderByRaw('CAST(administrations.nik AS UNSIGNED) asc');
            }

            // Apply pagination
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $query->skip($start)->take($length);

            $administrations = $query->get();

            $data = [];
            foreach ($administrations as $index => $administration) {
                $employee = $administration->employee;

                // Skip if no employee found
                if (! $employee) {
                    continue;
                }

                // Get all entitlements grouped by leave type ID for paid leave lookup
                $entitlementsByTypeId = $employee->leaveEntitlements->keyBy('leave_type_id');

                // Get entitlements grouped by category for annual and LSL
                $entitlements = $employee->leaveEntitlements->keyBy('leaveType.category');

                // Get current active period entitlements for remaining days calculation
                $today = now();
                $currentPeriodEntitlements = $employee->leaveEntitlements()
                    ->where('period_start', '<=', $today)
                    ->where('period_end', '>=', $today)
                    ->with('leaveType')
                    ->get();

                $currentPeriodEntitlementsByCategory = $currentPeriodEntitlements->keyBy('leaveType.category');
                $currentPeriodEntitlementsByTypeId = $currentPeriodEntitlements->keyBy('leave_type_id');

                // Determine project type for this employee
                $projectType = null;
                if ($projectId === 'all') {
                    // For all projects, get the project type from the administration
                    $projectType = $administration->project->leave_type ?? 'non_roster';
                } else {
                    $projectType = $project->leave_type;
                }

                $row = [
                    'DT_RowIndex' => $start + $index + 1,
                    'nik' => $administration->nik ?? 'N/A',
                    'name' => $employee->fullname ?? 'N/A',
                    'position' => $administration->position ? $administration->position->position_name : 'N/A',
                    'doh' => $administration->doh ? $administration->doh->format('d/m/Y') : 'N/A',
                ];

                // Add project column for "All Projects" view
                if ($projectId === 'all') {
                    $row['project'] = $administration->project->project_code ?? 'N/A';
                }

                // Annual leave - always show (0 for roster projects)
                if ($projectType === 'roster') {
                    $row['annual'] = 0;
                    $row['annual_remaining'] = 0;
                } else {
                    $annualEntitlement = $entitlements->get('annual');
                    $row['annual'] = $annualEntitlement ? $annualEntitlement->entitled_days : 0;
                    $annualCurrent = $currentPeriodEntitlementsByCategory->get('annual');
                    $row['annual_remaining'] = $annualCurrent ? $annualCurrent->remaining_days : 0;
                }

                // Sakit (Sick Leave) - specific paid leave type
                if ($sickLeaveType) {
                    $sickEntitlement = $entitlementsByTypeId->get($sickLeaveType->id);
                    $row['sick'] = $sickEntitlement ? $sickEntitlement->entitled_days : 0;
                    $sickCurrent = $currentPeriodEntitlementsByTypeId->get($sickLeaveType->id);
                    $row['sick_remaining'] = $sickCurrent ? $sickCurrent->remaining_days : 0;
                } else {
                    $row['sick'] = 0;
                    $row['sick_remaining'] = 0;
                }

                // Ijin Tanpa Upah (Unpaid Leave)
                if ($unpaidLeaveType) {
                    $unpaidEntitlement = $entitlementsByTypeId->get($unpaidLeaveType->id);
                    $row['unpaid'] = $unpaidEntitlement ? $unpaidEntitlement->entitled_days : 0;
                    $unpaidCurrent = $currentPeriodEntitlementsByTypeId->get($unpaidLeaveType->id);
                    $row['unpaid_remaining'] = $unpaidCurrent ? $unpaidCurrent->remaining_days : 0;
                } else {
                    // Fallback to category-based lookup if specific type not found
                    $unpaidEntitlement = $entitlements->get('unpaid');
                    $row['unpaid'] = $unpaidEntitlement ? $unpaidEntitlement->entitled_days : 0;
                    $unpaidCurrent = $currentPeriodEntitlementsByCategory->get('unpaid');
                    $row['unpaid_remaining'] = $unpaidCurrent ? $unpaidCurrent->remaining_days : 0;
                }

                // LSL
                $lslEntitlement = $entitlements->get('lsl');
                $row['lsl'] = $lslEntitlement ? $lslEntitlement->entitled_days : 0;
                $lslCurrent = $currentPeriodEntitlementsByCategory->get('lsl');
                $row['lsl_remaining'] = $lslCurrent ? $lslCurrent->remaining_days : 0;

                // Get latest period from leave entitlements for edit URL
                $latestEntitlement = LeaveEntitlement::where('employee_id', $administration->employee_id)
                    ->orderBy('period_start', 'desc')
                    ->first();

                $editUrl = route('leave.entitlements.employee.edit', $administration->employee_id);
                if ($latestEntitlement) {
                    // Add period parameters as query string to edit URL for latest period
                    $editUrl = route('leave.entitlements.employee.edit', $administration->employee_id).
                        '?period_start='.$latestEntitlement->period_start->format('Y-m-d').
                        '&period_end='.$latestEntitlement->period_end->format('Y-m-d');
                }

                $row['actions'] = [
                    'view_url' => route('leave.entitlements.employee.show', $administration->employee_id),
                    'edit_url' => $editUrl,
                ];

                $data[] = $row;
            }

            return response()->json([
                'draw' => intval($request->get('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($request->get('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all employees from all active projects query builder (Administration-based)
     */
    private function getAllProjectsEmployeesQuery()
    {
        $query = Administration::whereNotNull('administrations.project_id')
            ->where('administrations.is_active', 1)
            ->with([
                'employee',
                'level',
                'position',
                'project',
                'employee.leaveEntitlements.leaveType' => function ($q) {
                    $q->where('is_active', true);
                },
            ])
            ->join('employees', 'administrations.employee_id', '=', 'employees.id')
            ->leftJoin('levels', 'administrations.level_id', '=', 'levels.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->select(
                'administrations.id as administration_id',
                'administrations.employee_id',
                'administrations.nik',
                'administrations.doh',
                'administrations.level_id',
                'administrations.position_id',
                'administrations.project_id',
                'employees.id as employee_id',
                'employees.fullname',
                'levels.name as level_name',
                'positions.position_name',
                'projects.project_code',
                'projects.leave_type'
            );

        return $query;
    }

    /**
     * Get project employees query builder (Administration-based)
     */
    private function getProjectEmployeesQuery($project)
    {
        return Administration::where('administrations.project_id', $project->id)
            ->where('administrations.is_active', true)
            ->with([
                'employee',
                'level',
                'position',
                'employee.leaveEntitlements.leaveType' => function ($q) {
                    $q->where('is_active', true);
                },
            ])
            ->join('employees', 'administrations.employee_id', '=', 'employees.id')
            ->leftJoin('levels', 'administrations.level_id', '=', 'levels.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->select(
                'administrations.id as administration_id',
                'administrations.employee_id',
                'administrations.nik',
                'administrations.doh',
                'administrations.level_id',
                'administrations.position_id',
                'administrations.project_id',
                'employees.id as employee_id',
                'employees.fullname',
                'levels.name as level_name',
                'positions.position_name'
            );
    }

    /**
     * Show the form for creating a new resource.
     * DEPRECATED: Redirect to index page - use employee.edit instead
     */
    public function create()
    {
        // Redirect to index page - users should use employee.edit instead
        return redirect()->route('leave.entitlements.index')
            ->with('toast_info', 'Please select an employee from the list to add entitlements.');
    }

    /**
     * Get available leave types for employee based on business rules
     */
    public function getAvailableLeaveTypes(Request $request)
    {
        $employeeId = $request->get('employee_id');

        if (! $employeeId) {
            return response()->json(['leaveTypes' => []]);
        }

        $employee = Employee::with(['administrations.project', 'administrations.level'])->find($employeeId);

        if ($employee && auth()->user() instanceof User && auth()->user()->can('leave-entitlements.show') && ! UserProject::canViewEmployee($employee)) {
            return response()->json(['leaveTypes' => [], 'message' => 'Forbidden'], 403);
        }

        $activeAdministration = $employee ? $employee->administrations->where('is_active', 1)->first() : null;
        if (! $activeAdministration && $employee) {
            $activeAdministration = $employee->administrations->first();
        }

        if (! $employee || ! $activeAdministration) {
            return response()->json(['leaveTypes' => []]);
        }

        $project = $activeAdministration->project;
        $eligibleCategories = $this->getEligibleLeaveCategories($project);

        $availableLeaveTypes = LeaveType::where('is_active', true)
            ->whereIn('category', $eligibleCategories)
            ->get()
            ->map(function ($leaveType) use ($employee) {
                $entitlementDays = $this->calculateEntitlementDays($leaveType, $employee);
                $isEligible = $entitlementDays > 0;

                return [
                    'id' => $leaveType->id,
                    'name' => $leaveType->name,
                    'category' => $leaveType->category,
                    'default_days' => $leaveType->default_days,
                    'is_eligible' => $isEligible,
                    'calculated_days' => $entitlementDays,
                ];
            });

        return response()->json(['leaveTypes' => $availableLeaveTypes]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'entitled_days' => 'required|integer|min:0',
            'deposit_days' => 'nullable|integer|min:0',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        if ($r = $this->guardEntitlementEmployeeForHr($employee)) {
            return $r;
        }

        DB::beginTransaction();
        try {
            $leaveType = LeaveType::findOrFail($request->leave_type_id);

            // Validate business rules
            $validationErrors = $this->validateEntitlementAssignment($employee, $leaveType, $request->entitled_days);
            if (! empty($validationErrors)) {
                return back()->with(['toast_error' => implode('. ', $validationErrors)]);
            }

            // Check for duplicate entitlement
            $existing = LeaveEntitlement::where('employee_id', $request->employee_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->where('period_start', $request->period_start)
                ->where('period_end', $request->period_end)
                ->first();

            if ($existing) {
                return back()->with(['toast_error' => 'Leave entitlement already exists for this employee and period.']);
            }

            $entitlement = LeaveEntitlement::create([
                'employee_id' => $request->employee_id,
                'leave_type_id' => $request->leave_type_id,
                'period_start' => $request->period_start,
                'period_end' => $request->period_end,
                'entitled_days' => (int) $request->entitled_days,
                'deposit_days' => (int) ($request->deposit_days ?? $leaveType->getDepositDays()),
                'taken_days' => (int) ($request->taken_days ?? 0),
            ]);

            DB::commit();

            // Redirect to employee entitlements page instead of root level show
            return redirect()->route('leave.entitlements.employee.show', $employee)
                ->with('toast_success', 'Leave entitlement created successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with(['toast_error' => 'Failed to create leave entitlement: '.$e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     * DEPRECATED: Redirect to employee entitlements page
     */
    public function show(LeaveEntitlement $leaveEntitlement)
    {
        // Redirect to employee entitlements page instead of root level show
        $employee = $leaveEntitlement->employee;
        if ($r = $this->guardEntitlementEmployeeForHr($employee)) {
            return $r;
        }

        return redirect()->route('leave.entitlements.employee.show', $employee)
            ->with('toast_info', 'Redirected to employee entitlements page.');
    }

    /**
     * Show the form for editing the specified resource.
     * DEPRECATED: Redirect to employee entitlements edit page
     */
    public function edit(LeaveEntitlement $leaveEntitlement)
    {
        // Redirect to employee entitlements edit page instead of root level edit
        $employee = $leaveEntitlement->employee;
        if ($r = $this->guardEntitlementEmployeeForHr($employee)) {
            return $r;
        }

        return redirect()->route('leave.entitlements.employee.edit', $employee)
            ->with('toast_info', 'Redirected to employee entitlements edit page.');
    }

    /**
     * Update the specified resource in storage.
     * DEPRECATED: Use updateEmployee instead
     */
    public function update(Request $request, LeaveEntitlement $leaveEntitlement)
    {
        $request->validate([
            'entitled_days' => 'required|integer|min:0',
            'deposit_days' => 'nullable|integer|min:0',
        ]);

        $employee = $leaveEntitlement->employee;
        if ($r = $this->guardEntitlementEmployeeForHr($employee)) {
            return $r;
        }

        DB::beginTransaction();
        try {
            $leaveEntitlement->update([
                'entitled_days' => $request->entitled_days,
                'deposit_days' => $request->deposit_days ?? 0,
            ]);

            // remaining_days is now calculated via accessor, no need to recalculate
            $leaveEntitlement->save();

            DB::commit();

            // Redirect to employee entitlements page instead of root level show

            return redirect()->route('leave.entitlements.employee.show', $employee)
                ->with('toast_success', 'Leave entitlement updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with(['toast_error' => 'Failed to update leave entitlement: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveEntitlement $leaveEntitlement)
    {
        $employee = $leaveEntitlement->employee;
        if ($r = $this->guardEntitlementEmployeeForHr($employee)) {
            return $r;
        }

        // Check if there are any leave requests for this entitlement
        if ($leaveEntitlement->leaveRequests()->count() > 0) {
            return back()->with(['toast_error' => 'Cannot delete leave entitlement with existing leave requests.']);
        }

        $leaveEntitlement->delete();

        return redirect()->route('leave-entitlements.index')
            ->with('toast_success', 'Leave entitlement deleted successfully.');
    }

    /**
     * Delete all entitlements for a specific employee and period
     */
    public function deletePeriodEntitlements(Request $request, Employee $employee)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
        ]);

        if ($r = $this->guardEntitlementEmployeeForHr($employee)) {
            return $r;
        }

        DB::beginTransaction();
        try {
            // Get all entitlements for this employee and period
            $entitlements = LeaveEntitlement::where('employee_id', $employee->id)
                ->where('period_start', $request->period_start)
                ->where('period_end', $request->period_end)
                ->get();

            // Check if any entitlements have been used (taken_days > 0)
            // If taken_days > 0, it means the entitlement has been used and cannot be deleted
            $hasUsedEntitlements = false;
            $usedEntitlements = [];

            foreach ($entitlements as $entitlement) {
                if ($entitlement->taken_days > 0) {
                    $hasUsedEntitlements = true;
                    $usedEntitlements[] = [
                        'id' => $entitlement->id,
                        'leave_type' => $entitlement->leaveType->name ?? 'N/A',
                        'taken_days' => $entitlement->taken_days,
                        'entitled_days' => $entitlement->entitled_days,
                        'remaining_days' => $entitlement->remaining_days,
                    ];
                }
            }

            if ($hasUsedEntitlements) {
                DB::rollBack();

                // Build detailed error message
                $usedTypes = collect($usedEntitlements)->pluck('leave_type')->unique()->implode(', ');
                $totalTaken = collect($usedEntitlements)->sum('taken_days');

                return back()->with([
                    'toast_error' => "Cannot delete entitlements that have been used. Found {$totalTaken} taken day(s) in: {$usedTypes}.",
                ]);
            }

            $deletedCount = $entitlements->count();
            LeaveEntitlement::where('employee_id', $employee->id)
                ->where('period_start', $request->period_start)
                ->where('period_end', $request->period_end)
                ->delete();

            DB::commit();

            return redirect()->route('leave.entitlements.employee.show', $employee)
                ->with('toast_success', "Successfully deleted {$deletedCount} entitlement(s) for the selected period.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with(['toast_error' => 'Failed to delete entitlements: '.$e->getMessage()]);
        }
    }

    /**
     * Clear all entitlements for debugging purposes
     */
    public function clearAllEntitlements(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'confirm' => 'required|in:yes',
        ]);

        try {
            if ($request->project_id === 'all') {
                $scope = UserProject::assignmentScope(auth()->user());
                if ($scope === []) {
                    return back()->with(['toast_error' => 'Tidak ada proyek yang di-assign untuk akun Anda.']);
                }

                $employeeIds = Employee::whereHas('administrations', function ($q) use ($scope) {
                    $q->whereIn('project_id', $scope)->where('is_active', 1);
                })->pluck('id');

                $deletedCount = LeaveEntitlement::whereIn('employee_id', $employeeIds)->count();
                LeaveEntitlement::whereIn('employee_id', $employeeIds)->delete();

                return redirect()
                    ->route('leave.entitlements.index', ['project_id' => 'all'])
                    ->with('toast_success', "Entitlements cleared for proyek yang Anda kelola. Deleted {$deletedCount} entitlements.");
            } else {
                // Clear entitlements for specific project employees only
                $project = Project::findOrFail($request->project_id);
                if (! UserProject::canAccessProjectId((int) $project->id)) {
                    return UserProject::redirectAccessDenied(route('leave.entitlements.index'));
                }
                $employees = $this->getProjectEmployees($project);
                $employeeIds = $employees->pluck('id');

                $deletedCount = LeaveEntitlement::whereIn('employee_id', $employeeIds)->count();
                LeaveEntitlement::whereIn('employee_id', $employeeIds)->delete();

                // Reset auto increment to 1
                DB::statement('ALTER TABLE leave_entitlements AUTO_INCREMENT = 1');

                return redirect()
                    ->route('leave.entitlements.index', ['project_id' => $project->id])
                    ->with('toast_success', "Entitlements cleared successfully for {$project->project_code} project. Deleted {$deletedCount} entitlements.");
            }
        } catch (\Exception $e) {
            return back()->with(['toast_error' => 'Failed to clear entitlements: '.$e->getMessage()]);
        }
    }

    /**
     * Generate entitlements for project employees
     */
    public function generateProjectEntitlements(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'year' => 'required|integer|min:2020|max:2030',
        ]);

        $generatedCount = 0;
        $skippedCount = 0;

        if ($request->project_id === 'all') {
            // Generate entitlements for employees in projects assigned to user
            $projects = UserProject::projectsForSelect();
            foreach ($projects as $project) {
                $employees = $this->getProjectEmployees($project);
                foreach ($employees as $employee) {
                    $result = $this->generateEmployeeEntitlements($employee, $request->year);
                    $generatedCount += $result['generated'] ?? 0;
                    $skippedCount += $result['skipped'] ?? 0;
                }
            }

            return redirect()
                ->route('leave.entitlements.index', ['project_id' => 'all'])
                ->with('toast_success', "Entitlements generated successfully. Generated {$generatedCount} new entitlements, skipped {$skippedCount} existing entitlements.");
        } else {
            $request->validate([
                'project_id' => 'exists:projects,id',
            ]);

            $project = Project::findOrFail($request->project_id);
            if (! UserProject::canAccessProjectId((int) $project->id)) {
                return UserProject::redirectAccessDenied(route('leave.entitlements.index'));
            }
            $employees = $this->getProjectEmployees($project);

            foreach ($employees as $employee) {
                $result = $this->generateEmployeeEntitlements($employee, $request->year);
                $generatedCount += $result['generated'] ?? 0;
                $skippedCount += $result['skipped'] ?? 0;
            }

            return redirect()
                ->route('leave.entitlements.index', ['project_id' => $project->id])
                ->with('toast_success', "Entitlements generated successfully. Generated {$generatedCount} new entitlements, skipped {$skippedCount} existing entitlements.");
        }
    }

    /**
     * Generate entitlements for selected project employees (current year)
     */
    public function generateSelectedProjectEntitlements(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
        ]);

        $project = Project::findOrFail($request->project_id);
        if (! UserProject::canAccessProjectId((int) $project->id)) {
            return UserProject::redirectAccessDenied(route('leave.entitlements.index'));
        }
        $currentYear = now()->year;
        $employees = $this->getProjectEmployees($project);

        $generatedCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                $administration = $employee->administrations->where('is_active', 1)->first();
                if (! $administration) {
                    $administration = $employee->administrations->first();
                }
                if (! $administration) {
                    $skippedCount++;

                    continue;
                }

                // Note: Service start DOH calculation is handled in calculateEntitlementDays()
                // which uses getServiceStartDoh() to handle rehire scenarios:
                // - "End of Contract" termination → service continues from first DOH
                // - Other termination reasons → service resets from new hire DOH

                // Get eligible leave types based on project group
                $eligibleCategories = $this->getEligibleLeaveCategories($project);

                foreach ($eligibleCategories as $category) {
                    // Get ALL leave types for this category (not just first one)
                    $leaveTypesInCategory = LeaveType::where('category', $category)
                        ->where('is_active', true)
                        ->orderBy('code')
                        ->get();

                    foreach ($leaveTypesInCategory as $leaveType) {
                        // Special filtering for LSL based on staff level
                        if ($category === 'lsl') {
                            $levelName = $administration->level ? $administration->level->name : '';
                            $isStaff = $this->isStaffLevel($levelName);
                            $hasStaffInName = str_contains($leaveType->name, 'Staff');
                            $hasNonStaffInName = str_contains($leaveType->name, 'Non Staff');

                            if ($isStaff) {
                                // For Staff employees: show only "Cuti Panjang - Staff"
                                if ($hasNonStaffInName) {
                                    continue;
                                }
                            } else {
                                // For Non-Staff employees: show only "Cuti Panjang - Non Staff"
                                if ($hasStaffInName && ! $hasNonStaffInName) {
                                    continue;
                                }
                            }
                        }

                        $entitlementDays = $this->calculateEntitlementDays($leaveType, $employee);

                        // Special validation for LSL in Group 2 projects
                        if ($category === 'lsl' && $project->leave_type === 'roster') {
                            if (! $this->validateLSLForGroup2($employee, $currentYear)) {
                                continue; // Skip LSL if special rules not met
                            }
                        }

                        // For paid and unpaid leave, always create entitlement regardless of calculated days
                        // For other categories, only create if employee is eligible (entitlementDays > 0)
                        $shouldCreate = in_array($category, ['paid', 'unpaid']) || $entitlementDays > 0;

                        if ($shouldCreate) {
                            // Calculate period dates based on project group rules
                            $periodDates = $this->calculatePeriodDates($employee, $currentYear, $leaveType);

                            if ($periodDates === null) {
                                continue;
                            }

                            // Check if entitlement already exists - only create if not exists
                            // Use whereDate for proper date comparison with datetime columns
                            $existingEntitlement = LeaveEntitlement::where('employee_id', $employee->id)
                                ->where('leave_type_id', $leaveType->id)
                                ->whereDate('period_start', $periodDates['start']->format('Y-m-d'))
                                ->whereDate('period_end', $periodDates['end']->format('Y-m-d'))
                                ->first();

                            if (! $existingEntitlement) {
                                $levelName = $this->getEmployeeLevelName($employee);
                                $createAttributes = $this->carryOverService()->buildCreateAttributes(
                                    $employee->id,
                                    $leaveType,
                                    $periodDates['start'],
                                    $periodDates['end'],
                                    0,
                                    $levelName
                                );

                                if (! $this->carryOverService()->supportsCarryOver($leaveType, $levelName)) {
                                    $createAttributes['entitled_days'] = $entitlementDays;
                                }

                                LeaveEntitlement::create([
                                    'employee_id' => $employee->id,
                                    'leave_type_id' => $leaveType->id,
                                    'period_start' => $periodDates['start'],
                                    'period_end' => $periodDates['end'],
                                    'entitled_days' => $createAttributes['entitled_days'],
                                    'deposit_days' => $createAttributes['deposit_days'],
                                    'taken_days' => 0,
                                ]);

                                $generatedCount++;
                            } else {
                                $skippedCount++;
                            }
                        }
                    }
                }
            }

            DB::commit();

            $message = "Entitlements generated successfully for {$project->project_code} project. ";
            $message .= "Generated {$generatedCount} entitlements for ".$employees->count().' employees.';
            if ($skippedCount > 0) {
                $message .= " Skipped {$skippedCount} duplicate entitlements.";
            }

            return redirect()
                ->route('leave.entitlements.index', ['project_id' => $project->id])
                ->with('toast_success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to generate entitlements', [
                'project_id' => $project->id,
                'project_code' => $project->project_code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with(['toast_error' => 'Failed to generate entitlements: '.$e->getMessage()]);
        }
    }

    /**
     * Show individual employee entitlements
     */
    public function showEmployee(Employee $employee)
    {
        if ($r = $this->guardEntitlementEmployeeForHr($employee)) {
            return $r;
        }

        $employee->load([
            'administrations.project',
            'administrations.level',
            'administrations.position',
            'leaveEntitlements.leaveType',
        ]);

        $addEntitlementContext = $this->buildAddEntitlementContext($employee);

        return view('leave-entitlements.show', compact('employee', 'addEntitlementContext'))
            ->with('title', 'Employee Leave Entitlements - '.$employee->fullname);
    }

    /**
     * Show detailed leave calculation breakdown for specific employee and leave type
     */
    public function showLeaveCalculationDetails(Request $request, Employee $employee)
    {
        // Check if user is accessing their own data or has admin permission
        $user = auth()->user();
        $isPersonalUser = $user->can('personal.leave.view-entitlements') && ! $user->can('leave-entitlements.show');

        if ($isPersonalUser && $user->employee_id !== $employee->id) {
            return back()->with(['toast_error' => 'You can only view your own leave calculation details.']);
        }

        if ($user->can('leave-entitlements.show') && ! UserProject::canViewEmployee($employee)) {
            return UserProject::redirectAccessDenied();
        }

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after:period_start',
        ]);

        $leaveTypeId = $request->leave_type_id;
        $periodStart = $request->period_start;
        $periodEnd = $request->period_end;

        // Get leave calculation details using the model method
        $calculationDetails = LeaveEntitlement::getEmployeeLeaveDetails(
            $employee->id,
            $leaveTypeId,
            $periodStart,
            $periodEnd
        );

        if (! $calculationDetails) {
            return back()->with(['toast_error' => 'No leave entitlement found for this employee and leave type.']);
        }

        // Load additional data for the view
        $employee->load(['administrations.project', 'administrations.level']);
        $leaveType = \App\Models\LeaveType::findOrFail($leaveTypeId);

        if (! $periodStart || ! $periodEnd) {
            $periodStart = $calculationDetails['entitlement_period']['start'] ?? null;
            $periodEnd = $calculationDetails['entitlement_period']['end'] ?? null;
        }

        return view('leave-entitlements.calculation-details', compact(
            'employee',
            'leaveType',
            'calculationDetails',
            'periodStart',
            'periodEnd'
        ))->with('title', 'Leave Calculation Details - '.$employee->fullname);
    }

    /**
     * Get leave calculation details via AJAX
     */
    public function getLeaveCalculationDetailsAjax(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after:period_start',
        ]);

        $empForScope = Employee::find($request->employee_id);
        if ($empForScope && auth()->user() instanceof User && auth()->user()->can('leave-entitlements.show') && ! UserProject::canViewEmployee($empForScope)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        try {
            $calculationDetails = LeaveEntitlement::getEmployeeLeaveDetails(
                $request->employee_id,
                $request->leave_type_id,
                $request->period_start,
                $request->period_end
            );

            if (! $calculationDetails) {
                return response()->json([
                    'success' => false,
                    'message' => 'No leave entitlement found for this employee and leave type.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $calculationDetails,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get leave calculation details: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Edit individual employee entitlements
     */
    public function editEmployee(Employee $employee, Request $request)
    {
        if ($r = $this->guardEntitlementEmployeeForHr($employee)) {
            return $r;
        }

        // Reload employee with fresh relationships
        $employee->refresh();

        $employee->load([
            'administrations.project',
            'administrations.level',
            'administrations.position',
            'leaveEntitlements' => function ($query) {
                $query->with('leaveType');
            },
        ]);

        $leaveTypes = LeaveType::where('is_active', true)->get();

        // Get employee business rules info
        $businessRules = $this->getEmployeeBusinessRules($employee);

        if (! $businessRules) {
            return back()->with(['toast_error' => 'Unable to load employee business rules.']);
        }

        // Check if specific period is provided in query parameter
        $periodDates = null;
        $currentYear = now()->year;
        $isEditMode = false;
        $entitlementScope = $request->query('scope', 'annual');
        $addEntitlementContext = $this->buildAddEntitlementContext($employee);

        if ($request->has('period_start') && $request->has('period_end')) {
            // Edit mode: Use provided period dates - parse and ensure they are start/end of day
            $periodDates = [
                'start' => Carbon::parse($request->period_start)->startOfDay(),
                'end' => Carbon::parse($request->period_end)->endOfDay(),
            ];
            $currentYear = $periodDates['start']->year;
            $isEditMode = true;

            $periodPresenter = \App\Support\LeaveEntitlementPeriodPresenter::make(
                $periodDates['start'],
                $periodDates['end']
            );
            $entitlementScope = $periodPresenter->isLsl ? 'lsl' : 'annual';
        } else {
            if (! in_array($entitlementScope, ['annual', 'lsl'], true)) {
                return redirect()
                    ->route('leave.entitlements.employee.show', $employee)
                    ->with('toast_error', 'Jenis entitlement tidak valid. Pilih Tambah Tahunan atau Tambah Cuti Panjang.');
            }

            if ($entitlementScope === 'lsl') {
                if (! $addEntitlementContext['can_add_lsl']) {
                    return redirect()
                        ->route('leave.entitlements.employee.show', $employee)
                        ->with('toast_error', $addEntitlementContext['lsl_blocked_reason'] ?? 'Cuti panjang tidak dapat ditambahkan saat ini.');
                }

                $lslPeriod = $addEntitlementContext['lsl_period'];
                $periodDates = [
                    'start' => $lslPeriod['start']->copy()->startOfDay(),
                    'end' => $lslPeriod['end']->copy()->endOfDay(),
                ];
            } else {
                if (! $addEntitlementContext['can_add_annual']) {
                    return redirect()
                        ->route('leave.entitlements.employee.show', $employee)
                        ->with('toast_error', $addEntitlementContext['annual_blocked_reason'] ?? 'Entitlement tahunan tidak dapat ditambahkan saat ini.');
                }

                $periodDates = $this->calculatePeriodDates($employee, $currentYear);
                if ($periodDates === null) {
                    return back()->with(['toast_error' => 'Unable to calculate annual period dates.']);
                }
                $periodDates['start'] = $periodDates['start']->startOfDay();
                $periodDates['end'] = $periodDates['end']->endOfDay();
            }

            $isEditMode = false;
        }

        $businessRules = $this->filterEligibleLeavesByScope($businessRules, $entitlementScope);

        if (empty($businessRules['eligible_leaves'])) {
            return redirect()
                ->route('leave.entitlements.employee.show', $employee)
                ->with('toast_error', 'Tidak ada jenis cuti yang eligible untuk ditambahkan.');
        }

        $lslPeriodDatesByLeaveTypeId = $this->buildLSLPeriodDatesMap($employee, $currentYear);
        $this->applyCarryOverToEligibleLeaves($businessRules, $employee, $periodDates, $lslPeriodDatesByLeaveTypeId);

        $scopeTitle = $entitlementScope === 'lsl' ? 'Cuti Panjang' : 'Tahunan';

        return view('leave-entitlements.edit', compact(
            'employee',
            'leaveTypes',
            'businessRules',
            'periodDates',
            'lslPeriodDatesByLeaveTypeId',
            'currentYear',
            'isEditMode',
            'entitlementScope',
            'addEntitlementContext',
            'scopeTitle'
        ))->with('title', ($isEditMode ? 'Edit' : 'Tambah').' Hak Cuti '.$scopeTitle.' - '.$employee->fullname);
    }

    /**
     * Update individual employee entitlements
     */
    public function updateEmployee(Request $request, Employee $employee)
    {
        if ($r = $this->guardEntitlementEmployeeForHr($employee)) {
            return $r;
        }

        $request->validate([
            'entitlements' => 'required|array',
            'entitlements.*.leave_type_id' => 'required|exists:leave_types,id',
            'entitlements.*.entitled_days' => 'required|integer|min:0',
            'entitlements.*.period_start' => 'nullable|date',
            'entitlements.*.period_end' => 'nullable|date|after:entitlements.*.period_start',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after:period_start',
            'entitlement_scope' => 'nullable|in:annual,lsl',
        ]);

        DB::beginTransaction();
        try {
            // Get period dates from request (form level) or from first entitlement, or calculate default
            $periodStart = null;
            $periodEnd = null;

            if ($request->has('period_start') && $request->has('period_end')) {
                // Use form-level period dates (from hidden fields)
                $periodStart = Carbon::parse($request->period_start);
                $periodEnd = Carbon::parse($request->period_end);
            } elseif (! empty($request->entitlements[0]['period_start']) && ! empty($request->entitlements[0]['period_end'])) {
                // Use period dates from first entitlement (backward compatibility)
                $periodStart = Carbon::parse($request->entitlements[0]['period_start']);
                $periodEnd = Carbon::parse($request->entitlements[0]['period_end']);
            } else {
                // Calculate default period dates based on project group and DOH
                $currentYear = now()->year;
                $periodDates = $this->calculatePeriodDates($employee, $currentYear);
                $periodStart = $periodDates['start'];
                $periodEnd = $periodDates['end'];
            }

            foreach ($request->entitlements as $entitlementData) {
                $leaveType = LeaveType::findOrFail($entitlementData['leave_type_id']);

                if ($request->filled('entitlement_scope')) {
                    $scope = $request->entitlement_scope;
                    $annualCategories = ['annual', 'paid', 'unpaid'];
                    if ($scope === 'lsl' && $leaveType->category !== 'lsl') {
                        continue;
                    }
                    if ($scope === 'annual' && ! in_array($leaveType->category, $annualCategories, true)) {
                        continue;
                    }
                }

                $entitledDays = (int) $entitlementData['entitled_days'];

                // Use the determined period dates (from form level or default)
                // Override only if specific period is provided in entitlement data
                $entitlementPeriodStart = $periodStart;
                $entitlementPeriodEnd = $periodEnd;

                if ($leaveType->category === 'lsl') {
                    if ($request->has('period_start') && $request->has('period_end')) {
                        $refStart = Carbon::parse($request->period_start);
                        $refEnd = Carbon::parse($request->period_end);
                        $referenceDate = $refStart->copy()->addDays((int) floor($refStart->diffInDays($refEnd) / 2));
                    } else {
                        $referenceDate = $this->resolveReferenceDateForYear(now()->year);
                    }

                    $lslPeriod = $this->calculateLSLPeriodDatesForEmployee(
                        $employee,
                        $leaveType,
                        $referenceDate
                    );

                    if ($lslPeriod !== null) {
                        $entitlementPeriodStart = $lslPeriod['start'];
                        $entitlementPeriodEnd = $lslPeriod['end'];
                    }
                } elseif (! empty($entitlementData['period_start']) && ! empty($entitlementData['period_end'])) {
                    $entitlementPeriodStart = Carbon::parse($entitlementData['period_start']);
                    $entitlementPeriodEnd = Carbon::parse($entitlementData['period_end']);
                }

                // Find existing entitlement with exact same combination
                $existingEntitlement = LeaveEntitlement::where('employee_id', $employee->id)
                    ->where('leave_type_id', $entitlementData['leave_type_id'])
                    ->where('period_start', $entitlementPeriodStart->format('Y-m-d'))
                    ->where('period_end', $entitlementPeriodEnd->format('Y-m-d'))
                    ->first();

                $takenDays = $existingEntitlement ? $existingEntitlement->taken_days : 0;

                // Manual add/edit: use entitled_days from form. Carry over applies on generate only.
                LeaveEntitlement::updateOrCreate([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $entitlementData['leave_type_id'],
                    'period_start' => $entitlementPeriodStart->format('Y-m-d'),
                    'period_end' => $entitlementPeriodEnd->format('Y-m-d'),
                ], [
                    'entitled_days' => $entitledDays,
                    'deposit_days' => $leaveType->getDepositDays(),
                    'taken_days' => $takenDays,
                ]);
            }

            DB::commit();

            // Redirect back to show page with period parameter if available
            $redirectUrl = route('leave.entitlements.employee.show', $employee);
            if ($periodStart && $periodEnd) {
                $periodKey = $periodStart->format('Y-m-d').'-'.$periodEnd->format('Y-m-d');
                $redirectUrl .= '?period='.urlencode($periodKey);
            }

            return redirect($redirectUrl)
                ->with('toast_success', 'Employee entitlements updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with(['toast_error' => 'Failed to update employee entitlements: '.$e->getMessage()]);
        }
    }

    /**
     * Get employees for a specific project
     */
    private function getProjectEmployees($project)
    {
        return Employee::whereHas('administrations', function ($q) use ($project) {
            $q->where('project_id', $project->id)
                ->where('is_active', true);
        })
            ->with([
                // Load ALL administrations (including inactive) for service start DOH calculation
                'administrations' => function ($q) use ($project) {
                    $q->where('project_id', $project->id)
                        ->orderBy('doh', 'asc');
                },
                'administrations.level',
                'leaveEntitlements.leaveType' => function ($q) {
                    $q->where('is_active', true);
                },
            ])
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->where('administrations.project_id', $project->id)
            ->where('administrations.is_active', true)
            ->orderBy('administrations.nik', 'asc')
            ->select('employees.*')
            ->get();
    }

    /**
     * Generate entitlements for individual employee
     * Returns array with 'generated' and 'skipped' counts
     */
    private function generateEmployeeEntitlements($employee, $year)
    {
        $administration = $employee->administrations->where('is_active', 1)->first();
        if (! $administration) {
            $administration = $employee->administrations->first();
        }
        $project = $administration->project;

        // Get eligible leave types based on project group
        $eligibleCategories = $this->getEligibleLeaveCategories($project);

        $generated = 0;
        $skipped = 0;

        foreach ($eligibleCategories as $category) {
            // Get ALL leave types for this category (not just first one)
            $leaveTypesInCategory = LeaveType::where('category', $category)
                ->where('is_active', true)
                ->orderBy('code')
                ->get();

            foreach ($leaveTypesInCategory as $leaveType) {
                // Special filtering for LSL based on staff level
                if ($category === 'lsl') {
                    $levelName = $administration->level ? $administration->level->name : '';
                    $isStaff = $this->isStaffLevel($levelName);
                    $hasStaffInName = str_contains($leaveType->name, 'Staff');
                    $hasNonStaffInName = str_contains($leaveType->name, 'Non Staff');

                    if ($isStaff) {
                        // For Staff employees: show only "Cuti Panjang - Staff"
                        if ($hasNonStaffInName) {
                            continue;
                        }
                    } else {
                        // For Non-Staff employees: show only "Cuti Panjang - Non Staff"
                        if ($hasStaffInName && ! $hasNonStaffInName) {
                            continue;
                        }
                    }
                }

                $entitlementDays = $this->calculateEntitlementDays($leaveType, $employee);

                // Special validation for LSL in Group 2 projects
                if ($category === 'lsl' && $project->leave_type === 'roster') {
                    if (! $this->validateLSLForGroup2($employee, $year)) {
                        continue; // Skip LSL if special rules not met
                    }
                }

                // For paid and unpaid leave, always create entitlement regardless of calculated days
                // For other categories, only create if employee is eligible (entitlementDays > 0)
                $shouldCreate = in_array($leaveType->category, ['paid', 'unpaid']) || $entitlementDays > 0;

                if ($shouldCreate) {
                    // Calculate period dates based on project group rules
                    $periodDates = $this->calculatePeriodDates($employee, $year, $leaveType);

                    if ($periodDates === null) {
                        continue;
                    }

                    // Check if entitlement already exists - only create if not exists
                    // Use whereDate for proper date comparison with datetime columns
                    $existingEntitlement = LeaveEntitlement::where('employee_id', $employee->id)
                        ->where('leave_type_id', $leaveType->id)
                        ->whereDate('period_start', $periodDates['start']->format('Y-m-d'))
                        ->whereDate('period_end', $periodDates['end']->format('Y-m-d'))
                        ->first();

                    if (! $existingEntitlement) {
                        $levelName = $this->getEmployeeLevelName($employee);
                        $createAttributes = $this->carryOverService()->buildCreateAttributes(
                            $employee->id,
                            $leaveType,
                            $periodDates['start'],
                            $periodDates['end'],
                            0,
                            $levelName
                        );

                        if (! $this->carryOverService()->supportsCarryOver($leaveType, $levelName)) {
                            $createAttributes['entitled_days'] = $entitlementDays;
                        }

                        LeaveEntitlement::create([
                            'employee_id' => $employee->id,
                            'leave_type_id' => $leaveType->id,
                            'period_start' => $periodDates['start'],
                            'period_end' => $periodDates['end'],
                            'entitled_days' => $createAttributes['entitled_days'],
                            'deposit_days' => $createAttributes['deposit_days'],
                            'taken_days' => 0,
                        ]);
                        $generated++;
                    } else {
                        $skipped++;
                    }
                }
            }
        }

        return [
            'generated' => $generated,
            'skipped' => $skipped,
        ];
    }

    /**
     * Get eligible leave categories based on project type
     */
    private function getEligibleLeaveCategories($project)
    {
        if ($project->leave_type === 'roster') {
            // Group 2: 017C, 022C - paid, unpaid, lsl (annual di-skip untuk roster)
            return ['paid', 'unpaid', 'lsl'];
        } else {
            // Group 1: 000H, 001H, APS, 021C, 025C - annual, paid, unpaid, lsl
            return ['annual', 'paid', 'unpaid', 'lsl'];
        }
    }

    /**
     * Get service start DOH based on termination reason logic:
     * - If termination_reason = "end of contract" → use first DOH (continuity)
     * - If termination_reason != "end of contract" → use DOH after termination (reset)
     *
     * Logic:
     * 1. Start with the earliest DOH
     * 2. If any termination is NOT "end of contract", reset to the next DOH after that termination
     * 3. If all terminations are "end of contract", keep using the first DOH
     */
    private function getServiceStartDoh($employee)
    {
        $administration = $this->getActiveAdministration($employee);

        if (! $administration) {
            return null;
        }

        $startDoh = $this->yearsOfServiceCalculator()->getServiceStartDoh(
            $administration,
            $employee->administrations
        );

        return $startDoh?->format('Y-m-d');
    }

    /**
     * Calculate period start and end dates based on project group and DOH.
     * LSL uses multi-year cycles from eligible_after_years on the leave type.
     *
     * @return array{start: Carbon, end: Carbon}|null
     */
    private function calculatePeriodDates($employee, $year, ?LeaveType $leaveType = null)
    {
        if ($leaveType !== null && $leaveType->category === 'lsl') {
            return $this->calculateLSLPeriodDatesForEmployee(
                $employee,
                $leaveType,
                $this->resolveReferenceDateForYear($year)
            );
        }

        $administration = $this->getActiveAdministration($employee);
        if (! $administration) {
            return null;
        }

        $project = $administration->project;
        $serviceStartDoh = $this->getServiceStartDoh($employee);
        $doh = $serviceStartDoh ? Carbon::parse($serviceStartDoh) : Carbon::parse($administration->doh);

        if ($project->leave_type === 'roster') {
            // Group 2 (Roster): Calendar year (1 Jan - 31 Dec)
            return [
                'start' => Carbon::create($year, 1, 1),
                'end' => Carbon::create($year, 12, 31),
            ];
        }

        // Group 1 (Non-roster): DOH-based period
        $periodStart = Carbon::create($year, $doh->month, $doh->day);

        if ($periodStart->isFuture()) {
            $periodStart = Carbon::create($year - 1, $doh->month, $doh->day);
        }

        $periodEnd = $periodStart->copy()->addYear()->subDay();

        return [
            'start' => $periodStart,
            'end' => $periodEnd,
        ];
    }

    private function yearsOfServiceCalculator(): AdministrationYearsOfServiceCalculator
    {
        return app(AdministrationYearsOfServiceCalculator::class);
    }

    private function getActiveAdministration($employee)
    {
        $administration = $employee->administrations->where('is_active', 1)->first();

        if (! $administration) {
            $administration = $employee->administrations->first();
        }

        return $administration;
    }

    private function resolveReferenceDateForYear(int $year): Carbon
    {
        if ($year === now()->year) {
            return now()->copy()->startOfDay();
        }

        return Carbon::create($year, 12, 31)->startOfDay();
    }

    /**
     * @return array{start: Carbon, end: Carbon}|null
     */
    private function calculateLSLPeriodDatesForEmployee(
        $employee,
        LeaveType $leaveType,
        ?Carbon $referenceDate = null
    ): ?array {
        $administration = $this->getActiveAdministration($employee);

        if (! $administration || ! $administration->doh) {
            return null;
        }

        return $this->yearsOfServiceCalculator()->calculateLSLPeriodDates(
            $administration,
            $employee->administrations,
            (int) $leaveType->eligible_after_years,
            $referenceDate
        );
    }

    /**
     * @return array<int, array{start: Carbon, end: Carbon}>
     */
    private function buildLSLPeriodDatesMap($employee, int $year): array
    {
        $referenceDate = $this->resolveReferenceDateForYear($year);
        $map = [];

        foreach (LeaveType::where('category', 'lsl')->where('is_active', true)->get() as $leaveType) {
            $period = $this->calculateLSLPeriodDatesForEmployee($employee, $leaveType, $referenceDate);

            if ($period !== null) {
                $map[$leaveType->id] = $period;
            }
        }

        return $map;
    }

    /**
     * @return array{
     *     annual_period: ?array{start: Carbon, end: Carbon},
     *     lsl_period: ?array{start: Carbon, end: Carbon},
     *     can_add_annual: bool,
     *     can_add_lsl: bool,
     *     annual_blocked_reason: ?string,
     *     lsl_blocked_reason: ?string
     * }
     */
    private function buildAddEntitlementContext(Employee $employee): array
    {
        $currentYear = now()->year;
        $annualPeriod = $this->calculatePeriodDates($employee, $currentYear);
        $lslPeriodMap = $this->buildLSLPeriodDatesMap($employee, $currentYear);
        $lslPeriod = collect($lslPeriodMap)->first();

        $canAddAnnual = false;
        $canAddLsl = false;
        $annualBlockedReason = null;
        $lslBlockedReason = null;

        if ($annualPeriod !== null) {
            $annualActive = $this->isPeriodCurrentlyActive($annualPeriod['start'], $annualPeriod['end']);
            $hasAnnualEntitlements = $this->hasEntitlementsForPeriod(
                $employee,
                $annualPeriod['start'],
                $annualPeriod['end'],
                ['annual', 'paid', 'unpaid']
            );

            $canAddAnnual = ! ($annualActive && $hasAnnualEntitlements);

            if ($annualActive && $hasAnnualEntitlements) {
                $annualBlockedReason = 'Periode tahunan aktif.';
            }
        } else {
            $annualBlockedReason = 'Periode tahunan tidak dapat dihitung (periksa data DOH karyawan).';
        }

        if ($lslPeriod !== null) {
            $lslActive = $this->isPeriodCurrentlyActive($lslPeriod['start'], $lslPeriod['end']);
            $hasLslEntitlements = $this->hasEntitlementsForPeriod(
                $employee,
                $lslPeriod['start'],
                $lslPeriod['end'],
                ['lsl']
            );

            $canAddLsl = ! ($lslActive && $hasLslEntitlements);

            if ($lslActive && $hasLslEntitlements) {
                $lslBlockedReason = 'Periode cuti panjang aktif.';
            }
        } else {
            $lslBlockedReason = 'Belum eligible cuti panjang.';
        }

        return [
            'annual_period' => $annualPeriod,
            'lsl_period' => $lslPeriod,
            'can_add_annual' => $canAddAnnual,
            'can_add_lsl' => $canAddLsl,
            'annual_blocked_reason' => $annualBlockedReason,
            'lsl_blocked_reason' => $lslBlockedReason,
        ];
    }

    private function isPeriodCurrentlyActive(Carbon $start, Carbon $end): bool
    {
        return now()->startOfDay()->between($start->copy()->startOfDay(), $end->copy()->endOfDay());
    }

    /**
     * @param  array<int, string>  $categories
     */
    private function hasEntitlementsForPeriod(Employee $employee, Carbon $start, Carbon $end, array $categories): bool
    {
        return LeaveEntitlement::query()
            ->where('employee_id', $employee->id)
            ->whereDate('period_start', $start->format('Y-m-d'))
            ->whereDate('period_end', $end->format('Y-m-d'))
            ->whereHas('leaveType', fn ($query) => $query->whereIn('category', $categories))
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $businessRules
     * @return array<string, mixed>
     */
    private function filterEligibleLeavesByScope(array $businessRules, string $scope): array
    {
        $annualCategories = ['annual', 'paid', 'unpaid'];

        $businessRules['eligible_leaves'] = collect($businessRules['eligible_leaves'] ?? [])
            ->filter(function (array $leave) use ($scope, $annualCategories) {
                if ($scope === 'lsl') {
                    return ($leave['category'] ?? '') === 'lsl';
                }

                return in_array($leave['category'] ?? '', $annualCategories, true);
            })
            ->values()
            ->all();

        return $businessRules;
    }

    /**
     * Validate business rules for manual entitlement assignment
     */
    private function validateEntitlementAssignment($employee, $leaveType, $entitledDays)
    {
        $administration = $employee->administrations->where('is_active', 1)->first();
        if (! $administration) {
            $administration = $employee->administrations->first();
        }
        $project = $administration->project;
        $level = $administration->level;

        // Calculate months of service from service start DOH
        $serviceStartDoh = $this->getServiceStartDoh($employee);
        $doh = $serviceStartDoh ? \Carbon\Carbon::parse($serviceStartDoh) : \Carbon\Carbon::parse($administration->doh);
        $monthsOfService = $doh->diffInMonths(now());
        $isStaff = $this->isStaffLevel($level ? $level->name : '');

        $errors = [];

        switch ($leaveType->category) {
            case 'annual':
                // Only for Group 1 projects
                if ($project->leave_type !== 'non_roster') {
                    $errors[] = 'Annual leave is only available for Group 1 projects (000H, 001H, APS, 021C, 025C)';
                }

                // Must have 12+ months service
                if ($monthsOfService < 12) {
                    $errors[] = 'Employee must have at least 12 months of service for annual leave';
                }
                break;

            case 'periodic':
                // Only for Group 2 projects
                if ($project->leave_type !== 'roster') {
                    $errors[] = 'Periodic leave is only available for Group 2 projects (017C, 022C)';
                }
                break;

            case 'lsl':
                // Check service requirement
                $requiredMonths = $isStaff ? 60 : 72;
                if ($monthsOfService < $requiredMonths) {
                    $staffType = $isStaff ? 'staff' : 'non-staff';
                    $errors[] = "Employee must have at least {$requiredMonths} months of service for LSL ({$staffType})";
                }
                break;
        }

        return $errors;
    }

    /**
     * Calculate entitlement days based on leave type and employee
     */
    private function calculateEntitlementDays($leaveType, $employee)
    {
        $administration = $employee->administrations->where('is_active', 1)->first();
        if (! $administration) {
            $administration = $employee->administrations->first();
        }
        $project = $administration->project;
        $level = $administration->level;

        // Calculate months of service from service start DOH
        $serviceStartDoh = $this->getServiceStartDoh($employee);
        $doh = $serviceStartDoh ? \Carbon\Carbon::parse($serviceStartDoh) : \Carbon\Carbon::parse($administration->doh);
        $monthsOfService = $doh->diffInMonths(now());

        // Determine if employee is staff or non-staff based on level
        $isStaff = $this->isStaffLevel($level ? $level->name : '');

        switch ($leaveType->category) {
            case 'annual':
                // Only for Group 1 projects (non_roster)
                if ($project->leave_type !== 'non_roster') {
                    return 0;
                }

                // Annual leave eligibility: 12+ months
                if ($monthsOfService < 12) {
                    return 0;
                }

                return $leaveType->default_days ?? 12;

            case 'periodic':
                // Only for Group 2 projects (roster)
                if ($project->leave_type !== 'roster') {
                    return 0;
                }

                // Periodic leave based on level Roster Cycle
                $levelName = $level ? $level->name : '';

                return $this->calculatePeriodicDays($levelName);

            case 'lsl':
                $requiredMonths = ((int) $leaveType->eligible_after_years) * 12;
                if ($requiredMonths <= 0 || $monthsOfService < $requiredMonths) {
                    return 0;
                }

                return $leaveType->default_days ?? 50;

            case 'paid':
                // Always available, use default days from leave type
                return $leaveType->default_days ?? 0;

            case 'unpaid':
                // Always available, use default days from leave type
                return $leaveType->default_days ?? 0;

            default:
                return 0;
        }
    }

    /**
     * Determine if level is considered staff
     */
    private function isStaffLevel($levelName)
    {
        $staffLevels = [
            'Director',
            'Manager',
            'Superintendent',
            'Supervisor',
            'Foreman/Officer',
            'Project Manager',
            'SPT',
            'SPV',
            'FM',
        ];

        return in_array($levelName, $staffLevels);
    }

    /**
     * Calculate periodic leave days based on level Roster Cycle
     */
    private function calculatePeriodicDays($levelName)
    {
        $level = \App\Models\Level::where('name', $levelName)->first();

        // Jika level punya work_days, berarti bisa pakai roster
        return $level && $level->hasRosterConfig() ? $level->getOffDays() : 0;
    }

    /**
     * Validate LSL special rules for Group 2 projects
     */
    private function validateLSLForGroup2($employee, $year)
    {
        $administration = $employee->administrations->where('is_active', 1)->first();
        if (! $administration) {
            $administration = $employee->administrations->first();
        }
        $project = $administration->project;

        // Only apply special rules for Group 2 projects
        if ($project->leave_type !== 'roster') {
            return true;
        }

        // Check if LSL has been taken this year
        $existingLSL = LeaveEntitlement::where('employee_id', $employee->id)
            ->whereHas('leaveType', function ($q) {
                $q->where('category', 'lsl');
            })
            ->whereYear('period_start', $year)
            ->where('taken_days', '>', 0)
            ->first();

        if ($existingLSL) {
            return false; // LSL already taken this year
        }

        // For roster projects, LSL uses same requirements as non-roster projects
        // Check standard LSL eligibility (60 months for staff, 72 months for non-staff)
        // Use service start DOH for months calculation
        $serviceStartDoh = $this->getServiceStartDoh($employee);
        $doh = $serviceStartDoh ? Carbon::parse($serviceStartDoh) : Carbon::parse($administration->doh);
        $level = $administration->level;
        $isStaff = $this->isStaffLevel($level ? $level->name : '');

        $monthsOfService = $doh->diffInMonths(now());

        // Standard LSL eligibility: 60 months for staff, 72 months for non-staff
        $requiredMonths = $isStaff ? 60 : 72;
        if ($monthsOfService < $requiredMonths) {
            return false; // Not enough months of service for LSL
        }

        return true;
    }

    /**
     * Apply LSL special rules for Group 2 projects
     */
    private function applyLSLGroup2Rules($employee, $year)
    {
        $administration = $employee->administrations->first();
        $project = $administration->project;

        // Only apply for Group 2 projects
        if ($project->leave_type !== 'roster') {
            return;
        }

        // Reduce periodic leave by 10 days when LSL is taken
        $periodicEntitlement = LeaveEntitlement::where('employee_id', $employee->id)
            ->whereHas('leaveType', function ($q) {
                $q->where('category', 'periodic');
            })
            ->whereYear('period_start', $year)
            ->first();

        if ($periodicEntitlement) {
            $periodicEntitlement->update([
                'taken_days' => $periodicEntitlement->taken_days + 10,
            ]);
            // remaining_days is now calculated via accessor, no need to update manually
        }
    }

    /**
     * Get employee business rules information
     */
    private function getEmployeeBusinessRules($employee)
    {
        $administration = $employee->administrations->where('is_active', 1)->first();
        if (! $administration) {
            $administration = $employee->administrations->first();
        }

        if (! $administration || ! $administration->project) {
            return null;
        }

        $project = $administration->project;
        $level = $administration->level;

        // Calculate months of service from service start DOH
        $serviceStartDoh = $this->getServiceStartDoh($employee);
        $doh = $serviceStartDoh ? Carbon::parse($serviceStartDoh) : Carbon::parse($administration->doh);
        $monthsOfService = $doh->diffInMonths(now());
        $yearsOfService = round($monthsOfService / 12, 1);

        // Determine staff/non-staff
        $isStaff = $this->isStaffLevel($level ? $level->name : '');
        $staffType = $isStaff ? 'Staff' : 'Non-Staff';

        // Determine project group
        $projectGroup = $project->leave_type === 'roster' ? 'Group 2 (Roster-Based)' : 'Group 1 (Regular)';

        // Get eligible leave types with calculated days
        $eligibleLeaves = [];
        $eligibleCategories = $this->getEligibleLeaveCategories($project);

        foreach ($eligibleCategories as $category) {
            // Get ALL leave types for this category
            $leaveTypesInCategory = LeaveType::where('category', $category)
                ->where('is_active', true)
                ->orderBy('code')
                ->get();

            foreach ($leaveTypesInCategory as $leaveType) {
                // Special filtering for LSL based on staff level
                if ($category === 'lsl') {
                    $hasStaffInName = str_contains($leaveType->name, 'Staff');
                    $hasNonStaffInName = str_contains($leaveType->name, 'Non Staff');

                    if ($isStaff) {
                        // For Staff employees: show only "Cuti Panjang - Staff"
                        // Skip if it has "Non Staff" in the name
                        if ($hasNonStaffInName) {
                            continue;
                        }
                    } else {
                        // For Non-Staff employees: show only "Cuti Panjang - Non Staff"
                        // Skip if it only has "Staff" but not "Non Staff"
                        if ($hasStaffInName && ! $hasNonStaffInName) {
                            continue;
                        }
                    }
                }

                $calculatedDays = $this->calculateEntitlementDays($leaveType, $employee);

                // Special validation for LSL in Group 2 projects
                if ($category === 'lsl' && $project->leave_type === 'roster') {
                    if (! $this->validateLSLForGroup2($employee, now()->year)) {
                        continue; // Skip LSL if special rules not met
                    }
                }

                // Paid and unpaid leave are always eligible regardless of calculated days
                $isEligible = in_array($category, ['paid', 'unpaid']) ? true : ($calculatedDays > 0);

                $eligibilityReason = $this->getEligibilityReason($category, $monthsOfService, $isStaff, $project->leave_type);

                // Only add if eligible
                if ($isEligible) {
                    $eligibleLeaves[] = [
                        'id' => $leaveType->id,
                        'name' => $leaveType->name,
                        'code' => $leaveType->code,
                        'category' => $category,
                        'default_days' => $leaveType->default_days,
                        'calculated_days' => $calculatedDays,
                        'is_eligible' => $isEligible,
                        'eligibility_reason' => $eligibilityReason,
                    ];
                }
            }
        }

        // Special notes
        $specialNotes = [];

        if ($project->leave_type === 'roster') {
            $rosterPattern = $this->getRosterPatternForLevel($level->name ?? '');
            $specialNotes[] = "Roster Cycle: {$rosterPattern}";
            // Note: Periodic leave implementation coming soon
        }

        if ($monthsOfService < 12 && $project->leave_type !== 'roster') {
            $specialNotes[] = 'Annual leave will be available after 12 months of service';
        }

        $lslThreshold = $isStaff ? 60 : 72;
        if ($monthsOfService < $lslThreshold) {
            $remainingMonths = $lslThreshold - $monthsOfService;
            $specialNotes[] = "LSL will be available after {$lslThreshold} months of service ({$remainingMonths} months remaining)";
        }

        return [
            'project_code' => $project->project_code,
            'project_name' => $project->project_name,
            'project_group' => $projectGroup,
            'level_name' => $level->name ?? 'N/A',
            'staff_type' => $staffType,
            'doh' => $doh ? $doh->format('d F Y') : 'N/A',
            'months_of_service' => $monthsOfService,
            'years_of_service' => $yearsOfService,
            'eligible_leaves' => $eligibleLeaves,
            'special_notes' => $specialNotes,
        ];
    }

    /**
     * Get eligibility reason for leave category
     */
    private function getEligibilityReason($category, $monthsOfService, $isStaff, $projectType)
    {
        switch ($category) {
            case 'annual':
                if ($projectType === 'roster') {
                    return 'Not available for roster-based projects';
                }
                if ($monthsOfService < 12) {
                    return 'Requires 12 months of service';
                }

                return 'Eligible - 12 days per year after 1 year service';

            case 'periodic':
                if ($projectType !== 'roster') {
                    return 'Only available for roster-based projects';
                }

                return 'Eligible - Based on Roster Cycle';

            case 'lsl':
                $requiredMonths = $isStaff ? 60 : 72;
                if ($monthsOfService < $requiredMonths) {
                    $staffType = $isStaff ? 'staff' : 'non-staff';

                    return "Requires {$requiredMonths} months of service ({$staffType})";
                }

                return 'Eligible - 50 days LSL';

            case 'paid':
                return 'Eligible - Based on specific events';

            case 'unpaid':
                return 'Eligible - Can be requested anytime (no limit)';

            default:
                return 'N/A';
        }
    }

    /**
     * Get Roster Cycle for level
     */
    private function getRosterPatternForLevel($levelName)
    {
        $patterns = [
            'Project Manager' => '6 weeks on / 2 weeks off',
            'PM' => '6 weeks on / 2 weeks off',
            'Manager' => '6 weeks on / 2 weeks off',
            'Superintendent' => '6 weeks on / 2 weeks off',
            'SPT' => '6 weeks on / 2 weeks off',
            'Supervisor' => '8 weeks on / 2 weeks off',
            'SPV' => '8 weeks on / 2 weeks off',
            'Foreman/Officer' => '9 weeks on / 2 weeks off',
            'FM' => '9 weeks on / 2 weeks off',
            'Non Staff-Non Skill' => '10 weeks on / 2 weeks off',
            'NS' => '10 weeks on / 2 weeks off',
        ];

        return $patterns[$levelName] ?? '10 weeks on / 2 weeks off';
    }

    /**
     * Export leave entitlements template
     */
    public function exportTemplate(Request $request)
    {
        $includeData = $request->get('include_data', true);
        $projectId = $request->get('project_id', null);

        if ($projectId && $projectId !== 'all' && ! UserProject::canAccessProjectId((int) $projectId)) {
            abort(403);
        }

        // Build filename with project info if specified
        $filename = 'leave_entitlement_'.($includeData ? 'data_' : 'template_');

        if ($projectId && $projectId !== 'all') {
            $project = Project::find($projectId);
            if ($project) {
                $filename .= str_replace(' ', '_', $project->project_code).'_';
            }
        } elseif ($projectId === 'all') {
            $filename .= 'all_projects_';
        }

        $filename .= date('Y-m-d_His').'.xlsx';

        return Excel::download(
            new LeaveEntitlementExport($includeData, $projectId),
            $filename
        );
    }

    /**
     * Import leave entitlements from Excel
     */
    public function importTemplate(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240', // 10MB max
        ], [
            'file.required' => 'Please select a file to import.',
            'file.mimes' => 'The file must be a file of type: xlsx, xls.',
            'file.max' => 'The file may not be greater than 10MB.',
        ]);

        try {
            $import = new LeaveEntitlementImport;
            Excel::import($import, $request->file('file'));

            $successCount = $import->getSuccessCount();
            $skippedCount = $import->getSkippedCount();
            $errors = $import->getErrors();

            if (empty($errors)) {
                return redirect()->route('leave.entitlements.index')
                    ->with('toast_success', "Successfully imported {$successCount} entitlement records.");
            } else {
                // Format errors for display
                $formattedFailures = collect();
                foreach ($errors as $error) {
                    $formattedFailures->push([
                        'sheet' => 'Leave Entitlements',
                        'row' => $error['row'],
                        'attribute' => 'NIK: '.($error['nik'] ?: 'N/A'),
                        'value' => '',
                        'errors' => implode(', ', $error['errors']),
                    ]);
                }

                $message = "Imported {$successCount} records successfully. Skipped {$skippedCount} records due to validation errors.";

                return back()
                    ->with('failures', $formattedFailures)
                    ->with('toast_warning', $message);
            }
        } catch (ValidationException $e) {
            $failures = collect();
            foreach ($e->failures() as $failure) {
                $failures->push([
                    'sheet' => 'Leave Entitlements',
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'value' => $failure->values()[$failure->attribute()] ?? null,
                    'errors' => implode(', ', $failure->errors()),
                ]);
            }

            return back()->with('failures', $failures);
        } catch (\Throwable $e) {
            Log::error('Leave Entitlement Import Error', [
                'exception' => $e,
            ]);

            $failures = collect([
                [
                    'sheet' => 'Impor',
                    'row' => '-',
                    'attribute' => '-',
                    'value' => null,
                    'errors' => 'Impor tidak dapat diselesaikan karena kesalahan sistem. Periksa file (format .xlsx/.xls), lalu coba lagi. Jika masih gagal, hubungi administrator—detail teknis ada di log.',
                ],
            ]);

            return back()->with('failures', $failures)->with('toast_warning', 'Impor entitlement gagal.');
        }
    }

    private function carryOverService(): LeaveEntitlementCarryOverService
    {
        return app(LeaveEntitlementCarryOverService::class);
    }

    private function getEmployeeLevelName(Employee $employee): ?string
    {
        return $this->getActiveAdministration($employee)?->level?->name;
    }

    /**
     * @param  array<string, mixed>  $businessRules
     * @param  array{start: Carbon, end: Carbon}|null  $periodDates
     * @param  array<int, array{start: Carbon, end: Carbon}>  $lslPeriodDatesByLeaveTypeId
     */
    private function applyCarryOverToEligibleLeaves(
        array &$businessRules,
        Employee $employee,
        ?array $periodDates,
        array $lslPeriodDatesByLeaveTypeId
    ): void {
        if (empty($businessRules['eligible_leaves'])) {
            return;
        }

        $levelName = $this->getEmployeeLevelName($employee);

        foreach ($businessRules['eligible_leaves'] as &$leave) {
            $category = $leave['category'] ?? '';
            if (! in_array($category, ['lsl', 'annual'], true)) {
                continue;
            }

            $leaveTypeId = $leave['id'] ?? null;
            if (! $leaveTypeId) {
                continue;
            }

            $leaveType = LeaveType::find($leaveTypeId);
            if (! $leaveType || ! $this->carryOverService()->supportsCarryOver($leaveType, $levelName)) {
                continue;
            }

            $periodStart = null;
            if ($category === 'lsl') {
                $periodStart = $lslPeriodDatesByLeaveTypeId[$leaveTypeId]['start'] ?? null;
            } elseif ($periodDates !== null) {
                $periodStart = $periodDates['start'];
            }

            if ($periodStart === null) {
                continue;
            }

            $calculation = $this->carryOverService()->calculate(
                $employee->id,
                $leaveType,
                $periodStart,
                $levelName
            );

            $leave['calculated_days'] = $calculation['entitled_days'];
            $leave['carried_over'] = $calculation['carried_over'];
            $leave['base_days'] = $calculation['base_days'];
        }

        unset($leave);
    }

    /**
     * HR: employee must have administration in at least one user-assigned project.
     */
    private function guardEntitlementEmployeeForHr(Employee $employee): ?RedirectResponse
    {
        $user = auth()->user();
        if (! $user instanceof User || ! $user->can('leave-entitlements.show')) {
            return null;
        }
        if (! UserProject::canViewEmployee($employee)) {
            return UserProject::redirectAccessDenied(route('leave.entitlements.index'));
        }

        return null;
    }
}
