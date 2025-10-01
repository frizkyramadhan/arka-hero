# Leave Entitlement Management Implementation Plan

## Overview

This document outlines the step-by-step implementation plan for the Leave Entitlement Management system based on the technical flow documentation. The implementation follows Laravel MVC best practices and integrates with the existing AdminLTE system.

## Phase 1: Database Structure Preparation

### Step 1: Project Table Enhancement

**Objective**: Add leave type classification to projects table

**Tasks**:

1. Create migration to add new fields to projects table
2. Update Project model with new methods
3. Update ProjectSeeder with project categorization

**Files to Create/Modify**:

-   `database/migrations/xxxx_xx_xx_add_leave_type_to_projects_table.php`
-   `app/Models/Project.php`
-   `database/seeders/ProjectSeeder.php`

**Migration Code**:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('leave_type', ['non_roster', 'roster'])->default('non_roster')->after('project_name');
            $table->boolean('has_periodic_leave')->default(false)->after('leave_type');
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['leave_type', 'has_periodic_leave']);
        });
    }
};
```

**Project Model Updates**:

```php
// Add to Project model
protected $fillable = [
    'project_code', 'project_name', 'leave_type', 'has_periodic_leave'
];

public function isRosterProject(): bool
{
    return $this->leave_type === 'roster';
}

public function isNonRosterProject(): bool
{
    return $this->leave_type === 'non_roster';
}

public function getEligibleLeaveTypes(): array
{
    if ($this->isRosterProject()) {
        return ['paid', 'unpaid', 'periodic', 'lsl'];
    }

    return ['paid', 'unpaid', 'annual', 'lsl'];
}
```

**ProjectSeeder Updates**:

```php
public function run()
{
    $projects = [
        // Non-Roster Projects (Group 1)
        ['project_code' => '000H', 'project_name' => 'Project 000H', 'leave_type' => 'non_roster'],
        ['project_code' => '001H', 'project_name' => 'Project 001H', 'leave_type' => 'non_roster'],
        ['project_code' => 'APS', 'project_name' => 'Project APS', 'leave_type' => 'non_roster'],
        ['project_code' => '021C', 'project_name' => 'Project 021C', 'leave_type' => 'non_roster'],
        ['project_code' => '025C', 'project_name' => 'Project 025C', 'leave_type' => 'non_roster'],

        // Roster Projects (Group 2)
        ['project_code' => '017C', 'project_name' => 'Project 017C', 'leave_type' => 'roster', 'has_periodic_leave' => true],
        ['project_code' => '022C', 'project_name' => 'Project 022C', 'leave_type' => 'roster', 'has_periodic_leave' => true],
    ];

    foreach ($projects as $project) {
        Project::updateOrCreate(
            ['project_code' => $project['project_code']],
            $project
        );
    }
}
```

### Step 2: Leave Types Verification

**Objective**: Ensure leave_types table has all required categories

**Tasks**:

1. Verify existing leave types in database
2. Add missing categories if needed
3. Update LeaveTypeSeeder

**Required Categories**:

-   `annual` - Annual leave for non-roster projects
-   `paid` - Paid leave (both project types)
-   `unpaid` - Unpaid leave (both project types)
-   `lsl` - Long Service Leave (both project types)
-   `periodic` - Periodic leave for roster projects

**Verification Query**:

```sql
SELECT category, COUNT(*) as count FROM leave_types GROUP BY category;
```

### Step 3: Leave Entitlements Table Verification

**Objective**: Ensure leave_entitlements table structure is correct

**Required Fields**:

-   `employee_id` (foreign key)
-   `leave_type_id` (foreign key)
-   `period_start` (date)
-   `period_end` (date)
-   `entitled_days` (integer)
-   `withdrawable_days` (integer)
-   `deposit_days` (integer)
-   `carried_over` (integer)
-   `taken_days` (integer)
-   `remaining_days` (integer)

### Step 4: Roster Templates Setup

**Objective**: Create roster configuration for Group 2 projects

**Files to Create**:

-   `database/migrations/xxxx_xx_xx_create_roster_templates_table.php`
-   `app/Models/RosterTemplate.php`
-   `database/seeders/RosterTemplateSeeder.php`

**Roster Template Migration**:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('roster_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('level_id')->constrained()->onDelete('cascade');
            $table->integer('work_weeks')->default(6);
            $table->integer('off_weeks')->default(2);
            $table->integer('off_days_local')->default(14);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['project_id', 'level_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('roster_templates');
    }
};
```

**Roster Template Seeder**:

