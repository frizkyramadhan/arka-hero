@extends('layouts.main')

@section('content')
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                    <p class="text-muted">{{ $subtitle }}</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('employee.registration.admin.index') }}">Employee
                                Registrations</a></li>
                        <li class="breadcrumb-item active">Registration Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Registration Status Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle mr-2"></i>
                                Registration Status
                            </h3>
                            <div class="card-tools">
                                @php
                                    $statusClass = match ($registration->status) {
                                        'submitted' => 'badge-warning',
                                        'approved' => 'badge-success',
                                        'rejected' => 'badge-danger',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }} badge-lg">
                                    {{ strtoupper($registration->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Email:</strong><br>
                                    <span class="text-muted">{{ $registration->token->email }}</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Submitted:</strong><br>
                                    <span class="text-muted">{{ $registration->created_at->format('d M Y, H:i') }}</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Documents:</strong><br>
                                    <span class="badge badge-info">{{ $registration->documents->count() }} files</span>
                                </div>
                                <div class="col-md-3">
                                    @if ($registration->status === 'submitted')
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-success" id="approveBtn">
                                                <i class="fas fa-check mr-2"></i>
                                                Approve
                                            </button>
                                            <button type="button" class="btn btn-danger" id="rejectBtn">
                                                <i class="fas fa-times mr-2"></i>
                                                Reject
                                            </button>
                                        </div>
                                    @elseif($registration->reviewed_by)
                                        <strong>Reviewed by:</strong><br>
                                        <span class="text-muted">{{ $registration->reviewer->name ?? 'N/A' }}</span><br>
                                        <small
                                            class="text-muted">{{ $registration->reviewed_at->format('d M Y, H:i') }}</small>
                                    @endif
                                </div>
                            </div>

                            @if ($registration->admin_notes)
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-sticky-note mr-2"></i>Admin Notes:</h6>
                                            <p class="mb-0">{{ $registration->admin_notes }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval Progress Section -->
            @if ($registration->approval)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-tasks mr-2"></i>
                                    Approval Progress
                                </h3>
                            </div>
                            <div class="card-body">
                                <!-- Approval Flow Progress -->
                                <div class="approval-flow-progress">
                                    @php
                                        $progress = $registration->getApprovalProgress();
                                        $timeline = $registration->getApprovalTimeline();
                                    @endphp

                                    <div class="progress-bar-container mb-3">
                                        <div class="progress">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}"
                                                aria-valuemin="0" aria-valuemax="100">
                                                {{ $progress }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">Approval Progress</small>
                                    </div>

                                    <!-- Current Stage Info -->
                                    <div class="current-stage-info mb-3">
                                        <h5>Current Status: {{ ucfirst($registration->getCurrentApprovalStatus()) }}</h5>

                                        @if ($registration->isApprovalOverdue())
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                This approval is overdue and may need escalation.
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Approval Timeline -->
                                    <div class="approval-timeline">
                                        <h5>Approval Timeline</h5>
                                        <div class="timeline">
                                            @foreach ($timeline as $event)
                                                <div class="timeline-item mb-3">
                                                    <div class="d-flex">
                                                        <div class="timeline-marker mr-3">
                                                            @php
                                                                $markerClass = match ($event['action']) {
                                                                    'approved' => 'bg-success',
                                                                    'rejected' => 'bg-danger',
                                                                    default => 'bg-warning',
                                                                };
                                                                $icon = match ($event['action']) {
                                                                    'approved' => 'fa-check',
                                                                    'rejected' => 'fa-times',
                                                                    default => 'fa-clock',
                                                                };
                                                            @endphp
                                                            <div class="rounded-circle {{ $markerClass }} text-white d-flex align-items-center justify-content-center"
                                                                style="width: 30px; height: 30px;">
                                                                <i class="fas {{ $icon }}"></i>
                                                            </div>
                                                        </div>
                                                        <div class="timeline-content flex-grow-1">
                                                            <div class="d-flex justify-content-between">
                                                                <h6 class="mb-1">{{ ucfirst($event['action']) }}</h6>
                                                                <small
                                                                    class="text-muted">{{ $event['date']->format('M d, Y H:i') }}</small>
                                                            </div>
                                                            <p class="mb-1">{{ $event['description'] }}</p>
                                                            @if ($event['user'])
                                                                <small class="text-muted">By:
                                                                    {{ $event['user']->name }}</small>
                                                            @endif
                                                            @if ($event['comments'])
                                                                <div class="comments mt-2">
                                                                    <strong>Comments:</strong> {{ $event['comments'] }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Next Approvers -->
                                    @php
                                        $nextApprovers = $registration->getNextApprovers();
                                    @endphp
                                    @if (count($nextApprovers) > 0)
                                        <div class="next-approvers mt-4">
                                            <h5>Next Approvers</h5>
                                            <div class="approvers-list">
                                                @foreach ($nextApprovers as $approver)
                                                    <div class="approver-item d-flex align-items-center mb-2">
                                                        <div class="approver-icon mr-3">
                                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                                                style="width: 30px; height: 30px;">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                        </div>
                                                        <div class="approver-info flex-grow-1">
                                                            <div class="approver-name font-weight-bold">
                                                                {{ $approver['name'] }}</div>
                                                            <div class="approver-type text-muted">
                                                                {{ ucfirst($approver['type']) }}</div>
                                                            @if ($approver['is_backup'])
                                                                <span class="badge badge-warning">Backup</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <!-- Personal Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user mr-2"></i>
                                Personal Information
                            </h3>
                        </div>
                        <div class="card-body">
                            @php $personal = $registration->personal_data; @endphp
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="font-weight-bold">Full Name:</label>
                                    <p class="mb-1">{{ $personal['fullname'] ?? 'N/A' }}</p>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="font-weight-bold">Identity Card:</label>
                                    <p class="mb-1">{{ $personal['identity_card'] ?? 'N/A' }}</p>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="font-weight-bold">Birth Date:</label>
                                    <p class="mb-1">{{ $personal['birth_date'] ?? 'N/A' }}</p>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="font-weight-bold">Gender:</label>
                                    <p class="mb-1">{{ $personal['gender'] ?? 'N/A' }}</p>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="font-weight-bold">Phone:</label>
                                    <p class="mb-1">{{ $personal['phone'] ?? 'N/A' }}</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="font-weight-bold">Address:</label>
                                    <p class="mb-1">{{ $personal['address'] ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employment Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-briefcase mr-2"></i>
                                Employment Information
                            </h3>
                        </div>
                        <div class="card-body">
                            @php $employment = $registration->employment_data; @endphp
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="font-weight-bold">Position:</label>
                                    <p class="mb-1">{{ $employment['position'] ?? 'N/A' }}</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="font-weight-bold">Department:</label>
                                    <p class="mb-1">{{ $employment['department'] ?? 'N/A' }}</p>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="font-weight-bold">Start Date:</label>
                                    <p class="mb-1">{{ $employment['start_date'] ?? 'N/A' }}</p>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="font-weight-bold">Employment Type:</label>
                                    <p class="mb-1">{{ $employment['employment_type'] ?? 'N/A' }}</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="font-weight-bold">Salary:</label>
                                    <p class="mb-1">
                                        {{ isset($employment['salary']) ? 'Rp ' . number_format($employment['salary']) : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            @if ($registration->additional_data)
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-plus-circle mr-2"></i>
                                    Additional Information
                                </h3>
                            </div>
                            <div class="card-body">
                                @php $additional = $registration->additional_data; @endphp
                                <div class="row">
                                    @foreach ($additional as $key => $value)
                                        @if ($value)
                                            <div class="col-md-4 mb-3">
                                                <label
                                                    class="font-weight-bold">{{ ucwords(str_replace('_', ' ', $key)) }}:</label>
                                                <p class="mb-1">
                                                    {{ is_array($value) ? implode(', ', $value) : $value }}</p>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Documents -->
            @if ($registration->documents->count() > 0)
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-file-alt mr-2"></i>
                                    Uploaded Documents
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach ($registration->documents as $document)
                                        <div class="col-md-4 mb-3">
                                            <div class="document-card">
                                                <div class="document-icon">
                                                    @php
                                                        $extension = pathinfo(
                                                            $document->original_filename,
                                                            PATHINFO_EXTENSION,
                                                        );
                                                        $iconClass = match (strtolower($extension)) {
                                                            'pdf' => 'fa-file-pdf text-danger',
                                                            'jpg', 'jpeg', 'png' => 'fa-file-image text-primary',
                                                            'doc', 'docx' => 'fa-file-word text-info',
                                                            default => 'fa-file text-secondary',
                                                        };
                                                    @endphp
                                                    <i class="fas {{ $iconClass }} fa-3x"></i>
                                                </div>
                                                <div class="document-info">
                                                    <h6 class="document-title">{{ $document->document_type }}</h6>
                                                    <p class="document-filename">{{ $document->original_filename }}
                                                    </p>
                                                    <small class="text-muted">
                                                        {{ number_format($document->file_size / 1024, 1) }} KB
                                                    </small>
                                                </div>
                                                <div class="document-actions">
                                                    <a href="{{ route('employee.registration.admin.download.document', [$registration->id, $document->id]) }}"
                                                        class="btn btn-sm btn-primary" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
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
        </div>
    </section>
    </div>

    <!-- Approval Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-check-circle mr-2"></i>
                        Approve Registration
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="approvalForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <h6><i class="fas fa-info-circle mr-2"></i>Approval Confirmation</h6>
                            <p class="mb-0">Are you sure you want to approve this employee registration?</p>
                        </div>

                        <div class="form-group">
                            <label for="approvalNotes">Admin Notes (Optional)</label>
                            <textarea class="form-control" id="approvalNotes" name="admin_notes" rows="3"
                                placeholder="Add any notes about this approval..."></textarea>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-lightbulb mr-2"></i>What happens next?</h6>
                            <ul class="mb-0 pl-3">
                                <li>Registration status will be updated to "Approved"</li>
                                <li>Employee record will be created in the system</li>
                                <li>Employee will be notified via email</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check mr-2"></i>
                            Approve Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-times-circle mr-2"></i>
                        Reject Registration
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="rejectionForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle mr-2"></i>Rejection Confirmation</h6>
                            <p class="mb-0">Please provide a reason for rejecting this registration.</p>
                        </div>

                        <div class="form-group">
                            <label for="rejectionReason">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejectionReason" name="admin_notes" rows="4"
                                placeholder="Please explain why this registration is being rejected..." required></textarea>
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="fas fa-info-circle mr-2"></i>What happens next?</h6>
                            <ul class="mb-0 pl-3">
                                <li>Registration status will be updated to "Rejected"</li>
                                <li>Employee will be notified with the rejection reason</li>
                                <li>Documents will be retained for audit purposes</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times mr-2"></i>
                            Reject Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
    </section>

@endsection

@push('styles')
    <style>
        .badge-lg {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }

        .document-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.3s ease;
        }

        .document-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-color: #007bff;
        }

        .document-icon {
            margin-bottom: 10px;
        }

        .document-info {
            flex-grow: 1;
        }

        .document-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: #495057;
        }

        .document-filename {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 5px;
            word-break: break-word;
        }

        .document-actions {
            margin-top: 10px;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .modal-header.bg-success,
        .modal-header.bg-danger {
            border-bottom: none;
        }

        .alert ul {
            margin-bottom: 0;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Approve button
            $('#approveBtn').on('click', function() {
                $('#approvalModal').modal('show');
            });

            // Reject button
            $('#rejectBtn').on('click', function() {
                $('#rejectionModal').modal('show');
            });

            // Approval form submission
            $('#approvalForm').on('submit', function(e) {
                e.preventDefault();

                let submitBtn = $(this).find('button[type="submit"]');
                let originalText = submitBtn.html();

                submitBtn.html('<span class="spinner-border spinner-border-sm mr-2"></span>Processing...')
                    .prop('disabled', true);

                $.ajax({
                    url: '{{ route('employee.registration.admin.approve', $registration->id) }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message, 'Success');
                            setTimeout(function() {
                                window.location.href =
                                    '{{ route('employee.registration.admin.index') }}';
                            }, 1500);
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'An error occurred', 'Error');
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Rejection form submission
            $('#rejectionForm').on('submit', function(e) {
                e.preventDefault();

                let submitBtn = $(this).find('button[type="submit"]');
                let originalText = submitBtn.html();

                submitBtn.html('<span class="spinner-border spinner-border-sm mr-2"></span>Processing...')
                    .prop('disabled', true);

                $.ajax({
                    url: '{{ route('employee.registration.admin.reject', $registration->id) }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message, 'Success');
                            setTimeout(function() {
                                window.location.href =
                                    '{{ route('employee.registration.admin.index') }}';
                            }, 1500);
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'An error occurred', 'Error');
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush
