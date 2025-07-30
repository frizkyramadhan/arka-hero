@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">{{ $subtitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Dashboard Tabs -->
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="dashboard-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="employee-tab" data-toggle="pill" href="#employee" role="tab"
                                aria-controls="employee" aria-selected="false">
                                <i class="fas fa-users mr-1"></i> Employees
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="official-travel-tab" data-toggle="pill" href="#official-travel"
                                role="tab" aria-controls="official-travel" aria-selected="true">
                                <i class="fas fa-plane mr-1"></i> Official Travel
                            </a>
                        </li>

                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="dashboard-tabContent">
                        <!-- EMPLOYEE TAB -->
                        <div class="tab-pane fade show active" id="employee" role="tabpanel"
                            aria-labelledby="employee-tab">
                            <!-- Employee Summary Cards -->
                            <div class="row">
                                <!-- Total Employees -->
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-primary">
                                        <div class="inner">
                                            <h3>{{ $totalEmployees }}</h3>
                                            <p>Total Employees</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <a href="{{ route('employees.index') }}" class="small-box-footer">
                                            View all employees <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>

                                <!-- Employee classification -->
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-success">
                                        <div class="inner">
                                            <h3>{{ $staffEmployees }}/{{ $nonStaffEmployees }}</h3>
                                            <p>Staff/Non-Staff</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-id-card"></i>
                                        </div>
                                        <a href="{{ route('employees.staff') }}" class="small-box-footer">
                                            View details <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>

                                <!-- Employee classification -->
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-warning">
                                        <div class="inner">
                                            <h3>{{ $permanentEmployees }}/{{ $contractEmployees }}</h3>
                                            <p>Permanent/Contract</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-id-badge"></i>
                                        </div>
                                        <a href="{{ route('employees.employment') }}" class="small-box-footer">
                                            View details <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>

                                <!-- Employees with Birthday in this month -->
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-birthday">
                                        <div class="inner">
                                            <h3>{{ $birthdayEmployees }}</h3>
                                            <p>Born this {{ date('F') }}</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-birthday-cake"></i>
                                        </div>
                                        <a href="{{ route('employees.birthday') }}" class="small-box-footer">
                                            View details <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Employee Analytics & Recent Employees -->
                            <div class="row">
                                <!-- Employees by Department Chart -->
                                <div class="col-md-6">
                                    <div class="card card-success">
                                        <div class="card-header">
                                            <h3 class="card-title">Employees by Department</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-responsive">
                                                <div class="chart" style="position: relative; height: 300px;">
                                                    <canvas id="departmentChart"></canvas>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Department</th>
                                                                <th class="text-right">Employees</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($employeesByDepartment as $dept)
                                                                <tr>
                                                                    <td>{{ $dept->department_name }}</td>
                                                                    <td class="text-right">
                                                                        {{ $dept->administrations_count }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <a href="#" class="btn btn-sm btn-success mt-3" data-toggle="modal"
                                                    data-target="#modal-departments">
                                                    View All Departments
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Employees by Project Chart -->
                                <div class="col-md-6">
                                    <div class="card card-primary">
                                        <div class="card-header">
                                            <h3 class="card-title">Employees by Project</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-responsive">
                                                <div class="chart" style="position: relative; height: 300px;">
                                                    <canvas id="projectChart"></canvas>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <div class="table-responsive"
                                                    style="max-height: 200px; overflow-y: auto;">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Project</th>
                                                                <th class="text-right">Employees</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($employeesByProject as $proj)
                                                                <tr>
                                                                    <td>{{ $proj->project_code }}</td>
                                                                    <td class="text-right">
                                                                        {{ $proj->administrations_count }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <a href="#" class="btn btn-sm btn-primary mt-3" data-toggle="modal"
                                                    data-target="#modal-projects">
                                                    View All Projects
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Recent Employees -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Recently Joined Employees in Last 30 Days</h3>
                                            <a href="{{ route('employees.create') }}"
                                                class="btn btn-sm btn-primary float-right">
                                                <i class="fas fa-plus"></i> Add New Employee
                                            </a>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                                <table class="table table-striped m-0">
                                                    <thead>
                                                        <tr>
                                                            <th class="align-middle">NIK</th>
                                                            <th class="align-middle">Name</th>
                                                            <th class="align-middle">Position</th>
                                                            <th class="align-middle">Project</th>
                                                            <th class="align-middle">Hire Date</th>
                                                            <th class="align-middle text-center">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($newEmployees as $employee)
                                                            <tr>
                                                                <td>{{ $employee->nik ?? 'N/A' }}</td>
                                                                <td>{{ $employee->fullname ?? 'N/A' }}</td>
                                                                <td>{{ $employee->position_name ?? 'N/A' }}</td>
                                                                <td>{{ $employee->project_code ?? 'N/A' }}</td>
                                                                <td>{{ $employee->doh ? date('d M Y', strtotime($employee->doh)) : 'N/A' }}
                                                                </td>
                                                                <td class="text-center">
                                                                    <a href="{{ route('employees.show', $employee->id) }}"
                                                                        class="btn btn-sm btn-info">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="6" class="text-center">No recent employees
                                                                    found</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="card-footer clearfix">
                                            <a href="{{ route('employees.index') }}"
                                                class="btn btn-sm btn-secondary float-right">
                                                View All Employees
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Expiring Contracts -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-warning">
                                            <h3 class="card-title">Contracts Expiring Soon in Next 30 Days</h3>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                                <table class="table table-striped table-hover m-0">
                                                    <thead>
                                                        <tr>
                                                            <th class="align-middle">NIK</th>
                                                            <th class="align-middle">Name</th>
                                                            <th class="align-middle">Position</th>
                                                            <th class="align-middle">End Date</th>
                                                            <th class="align-middle text-center">Remaining</th>
                                                            <th class="align-middle text-center">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($expiringContracts as $expire)
                                                            <tr>
                                                                <td>{{ $expire->nik ?? 'N/A' }}</td>
                                                                <td>{{ $expire->employee->fullname ?? 'N/A' }}</td>
                                                                <td>
                                                                    @if (isset($expire->position->position_name))
                                                                        {{ $expire->position->position_name }}
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    {{ date('d M Y', strtotime($expire->foc)) }}
                                                                </td>
                                                                <td class="text-center">
                                                                    @php
                                                                        $remaining = \Carbon\Carbon::now()->diffInDays(
                                                                            \Carbon\Carbon::parse($expire->foc),
                                                                            false,
                                                                        );
                                                                        $isOverdue = \Carbon\Carbon::parse(
                                                                            $expire->foc,
                                                                        )->isPast();

                                                                        if ($isOverdue) {
                                                                            $badgeClass = 'badge-dark';
                                                                            $daysText = 'overdue';
                                                                        } else {
                                                                            $badgeClass =
                                                                                $remaining <= 7
                                                                                    ? 'badge-danger'
                                                                                    : ($remaining <= 14
                                                                                        ? 'badge-warning'
                                                                                        : 'badge-info');
                                                                            $daysText = $remaining . ' days';
                                                                        }
                                                                    @endphp
                                                                    <span
                                                                        class="badge {{ $badgeClass }}">{{ $daysText }}</span>
                                                                </td>
                                                                <td class="text-center">
                                                                    <a href="{{ url('employees/' . $expire->employee->id . '#administration') }}"
                                                                        class="btn btn-sm btn-info">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="6" class="text-center">No contracts
                                                                    expiring in the next 30 days</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="card-footer clearfix">
                                            <a href="{{ route('administrations.index') }}"
                                                class="btn btn-sm btn-warning float-right">
                                                Manage Contracts
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- OFFICIAL TRAVEL TAB -->
                        <div class="tab-pane fade" id="official-travel" role="tabpanel"
                            aria-labelledby="official-travel-tab">

                            <!-- Overview Cards -->
                            <div class="row mb-4">
                                <!-- Total Travels -->
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="info-box bg-gradient-primary">
                                        <span class="info-box-icon"><i class="fas fa-plane"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Travels</span>
                                            <span class="info-box-number">{{ $totalTravels ?? 0 }}</span>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: 100%"></div>
                                            </div>
                                            <span class="progress-description">
                                                All time official travels
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Active Travels -->
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="info-box bg-gradient-success">
                                        <span class="info-box-icon"><i class="fas fa-plane-departure"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Active Travels</span>
                                            <span class="info-box-number">{{ $activeTravels ?? 0 }}</span>
                                            <div class="progress">
                                                <div class="progress-bar"
                                                    style="width: {{ $activeTravels > 0 ? ($activeTravels / ($totalTravels ?? 1)) * 100 : 0 }}%">
                                                </div>
                                            </div>
                                            <span class="progress-description">
                                                Currently on travel
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pending Approvals -->
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="info-box bg-gradient-warning">
                                        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Pending Approvals</span>
                                            <span class="info-box-number">{{ $pendingApprovals }}</span>
                                            <div class="progress">
                                                <div class="progress-bar"
                                                    style="width: {{ $pendingApprovals > 0 ? ($pendingApprovals / ($totalTravels ?? 1)) * 100 : 0 }}%">
                                                </div>
                                            </div>
                                            <span class="progress-description">
                                                Waiting for approval
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- This Month Travels -->
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="info-box bg-gradient-info">
                                        <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">This Month</span>
                                            <span class="info-box-number">{{ $thisMonthTravels ?? 0 }}</span>
                                            <div class="progress">
                                                <div class="progress-bar"
                                                    style="width: {{ $thisMonthTravels > 0 ? ($thisMonthTravels / ($totalTravels ?? 1)) * 100 : 0 }}%">
                                                </div>
                                            </div>
                                            <span class="progress-description">
                                                Travels in {{ date('M Y') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Overview & Quick Actions -->
                            <div class="row mb-4">
                                <!-- Status Overview -->
                                <div class="col-lg-8">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-chart-bar mr-2"></i>
                                                Travel Status Overview
                                            </h3>
                                            <div class="card-tools">
                                                <a href="{{ route('officialtravels.create') }}"
                                                    class="btn btn-primary btn-sm">
                                                    <i class="fas fa-plus"></i> New Official Travel
                                                </a>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3 text-center mb-3">
                                                    <div class="status-card draft">
                                                        <div class="status-icon">
                                                            <i class="fas fa-edit"></i>
                                                        </div>
                                                        <div class="status-number">{{ $draftTravels ?? 0 }}</div>
                                                        <div class="status-label">Draft</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 text-center mb-3">
                                                    <div class="status-card submitted">
                                                        <div class="status-icon">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </div>
                                                        <div class="status-number">{{ $submittedTravels ?? 0 }}</div>
                                                        <div class="status-label">Submitted</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 text-center mb-3">
                                                    <div class="status-card approved">
                                                        <div class="status-icon">
                                                            <i class="fas fa-check-circle"></i>
                                                        </div>
                                                        <div class="status-number">{{ $approvedTravels ?? 0 }}</div>
                                                        <div class="status-label">Approved</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 text-center mb-3">
                                                    <div class="status-card rejected">
                                                        <div class="status-icon">
                                                            <i class="fas fa-times-circle"></i>
                                                        </div>
                                                        <div class="status-number">{{ $rejectedTravels ?? 0 }}</div>
                                                        <div class="status-label">Rejected</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Actions -->
                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-bolt mr-2"></i>
                                                Quick Actions
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="quick-actions">
                                                <a href="{{ route('approval.requests.index') }}"
                                                    class="btn btn-warning btn-block mb-2">
                                                    <i class="fas fa-check-circle mr-2"></i>
                                                    Pending Approvals ({{ $pendingApprovals }})
                                                </a>
                                                <a href="#" class="btn btn-info btn-block mb-2" data-toggle="modal"
                                                    data-target="#modal-arrivals">
                                                    <i class="fas fa-plane-arrival mr-2"></i>
                                                    Arrival Stamps ({{ $pendingArrivals }})
                                                </a>
                                                <a href="#" class="btn btn-purple btn-block mb-2"
                                                    data-toggle="modal" data-target="#modal-departures">
                                                    <i class="fas fa-plane-departure mr-2"></i>
                                                    Departure Stamps ({{ $pendingDepartures }})
                                                </a>
                                                <a href="{{ route('officialtravels.index') }}"
                                                    class="btn btn-secondary btn-block">
                                                    <i class="fas fa-list mr-2"></i>
                                                    View All Travels
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Travels & Analytics -->
                            <div class="row">
                                <!-- Recent Travels Table -->
                                <div class="col-lg-8">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-list mr-2"></i>
                                                Recent Official Travels
                                            </h3>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover m-0">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Travel Number</th>
                                                            <th>Traveler</th>
                                                            <th>Destination</th>
                                                            <th>Date</th>
                                                            <th>Status</th>
                                                            <th class="text-center">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($openTravels as $travel)
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $travel->official_travel_number }}</strong>
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="avatar-sm mr-2">
                                                                            <i
                                                                                class="fas fa-user-circle fa-2x text-primary"></i>
                                                                        </div>
                                                                        <div>
                                                                            <div class="font-weight-bold">
                                                                                {{ $travel->traveler->employee->fullname ?? 'N/A' }}
                                                                            </div>
                                                                            <small
                                                                                class="text-muted">{{ $travel->traveler->position->position_name ?? 'N/A' }}</small>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div>
                                                                        <div class="font-weight-bold">
                                                                            {{ $travel->destination }}</div>
                                                                        <small
                                                                            class="text-muted">{{ $travel->duration }}</small>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div>
                                                                        <div class="font-weight-bold">
                                                                            {{ date('d M Y', strtotime($travel->official_travel_date)) }}
                                                                        </div>
                                                                        <small
                                                                            class="text-muted">{{ date('H:i', strtotime($travel->official_travel_date)) }}</small>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    @php
                                                                        $statusMap = [
                                                                            'draft' => [
                                                                                'label' => 'Draft',
                                                                                'class' => 'badge badge-secondary',
                                                                            ],
                                                                            'submitted' => [
                                                                                'label' => 'Submitted',
                                                                                'class' => 'badge badge-info',
                                                                            ],
                                                                            'approved' => [
                                                                                'label' => 'Open',
                                                                                'class' => 'badge badge-success',
                                                                            ],
                                                                            'rejected' => [
                                                                                'label' => 'Rejected',
                                                                                'class' => 'badge badge-danger',
                                                                            ],
                                                                            'closed' => [
                                                                                'label' => 'Closed',
                                                                                'class' => 'badge badge-primary',
                                                                            ],
                                                                            'cancelled' => [
                                                                                'label' => 'Cancelled',
                                                                                'class' => 'badge badge-warning',
                                                                            ],
                                                                        ];
                                                                        $status = $travel->status;
                                                                        $pill = $statusMap[$status] ?? [
                                                                            'label' => ucfirst($status),
                                                                            'class' => 'badge badge-secondary',
                                                                        ];
                                                                    @endphp
                                                                    <span
                                                                        class="{{ $pill['class'] }}">{{ $pill['label'] }}</span>
                                                                </td>
                                                                <td class="text-center">
                                                                    <div class="btn-group">
                                                                        <a href="{{ route('officialtravels.show', $travel->id) }}"
                                                                            class="btn btn-sm btn-outline-info"
                                                                            title="View Details">
                                                                            <i class="fas fa-eye"></i>
                                                                        </a>
                                                                        @if ($travel->status === 'approved')
                                                                            @if ($travel->arrival_at_destination == null)
                                                                                <a href="{{ route('officialtravels.showArrivalForm', $travel->id) }}"
                                                                                    class="btn btn-sm btn-outline-primary"
                                                                                    title="Arrival">
                                                                                    <i class="fas fa-plane-arrival"></i>
                                                                                </a>
                                                                            @elseif ($travel->arrival_at_destination && $travel->departure_from_destination == null)
                                                                                <a href="{{ route('officialtravels.showDepartureForm', $travel->id) }}"
                                                                                    class="btn btn-sm btn-outline-success"
                                                                                    title="Departure">
                                                                                    <i class="fas fa-plane-departure"></i>
                                                                                </a>
                                                                            @elseif ($travel->departure_from_destination)
                                                                                <a href="{{ route('officialtravels.close', $travel->id) }}"
                                                                                    class="btn btn-sm btn-outline-warning"
                                                                                    title="Close">
                                                                                    <i class="fas fa-lock"></i>
                                                                                </a>
                                                                            @endif
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="6" class="text-center py-4">
                                                                    <div class="empty-state">
                                                                        <i class="fas fa-plane fa-3x text-muted mb-3"></i>
                                                                        <h5>No Official Travels Found</h5>
                                                                        <p class="text-muted">Start by creating a new
                                                                            official travel request.</p>
                                                                        <a href="{{ route('officialtravels.create') }}"
                                                                            class="btn btn-primary">
                                                                            <i class="fas fa-plus mr-2"></i>Create New
                                                                            Travel
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Analytics & Stats -->
                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-chart-line mr-2"></i>
                                                Analytics
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <!-- Monthly Trend -->
                                            <div class="analytics-item mb-4">
                                                <h6 class="text-muted mb-2">Monthly Trend</h6>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="h4 mb-0">{{ $thisMonthTravels ?? 0 }}</span>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-arrow-up"></i> {{ $monthlyGrowth ?? 0 }}%
                                                    </span>
                                                </div>
                                                <small class="text-muted">vs last month</small>
                                            </div>

                                            <!-- Top Destinations -->
                                            <div class="analytics-item mb-4">
                                                <h6 class="text-muted mb-2">Top Destinations</h6>
                                                @if (isset($topDestinations) && count($topDestinations) > 0)
                                                    @foreach ($topDestinations as $destination)
                                                        <div
                                                            class="d-flex justify-content-between align-items-center mb-1">
                                                            <span>{{ $destination->destination }}</span>
                                                            <span
                                                                class="badge badge-light">{{ $destination->count }}</span>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <p class="text-muted small">No data available</p>
                                                @endif
                                            </div>

                                            <!-- Department Stats -->
                                            <div class="analytics-item">
                                                <h6 class="text-muted mb-2">By Department</h6>
                                                @if (isset($departmentStats) && count($departmentStats) > 0)
                                                    @foreach ($departmentStats as $dept)
                                                        <div
                                                            class="d-flex justify-content-between align-items-center mb-1">
                                                            <span>{{ $dept->department_name }}</span>
                                                            <span class="badge badge-info">{{ $dept->count }}</span>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <p class="text-muted small">No data available</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <!-- Pending Arrivals Modal -->
    <div class="modal fade" id="modal-arrivals">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h4 class="modal-title">Pending Arrivals</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="pending-arrivals-table" class="table table-sm table-bordered table-striped"
                            style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle">No</th>
                                    <th class="align-middle">Travel Number</th>
                                    <th class="align-middle">Date</th>
                                    <th class="align-middle">Traveler</th>
                                    <th class="align-middle">Destination</th>
                                    <th class="text-center align-middle">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Departures Modal -->
    <div class="modal fade" id="modal-departures">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header text-white" style="background-color: #8e44ad;">
                    <h4 class="modal-title">Pending Departures</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="pending-departures-table" class="table table-sm table-bordered table-striped"
                            style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle">No</th>
                                    <th class="align-middle">Travel Number</th>
                                    <th class="align-middle">Date</th>
                                    <th class="align-middle">Traveler</th>
                                    <th class="align-middle">Destination</th>
                                    <th class="text-center align-middle">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Departments Modal -->
    <div class="modal fade" id="modal-departments">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h4 class="modal-title">Employees by Department</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="employees-by-department-table" class="table table-sm table-bordered table-striped"
                            style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle">No</th>
                                    <th class="align-middle">Department</th>
                                    <th class="align-middle">Total Employees</th>
                                    <th class="text-center align-middle">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Modal -->
    <div class="modal fade" id="modal-projects">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title">Employees by Project</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="employees-by-project-table" class="table table-sm table-bordered table-striped"
                            style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle">No</th>
                                    <th class="align-middle">Project</th>
                                    <th class="align-middle">Total Employees</th>
                                    <th class="text-center align-middle">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <style>
        .bg-birthday {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 50%, #f6d365 100%);
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .bg-birthday .inner {
            position: relative;
            z-index: 2;
        }

        .bg-birthday .small-box-footer {
            background: rgba(0, 0, 0, 0.1);
            color: #fff;
            position: relative;
            z-index: 2;
        }

        .bg-birthday .small-box-footer:hover {
            background: rgba(0, 0, 0, 0.2);
            color: #fff;
        }

        /* Official Travel Dashboard Styles */
        .status-card {
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .status-card.draft {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }

        .status-card.submitted {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }

        .status-card.approved {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            color: white;
        }

        .status-card.rejected {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .status-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            opacity: 0.9;
        }

        .status-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .status-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .quick-actions .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .quick-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-purple {
            background-color: #8e44ad;
            border-color: #8e44ad;
            color: white;
        }

        .btn-purple:hover {
            background-color: #7d3c98;
            border-color: #7d3c98;
            color: white;
        }

        .avatar-sm {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .empty-state {
            padding: 40px 20px;
            text-align: center;
        }

        .empty-state i {
            color: #6c757d;
        }

        .analytics-item {
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .analytics-item:last-child {
            border-bottom: none;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .btn-group .btn {
            margin-right: 5px;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }

        .info-box {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .info-box-icon {
            border-radius: 0;
        }

        .progress {
            height: 6px;
            border-radius: 3px;
        }

        .progress-bar {
            border-radius: 3px;
        }
    </style>
@endsection

@section('scripts')
    <!-- ChartJS -->
    <script src="{{ asset('assets/plugins/chart.js/Chart.min.js') }}"></script>
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
    <script>
        $(function() {
            // Official Travel DataTables
            $('#pending-recommendations-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('dashboard.pendingRecommendations') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'official_travel_number',
                        name: 'official_travel_number'
                    },
                    {
                        data: 'official_travel_date',
                        name: 'official_travel_date'
                    },
                    {
                        data: 'traveler',
                        name: 'traveler'
                    },
                    {
                        data: 'destination',
                        name: 'destination'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [1, 'asc']
                ]
            });

            $('#pending-approvals-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('dashboard.pendingApprovals') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'official_travel_number',
                        name: 'official_travel_number'
                    },
                    {
                        data: 'official_travel_date',
                        name: 'official_travel_date'
                    },
                    {
                        data: 'traveler',
                        name: 'traveler'
                    },
                    {
                        data: 'destination',
                        name: 'destination'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [1, 'asc']
                ]
            });



            $('#pending-arrivals-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('dashboard.pendingArrivals') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'official_travel_number',
                        name: 'official_travel_number'
                    },
                    {
                        data: 'official_travel_date',
                        name: 'official_travel_date'
                    },
                    {
                        data: 'traveler',
                        name: 'traveler'
                    },
                    {
                        data: 'destination',
                        name: 'destination'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [1, 'asc']
                ]
            });

            $('#pending-departures-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('dashboard.pendingDepartures') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'official_travel_number',
                        name: 'official_travel_number'
                    },
                    {
                        data: 'official_travel_date',
                        name: 'official_travel_date'
                    },
                    {
                        data: 'traveler',
                        name: 'traveler'
                    },
                    {
                        data: 'destination',
                        name: 'destination'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [1, 'asc']
                ]
            });

            // Employee DataTables
            $('#employees-by-department-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('dashboard.employeesByDepartment') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'department',
                        name: 'department'
                    },
                    {
                        data: 'total_employees',
                        name: 'total_employees'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ],
                order: [
                    [2, 'desc']
                ]
            });

            $('#employees-by-project-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('dashboard.employeesByProject') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'project',
                        name: 'project'
                    },
                    {
                        data: 'total_employees',
                        name: 'total_employees'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ],
                order: [
                    [2, 'desc']
                ]
            });

            // Department Chart
            var departmentChartCanvas = $('#departmentChart').get(0).getContext('2d');
            var departmentChartData = {
                labels: [
                    @foreach ($employeesByDepartment as $dept)
                        '{{ $dept->slug }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Employees',
                    backgroundColor: 'rgba(60,141,188,0.9)',
                    borderColor: 'rgba(60,141,188,0.8)',
                    pointRadius: false,
                    pointColor: '#3b8bba',
                    pointStrokeColor: 'rgba(60,141,188,1)',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(60,141,188,1)',
                    data: [
                        @foreach ($employeesByDepartment as $dept)
                            {{ $dept->administrations_count }},
                        @endforeach
                    ]
                }]
            };

            var departmentChartOptions = {
                maintainAspectRatio: false,
                responsive: true,
                legend: {
                    display: false
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display: true
                        },
                        ticks: {
                            beginAtZero: true,
                            precision: 0
                        }
                    }]
                }
            };

            new Chart(departmentChartCanvas, {
                type: 'bar',
                data: departmentChartData,
                options: departmentChartOptions
            });

            // Project Chart
            var projectChartCanvas = $('#projectChart').get(0).getContext('2d');
            var projectChartData = {
                labels: [
                    @foreach ($employeesByProject as $proj)
                        '{{ $proj->project_code }}',
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach ($employeesByProject as $proj)
                            {{ $proj->administrations_count }},
                        @endforeach
                    ],
                    backgroundColor: [
                        '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8',
                        '#6c757d', '#343a40', '#20c997', '#6610f2', '#fd7e14',
                    ]
                }]
            };

            var projectChartOptions = {
                maintainAspectRatio: false,
                responsive: true,
                legend: {
                    position: 'right',
                    fontSize: 10
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            };

            new Chart(projectChartCanvas, {
                type: 'doughnut',
                data: projectChartData,
                options: projectChartOptions
            });
        });
    </script>
@endsection
