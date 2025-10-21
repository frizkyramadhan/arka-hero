# Rencana Implementasi Roster Management System

**Date:** 17 Oktober 2025  
**Project:** ARKA HERO - Roster Management Integration  
**Approach:** Simple, Pragmatic, No Over-Engineering

---

## 🎯 Executive Summary

Implementasi roster management system yang terintegrasi dengan leave management yang sudah ada, dengan fokus pada:

-   Simple database structure
-   Reusable existing components
-   Clean UI based on actual roster table
-   Seamless integration dengan leave system

---

## 📋 Implementation Phases

### **Phase 1: Database Foundation (2-3 hari)**

#### 1.1. Roster Daily Status Table

```sql
-- Table untuk tracking status harian roster
CREATE TABLE roster_daily_status (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    roster_id BIGINT NOT NULL,
    date DATE NOT NULL,
    status_code ENUM('D', 'N', 'OFF', 'S', 'I', 'A', 'C') NOT NULL DEFAULT 'D',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (roster_id) REFERENCES rosters(id) ON DELETE CASCADE,
    UNIQUE KEY idx_roster_date (roster_id, date),
    INDEX idx_date_status (date, status_code)
);
```

#### 1.2. Update Existing Tables

```sql
-- Pastikan roster_templates sudah ada data untuk 017C dan 022C
-- Data akan di-seed berdasarkan level pattern (6/2, 8/2, 9/2, 10/2)

-- Pastikan projects table memiliki leave_type
-- Already exists: leave_type ENUM('non_roster', 'roster')
```

#### 1.3. Seeder untuk Roster Templates

```php
// database/seeders/RosterTemplateSeeder.php
public function run()
{
    $projects = Project::whereIn('project_code', ['017C', '022C'])->get();
    $levels = Level::all();

    $patterns = [
        'Manager' => ['work_days' => 42, 'off_days' => 14, 'cycle' => 56],
        'Superintendent' => ['work_days' => 42, 'off_days' => 14, 'cycle' => 56],
        'Supervisor' => ['work_days' => 56, 'off_days' => 14, 'cycle' => 70],
        'Foreman/Officer' => ['work_days' => 63, 'off_days' => 14, 'cycle' => 77],
        'Non Staff-Non Skill' => ['work_days' => 70, 'off_days' => 14, 'cycle' => 84],
    ];

    foreach ($projects as $project) {
        foreach ($levels as $level) {
            $pattern = $patterns[$level->name] ?? $patterns['Non Staff-Non Skill'];

            RosterTemplate::updateOrCreate([
                'project_id' => $project->id,
                'level_id' => $level->id,
            ], [
                'work_days' => $pattern['work_days'],
                'off_days_local' => $pattern['off_days'],
                'off_days_nonlocal' => $pattern['off_days'],
                'cycle_length' => $pattern['cycle'],
                'effective_date' => now(),
                'is_active' => true
            ]);
        }
    }
}
```

---

### **Phase 2: Model Enhancement (1 hari)**

#### 2.1. RosterDailyStatus Model

```php
<?php
// app/Models/RosterDailyStatus.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RosterDailyStatus extends Model
{
    protected $fillable = [
        'roster_id', 'date', 'status_code', 'notes'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Relationships
    public function roster()
    {
        return $this->belongsTo(Roster::class);
    }

    // Business Logic
    public function getStatusColor()
    {
        return match($this->status_code) {
            'C' => '#90EE90',  // Light green untuk cuti periodik
            'OFF' => '#FFB6C1', // Light red untuk off
            'N' => '#ADD8E6',   // Light blue untuk night shift
            'S' => '#FFE4B5',   // Light orange untuk sakit
            'I' => '#E6E6FA',   // Light purple untuk izin
            'A' => '#FFE4E1',   // Light pink untuk alpha
            default => '#FFFFFF' // White untuk D (day shift)
        };
    }

    public function getStatusName()
    {
        return match($this->status_code) {
            'D' => 'Shift Siang',
            'N' => 'Shift Malam',
            'OFF' => 'Off Kerja',
            'S' => 'Sakit',
            'I' => 'Izin',
            'A' => 'Alpha',
            'C' => 'Cuti Periodik',
            default => 'Unknown'
        };
    }
}
```