```php
<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Level;
use App\Models\RosterTemplate;
use Illuminate\Database\Seeder;

class RosterTemplateSeeder extends Seeder
{
    public function run()
    {
        $rosterProjects = Project::where('leave_type', 'roster')->get();
        $levels = Level::all();

        $rosterConfigs = [
            'Manager' => ['work_weeks' => 6, 'off_weeks' => 2, 'off_days_local' => 14],
            'Superintendent' => ['work_weeks' => 6, 'off_weeks' => 2, 'off_days_local' => 14],
            'Supervisor' => ['work_weeks' => 8, 'off_weeks' => 2, 'off_days_local' => 14],
            'Foreman/Officer' => ['work_weeks' => 9, 'off_weeks' => 2, 'off_days_local' => 14],
            'Non Staff-Non Skill' => ['work_weeks' => 10, 'off_weeks' => 2, 'off_days_local' => 14],
        ];

        foreach ($rosterProjects as $project) {
            foreach ($levels as $level) {
                $config = $rosterConfigs[$level->name] ?? $rosterConfigs['Non Staff-Non Skill'];

                RosterTemplate::updateOrCreate([
                    'project_id' => $project->id,
                    'level_id' => $level->id,
                ], $config);
            }
        }
    }
}
```

## Phase 2: Controller Implementation

### Step 5: LeaveEntitlementController Creation

**Objective**: Create main controller for leave entitlement management

**Files to Create**:

-   `app/Http/Controllers/LeaveEntitlementController.php`

**Controller Structure**:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveEntitlement;
use App\Models\LeaveType;
use App\Models\Project;
use App\Exports\LeaveEntitlementExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class LeaveEntitlementController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::where('is_active', true)
            ->select('id', 'project_code', 'project_name', 'leave_type')
            ->get();

        $selectedProject = null;
        $employees = collect();

        if ($request->filled('project_id')) {
            $selectedProject = Project::findOrFail($request->project_id);
            $employees = $this->getProjectEmployees($selectedProject);
        }

        return view('leave-entitlements.management', compact(
            'projects',
            'selectedProject',
            'employees'
        ));
    }

    public function generateEntitlements(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'year' => 'required|integer|min:2020|max:2030'
        ]);

        $project = Project::findOrFail($request->project_id);
        $employees = $this->getProjectEmployees($project);

        foreach ($employees as $employee) {
            $this->generateEmployeeEntitlements($employee, $request->year);
        }

        return redirect()
            ->route('leave-entitlements.management', ['project_id' => $project->id])
            ->with('success', 'Entitlements generated successfully for all employees.');
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id'
        ]);

        $project = Project::findOrFail($request->project_id);
        $employees = $this->getProjectEmployees($project);

        return Excel::download(
            new LeaveEntitlementExport($employees, $project),
            "Leave_Entitlements_{$project->project_code}_{now()->year}.xlsx"
        );
    }

    public function showEmployee(Employee $employee)
    {
        $employee->load([
            'administration.project',
            'administration.level',
            'leaveEntitlements.leaveType'
        ]);

        return view('leave-entitlements.employee.show', compact('employee'));
    }

    public function editEmployee(Employee $employee)
    {
        $employee->load([
            'administration.project',
            'administration.level',
            'leaveEntitlements.leaveType'
        ]);

        $leaveTypes = LeaveType::where('is_active', true)->get();

        return view('leave-entitlements.employee.edit', compact('employee', 'leaveTypes'));
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        $request->validate([
            'entitlements' => 'required|array',
            'entitlements.*.leave_type_id' => 'required|exists:leave_types,id',
            'entitlements.*.entitled_days' => 'required|integer|min:0',
            'entitlements.*.year' => 'required|integer|min:2020|max:2030'
        ]);

        foreach ($request->entitlements as $entitlementData) {
            LeaveEntitlement::updateOrCreate([
                'employee_id' => $employee->id,
                'leave_type_id' => $entitlementData['leave_type_id'],
                'period_start' => Carbon::create($entitlementData['year'], 1, 1),
                'period_end' => Carbon::create($entitlementData['year'], 12, 31)
            ], [
                'entitled_days' => $entitlementData['entitled_days'],
                'withdrawable_days' => $entitlementData['entitled_days'],
                'remaining_days' => $entitlementData['entitled_days']
            ]);
        }

        return redirect()
            ->route('leave-entitlements.employee.show', $employee)
            ->with('success', 'Employee entitlements updated successfully.');
    }

    private function getProjectEmployees($project)
    {
        return Employee::whereHas('administration', function($q) use ($project) {
            $q->where('project_id', $project->id)
              ->where('is_active', true);
        })
        ->with([
            'administration.level',
            'leaveEntitlements.leaveType' => function($q) {
                $q->where('is_active', true);
            }
        ])
        ->get();
    }

    private function generateEmployeeEntitlements($employee, $year)
    {
        $project = $employee->administration->project;
        $eligibleTypes = $project->getEligibleLeaveTypes();

        foreach ($eligibleTypes as $category) {
            $leaveType = LeaveType::where('category', $category)->first();
            if (!$leaveType) continue;

            $entitlementDays = $this->calculateEntitlementDays($leaveType, $employee);

            LeaveEntitlement::updateOrCreate([
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'period_start' => Carbon::create($year, 1, 1),
                'period_end' => Carbon::create($year, 12, 31)
            ], [
                'entitled_days' => $entitlementDays,
                'withdrawable_days' => $entitlementDays,
                'remaining_days' => $entitlementDays
            ]);
        }
    }

    private function calculateEntitlementDays($leaveType, $employee)
    {
        switch ($leaveType->category) {
            case 'annual':
                return $leaveType->default_days ?? 12;

            case 'periodic':
                $rosterCalculator = new RosterLeaveCalculator();
                return $rosterCalculator->calculatePeriodicLeave(
                    $employee->administration->level_id,
                    $employee->administration->project_id
                );

            case 'lsl':
                return $leaveType->default_days ?? 30;

            case 'paid':
            case 'unpaid':
                return $leaveType->default_days ?? 0;

            default:
                return 0;
        }
    }
}
```

## Phase 3: View Implementation

### Step 6: Management View Creation

**Objective**: Create main management page

**Files to Create**:

-   `resources/views/leave-entitlements/management.blade.php`

**View Features**:

-   Project filter dropdown
-   Dynamic table based on project type
-   Generate entitlements functionality
-   Export Excel functionality
-   Empty state handling

### Step 7: Employee Views Creation

**Objective**: Create individual employee entitlement management views

**Files to Create**:

-   `resources/views/leave-entitlements/employee/show.blade.php`
-   `resources/views/leave-entitlements/employee/edit.blade.php`

**Features**:

-   Employee entitlement overview
-   Individual entitlement editing
-   Leave balance display
-   Project-specific leave types

## Phase 4: Routes and Navigation

### Step 8: Routes Configuration

**Objective**: Add all required routes

**Files to Modify**:

-   `routes/web.php`

**Routes to Add**:

```php
Route::middleware(['auth'])->group(function () {

    // Main management page
    Route::get('/leave-entitlements/management', [LeaveEntitlementController::class, 'index'])
        ->name('leave-entitlements.management');

    // Generate entitlements for project
    Route::post('/leave-entitlements/generate', [LeaveEntitlementController::class, 'generateEntitlements'])
        ->name('leave-entitlements.generate');

    // Export Excel
    Route::get('/leave-entitlements/export', [LeaveEntitlementController::class, 'exportExcel'])
        ->name('leave-entitlements.export');

    // Individual employee entitlement management
    Route::get('/leave-entitlements/employee/{employee}', [LeaveEntitlementController::class, 'showEmployee'])
        ->name('leave-entitlements.employee.show');

    Route::get('/leave-entitlements/employee/{employee}/edit', [LeaveEntitlementController::class, 'editEmployee'])
        ->name('leave-entitlements.employee.edit');

    Route::put('/leave-entitlements/employee/{employee}', [LeaveEntitlementController::class, 'updateEmployee'])
        ->name('leave-entitlements.employee.update');
});
```

### Step 9: Sidebar Navigation Update

**Objective**: Add leave entitlement management to sidebar

**Files to Modify**:

-   `resources/views/layouts/partials/sidebar.blade.php`

**Navigation Item**:

```blade
<li class="nav-item">
    <a href="{{ route('leave-entitlements.management') }}"
       class="nav-link {{ Request::is('leave-entitlements*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-calendar-check"></i>
        <p>Leave Entitlements</p>
    </a>
