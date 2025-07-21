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
                        <li class="breadcrumb-item active">Audit Trail</li>
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
                            Filter Audit Logs
                        </h3>
                    </div>
                    <div class="card-body">
                        <form id="auditFilterForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filterDateRange">Date Range</label>
                                        <select class="form-control" id="filterDateRange">
                                            <option value="">All Time</option>
                                            <option value="today">Today</option>
                                            <option value="yesterday">Yesterday</option>
                                            <option value="week">This Week</option>
                                            <option value="month">This Month</option>
                                            <option value="custom">Custom Range</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filterAction">Action Type</label>
                                        <select class="form-control" id="filterAction">
                                            <option value="">All Actions</option>
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                            <option value="forwarded">Forwarded</option>
                                            <option value="delegated">Delegated</option>
                                            <option value="escalated">Escalated</option>
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
                                        <label for="filterApprover">Approver</label>
                                        <input type="text" class="form-control" id="filterApprover"
                                            placeholder="Search approver...">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filterFlow">Approval Flow</label>
                                        <select class="form-control" id="filterFlow">
                                            <option value="">All Flows</option>
                                            <option value="linear">Linear Approval</option>
                                            <option value="parallel">Parallel Approval</option>
                                            <option value="conditional">Conditional Approval</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filterStage">Stage</label>
                                        <select class="form-control" id="filterStage">
                                            <option value="">All Stages</option>
                                            <option value="recommendation">Recommendation</option>
                                            <option value="approval">Approval</option>
                                            <option value="hr_review">HR Review</option>
                                            <option value="pm_approval">PM Approval</option>
                                            <option value="director_approval">Director Approval</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filterStatus">Status</label>
                                        <select class="form-control" id="filterStatus">
                                            <option value="">All Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn btn-primary btn-block" id="applyFilters">
                                            <i class="fas fa-search"></i> Apply Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Audit Trail Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i>
                            Audit Trail Logs
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-success" id="exportAuditLogs">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <button type="button" class="btn btn-sm btn-info" id="refreshAuditLogs">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="auditLogsTable">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Action</th>
                                        <th>Document</th>
                                        <th>Flow</th>
                                        <th>Stage</th>
                                        <th>Approver</th>
                                        <th>Comments</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($auditLogs as $log)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $log->action_date->format('M d, Y') }}</strong>
                                                    <br>
                                                    <small
                                                        class="text-muted">{{ $log->action_date->format('H:i:s') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-{{ $log->action === 'approved' ? 'success' : ($log->action === 'rejected' ? 'danger' : 'info') }}">
                                                    {{ ucfirst($log->action) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ ucfirst(str_replace('_', ' ', $log->documentApproval->document_type ?? 'Unknown')) }}</strong>
                                                    <br>
                                                    <small class="text-muted">ID:
                                                        {{ $log->documentApproval->document_id ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-info">{{ $log->documentApproval->approvalFlow->name ?? 'Unknown' }}</span>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-primary">{{ $log->approvalStage->stage_name ?? 'Unknown' }}</span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $log->approver->name ?? 'Unknown' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $log->approver->email ?? '' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($log->comments)
                                                    <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                                        title="{{ $log->comments }}">
                                                        {{ Str::limit($log->comments, 50) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">No comments</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-{{ $log->documentApproval->overall_status === 'approved' ? 'success' : ($log->documentApproval->overall_status === 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($log->documentApproval->overall_status ?? 'Unknown') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-info viewAuditDetails"
                                                        data-log-id="{{ $log->id }}">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    @if ($log->action === 'forwarded' && $log->forwarded_to)
                                                        <button type="button"
                                                            class="btn btn-sm btn-warning viewForwardDetails"
                                                            data-log-id="{{ $log->id }}">
                                                            <i class="fas fa-share"></i>
                                                        </button>
                                                    @endif
                                                    @if ($log->action === 'delegated' && $log->delegated_to)
                                                        <button type="button"
                                                            class="btn btn-sm btn-secondary viewDelegationDetails"
                                                            data-log-id="{{ $log->id }}">
                                                            <i class="fas fa-user-plus"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-history fa-3x mb-3"></i>
                                                <p>No audit logs found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if ($auditLogs->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $auditLogs->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="totalAuditLogs">{{ $auditLogs->total() }}</h3>
                        <p>Total Audit Logs</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-history"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="approvedActions">{{ $auditLogs->where('action', 'approved')->count() }}</h3>
                        <p>Approved Actions</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="rejectedActions">{{ $auditLogs->where('action', 'rejected')->count() }}</h3>
                        <p>Rejected Actions</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="otherActions">{{ $auditLogs->whereNotIn('action', ['approved', 'rejected'])->count() }}
                        </h3>
                        <p>Other Actions</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-ellipsis-h"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Details Modal -->
    <div class="modal fade" id="auditDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye"></i>
                        Audit Log Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="auditDetailsContent">
                    <!-- Audit details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Forward Details Modal -->
    <div class="modal fade" id="forwardDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-share"></i>
                        Forward Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="forwardDetailsContent">
                    <!-- Forward details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delegation Details Modal -->
    <div class="modal fade" id="delegationDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus"></i>
                        Delegation Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="delegationDetailsContent">
                    <!-- Delegation details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Apply filters
            $('#applyFilters').click(function() {
                applyAuditFilters();
            });

            // Refresh audit logs
            $('#refreshAuditLogs').click(function() {
                location.reload();
            });

            // Export audit logs
            $('#exportAuditLogs').click(function() {
                exportAuditLogs();
            });

            // View audit details
            $(document).on('click', '.viewAuditDetails', function() {
                const logId = $(this).data('log-id');
                loadAuditDetails(logId);
            });

            // View forward details
            $(document).on('click', '.viewForwardDetails', function() {
                const logId = $(this).data('log-id');
                loadForwardDetails(logId);
            });

            // View delegation details
            $(document).on('click', '.viewDelegationDetails', function() {
                const logId = $(this).data('log-id');
                loadDelegationDetails(logId);
            });

            function applyAuditFilters() {
                const filters = {
                    date_range: $('#filterDateRange').val(),
                    action: $('#filterAction').val(),
                    document_type: $('#filterDocumentType').val(),
                    approver: $('#filterApprover').val(),
                    flow: $('#filterFlow').val(),
                    stage: $('#filterStage').val(),
                    status: $('#filterStatus').val()
                };

                // Build query string
                const queryString = Object.keys(filters)
                    .filter(key => filters[key])
                    .map(key => `${key}=${encodeURIComponent(filters[key])}`)
                    .join('&');

                // Redirect with filters
                window.location.href = `{{ route('approval.admin.dashboard.audit-trail') }}?${queryString}`;
            }

            function loadAuditDetails(logId) {
                $.ajax({
                    url: `{{ route('approval.admin.dashboard.audit-details') }}/${logId}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#auditDetailsContent').html(response.html);
                            $('#auditDetailsModal').modal('show');
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to load audit details');
                    }
                });
            }

            function loadForwardDetails(logId) {
                $.ajax({
                    url: `{{ route('approval.admin.dashboard.forward-details') }}/${logId}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#forwardDetailsContent').html(response.html);
                            $('#forwardDetailsModal').modal('show');
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to load forward details');
                    }
                });
            }

            function loadDelegationDetails(logId) {
                $.ajax({
                    url: `{{ route('approval.admin.dashboard.delegation-details') }}/${logId}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#delegationDetailsContent').html(response.html);
                            $('#delegationDetailsModal').modal('show');
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to load delegation details');
                    }
                });
            }

            function exportAuditLogs() {
                const filters = {
                    date_range: $('#filterDateRange').val(),
                    action: $('#filterAction').val(),
                    document_type: $('#filterDocumentType').val(),
                    approver: $('#filterApprover').val(),
                    flow: $('#filterFlow').val(),
                    stage: $('#filterStage').val(),
                    status: $('#filterStatus').val()
                };

                const queryString = Object.keys(filters)
                    .filter(key => filters[key])
                    .map(key => `${key}=${encodeURIComponent(filters[key])}`)
                    .join('&');

                window.open(`{{ route('approval.admin.dashboard.export-audit-logs') }}?${queryString}`, '_blank');
            }

            // Auto-refresh every 5 minutes
            setInterval(function() {
                // Only refresh if no modal is open
                if (!$('.modal').hasClass('show')) {
                    location.reload();
                }
            }, 300000);
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

        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
@endsection
