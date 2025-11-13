@extends('layouts.main')

@section('title', 'Roster Management')

@section('content')
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        Roster Management
                    </h1>
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

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Project Filter Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-filter mr-2"></i>
                                Filter Roster
                            </h3>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="project_id">Select Project</label>
                                        <select name="project_id" id="project_id" class="form-control select2bs4" required>
                                            <option value="">Choose Project...</option>
                                            @foreach ($projects as $project)
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
                                        <label for="year">Year</label>
                                        <select name="year" id="year" class="form-control select2bs4">
                                            @for ($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                                    {{ $y }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="month">Month</label>
                                        <select name="month" id="month" class="form-control select2bs4">
                                            @for ($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-search mr-1"></i> Load
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @if ($selectedProject)
                <!-- Search Box -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="GET" action="{{ route('rosters.index') }}" class="row">
                                    <input type="hidden" name="project_id" value="{{ $selectedProject->id }}">
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <input type="hidden" name="month" value="{{ $month }}">
                                    <div class="col-md-10">
                                        <div class="form-group mb-0">
                                            <label for="search">Search Employee</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-search"></i>
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control" id="search" name="search"
                                                    placeholder="Search by Name, NIK, Department, or Position..."
                                                    value="{{ $search }}">
                                            </div>
                                            <small class="form-text text-muted">Search by employee name, NIK, department, or
                                                position</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-0">
                                            <label>&nbsp;</label>
                                            <div class="btn-group w-100" role="group">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-search mr-1"></i> Search
                                                </button>
                                                @if ($search)
                                                    <a href="{{ route('rosters.index', ['project_id' => $selectedProject->id, 'year' => $year, 'month' => $month]) }}"
                                                        class="btn btn-secondary" title="Clear search">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Roster Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-table mr-1"></i>
                                        Roster - {{ $selectedProject->project_code }}
                                        ({{ \Carbon\Carbon::create($year, $month)->format('F Y') }})
                                        @if ($employeeStats)
                                            <span class="badge badge-primary ml-2">
                                                Showing
                                                {{ $employees->firstItem() ?? 0 }}-{{ $employees->lastItem() ?? 0 }} of
                                                {{ $employees->total() }}
                                            </span>
                                        @endif
                                    </h5>
                                    <div class="card-tools">
                                        <button class="btn btn-danger btn-sm mr-2" onclick="clearRoster()">
                                            <i class="fas fa-trash mr-1"></i> Clear Roster
                                        </button>
                                        <button class="btn btn-info btn-sm mr-2" onclick="showImportModal()">
                                            <i class="fas fa-upload mr-1"></i> Import
                                        </button>
                                        <button class="btn btn-success btn-sm mr-2" onclick="exportRoster()">
                                            <i class="fas fa-download mr-1"></i> Export
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                            <i class="fas fa-expand"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                @if (isset($employees) && $employees->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover mb-0 roster-table" width="100%">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th class="text-center align-middle" style="width: 20px;">#</th>
                                                    <th class="align-middle" style="width: 150px;">Name</th>
                                                    <th class="text-center align-middle" style="width: 80px;">NIK</th>
                                                    <th class="text-center align-middle" style="width: 120px;">Department
                                                    </th>
                                                    <th class="text-center align-middle" style="width: 150px;">Position
                                                    </th>
                                                    @for ($day = 1; $day <= \Carbon\Carbon::create($year, $month)->daysInMonth; $day++)
                                                        <th class="text-center align-middle day-header"
                                                            style="width: 15px;">
                                                            <div class="day-number">{{ $day }}</div>
                                                            <div class="day-weekday">
                                                                {{ \Carbon\Carbon::create($year, $month, $day)->format('D') }}
                                                            </div>
                                                        </th>
                                                    @endfor
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($employees as $index => $admin)
                                                    <tr>
                                                        <td class="text-center">{{ $loop->iteration }}</td>
                                                        <td class="font-weight-bold">{{ $admin->employee->fullname }}</td>
                                                        <td class="text-center">{{ $admin->nik }}</td>
                                                        <td>{{ $admin->position->department->department_name ?? 'N/A' }}
                                                        </td>
                                                        <td>{{ $admin->position->position_name }}</td>
                                                        @for ($day = 1; $day <= \Carbon\Carbon::create($year, $month)->daysInMonth; $day++)
                                                            @php
                                                                $currentDate = \Carbon\Carbon::create(
                                                                    $year,
                                                                    $month,
                                                                    $day,
                                                                );
                                                                $isWeekend = $currentDate->isWeekend();
                                                                $isToday = $currentDate->isToday();

                                                                // Get status for this specific date
                                                                $dayStatus = null;
                                                                if ($admin->roster) {
                                                                    $dayStatus = \App\Models\RosterDailyStatus::where(
                                                                        'roster_id',
                                                                        $admin->roster->id,
                                                                    )
                                                                        ->where('date', $currentDate->format('Y-m-d'))
                                                                        ->first();
                                                                }

                                                                $statusCode = $dayStatus
                                                                    ? $dayStatus->status_code
                                                                    : 'D';
                                                                $statusColor = $dayStatus
                                                                    ? $dayStatus->getStatusColor()
                                                                    : '#FFFFFF';
                                                                $notes = $dayStatus ? $dayStatus->notes : '';
                                                            @endphp
                                                            <td class="text-center roster-cell {{ $isWeekend ? 'weekend' : '' }} {{ $isToday ? 'today' : '' }}"
                                                                style="background-color: {{ $statusColor }}; cursor: pointer;"
                                                                data-roster-id="{{ $admin->roster ? $admin->roster->id : '' }}"
                                                                data-administration-id="{{ $admin->id }}"
                                                                data-date="{{ $currentDate->format('Y-m-d') }}"
                                                                data-date-formatted="{{ $currentDate->format('d M Y') }}"
                                                                data-date-day="{{ $currentDate->format('d') }}"
                                                                data-date-weekday="{{ $currentDate->format('l') }}"
                                                                data-status="{{ $statusCode }}"
                                                                data-notes="{{ $notes }}"
                                                                data-employee="{{ $admin->employee->fullname }}"
                                                                data-employee-nik="{{ $admin->nik }}"
                                                                data-employee-department="{{ $admin->position->department->department_name ?? 'N/A' }}"
                                                                data-employee-position="{{ $admin->position->position_name }}"
                                                                onclick="openStatusModal(this)"
                                                                title="Click to update status for {{ $admin->employee->fullname }} on {{ $currentDate->format('d M Y') }}">
                                                                <span class="status-text">{{ $statusCode }}</span>
                                                                @if ($notes)
                                                                    <i class="fas fa-sticky-note text-muted"
                                                                        style="font-size: 8px; position: absolute; top: 2px; right: 2px;"></i>
                                                                @endif
                                                            </td>
                                                        @endfor
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Pagination -->
                                    @if (method_exists($employees, 'hasPages') && $employees->hasPages())
                                        <div class="card-footer clearfix">
                                            <div class="float-left">
                                                <p class="text-muted mb-0">
                                                    Showing {{ $employees->firstItem() ?? 0 }} to
                                                    {{ $employees->lastItem() ?? 0 }}
                                                    of {{ $employees->total() }} entries
                                                    @if ($search)
                                                        <span class="badge badge-info ml-2">Filtered</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="float-right">
                                                {{ $employees->links('pagination::bootstrap-4') }}
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">
                                            @if ($search)
                                                No employees found matching "{{ $search }}"
                                            @else
                                                No employees found for this project
                                            @endif
                                        </h5>
                                        <p class="text-muted">
                                            @if ($search)
                                                Try adjusting your search criteria.
                                            @else
                                                Please ensure employees are assigned to this roster project.
                                            @endif
                                        </p>
                                        @if ($search)
                                            <a href="{{ route('rosters.index', ['project_id' => $selectedProject->id, 'year' => $year, 'month' => $month]) }}"
                                                class="btn btn-secondary mt-2">
                                                <i class="fas fa-times mr-1"></i> Clear Search
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Roster Legend -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Status Legend
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                                        <div class="legend-item">
                                            <span class="legend-color"
                                                style="background-color: #FFFFFF; border: 1px solid #ddd;"></span>
                                            <span class="legend-text">D - Day Shift</span>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                                        <div class="legend-item">
                                            <span class="legend-color" style="background-color: #ADD8E6;"></span>
                                            <span class="legend-text">N - Night Shift</span>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                                        <div class="legend-item">
                                            <span class="legend-color" style="background-color: #FFB6C1;"></span>
                                            <span class="legend-text">OFF - Off Work</span>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                                        <div class="legend-item">
                                            <span class="legend-color" style="background-color: #90EE90;"></span>
                                            <span class="legend-text">C - Periodic Leave</span>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                                        <div class="legend-item">
                                            <span class="legend-color" style="background-color: #FFE4B5;"></span>
                                            <span class="legend-text">S - Sick Leave</span>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                                        <div class="legend-item">
                                            <span class="legend-color" style="background-color: #E6E6FA;"></span>
                                            <span class="legend-text">I - Permission</span>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                                        <div class="legend-item">
                                            <span class="legend-color" style="background-color: #FF6B6B;"></span>
                                            <span class="legend-text">A - Absent</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">Select a Project to View Roster</h4>
                                <p class="text-muted">Choose a roster-based project from the dropdown above to view and
                                    manage employee schedules.</p>
                            </div>
                        </div>
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
                    <h4 class="modal-title">
                        <i class="fas fa-edit mr-2"></i>
                        Update Roster Status
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Employee & Date Info -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-2">
                                    <!-- Employee Info -->
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="mr-2">
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 35px; height: 35px;">
                                                <i class="fas fa-user text-white" style="font-size: 14px;"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-primary font-weight-bold" id="modal-employee-name">-</h6>
                                            <small class="text-muted" id="modal-employee-nik">NIK: -</small>
                                            <small class="text-muted d-block" id="modal-employee-position">Position:
                                                -</small>
                                        </div>
                                    </div>

                                    <!-- Date Info -->
                                    <div class="d-flex align-items-center">
                                        <div class="mr-2">
                                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 35px; height: 35px;">
                                                <i class="fas fa-calendar text-white" style="font-size: 14px;"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-success font-weight-bold" id="modal-date-formatted">-
                                            </h6>
                                            <small class="text-muted d-block" id="modal-date-weekday">-</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form id="statusForm">
                        <input type="hidden" id="roster_id" name="roster_id">
                        <input type="hidden" id="administration_id" name="administration_id">
                        <input type="hidden" id="date" name="date">

                        <div class="form-group">
                            <label for="status_code">Status</label>
                            <select name="status_code" id="status_code" class="form-control" required>
                                <option value="D">D - Day Shift</option>
                                <option value="N">N - Night Shift</option>
                                <option value="OFF">OFF - Off Work</option>
                                <option value="C">C - Periodic Leave</option>
                                <option value="S">S - Sick Leave</option>
                                <option value="I">I - Permission</option>
                                <option value="A">A - Absent</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes (Optional)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Add notes..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" onclick="updateStatus()">
                        <i class="fas fa-save mr-1"></i>Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <style>
        /* Legend Styles */
        .legend-item {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .legend-color {
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-right: 8px;
            border-radius: 3px;
            border: 1px solid #ddd;
        }

        .legend-text {
            font-weight: 500;
        }

        /* Roster Table Styles */
        .table-responsive {
            overflow-x: auto;
            overflow-y: visible;
        }

        .roster-table {
            font-size: 0.85rem;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .roster-table thead th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            text-align: center;
            padding: 8px 4px;
            border: 1px solid #dee2e6;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .roster-table tbody td {
            padding: 6px 4px;
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }

        /* Sticky Columns - #, Name, NIK, Department, Position */
        /* Column 1: # (NO) - left: 0 */
        .roster-table thead th:nth-child(1),
        .roster-table tbody td:nth-child(1) {
            position: sticky;
            left: 0;
            z-index: 9;
            background-color: #f8f9fa;
            width: 50px;
            min-width: 50px;
            max-width: 50px;
        }

        /* Column 2: Name - left: 50px */
        .roster-table thead th:nth-child(2),
        .roster-table tbody td:nth-child(2) {
            position: sticky;
            left: 50px;
            z-index: 9;
            background-color: #f8f9fa;
            width: 150px;
            min-width: 150px;
            max-width: 150px;
        }

        /* Column 3: NIK - left: 200px (50 + 150) */
        .roster-table thead th:nth-child(3),
        .roster-table tbody td:nth-child(3) {
            position: sticky;
            left: 200px;
            z-index: 9;
            background-color: #f8f9fa;
            width: 80px;
            min-width: 80px;
            max-width: 80px;
        }

        /* Column 4: Department - left: 280px (50 + 150 + 80) */
        .roster-table thead th:nth-child(4),
        .roster-table tbody td:nth-child(4) {
            position: sticky;
            left: 280px;
            z-index: 9;
            background-color: #f8f9fa;
            width: 120px;
            min-width: 120px;
            max-width: 120px;
        }

        /* Column 5: Position - left: 400px (50 + 150 + 80 + 120) */
        .roster-table thead th:nth-child(5),
        .roster-table tbody td:nth-child(5) {
            position: sticky;
            left: 400px;
            z-index: 9;
            background-color: #f8f9fa;
            width: 150px;
            min-width: 150px;
            max-width: 150px;
        }

        /* Responsive: Remove sticky columns on smaller screens */
        @media (max-width: 768px) {

            .roster-table thead th:nth-child(1),
            .roster-table tbody td:nth-child(1),
            .roster-table thead th:nth-child(2),
            .roster-table tbody td:nth-child(2),
            .roster-table thead th:nth-child(3),
            .roster-table tbody td:nth-child(3),
            .roster-table thead th:nth-child(4),
            .roster-table tbody td:nth-child(4),
            .roster-table thead th:nth-child(5),
            .roster-table tbody td:nth-child(5) {
                position: static;
                left: auto;
                z-index: auto;
                background-color: transparent;
                width: auto;
                min-width: auto;
                max-width: none;
            }
        }

        /* Hover effects for sticky columns */
        .roster-table tbody tr:hover td:nth-child(1),
        .roster-table tbody tr:hover td:nth-child(2),
        .roster-table tbody tr:hover td:nth-child(3),
        .roster-table tbody tr:hover td:nth-child(4),
        .roster-table tbody tr:hover td:nth-child(5) {
            background-color: #e9ecef !important;
        }

        /* Higher z-index for header sticky columns */
        .roster-table thead th:nth-child(1),
        .roster-table thead th:nth-child(2),
        .roster-table thead th:nth-child(3),
        .roster-table thead th:nth-child(4),
        .roster-table thead th:nth-child(5) {
            z-index: 11;
        }

        /* Shadow effect for sticky columns */
        .roster-table tbody td:nth-child(5)::after {
            content: '';
            position: absolute;
            top: 0;
            right: -8px;
            bottom: 0;
            width: 8px;
            background: linear-gradient(to right, rgba(0, 0, 0, 0.1), transparent);
            pointer-events: none;
        }

        /* Day Header Styles */
        .day-header {
            font-size: 0.75rem;
            line-height: 1.2;
            width: 50px;
            min-width: 50px;
            max-width: 50px;
        }

        .day-number {
            font-weight: bold;
            font-size: 0.8rem;
        }

        .day-weekday {
            font-size: 0.7rem;
            color: #6c757d;
            text-transform: uppercase;
        }

        /* Roster Cell Styles */
        .roster-cell {
            transition: all 0.2s ease;
            border: 1px solid transparent;
            position: relative;
            min-height: 32px;
            text-align: center !important;
            vertical-align: middle !important;
            width: 50px;
            min-width: 50px;
            max-width: 50px;
        }

        /* Ensure all roster cells have consistent alignment */
        .roster-table tbody td.roster-cell {
            text-align: center !important;
            vertical-align: middle !important;
        }

        .roster-cell:hover {
            border-color: #007bff;
            transform: scale(1.05);
            z-index: 5;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }


        .roster-cell.today {
            border-color: #28a745 !important;
            box-shadow: 0 0 0 1px rgba(40, 167, 69, 0.25);
        }

        .status-text {
            font-weight: bold;
            font-size: 0.8rem;
        }

        /* Status D (Day Shift) dengan border */
        .roster-cell[data-status="D"] {
            border: 1px solid #dee2e6 !important;
        }

        /* Card Styles */
        .card {
            box-shadow: 0 0 1px rgba(0, 0, 0, 0.125), 0 1px 3px rgba(0, 0, 0, 0.2);
            border: 0;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        /* Modal Styles */
        .info-box {
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .roster-table {
                font-size: 0.75rem;
            }

            .day-header {
                font-size: 0.7rem;
            }

            .roster-cell {
                min-height: 30px;
            }
        }

        /* Scrollbar Styling */
        .table-responsive::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
@endsection

@section('scripts')
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        let statusModal;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize modal
            statusModal = $('#statusModal');
            // Initialize Select2
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            }).on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });
        });

        function openStatusModal(cell) {
            const rosterId = cell.dataset.rosterId;
            const administrationId = cell.dataset.administrationId;
            const date = cell.dataset.date;
            const dateFormatted = cell.dataset.dateFormatted;
            const dateDay = cell.dataset.dateDay;
            const dateWeekday = cell.dataset.dateWeekday;
            const status = cell.dataset.status;
            const notes = cell.dataset.notes;
            const employee = cell.dataset.employee;
            const employeeNik = cell.dataset.employeeNik;
            const employeePosition = cell.dataset.employeePosition;

            // Set form values
            document.getElementById('roster_id').value = rosterId || '';
            document.getElementById('administration_id').value = administrationId || '';
            document.getElementById('date').value = date;
            document.getElementById('status_code').value = status;
            document.getElementById('notes').value = notes;

            // Update modal info boxes
            document.getElementById('modal-employee-name').textContent = employee;
            document.getElementById('modal-employee-nik').textContent = `NIK: ${employeeNik}`;
            document.getElementById('modal-employee-position').textContent = `Position: ${employeePosition}`;
            document.getElementById('modal-date-formatted').textContent = dateFormatted;
            document.getElementById('modal-date-weekday').textContent = dateWeekday;

            // Show modal
            statusModal.modal('show');
        }

        function updateStatus() {
            const form = document.getElementById('statusForm');
            const formData = new FormData(form);

            // Show loading state
            const updateBtn = document.querySelector('button[onclick="updateStatus()"]');
            const originalText = updateBtn.innerHTML;
            updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Updating...';
            updateBtn.disabled = true;

            fetch('{{ route('rosters.update-status') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || 'Failed to update status');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update roster_id in cell if it was created
                        if (data.data && data.data.roster_id) {
                            const administrationId = formData.get('administration_id');
                            if (administrationId) {
                                const cells = document.querySelectorAll(
                                    `[data-administration-id="${administrationId}"]`);
                                cells.forEach(cell => {
                                    cell.dataset.rosterId = data.data.roster_id;
                                });
                            }
                        }

                        // Update the cell - try both roster_id and administration_id
                        const rosterId = formData.get('roster_id') || (data.data && data.data.roster_id);
                        const administrationId = formData.get('administration_id');
                        const date = formData.get('date');

                        let cell = null;
                        if (rosterId) {
                            cell = document.querySelector(`[data-roster-id="${rosterId}"][data-date="${date}"]`);
                        }
                        if (!cell && administrationId) {
                            cell = document.querySelector(
                                `[data-administration-id="${administrationId}"][data-date="${date}"]`);
                        }

                        if (cell && data.data) {
                            cell.querySelector('.status-text').textContent = data.data.status;
                            cell.style.backgroundColor = data.data.color;
                            cell.dataset.status = data.data.status;

                            // Update roster_id in cell if it was created
                            if (data.data.roster_id) {
                                cell.dataset.rosterId = data.data.roster_id;
                            }

                            // Update notes indicator
                            const notesIcon = cell.querySelector('.fas.fa-sticky-note');
                            if (formData.get('notes') && !notesIcon) {
                                const icon = document.createElement('i');
                                icon.className = 'fas fa-sticky-note text-muted';
                                icon.style.cssText = 'font-size: 8px; position: absolute; top: 2px; right: 2px;';
                                cell.appendChild(icon);
                            } else if (!formData.get('notes') && notesIcon) {
                                notesIcon.remove();
                            }
                        }

                        statusModal.modal('hide');

                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Failed to update status'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: error.message || 'An error occurred while updating status'
                    });
                })
                .finally(() => {
                    // Restore button state
                    updateBtn.innerHTML = originalText;
                    updateBtn.disabled = false;
                });
        }

        function exportRoster() {
            const projectId = document.getElementById('project_id').value;
            const year = document.getElementById('year').value;
            const month = document.getElementById('month').value;
            const search = document.getElementById('search') ? document.getElementById('search').value : '';

            if (!projectId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning!',
                    text: 'Please select a project first',
                    toast: true,
                    position: 'top-end'
                });
                return;
            }

            let url = `{{ route('rosters.export') }}?project_id=${projectId}&year=${year}&month=${month}`;
            if (search) {
                url += `&search=${encodeURIComponent(search)}`;
            }
            window.open(url, '_blank');
        }

        function clearRoster() {
            const projectId = document.getElementById('project_id').value;
            const year = document.getElementById('year').value;
            const month = document.getElementById('month').value;

            if (!projectId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning!',
                    text: 'Please select a project first',
                    toast: true,
                    position: 'top-end'
                });
                return;
            }

            const monthName = new Date(year, month - 1).toLocaleString('en', {
                month: 'long'
            });

            Swal.fire({
                title: 'Clear Roster Data?',
                html: `Are you sure you want to clear all roster data for <strong>${monthName} ${year}</strong>?<br><br>This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Clear All Data!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Clearing...',
                        text: 'Please wait while we clear the roster data',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Make AJAX request
                    fetch('{{ route('rosters.clear') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: JSON.stringify({
                                project_id: projectId,
                                year: year,
                                month: month
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: data.message,
                                    toast: true,
                                    position: 'top-end',
                                    timer: 3000
                                });

                                // Reload the page to show updated data
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: data.message || 'Failed to clear roster data',
                                    toast: true,
                                    position: 'top-end'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'An error occurred while clearing roster data',
                                toast: true,
                                position: 'top-end'
                            });
                        });
                }
            });
        }


        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // ESC to close modal
            if (e.key === 'Escape' && statusModal.hasClass('show')) {
                statusModal.modal('hide');
            }

            // Enter to submit form when modal is open
            if (e.key === 'Enter' && statusModal.hasClass('show') && e.ctrlKey) {
                updateStatus();
            }
        });

        // Debug: Log roster information for each employee
        @if (isset($employees) && $employees->count() > 0)
            console.log('=== ROSTER DEBUG INFO ===');
            @foreach ($employees as $admin)
                @php
                    $employeeName = \DB::table('employees')->where('id', $admin->employee_id)->value('fullname');
                @endphp
                console.log({
                    name: '{{ $employeeName ?? 'Unknown' }}',
                    nik: '{{ $admin->nik ?? 'Unknown' }}',
                    level: '{{ $admin->level ? $admin->level->name : 'Unknown' }}',
                    roster_pattern: '{{ $admin->level ? $admin->level->getRosterPattern() ?? 'No Roster' : 'No Level' }}',
                    work_days: {{ $admin->level ? $admin->level->work_days ?? 0 : 0 }},
                    off_days: {{ $admin->level ? $admin->level->off_days ?? 0 : 0 }},
                    has_roster: {{ $admin->roster ? 'true' : 'false' }},
                    roster_id: {{ $admin->roster ? $admin->roster->id : 'null' }},
                    employee_id: '{{ $admin->employee_id }}',
                    employee_relation: '{{ $admin->employee ? 'Loaded' : 'Not Loaded' }}'
                });
            @endforeach
            console.log('=== END ROSTER DEBUG INFO ===');
        @endif
    </script>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="fas fa-upload mr-2"></i>Import Roster Data
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="importForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Import Instructions:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Upload Excel file (.xlsx or .xls) with roster data</li>
                                <li>File must contain columns: NO, Name, NIK, Position, and day numbers (1-31)</li>
                                <li>Status codes: D (Day Shift), N (Night Shift), OFF (Off Work), S (Sick), I (Permission),
                                    A (Absent), C (Periodic Leave)</li>
                                <li>Employee NIK and Name must match existing data in the system</li>
                            </ul>
                        </div>

                        <div class="form-group">
                            <label for="import_file">Select Excel File</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="import_file" name="file"
                                        accept=".xlsx,.xls" required>
                                    <label class="custom-file-label" for="import_file">Choose file...</label>
                                </div>
                            </div>
                            <small class="form-text text-muted">Maximum file size: 10MB</small>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="clear_existing"
                                    name="clear_existing">
                                <label class="form-check-label" for="clear_existing">
                                    Clear existing roster data before import
                                </label>
                            </div>
                            <small class="form-text text-muted">This will delete all existing roster data for the selected
                                project, year, and month before importing new data.</small>
                        </div>

                        <div class="form-group">
                            <label>Import Preview</label>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Current Selection:</strong><br>
                                Project: <span
                                    id="import_project_name">{{ $selectedProject->project_code ?? 'None' }}</span><br>
                                Period: <span
                                    id="import_period">{{ \Carbon\Carbon::create($year, $month)->format('F Y') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="importBtn">
                            <i class="fas fa-upload mr-1"></i>Import Roster
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Import functionality
        function showImportModal() {
            const projectId = document.getElementById('project_id').value;
            const year = document.getElementById('year').value;
            const month = document.getElementById('month').value;

            if (!projectId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Project Selected',
                    text: 'Please select a project first before importing roster data.',
                    toast: true,
                    position: 'top-end'
                });
                return;
            }

            $('#importModal').modal('show');
        }

        // Handle file input change
        document.getElementById('import_file').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Choose file...';
            e.target.nextElementSibling.textContent = fileName;
        });

        // Handle import form submission
        document.getElementById('importForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const projectId = document.getElementById('project_id').value;
            const year = document.getElementById('year').value;
            const month = document.getElementById('month').value;
            const fileInput = document.getElementById('import_file');
            const clearExisting = document.getElementById('clear_existing').checked;

            if (!fileInput.files[0]) {
                Swal.fire({
                    icon: 'error',
                    title: 'No File Selected',
                    text: 'Please select an Excel file to import.',
                    toast: true,
                    position: 'top-end'
                });
                return;
            }

            const formData = new FormData();
            formData.append('project_id', projectId);
            formData.append('year', year);
            formData.append('month', month);
            formData.append('file', fileInput.files[0]);
            formData.append('clear_existing', clearExisting ? 1 : 0);

            const importBtn = document.getElementById('importBtn');
            const originalText = importBtn.innerHTML;
            importBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Importing...';
            importBtn.disabled = true;

            fetch('{{ route('rosters.import') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || 'Import failed');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        let message = data.message ||
                            `Successfully imported ${data.data.imported_count} roster entries.`;

                        // Show errors if any
                        if (data.data.has_errors && data.data.errors && data.data.errors.length > 0) {
                            const errorList = data.data.errors.slice(0, 5).join('\n');
                            const moreErrors = data.data.errors.length > 5 ?
                                `\n... and ${data.data.errors.length - 5} more errors` : '';
                            message += `\n\nErrors:\n${errorList}${moreErrors}`;
                        }

                        Swal.fire({
                            icon: data.data.has_errors ? 'warning' : 'success',
                            title: data.data.has_errors ? 'Import Completed with Errors' :
                                'Import Successful',
                            text: message,
                            showConfirmButton: true,
                            confirmButtonText: 'OK',
                            width: '600px'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Import Failed',
                            text: data.message,
                            toast: true,
                            position: 'top-end'
                        });
                    }
                })
                .catch(error => {
                    console.error('Import error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Import Error',
                        text: error.message || 'An error occurred while importing the file.',
                        toast: true,
                        position: 'top-end'
                    });
                })
                .finally(() => {
                    importBtn.innerHTML = originalText;
                    importBtn.disabled = false;
                    $('#importModal').modal('hide');
                });
        });
    </script>
@endsection