#### 2.2. Enhance Existing Roster Model

```php
// app/Models/Roster.php - Add methods

public function dailyStatuses()
{
    return $this->hasMany(RosterDailyStatus::class);
}

public function getStatusForDate($date)
{
    return $this->dailyStatuses()
        ->where('date', $date)
        ->first();
}

public function setStatusForDate($date, $statusCode, $notes = null)
{
    return RosterDailyStatus::updateOrCreate(
        [
            'roster_id' => $this->id,
            'date' => $date
        ],
        [
            'status_code' => $statusCode,
            'notes' => $notes
        ]
    );
}
```

---

### **Phase 3: Controller Implementation (2-3 hari)**

#### 3.1. RosterController - Simple Approach

```php
<?php
// app/Http/Controllers/RosterController.php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Administration;
use App\Models\Roster;
use App\Models\RosterDailyStatus;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RosterController extends Controller
{
    /**
     * Display roster management page with project filter
     */
    public function index(Request $request)
    {
        $projects = Project::where('leave_type', 'roster')
            ->where('project_status', 1)
            ->orderBy('project_code')
            ->get();

        $selectedProject = null;
        $employees = collect();
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $rosterData = [];

        if ($request->filled('project_id')) {
            $selectedProject = Project::find($request->project_id);
            $employees = $this->getProjectEmployees($selectedProject);
            $rosterData = $this->getRosterData($employees, $year, $month);
        }

        return view('rosters.index', compact(
            'projects',
            'selectedProject',
            'employees',
            'rosterData',
            'year',
            'month'
        ));
    }

    /**
     * Get employees for roster project
     */
    private function getProjectEmployees($project)
    {
        return Administration::with(['employee', 'position', 'roster.rosterTemplate'])
            ->where('project_id', $project->id)
            ->where('is_active', 1)
            ->orderBy('nik')
            ->get();
    }

    /**
     * Get roster data for month view
     */
    private function getRosterData($employees, $year, $month)
    {
        $data = [];
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        foreach ($employees as $admin) {
            if (!$admin->roster) {
                // Auto-create roster if not exists
                $admin->roster = $this->createRosterForEmployee($admin);
            }

            $employeeData = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($year, $month, $day);
                $status = RosterDailyStatus::where('roster_id', $admin->roster->id)
                    ->where('date', $date)
                    ->first();

                $employeeData[$day] = [
                    'status' => $status?->status_code ?? 'D',
                    'color' => $status?->getStatusColor() ?? '#FFFFFF',
                    'notes' => $status?->notes
                ];
            }

            $data[$admin->employee_id] = $employeeData;
        }

        return $data;
    }

    /**
     * Auto-create roster for employee
     */
    private function createRosterForEmployee($administration)
    {
        $template = RosterTemplate::where('project_id', $administration->project_id)
            ->where('level_id', $administration->level_id)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return null;
        }

        return Roster::create([
            'employee_id' => $administration->employee_id,
            'administration_id' => $administration->id,
            'roster_template_id' => $template->id,
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->addMonths(3)->endOfMonth(),
            'cycle_no' => 1,
            'adjusted_days' => 0,
            'is_active' => true
        ]);
    }

    /**
     * Update roster status via AJAX
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'roster_id' => 'required|exists:rosters,id',
            'date' => 'required|date',
            'status_code' => 'required|in:D,N,OFF,S,I,A,C',
            'notes' => 'nullable|string|max:500'
        ]);

        $roster = Roster::findOrFail($request->roster_id);

        $status = RosterDailyStatus::updateOrCreate(
            [
                'roster_id' => $roster->id,
                'date' => $request->date
            ],
            [
                'status_code' => $request->status_code,
                'notes' => $request->notes
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => [
                'status' => $status->status_code,
                'color' => $status->getStatusColor(),
                'name' => $status->getStatusName()
            ]
        ]);
    }

    /**
     * Bulk update statuses
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.roster_id' => 'required|exists:rosters,id',
            'updates.*.date' => 'required|date',
            'updates.*.status_code' => 'required|in:D,N,OFF,S,I,A,C'
        ]);

        $updated = 0;
        foreach ($request->updates as $update) {
            RosterDailyStatus::updateOrCreate(
                [
                    'roster_id' => $update['roster_id'],
                    'date' => $update['date']
                ],
                [
                    'status_code' => $update['status_code'],
                    'notes' => $update['notes'] ?? null
                ]
            );
            $updated++;
        }

        return response()->json([
            'success' => true,
            'message' => "Updated {$updated} roster entries"
        ]);
    }
}
```

