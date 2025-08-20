@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $subtitle }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active">{{ $title }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Database Debug Tools</h3>
                            <div class="card-tools">
                                <span class="badge badge-warning">Administrator Only</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-triangle"></i> <strong>DANGER ZONE</strong></h5>
                                <p class="mb-0">These actions will permanently delete all data from the specified tables.
                                    <strong>This action cannot be undone!</strong>
                                </p>
                            </div>

                            <!-- Tab Navigation -->
                            <ul class="nav nav-tabs" id="debugTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="employee-tab" data-toggle="tab"
                                        data-target="#employee" type="button" role="tab" aria-controls="employee"
                                        aria-selected="true">
                                        <i class="fas fa-user-tie"></i> Employee Management
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="recruitment-tab" data-toggle="tab"
                                        data-target="#recruitment" type="button" role="tab" aria-controls="recruitment"
                                        aria-selected="false">
                                        <i class="fas fa-users"></i> Recruitment System
                                    </button>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content" id="debugTabContent">
                                <!-- Employee Management Tab -->
                                <div class="tab-pane fade show active" id="employee" role="tabpanel"
                                    aria-labelledby="employee-tab">
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="card border-danger">
                                                <div class="card-header bg-danger">
                                                    <h4 class="card-title text-white mb-0">
                                                        <i class="fas fa-user-tie"></i> Employee Data Management
                                                    </h4>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h5 class="text-danger">
                                                                <i class="fas fa-users"></i> Core Employee Tables
                                                            </h5>
                                                            <div class="table-responsive">
                                                                <table class="table table-hover table-bordered">
                                                                    <thead class="thead-light">
                                                                        <tr>
                                                                            <th>Table Name</th>
                                                                            <th width="120">Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td><i class="fas fa-user"></i> Employees</td>
                                                                            <td>
                                                                                <form
                                                                                    action="{{ route('debug.truncate.employees') }}"
                                                                                    method="POST" style="display: inline;">
                                                                                    @csrf
                                                                                    <button type="submit"
                                                                                        class="btn btn-danger btn-sm"
                                                                                        onclick="return confirm('Are you sure you want to truncate employees table?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Truncate
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><i class="fas fa-clipboard-list"></i>
                                                                                Administrations</td>
                                                                            <td>
                                                                                <form
                                                                                    action="{{ route('debug.truncate.administrations') }}"
                                                                                    method="POST" style="display: inline;">
                                                                                    @csrf
                                                                                    <button type="submit"
                                                                                        class="btn btn-danger btn-sm"
                                                                                        onclick="return confirm('Are you sure you want to truncate administrations table?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Truncate
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><i class="fas fa-university"></i> Employee
                                                                                Banks</td>
                                                                            <td>
                                                                                <form
                                                                                    action="{{ route('debug.truncate.employeebanks') }}"
                                                                                    method="POST" style="display: inline;">
                                                                                    @csrf
                                                                                    <button type="submit"
                                                                                        class="btn btn-danger btn-sm"
                                                                                        onclick="return confirm('Are you sure you want to truncate employee banks table?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Truncate
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><i class="fas fa-receipt"></i> Tax
                                                                                Identifications</td>
                                                                            <td>
                                                                                <form
                                                                                    action="{{ route('debug.truncate.taxidentifications') }}"
                                                                                    method="POST" style="display: inline;">
                                                                                    @csrf
                                                                                    <button type="submit"
                                                                                        class="btn btn-danger btn-sm"
                                                                                        onclick="return confirm('Are you sure you want to truncate tax identifications table?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Truncate
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><i class="fas fa-shield-alt"></i>
                                                                                Insurances</td>
                                                                            <td>
                                                                                <form
                                                                                    action="{{ route('debug.truncate.insurances') }}"
                                                                                    method="POST"
                                                                                    style="display: inline;">
                                                                                    @csrf
                                                                                    <button type="submit"
                                                                                        class="btn btn-danger btn-sm"
                                                                                        onclick="return confirm('Are you sure you want to truncate insurances table?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Truncate
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><i class="fas fa-id-card"></i> Licenses
                                                                            </td>
                                                                            <td>
                                                                                <form
                                                                                    action="{{ route('debug.truncate.licenses') }}"
                                                                                    method="POST"
                                                                                    style="display: inline;">
                                                                                    @csrf
                                                                                    <button type="submit"
                                                                                        class="btn btn-danger btn-sm"
                                                                                        onclick="return confirm('Are you sure you want to truncate licenses table?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Truncate
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h5 class="text-danger">
                                                                <i class="fas fa-plus-circle"></i> Additional Employee Data
                                                            </h5>
                                                            <div class="table-responsive">
                                                                <table class="table table-hover table-bordered">
                                                                    <thead class="thead-light">
                                                                        <tr>
                                                                            <th>Table Name</th>
                                                                            <th width="120">Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td><i class="fas fa-home"></i> Families</td>
                                                                            <td>
                                                                                <form
                                                                                    action="{{ route('debug.truncate.families') }}"
                                                                                    method="POST"
                                                                                    style="display: inline;">
                                                                                    @csrf
                                                                                    <button type="submit"
                                                                                        class="btn btn-danger btn-sm"
                                                                                        onclick="return confirm('Are you sure you want to truncate families table?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Truncate
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><i class="fas fa-graduation-cap"></i>
                                                                                Educations</td>
                                                                            <td>
                                                                                <form
                                                                                    action="{{ route('debug.truncate.educations') }}"
                                                                                    method="POST"
                                                                                    style="display: inline;">
                                                                                    @csrf
                                                                                    <button type="submit"
                                                                                        class="btn btn-danger btn-sm"
                                                                                        onclick="return confirm('Are you sure you want to truncate educations table?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Truncate
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><i class="fas fa-chalkboard-teacher"></i>
                                                                                Courses</td>
                                                                            <td>
                                                                                <form
                                                                                    action="{{ route('debug.truncate.courses') }}"
                                                                                    method="POST"
                                                                                    style="display: inline;">
                                                                                    @csrf
                                                                                    <button type="submit"
                                                                                        class="btn btn-danger btn-sm"
                                                                                        onclick="return confirm('Are you sure you want to truncate courses table?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Truncate
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><i class="fas fa-briefcase"></i> Job
                                                                                Experiences</td>
                                                                            <td>
                                                                                <form
                                                                                    action="{{ route('debug.truncate.jobexperiences') }}"
                                                                                    method="POST"
                                                                                    style="display: inline;">
                                                                                    @csrf
                                                                                    <button type="submit"
                                                                                        class="btn btn-danger btn-sm"
                                                                                        onclick="return confirm('Are you sure you want to truncate job experiences table?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Truncate
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><i class="fas fa-building"></i> Operable
                                                                                Units</td>
                                                                            <td>
                                                                                <form
                                                                                    action="{{ route('debug.truncate.operableunits') }}"
                                                                                    method="POST"
                                                                                    style="display: inline;">
                                                                                    @csrf
                                                                                    <button type="submit"
                                                                                        class="btn btn-danger btn-sm"
                                                                                        onclick="return confirm('Are you sure you want to truncate operable units table?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Truncate
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><i class="fas fa-phone"></i> Emergency
                                                                                Calls</td>
                                                                            <td>
                                                                                <form
                                                                                    action="{{ route('debug.truncate.emrgcalls') }}"
                                                                                    method="POST"
                                                                                    style="display: inline;">
                                                                                    @csrf
                                                                                    <button type="submit"
                                                                                        class="btn btn-danger btn-sm"
                                                                                        onclick="return confirm('Are you sure you want to truncate emergency calls table?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Truncate
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><i class="fas fa-database"></i> Additional
                                                                                Datas</td>
                                                                            <td>
                                                                                <form
                                                                                    action="{{ route('debug.truncate.additionaldatas') }}"
                                                                                    method="POST"
                                                                                    style="display: inline;">
                                                                                    @csrf
                                                                                    <button type="submit"
                                                                                        class="btn btn-danger btn-sm"
                                                                                        onclick="return confirm('Are you sure you want to truncate additional datas table?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Truncate
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Employee Bulk Operation -->
                                                    <div class="row mt-4">
                                                        <div class="col-12">
                                                            <div class="alert alert-danger border-danger">
                                                                <h5><i class="fas fa-bomb"></i> <strong>NUCLEAR
                                                                        OPTION</strong></h5>
                                                                <p class="mb-3">This will truncate <strong>ALL
                                                                        employee-related tables</strong> at once. Use with
                                                                    extreme caution!</p>
                                                                <form action="{{ route('debug.truncate.all') }}"
                                                                    method="POST" style="display: inline;">
                                                                    @csrf
                                                                    <button type="submit"
                                                                        class="btn btn-danger btn-lg shadow"
                                                                        onclick="return confirm('⚠️ FINAL WARNING: This will truncate ALL employee tables and related data. This action is IRREVERSIBLE. Are you absolutely sure?')">
                                                                        <i class="fas fa-radiation"></i> TRUNCATE ALL
                                                                        EMPLOYEE TABLES
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Recruitment System Tab -->
                                <div class="tab-pane fade" id="recruitment" role="tabpanel"
                                    aria-labelledby="recruitment-tab">
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="card border-warning">
                                                <div class="card-header bg-warning">
                                                    <h4 class="card-title text-dark mb-0">
                                                        <i class="fas fa-users"></i> Recruitment System Management
                                                    </h4>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h5 class="text-warning">
                                                                <i class="fas fa-database"></i> Core Recruitment Tables
                                                            </h5>
                                                            <div class="table-responsive">
                                                                <table class="table table-hover table-bordered">
                                                                    <thead class="thead-light">
                                                                        <tr>
                                                                            <th>Table Name</th>
                                                                            <th width="120">Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td><i class="fas fa-file-alt"></i> Recruitment
                                                                                Requests</td>
                                                                            <td>
                                                                                <form
                                                                                    action="{{ route('debug.truncate.recruitment_requests') }}"
                                                                                    method="POST"
                                                                                    style="display: inline;">
                                                                                    @csrf
                                                                                    <button type="submit"
                                                                                        class="btn btn-warning btn-sm"
                                                                                        onclick="return confirm('Are you sure you want to truncate recruitment requests table?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Truncate
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><i class="fas fa-user-plus"></i>
                                                                                Recruitment Candidates</td>
                                                                            <td>
                                                                                <form
                                                                                    action="{{ route('debug.truncate.recruitment_candidates') }}"
                                                                                    method="POST"
                                                                                    style="display: inline;">
                                                                                    @csrf
                                                                                    <button type="submit"
                                                                                        class="btn btn-warning btn-sm"
                                                                                        onclick="return confirm('Are you sure you want to truncate recruitment candidates table?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Truncate
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><i class="fas fa-handshake"></i>
                                                                                Recruitment Sessions</td>
                                                                            <td>
                                                                                <form
                                                                                    action="{{ route('debug.truncate.recruitment_sessions') }}"
                                                                                    method="POST"
                                                                                    style="display: inline;">
                                                                                    @csrf
                                                                                    <button type="submit"
                                                                                        class="btn btn-warning btn-sm"
                                                                                        onclick="return confirm('Are you sure you want to truncate recruitment sessions table?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Truncate
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h5 class="text-warning">
                                                                <i class="fas fa-tasks"></i> Assessment Stages
                                                            </h5>
                                                            <div class="table-responsive">
                                                                <table class="table table-hover table-bordered">
                                                                    <thead class="thead-light">
                                                                        <tr>
                                                                            <th>Stage Category</th>
                                                                            <th width="120">Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td>
                                                                                <i class="fas fa-layer-group"></i> All
                                                                                Assessment Stages
                                                                                <br>
                                                                                <small class="text-muted">
                                                                                    CV Reviews, Psikotes, Theory Tests,<br>
                                                                                    Interviews, Offerings, MCU, Hiring,
                                                                                    Onboarding
                                                                                </small>
                                                                            </td>
                                                                            <td>
                                                                                <form
                                                                                    action="{{ route('debug.truncate.recruitment_stages') }}"
                                                                                    method="POST"
                                                                                    style="display: inline;">
                                                                                    @csrf
                                                                                    <button type="submit"
                                                                                        class="btn btn-warning btn-sm"
                                                                                        onclick="return confirm('Are you sure you want to truncate all recruitment stage tables?')">
                                                                                        <i class="fas fa-trash"></i>
                                                                                        Truncate All
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>

                                                            <div class="alert alert-info mt-3">
                                                                <h6><i class="fas fa-info-circle"></i> Stage Tables
                                                                    Included:</h6>
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <ul class="list-unstyled mb-0 small">
                                                                            <li><i
                                                                                    class="fas fa-file-search text-primary"></i>
                                                                                CV Reviews</li>
                                                                            <li><i class="fas fa-brain text-info"></i>
                                                                                Psikotes</li>
                                                                            <li><i
                                                                                    class="fas fa-clipboard-check text-success"></i>
                                                                                Theory Tests</li>
                                                                            <li><i
                                                                                    class="fas fa-comments text-warning"></i>
                                                                                Interviews</li>
                                                                        </ul>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <ul class="list-unstyled mb-0 small">
                                                                            <li><i
                                                                                    class="fas fa-handshake text-primary"></i>
                                                                                Offerings</li>
                                                                            <li><i
                                                                                    class="fas fa-stethoscope text-danger"></i>
                                                                                MCU</li>
                                                                            <li><i
                                                                                    class="fas fa-user-check text-success"></i>
                                                                                Hiring</li>
                                                                            <li><i class="fas fa-door-open text-info"></i>
                                                                                Onboarding</li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="alert alert-success mt-2">
                                                                <h6><i class="fas fa-shield-check"></i> Smart Constraint
                                                                    Handling:</h6>
                                                                <p class="mb-0 small">
                                                                    <i class="fas fa-check-circle text-success"></i>
                                                                    Foreign key constraints automatically handled<br>
                                                                    <i class="fas fa-check-circle text-success"></i>
                                                                    Dependencies resolved in proper order<br>
                                                                    <i class="fas fa-check-circle text-success"></i> Safe
                                                                    truncation guaranteed
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Recruitment Bulk Operation -->
                                                    <div class="row mt-4">
                                                        <div class="col-12">
                                                            <div class="alert alert-warning border-warning">
                                                                <h5><i class="fas fa-bomb"></i> <strong>RECRUITMENT
                                                                        RESET</strong></h5>
                                                                <p class="mb-3">This will truncate <strong>ALL
                                                                        recruitment-related tables</strong> including
                                                                    requests, candidates, sessions, and all assessment
                                                                    stages.</p>
                                                                <form
                                                                    action="{{ route('debug.truncate.recruitment_all') }}"
                                                                    method="POST" style="display: inline;">
                                                                    @csrf
                                                                    <button type="submit"
                                                                        class="btn btn-danger btn-lg shadow"
                                                                        onclick="return confirm('⚠️ WARNING: This will truncate ALL recruitment tables and assessment data. This action is IRREVERSIBLE. Are you absolutely sure?')">
                                                                        <i class="fas fa-users-slash"></i> TRUNCATE ALL
                                                                        RECRUITMENT TABLES
                                                                    </button>
                                                                </form>
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
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
