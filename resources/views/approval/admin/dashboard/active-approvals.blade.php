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
                        <li class="breadcrumb-item active">Active Approvals</li>
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
                        <h3>{{ $approvalStats['pending'] }}</h3>
                        <p>Pending Approvals</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $approvalStats['approved'] }}</h3>
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
                        <h3>{{ $approvalStats['rejected'] }}</h3>
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
                        <h3>{{ $approvalStats['total'] }}</h3>
                        <p>Total Approvals</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Bars -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie"></i>
                            Approval Status Distribution
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="progress-group">
                                    Pending
                                    <span
                                        class="float-right"><b>{{ $approvalStats['pending'] }}</b>/{{ $approvalStats['total'] }}</span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-warning"
                                            style="width: {{ $approvalStats['pending_percentage'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="progress-group">
                                    Approved
                                    <span
                                        class="float-right"><b>{{ $approvalStats['approved'] }}</b>/{{ $approvalStats['total'] }}</span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-success"
                                            style="width: {{ $approvalStats['approved_percentage'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="progress-group">
                                    Rejected
                                    <span
                                        class="float-right"><b>{{ $approvalStats['rejected'] }}</b>/{{ $approvalStats['total'] }}</span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-danger"
                                            style="width: {{ $approvalStats['rejected_percentage'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="progress-group">
                                    Cancelled
                                    <span
                                        class="float-right"><b>{{ $approvalStats['cancelled'] }}</b>/{{ $approvalStats['total'] }}</span>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-secondary"
                                            style="width: {{ $approvalStats['cancelled_percentage'] ?? 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Approvals Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i>
                            Active Approvals
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-primary" id="refreshApprovals">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select class="form-control" id="filterDocumentType">
                                    <option value="">All Document Types</option>
                                    <option value="officialtravel">Official Travel</option>
                                    <option value="recruitment_request">Recruitment Request</option>
                                    <option value="employee_registration">Employee Registration</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-control" id="filterFlow">
                                    <option value="">All Flows</option>
                                    @foreach ($activeApprovals->pluck('approvalFlow.name')->unique() as $flowName)
                                        <option value="{{ $flowName }}">{{ $flowName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-control" id="filterDays">
                                    <option value="">All Time</option>
                                    <option value="1">Last 24 Hours</option>
                                    <option value="7">Last 7 Days</option>
                                    <option value="30">Last 30 Days</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" id="searchApprovals" placeholder="Search...">
                            </div>
                        </div>

                        <!-- Approvals Table -->
                        <div class="table-responsive">
                            <table class="table table-hover" id="approvalsTable">
                                <thead>
                                    <tr>
                                        <th>Document</th>
                                        <th>Flow</th>
                                        <th>Current Stage</th>
                                        <th>Submitted By</th>
                                        <th>Submitted</th>
                                        <th>Days Pending</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activeApprovals as $approval)
                                        <tr data-approval-id="{{ $approval->id }}">
                                            <td>
                                                <div>
                                                    <strong>{{ ucfirst(str_replace('_', ' ', $approval->document_type)) }}</strong>
                                                    <br>
                                                    <small class="text-muted">ID: {{ $approval->document_id }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-info">{{ $approval->approvalFlow->name ?? 'Unknown' }}</span>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-primary">{{ $approval->currentStage->stage_name ?? 'Unknown' }}</span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $approval->submittedBy->name ?? 'Unknown' }}</strong>
                                                    <br>
                                                    <small
                                                        class="text-muted">{{ $approval->submittedBy->email ?? '' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $approval->submitted_at->format('M d, Y') }}
                                                    <br>
                                                    <small
                                                        class="text-muted">{{ $approval->submitted_at->format('H:i') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $daysPending = $approval->submitted_at->diffInDays(now());
                                                @endphp
                                                <span
                                                    class="badge badge-{{ $daysPending > 7 ? 'danger' : ($daysPending > 3 ? 'warning' : 'success') }}">
                                                    {{ $daysPending }} days
                                                </span>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge badge-{{ $approval->overall_status === 'pending' ? 'warning' : ($approval->overall_status === 'approved' ? 'success' : 'danger') }}">
                                                    {{ ucfirst($approval->overall_status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-info viewApproval"
                                                        data-approval-id="{{ $approval->id }}">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-warning escalateApproval"
                                                        data-approval-id="{{ $approval->id }}">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger cancelApproval"
                                                        data-approval-id="{{ $approval->id }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <p>No active approvals found</p>
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

        <!-- Overdue Approvals Alert -->
        @if ($activeApprovals->where('submitted_at', '<', now()->subDays(7))->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle"></i> Overdue Approvals</h5>
                        <p>There are {{ $activeApprovals->where('submitted_at', '<', now()->subDays(7))->count() }}
                            approvals that have been pending for more than 7 days. Consider escalating these approvals.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- View Approval Modal -->
    <div class="modal fade" id="viewApprovalModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye"></i>
                        Approval Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="approvalDetails">
                    <!-- Approval details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Escalate Approval Modal -->
    <div class="modal fade" id="escalateApprovalModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Escalate Approval
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="escalateApprovalForm">
                    <div class="modal-body">
                        <input type="hidden" id="escalate_approval_id" name="approval_id">
                        <div class="form-group">
                            <label for="escalation_reason">Escalation Reason</label>
                            <textarea class="form-control" id="escalation_reason" name="reason" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="escalation_priority">Priority</label>
                            <select class="form-control" id="escalation_priority" name="priority" required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-exclamation-triangle"></i> Escalate
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

            // Filter functionality
            $('#filterDocumentType, #filterFlow, #filterDays').change(function() {
                filterApprovals();
            });

            // Search functionality
            $('#searchApprovals').on('keyup', function() {
                filterApprovals();
            });

            // View approval details
            $(document).on('click', '.viewApproval', function() {
                const approvalId = $(this).data('approval-id');
                loadApprovalDetails(approvalId);
            });

            // Escalate approval
            $(document).on('click', '.escalateApproval', function() {
                const approvalId = $(this).data('approval-id');
                $('#escalate_approval_id').val(approvalId);
                $('#escalateApprovalModal').modal('show');
            });

            // Handle escalate form submission
            $('#escalateApprovalForm').submit(function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '/approval/admin/escalate-approval',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#escalateApprovalModal').modal('hide');
                            $('#escalateApprovalForm')[0].reset();
                            refreshApprovals();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to escalate approval');
                    }
                });
            });

            // Cancel approval
            $(document).on('click', '.cancelApproval', function() {
                const approvalId = $(this).data('approval-id');

                if (confirm('Are you sure you want to cancel this approval?')) {
                    $.ajax({
                        url: `/approval/admin/cancel-approval/${approvalId}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                refreshApprovals();
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function() {
                            toastr.error('Failed to cancel approval');
                        }
                    });
                }
            });

            function refreshApprovals() {
                $.ajax({
                    url: '{{ route('approval.admin.dashboard.active-approvals-data') }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            updateApprovalsTable(response.approvals);
                        }
                    },
                    error: function() {
                        console.log('Failed to refresh approvals');
                    }
                });
            }

            function updateApprovalsTable(approvals) {
                const tbody = $('#approvalsTable tbody');
                tbody.empty();

                if (approvals.length === 0) {
                    tbody.append(`
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>No active approvals found</p>
                    </td>
                </tr>
            `);
                    return;
                }

                approvals.forEach(approval => {
                    const daysPending = Math.floor((new Date() - new Date(approval.submitted_at)) / (1000 *
                        60 * 60 * 24));
                    const badgeClass = daysPending > 7 ? 'danger' : (daysPending > 3 ? 'warning' :
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
                        </div>
                    </td>
                    <td>
                        <div>
                            ${new Date(approval.submitted_at).toLocaleDateString()}
                            <br>
                            <small class="text-muted">${new Date(approval.submitted_at).toLocaleTimeString()}</small>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-${badgeClass}">${daysPending} days</span>
                    </td>
                    <td>
                        <span class="badge badge-warning">Pending</span>
                    </td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-info viewApproval" data-approval-id="${approval.id}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-warning escalateApproval" data-approval-id="${approval.id}">
                                <i class="fas fa-exclamation-triangle"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger cancelApproval" data-approval-id="${approval.id}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `);
                });
            }

            function filterApprovals() {
                const documentType = $('#filterDocumentType').val();
                const flow = $('#filterFlow').val();
                const days = $('#filterDays').val();
                const search = $('#searchApprovals').val().toLowerCase();

                $('#approvalsTable tbody tr').each(function() {
                    const row = $(this);
                    const documentTypeText = row.find('td:first').text().toLowerCase();
                    const flowText = row.find('td:nth-child(2)').text().toLowerCase();
                    const submittedText = row.find('td:nth-child(5)').text().toLowerCase();

                    let show = true;

                    if (documentType && !documentTypeText.includes(documentType.toLowerCase())) {
                        show = false;
                    }

                    if (flow && !flowText.includes(flow.toLowerCase())) {
                        show = false;
                    }

                    if (search && !row.text().toLowerCase().includes(search)) {
                        show = false;
                    }

                    if (days) {
                        const submittedDate = new Date(submittedText);
                        const daysAgo = new Date();
                        daysAgo.setDate(daysAgo.getDate() - parseInt(days));

                        if (submittedDate < daysAgo) {
                            show = false;
                        }
                    }

                    row.toggle(show);
                });
            }

            function loadApprovalDetails(approvalId) {
                $.ajax({
                    url: `/approval/admin/approval-details/${approvalId}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#approvalDetails').html(response.html);
                            $('#viewApprovalModal').modal('show');
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to load approval details');
                    }
                });
            }
        });
    </script>
@endsection

@section('styles')
    <style>
        .progress-group {
            margin-bottom: 20px;
        }

        .progress-group .progress {
            margin-top: 5px;
        }

        .badge {
            font-size: 0.8em;
        }

        .btn-group .btn {
            margin-right: 2px;
        }
    </style>
@endsection
