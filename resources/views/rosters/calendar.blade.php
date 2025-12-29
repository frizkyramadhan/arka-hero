@extends('layouts.main')

@section('title', $title)

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('rosters.index') }}">Roster Management</a></li>
                        <li class="breadcrumb-item active">Calendar View</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Filter Card -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filter</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            @if ($selectedProject)
                                <a href="{{ route('leave.periodic-requests.create', ['project_id' => $selectedProject->id]) }}"
                                    class="btn btn-sm btn-primary" title="Create Periodic Leave">
                                    <i class="fas fa-calendar-plus mr-1"></i> Create Periodic Leave
                                </a>
                            @endif
                            <a href="{{ route('rosters.index', ['project_id' => $selectedProject?->id]) }}"
                                class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('rosters.calendar') }}" id="filterForm">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="project_id">Select Project <span class="text-danger">*</span></label>
                                    <select name="project_id" id="project_id" class="form-control select2" required>
                                        <option value="">-- Select Project --</option>
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
                                    <label for="month">Month</label>
                                    <select name="month" id="month" class="form-control">
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}"
                                                {{ (int) $month == $m ? 'selected' : '' }}>
                                                {{ Carbon\Carbon::create(null, $m, 1)->format('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="year">Year</label>
                                    <select name="year" id="year" class="form-control">
                                        @for ($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search mr-1"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if ($selectedProject && count($calendarData) > 0)
                <!-- Legend -->
                <div class="card card-info card-outline">
                    <div class="card-header bg-info">
                        <h3 class="card-title text-white">
                            <i class="fas fa-info-circle mr-2"></i>Legend
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4 mb-2 mb-md-0">
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-work-day badge-lg mr-2">
                                        W
                                    </span>
                                    <div>
                                        <strong>Work Day</strong>
                                        <br>
                                        <small class="text-muted">Hari kerja aktif</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2 mb-md-0">
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-leave-day badge-lg mr-2">
                                        L
                                    </span>
                                    <div>
                                        <strong>Leave Day</strong>
                                        <br>
                                        <small class="text-muted">Hari cuti</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-secondary badge-lg mr-2">
                                        <i class="fas fa-calendar-times"></i>
                                    </span>
                                    <div>
                                        <strong>Off Day</strong>
                                        <br>
                                        <small class="text-muted">Hari libur/tidak aktif</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            {{ Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                            <table class="table table-bordered table-sm table-hover calendar-table"
                                style="margin-bottom: 0;">
                                <thead class="thead-light" style="position: sticky; top: 0; z-index: 10;">
                                    <tr>
                                        <th class="calendar-col-employee"
                                            style="position: sticky; left: 0; background: #f8f9fa; z-index: 11;">
                                            Employee
                                        </th>
                                        <th class="calendar-col-nik"
                                            style="position: sticky; left: 150px; background: #f8f9fa; z-index: 11;">
                                            NIK
                                        </th>
                                        <th class="calendar-col-position"
                                            style="position: sticky; left: 270px; background: #f8f9fa; z-index: 11;">
                                            Position
                                        </th>
                                        @for ($day = 1; $day <= Carbon\Carbon::create($year, $month, 1)->daysInMonth; $day++)
                                            <th class="text-center calendar-col-day">
                                                {{ $day }}<br>
                                                <small style="font-size: 10px;">
                                                    {{ Carbon\Carbon::create($year, $month, $day)->format('D') }}
                                                </small>
                                            </th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($calendarData as $employee)
                                        <tr>
                                            <td class="calendar-col-employee"
                                                style="position: sticky; left: 0; background: white; z-index: 9; font-weight: 600;">
                                                <a href="{{ route('rosters.show', $employee['roster_id']) }}"
                                                    class="text-primary" style="text-decoration: none; font-weight: 600;">
                                                    {{ $employee['name'] }}
                                                </a>
                                            </td>
                                            <td class="calendar-col-nik"
                                                style="position: sticky; left: 150px; background: white; z-index: 9;">
                                                {{ $employee['nik'] }}
                                            </td>
                                            <td class="calendar-col-position"
                                                style="position: sticky; left: 270px; background: white; z-index: 9;">
                                                {{ $employee['position'] ?? '-' }}
                                            </td>
                                            @for ($day = 1; $day <= Carbon\Carbon::create($year, $month, 1)->daysInMonth; $day++)
                                                @php
                                                    $dayData = $employee['days'][$day];
                                                    $status = $dayData['status'] ?? 'off';
                                                    $cycleNo = $dayData['cycle_no'] ?? null;
                                                    $type = $dayData['type'] ?? null;

                                                    // Determine badge class and label
                                                    $badgeClass = 'badge-secondary';
                                                    $badgeLabel = '-';
                                                    if ($type === 'work') {
                                                        $badgeClass = 'badge-work-day';
                                                        $badgeLabel = 'W';
                                                    } elseif ($type === 'leave') {
                                                        $badgeClass = 'badge-leave-day';
                                                        $badgeLabel = 'L';
                                                    }
                                                @endphp
                                                <td class="text-center calendar-col-day">
                                                    @if ($status !== 'off')
                                                        <span class="badge {{ $badgeClass }}"
                                                            title="Cycle: {{ $cycleNo ?? 'N/A' }} | {{ ucfirst($type) }}">
                                                            {{ $badgeLabel }}
                                                        </span>
                                                    @else
                                                        <span style="color: #ccc;">-</span>
                                                    @endif
                                                </td>
                                            @endfor
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @elseif ($selectedProject && count($calendarData) == 0)
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    No roster data found for this project in {{ Carbon\Carbon::create($year, $month, 1)->format('F Y') }}.
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Please select a project to view the calendar.
                </div>
            @endif
        </div>
    </section>

    <style>
        .table-responsive {
            border: 1px solid #dee2e6;
        }

        .calendar-table {
            table-layout: fixed;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .calendar-table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            border: 1px solid #dee2e6;
            vertical-align: middle;
            font-size: 12px;
        }

        .calendar-table tbody td {
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }

        /* Remove border between Employee and NIK columns */
        .calendar-table thead th.calendar-col-employee,
        .calendar-table tbody td.calendar-col-employee {
            border-right: none;
        }

        .calendar-table thead th.calendar-col-nik,
        .calendar-table tbody td.calendar-col-nik {
            border-left: none;
        }

        .calendar-table thead th.calendar-col-position,
        .calendar-table tbody td.calendar-col-position {
            border-left: none;
        }

        .calendar-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Fixed width columns */
        .calendar-col-employee {
            width: 150px;
            min-width: 150px;
            max-width: 150px;
            padding: 8px 12px 8px 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 12px;
        }

        .calendar-col-nik {
            width: 120px;
            min-width: 120px;
            max-width: 120px;
            padding: 8px 12px 8px 8px;
            text-align: center;
            font-size: 12px;
        }

        .calendar-col-position {
            width: 250px;
            min-width: 250px;
            max-width: 250px;
            padding: 8px 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 12px;
        }

        .calendar-col-day {
            width: 45px;
            min-width: 45px;
            max-width: 45px;
            padding: 4px 2px;
            font-size: 11px;
        }

        /* Sticky column styling */
        .calendar-table thead th:first-child,
        .calendar-table thead th:nth-child(2),
        .calendar-table thead th:nth-child(3),
        .calendar-table tbody td:first-child,
        .calendar-table tbody td:nth-child(2),
        .calendar-table tbody td:nth-child(3) {
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        /* Legend badge styling */
        .badge-lg {
            font-size: 16px;
            padding: 10px 14px;
            min-width: 40px;
            text-align: center;
            font-weight: 600;
        }

        /* Legend badge specific styling */
        .badge-lg.badge-work-day {
            background-color: #ffffff;
            color: #333333;
            border: 1px solid #dee2e6;
        }

        .badge-lg.badge-leave-day {
            background-color: #90EE90;
            color: #155724;
            border: 1px solid #90EE90;
        }

        /* Calendar day badge styling */
        .calendar-table .badge {
            display: inline-block;
            width: 28px;
            height: 28px;
            line-height: 28px;
            padding: 0;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 600;
        }

        /* Work day badge - white background */
        .badge-work-day {
            background-color: #ffffff;
            color: #333333;
            border: 1px solid #dee2e6;
        }

        /* Leave day badge - light green */
        .badge-leave-day {
            background-color: #90EE90;
            color: #155724;
            border: 1px solid #90EE90;
        }
    </style>
@endsection
