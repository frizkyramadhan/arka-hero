@extends('layouts.main')

@section('title', 'Edit Leave Request')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Leave Request</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave-requests.index') }}">Leave Requests</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave-requests.show', $leaveRequest) }}">Request
                                #{{ $leaveRequest->id }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Edit Leave Request</h3>
                            <div class="card-tools">
                                <a href="{{ route('leave-requests.show', $leaveRequest) }}"
                                    class="btn btn-secondary btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                        <form action="{{ route('leave-requests.update', $leaveRequest) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="employee_id">Employee <span class="text-danger">*</span></label>
                                            <select class="form-control @error('employee_id') is-invalid @enderror"
                                                id="employee_id" name="employee_id" required>
                                                <option value="">Select Employee</option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{ $employee->id }}"
                                                        {{ old('employee_id', $leaveRequest->employee_id) == $employee->id ? 'selected' : '' }}>
                                                        {{ $employee->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('employee_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="leave_type_id">Leave Type <span class="text-danger">*</span></label>
                                            <select class="form-control @error('leave_type_id') is-invalid @enderror"
                                                id="leave_type_id" name="leave_type_id" required>
                                                <option value="">Select Leave Type</option>
                                                @foreach ($leaveTypes as $leaveType)
                                                    <option value="{{ $leaveType->id }}"
                                                        {{ old('leave_type_id', $leaveRequest->leave_type_id) == $leaveType->id ? 'selected' : '' }}>
                                                        {{ $leaveType->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('leave_type_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                            <input type="date"
                                                class="form-control @error('start_date') is-invalid @enderror"
                                                id="start_date" name="start_date"
                                                value="{{ old('start_date', $leaveRequest->start_date->format('Y-m-d')) }}"
                                                required>
                                            @error('start_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="end_date">End Date <span class="text-danger">*</span></label>
                                            <input type="date"
                                                class="form-control @error('end_date') is-invalid @enderror" id="end_date"
                                                name="end_date"
                                                value="{{ old('end_date', $leaveRequest->end_date->format('Y-m-d')) }}"
                                                required>
                                            @error('end_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="total_days">Total Days</label>
                                            <input type="number"
                                                class="form-control @error('total_days') is-invalid @enderror"
                                                id="total_days" name="total_days"
                                                value="{{ old('total_days', $leaveRequest->total_days) }}" min="1"
                                                readonly>
                                            @error('total_days')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="back_to_work_date">Back to Work Date</label>
                                            <input type="date"
                                                class="form-control @error('back_to_work_date') is-invalid @enderror"
                                                id="back_to_work_date" name="back_to_work_date"
                                                value="{{ old('back_to_work_date', $leaveRequest->back_to_work_date ? $leaveRequest->back_to_work_date->format('Y-m-d') : '') }}">
                                            @error('back_to_work_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="reason">Reason <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3"
                                        required>{{ old('reason', $leaveRequest->reason) }}</textarea>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="leave_period">Leave Period</label>
                                    <input type="text" class="form-control @error('leave_period') is-invalid @enderror"
                                        id="leave_period" name="leave_period"
                                        value="{{ old('leave_period', $leaveRequest->leave_period) }}"
                                        placeholder="e.g., 2024 Annual Leave">
                                    @error('leave_period')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Request
                                </button>
                                <a href="{{ route('leave-requests.show', $leaveRequest) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Current Status</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h5><i class="icon fas fa-info"></i> Status:
                                    @switch($leaveRequest->status)
                                        @case('pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @break

                                        @case('approved')
                                            <span class="badge badge-success">Approved</span>
                                        @break

                                        @case('rejected')
                                            <span class="badge badge-danger">Rejected</span>
                                        @break

                                        @case('cancelled')
                                            <span class="badge badge-secondary">Cancelled</span>
                                        @break

                                        @case('auto_approved')
                                            <span class="badge badge-info">Auto Approved</span>
                                        @break
                                    @endswitch
                                </h5>
                                <p class="mb-0">Requested on:
                                    {{ $leaveRequest->requested_at ? $leaveRequest->requested_at->format('d M Y H:i') : 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <h5><i class="icon fas fa-exclamation-triangle"></i> Note!</h5>
                                <ul class="mb-0">
                                    <li>Total days are calculated automatically</li>
                                    <li>Back to work date is required for long service leave</li>
                                    <li>Leave period helps identify the entitlement period</li>
                                    <li>Only pending requests can be edited</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Calculate total days when dates change
            function calculateTotalDays() {
                const startDate = new Date($('#start_date').val());
                const endDate = new Date($('#end_date').val());

                if (startDate && endDate && startDate <= endDate) {
                    const timeDiff = endDate.getTime() - startDate.getTime();
                    const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) +
                        1; // +1 to include both start and end dates
                    $('#total_days').val(daysDiff);
                } else {
                    $('#total_days').val('');
                }
            }

            $('#start_date, #end_date').on('change', calculateTotalDays);

            // Form validation
            $('form').on('submit', function(e) {
                let isValid = true;

                // Check required fields
                $('input[required], select[required], textarea[required]').each(function() {
                    if ($(this).val() === '') {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                // Check date validation
                const startDate = new Date($('#start_date').val());
                const endDate = new Date($('#end_date').val());

                if (startDate && endDate && startDate > endDate) {
                    $('#end_date').addClass('is-invalid');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    toastr.error('Please fill in all required fields correctly.');
                }
            });
        });
    </script>
@endpush
