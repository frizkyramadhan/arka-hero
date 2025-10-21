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

                <!-- Staff / Non-Staff -->
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

                <!-- Permanent / Contract -->
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

                <!-- Born This Month -->
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

            <!-- Quick Actions Card -->
            @can('employees.create')
                <div class="row">
                    <div class="col-12">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-rocket mr-2"></i>Quick Actions
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Personal & Administration -->
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="quick-action-group">
                                            <h6 class="text-muted mb-3"><i class="fas fa-user mr-2"></i>Personal & Admin</h6>
                                            <div class="quick-action-buttons">
                                                <a href="{{ url('personals') }}" class="btn btn-primary btn-sm">
                                                    <i class="fa fa-house-user mr-2"></i>Personal Details
                                                </a>
                                                <a href="{{ url('administrations') }}" class="btn btn-success btn-sm">
                                                    <i class="fa fa-folder mr-2"></i>Administrations
                                                </a>
                                                <a href="{{ url('employeebanks') }}" class="btn btn-info btn-sm">
                                                    <i class="fa fa-credit-card mr-2"></i>Bank Accounts
                                                </a>
                                                <a href="{{ url('taxidentifications') }}" class="btn btn-warning btn-sm">
                                                    <i class="fa fa-user-md mr-2"></i>Tax Identification
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Insurance & Licenses -->
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="quick-action-group">
                                            <h6 class="text-muted mb-3"><i class="fas fa-shield-alt mr-2"></i>Insurance &
                                                Licenses</h6>
                                            <div class="quick-action-buttons">
                                                <a href="{{ url('insurances') }}" class="btn btn-danger btn-sm">
                                                    <i class="fa fa-medkit mr-2"></i>Insurances
                                                </a>
                                                <a href="{{ url('licenses') }}" class="btn btn-purple btn-sm">
                                                    <i class="fa fa-car mr-2"></i>Driver Licenses
                                                </a>
                                                <a href="{{ url('families') }}" class="btn btn-pink btn-sm">
                                                    <i class="fa fa-address-card mr-2"></i>Employee Families
                                                </a>
                                                <a href="{{ url('emrgcalls') }}" class="btn btn-orange btn-sm">
                                                    <i class="fa fa-ambulance mr-2"></i>Emergency Calls
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Education & Training -->
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="quick-action-group">
                                            <h6 class="text-muted mb-3"><i class="fas fa-graduation-cap mr-2"></i>Education &
                                                Training</h6>
                                            <div class="quick-action-buttons">
                                                <a href="{{ url('educations') }}" class="btn btn-indigo btn-sm">
                                                    <i class="fa fa-university mr-2"></i>Educations
                                                </a>
                                                <a href="{{ url('courses') }}" class="btn btn-teal btn-sm">
                                                    <i class="fa fa-graduation-cap mr-2"></i>Courses
                                                </a>
                                                <a href="{{ url('jobexperiences') }}" class="btn btn-cyan btn-sm">
                                                    <i class="fa fa-building mr-2"></i>Job Experiences
                                                </a>
                                                <a href="{{ url('additionaldatas') }}" class="btn btn-secondary btn-sm">
                                                    <i class="fa fa-list mr-2"></i>Additional Data
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Operations & Management -->
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="quick-action-group">
                                            <h6 class="text-muted mb-3"><i class="fas fa-cogs mr-2"></i>Operations &
                                                Management</h6>
                                            <div class="quick-action-buttons">
                                                <a href="{{ url('operableunits') }}" class="btn btn-dark btn-sm">
                                                    <i class="fa fa-truck mr-2"></i>Operable Units
                                                </a>
                                                <a href="{{ route('employees.create') }}" class="btn btn-success btn-sm">
                                                    <i class="fas fa-user-plus mr-2"></i>Add New Employee
                                                </a>
                                                <a href="{{ route('employees.index') }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-list mr-2"></i>View All Employees
                                                </a>
                                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                                    data-target="#importModal">
                                                    <i class="fas fa-upload mr-2"></i>Import Data
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            <!-- Import Modal -->
            <div class="modal fade" id="importModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h4 class="modal-title">
                                <i class="fas fa-upload mr-2"></i>Import Employees
                            </h4>
                            <button type="button" class="close" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <form class="form-horizontal" action="{{ url('employees/import') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Choose Excel File</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="import_file" name="employee"
                                            accept=".xlsx,.xls">
                                        <label class="custom-file-label" for="import_file">Choose file...</label>
                                    </div>
                                    <small class="text-muted">Only Excel files (.xlsx, .xls) are allowed</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-upload mr-2"></i>Import
                                </button>
                            </div>
                        </form>
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
                                                    <td class="text-right">{{ $dept->administrations_count }}</td>
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
                                <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
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
                                                    <td class="text-right">{{ $proj->administrations_count }}</td>
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
                            <a href="{{ route('employees.create') }}" class="btn btn-sm btn-primary float-right">
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
                                                <td colspan="6" class="text-center">No recent employees found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer clearfix">
                            <a href="{{ route('employees.index') }}" class="btn btn-sm btn-secondary float-right">
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
                                                <td>{{ date('d M Y', strtotime($expire->foc)) }}</td>
                                                <td class="text-center">
                                                    @php
                                                        $remaining = \Carbon\Carbon::now()->diffInDays(
                                                            \Carbon\Carbon::parse($expire->foc),
                                                            false,
                                                        );
                                                        $isOverdue = \Carbon\Carbon::parse($expire->foc)->isPast();
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
                                                    <span class="badge {{ $badgeClass }}">{{ $daysText }}</span>
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
                                                <td colspan="6" class="text-center">No contracts expiring in
                                                    the next 30 days</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer clearfix">
                            <a href="{{ route('administrations.index') }}" class="btn btn-sm btn-warning float-right">
                                Manage Contracts
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Bonds Section -->
            <div class="row">
                <!-- Recent Active Employee Bonds -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h3 class="card-title">Recent Active Employee Bonds</h3>
                            <a href="{{ route('employee-bonds.create') }}" class="btn btn-sm btn-secondary float-right">
                                <i class="fas fa-plus"></i> Create New Bond
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-striped m-0">
                                    <thead>
                                        <tr>
                                            <th class="align-middle">Employee</th>
                                            <th class="align-middle">Bond Name</th>
                                            <th class="align-middle">Investment</th>
                                            <th class="align-middle">End Date</th>
                                            <th class="align-middle text-center">Remaining</th>
                                            <th class="align-middle text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentActiveEmployeeBonds as $bond)
                                            <tr>
                                                <td>
                                                    <strong>{{ $bond->employee->fullname }}</strong><br>
                                                    <small
                                                        class="text-muted">{{ $bond->employee->administrations->first()->nik ?? 'N/A' }}</small>
                                                </td>
                                                <td>{{ $bond->bond_name }}</td>
                                                <td>Rp {{ number_format($bond->total_investment_value, 0, ',', '.') }}</td>
                                                <td>{{ $bond->end_date->format('d M Y') }}</td>
                                                <td class="text-center">
                                                    @php
                                                        $remaining = $bond->remaining_days;
                                                        $badgeClass =
                                                            $remaining <= 30
                                                                ? 'badge-danger'
                                                                : ($remaining <= 90
                                                                    ? 'badge-warning'
                                                                    : 'badge-success');
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">{{ $remaining }}
                                                        days</span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('employee-bonds.show', $bond->id) }}"
                                                        class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No active employee bonds found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer clearfix">
                            <a href="{{ route('employee-bonds.index') }}" class="btn btn-sm btn-info float-right">
                                View All Employee Bonds
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Pending Bond Violations -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger">
                            <h3 class="card-title">Pending Bond Violations</h3>
                            <a href="{{ route('bond-violations.create') }}" class="btn btn-sm btn-white float-right">
                                <i class="fas fa-plus"></i> Create New Violation
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-striped table-hover m-0">
                                    <thead>
                                        <tr>
                                            <th class="align-middle">Employee</th>
                                            <th class="align-middle">Bond Name</th>
                                            <th class="align-middle">Violation Date</th>
                                            <th class="align-middle">Penalty Amount</th>
                                            <th class="align-middle text-center">Days Worked</th>
                                            <th class="align-middle text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pendingBondViolations as $violation)
                                            <tr>
                                                <td>
                                                    <strong>{{ $violation->employeeBond->employee->fullname }}</strong><br>
                                                    <small
                                                        class="text-muted">{{ $violation->employeeBond->employee->administrations->first()->nik ?? 'N/A' }}</small>
                                                </td>
                                                <td>{{ $violation->employeeBond->bond_name }}</td>
                                                <td>{{ $violation->violation_date->format('d M Y') }}</td>
                                                <td>{{ $violation->formatted_calculated_penalty }}</td>
                                                <td class="text-center">
                                                    <span class="badge badge-info">{{ $violation->days_worked }}
                                                        days</span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('bond-violations.show', $violation->id) }}"
                                                        class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No pending bond violations found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer clearfix">
                            <a href="{{ route('bond-violations.index') }}" class="btn btn-sm btn-danger float-right">
                                View All Bond Violations
                            </a>
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
                                <table id="employees-by-department-table"
                                    class="table table-sm table-bordered table-striped" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th class="text-center align-middle">No</th>
                                            <th class="align-middle">Department</th>
                                            <th class="align-middle">Total Employees</th>
                                            <th class="text-center align-middle">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
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
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
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

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .info-box {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .progress {
            height: 6px;
            border-radius: 3px;
        }

        .progress-bar {
            border-radius: 3px;
        }

        /* Quick Actions Styles */
        .quick-action-group {
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            padding: 20px;
            height: 100%;
            transition: all 0.3s ease;
            background: #fff;
        }

        .quick-action-group:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .quick-action-group h6 {
            font-weight: 600;
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 8px;
        }

        .quick-action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .quick-action-buttons .btn {
            border-radius: 6px !important;
            font-weight: 500;
            transition: all 0.2s ease;
            font-size: 11px;
            padding: 8px 6px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .quick-action-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .quick-action-buttons .btn i {
            font-size: 10px;
        }

        /* Custom Button Colors */
        .btn-purple {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: #fff;
        }

        .btn-purple:hover {
            background-color: #5a359a;
            border-color: #5a359a;
            color: #fff;
        }

        .btn-indigo {
            background-color: #6610f2;
            border-color: #6610f2;
            color: #fff;
        }

        .btn-indigo:hover {
            background-color: #520dc2;
            border-color: #520dc2;
            color: #fff;
        }

        .btn-orange {
            background-color: #fd7e14;
            border-color: #fd7e14;
            color: #fff;
        }

        .btn-orange:hover {
            background-color: #e76500;
            border-color: #e76500;
            color: #fff;
        }

        .btn-teal {
            background-color: #20c997;
            border-color: #20c997;
            color: #fff;
        }

        .btn-teal:hover {
            background-color: #1ba085;
            border-color: #1ba085;
            color: #fff;
        }

        .btn-cyan {
            background-color: #17a2b8;
            border-color: #17a2b8;
            color: #fff;
        }

        .btn-cyan:hover {
            background-color: #138496;
            border-color: #138496;
            color: #fff;
        }

        .btn-pink {
            background-color: #e83e8c;
            border-color: #e83e8c;
            color: #fff;
        }

        .btn-pink:hover {
            background-color: #d91a72;
            border-color: #d91a72;
            color: #fff;
        }

        /* Card improvements */
        .card-outline.card-info {
            border-top: 3px solid #17a2b8;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .quick-action-buttons {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .quick-action-buttons .btn {
                font-size: 12px;
                padding: 10px 8px;
            }

            .quick-action-group {
                padding: 15px;
                margin-bottom: 15px;
            }
        }

        @media (max-width: 576px) {
            .quick-action-buttons .btn {
                font-size: 11px;
                padding: 8px 6px;
            }

            .quick-action-buttons .btn i {
                font-size: 9px;
            }
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/chart.js/Chart.min.js') }}"></script>
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
            // File input handling for import modal
            $('.custom-file-input').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
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
                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d',
                        '#343a40', '#20c997', '#6610f2', '#fd7e14'
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
