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
        <!-- Approval Status -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i>
                            Approval Status
                        </h3>
                        <div class="card-tools">
                            <span
                                class="badge badge-{{ $approval->overall_status === 'approved' ? 'success' : ($approval->overall_status === 'rejected' ? 'danger' : 'warning') }} badge-lg">
                                {{ ucfirst($approval->overall_status) }}
                            </span>
                        </div>
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

        <!-- Document Information -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-alt"></i>
                            Document Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div id="documentInfo">
                            <!-- Document information will be loaded via AJAX -->
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p>Loading document information...</p>
                            </div>
                        </div>
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
                            <form id="approvalActionForm">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="actionComments">Comments (Optional)</label>
                                            <textarea class="form-control" id="actionComments" name="comments" rows="3"
                                                placeholder="Enter your comments here..."></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="actionType">Action</label>
                                            <select class="form-control" id="actionType" name="action">
                                                <option value="">Select Action</option>
                                                <option value="approve">Approve</option>
                                                <option value="reject">Reject</option>
                                                <option value="forward">Forward</option>
                                                <option value="delegate">Delegate</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="forwardDelegateOptions" style="display: none;">
                                    <div class="col-md-6">
                                        <div class="form-group" id="forwardGroup">
                                            <label for="forwardTo">Forward To</label>
                                            <select class="form-control" id="forwardTo" name="forward_to">
                                                <option value="">Select User</option>
                                                <!-- Users will be loaded via AJAX -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group" id="delegateGroup">
                                            <label for="delegateTo">Delegate To</label>
                                            <select class="form-control" id="delegateTo" name="delegate_to">
                                                <option value="">Select User</option>
                                                <!-- Users will be loaded via AJAX -->
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary" id="submitApprovalAction">
                                            <i class="fas fa-save"></i> Process Approval
                                        </button>
                                        <a href="{{ route('approval.dashboard.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                                        </a>
                                    </div>
                                </div>
                            </form>
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
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Load document information
            loadDocumentInfo();

            // Handle action type change
            $('#actionType').change(function() {
                const action = $(this).val();

                if (action === 'forward') {
                    $('#forwardGroup').show();
                    $('#delegateGroup').hide();
                    $('#forwardDelegateOptions').show();
                    loadUsers('#forwardTo');
                } else if (action === 'delegate') {
                    $('#forwardGroup').hide();
                    $('#delegateGroup').show();
                    $('#forwardDelegateOptions').show();
                    loadUsers('#delegateTo');
                } else {
                    $('#forwardDelegateOptions').hide();
                }
            });

            // Handle form submission
            $('#approvalActionForm').submit(function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                $.ajax({
                    url: '{{ route('approval.dashboard.process', $approval) }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            setTimeout(function() {
                                window.location.href = response.redirect ||
                                    '{{ route('approval.dashboard.index') }}';
                            }, 1500);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to process approval');
                    }
                });
            });

            function loadDocumentInfo() {
                $.ajax({
                    url: '{{ route('approval.dashboard.document-info', $approval) }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#documentInfo').html(response.html);
                        } else {
                            $('#documentInfo').html(`
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Failed to load document information
                        </div>
                    `);
                        }
                    },
                    error: function() {
                        $('#documentInfo').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i>
                        Failed to load document information
                    </div>
                `);
                    }
                });
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

        .badge-lg {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
    </style>
@endsection
