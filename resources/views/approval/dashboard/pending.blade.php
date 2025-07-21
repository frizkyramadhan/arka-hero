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
                        <li class="breadcrumb-item active">Pending Approvals</li>
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
                            Filter Pending Approvals
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
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
                                    <label for="filterPriority">Priority</label>
                                    <select class="form-control" id="filterPriority">
                                        <option value="">All Priorities</option>
                                        <option value="high">High</option>
                                        <option value="medium">Medium</option>
                                        <option value="low">Low</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filterDays">Days Pending</label>
                                    <select class="form-control" id="filterDays">
                                        <option value="">All</option>
                                        <option value="1">1 day</option>
                                        <option value="3">3 days</option>
                                        <option value="7">7 days</option>
                                        <option value="14">14+ days</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="searchApprovals">Search</label>
                                    <input type="text" class="form-control" id="searchApprovals"
                                        placeholder="Search by document ID, submitted by...">
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-secondary btn-block" id="clearFilters">
                                        <i class="fas fa-times"></i> Clear Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Approvals Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock"></i>
                            Pending Approvals
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-success" id="bulkApprove">
                                <i class="fas fa-check-double"></i> Bulk Approve
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" id="bulkReject">
                                <i class="fas fa-times-circle"></i> Bulk Reject
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" id="refreshApprovals">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="pendingApprovalsTable">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll">
                                        </th>
                                        <th>Document</th>
                                        <th>Flow</th>
                                        <th>Stage</th>
                                        <th>Submitted By</th>
                                        <th>Submitted</th>
                                        <th>Days Pending</th>
                                        <th>Priority</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendingApprovals as $approval)
                                        <tr data-approval-id="{{ $approval['id'] }}">
                                            <td>
                                                <input type="checkbox" class="approval-checkbox"
                                                    value="{{ $approval['id'] }}">
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ ucfirst(str_replace('_', ' ', $approval['document_type'])) }}</strong>
                                                    <br>
                                                    <small class="text-muted">ID: {{ $approval['document_id'] }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $approval['flow_name'] }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">{{ $approval['current_stage'] }}</span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $approval['submitted_by'] }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $approval['submitted_at'] }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    {{ \Carbon\Carbon::parse($approval['submitted_at'])->format('M d, Y') }}
                                                    <br>
                                                    <small
                                                        class="text-muted">{{ \Carbon\Carbon::parse($approval['submitted_at'])->format('H:i') }}</small>
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
                                                        <button type="button"
                                                            class="btn btn-sm btn-warning forwardApproval"
                                                            data-approval-id="{{ $approval['id'] }}">
                                                            <i class="fas fa-share"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-muted">No actions available</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <p>No pending approvals found</p>
                                                <small>You're all caught up!</small>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="totalPending">{{ count($pendingApprovals) }}</h3>
                        <p>Total Pending</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="highPriority">
                            {{ count(array_filter($pendingApprovals, function ($a) {return $a['priority'] === 'high';})) }}
                        </h3>
                        <p>High Priority</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="overdueCount">
                            {{ count(array_filter($pendingApprovals, function ($a) {return $a['days_pending'] > 7;})) }}
                        </h3>
                        <p>Overdue (>7 days)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="canProcess">
                            {{ count(array_filter($pendingApprovals, function ($a) {return $a['can_approve'];})) }}</h3>
                        <p>Can Process</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
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
            // Auto-refresh every 60 seconds
            setInterval(function() {
                refreshApprovals();
            }, 60000);

            // Refresh button
            $('#refreshApprovals').click(function() {
                refreshApprovals();
            });

            // Apply filters
            $('#applyFilters').click(function() {
                applyFilters();
            });

            // Clear filters
            $('#clearFilters').click(function() {
                clearFilters();
            });

            // Select all checkboxes
            $('#selectAll').change(function() {
                $('.approval-checkbox').prop('checked', $(this).is(':checked'));
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

            // Forward approval
            $(document).on('click', '.forwardApproval', function() {
                const approvalId = $(this).data('approval-id');
                showApprovalModal(approvalId, 'forward');
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
                            refreshApprovals();
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
                            refreshApprovals();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to process bulk actions');
                    }
                });
            });

            function refreshApprovals() {
                location.reload();
            }

            function applyFilters() {
                const filters = {
                    document_type: $('#filterDocumentType').val(),
                    flow: $('#filterFlow').val(),
                    priority: $('#filterPriority').val(),
                    days: $('#filterDays').val(),
                    search: $('#searchApprovals').val()
                };

                // Build query string
                const queryString = Object.keys(filters)
                    .filter(key => filters[key])
                    .map(key => `${key}=${encodeURIComponent(filters[key])}`)
                    .join('&');

                // Redirect with filters
                window.location.href = `{{ route('approval.dashboard.pending') }}?${queryString}`;
            }

            function clearFilters() {
                $('#filterDocumentType').val('');
                $('#filterFlow').val('');
                $('#filterPriority').val('');
                $('#filterDays').val('');
                $('#searchApprovals').val('');

                window.location.href = '{{ route('approval.dashboard.pending') }}';
            }

            function showApprovalModal(approvalId, action) {
                $('#approval_id').val(approvalId);
                $('#action_type').val(action);

                if (action === 'approve') {
                    $('#approvalActionTitle').html('<i class="fas fa-check"></i> Approve Approval');
                    $('#submitAction').removeClass('btn-danger').addClass('btn-success');
                    $('#forwardGroup, #delegateGroup').hide();
                } else if (action === 'reject') {
                    $('#approvalActionTitle').html('<i class="fas fa-times"></i> Reject Approval');
                    $('#submitAction').removeClass('btn-success').addClass('btn-danger');
                    $('#forwardGroup, #delegateGroup').hide();
                } else if (action === 'forward') {
                    $('#approvalActionTitle').html('<i class="fas fa-share"></i> Forward Approval');
                    $('#submitAction').removeClass('btn-danger btn-success').addClass('btn-info');
                    $('#forwardGroup').show();
                    $('#delegateGroup').hide();
                    loadUsers('#forward_to');
                }

                $('#approvalActionModal').modal('show');
            }

            function showBulkActionModal(approvals, action) {
                $('#selectedApprovalsList').empty();

                approvals.forEach(approvalId => {
                    const row = $(`tr[data-approval-id="${approvalId}"]`);
                    const documentType = row.find('td:nth-child(2) strong').text();
                    const flowName = row.find('td:nth-child(3) .badge').text();

                    $('#selectedApprovalsList').append(`
                <div class="alert alert-info">
                    <strong>${documentType}</strong> - ${flowName}
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
                $('.approval-checkbox:checked').each(function() {
                    approvals.push($(this).val());
                });
                return approvals;
            }

            function loadUsers(selectElement) {
                $.ajax({
                    url: '{{ route('users.list') }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $(selectElement).empty();
                            $(selectElement).append('<option value="">Select User</option>');

                            response.users.forEach(user => {
                                $(selectElement).append(
                                    `<option value="${user.id}">${user.name}</option>`);
                            });
                        }
                    },
                    error: function() {
                        toastr.error('Failed to load users');
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
    </style>
@endsection
