@extends('layouts.main')

@section('title', 'Approval Stage Details')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Stage: {{ $stage->stage_name }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('approval.flows.index') }}">Approval Flows</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('approval.flows.show', $flow) }}">{{ $flow->name }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('approval.stages.index', $flow) }}">Stages</a></li>
                        <li class="breadcrumb-item active">{{ $stage->stage_name }}</li>
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
                            <h3 class="card-title">Stage Details: {{ $stage->stage_name }}</h3>
                            <div class="card-tools">
                                <a href="{{ route('approval.stages.edit', [$flow, $stage]) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('approval.stages.index', $flow) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Stages
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Stage Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Stage Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Name:</strong></td>
                                            <td>{{ $stage->stage_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Order:</strong></td>
                                            <td>{{ $stage->stage_order }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Type:</strong></td>
                                            <td>
                                                @if ($stage->stage_type === 'parallel')
                                                    <span class="badge badge-info">Parallel</span>
                                                @else
                                                    <span class="badge badge-secondary">Sequential</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Mandatory:</strong></td>
                                            <td>
                                                @if ($stage->is_mandatory)
                                                    <span class="badge badge-success">Yes</span>
                                                @else
                                                    <span class="badge badge-warning">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Escalation:</strong></td>
                                            <td>{{ $stage->escalation_hours }} hours</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5>Flow Information</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Flow:</strong></td>
                                            <td>{{ $flow->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Document Type:</strong></td>
                                            <td><span class="badge badge-info">{{ $flow->document_type }}</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Stages:</strong></td>
                                            <td>{{ $flow->stages->count() }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Stage Position:</strong></td>
                                            <td>{{ $stage->stage_order }} of {{ $flow->stages->count() }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <hr>

                            <!-- Approvers Section -->
                            <div class="row">
                                <div class="col-12">
                                    <h5>Approvers</h5>
                                    @if ($stage->approvers->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Type</th>
                                                        <th>Approver</th>
                                                        <th>Role</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($stage->approvers as $index => $approver)
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>
                                                                <span
                                                                    class="badge badge-info">{{ ucfirst($approver->approver_type) }}</span>
                                                            </td>
                                                            <td>
                                                                @if ($approver->approver_type === 'user')
                                                                    {{ $approver->approverUser->name ?? 'Unknown User' }}
                                                                @elseif($approver->approver_type === 'role')
                                                                    {{ $approver->approverRole->name ?? 'Unknown Role' }}
                                                                @elseif($approver->approver_type === 'department')
                                                                    {{ $approver->approverDepartment->name ?? 'Unknown Department' }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if ($approver->approver_type === 'user')
                                                                    @if ($approver->approverUser)
                                                                        @foreach ($approver->approverUser->roles as $role)
                                                                            <span
                                                                                class="badge badge-secondary">{{ $role->name }}</span>
                                                                        @endforeach
                                                                    @endif
                                                                @else
                                                                    <span class="text-muted">N/A</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if ($approver->is_backup)
                                                                    <span class="badge badge-warning">Backup</span>
                                                                @else
                                                                    <span class="badge badge-success">Primary</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> No approvers assigned to this stage.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Recent Actions -->
                            @if ($stage->approvalActions->count() > 0)
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h5>Recent Approval Actions</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Document</th>
                                                        <th>Action</th>
                                                        <th>Approver</th>
                                                        <th>Date</th>
                                                        <th>Comments</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($stage->approvalActions->take(10) as $action)
                                                        <tr>
                                                            <td>
                                                                <strong>{{ $action->documentApproval->document_type }}</strong>
                                                                <br><small class="text-muted">ID:
                                                                    {{ $action->documentApproval->document_id }}</small>
                                                            </td>
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
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Stage Statistics -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>Stage Statistics</h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info"><i class="fas fa-file-alt"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Actions</span>
                                                    <span
                                                        class="info-box-number">{{ $stage->approvalActions->count() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Approved</span>
                                                    <span
                                                        class="info-box-number">{{ $stage->approvalActions->where('action', 'approved')->count() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Rejected</span>
                                                    <span
                                                        class="info-box-number">{{ $stage->approvalActions->where('action', 'rejected')->count() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Avg Time</span>
                                                    <span class="info-box-number">{{ $stage->escalation_hours }}h</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
