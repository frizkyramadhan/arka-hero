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
                            <!-- Quick Access Cards -->
                            <div class="row">
                                <!-- Pending Recommendations -->
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-warning">
                                        <div class="inner">
                                            <h3>{{ $pendingRecommendations }}</h3>
                                            <p>Pending Recommendations</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-thumbs-up"></i>
                                        </div>
                                        <a href="#" class="small-box-footer" data-toggle="modal"
                                            data-target="#modal-recommendations">
                                            Take action <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>

                                <!-- Pending Approvals -->
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-success">
                                        <div class="inner">
                                            <h3>{{ $pendingApprovals }}</h3>
                                            <p>Pending Approvals</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <a href="#" class="small-box-footer" data-toggle="modal"
                                            data-target="#modal-approvals">
                                            Take action <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>

                                <!-- Pending Arrivals -->
                                <div class="col-lg-3 col-6">
                                    <div class="small-box bg-info">
                                        <div class="inner">
                                            <h3>{{ $pendingArrivals }}</h3>
                                            <p>Pending Arrival Stamps</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-plane-arrival"></i>
                                        </div>
                                        <a href="#" class="small-box-footer" data-toggle="modal"
                                            data-target="#modal-arrivals">
                                            View list <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>

                                <!-- Pending Departures -->
                                <div class="col-lg-3 col-6">
                                    <div class="small-box" style="background-color: #8e44ad; color: white;">
                                        <div class="inner">
                                            <h3>{{ $pendingDepartures }}</h3>
                                            <p>Pending Departure Stamps</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-plane-departure"></i>
                                        </div>
                                        <a href="#" class="small-box-footer" data-toggle="modal"
                                            data-target="#modal-departures">
                                            View list <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Travel & Summary -->
                            <div class="row">
                                <!-- Recent Travels -->
                                <div class="col-md-8">
                                    <div class="card">
                                        <div class="card-header border-transparent">
                                            <h3 class="card-title">Open Official Travels</h3>
                                            <a href="{{ route('officialtravels.create') }}"
                                                class="btn btn-sm btn-primary float-right">
                                                <i class="fas fa-plus"></i> Create New Official Travel
                                            </a>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table m-0">
                                                    <thead>
                                                        <tr>
                                                            <th class="align-middle">Travel Number</th>
                                                            <th class="align-middle">Traveler</th>
                                                            <th class="align-middle">Destination</th>
                                                            <th class="align-middle">Status</th>
                                                            <th class="align-middle">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($openTravels as $travel)
                                                            <tr>
                                                                <td>{{ $travel->official_travel_number }}</td>
                                                                <td>{{ $travel->traveler->employee->fullname ?? 'N/A' }}
                                                                </td>
                                                                <td>{{ $travel->destination }}</td>
                                                                <td class="text-center">
                                                                    @if ($travel->official_travel_status == 'draft')
                                                                        <span class="badge badge-secondary">Draft</span>
                                                                    @elseif($travel->official_travel_status == 'open')
                                                                        <span class="badge badge-primary">Open</span>
                                                                    @elseif($travel->official_travel_status == 'closed')
                                                                        <span class="badge badge-success">Closed</span>
                                                                    @elseif($travel->official_travel_status == 'canceled')
                                                                        <span class="badge badge-danger">Canceled</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    <a href="{{ route('officialtravels.show', $travel->id) }}"
                                                                        class="btn btn-sm btn-info">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5" class="text-center">No recent official
                                                                    travels found</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="card-footer clearfix">
                                            <a href="{{ route('officialtravels.index') }}"
                                                class="btn btn-sm btn-secondary float-right">
                                                View All Official Travels
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Summary Stats -->
                                <div class="col-md-4">
                                    <div class="card card-primary card-outline">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-chart-pie mr-1"></i>
                                                Official Travel Summary
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <div
                                                class="d-flex justify-content-between align-items-center border-bottom mb-3">
                                                <p class="text-warning text-xl">
                                                    <i class="fas fa-thumbs-up"></i>
                                                </p>
                                                <p class="d-flex flex-column">
                                                    <span class="text-muted">Pending Recommendations</span>
                                                    <span
                                                        class="font-weight-bold text-right">{{ $pendingRecommendations }}</span>
                                                </p>
                                            </div>
                                            <div
                                                class="d-flex justify-content-between align-items-center border-bottom mb-3">
                                                <p class="text-success text-xl">
                                                    <i class="fas fa-check-circle"></i>
                                                </p>
                                                <p class="d-flex flex-column">
                                                    <span class="text-muted">Pending Approvals</span>
                                                    <span
                                                        class="font-weight-bold text-right">{{ $pendingApprovals }}</span>
                                                </p>
                                            </div>
                                            <div
                                                class="d-flex justify-content-between align-items-center border-bottom mb-3">
                                                <p class="text-info text-xl">
                                                    <i class="fas fa-clipboard-check"></i>
                                                </p>
                                                <p class="d-flex flex-column">
                                                    <span class="text-muted">Pending Stamps</span>
                                                    <span
                                                        class="font-weight-bold text-right">{{ $pendingArrivals + $pendingDepartures }}</span>
                                                </p>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <p class="text-success text-xl">
                                                    <i class="fas fa-plane"></i>
                                                </p>
                                                <p class="d-flex flex-column">
                                                    <span class="text-muted">Open Travels</span>
                                                    <span class="font-weight-bold text-right">{{ $openTravel }}</span>
                                                </p>
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

    <!-- Modals for Data Tables -->

    <!-- Pending Recommendations Modal -->
    <div class="modal fade" id="modal-recommendations">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title">Pending Recommendations</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="pending-recommendations-table" class="table table-sm table-bordered table-striped"
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

    <!-- Pending Approvals Modal -->
    <div class="modal fade" id="modal-approvals">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h4 class="modal-title">Pending Approvals</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="pending-approvals-table" class="table table-sm table-bordered table-striped"
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