</li>
```

## Phase 5: Excel Export Implementation

### Step 10: Excel Export Class

**Objective**: Create Excel export functionality

**Files to Create**:

-   `app/Exports/LeaveEntitlementExport.php`

**Export Features**:

-   Dynamic columns based on project type
-   Employee data with entitlements
-   Project-specific formatting
-   Proper Excel styling

## Phase 6: Testing and Verification

### Step 11: Testing Scenarios

**Objective**: Test all functionality with different scenarios

**Test Cases**:

1. **Non-Roster Project (000H)**:

    - Load employees
    - Generate entitlements
    - Verify annual leave allocation
    - Export Excel

2. **Roster Project (017C)**:

    - Load employees
    - Generate entitlements
    - Verify periodic leave allocation
    - Check level-based calculations

3. **Individual Employee Management**:

    - View employee entitlements
    - Edit entitlements
    - Update entitlements

4. **Edge Cases**:
    - Empty project
    - Employee without administration
    - Invalid project selection

## Implementation Order

1. **Database Preparation** (Steps 1-4)
2. **Controller Implementation** (Step 5)
3. **View Implementation** (Steps 6-7)
4. **Routes and Navigation** (Steps 8-9)
5. **Excel Export** (Step 10)
6. **Testing** (Step 11)

## Dependencies

-   Existing Employee model and relationships
-   Existing Administration model
-   Existing Project model
-   Existing LeaveType model
-   Existing LeaveEntitlement model
-   AdminLTE template system
-   Laravel Excel package

## Success Criteria

-   [ ] Projects properly categorized as roster/non-roster
-   [ ] Dynamic table columns based on project type
-   [ ] Entitlement generation working for both project types
-   [ ] Individual employee management functional
-   [ ] Excel export working correctly
-   [ ] All AdminLTE styling consistent
-   [ ] No JavaScript errors
-   [ ] All routes accessible and functional

## Notes

-   Follow existing code patterns in the system
-   Use AdminLTE CSS classes for styling
-   Implement proper error handling
-   Add validation for all user inputs
-   Use Laravel's built-in features (validation, relationships, etc.)
-   Maintain consistency with existing UI/UX patterns