---

### **Phase 4: View Implementation (2-3 hari)**

#### 4.1. Main Roster View - Based on Image

```blade
{{-- resources/views/rosters/index.blade.php --}}
@extends('layouts.main')

@section('title', 'Roster Management')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Roster Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Roster Management</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filter Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filter</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('rosters.index') }}" id="filterForm">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Project</label>
                                <select name="project_id" class="form-control" required onchange="this.form.submit()">
                                    <option value="">-- Select Project --</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}"
                                                {{ $selectedProject && $selectedProject->id == $project->id ? 'selected' : '' }}>
                                            {{ $project->project_code }} - {{ $project->project_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Year</label>
                                <select name="year" class="form-control" onchange="this.form.submit()">
                                    @for($y = 2024; $y <= 2026; $y++)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Month</label>
                                <select name="month" class="form-control" onchange="this.form.submit()">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                            {{ Carbon\Carbon::create()->month($m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-success btn-block" onclick="exportRoster()">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($selectedProject)
            <!-- Legend Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Keterangan</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap">
                                <div class="mr-4 mb-2">
                                    <span class="badge" style="background-color: #FFFFFF; border: 1px solid #ccc;">D</span>
                                    <small class="ml-1">Shift Siang</small>
                                </div>
                                <div class="mr-4 mb-2">
                                    <span class="badge" style="background-color: #ADD8E6;">N/NS</span>
                                    <small class="ml-1">Shift Malam</small>
                                </div>
                                <div class="mr-4 mb-2">
                                    <span class="badge" style="background-color: #FFB6C1;">Off</span>
                                    <small class="ml-1">Off Kerja</small>
                                </div>
                                <div class="mr-4 mb-2">
                                    <span class="badge" style="background-color: #FFE4B5;">S</span>
                                    <small class="ml-1">Sakit</small>
                                </div>
                                <div class="mr-4 mb-2">
                                    <span class="badge" style="background-color: #E6E6FA;">I</span>
                                    <small class="ml-1">Izin</small>
                                </div>
                                <div class="mr-4 mb-2">
                                    <span class="badge" style="background-color: #FFE4E1;">A</span>
                                    <small class="ml-1">Alpha</small>
                                </div>
                                <div class="mr-4 mb-2">
                                    <span class="badge" style="background-color: #90EE90;">C</span>
                                    <small class="ml-1">Cuti Periodik</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Roster Table Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Roster {{ $selectedProject->project_code }} -
                        {{ Carbon\Carbon::create($year, $month)->format('F Y') }}
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-bordered table-sm roster-table" style="font-size: 12px;">
                            <thead class="thead-light" style="position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th style="width: 30px;">NO</th>
                                    <th style="width: 150px;">Name</th>
                                    <th style="width: 80px;">NIK</th>
                                    <th style="width: 200px;">Position</th>
                                    @php
                                        $daysInMonth = Carbon\Carbon::create($year, $month)->daysInMonth;
                                    @endphp
                                    @for($day = 1; $day <= $daysInMonth; $day++)
                                        <th class="text-center" style="width: 30px; min-width: 30px;">{{ $day }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $index => $admin)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $admin->employee->fullname }}</td>
                                        <td class="text-center">{{ $admin->nik }}</td>
                                        <td>{{ $admin->position->position_name }}</td>

                                        @for($day = 1; $day <= $daysInMonth; $day++)
                                            @php
                                                $date = Carbon\Carbon::create($year, $month, $day);
                                                $dayData = $rosterData[$admin->employee_id][$day] ?? ['status' => 'D', 'color' => '#FFFFFF', 'notes' => null];
                                            @endphp
                                            <td class="text-center roster-cell"
                                                style="background-color: {{ $dayData['color'] }}; cursor: pointer; padding: 2px;"
                                                data-roster-id="{{ $admin->roster?->id }}"
                                                data-date="{{ $date->format('Y-m-d') }}"
                                                data-status="{{ $dayData['status'] }}"
                                                data-employee-name="{{ $admin->employee->fullname }}"
                                                onclick="openStatusModal(this)"
                                                title="{{ $dayData['notes'] }}">
                                                <small>{{ $dayData['status'] }}</small>
                                            </td>
                                        @endfor
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 4 + $daysInMonth }}" class="text-center">
                                            No employees found for this project
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Select a Roster Project</h4>
                    <p class="text-muted">Choose a project from the filter above to view roster management</p>
                </div>
            </div>
        @endif
    </div>
</section>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Roster Status</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    @csrf
                    <input type="hidden" id="rosterId" name="roster_id">
                    <input type="hidden" id="rosterDate" name="date">

                    <div class="form-group">
                        <label>Employee</label>
                        <input type="text" class="form-control" id="employeeName" readonly>
                    </div>

                    <div class="form-group">
                        <label>Date</label>
                        <input type="text" class="form-control" id="displayDate" readonly>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" id="statusCode" name="status_code" required>
                            <option value="D">D - Shift Siang</option>
                            <option value="N">N - Shift Malam</option>
                            <option value="OFF">OFF - Off Kerja</option>
                            <option value="S">S - Sakit</option>
                            <option value="I">I - Izin</option>
                            <option value="A">A - Alpha</option>
                            <option value="C">C - Cuti Periodik</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Notes <small class="text-muted">(optional)</small></label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateStatus()">Update</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.roster-table {
    font-family: 'Arial', sans-serif;
}
.roster-table th,
.roster-table td {
    border: 1px solid #dee2e6;
}
.roster-cell:hover {
    opacity: 0.8;
    box-shadow: 0 0 5px rgba(0,0,0,0.3);
}
.thead-light th {
    background-color: #f8f9fa;
    font-weight: bold;
}
</style>
@endpush

@push('scripts')
<script>
function openStatusModal(cell) {
    const rosterId = cell.dataset.rosterId;
    const date = cell.dataset.date;
    const status = cell.dataset.status;
    const employeeName = cell.dataset.employeeName;

    if (!rosterId) {
        alert('Roster not assigned for this employee');
        return;
    }

    document.getElementById('rosterId').value = rosterId;
    document.getElementById('rosterDate').value = date;
    document.getElementById('employeeName').value = employeeName;
    document.getElementById('displayDate').value = new Date(date).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    document.getElementById('statusCode').value = status;

    $('#statusModal').modal('show');
}

function updateStatus() {
    const formData = new FormData(document.getElementById('statusForm'));

    fetch('{{ route("rosters.update-status") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#statusModal').modal('hide');
            location.reload();
        } else {
            alert('Error updating status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating status');
    });
}

function exportRoster() {
    const projectId = document.querySelector('select[name="project_id"]').value;
    const year = document.querySelector('select[name="year"]').value;
    const month = document.querySelector('select[name="month"]').value;

    if (!projectId) {
        alert('Please select a project first');
        return;
    }

    window.location.href = `{{ route('rosters.export') }}?project_id=${projectId}&year=${year}&month=${month}`;
}
</script>
@endpush
@endsection
```

