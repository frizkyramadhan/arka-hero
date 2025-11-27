@extends('layouts.main')

@section('title', 'Request Leave Cancellation')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Request Leave Cancellation</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        @php
                            // Determine routes based on route asal, bukan permission
                            // Jika dari my-requests.cancellation-form, gunakan my-requests routes
                            // Jika dari cancellation-form, gunakan leave.requests routes
                            $isFromMyRequests = request()->routeIs('leave.my-requests.cancellation-form');

                            $listRoute = $isFromMyRequests ? route('leave.my-requests') : route('leave.requests.index');
                            $showRoute = $isFromMyRequests
                                ? route('leave.my-requests.show', $leaveRequest)
                                : route('leave.requests.show', $leaveRequest);
                            $cancellationRoute = $isFromMyRequests
                                ? route('leave.my-requests.cancellation', $leaveRequest)
                                : route('leave.requests.cancellation', $leaveRequest);
                        @endphp
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ $listRoute }}">Leave Requests</a></li>
                        <li class="breadcrumb-item"><a href="{{ $showRoute }}">Details</a></li>
                        <li class="breadcrumb-item active">Cancellation</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-primary card-outline elevation-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-times-circle mr-2"></i>
                                <strong>Cancellation Request Form</strong>
                            </h3>
                        </div>
                        @php
                            // Determine routes based on route asal, bukan permission
                            // Jika dari my-requests.cancellation-form, gunakan my-requests routes
                            // Jika dari cancellation-form, gunakan leave.requests routes
                            $isFromMyRequests = request()->routeIs('leave.my-requests.cancellation-form');

                            $cancellationRoute = $isFromMyRequests
                                ? route('leave.my-requests.cancellation', $leaveRequest)
                                : route('leave.requests.cancellation', $leaveRequest);
                            $showRoute = $isFromMyRequests
                                ? route('leave.my-requests.show', $leaveRequest)
                                : route('leave.requests.show', $leaveRequest);

                            // Calculate available days to cancel
                            $totalCancelledDays = $leaveRequest->getTotalCancelledDays();
                            $availableDaysToCancel = $leaveRequest->total_days - $totalCancelledDays;
                        @endphp
                        <form method="POST" action="{{ $cancellationRoute }}">
                            @csrf
                            <div class="card-body">
                                <!-- Leave Request Information -->
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="card border-left-primary">
                                            <div class="card-body py-3">
                                                <div class="row align-items-center">
                                                    <div class="col-md-12">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="fas fa-calendar-alt text-primary mr-2"></i>
                                                            <h6 class="mb-0 text-primary font-weight-bold">Leave Request
                                                                Information</h6>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="info-item mb-1">
                                                                    <span class="info-label">Employee:</span>
                                                                    <span
                                                                        class="info-value">{{ $leaveRequest->employee->fullname }}</span>
                                                                </div>
                                                                <div class="info-item mb-1">
                                                                    <span class="info-label">Leave Type:</span>
                                                                    <span
                                                                        class="info-value">{{ $leaveRequest->leaveType->name }}</span>
                                                                </div>
                                                                <div class="info-item mb-1">
                                                                    <span class="info-label">Period:</span>
                                                                    <span
                                                                        class="info-value">{{ $leaveRequest->start_date->format('d/m/Y') }}
                                                                        -
                                                                        {{ $leaveRequest->end_date->format('d/m/Y') }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="info-item mb-1">
                                                                    <span class="info-label">Total Days:</span>
                                                                    <span class="info-value">{{ $leaveRequest->total_days }}
                                                                        day{{ $leaveRequest->total_days > 1 ? 's' : '' }}</span>
                                                                </div>
                                                                @if ($totalCancelledDays > 0)
                                                                    <div class="info-item mb-1">
                                                                        <span class="info-label">Cancelled Days:</span>
                                                                        <span class="info-value text-warning">
                                                                            {{ $totalCancelledDays }}
                                                                            day{{ $totalCancelledDays > 1 ? 's' : '' }}
                                                                        </span>
                                                                    </div>
                                                                    <div class="info-item mb-1">
                                                                        <span class="info-label">Available to Cancel:</span>
                                                                        <span
                                                                            class="info-value text-success font-weight-bold">
                                                                            {{ $availableDaysToCancel }}
                                                                            day{{ $availableDaysToCancel > 1 ? 's' : '' }}
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                                <div class="info-item mb-1">
                                                                    <span class="info-label">Status:</span>
                                                                    <span
                                                                        class="badge badge-success badge-sm">{{ ucfirst($leaveRequest->status) }}</span>
                                                                </div>
                                                                <div class="info-item mb-1">
                                                                    <span class="info-label">Requested:</span>
                                                                    <span
                                                                        class="info-value">{{ $leaveRequest->created_at->format('d/m/Y H:i') }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Days to Cancel -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="days_to_cancel">
                                                <i class="fas fa-calendar-times mr-1"></i>
                                                Days to Cancel <span class="text-danger">*</span>
                                            </label>
                                            @if ($availableDaysToCancel > 0)
                                                <select class="form-control @error('days_to_cancel') is-invalid @enderror"
                                                    name="days_to_cancel" id="days_to_cancel" required>
                                                    <option value="">Select number of days to cancel</option>
                                                    @for ($i = 1; $i <= $availableDaysToCancel; $i++)
                                                        <option value="{{ $i }}"
                                                            {{ old('days_to_cancel') == $i ? 'selected' : '' }}>
                                                            {{ $i }} day{{ $i > 1 ? 's' : '' }}
                                                            @if ($i == $availableDaysToCancel)
                                                                (Full cancellation)
                                                            @else
                                                                (Partial cancellation - {{ $availableDaysToCancel - $i }}
                                                                day{{ $availableDaysToCancel - $i > 1 ? 's' : '' }}
                                                                remaining)
                                                            @endif
                                                        </option>
                                                    @endfor
                                                </select>
                                            @else
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    All days have already been cancelled. No days available for
                                                    cancellation.
                                                </div>
                                            @endif
                                            @error('days_to_cancel')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                @if ($totalCancelledDays > 0)
                                                    <strong>Note:</strong> You have already cancelled
                                                    {{ $totalCancelledDays }} day{{ $totalCancelledDays > 1 ? 's' : '' }}
                                                    from this leave request.
                                                    <br>Available days to cancel: <strong>{{ $availableDaysToCancel }}
                                                        day{{ $availableDaysToCancel > 1 ? 's' : '' }}</strong>
                                                    <br>
                                                @endif
                                                <strong>Partial Cancellation:</strong> You can cancel part or all of your
                                                approved leave, even if the leave has already started.
                                                Cancelled days will be returned to your leave entitlement.
                                                <br><strong>Example:</strong> If you have 3 days leave and cancel 2 days,
                                                you'll
                                                have 1 day remaining and 2 days returned to your entitlement.
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="reason">
                                                <i class="fas fa-comment-alt mr-1"></i>
                                                Reason for Cancellation <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control @error('reason') is-invalid @enderror" name="reason" id="reason" rows="4"
                                                placeholder="Please provide a detailed reason for cancelling your leave..." required>{{ old('reason') }}</textarea>
                                            @error('reason')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                This reason will be reviewed by HR before approval.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            @if ($availableDaysToCancel > 0)
                                                <button type="submit" class="btn btn-danger"
                                                    onclick="return confirm('Are you sure you want to submit this cancellation request?')">
                                                    <i class="fas fa-paper-plane"></i> Submit Cancellation Request
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-danger" disabled>
                                                    <i class="fas fa-ban"></i> No Days Available to Cancel
                                                </button>
                                            @endif
                                            <a href="{{ $showRoute }}" class="btn btn-secondary ml-2">
                                                <i class="fas fa-times"></i> Cancel
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-info card-outline elevation-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Important Notes</strong>
                            </h3>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3">
                                    <i class="fas fa-info-circle text-info mr-2"></i>
                                    <strong>Cancellation requests require HR approval</strong>
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-calendar text-success mr-2"></i>
                                    <strong>Cancelled days will be returned to your leave balance</strong>
                                </li>
                                <li class="mb-0">
                                    <i class="fas fa-file-alt text-primary mr-2"></i>
                                    <strong>You can only have one pending cancellation request at a time</strong>
                                </li>
                            </ul>
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
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }

        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        .list-unstyled li {
            padding: 0.25rem 0;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .text-info {
            color: #17a2b8 !important;
        }

        .text-warning {
            color: #ffc107 !important;
        }

        .text-success {
            color: #28a745 !important;
        }

        .text-primary {
            color: #007bff !important;
        }

        /* Compact Leave Request Information Styles */
        .border-left-primary {
            border-left: 4px solid #007bff !important;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.875rem;
            min-width: 80px;
        }

        .info-value {
            color: #495057;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-indicator {
            text-align: center;
        }

        .status-text {
            font-size: 0.75rem;
            color: #28a745;
            font-weight: 600;
            margin-top: 0.25rem;
        }

        .badge-sm {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }

        .font-weight-bold {
            font-weight: 700 !important;
        }

        /* Compact Leave Request Information Styles */
        .border-left-primary {
            border-left: 4px solid #007bff !important;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.875rem;
            min-width: 80px;
        }

        .info-value {
            color: #495057;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-indicator {
            text-align: center;
        }

        .status-text {
            font-size: 0.75rem;
            color: #28a745;
            font-weight: 600;
            margin-top: 0.25rem;
        }

        .badge-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .elevation-3 {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .card-outline {
            border-top: 3px solid #007bff !important;
        }

        .card-info.card-outline {
            border-top-color: #17a2b8 !important;
        }

        .card-primary.card-outline {
            border-top-color: #007bff !important;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Auto-resize textarea
            $('#reason').on('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });

            // Form validation
            $('form').on('submit', function(e) {
                const daysToCancel = $('#days_to_cancel').val();
                const reason = $('#reason').val().trim();

                if (!daysToCancel) {
                    e.preventDefault();
                    alert('Please select the number of days to cancel.');
                    $('#days_to_cancel').focus();
                    return false;
                }

                if (!reason) {
                    e.preventDefault();
                    alert('Please provide a reason for cancellation.');
                    $('#reason').focus();
                    return false;
                }
            });
        });
    </script>
@endsection
