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
                        <li class="breadcrumb-item"><a href="{{ route('approval.dashboard.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Approval History</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Filters -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-filter"></i>
                            Filter History
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filterAction">Action Type</label>
                                    <select class="form-control" id="filterAction">
                                        <option value="">All Actions</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                        <option value="forwarded">Forwarded</option>
                                        <option value="delegated">Delegated</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filterDocumentType">Document Type</label>
                                    <select class="form-control" id="filterDocumentType">
                                        <option value="">All Types</option>
                                        <option value="officialtravel">Official Travel</option>
                                        <option value="recruitment_request">Recruitment Request</option>
                                        <option value="employee_registration">Employee Registration</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filterDateRange">Date Range</label>
                                    <select class="form-control" id="filterDateRange">
                                        <option value="">All Time</option>
                                        <option value="today">Today</option>
                                        <option value="week">This Week</option>
                                        <option value="month">This Month</option>
                                        <option value="quarter">This Quarter</option>
                                        <option value="year">This Year</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="searchHistory">Search</label>
                                    <input type="text" class="form-control" id="searchHistory"
                                        placeholder="Search by document ID, comments...">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-primary" id="applyHistoryFilters">
                                    <i class="fas fa-search"></i> Apply Filters
                                </button>
                                <button type="button" class="btn btn-secondary" id="clearHistoryFilters">
                                    <i class="fas fa-times"></i> Clear Filters
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" class="btn btn-success" id="exportHistory">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="totalApproved">{{ $history->where('action', 'approved')->count() }}</h3>
                        <p>Approved</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="totalRejected">{{ $history->where('action', 'rejected')->count() }}</h3>
                        <p>Rejected</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="totalForwarded">{{ $history->where('action', 'forwarded')->count() }}</h3>
                        <p>Forwarded</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-share"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="totalDelegated">{{ $history->where('action', 'delegated')->count() }}</h3>
                        <p>Delegated</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval History Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i>
                            Approval History
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-info">{{ $history->total() }} total records</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($history->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="approvalHistoryTable">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Action</th>
                                            <th>Document</th>
                                            <th>Flow</th>
                                            <th>Stage</th>
                                            <th>Comments</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($history as $action)
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong>{{ \Carbon\Carbon::parse($action['action_date'])->format('M d, Y') }}</strong>
                                                        <br>
                                                        <small
                                                            class="text-muted">{{ \Carbon\Carbon::parse($action['action_date'])->format('H:i:s') }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge badge-{{ $action['action'] === 'approved' ? 'success' : ($action['action'] === 'rejected' ? 'danger' : 'info') }}">
                                                        {{ ucfirst($action['action']) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong>{{ ucfirst(str_replace('_', ' ', $action['document_type'])) }}</strong>
                                                        <br>
                                                        <small class="text-muted">ID:
                                                            {{ $action['documentApproval']['document_id'] ?? 'N/A' }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">{{ $action['flow_name'] }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary">{{ $action['stage'] }}</span>
                                                </td>
                                                <td>
                                                    @if ($action['comments'])
                                                        <span title="{{ $action['comments'] }}">
                                                            {{ Str::limit($action['comments'], 50) }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">No comments</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge badge-{{ $action['status'] === 'approved' ? 'success' : ($action['status'] === 'rejected' ? 'danger' : 'warning') }}">
                                                        {{ ucfirst($action['status']) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button"
                                                            class="btn btn-sm btn-info viewActionDetails"
                                                            data-action-id="{{ $action['id'] }}">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        @if ($action['documentApproval'])
                                                            <a href="{{ route('approval.dashboard.show', $action['documentApproval']['id']) }}"
                                                                class="btn btn-sm btn-primary">
                                                                <i class="fas fa-external-link-alt"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-3">
                                {{ $history->links() }}
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-history fa-3x mb-3"></i>
                                <p>No approval history found</p>
                                <small>Your approval history will appear here</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie"></i>
                            Actions Distribution
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="actionsChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line"></i>
                            Actions Timeline (Last 30 Days)
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="timelineChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Details Modal -->
    <div class="modal fade" id="actionDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle"></i>
                        Action Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="actionDetailsContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Apply history filters
            $('#applyHistoryFilters').click(function() {
                applyHistoryFilters();
            });

            // Clear history filters
            $('#clearHistoryFilters').click(function() {
                clearHistoryFilters();
            });

            // Export history
            $('#exportHistory').click(function() {
                exportHistory();
            });

            // View action details
            $(document).on('click', '.viewActionDetails', function() {
                const actionId = $(this).data('action-id');
                showActionDetails(actionId);
            });

            // Initialize charts
            initializeCharts();

            function applyHistoryFilters() {
                const filters = {
                    action: $('#filterAction').val(),
                    document_type: $('#filterDocumentType').val(),
                    date_range: $('#filterDateRange').val(),
                    search: $('#searchHistory').val()
                };

                // Build query string
                const queryString = Object.keys(filters)
                    .filter(key => filters[key])
                    .map(key => `${key}=${encodeURIComponent(filters[key])}`)
                    .join('&');

                // Redirect with filters
                window.location.href = `{{ route('approval.dashboard.history') }}?${queryString}`;
            }

            function clearHistoryFilters() {
                $('#filterAction').val('');
                $('#filterDocumentType').val('');
                $('#filterDateRange').val('');
                $('#searchHistory').val('');

                window.location.href = '{{ route('approval.dashboard.history') }}';
            }

            function exportHistory() {
                const filters = {
                    action: $('#filterAction').val(),
                    document_type: $('#filterDocumentType').val(),
                    date_range: $('#filterDateRange').val(),
                    search: $('#searchHistory').val()
                };

                // Build query string
                const queryString = Object.keys(filters)
                    .filter(key => filters[key])
                    .map(key => `${key}=${encodeURIComponent(filters[key])}`)
                    .join('&');

                // Download export
                window.location.href = `{{ route('approval.dashboard.export-history') }}?${queryString}`;
            }

            function showActionDetails(actionId) {
                $.ajax({
                    url: `{{ route('approval.dashboard.action-details', '') }}/${actionId}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#actionDetailsContent').html(response.html);
                            $('#actionDetailsModal').modal('show');
                        } else {
                            toastr.error('Failed to load action details');
                        }
                    },
                    error: function() {
                        toastr.error('Failed to load action details');
                    }
                });
            }

            function initializeCharts() {
                // Actions Distribution Chart
                const actionsCtx = document.getElementById('actionsChart').getContext('2d');
                new Chart(actionsCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Approved', 'Rejected', 'Forwarded', 'Delegated'],
                        datasets: [{
                            data: [
                                {{ $history->where('action', 'approved')->count() }},
                                {{ $history->where('action', 'rejected')->count() }},
                                {{ $history->where('action', 'forwarded')->count() }},
                                {{ $history->where('action', 'delegated')->count() }}
                            ],
                            backgroundColor: [
                                '#28a745',
                                '#dc3545',
                                '#17a2b8',
                                '#ffc107'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });

                // Timeline Chart (Last 30 days)
                const timelineCtx = document.getElementById('timelineChart').getContext('2d');

                // Generate last 30 days data
                const labels = [];
                const approvedData = [];
                const rejectedData = [];

                for (let i = 29; i >= 0; i--) {
                    const date = new Date();
                    date.setDate(date.getDate() - i);
                    labels.push(date.toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric'
                    }));

                    // Mock data - in real implementation, this would come from backend
                    approvedData.push(Math.floor(Math.random() * 10));
                    rejectedData.push(Math.floor(Math.random() * 5));
                }

                new Chart(timelineCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Approved',
                            data: approvedData,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            tension: 0.4
                        }, {
                            label: 'Rejected',
                            data: rejectedData,
                            borderColor: '#dc3545',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endsection

@section('styles')
    <style>
        .small-box {
            margin-bottom: 20px;
        }

        .badge {
            font-size: 0.8em;
        }

        .table th {
            background-color: #f8f9fa;
        }

        .pagination {
            margin-bottom: 0;
        }
    </style>
@endsection
