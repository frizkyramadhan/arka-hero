<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Project;
use App\Models\Employee;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use App\Models\Administration;
use App\Models\LeaveEntitlement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use App\Exports\LeaveEntitlementExport;
use App\Imports\LeaveEntitlementImport;

class LeaveEntitlementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:leave-entitlements.show')->only('index', 'show', 'data', 'showEmployee', 'getAvailableLeaveTypes', 'getLeaveCalculationDetailsAjax', 'exportTemplate');
        // showLeaveCalculationDetails can be accessed by both admin (leave-entitlements.show) and personal users (personal.leave.view-entitlements)
        // Permission check is handled inside the method - allow either permission
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user->can('leave-entitlements.show') && !$user->can('personal.leave.view-entitlements')) {
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
            $projects = Project::where('project_status', 1)
                ->select('id', 'project_code', 'project_name', 'leave_type')
                ->orderBy('project_code', 'asc')
                ->get();

            $selectedProject = null;
            $showAllProjects = false;

            if ($request->filled('project_id')) {
                if ($request->project_id === 'all') {
                    $showAllProjects = true;
                } else {
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

            if (!$projectId) {
                return response()->json([
                    'draw' => intval($request->get('draw')),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
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
                // Get all employees from all active projects
                $query = $this->getAllProjectsEmployeesQuery();
            } else {
                $project = Project::findOrFail($projectId);
                $query = $this->getProjectEmployeesQuery($project);
            }

            // Get total records count
            $totalRecords = $query->count();

            // Apply search filter
            $searchValue = $request->get('search')['value'] ?? '';
            if (!empty($searchValue)) {
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
                10 => 'actions' // Actions
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
                $query->orderByRaw("CAST(administrations.nik AS UNSIGNED) asc");
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
                if (!$employee) {
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
                    $editUrl = route('leave.entitlements.employee.edit', $administration->employee_id) .
                        '?period_start=' . $latestEntitlement->period_start->format('Y-m-d') .
                        '&period_end=' . $latestEntitlement->period_end->format('Y-m-d');
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
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($request->get('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
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
                }
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
                }
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

        if (!$employeeId) {
            return response()->json(['leaveTypes' => []]);
        }

        $employee = Employee::with(['administrations.project', 'administrations.level'])->find($employeeId);

        $activeAdministration = $employee ? $employee->administrations->where('is_active', 1)->first() : null;
        if (!$activeAdministration && $employee) {
            $activeAdministration = $employee->administrations->first();
        }

        if (!$employee || !$activeAdministration) {
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
                    'calculated_days' => $entitlementDays
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
            'deposit_days' => 'nullable|integer|min:0'
        ]);

        DB::beginTransaction();
        try {
            $employee = Employee::findOrFail($request->employee_id);
            $leaveType = LeaveType::findOrFail($request->leave_type_id);

            // Validate business rules
            $validationErrors = $this->validateEntitlementAssignment($employee, $leaveType, $request->entitled_days);
            if (!empty($validationErrors)) {
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
                'entitled_days' => $request->entitled_days,
                'deposit_days' => $request->deposit_days ?? 0,
                'taken_days' => $request->taken_days ?? 0
            ]);

            DB::commit();

            // Redirect to employee entitlements page instead of root level show
            return redirect()->route('leave.entitlements.employee.show', $employee)
                ->with('toast_success', 'Leave entitlement created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with(['toast_error' => 'Failed to create leave entitlement: ' . $e->getMessage()]);
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
            'deposit_days' => 'nullable|integer|min:0'
        ]);

        DB::beginTransaction();
        try {
            $leaveEntitlement->update([
                'entitled_days' => $request->entitled_days,
                'deposit_days' => $request->deposit_days ?? 0
            ]);

            // remaining_days is now calculated via accessor, no need to recalculate
            $leaveEntitlement->save();

            DB::commit();

            // Redirect to employee entitlements page instead of root level show
            $employee = $leaveEntitlement->employee;
            return redirect()->route('leave.entitlements.employee.show', $employee)
                ->with('toast_success', 'Leave entitlement updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with(['toast_error' => 'Failed to update leave entitlement: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveEntitlement $leaveEntitlement)
    {
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
            'period_end' => 'required|date|after:period_start'
        ]);

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
                        'remaining_days' => $entitlement->remaining_days
                    ];
                }
            }

            if ($hasUsedEntitlements) {
                DB::rollBack();
                
                // Build detailed error message
                $usedTypes = collect($usedEntitlements)->pluck('leave_type')->unique()->implode(', ');
                $totalTaken = collect($usedEntitlements)->sum('taken_days');
                
                return back()->with([
                    'toast_error' => "Cannot delete entitlements that have been used. Found {$totalTaken} taken day(s) in: {$usedTypes}."
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
            return back()->with(['toast_error' => 'Failed to delete entitlements: ' . $e->getMessage()]);
        }
    }




    /**
     * Clear all entitlements for debugging purposes
     */
    public function clearAllEntitlements(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'confirm' => 'required|in:yes'
        ]);

        try {
            if ($request->project_id === 'all') {
                // Clear all entitlements for all projects
                $deletedCount = LeaveEntitlement::count();
                LeaveEntitlement::truncate();

                // Reset auto increment
                DB::statement('ALTER TABLE leave_entitlements AUTO_INCREMENT = 1');

                return redirect()
                    ->route('leave.entitlements.index', ['project_id' => 'all'])
                    ->with('toast_success', "All entitlements cleared successfully. Deleted {$deletedCount} entitlements.");
            } else {
                // Clear entitlements for specific project employees only
                $project = Project::findOrFail($request->project_id);
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
            return back()->with(['toast_error' => 'Failed to clear entitlements: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate entitlements for project employees
     */
    public function generateProjectEntitlements(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'year' => 'required|integer|min:2020|max:2030'
        ]);

        $generatedCount = 0;
        $skippedCount = 0;

        if ($request->project_id === 'all') {
            // Generate entitlements for all employees in all projects
            $projects = Project::where('project_status', 1)->get();
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
                'project_id' => 'exists:projects,id'
            ]);

            $project = Project::findOrFail($request->project_id);
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
            'project_id' => 'required|exists:projects,id'
        ]);

        $project = Project::findOrFail($request->project_id);
        $currentYear = now()->year;
        $employees = $this->getProjectEmployees($project);

        $generatedCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                $administration = $employee->administrations->where('is_active', 1)->first();
                if (!$administration) {
                    $administration = $employee->administrations->first();
                }
                if (!$administration) {
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
                                if ($hasStaffInName && !$hasNonStaffInName) {
                                    continue;
                                }
                            }
                        }

                        $entitlementDays = $this->calculateEntitlementDays($leaveType, $employee);

                        // Special validation for LSL in Group 2 projects
                        if ($category === 'lsl' && $project->leave_type === 'roster') {
                            if (!$this->validateLSLForGroup2($employee, $currentYear)) {
                                continue; // Skip LSL if special rules not met
                            }
                        }

                        // For paid and unpaid leave, always create entitlement regardless of calculated days
                        // For other categories, only create if employee is eligible (entitlementDays > 0)
                        $shouldCreate = in_array($category, ['paid', 'unpaid']) || $entitlementDays > 0;

                        if ($shouldCreate) {
                            // Calculate period dates based on project group rules
                            $periodDates = $this->calculatePeriodDates($employee, $currentYear);

                            // Check if entitlement already exists - only create if not exists
                            // Use whereDate for proper date comparison with datetime columns
                            $existingEntitlement = LeaveEntitlement::where('employee_id', $employee->id)
                                ->where('leave_type_id', $leaveType->id)
                                ->whereDate('period_start', $periodDates['start']->format('Y-m-d'))
                                ->whereDate('period_end', $periodDates['end']->format('Y-m-d'))
                                ->first();

                            if (!$existingEntitlement) {
                                LeaveEntitlement::create([
                                    'employee_id' => $employee->id,
                                    'leave_type_id' => $leaveType->id,
                                    'period_start' => $periodDates['start'],
                                    'period_end' => $periodDates['end'],
                                    'entitled_days' => $entitlementDays,
                                    'deposit_days' => $leaveType->getDepositDays(),
                                    'taken_days' => 0
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
            $message .= "Generated {$generatedCount} entitlements for " . $employees->count() . " employees.";
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
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with(['toast_error' => 'Failed to generate entitlements: ' . $e->getMessage()]);
        }
    }


    /**
     * Show individual employee entitlements
     */
    public function showEmployee(Employee $employee)
    {
        $employee->load([
            'administrations.project',
            'administrations.level',
            'administrations.position',
            'leaveEntitlements.leaveType'
        ]);

        return view('leave-entitlements.show', compact('employee'))
            ->with('title', 'Employee Leave Entitlements - ' . $employee->fullname);
    }

    /**
     * Show detailed leave calculation breakdown for specific employee and leave type
     */
    public function showLeaveCalculationDetails(Request $request, Employee $employee)
    {
        // Check if user is accessing their own data or has admin permission
        $user = auth()->user();
        $isPersonalUser = $user->can('personal.leave.view-entitlements') && !$user->can('leave-entitlements.show');

        if ($isPersonalUser && $user->employee_id !== $employee->id) {
            return back()->with(['toast_error' => 'You can only view your own leave calculation details.']);
        }

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after:period_start'
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

        if (!$calculationDetails) {
            return back()->with(['toast_error' => 'No leave entitlement found for this employee and leave type.']);
        }

        // Load additional data for the view
        $employee->load(['administrations.project', 'administrations.level']);
        $leaveType = \App\Models\LeaveType::findOrFail($leaveTypeId);

        return view('leave-entitlements.calculation-details', compact(
            'employee',
            'leaveType',
            'calculationDetails'
        ))->with('title', 'Leave Calculation Details - ' . $employee->fullname);
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
            'period_end' => 'nullable|date|after:period_start'
        ]);

        try {
            $calculationDetails = LeaveEntitlement::getEmployeeLeaveDetails(
                $request->employee_id,
                $request->leave_type_id,
                $request->period_start,
                $request->period_end
            );

            if (!$calculationDetails) {
                return response()->json([
                    'success' => false,
                    'message' => 'No leave entitlement found for this employee and leave type.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $calculationDetails
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get leave calculation details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Edit individual employee entitlements
     */
    public function editEmployee(Employee $employee, Request $request)
    {
        // Reload employee with fresh relationships
        $employee->refresh();

        $employee->load([
            'administrations.project',
            'administrations.level',
            'administrations.position',
            'leaveEntitlements' => function ($query) {
                $query->with('leaveType');
            }
        ]);

        $leaveTypes = LeaveType::where('is_active', true)->get();

        // Get employee business rules info
        $businessRules = $this->getEmployeeBusinessRules($employee);

        // Check if specific period is provided in query parameter
        $periodDates = null;
        $currentYear = now()->year;
        $isEditMode = false; // Flag to distinguish edit vs add mode

        if ($request->has('period_start') && $request->has('period_end')) {
            // Edit mode: Use provided period dates - parse and ensure they are start/end of day
            $periodDates = [
                'start' => Carbon::parse($request->period_start)->startOfDay(),
                'end' => Carbon::parse($request->period_end)->endOfDay()
            ];
            $currentYear = $periodDates['start']->year;
            $isEditMode = true; // This is edit mode - use existing entitlement values
        } else {
            // Add mode: Default to current year period (for "Add Entitlements" button)
            $periodDates = $this->calculatePeriodDates($employee, $currentYear);
            // Ensure dates are start/end of day
            $periodDates['start'] = $periodDates['start']->startOfDay();
            $periodDates['end'] = $periodDates['end']->endOfDay();
            $isEditMode = false; // This is add mode - use default calculated values
        }

        return view('leave-entitlements.edit', compact(
            'employee',
            'leaveTypes',
            'businessRules',
            'periodDates',
            'currentYear',
            'isEditMode'
        ))->with('title', ($isEditMode ? 'Edit' : 'Add') . ' Employee Leave Entitlements - ' . $employee->fullname);
    }



    /**
     * Update individual employee entitlements
     */
    public function updateEmployee(Request $request, Employee $employee)
    {
        $request->validate([
            'entitlements' => 'required|array',
            'entitlements.*.leave_type_id' => 'required|exists:leave_types,id',
            'entitlements.*.entitled_days' => 'required|integer|min:0',
            'entitlements.*.period_start' => 'nullable|date',
            'entitlements.*.period_end' => 'nullable|date|after:entitlements.*.period_start',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after:period_start'
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
            } elseif (!empty($request->entitlements[0]['period_start']) && !empty($request->entitlements[0]['period_end'])) {
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
                $entitledDays = (int) $entitlementData['entitled_days'];

                // Use the determined period dates (from form level or default)
                // Override only if specific period is provided in entitlement data
                $entitlementPeriodStart = $periodStart;
                $entitlementPeriodEnd = $periodEnd;

                if (!empty($entitlementData['period_start']) && !empty($entitlementData['period_end'])) {
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
                $remainingDays = max(0, $entitledDays - $takenDays);

                // Use updateOrCreate with exact period matching
                // This ensures CREATE behavior for different periods, UPDATE for same periods
                LeaveEntitlement::updateOrCreate([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $entitlementData['leave_type_id'],
                    'period_start' => $entitlementPeriodStart->format('Y-m-d'),
                    'period_end' => $entitlementPeriodEnd->format('Y-m-d')
                ], [
                    'entitled_days' => $entitledDays,
                    'deposit_days' => $leaveType->getDepositDays(),
                    'taken_days' => $takenDays
                ]);
            }

            DB::commit();

            // Redirect back to show page with period parameter if available
            $redirectUrl = route('leave.entitlements.employee.show', $employee);
            if ($periodStart && $periodEnd) {
                $periodKey = $periodStart->format('Y-m-d') . '-' . $periodEnd->format('Y-m-d');
                $redirectUrl .= '?period=' . urlencode($periodKey);
            }

            return redirect($redirectUrl)
                ->with('toast_success', 'Employee entitlements updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with(['toast_error' => 'Failed to update employee entitlements: ' . $e->getMessage()]);
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
                }
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
        if (!$administration) {
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
                        if ($hasStaffInName && !$hasNonStaffInName) {
                            continue;
                        }
                    }
                }

                $entitlementDays = $this->calculateEntitlementDays($leaveType, $employee);

                // Special validation for LSL in Group 2 projects
                if ($category === 'lsl' && $project->leave_type === 'roster') {
                    if (!$this->validateLSLForGroup2($employee, $year)) {
                        continue; // Skip LSL if special rules not met
                    }
                }

                // For paid and unpaid leave, always create entitlement regardless of calculated days
                // For other categories, only create if employee is eligible (entitlementDays > 0)
                $shouldCreate = in_array($leaveType->category, ['paid', 'unpaid']) || $entitlementDays > 0;

                if ($shouldCreate) {
                    // Calculate period dates based on project group rules
                    $periodDates = $this->calculatePeriodDates($employee, $year);

                    // Check if entitlement already exists - only create if not exists
                    // Use whereDate for proper date comparison with datetime columns
                    $existingEntitlement = LeaveEntitlement::where('employee_id', $employee->id)
                        ->where('leave_type_id', $leaveType->id)
                        ->whereDate('period_start', $periodDates['start']->format('Y-m-d'))
                        ->whereDate('period_end', $periodDates['end']->format('Y-m-d'))
                        ->first();

                    if (!$existingEntitlement) {
                        LeaveEntitlement::create([
                            'employee_id' => $employee->id,
                            'leave_type_id' => $leaveType->id,
                            'period_start' => $periodDates['start'],
                            'period_end' => $periodDates['end'],
                            'entitled_days' => $entitlementDays,
                            'deposit_days' => $leaveType->getDepositDays(),
                            'taken_days' => 0
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
            'skipped' => $skipped
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
        $allAdministrations = $employee->administrations
            ->whereNotNull('doh')
            ->sortBy('doh')
            ->values();

        if ($allAdministrations->count() === 0) {
            return null;
        }

        // Start with first DOH (earliest)
        $serviceStartDoh = $allAdministrations->first()->doh;

        // Check each administration in chronological order
        foreach ($allAdministrations as $admin) {
            // Check if this administration has termination
            if ($admin->termination_date && $admin->termination_reason) {
                // Normalize termination reason (case insensitive)
                $terminationReason = strtolower(trim($admin->termination_reason));

                // If termination reason is NOT "end of contract", reset calculation from next DOH
                if ($terminationReason !== 'end of contract') {
                    // Find next administration after this termination
                    $nextAdmin = $allAdministrations->filter(function ($next) use ($admin) {
                        return $next->doh && $admin->termination_date && $next->doh > $admin->termination_date;
                    })->first();

                    if ($nextAdmin) {
                        // Reset service start to the next DOH after non-contract termination
                        $serviceStartDoh = $nextAdmin->doh;
                    }
                    // If no next administration found, service start remains at last reset point
                }
                // If termination reason IS "end of contract", continue using current service start (no reset)
            }
        }

        return $serviceStartDoh;
    }

    /**
     * Calculate period start and end dates based on project group and DOH
     */
    private function calculatePeriodDates($employee, $year)
    {
        $administration = $employee->administrations->where('is_active', 1)->first();
        if (!$administration) {
            $administration = $employee->administrations->first();
        }
        $project = $administration->project;

        // Use service start DOH for period calculation
        $serviceStartDoh = $this->getServiceStartDoh($employee);
        $doh = $serviceStartDoh ? Carbon::parse($serviceStartDoh) : Carbon::parse($administration->doh);

        if ($project->leave_type === 'roster') {
            // Group 2 (Roster): Calendar year (1 Jan - 31 Dec)
            return [
                'start' => Carbon::create($year, 1, 1),
                'end' => Carbon::create($year, 12, 31)
            ];
        } else {
            // Group 1 (Non-roster): DOH-based period
            // Period starts from DOH month/day of the year
            $periodStart = Carbon::create($year, $doh->month, $doh->day);

            // If DOH date hasn't occurred yet this year, use previous year's period
            if ($periodStart->isFuture()) {
                $periodStart = Carbon::create($year - 1, $doh->month, $doh->day);
            }

            // Period ends one day before the same date next year
            $periodEnd = $periodStart->copy()->addYear()->subDay();

            return [
                'start' => $periodStart,
                'end' => $periodEnd
            ];
        }
    }


    /**
     * Validate business rules for manual entitlement assignment
     */
    private function validateEntitlementAssignment($employee, $leaveType, $entitledDays)
    {
        $administration = $employee->administrations->where('is_active', 1)->first();
        if (!$administration) {
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
                    $errors[] = "Annual leave is only available for Group 1 projects (000H, 001H, APS, 021C, 025C)";
                }

                // Must have 12+ months service
                if ($monthsOfService < 12) {
                    $errors[] = "Employee must have at least 12 months of service for annual leave";
                }
                break;

            case 'periodic':
                // Only for Group 2 projects
                if ($project->leave_type !== 'roster') {
                    $errors[] = "Periodic leave is only available for Group 2 projects (017C, 022C)";
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
        if (!$administration) {
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
                // LSL eligibility: 60 months for staff, 72 months for non-staff
                $requiredMonths = $isStaff ? 60 : 72;
                if ($monthsOfService < $requiredMonths) {
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
            'FM'
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
        if (!$administration) {
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
                'taken_days' => $periodicEntitlement->taken_days + 10
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
        if (!$administration) {
            $administration = $employee->administrations->first();
        }

        if (!$administration || !$administration->project) {
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
                        if ($hasStaffInName && !$hasNonStaffInName) {
                            continue;
                        }
                    }
                }

                $calculatedDays = $this->calculateEntitlementDays($leaveType, $employee);

                // Special validation for LSL in Group 2 projects
                if ($category === 'lsl' && $project->leave_type === 'roster') {
                    if (!$this->validateLSLForGroup2($employee, now()->year)) {
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
                        'eligibility_reason' => $eligibilityReason
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
            $specialNotes[] = "Annual leave will be available after 12 months of service";
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
            'special_notes' => $specialNotes
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
            'NS' => '10 weeks on / 2 weeks off'
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
        
        // Build filename with project info if specified
        $filename = 'leave_entitlement_' . ($includeData ? 'data_' : 'template_');
        
        if ($projectId && $projectId !== 'all') {
            $project = Project::find($projectId);
            if ($project) {
                $filename .= str_replace(' ', '_', $project->project_code) . '_';
            }
        } elseif ($projectId === 'all') {
            $filename .= 'all_projects_';
        }
        
        $filename .= date('Y-m-d_His') . '.xlsx';

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
            'file.mimes'    => 'The file must be a file of type: xlsx, xls.',
            'file.max'      => 'The file may not be greater than 10MB.'
        ]);

        try {
            $import = new LeaveEntitlementImport();
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
                        'sheet'     => 'Leave Entitlements',
                        'row'       => $error['row'],
                        'attribute' => 'NIK: ' . ($error['nik'] ?: 'N/A'),
                        'value'     => '',
                        'errors'    => implode(', ', $error['errors']),
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
                    'sheet'     => 'Leave Entitlements',
                    'row'       => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'value'     => $failure->values()[$failure->attribute()] ?? null,
                    'errors'    => implode(', ', $failure->errors()),
                ]);
            }
            return back()->with('failures', $failures);
        } catch (\Throwable $e) {
            Log::error('Leave Entitlement Import Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            $failures = collect([
                [
                    'sheet'     => 'System Error',
                    'row'       => '-',
                    'attribute' => 'Import Failed',
                    'value'     => null,
                    'errors'    => 'An error occurred during import: ' . $e->getMessage()
                ]
            ]);
            return back()->with('failures', $failures);
        }
    }
}
