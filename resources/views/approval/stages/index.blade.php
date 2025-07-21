@extends('layouts.main')

@section('title', 'Approval Stages')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Approval Stages: {{ $flow->name }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('approval.flows.index') }}">Approval Flows</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('approval.flows.show', $flow) }}">{{ $flow->name }}</a></li>
                        <li class="breadcrumb-item active">Stages</li>
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
                            <h3 class="card-title">Approval Stages</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-success" onclick="saveOrder()">
                                    <i class="fas fa-save"></i> Save Order
                                </button>
                                <a href="{{ route('approval.stages.create', $flow) }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Stage
                                </a>
                                <a href="{{ route('approval.flows.show', $flow) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Flow
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @if ($flow->stages->count() > 0)
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    Drag and drop stages to reorder them. Click "Save Order" to apply changes.
                                </div>

                                <div id="stages-container" class="sortable-stages">
                                    @foreach ($flow->stages as $stage)
                                        <div class="stage-item card mb-3" data-stage-id="{{ $stage->id }}"
                                            data-stage-order="{{ $stage->stage_order }}">
                                            <div class="card-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-grip-vertical handle mr-2"
                                                            style="cursor: move;"></i>
                                                        <h6 class="mb-0">Stage {{ $stage->stage_order }}:
                                                            {{ $stage->stage_name }}</h6>
                                                    </div>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('approval.stages.show', [$flow, $stage]) }}"
                                                            class="btn btn-sm btn-info" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('approval.stages.edit', [$flow, $stage]) }}"
                                                            class="btn btn-sm btn-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-secondary"
                                                            onclick="duplicateStage({{ $stage->id }})"
                                                            title="Duplicate">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="deleteStage({{ $stage->id }}, '{{ $stage->stage_name }}')"
                                                            title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <strong>Type:</strong>
                                                        @if ($stage->stage_type === 'parallel')
                                                            <span class="badge badge-info">Parallel</span>
                                                        @else
                                                            <span class="badge badge-secondary">Sequential</span>
                                                        @endif
                                                        @if ($stage->is_mandatory)
                                                            <span class="badge badge-warning">Mandatory</span>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong>Escalation:</strong> {{ $stage->escalation_hours }} hours
                                                    </div>
                                                </div>

                                                <div class="mt-3">
                                                    <strong>Approvers:</strong>
                                                    @if ($stage->approvers->count() > 0)
                                                        <ul class="list-unstyled mt-2">
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
                                                                        <span class="badge badge-secondary">Backup</span>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <span class="text-muted">No approvers assigned</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    No stages configured for this flow.
                                    <a href="{{ route('approval.stages.create', $flow) }}" class="alert-link">Add the first
                                        stage</a>.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        // Initialize drag and drop
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('stages-container');
            if (container) {
                new Sortable(container, {
                    handle: '.handle',
                    animation: 150,
                    onEnd: function(evt) {
                        updateStageNumbers();
                    }
                });
            }
        });

        function updateStageNumbers() {
            const stages = document.querySelectorAll('.stage-item');
            stages.forEach((stage, index) => {
                const orderSpan = stage.querySelector('h6');
                const orderText = orderSpan.textContent.replace(/Stage \d+:/, `Stage ${index + 1}:`);
                orderSpan.textContent = orderText;
                stage.dataset.stageOrder = index + 1;
            });
        }

        function saveOrder() {
            const stages = document.querySelectorAll('.stage-item');
            const stageData = Array.from(stages).map((stage, index) => ({
                id: parseInt(stage.dataset.stageId),
                stage_order: index + 1
            }));

            fetch('{{ route('approval.stages.reorder', $flow) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        stages: stageData
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', 'Stage order saved successfully!');
                    } else {
                        showAlert('error', data.message || 'Failed to save stage order');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Failed to save stage order');
                });
        }

        function duplicateStage(stageId) {
            if (confirm('Are you sure you want to duplicate this stage?')) {
                fetch(`{{ route('approval.stages.index', $flow) }}/${stageId}/duplicate`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('success', 'Stage duplicated successfully!');
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            showAlert('error', data.message || 'Failed to duplicate stage');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('error', 'Failed to duplicate stage');
                    });
            }
        }

        function deleteStage(stageId, stageName) {
            if (confirm(`Are you sure you want to delete the stage "${stageName}"? This action cannot be undone.`)) {
                fetch(`{{ route('approval.stages.index', $flow) }}/${stageId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('success', 'Stage deleted successfully!');
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            showAlert('error', data.message || 'Failed to delete stage');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('error', 'Failed to delete stage');
                    });
            }
        }

        function showAlert(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';

            const alertDiv = document.createElement('div');
            alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="${icon}"></i> ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            `;

            const container = document.querySelector('.card-body');
            container.insertBefore(alertDiv, container.firstChild);

            // Auto dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    </script>
@endsection

@section('styles')
    <style>
        .sortable-stages {
            min-height: 50px;
        }

        .stage-item {
            transition: all 0.3s ease;
        }

        .stage-item:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .handle {
            color: #6c757d;
            font-size: 14px;
        }

        .handle:hover {
            color: #495057;
        }

        .sortable-ghost {
            opacity: 0.5;
            background: #f8f9fa;
        }

        .sortable-chosen {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
    </style>
@endsection