---

### **Phase 5: Routes Configuration (30 menit)**

```php
<?php
// routes/web.php

Route::middleware(['auth'])->group(function () {

    // Roster Management Routes
    Route::prefix('rosters')->name('rosters.')->group(function () {

        // Main roster management page
        Route::get('/', [RosterController::class, 'index'])
            ->name('index');

        // Update roster status via AJAX
        Route::post('/update-status', [RosterController::class, 'updateStatus'])
            ->name('update-status');

        // Bulk update statuses
        Route::post('/bulk-update', [RosterController::class, 'bulkUpdate'])
            ->name('bulk-update');

        // Export roster
        Route::get('/export', [RosterController::class, 'export'])
            ->name('export');
    });
});
```

---

### **Phase 6: Integration dengan Leave Management (1-2 hari)**

#### 6.1. Update LeaveRequestController

```php
// app/Http/Controllers/LeaveRequestController.php

// Existing method enhancement
private function createRosterAdjustment(LeaveRequest $leaveRequest)
{
    $administration = $leaveRequest->administration;
    $roster = Roster::where('employee_id', $leaveRequest->employee_id)
        ->where('is_active', 1)
        ->first();

    if ($roster) {
        // Update roster daily status untuk setiap hari cuti
        $currentDate = Carbon::parse($leaveRequest->start_date);
        $endDate = Carbon::parse($leaveRequest->end_date);

        while ($currentDate <= $endDate) {
            RosterDailyStatus::updateOrCreate(
                [
                    'roster_id' => $roster->id,
                    'date' => $currentDate->format('Y-m-d')
                ],
                [
                    'status_code' => 'C', // Cuti Periodik
                    'notes' => 'Leave Request: ' . $leaveRequest->leaveType->name
                ]
            );

            $currentDate->addDay();
        }

        // Create roster adjustment record
        RosterAdjustment::create([
            'roster_id' => $roster->id,
            'leave_request_id' => $leaveRequest->id,
            'adjustment_type' => '-days',
            'adjusted_value' => $leaveRequest->total_days,
            'reason' => 'Leave request: ' . $leaveRequest->leaveType->name
        ]);
    }
}
```

