@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $subtitle }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('officialtravels.index') }}">Official Travels</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('officialtravels.show', $officialtravel->id) }}">Details</a></li>
                        <li class="breadcrumb-item active">Process Approval</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Left Column - Document Information -->
                <div class="col-md-8">
                    <!-- Document Information Card -->
                    <div class="card card-primary card-outline elevation-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-alt mr-2"></i>
                                <strong>Document Information</strong>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">LOT Number</label>
                                        <div>{{ $officialtravel->official_travel_number }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Travel Date</label>
                                        <div>
                                            {{ $officialtravel->official_travel_date ? date('d/m/Y', strtotime($officialtravel->official_travel_date)) : '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Traveler</label>
                                        <div>{{ $officialtravel->traveler->employee->fullname ?? 'Unknown' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Destination</label>
                                        <div>{{ $officialtravel->destination }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Purpose</label>
                                        <div>{{ $officialtravel->purpose }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Duration</label>
                                        <div>{{ $officialtravel->duration }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Departure Date</label>
                                        <div>
                                            {{ $officialtravel->departure_from ? date('d/m/Y', strtotime($officialtravel->departure_from)) : '-' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Project</label>
                                        <div>{{ $officialtravel->project->project_name ?? 'Unknown' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approval History Card -->
                    <div class="card card-info card-outline elevation-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history mr-2"></i>
                                <strong>Approval History</strong>
                            </h3>
                        </div>
                        <div class="card-body">
                            @if ($approvalHistory && count($approvalHistory) > 0)
                                <div class="timeline">
                                    @foreach ($approvalHistory as $action)
                                        <div class="timeline-item">
                                            <div
                                                class="timeline-marker {{ $action['action'] === 'approved' ? 'bg-success' : ($action['action'] === 'rejected' ? 'bg-danger' : 'bg-info') }}">
                                                <i
                                                    class="fas fa-{{ $action['action'] === 'approved' ? 'check' : ($action['action'] === 'rejected' ? 'times' : 'arrow-right') }}"></i>
                                            </div>
                                            <div class="timeline-content">
                                                <div class="d-flex justify-content-between">
                                                    <strong>{{ ucfirst($action['action']) }}</strong>
                                                    <small class="text-muted">{{ $action['date'] }}</small>
                                                </div>
                                                <div class="text-muted">{{ $action['approver'] }}</div>
                                                @if ($action['comments'])
                                                    <div class="mt-2">
                                                        <strong>Comments:</strong>
                                                        <p class="mb-0">{{ $action['comments'] }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-muted">
                                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                                    <p>No approval actions recorded yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column - Approval Actions -->
                <div class="col-md-4">
                    <!-- Current Stage Information -->
                    <div class="card card-warning card-outline elevation-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clipboard-list mr-2"></i>
                                <strong>Current Stage</strong>
                            </h3>
                        </div>
                        <div class="card-body">
                            @if ($officialtravel->approval->currentStage)
                                <div class="current-stage-info">
                                    <h6 class="text-warning mb-3">
                                        <i class="fas fa-star"></i>
                                        {{ $officialtravel->approval->currentStage->stage_name }}
                                    </h6>

                                    <div class="approvers-list">
                                        <strong>Approvers:</strong>
                                        <ul class="list-unstyled mt-2">
                                            @foreach ($officialtravel->approval->currentStage->approvers as $approver)
                                                <li class="mb-1">
                                                    <i class="fas fa-user mr-1"></i>
                                                    @if ($approver->approver_type === 'user')
                                                        {{ $approver->user->name ?? 'Unknown User' }}
                                                    @elseif($approver->approver_type === 'role')
                                                        {{ $approver->role->name ?? 'Unknown Role' }}
                                                    @elseif($approver->approver_type === 'department')
                                                        {{ $approver->department->name ?? 'Unknown Department' }}
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    No current stage information available.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Next Approvers -->
                    @if ($nextApprovers && count($nextApprovers) > 0)
                        <div class="card card-success card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-arrow-right mr-2"></i>
                                    <strong>Next Approvers</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="next-approvers-list">
                                    @foreach ($nextApprovers as $approver)
                                        <div class="approver-item mb-2">
                                            <i class="fas fa-user-circle mr-1"></i>
                                            @if ($approver['type'] === 'user')
                                                {{ $approver['name'] }}
                                            @elseif($approver['type'] === 'role')
                                                {{ $approver['name'] }} (Role)
                                            @elseif($approver['type'] === 'department')
                                                {{ $approver['name'] }} (Department)
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Approval Action Form -->
                    <div class="card card-primary card-outline elevation-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-check-circle mr-2"></i>
                                <strong>Process Approval</strong>
                            </h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('officialtravels.approve', $officialtravel->id) }}" method="POST"
                                id="approvalForm">
                                @csrf

                                <div class="form-group">
                                    <label for="action">Action <span class="text-danger">*</span></label>
                                    <select class="form-control @error('action') is-invalid @enderror" name="action"
                                        id="action" required>
                                        <option value="">Select Action</option>
                                        <option value="approved">Approve</option>
                                        <option value="rejected">Reject</option>
                                        <option value="forwarded">Forward</option>
                                        <option value="delegated">Delegate</option>
                                    </select>
                                    @error('action')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group" id="forwardToGroup" style="display: none;">
                                    <label for="forward_to">Forward To <span class="text-danger">*</span></label>
                                    <select class="form-control @error('forward_to') is-invalid @enderror"
                                        name="forward_to" id="forward_to">
                                        <option value="">Select User</option>
                                        @foreach ($approvers as $approver)
                                            <option value="{{ $approver['id'] }}">{{ $approver['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('forward_to')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group" id="delegateToGroup" style="display: none;">
                                    <label for="delegate_to">Delegate To <span class="text-danger">*</span></label>
                                    <select class="form-control @error('delegate_to') is-invalid @enderror"
                                        name="delegate_to" id="delegate_to">
                                        <option value="">Select User</option>
                                        @foreach ($approvers as $approver)
                                            <option value="{{ $approver['id'] }}">{{ $approver['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('delegate_to')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="comments">Comments <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('comments') is-invalid @enderror" name="comments" id="comments" rows="4"
                                        placeholder="Please provide your comments for this action..." required></textarea>
                                    @error('comments')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-paper-plane mr-2"></i> Submit Action
                                    </button>
                                    <a href="{{ route('officialtravels.show', $officialtravel->id) }}"
                                        class="btn btn-secondary btn-block">
                                        <i class="fas fa-times-circle mr-2"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <style>
        .card {
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            border-radius: calc(0.5rem - 1px) calc(0.5rem - 1px) 0 0;
        }

        .btn {
            border-radius: 0.25rem;
        }

        /* Timeline styling */
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-marker {
            position: absolute;
            left: -35px;
            top: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }

        .timeline-content {
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            padding: 1rem;
            border-left: 3px solid #007bff;
        }

        /* Current stage styling */
        .current-stage-info {
            background-color: #fff3cd;
            border-radius: 0.25rem;
            padding: 1rem;
        }

        .approvers-list ul {
            margin-bottom: 0;
        }

        .approver-item {
            padding: 0.5rem;
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            border-left: 3px solid #28a745;
        }

        .next-approvers-list {
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            padding: 1rem;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(function() {
            // Handle action selection
            $('#action').change(function() {
                const selectedAction = $(this).val();

                // Hide all optional fields
                $('#forwardToGroup, #delegateToGroup').hide();
                $('#forward_to, #delegate_to').prop('required', false);

                // Show relevant field based on action
                if (selectedAction === 'forwarded') {
                    $('#forwardToGroup').show();
                    $('#forward_to').prop('required', true);
                } else if (selectedAction === 'delegated') {
                    $('#delegateToGroup').show();
                    $('#delegate_to').prop('required', true);
                }
            });

            // Form validation
            $('#approvalForm').on('submit', function(e) {
                const action = $('#action').val();
                const comments = $('#comments').val().trim();

                if (!action) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select an action to perform.'
                    });
                    return;
                }

                if (!comments || comments.length < 10) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please provide comments with at least 10 characters.'
                    });
                    return;
                }

                // Check if forward/delegate user is selected when required
                if (action === 'forwarded' && !$('#forward_to').val()) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select a user to forward to.'
                    });
                    return;
                }

                if (action === 'delegated' && !$('#delegate_to').val()) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select a user to delegate to.'
                    });
                    return;
                }
            });
        });
    </script>
@endsection
