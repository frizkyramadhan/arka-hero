@extends('layouts.main')

@section('title', 'Approval Flow Details')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Approval Flow: {{ $flow->name }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('approval.flows.index') }}">Approval Flows</a></li>
                        <li class="breadcrumb-item active">{{ $flow->name }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Approval Flow: {{ $flow->name }}</h3>
                            <div class="card-tools">
                                <a href="{{ route('approval.flows.edit', $flow) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('approval.flows.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Flows
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Flow Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Flow Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Name:</strong></td>
                                            <td>{{ $flow->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Document Type:</strong></td>
                                            <td><span class="badge badge-info">{{ $flow->document_type }}</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                @if ($flow->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-danger">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created By:</strong></td>
                                            <td>{{ $flow->creator->name ?? 'Unknown' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created At:</strong></td>
                                            <td>{{ $flow->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    @if ($flow->description)
                                        <h5>Description</h5>
                                        <p>{{ $flow->description }}</p>
                                    @endif
                                </div>
                            </div>

                            <hr>

                            <!-- Flow Statistics -->
                            @if ($statistics)
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5>Flow Statistics</h5>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-info"><i
                                                            class="fas fa-file-alt"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Total Approvals</span>
                                                        <span
                                                            class="info-box-number">{{ $statistics['total_approvals'] ?? 0 }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-warning"><i
                                                            class="fas fa-clock"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Pending</span>
                                                        <span
                                                            class="info-box-number">{{ $statistics['pending_approvals'] ?? 0 }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-success"><i
                                                            class="fas fa-check"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Approved</span>
                                                        <span
                                                            class="info-box-number">{{ $statistics['approved_approvals'] ?? 0 }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-danger"><i
                                                            class="fas fa-times"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Rejected</span>
                                                        <span
                                                            class="info-box-number">{{ $statistics['rejected_approvals'] ?? 0 }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Approval Stages -->
                            <div class="row">
                                <div class="col-12">
                                    <h5>Approval Stages</h5>
                                    @if ($flow->stages->count() > 0)
                                        <div class="timeline">
                                            @foreach ($flow->stages as $index => $stage)
                                                <div class="time-label">
                                                    <span class="bg-blue">Stage {{ $index + 1 }}</span>
                                                </div>
                                                <div>
                                                    <i class="fas fa-user-check bg-green"></i>
                                                    <div class="timeline-item">
                                                        <span class="time">
                                                            <i class="fas fa-clock"></i>
                                                            {{ $stage->escalation_hours }}h escalation
                                                        </span>
                                                        <h3 class="timeline-header">
                                                            {{ $stage->stage_name }}
                                                            @if ($stage->stage_type === 'parallel')
                                                                <span class="badge badge-info">Parallel</span>
                                                            @else
                                                                <span class="badge badge-secondary">Sequential</span>
                                                            @endif
                                                            @if ($stage->is_mandatory)
                                                                <span class="badge badge-warning">Mandatory</span>
                                                            @endif
                                                        </h3>
                                                        <div class="timeline-body">
                                                            <h6>Approvers:</h6>
                                                            @if ($stage->approvers->count() > 0)
                                                                <ul class="list-unstyled">
                                                                    @foreach ($stage->approvers as $approver)
                                                                        <li>
                                                                            <i class="fas fa-user"></i>
                                                                            @if ($approver->approver_type === 'user')
                                                                                {{ $approver->approverUser->name ?? 'Unknown User' }}
                                                                            @elseif($approver->approver_type === 'role')
                                                                                {{ $approver->approverRole->name ?? 'Unknown Role' }}
                                                                            @elseif($approver->approver_type === 'department')
                                                                                {{ $approver->approverDepartment->name ?? 'Unknown Department' }}
                                                                            @endif
                                                                            @if ($approver->is_backup)
                                                                                <span
                                                                                    class="badge badge-secondary">Backup</span>
                                                                            @endif
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                <p class="text-muted">No approvers assigned</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> No stages configured for this flow.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Recent Actions -->
                            @if ($flow->stages->count() > 0)
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h5>Recent Approval Actions</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Document</th>
                                                        <th>Stage</th>
                                                        <th>Action</th>
                                                        <th>Approver</th>
                                                        <th>Date</th>
                                                        <th>Comments</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($flow->stages->flatMap->actions->take(10) as $action)
                                                        <tr>
                                                            <td>
                                                                <strong>{{ $action->documentApproval->document_type }}</strong>
                                                                <br><small class="text-muted">ID:
                                                                    {{ $action->documentApproval->document_id }}</small>
                                                            </td>
                                                            <td>{{ $action->approvalStage->stage_name ?? 'Unknown' }}</td>
                                                            <td>
                                                                @switch($action->action)
                                                                    @case('approved')
                                                                        <span class="badge badge-success">Approved</span>
                                                                    @break

                                                                    @case('rejected')
                                                                        <span class="badge badge-danger">Rejected</span>
                                                                    @break

                                                                    @case('forwarded')
                                                                        <span class="badge badge-info">Forwarded</span>
                                                                    @break

                                                                    @case('delegated')
                                                                        <span class="badge badge-warning">Delegated</span>
                                                                    @break

                                                                    @default
                                                                        <span
                                                                            class="badge badge-secondary">{{ ucfirst($action->action) }}</span>
                                                                @endswitch
                                                            </td>
                                                            <td>{{ $action->approver->name ?? 'Unknown' }}</td>
                                                            <td>{{ $action->action_date->format('M d, Y H:i') }}</td>
                                                            <td>{{ Str::limit($action->comments, 50) }}</td>
                                                        </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="6" class="text-center">No recent actions found.
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endsection

    @section('styles')
        <style>
            .timeline {
                position: relative;
                margin: 0 0 30px 0;
                padding: 0;
                list-style: none;
            }

            .timeline:before {
                content: '';
                position: absolute;
                top: 0;
                bottom: 0;
                width: 4px;
                background: #ddd;
                left: 31px;
                margin: 0;
                border-radius: 2px;
            }

            .timeline>li {
                position: relative;
                margin-right: 10px;
                margin-bottom: 15px;
            }

            .timeline>li>.timeline-item {
                -webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
                border-radius: 0.25rem;
                background-color: #fff;
                color: #495057;
                margin-left: 60px;
                margin-top: 0;
                margin-bottom: 0;
                margin-right: 18px;
                margin-top: 0;
                padding: 0;
                position: relative;
            }

            .timeline>li>.timeline-item>.time {
                color: #999;
                float: right;
                font-size: 12px;
                padding: 10px;
            }

            .timeline>li>.timeline-item>.timeline-header {
                color: #495057;
                line-height: 1.1;
                margin: 0;
                padding: 10px;
                font-size: 16px;
                border-bottom: 1px solid rgba(0, 0, 0, .125);
            }

            .timeline>li>.timeline-item>.timeline-body {
                padding: 10px;
            }

            .timeline>li>.timeline-item>.timeline-footer {
                padding: 10px;
                background-color: rgba(0, 0, 0, .03);
                border-top: 1px solid rgba(0, 0, 0, .125);
            }

            .timeline>li>.fa,
            .timeline>li>.fas,
            .timeline>li>.far,
            .timeline>li>.fab,
            .timeline>li>.glyphicon {
                -webkit-box-shadow: 0 0 0 3px #fff;
                box-shadow: 0 0 0 3px #fff;
                border-radius: 50%;
                font-size: 15px;
                height: 30px;
                left: 18px;
                line-height: 30px;
                position: absolute;
                text-align: center;
                top: 0;
                width: 30px;
            }

            .timeline>li>.fa,
            .timeline>li>.fas,
            .timeline>li>.far,
            .timeline>li>.fab,
            .timeline>li>.glyphicon {
                background: #adb5bd;
                color: #fff;
            }

            .timeline>li.time-label>span {
                -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.75);
                box-shadow: 0 1px 1px rgba(0, 0, 0, 0.75);
                border-radius: 3px;
                color: #fff;
                display: inline-block;
                font-weight: 600;
                padding: 3px 10px;
                text-shadow: 0 1px 1px rgba(0, 0, 0, 0.75);
            }

            .timeline>li.time-label>span {
                background-color: #007bff;
            }

            .timeline>li>.fa,
            .timeline>li>.fas,
            .timeline>li>.far,
            .timeline>li>.fab,
            .timeline>li>.glyphicon {
                background: #28a745;
                color: #fff;
            }
        </style>
    @endsection
