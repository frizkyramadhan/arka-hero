@extends('layouts.main')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $subtitle }}</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clipboard-list"></i>
                                Leave Request Monitoring
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Monitor all leave requests with detailed status tracking, approval workflow, and auto
                                conversion deadlines.</p>
                            <p><strong>Features:</strong> Status filtering, date range, employee/project filters, Excel
                                export</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('leave.reports.monitoring') }}" class="btn btn-primary">
                                <i class="fas fa-chart-bar"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-times-circle"></i>
                                Leave Cancellation Report
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Track leave cancellation requests (partial/full) and their impact on entitlements.</p>
                            <p><strong>Features:</strong> Cancellation status, days recovered, approval tracking, Excel
                                export</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('leave.reports.cancellation') }}" class="btn btn-warning">
                                <i class="fas fa-undo"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calculator"></i>
                                Leave Entitlement Detailed
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Complete breakdown of leave entitlements including deposit days, withdrawable days, and
                                effective usage.</p>
                            <p><strong>Features:</strong> Calculation details, carry over tracking, utilization percentage
                            </p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('leave.reports.entitlement-detailed') }}" class="btn btn-success">
                                <i class="fas fa-chart-pie"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock"></i>
                                Auto Conversion Tracking
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Monitor paid leave requests without supporting documents that will auto-convert to unpaid
                                leave.</p>
                            <p><strong>Features:</strong> Conversion deadlines, document compliance, alert system</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('leave.reports.auto-conversion') }}" class="btn btn-info">
                                <i class="fas fa-exclamation-triangle"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-building"></i>
                                Leave by Project Report
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Analyze leave usage patterns by project and department for workforce planning.</p>
                            <p><strong>Features:</strong> Project grouping, leave type breakdown, team availability</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('leave.reports.by-project') }}" class="btn btn-dark">
                                <i class="fas fa-sitemap"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
