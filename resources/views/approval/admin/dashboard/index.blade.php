@extends('layouts.main')

@section('title', $title)

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('approval.flows.index') }}">Approval System</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_flows'] }}</h3>
                        <p>Total Approval Flows</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <a href="{{ route('approval.admin.dashboard.flows') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['pending_approvals'] }}</h3>
                        <p>Pending Approvals</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <a href="{{ route('approval.admin.dashboard.active-approvals') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['approved_today'] }}</h3>
                        <p>Approved Today</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="{{ route('approval.admin.dashboard.analytics') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['rejected_today'] }}</h3>
                        <p>Rejected Today</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <a href="{{ route('approval.admin.dashboard.analytics') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line"></i>
                            Performance Metrics
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-clock"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Avg Approval Time</span>
                                        <span class="info-box-number">{{ $performanceMetrics['avg_approval_time_hours'] }}
                                            hours</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-percentage"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Success Rate</span>
                                        <span
                                            class="info-box-number">{{ $performanceMetrics['success_rate_percent'] }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-list"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Completed</span>
                                        <span class="info-box-number">{{ $performanceMetrics['total_completed'] }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-check"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Approved</span>
                                        <span class="info-box-number">{{ $performanceMetrics['total_approved'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Bottleneck Analysis
                        </h3>
                    </div>
                    <div class="card-body">
                        @if (count($performanceMetrics['bottleneck_stages']) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Stage</th>
                                            <th>Flow</th>
                                            <th>Approvers</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($performanceMetrics['bottleneck_stages'] as $stage)
                                            <tr>
                                                <td>{{ $stage['stage_name'] }}</td>
                                                <td>{{ $stage['flow_name'] }}</td>
                                                <td>
                                                    <span
                                                        class="badge badge-warning">{{ $stage['approvers_count'] }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center">No bottlenecks detected</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Flows and Recent Activities -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i>
                            Active Approval Flows
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('approval.admin.dashboard.flows') }}" class="btn btn-sm btn-primary">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (count($activeFlows) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Flow Name</th>
                                            <th>Document Type</th>
                                            <th>Stages</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($activeFlows as $flow)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('approval.flows.show', $flow['id']) }}">
                                                        {{ $flow['name'] }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">{{ $flow['document_type'] }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary">{{ $flow['stages_count'] }}
                                                        stages</span>
                                                </td>
                                                <td>
                                                    @if ($flow['is_active'])
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>{{ $flow['created_at'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No active approval flows found</p>
                                <a href="{{ route('approval.flows.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create Flow
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i>
                            Recent Activities
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('approval.admin.dashboard.audit-trail') }}" class="btn btn-sm btn-primary">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if (count($recentActivities) > 0)
                            <div class="list-group list-group-flush">
                                @foreach ($recentActivities as $activity)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <span
                                                        class="badge badge-{{ $activity['action'] === 'approved' ? 'success' : ($activity['action'] === 'rejected' ? 'danger' : 'info') }} mr-2">
                                                        {{ ucfirst($activity['action']) }}
                                                    </span>
                                                    <small class="text-muted">{{ $activity['document_type'] }}</small>
                                                </div>
                                                <div class="text-sm">
                                                    <strong>{{ $activity['approver'] }}</strong> at
                                                    {{ $activity['stage'] }}
                                                </div>
                                                @if ($activity['comments'])
                                                    <small
                                                        class="text-muted">{{ Str::limit($activity['comments'], 50) }}</small>
                                                @endif
                                            </div>
                                            <small
                                                class="text-muted">{{ \Carbon\Carbon::parse($activity['action_date'])->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-history fa-2x mb-2"></i>
                                <p>No recent activities</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bolt"></i>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="{{ route('approval.flows.create') }}" class="btn btn-primary btn-block mb-2">
                                    <i class="fas fa-plus"></i> Create Flow
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('approval.admin.dashboard.active-approvals') }}"
                                    class="btn btn-warning btn-block mb-2">
                                    <i class="fas fa-clock"></i> Monitor Approvals
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('approval.admin.dashboard.analytics') }}"
                                    class="btn btn-info btn-block mb-2">
                                    <i class="fas fa-chart-bar"></i> View Analytics
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('approval.admin.dashboard.configuration') }}"
                                    class="btn btn-secondary btn-block mb-2">
                                    <i class="fas fa-cog"></i> System Config
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Auto-refresh dashboard stats every 30 seconds
            setInterval(function() {
                refreshDashboardStats();
            }, 30000);

            function refreshDashboardStats() {
                $.ajax({
                    url: '{{ route('approval.admin.dashboard.stats') }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            updateDashboardStats(response.stats, response.performance);
                        }
                    },
                    error: function() {
                        console.log('Failed to refresh dashboard stats');
                    }
                });
            }

            function updateDashboardStats(stats, performance) {
                // Update statistics cards
                $('.small-box.bg-info .inner h3').text(stats.total_flows);
                $('.small-box.bg-warning .inner h3').text(stats.pending_approvals);
                $('.small-box.bg-success .inner h3').text(stats.approved_today);
                $('.small-box.bg-danger .inner h3').text(stats.rejected_today);

                // Update performance metrics
                $('.info-box .info-box-number').each(function(index) {
                    if (index === 0) $(this).text(performance.avg_approval_time_hours + ' hours');
                    if (index === 1) $(this).text(performance.success_rate_percent + '%');
                    if (index === 2) $(this).text(performance.total_completed);
                    if (index === 3) $(this).text(performance.total_approved);
                });
            }
        });
    </script>
@endsection

@section('styles')
    <style>
        .info-box {
            margin-bottom: 15px;
        }

        .small-box {
            margin-bottom: 20px;
        }

        .list-group-item {
            border-left: none;
            border-right: none;
        }

        .list-group-item:first-child {
            border-top: none;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }
    </style>
@endsection
