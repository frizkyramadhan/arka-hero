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
                        <li class="breadcrumb-item"><a href="{{ route('approval.dashboard.pending') }}">Pending Approvals</a>
                        </li>
                        <li class="breadcrumb-item active">Approval Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Approval Status Banner -->
        <div class="row">
            <div class="col-12">
                <div
                    class="alert alert-{{ $approval->overall_status === 'approved' ? 'success' : ($approval->overall_status === 'rejected' ? 'danger' : 'warning') }} alert-dismissible">
                    <h5><i class="icon fas fa-info"></i> Approval Status</h5>
                    <strong>{{ ucfirst($approval->overall_status) }}</strong> -
                    @if ($approval->overall_status === 'pending')
                        This document is currently pending approval at stage:
                        <strong>{{ $approval->currentStage->stage_name ?? 'Unknown' }}</strong>
                    @elseif($approval->overall_status === 'approved')
                        This document has been approved on {{ $approval->completed_at->format('M d, Y H:i:s') }}
                    @elseif($approval->overall_status === 'rejected')
                        This document was rejected on {{ $approval->completed_at->format('M d, Y H:i:s') }}
                    @else
                        This document is {{ $approval->overall_status }}
                    @endif
                </div>
            </div>
        </div>

        <!-- Document Information and Statistics -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-alt"></i>
                            Document Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Document Type:</strong></td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $approval->document_type)) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Document ID:</strong></td>
                                        <td>{{ $approval->document_id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Approval Flow:</strong></td>
                                        <td>{{ $approval->approvalFlow->name ?? 'Unknown' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Submitted By:</strong></td>
                                        <td>{{ $approval->submittedBy->name ?? 'Unknown' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Submitted At:</strong></td>
                                        <td>{{ $approval->submitted_at->format('M d, Y H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Current Stage:</strong></td>
                                        <td>{{ $approval->currentStage->stage_name ?? 'Unknown' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Days Pending:</strong></td>
                                        <td>{{ $approval->submitted_at->diffInDays(now()) }} days</td>
                                    </tr>
                                    @if ($approval->completed_at)
                                        <tr>
                                            <td><strong>Completed At:</strong></td>
                                            <td>{{ $approval->completed_at->format('M d, Y H:i:s') }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>

                        <!-- Document Details -->
                        <div id="documentDetails">
                            <!-- Document details will be loaded via AJAX -->
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p>Loading document details...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar"></i>
                            Approval Statistics
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-clock"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Days Pending</span>
                                        <span class="info-box-number">{{ $stats['days_pending'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Approved</span>
                                        <span class="info-box-number">{{ $stats['approved_actions'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Rejected</span>
                                        <span class="info-box-number">{{ $stats['rejected_actions'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-share"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Forwarded</span>
                                        <span class="info-box-number">{{ $stats['forwarded_actions'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-percentage"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Approval Rate</span>
                                        <span class="info-box-number">{{ $stats['approval_rate'] ?? 0 }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Flow Progress -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-project-diagram"></i>
                            Approval Flow Progress
                        </h3>
                    </div>
                    <div class="card-body">
                        @if ($approval->approvalFlow && $approval->approvalFlow->stages)
                            <div class="approval-flow-progress">
                                @php
                                    $stages = $approval->approvalFlow->stages->sortBy('stage_order');
                                    $currentStageIndex = $stages->search(function ($stage) use ($approval) {
                                        return $stage->id === $approval->current_stage_id;
                                    });
                                @endphp

                                @foreach ($stages as $index => $stage)
                                    @php
                                        $isCompleted = $index < $currentStageIndex;
                                        $isCurrent = $index === $currentStageIndex;
                                        $isPending = $index > $currentStageIndex;
                                    @endphp

                                    <div
                                        class="stage-item {{ $isCompleted ? 'completed' : ($isCurrent ? 'current' : 'pending') }}">
                                        <div class="stage-icon">
                                            @if ($isCompleted)
                                                <i class="fas fa-check-circle text-success"></i>
                                            @elseif($isCurrent)
                                                <i class="fas fa-clock text-warning"></i>
                                            @else
                                                <i class="fas fa-circle text-muted"></i>
                                            @endif
                                        </div>
                                        <div class="stage-content">
                                            <h5 class="stage-title">{{ $stage->stage_name }}</h5>
                                            <p class="stage-description">
                                                @if ($stage->approvers->count() > 0)
                                                    <strong>Approvers:</strong>
                                                    @foreach ($stage->approvers as $approver)
                                                        @if ($approver->user)
                                                            {{ $approver->user->name }}
                                                        @elseif($approver->role)
                                                            {{ $approver->role->name }} (Role)
                                                        @elseif($approver->department)
                                                            {{ $approver->department->name }} (Dept)
                                                        @endif
                                                        @if (!$loop->last)
                                                            ,
                                                        @endif
                                                    @endforeach
                                                @else
                                                    No approvers assigned
                                                @endif
                                            </p>
                                            @if ($stage->escalation_hours)
                                                <small class="text-muted">
                                                    Escalation: {{ $stage->escalation_hours }} hours
                                                </small>
                                            @endif
                                        </div>
                                        @if (!$loop->last)
                                            <div class="stage-connector"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No approval flow stages found.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Actions -->
        @if ($approval->overall_status === 'pending' && auth()->user()->can('process', $approval))
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-tasks"></i>
                                Approval Actions
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-success btn-block mb-2"
                                        onclick="showActionModal('approve')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-danger btn-block mb-2"
                                        onclick="showActionModal('reject')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-info btn-block mb-2"
                                        onclick="showActionModal('forward')">
                                        <i class="fas fa-share"></i> Forward
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-warning btn-block mb-2"
                                        onclick="showActionModal('delegate')">
                                        <i class="fas fa-user-friends"></i> Delegate
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-secondary btn-block mb-2"
                                        onclick="showActionModal('request_info')">
                                        <i class="fas fa-question-circle"></i> Request Info
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-dark btn-block mb-2"
                                        onclick="showActionModal('escalate')">
                                        <i class="fas fa-exclamation-triangle"></i> Escalate
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-light btn-block mb-2"
                                        onclick="showActionModal('cancel')">
                                        <i class="fas fa-ban"></i> Cancel
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <a href="{{ route('approval.dashboard.index') }}"
                                        class="btn btn-outline-secondary btn-block mb-2">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Next Approvers -->
        @if (count($nextApprovers) > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users"></i>
                                Next Approvers
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach ($nextApprovers as $approver)
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">{{ ucfirst($approver['type']) }}</span>
                                                <span class="info-box-number">{{ $approver['name'] }}</span>
                                                @if ($approver['is_backup'])
                                                    <span class="badge badge-warning">Backup</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Approval History -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i>
                            Approval History
                        </h3>
                    </div>
                    <div class="card-body">
                        @if ($approval->actions && $approval->actions->count() > 0)
                            <div class="timeline">
                                @foreach ($approval->actions->sortBy('action_date') as $action)
                                    <div class="timeline-item">
                                        <div
                                            class="timeline-marker {{ $action->action === 'approved' ? 'bg-success' : ($action->action === 'rejected' ? 'bg-danger' : 'bg-info') }}">
                                            <i
                                                class="fas fa-{{ $action->action === 'approved' ? 'check' : ($action->action === 'rejected' ? 'times' : 'share') }}"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="timeline-header">
                                                <h6 class="timeline-title">
                                                    {{ ucfirst($action->action) }} by
                                                    {{ $action->approver->name ?? 'Unknown' }}
                                                </h6>
                                                <small class="text-muted">
                                                    {{ $action->action_date->format('M d, Y H:i:s') }}
                                                </small>
                                            </div>
                                            <div class="timeline-body">
                                                @if ($action->comments)
                                                    <p>{{ $action->comments }}</p>
                                                @endif
                                                @if ($action->forwarded_to)
                                                    <small class="text-info">
                                                        Forwarded to: {{ $action->forwardedTo->name ?? 'Unknown' }}
                                                    </small>
                                                @endif
                                                @if ($action->delegated_to)
                                                    <small class="text-info">
                                                        Delegated to: {{ $action->delegatedTo->name ?? 'Unknown' }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted text-center">No approval actions found.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="actionModalTitle">
                        <i class="fas fa-tasks"></i>
                        Approval Action
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="actionModalBody">
                    <!-- Action form will be loaded here -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Load document details
            loadDocumentDetails();

            function loadDocumentDetails() {
                $.ajax({
                    url: '{{ route('approval.dashboard.document-info', $approval) }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#documentDetails').html(response.html);
                        } else {
                            $('#documentDetails').html(`
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Failed to load document details
                        </div>
                    `);
                        }
                    },
                    error: function() {
                        $('#documentDetails').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i>
                        Failed to load document details
                    </div>
                `);
                    }
                });
            }
        });

        function showActionModal(action) {
            $.ajax({
                url: '{{ route('approval.actions.get-form', $approval) }}',
                type: 'GET',
                data: {
                    action: action
                },
                success: function(response) {
                    if (response.success) {
                        $('#actionModalBody').html(response.html);
                        $('#actionModalTitle').html(
                            `<i class="fas fa-${getActionIcon(action)}"></i> ${getActionTitle(action)}`);
                        $('#actionModal').modal('show');
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Failed to load action form');
                }
            });
        }

        function getActionIcon(action) {
            const icons = {
                'approve': 'check',
                'reject': 'times',
                'forward': 'share',
                'delegate': 'user-friends',
                'request_info': 'question-circle',
                'escalate': 'exclamation-triangle',
                'cancel': 'ban'
            };
            return icons[action] || 'tasks';
        }

        function getActionTitle(action) {
            const titles = {
                'approve': 'Approve Document',
                'reject': 'Reject Document',
                'forward': 'Forward Approval',
                'delegate': 'Delegate Approval',
                'request_info': 'Request Information',
                'escalate': 'Escalate Approval',
                'cancel': 'Cancel Approval'
            };
            return titles[action] || 'Approval Action';
        }
    </script>
@endsection

@section('styles')
    <style>
        .approval-flow-progress {
            position: relative;
            padding: 20px 0;
        }

        .stage-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
            position: relative;
        }

        .stage-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .stage-item.completed .stage-icon {
            background-color: #d4edda;
            color: #155724;
        }

        .stage-item.current .stage-icon {
            background-color: #fff3cd;
            color: #856404;
        }

        .stage-item.pending .stage-icon {
            background-color: #f8f9fa;
            color: #6c757d;
        }

        .stage-content {
            flex-grow: 1;
        }

        .stage-title {
            margin-bottom: 5px;
            font-weight: 600;
        }

        .stage-description {
            margin-bottom: 5px;
            color: #6c757d;
        }

        .stage-connector {
            position: absolute;
            left: 25px;
            top: 50px;
            width: 2px;
            height: 30px;
            background-color: #dee2e6;
        }

        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline-item {
            display: flex;
            margin-bottom: 20px;
        }

        .timeline-marker {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
            color: white;
        }

        .timeline-content {
            flex-grow: 1;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }

        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .timeline-title {
            margin: 0;
            font-weight: 600;
        }

        .timeline-body {
            color: #6c757d;
        }

        .info-box {
            margin-bottom: 15px;
        }
    </style>
@endsection
