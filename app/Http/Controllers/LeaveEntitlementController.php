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

class LeaveEntitlementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:leave-entitlements.show')->only('index', 'show', 'data', 'showEmployee', 'showLeaveCalculationDetails', 'getAvailableLeaveTypes', 'getLeaveCalculationDetailsAjax');
        $this->middleware('permission:leave-entitlements.create')->only('create', 'store', 'generateProjectEntitlements', 'generateSelectedProjectEntitlements');
        $this->middleware('permission:leave-entitlements.edit')->only('edit', 'update', 'editEmployee', 'updateEmployee');
        $this->middleware('permission:leave-entitlements.delete')->only('destroy', 'clearAllEntitlements');
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
                3 => 'projects.project_code', // Project (only for all projects)
                4 => 'administrations.doh', // DOH
                5 => 'annual', // Annual
                6 => 'lsl', // LSL
                7 => 'levels.name', // Level
                8 => 'periodic', // Periodic
                9 => 'actions' // Actions
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

                $entitlements = $employee->leaveEntitlements->keyBy('leaveType.category');

                // Get current active period entitlements for remaining days calculation
                $today = now();
                $currentPeriodEntitlements = $employee->leaveEntitlements()
                    ->where('period_start', '<=', $today)
                    ->where('period_end', '>=', $today)
                    ->with('leaveType')
                    ->get()
                    ->keyBy('leaveType.category');

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
                    'doh' => $administration->doh ? $administration->doh->format('d/m/Y') : 'N/A',
                ];

                // Add project column for "All Projects" view
                if ($projectId === 'all') {
                    $row['project'] = $administration->project->project_code ?? 'N/A';
                }

                if ($projectType === 'roster') {
                    $row['lsl'] = $entitlements->get('lsl')->entitled_days ?? 0;
                    $row['lsl_remaining'] = $currentPeriodEntitlements->get('lsl')->remaining_days ?? 0;
                    $row['level'] = $administration->level ? $administration->level->name : 'N/A';
                    $row['periodic'] = $entitlements->get('periodic')->entitled_days ?? 0;
                    $row['periodic_remaining'] = $currentPeriodEntitlements->get('periodic')->remaining_days ?? 0;
                    // For roster projects, set annual to 0
                    $row['annual'] = 0;
                    $row['annual_remaining'] = 0;
                } else {
                    $row['annual'] = $entitlements->get('annual')->entitled_days ?? 0;
                    $row['annual_remaining'] = $currentPeriodEntitlements->get('annual')->remaining_days ?? 0;
                    $row['lsl'] = $entitlements->get('lsl')->entitled_days ?? 0;
                    $row['lsl_remaining'] = $currentPeriodEntitlements->get('lsl')->remaining_days ?? 0;
                    // For non-roster projects, set level and periodic to empty
                    $row['level'] = '';
                    $row['periodic'] = 0;
                    $row['periodic_remaining'] = 0;
                }

                $row['actions'] = [
                    'view_url' => route('leave.entitlements.employee.show', $administration->employee_id),
                    'edit_url' => route('leave.entitlements.employee.edit', $administration->employee_id),
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
                'employee.leaveEntitlements.leaveType' => function ($q) {
                    $q->where('is_active', true);
                }
            ])
            ->join('employees', 'administrations.employee_id', '=', 'employees.id')
            ->join('levels', 'administrations.level_id', '=', 'levels.id')
            ->select(
                'administrations.id as administration_id',
                'administrations.employee_id',
                'administrations.nik',
                'administrations.doh',
                'administrations.level_id',
                'administrations.project_id',
                'employees.id as employee_id',
                'employees.fullname',
                'levels.name as level_name'
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
                'employee.leaveEntitlements.leaveType' => function ($q) {
                    $q->where('is_active', true);
                }
            ])
            ->join('employees', 'administrations.employee_id', '=', 'employees.id')
            ->join('levels', 'administrations.level_id', '=', 'levels.id')
            ->select(
                'administrations.id as administration_id',
                'administrations.employee_id',
                'administrations.nik',
                'administrations.doh',
                'administrations.level_id',
                'administrations.project_id',
                'employees.id as employee_id',
                'employees.fullname',
                'levels.name as level_name'
            );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees = Employee::with(['administrations.project', 'administrations.level'])->get();

        return view('leave-entitlements.create', compact('leaveTypes', 'employees'))->with('title', 'Create Leave Entitlement');
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

        if (!$employee || !$employee->administrations->first()) {
            return response()->json(['leaveTypes' => []]);
        }

        $project = $employee->administrations->first()->project;
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
            'withdrawable_days' => 'required|integer|min:0',
            'deposit_days' => 'nullable|integer|min:0',
            'carried_over' => 'nullable|integer|min:0'
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

            // Calculate remaining days
            $remainingDays = $request->withdrawable_days - ($request->taken_days ?? 0);

            $entitlement = LeaveEntitlement::create([
                'employee_id' => $request->employee_id,
                'leave_type_id' => $request->leave_type_id,
                'period_start' => $request->period_start,
                'period_end' => $request->period_end,
                'entitled_days' => $request->entitled_days,
                'withdrawable_days' => $request->withdrawable_days,
                'deposit_days' => $request->deposit_days ?? 0,
                'carried_over' => $request->carried_over ?? 0,
                'taken_days' => $request->taken_days ?? 0,
                'remaining_days' => $remainingDays
            ]);

            DB::commit();

            return redirect()->route('leave-entitlements.show', $entitlement)
                ->with('toast_success', 'Leave entitlement created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with(['toast_error' => 'Failed to create leave entitlement: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveEntitlement $leaveEntitlement)
    {
        $leaveEntitlement->load(['employee', 'leaveType', 'leaveRequests']);

        return view('leave-entitlements.show', compact('leaveEntitlement'))->with('title', 'Leave Entitlement Details');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveEntitlement $leaveEntitlement)
    {
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees = Employee::with('administrations')->get();

        return view('leave-entitlements.edit', compact('leaveEntitlement', 'leaveTypes', 'employees'))->with('title', 'Edit Leave Entitlement');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveEntitlement $leaveEntitlement)
    {
        $request->validate([
            'entitled_days' => 'required|integer|min:0',
            'withdrawable_days' => 'required|integer|min:0',
            'deposit_days' => 'nullable|integer|min:0',
            'carried_over' => 'nullable|integer|min:0'
        ]);

        DB::beginTransaction();
        try {
            $leaveEntitlement->update([
                'entitled_days' => $request->entitled_days,
                'withdrawable_days' => $request->withdrawable_days,
                'deposit_days' => $request->deposit_days ?? 0,
                'carried_over' => $request->carried_over ?? 0
            ]);

            // Recalculate remaining days
            $leaveEntitlement->calculateRemainingDays();
            $leaveEntitlement->save();

            DB::commit();

            return redirect()->route('leave-entitlements.show', $leaveEntitlement)
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

        if ($request->project_id === 'all') {
            // Generate entitlements for all employees in all projects
            $projects = Project::where('project_status', 1)->get();
            foreach ($projects as $project) {
                $employees = $this->getProjectEmployees($project);
                foreach ($employees as $employee) {
                    $this->generateEmployeeEntitlements($employee, $request->year);
                }
            }

            return redirect()
                ->route('leave.entitlements.index', ['project_id' => 'all'])
                ->with('toast_success', 'Entitlements generated successfully for all employees in all projects.');
        } else {
            $request->validate([
                'project_id' => 'exists:projects,id'
            ]);

            $project = Project::findOrFail($request->project_id);
            $employees = $this->getProjectEmployees($project);

            foreach ($employees as $employee) {
                $this->generateEmployeeEntitlements($employee, $request->year);
            }

            return redirect()
                ->route('leave.entitlements.index', ['project_id' => $project->id])
                ->with('toast_success', 'Entitlements generated successfully for all employees.');
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
                $administration = $employee->administrations->first();
                if (!$administration) {
                    $skippedCount++;
                    continue;
                }

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

                            LeaveEntitlement::updateOrCreate([
                                'employee_id' => $employee->id,
                                'leave_type_id' => $leaveType->id,
                                'period_start' => $periodDates['start'],
                                'period_end' => $periodDates['end']
                            ], [
                                'entitled_days' => $entitlementDays,
                                'withdrawable_days' => $entitlementDays,
                                'remaining_days' => $entitlementDays,
                                'deposit_days' => $leaveType->getDepositDays(),
                                'carried_over' => 0,
                                'taken_days' => 0
                            ]);

                            $generatedCount++;
                        }
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('leave.entitlements.index', ['project_id' => $project->id])
                ->with('toast_success', "Entitlements generated successfully for {$project->project_code} project. Generated {$generatedCount} entitlements for " . $employees->count() . " employees.");
        } catch (\Exception $e) {
            DB::rollback();
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
            'leaveEntitlements.leaveType'
        ]);

        return view('leave-entitlements.employee.show', compact('employee'))
            ->with('title', 'Employee Leave Entitlements - ' . $employee->fullname);
    }

    /**
     * Show detailed leave calculation breakdown for specific employee and leave type
     */
    public function showLeaveCalculationDetails(Request $request, Employee $employee)
    {
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

        return view('leave-entitlements.employee.calculation-details', compact(
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
    public function editEmployee(Employee $employee)
    {
        $employee->load([
            'administrations.project',
            'administrations.level',
            'leaveEntitlements.leaveType'
        ]);

        $leaveTypes = LeaveType::where('is_active', true)->get();

        // Get employee business rules info
        $businessRules = $this->getEmployeeBusinessRules($employee);

        // Fixed current year - always edit current year entitlements
        $currentYear = now()->year;

        // Get current period dates
        $periodDates = $this->calculatePeriodDates($employee, $currentYear);

        return view('leave-entitlements.employee.edit', compact(
            'employee',
            'leaveTypes',
            'businessRules',
            'periodDates',
            'currentYear'
        ))->with('title', 'Edit Employee Leave Entitlements - ' . $employee->fullname);
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
            'entitlements.*.period_end' => 'nullable|date|after:entitlements.*.period_start'
        ]);

        DB::beginTransaction();
        try {
            $currentYear = now()->year;

            foreach ($request->entitlements as $entitlementData) {
                $leaveType = LeaveType::findOrFail($entitlementData['leave_type_id']);
                $entitledDays = (int) $entitlementData['entitled_days'];

                // Determine period dates - use provided dates or calculate default
                if (!empty($entitlementData['period_start']) && !empty($entitlementData['period_end'])) {
                    // Use provided period dates
                    $periodStart = Carbon::parse($entitlementData['period_start']);
                    $periodEnd = Carbon::parse($entitlementData['period_end']);
                } else {
                    // Calculate default period dates based on project group and DOH
                    $periodDates = $this->calculatePeriodDates($employee, $currentYear);
                    $periodStart = $periodDates['start'];
                    $periodEnd = $periodDates['end'];
                }

                // Find existing entitlement with exact same combination
                $existingEntitlement = LeaveEntitlement::where('employee_id', $employee->id)
                    ->where('leave_type_id', $entitlementData['leave_type_id'])
                    ->where('period_start', $periodStart->format('Y-m-d'))
                    ->where('period_end', $periodEnd->format('Y-m-d'))
                    ->first();

                $takenDays = $existingEntitlement ? $existingEntitlement->taken_days : 0;
                $remainingDays = max(0, $entitledDays - $takenDays);

                // Use updateOrCreate with exact period matching
                // This ensures CREATE behavior for different periods, UPDATE for same periods
                LeaveEntitlement::updateOrCreate([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $entitlementData['leave_type_id'],
                    'period_start' => $periodStart->format('Y-m-d'),
                    'period_end' => $periodEnd->format('Y-m-d')
                ], [
                    'entitled_days' => $entitledDays,
                    'withdrawable_days' => $entitledDays,
                    'remaining_days' => $remainingDays,
                    'deposit_days' => $leaveType->getDepositDays(),
                    'carried_over' => 0,
                    'taken_days' => $takenDays
                ]);
            }

            DB::commit();

            return redirect()
                ->route('leave.entitlements.employee.show', $employee)
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
                'administrations' => function ($q) use ($project) {
                    $q->where('project_id', $project->id)
                        ->where('is_active', true);
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
     */
    private function generateEmployeeEntitlements($employee, $year)
    {
        $administration = $employee->administrations->first();
        $project = $administration->project;

        // Get eligible leave types based on project group
        $eligibleCategories = $this->getEligibleLeaveCategories($project);

        foreach ($eligibleCategories as $category) {
            $leaveType = LeaveType::where('category', $category)
                ->where('is_active', true)
                ->first();

            if (!$leaveType) continue;

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

                LeaveEntitlement::updateOrCreate([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'period_start' => $periodDates['start'],
                    'period_end' => $periodDates['end']
                ], [
                    'entitled_days' => $entitlementDays,
                    'withdrawable_days' => $entitlementDays,
                    'remaining_days' => $entitlementDays,
                    'deposit_days' => $leaveType->getDepositDays(),
                    'carried_over' => 0,
                    'taken_days' => 0
                ]);
            }
        }
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
     * Calculate period start and end dates based on project group and DOH
     */
    private function calculatePeriodDates($employee, $year)
    {
        $administration = $employee->administrations->first();
        $project = $administration->project;
        $doh = Carbon::parse($administration->doh);

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
        $administration = $employee->administrations->first();
        $project = $administration->project;
        $level = $administration->level;

        // Calculate months of service
        $doh = \Carbon\Carbon::parse($administration->doh);
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
        $administration = $employee->administrations->first();
        $project = $administration->project;
        $level = $administration->level;

        // Calculate months of service from DOH
        $doh = \Carbon\Carbon::parse($administration->doh);
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

                // Periodic leave based on level roster pattern
                return $this->calculatePeriodicDays($level->name);

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
     * Calculate periodic leave days based on level roster pattern
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
        $administration = $employee->administrations->first();
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
        $administration = $employee->administrations->first();
        $level = $administration->level;
        $isStaff = $this->isStaffLevel($level ? $level->name : '');

        $doh = Carbon::parse($administration->doh);
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
                'remaining_days' => max(0, $periodicEntitlement->remaining_days - 10)
            ]);
        }
    }

    /**
     * Get employee business rules information
     */
    private function getEmployeeBusinessRules($employee)
    {
        $administration = $employee->administrations->first();

        if (!$administration || !$administration->project) {
            return null;
        }

        $project = $administration->project;
        $level = $administration->level;
        $doh = $administration->doh;

        // Calculate months of service
        $monthsOfService = $doh ? Carbon::parse($doh)->diffInMonths(now()) : 0;
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
            $specialNotes[] = "Roster Pattern: {$rosterPattern}";
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
                return 'Eligible - Based on roster pattern';

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
     * Get roster pattern for level
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
}
