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
                        <li class="breadcrumb-item"><a href="{{ route('approval.admin.dashboard.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Analytics</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Time Range Filter -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-filter"></i>
                            Filter Analytics
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="timeRange">Time Range</label>
                                <select class="form-control" id="timeRange">
                                    <option value="7" {{ $timeRange == 7 ? 'selected' : '' }}>Last 7 Days</option>
                                    <option value="30" {{ $timeRange == 30 ? 'selected' : '' }}>Last 30 Days</option>
                                    <option value="90" {{ $timeRange == 90 ? 'selected' : '' }}>Last 90 Days</option>
                                    <option value="365" {{ $timeRange == 365 ? 'selected' : '' }}>Last Year</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="documentType">Document Type</label>
                                <select class="form-control" id="documentType">
                                    <option value="">All Types</option>
                                    <option value="officialtravel">Official Travel</option>
                                    <option value="recruitment_request">Recruitment Request</option>
                                    <option value="employee_registration">Employee Registration</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="flowType">Flow Type</label>
                                <select class="form-control" id="flowType">
                                    <option value="">All Flows</option>
                                    <option value="linear">Linear</option>
                                    <option value="parallel">Parallel</option>
                                    <option value="conditional">Conditional</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-primary btn-block" id="applyFilters">
                                    <i class="fas fa-search"></i> Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="totalApprovals">0</h3>
                        <p>Total Approvals</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="avgApprovalTime">0</h3>
                        <p>Avg Time (Hours)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="successRate">0%</h3>
                        <p>Success Rate</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="bottleneckCount">0</h3>
                        <p>Bottlenecks</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line"></i>
                            Approval Trends
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="approvalTrendsChart" style="min-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie"></i>
                            Document Type Distribution
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="documentTypeChart" style="min-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Analysis -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar"></i>
                            Average Approval Time by Flow
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="avgTimeByFlowChart" style="min-height: 300px;"></canvas>
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
                        <div id="bottleneckAnalysis">
                            <!-- Bottleneck analysis will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Analytics Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table"></i>
                            Detailed Analytics
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-success" id="exportAnalytics">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="analyticsTable">
                                <thead>
                                    <tr>
                                        <th>Flow Name</th>
                                        <th>Document Type</th>
                                        <th>Total Approvals</th>
                                        <th>Approved</th>
                                        <th>Rejected</th>
                                        <th>Avg Time (Hours)</th>
                                        <th>Success Rate</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Analytics data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            let approvalTrendsChart, documentTypeChart, avgTimeByFlowChart;

            // Initialize charts
            initializeCharts();

            // Load initial analytics
            loadAnalytics();

            // Handle filter changes
            $('#applyFilters').click(function() {
                loadAnalytics();
            });

            // Handle export
            $('#exportAnalytics').click(function() {
                exportAnalytics();
            });

            function initializeCharts() {
                // Approval Trends Chart
                const trendsCtx = document.getElementById('approvalTrendsChart').getContext('2d');
                approvalTrendsChart = new Chart(trendsCtx, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Approved',
                            data: [],
                            borderColor: 'rgb(40, 167, 69)',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            tension: 0.1
                        }, {
                            label: 'Rejected',
                            data: [],
                            borderColor: 'rgb(220, 53, 69)',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            tension: 0.1
                        }, {
                            label: 'Pending',
                            data: [],
                            borderColor: 'rgb(255, 193, 7)',
                            backgroundColor: 'rgba(255, 193, 7, 0.1)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                // Document Type Chart
                const docTypeCtx = document.getElementById('documentTypeChart').getContext('2d');
                documentTypeChart = new Chart(docTypeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: [],
                        datasets: [{
                            data: [],
                            backgroundColor: [
                                'rgb(255, 99, 132)',
                                'rgb(54, 162, 235)',
                                'rgb(255, 205, 86)',
                                'rgb(75, 192, 192)',
                                'rgb(153, 102, 255)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });

                // Average Time by Flow Chart
                const avgTimeCtx = document.getElementById('avgTimeByFlowChart').getContext('2d');
                avgTimeByFlowChart = new Chart(avgTimeCtx, {
                    type: 'bar',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Average Time (Hours)',
                            data: [],
                            backgroundColor: 'rgba(54, 162, 235, 0.8)',
                            borderColor: 'rgb(54, 162, 235)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            function loadAnalytics() {
                const timeRange = $('#timeRange').val();
                const documentType = $('#documentType').val();
                const flowType = $('#flowType').val();

                $.ajax({
                    url: '{{ route('approval.admin.dashboard.analytics-data') }}',
                    type: 'GET',
                    data: {
                        range: timeRange,
                        document_type: documentType,
                        flow_type: flowType
                    },
                    success: function(response) {
                        if (response.success) {
                            updateAnalytics(response.analytics);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to load analytics data');
                    }
                });
            }

            function updateAnalytics(analytics) {
                // Update key metrics
                $('#totalApprovals').text(analytics.total_approvals || 0);
                $('#avgApprovalTime').text(analytics.avg_approval_time || 0);
                $('#successRate').text((analytics.success_rate || 0) + '%');
                $('#bottleneckCount').text(analytics.bottleneck_count || 0);

                // Update approval trends chart
                if (analytics.daily_trends) {
                    const labels = Object.keys(analytics.daily_trends);
                    const approvedData = labels.map(date => {
                        const dayData = analytics.daily_trends[date];
                        return dayData.find(item => item.overall_status === 'approved')?.count || 0;
                    });
                    const rejectedData = labels.map(date => {
                        const dayData = analytics.daily_trends[date];
                        return dayData.find(item => item.overall_status === 'rejected')?.count || 0;
                    });
                    const pendingData = labels.map(date => {
                        const dayData = analytics.daily_trends[date];
                        return dayData.find(item => item.overall_status === 'pending')?.count || 0;
                    });

                    approvalTrendsChart.data.labels = labels;
                    approvalTrendsChart.data.datasets[0].data = approvedData;
                    approvalTrendsChart.data.datasets[1].data = rejectedData;
                    approvalTrendsChart.data.datasets[2].data = pendingData;
                    approvalTrendsChart.update();
                }

                // Update document type chart
                if (analytics.document_type_distribution) {
                    const labels = analytics.document_type_distribution.map(item => item.document_type);
                    const data = analytics.document_type_distribution.map(item => item.count);

                    documentTypeChart.data.labels = labels;
                    documentTypeChart.data.datasets[0].data = data;
                    documentTypeChart.update();
                }

                // Update average time by flow chart
                if (analytics.avg_time_by_flow) {
                    const labels = analytics.avg_time_by_flow.map(item => item.flow_name);
                    const data = analytics.avg_time_by_flow.map(item => item.avg_time_hours);

                    avgTimeByFlowChart.data.labels = labels;
                    avgTimeByFlowChart.data.datasets[0].data = data;
                    avgTimeByFlowChart.update();
                }

                // Update bottleneck analysis
                updateBottleneckAnalysis(analytics.bottleneck_stages || []);

                // Update analytics table
                updateAnalyticsTable(analytics.detailed_analytics || []);
            }

            function updateBottleneckAnalysis(bottlenecks) {
                const container = $('#bottleneckAnalysis');

                if (bottlenecks.length === 0) {
                    container.html('<p class="text-muted text-center">No bottlenecks detected</p>');
                    return;
                }

                let html = '<div class="table-responsive"><table class="table table-sm">';
                html +=
                    '<thead><tr><th>Stage</th><th>Flow</th><th>Approvers</th><th>Avg Time</th></tr></thead><tbody>';

                bottlenecks.forEach(bottleneck => {
                    html += `
                <tr>
                    <td>${bottleneck.stage_name}</td>
                    <td>${bottleneck.flow_name}</td>
                    <td><span class="badge badge-warning">${bottleneck.approvers_count}</span></td>
                    <td>${bottleneck.avg_time_hours} hours</td>
                </tr>
            `;
                });

                html += '</tbody></table></div>';
                container.html(html);
            }

            function updateAnalyticsTable(analytics) {
                const tbody = $('#analyticsTable tbody');
                tbody.empty();

                analytics.forEach(item => {
                    const performanceClass = item.success_rate >= 80 ? 'success' : (item.success_rate >=
                        60 ? 'warning' : 'danger');

                    tbody.append(`
                <tr>
                    <td>${item.flow_name}</td>
                    <td><span class="badge badge-info">${item.document_type}</span></td>
                    <td>${item.total_approvals}</td>
                    <td><span class="badge badge-success">${item.approved_count}</span></td>
                    <td><span class="badge badge-danger">${item.rejected_count}</span></td>
                    <td>${item.avg_time_hours} hours</td>
                    <td><span class="badge badge-${performanceClass}">${item.success_rate}%</span></td>
                    <td>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-${performanceClass}" style="width: ${item.success_rate}%"></div>
                        </div>
                    </td>
                </tr>
            `);
                });
            }

            function exportAnalytics() {
                const timeRange = $('#timeRange').val();
                const documentType = $('#documentType').val();
                const flowType = $('#flowType').val();

                window.open(
                    `{{ route('approval.admin.dashboard.export-analytics') }}?range=${timeRange}&document_type=${documentType}&flow_type=${flowType}`,
                    '_blank');
            }
        });
    </script>
@endsection

@section('styles')
    <style>
        .small-box {
            margin-bottom: 20px;
        }

        .card {
            margin-bottom: 20px;
        }

        .progress {
            height: 20px;
        }

        .badge {
            font-size: 0.8em;
        }
    </style>
@endsection
