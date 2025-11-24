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
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.personal') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">{{ $subtitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Loading Spinner -->
            <div id="loading-spinner" class="text-center" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p>Loading dashboard data...</p>
            </div>

            <!-- Error Alert -->
            <div id="error-alert" class="alert alert-danger" style="display: none;">
                <i class="fas fa-exclamation-triangle"></i>
                <span id="error-message">Failed to load dashboard data. Please try again.</span>
            </div>

            <!-- Info boxes -->
            <div class="row" id="stats-row" style="display: none;">
                <!-- Leave Requests Stats -->
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Leave Requests</span>
                            <span class="info-box-number" id="leave-total">0</span>
                            <div class="progress">
                                <div class="progress-bar bg-info" id="leave-progress" style="width: 0%"></div>
                            </div>
                            <span class="progress-description">
                                <span id="leave-approved">0</span> Approved
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Official Travel Stats -->
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-plane"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Official Travels</span>
                            <span class="info-box-number" id="travel-total">0</span>
                            <div class="progress">
                                <div class="progress-bar bg-success" id="travel-progress" style="width: 0%"></div>
                            </div>
                            <span class="progress-description">
                                <span id="travel-upcoming">0</span> Upcoming
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Recruitment Requests Stats -->
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Recruitment Requests</span>
                            <span class="info-box-number" id="recruitment-total">0</span>
                            <div class="progress">
                                <div class="progress-bar bg-warning" id="recruitment-progress" style="width: 0%"></div>
                            </div>
                            <span class="progress-description">
                                <span id="recruitment-approved">0</span> Approved
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pending Approvals</span>
                            <span class="info-box-number" id="approvals-total">0</span>
                            <div class="progress">
                                <div class="progress-bar bg-danger" id="approvals-progress" style="width: 0%"></div>
                            </div>
                            <span class="progress-description">
                                Requires Action
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Row -->
            <div class="row" id="content-row" style="display: none;">
                <!-- Recent Leave Requests -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Leave Requests</h3>
                            <div class="card-tools">
                                <a href="{{ route('leave.requests.my-requests') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View All
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($recentLeaveRequests as $request)
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $request['leave_type'] }}</strong><br>
                                                <small class="text-muted">{{ $request['period'] }} -
                                                    {{ $request['days'] }}</small>
                                            </div>
                                            <span
                                                class="badge {{ $request['status'] === 'approved' ? 'badge-success' : ($request['status'] === 'pending' ? 'badge-warning' : 'badge-secondary') }}">
                                                {{ ucfirst($request['status']) }}
                                            </span>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-muted">No leave requests found</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Recent Official Travels -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Official Travels</h3>
                            <div class="card-tools">
                                <a href="{{ route('officialtravels.my-travels') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View All
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($recentTravels as $travel)
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $travel['destination'] }}</strong><br>
                                                <small class="text-muted">{{ $travel['travel_date'] }} -
                                                    {{ $travel['project'] }}</small>
                                            </div>
                                            <span
                                                class="badge {{ $travel['status'] === 'approved' ? 'badge-success' : ($travel['status'] === 'submitted' ? 'badge-info' : ($travel['status'] === 'rejected' ? 'badge-danger' : 'badge-secondary')) }}">
                                                {{ ucfirst($travel['status']) }}
                                            </span>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-muted">No official travels found</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Entitlements -->
            <div class="row" id="entitlements-row" style="display: none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Current Leave Entitlements</h3>
                            <div class="card-tools">
                                <a href="{{ route('leave.requests.my-entitlements') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @forelse($leaveEntitlements as $entitlement)
                                    <div class="col-md-4 mb-3">
                                        <div
                                            class="card border-left-primary {{ $entitlement['is_expired'] ? 'border-danger' : ($entitlement['expires_soon'] ? 'border-warning' : '') }}">
                                            <div class="card-body">
                                                <h5 class="card-title">{{ $entitlement['leave_type'] }}</h5>
                                                <p class="card-text">
                                                    <strong>Entitled:</strong> {{ $entitlement['entitled'] }} days<br>
                                                    <strong>Used:</strong> {{ $entitlement['used'] }} days<br>
                                                    <strong>Remaining:</strong> {{ $entitlement['remaining'] }} days<br>
                                                    <small class="text-muted">Valid until:
                                                        {{ $entitlement['period_end'] }}</small>
                                                </p>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success"
                                                        style="width: {{ $entitlement['entitled'] > 0 ? ($entitlement['remaining'] / $entitlement['entitled']) * 100 : 0 }}%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> No active leave entitlements found.
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row" id="actions-row" style="display: none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @can('personal.leave.create-own')
                                    <div class="col-md-3 mb-3">
                                        <a href="{{ route('leave.requests.create') }}" class="btn btn-info btn-block">
                                            <i class="fas fa-calendar-plus"></i><br>
                                            Request Leave
                                        </a>
                                    </div>
                                @endcan

                                @can('personal.official-travel.create-own')
                                    <div class="col-md-3 mb-3">
                                        <a href="{{ route('officialtravels.create') }}" class="btn btn-success btn-block">
                                            <i class="fas fa-plane"></i><br>
                                            Official Travel
                                        </a>
                                    </div>
                                @endcan

                                @can('personal.recruitment.create-own')
                                    <div class="col-md-3 mb-3">
                                        <a href="{{ route('recruitment.requests.create') }}"
                                            class="btn btn-warning btn-block">
                                            <i class="fas fa-users"></i><br>
                                            Recruitment Request
                                        </a>
                                    </div>
                                @endcan

                                @can('personal.approval.view-pending')
                                    <div class="col-md-3 mb-3">
                                        <a href="{{ route('approval.requests.index') }}" class="btn btn-danger btn-block">
                                            <i class="fas fa-check-circle"></i><br>
                                            Pending Approvals
                                            <span id="pending-approvals-badge" class="badge badge-light"
                                                style="display: none;">0</span>
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Update stats with proper JSON encoding
            var leaveStats = @json($leaveStats);
            var travelStats = @json($travelStats);
            var recruitmentStats = @json($recruitmentStats);
            var pendingApprovals = @json($pendingApprovals);

            $('#leave-total').text(leaveStats.total_requests);
            $('#leave-approved').text(leaveStats.approved_requests);
            $('#leave-progress').css('width', leaveStats.total_requests > 0 ?
                (leaveStats.approved_requests / leaveStats.total_requests) * 100 + '%' : '0%');

            $('#travel-total').text(travelStats.total_travels);
            $('#travel-upcoming').text(travelStats.upcoming_travels);
            $('#travel-progress').css('width', travelStats.total_travels > 0 ?
                (travelStats.upcoming_travels / travelStats.total_travels) * 100 + '%' : '0%');

            $('#recruitment-total').text(recruitmentStats.total_requests);
            $('#recruitment-approved').text(recruitmentStats.approved_requests);
            $('#recruitment-progress').css('width', recruitmentStats.total_requests > 0 ?
                (recruitmentStats.approved_requests / recruitmentStats.total_requests) * 100 + '%' : '0%');

            $('#approvals-total').text(pendingApprovals);
            $('#approvals-progress').css('width', pendingApprovals > 0 ? '100%' : '0%');

            // Show all content since data is already loaded
            $('#stats-row, #content-row, #entitlements-row, #actions-row').show();

            @if($pendingApprovals > 0)
                $('#pending-approvals-badge').text(pendingApprovals).show();
            @endif
        });
    </script>
@endsection
