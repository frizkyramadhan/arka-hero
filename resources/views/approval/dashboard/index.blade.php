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
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Approval Dashboard</li>
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
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['pending_count'] }}</h3>
                        <p>Pending Approvals</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <a href="{{ route('approval.dashboard.pending') }}" class="small-box-footer">
                        View All <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['approved_count'] }}</h3>
                        <p>Approved (30 days)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="{{ route('approval.dashboard.history') }}" class="small-box-footer">
                        View History <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['rejected_count'] }}</h3>
                        <p>Rejected (30 days)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <a href="{{ route('approval.dashboard.history') }}" class="small-box-footer">
                        View History <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['approval_rate'] }}%</h3>
                        <p>Approval Rate</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <a href="{{ route('approval.dashboard.history') }}" class="small-box-footer">
                        View Details <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Pending Approvals and Recent Actions -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i>
                            Pending Approvals
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-primary" id="refreshPending">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (count($pendingApprovals) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover" id="pendingApprovalsTable">
                                    <thead>
                                        <tr>
                                            <th>Document</th>
                                            <th>Flow</th>
                                            <th>Stage</th>
                                            <th>Submitted By</th>
                                            <th>Submitted</th>
                                            <th>Priority</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pendingApprovals as $approval)
                                            <tr data-approval-id="{{ $approval['id'] }}">
                                                <td>
                                                    <div>
                                                        <strong>{{ ucfirst(str_replace('_', ' ', $approval['document_type'])) }}</strong>
                                                        <br>
                                                        <small class="text-muted">ID:
                                                            {{ $approval['document_id'] }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">{{ $approval['flow_name'] }}</span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge badge-primary">{{ $approval['current_stage'] }}</span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong>{{ $approval['submitted_by'] }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $approval['submitted_at'] }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $daysPending = $approval['days_pending'];
                                                    @endphp
                                                    <span
                                                        class="badge badge-{{ $daysPending > 7 ? 'danger' : ($daysPending > 3 ? 'warning' : 'success') }}">
                                                        {{ $daysPending }} days
                                                    </span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge badge-{{ $approval['priority'] === 'high' ? 'danger' : ($approval['priority'] === 'medium' ? 'warning' : 'success') }}">
                                                        {{ ucfirst($approval['priority']) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($approval['can_approve'])
                                                        <div class="btn-group">
                                                            <button type="button"
                                                                class="btn btn-sm btn-success approveApproval"
                                                                data-approval-id="{{ $approval['id'] }}">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger rejectApproval"
                                                                data-approval-id="{{ $approval['id'] }}">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-info viewApproval"
                                                                data-approval-id="{{ $approval['id'] }}">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">No actions available</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No pending approvals found</p>
                                <small>You're all caught up!</small>
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
                            Recent Actions
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('approval.dashboard.history') }}" class="btn btn-sm btn-primary">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if (count($recentActions) > 0)
                            <div class="list-group list-group-flush">
                                @foreach ($recentActions as $action)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <span
                                                        class="badge badge-{{ $action['action'] === 'approved' ? 'success' : ($action['action'] === 'rejected' ? 'danger' : 'info') }} mr-2">
                                                        {{ ucfirst($action['action']) }}
                                                    </span>
                                                    <small class="text-muted">{{ $action['document_type'] }}</small>
                                                </div>
                                                <div class="text-sm">
                                                    <strong>{{ $action['flow_name'] }}</strong> at {{ $action['stage'] }}
                                                </div>
                                                @if ($action['comments'])
                                                    <small
                                                        class="text-muted">{{ Str::limit($action['comments'], 50) }}</small>
                                                @endif
                                            </div>
                                            <small
                                                class="text-muted">{{ \Carbon\Carbon::parse($action['action_date'])->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-history fa-2x mb-2"></i>
                                <p>No recent actions</p>
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
                                <button type="button" class="btn btn-success btn-block mb-2" id="bulkApprove">
                                    <i class="fas fa-check-double"></i> Bulk Approve
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-danger btn-block mb-2" id="bulkReject">
                                    <i class="fas fa-times-circle"></i> Bulk Reject
                                </button>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('approval.dashboard.pending') }}"
                                    class="btn btn-warning btn-block mb-2">
                                    <i class="fas fa-clock"></i> View All Pending
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('approval.dashboard.history') }}" class="btn btn-info btn-block mb-2">
                                    <i class="fas fa-history"></i> View History
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval History Summary -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar"></i>
                            Recent Approval History
                        </h3>
                    </div>
                    <div class="card-body">
                        @if (count($recentHistory) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Action</th>
                                            <th>Document</th>
                                            <th>Flow</th>
                                            <th>Stage</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentHistory as $history)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($history['action_date'])->format('M d, Y H:i') }}
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge badge-{{ $history['action'] === 'approved' ? 'success' : ($history['action'] === 'rejected' ? 'danger' : 'info') }}">
                                                        {{ ucfirst($history['action']) }}
                                                    </span>
                                                </td>
                                                <td>{{ ucfirst(str_replace('_', ' ', $history['document_type'])) }}</td>
                                                <td>{{ $history['flow_name'] }}</td>
                                                <td>{{ $history['stage'] }}</td>
                                                <td>
                                                    <span
                                                        class="badge badge-{{ $history['status'] === 'approved' ? 'success' : ($history['status'] === 'rejected' ? 'danger' : 'warning') }}">
                                                        {{ ucfirst($history['status']) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center">No recent approval history</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Action Modal -->
    <div class="modal fade" id="approvalActionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approvalActionTitle">
                        <i class="fas fa-check"></i>
                        Process Approval
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="approvalActionForm">
                    <div class="modal-body">
                        <input type="hidden" id="approval_id" name="approval_id">
                        <input type="hidden" id="action_type" name="action">

                        <div class="form-group">
                            <label for="comments">Comments (Optional)</label>
                            <textarea class="form-control" id="comments" name="comments" rows="3"
                                placeholder="Enter your comments here..."></textarea>
                        </div>

                        <div class="form-group" id="forwardGroup" style="display: none;">
                            <label for="forward_to">Forward To</label>
                            <select class="form-control" id="forward_to" name="forward_to">
                                <option value="">Select User</option>
                                <!-- Users will be loaded via AJAX -->
                            </select>
                        </div>

                        <div class="form-group" id="delegateGroup" style="display: none;">
                            <label for="delegate_to">Delegate To</label>
                            <select class="form-control" id="delegate_to" name="delegate_to">
                                <option value="">Select User</option>
                                <!-- Users will be loaded via AJAX -->
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitAction">
                            <i class="fas fa-save"></i> Process
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Action Modal -->
    <div class="modal fade" id="bulkActionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkActionTitle">
                        <i class="fas fa-check-double"></i>
                        Bulk Action
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="bulkActionForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Selected Approvals</label>
                            <div id="selectedApprovalsList">
                                <!-- Selected approvals will be listed here -->
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="bulk_comments">Comments (Optional)</label>
                            <textarea class="form-control" id="bulk_comments" name="comments" rows="3"
                                placeholder="Enter your comments here..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBulkAction">
                            <i class="fas fa-save"></i> Process All
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Auto-refresh pending approvals every 60 seconds
            setInterval(function() {
                refreshPendingApprovals();
            }, 60000);

            // Refresh button
            $('#refreshPending').click(function() {
                refreshPendingApprovals();
            });

            // Approve approval
            $(document).on('click', '.approveApproval', function() {
                const approvalId = $(this).data('approval-id');
                showApprovalModal(approvalId, 'approve');
            });

            // Reject approval
            $(document).on('click', '.rejectApproval', function() {
                const approvalId = $(this).data('approval-id');
                showApprovalModal(approvalId, 'reject');
            });

            // View approval
            $(document).on('click', '.viewApproval', function() {
                const approvalId = $(this).data('approval-id');
                window.location.href = `{{ route('approval.dashboard.show', '') }}/${approvalId}`;
            });

            // Bulk approve
            $('#bulkApprove').click(function() {
                const selectedApprovals = getSelectedApprovals();
                if (selectedApprovals.length === 0) {
                    toastr.warning('Please select approvals to process');
                    return;
                }
                showBulkActionModal(selectedApprovals, 'approve');
            });

            // Bulk reject
            $('#bulkReject').click(function() {
                const selectedApprovals = getSelectedApprovals();
                if (selectedApprovals.length === 0) {
                    toastr.warning('Please select approvals to process');
                    return;
                }
                showBulkActionModal(selectedApprovals, 'reject');
            });

            // Handle approval action form submission
            $('#approvalActionForm').submit(function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const approvalId = $('#approval_id').val();

                $.ajax({
                    url: `{{ route('approval.dashboard.process', '') }}/${approvalId}`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#approvalActionModal').modal('hide');
                            $('#approvalActionForm')[0].reset();
                            refreshPendingApprovals();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to process approval');
                    }
                });
            });

            // Handle bulk action form submission
            $('#bulkActionForm').submit(function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const selectedApprovals = getSelectedApprovals();

                formData.append('approval_ids', JSON.stringify(selectedApprovals));
                formData.append('action', $('#bulkActionTitle').data('action'));

                $.ajax({
                    url: '{{ route('approval.dashboard.bulk') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#bulkActionModal').modal('hide');
                            $('#bulkActionForm')[0].reset();
                            refreshPendingApprovals();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to process bulk actions');
                    }
                });
            });

            function refreshPendingApprovals() {
                $.ajax({
                    url: '{{ route('approval.dashboard.pending-data') }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            updatePendingApprovalsTable(response.approvals);
                        }
                    },
                    error: function() {
                        console.log('Failed to refresh pending approvals');
                    }
                });
            }

            function updatePendingApprovalsTable(approvals) {
                const tbody = $('#pendingApprovalsTable tbody');
                tbody.empty();

                if (approvals.length === 0) {
                    tbody.append(`
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>No pending approvals found</p>
                        <small>You're all caught up!</small>
                    </td>
                </tr>
            `);
                    return;
                }

                approvals.forEach(approval => {
                    const daysPending = approval.days_pending;
                    const priorityClass = approval.priority === 'high' ? 'danger' : (approval.priority ===
                        'medium' ? 'warning' : 'success');
                    const daysClass = daysPending > 7 ? 'danger' : (daysPending > 3 ? 'warning' :
                    'success');

                    tbody.append(`
                <tr data-approval-id="${approval.id}">
                    <td>
                        <div>
                            <strong>${approval.document_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</strong>
                            <br>
                            <small class="text-muted">ID: ${approval.document_id}</small>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-info">${approval.flow_name}</span>
                    </td>
                    <td>
                        <span class="badge badge-primary">${approval.current_stage}</span>
                    </td>
                    <td>
                        <div>
                            <strong>${approval.submitted_by}</strong>
                            <br>
                            <small class="text-muted">${approval.submitted_at}</small>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-${daysClass}">${daysPending} days</span>
                    </td>
                    <td>
                        <span class="badge badge-${priorityClass}">${approval.priority.charAt(0).toUpperCase() + approval.priority.slice(1)}</span>
                    </td>
                    <td>
                        ${approval.can_approve ? `
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-success approveApproval" data-approval-id="${approval.id}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger rejectApproval" data-approval-id="${approval.id}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-info viewApproval" data-approval-id="${approval.id}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            ` : '<span class="text-muted">No actions available</span>'}
                    </td>
                </tr>
            `);
                });
            }

            function showApprovalModal(approvalId, action) {
                $('#approval_id').val(approvalId);
                $('#action_type').val(action);

                if (action === 'approve') {
                    $('#approvalActionTitle').html('<i class="fas fa-check"></i> Approve Approval');
                    $('#submitAction').removeClass('btn-danger').addClass('btn-success');
                } else if (action === 'reject') {
                    $('#approvalActionTitle').html('<i class="fas fa-times"></i> Reject Approval');
                    $('#submitAction').removeClass('btn-success').addClass('btn-danger');
                }

                $('#approvalActionModal').modal('show');
            }

            function showBulkActionModal(approvals, action) {
                $('#selectedApprovalsList').empty();

                approvals.forEach(approval => {
                    $('#selectedApprovalsList').append(`
                <div class="alert alert-info">
                    <strong>${approval.document_type}</strong> - ${approval.flow_name}
                </div>
            `);
                });

                if (action === 'approve') {
                    $('#bulkActionTitle').html('<i class="fas fa-check-double"></i> Bulk Approve');
                    $('#submitBulkAction').removeClass('btn-danger').addClass('btn-success');
                } else if (action === 'reject') {
                    $('#bulkActionTitle').html('<i class="fas fa-times-circle"></i> Bulk Reject');
                    $('#submitBulkAction').removeClass('btn-success').addClass('btn-danger');
                }

                $('#bulkActionTitle').data('action', action);
                $('#bulkActionModal').modal('show');
            }

            function getSelectedApprovals() {
                const approvals = [];
                $('#pendingApprovalsTable tbody tr').each(function() {
                    const approvalId = $(this).data('approval-id');
                    if (approvalId) {
                        approvals.push(approvalId);
                    }
                });
                return approvals;
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