#### 6.2. Leave Request Cancellation Integration

```php
// When leave request is cancelled, revert roster status
public function cancelLeaveRequest($leaveRequestId)
{
    $leaveRequest = LeaveRequest::findOrFail($leaveRequestId);

    // Revert roster daily status
    $currentDate = Carbon::parse($leaveRequest->start_date);
    $endDate = Carbon::parse($leaveRequest->end_date);

    while ($currentDate <= $endDate) {
        RosterDailyStatus::where('roster_id', $leaveRequest->roster?->id)
            ->where('date', $currentDate->format('Y-m-d'))
            ->update(['status_code' => 'D']); // Back to day shift

        $currentDate->addDay();
    }

    // Delete roster adjustment
    RosterAdjustment::where('leave_request_id', $leaveRequestId)->delete();
}
```

---

### **Phase 7: Export Functionality (1 hari)**

```php
<?php
// app/Exports/RosterExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RosterExport implements FromCollection, WithHeadings, WithStyles
{
    protected $employees;
    protected $rosterData;
    protected $year;
    protected $month;

    public function __construct($employees, $rosterData, $year, $month)
    {
        $this->employees = $employees;
        $this->rosterData = $rosterData;
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        $data = [];
        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;

        foreach ($this->employees as $index => $admin) {
            $row = [
                $index + 1,
                $admin->employee->fullname,
                $admin->nik,
                $admin->position->position_name
            ];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dayData = $this->rosterData[$admin->employee_id][$day] ?? ['status' => 'D'];
                $row[] = $dayData['status'];
            }

            $data[] = $row;
        }

        return collect($data);
    }

    public function headings(): array
    {
        $headings = ['NO', 'Name', 'NIK', 'Position'];
        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $headings[] = $day;
        }

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

// Controller method
public function export(Request $request)
{
    $project = Project::findOrFail($request->project_id);
    $year = $request->get('year', now()->year);
    $month = $request->get('month', now()->month);

    $employees = $this->getProjectEmployees($project);
    $rosterData = $this->getRosterData($employees, $year, $month);

    $filename = sprintf(
        'Roster_%s_%s_%s.xlsx',
        $project->project_code,
        Carbon::create($year, $month)->format('Y_m'),
        now()->format('YmdHis')
    );

    return Excel::download(
        new RosterExport($employees, $rosterData, $year, $month),
        $filename
    );
}
```

