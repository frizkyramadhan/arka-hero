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
                            <ul class="list-group list-group-flush" id="recent-leave-requests">
                                <li class="list-group-item text-center text-muted">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    Loading...
                                </li>
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
                            <ul class="list-group list-group-flush" id="recent-travels">
                                <li class="list-group-item text-center text-muted">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    Loading...
                                </li>
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
                            <div class="row" id="leave-entitlements">
                                <div class="col-12 text-center text-muted">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    Loading entitlements...
                                </div>
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
            loadDashboardData();
        });

        function loadDashboardData() {
            $('#loading-spinner').show();
            $('#error-alert').hide();

            $.ajax({
                url: '{{ route('api.personal.dashboard') }}',
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    $('#loading-spinner').hide();
                    populateDashboard(response);
                    $('#stats-row, #content-row, #entitlements-row, #actions-row').show();
                },
                error: function(xhr, status, error) {
                    $('#loading-spinner').hide();
                    $('#error-message').text('Failed to load dashboard data. Please refresh the page.');
                    $('#error-alert').show();
                    console.error('Dashboard API Error:', error);
                }
            });
        }

        function populateDashboard(data) {
            // Update stats
            $('#leave-total').text(data.leaveStats.total_requests);
            $('#leave-approved').text(data.leaveStats.approved_requests);
            $('#leave-progress').css('width', data.leaveStats.total_requests > 0 ?
                (data.leaveStats.approved_requests / data.leaveStats.total_requests) * 100 + '%' : '0%');

            $('#travel-total').text(data.travelStats.total_travels);
            $('#travel-upcoming').text(data.travelStats.upcoming_travels);
            $('#travel-progress').css('width', data.travelStats.total_travels > 0 ?
                (data.travelStats.upcoming_travels / data.travelStats.total_travels) * 100 + '%' : '0%');

            $('#recruitment-total').text(data.recruitmentStats.total_requests);
            $('#recruitment-approved').text(data.recruitmentStats.approved_requests);
            $('#recruitment-progress').css('width', data.recruitmentStats.total_requests > 0 ?
                (data.recruitmentStats.approved_requests / data.recruitmentStats.total_requests) * 100 + '%' : '0%');

            $('#approvals-total').text(data.pendingApprovals);
            $('#approvals-progress').css('width', data.pendingApprovals > 0 ? '100%' : '0%');

            if (data.pendingApprovals > 0) {
                $('#pending-approvals-badge').text(data.pendingApprovals).show();
            }

            // Update recent leave requests
            let leaveHtml = '';
            if (data.recentLeaveRequests.length > 0) {
                data.recentLeaveRequests.forEach(function(request) {
                    let badgeClass = 'badge-secondary';
                    if (request.status === 'approved') badgeClass = 'badge-success';
                    else if (request.status === 'pending') badgeClass = 'badge-warning';
                    else if (request.status === 'rejected') badgeClass = 'badge-danger';

                    leaveHtml += `
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${request.leave_type}</strong><br>
                            <small class="text-muted">${request.period} (${request.total_days} days)</small>
                        </div>
                        <span class="badge ${badgeClass}">${request.status.charAt(0).toUpperCase() + request.status.slice(1)}</span>
                    </div>
                </li>
            `;
                });
            } else {
                leaveHtml = '<li class="list-group-item text-center text-muted">No leave requests found</li>';
            }
            $('#recent-leave-requests').html(leaveHtml);

            // Update recent travels
            let travelHtml = '';
            if (data.recentTravels.length > 0) {
                data.recentTravels.forEach(function(travel) {
                    let badgeClass = 'badge-secondary';
                    if (travel.status === 'approved') badgeClass = 'badge-success';
                    else if (travel.status === 'submitted') badgeClass = 'badge-info';
                    else if (travel.status === 'rejected') badgeClass = 'badge-danger';

                    travelHtml += `
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${travel.destination}</strong><br>
                            <small class="text-muted">${travel.travel_date} - ${travel.project}</small>
                        </div>
                        <span class="badge ${badgeClass}">${travel.status.charAt(0).toUpperCase() + travel.status.slice(1)}</span>
                    </div>
                </li>
            `;
                });
            } else {
                travelHtml = '<li class="list-group-item text-center text-muted">No official travels found</li>';
            }
            $('#recent-travels').html(travelHtml);

            // Update leave entitlements
            let entitlementsHtml = '';
            if (data.leaveEntitlements.length > 0) {
                data.leaveEntitlements.forEach(function(entitlement) {
                    let progressWidth = entitlement.entitled > 0 ? (entitlement.remaining / entitlement.entitled) *
                        100 : 0;
                    let alertClass = '';
                    if (entitlement.is_expired) {
                        alertClass = 'border-danger';
                    } else if (entitlement.expires_soon) {
                        alertClass = 'border-warning';
                    }

                    entitlementsHtml += `
                <div class="col-md-4 mb-3">
                    <div class="card border-left-primary ${alertClass}">
                        <div class="card-body">
                            <h5 class="card-title">${entitlement.leave_type}</h5>
                            <p class="card-text">
                                <strong>Entitled:</strong> ${entitlement.entitled} days<br>
                                <strong>Used:</strong> ${entitlement.used} days<br>
                                <strong>Remaining:</strong> ${entitlement.remaining} days<br>
                                <small class="text-muted">Valid until: ${entitlement.period_end}</small>
                            </p>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: ${progressWidth}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                });
            } else {
                entitlementsHtml = `
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No active leave entitlements found.
                </div>
            </div>
        `;
            }
            $('#leave-entitlements').html(entitlementsHtml);
        }
    </script>
@endsection