---

## 🔗 Integration Summary

### **Roster → Leave Management**

1. Leave request untuk roster project automatically updates `roster_daily_status`
2. Status 'C' (Cuti Periodik) set untuk tanggal cuti
3. `roster_adjustments` table tracks leave impact

### **Leave Management → Roster**

1. Leave cancellation reverts roster status
2. Leave approval triggers roster status update
3. Periodic leave balance linked to roster template

---

## 📊 Testing Checklist

### **Phase 1-2: Database & Models**

-   [ ] Roster templates seeded for 017C and 022C
-   [ ] All level patterns correct (6/2, 8/2, 9/2, 10/2)
-   [ ] RosterDailyStatus model working
-   [ ] Relationships established

### **Phase 3-4: Controller & Views**

-   [ ] Roster index page loads
-   [ ] Project filter working
-   [ ] Month navigation working
-   [ ] Status update modal working
-   [ ] Cell click updates status

### **Phase 5-6: Routes & Integration**

-   [ ] All routes accessible
-   [ ] Leave request updates roster
-   [ ] Roster status reflects leave
-   [ ] Leave cancellation reverts roster

### **Phase 7: Export**

-   [ ] Export generates Excel file
-   [ ] All data included
-   [ ] Formatting correct

---

## 🎯 Success Criteria

1. ✅ Roster view matches image layout
2. ✅ Simple, maintainable code
3. ✅ No over-engineering
4. ✅ Seamless integration with leave management
5. ✅ Fast performance (<2s page load)
6. ✅ Mobile responsive (bonus)

---

## 📝 Total Time Estimate

| Phase                | Duration       | Priority |
| -------------------- | -------------- | -------- |
| Phase 1: Database    | 2-3 hari       | High     |
| Phase 2: Models      | 1 hari         | High     |
| Phase 3: Controller  | 2-3 hari       | High     |
| Phase 4: Views       | 2-3 hari       | High     |
| Phase 5: Routes      | 30 menit       | High     |
| Phase 6: Integration | 1-2 hari       | High     |
| Phase 7: Export      | 1 hari         | Medium   |
| **Total**            | **10-13 hari** |          |

---

## 🚀 Quick Start Implementation

```bash
# 1. Run migrations
php artisan migrate

# 2. Seed roster templates
php artisan db:seed --class=RosterTemplateSeeder

# 3. Clear cache
php artisan cache:clear
php artisan view:clear

# 4. Test roster page
# Navigate to: /rosters
```

---

## 💡 Key Decisions

1. **Simple Status Tracking**: Use `roster_daily_status` table instead of complex calculation
2. **Auto-create Rosters**: Automatically create roster when employee viewed
3. **AJAX Updates**: Single-cell updates without page reload
4. **Color Coding**: Match image colors for consistency
5. **Integration**: Leverage existing leave management infrastructure

---

## 🔄 Future Enhancements (Post-MVP)

1. Bulk operations (select multiple cells)
2. Copy previous month roster
3. Auto-fill based on pattern
4. Conflict detection
5. Mobile app for roster updates
6. Real-time notifications
7. Advanced reporting & analytics

---

**End of Implementation Plan**
