@extends('layouts.main')

@section('title', 'Create Leave Request')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create Leave Request</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave.requests.index') }}">Leave Requests</a></li>
                        <li class="breadcrumb-item active">Create</li>
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
                            <h3 class="card-title">Leave Request Form</h3>
                        </div>
                        <form method="POST" action="{{ route('leave.requests.store') }}">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="employee_id">Employee <span class="text-danger">*</span></label>
                                            <select name="employee_id" id="employee_id"
                                                class="form-control @error('employee_id') is-invalid @enderror" required>
                                                <option value="">Select Employee</option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{ $employee->id }}"
                                                        {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                                        {{ $employee->fullname }} - {{ $employee->employee_id }}
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
                                            <select name="leave_type_id" id="leave_type_id"
                                                class="form-control @error('leave_type_id') is-invalid @enderror" required>
                                                <option value="">Select Leave Type</option>
                                                @foreach ($leaveTypes as $leaveType)
                                                    <option value="{{ $leaveType->id }}"
                                                        {{ old('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                                                        {{ $leaveType->name }} ({{ $leaveType->code }})
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
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                            <input type="date" name="start_date" id="start_date"
                                                class="form-control @error('start_date') is-invalid @enderror"
                                                value="{{ old('start_date') }}" required>
                                            @error('start_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="end_date">End Date <span class="text-danger">*</span></label>
                                            <input type="date" name="end_date" id="end_date"
                                                class="form-control @error('end_date') is-invalid @enderror"
                                                value="{{ old('end_date') }}" required>
                                            @error('end_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="back_to_work_date">Back to Work Date</label>
                                            <input type="date" name="back_to_work_date" id="back_to_work_date"
                                                class="form-control @error('back_to_work_date') is-invalid @enderror"
                                                value="{{ old('back_to_work_date') }}">
                                            <small class="form-text text-muted">Required for Long Service Leave</small>
                                            @error('back_to_work_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="leave_period">Leave Period</label>
                                            <input type="text" name="leave_period" id="leave_period"
                                                class="form-control @error('leave_period') is-invalid @enderror"
                                                value="{{ old('leave_period') }}" placeholder="e.g., 2025">
                                            <small class="form-text text-muted">For annual and long service
                                                leave</small>
                                            @error('leave_period')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Total Days</label>
                                            <input type="text" id="total_days_display" class="form-control" readonly>
                                            <small class="form-text text-muted">Calculated automatically</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="reason">Reason <span class="text-danger">*</span></label>
                                    <textarea name="reason" id="reason" rows="4" class="form-control @error('reason') is-invalid @enderror"
                                        placeholder="Please provide a detailed reason for your leave request..." required>{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Submit Request
                                </button>
                                <a href="{{ route('leave.requests.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Leave Balance Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Leave Balance</h3>
                        </div>
                        <div class="card-body">
                            <div id="leave_balance_info">
                                <p class="text-muted">Select an employee to view leave balance</p>
                            </div>
                        </div>
                    </div>

                    <!-- Leave Type Info Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Leave Type Information</h3>
                        </div>
                        <div class="card-body">
                            <div id="leave_type_info">
                                <p class="text-muted">Select a leave type to view details</p>
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
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();

                if (startDate && endDate) {
                    const start = new Date(startDate);
                    const end = new Date(endDate);
                    const diffTime = Math.abs(end - start);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    $('#total_days_display').val(diffDays + ' days');
                } else {
                    $('#total_days_display').val('');
                }
            }

            $('#start_date, #end_date').on('change', calculateTotalDays);

            // Load leave balance when employee is selected
            $('#employee_id').on('change', function() {
                const employeeId = $(this).val();
                if (employeeId) {
                    loadLeaveBalance(employeeId);
                } else {
                    $('#leave_balance_info').html(
                        '<p class="text-muted">Select an employee to view leave balance</p>');
                }
            });

            // Load leave type info when leave type is selected
            $('#leave_type_id').on('change', function() {
                const leaveTypeId = $(this).val();
                if (leaveTypeId) {
                    loadLeaveTypeInfo(leaveTypeId);
                } else {
                    $('#leave_type_info').html(
                        '<p class="text-muted">Select a leave type to view details</p>');
                }
            });

            function loadLeaveBalance(employeeId) {
                $.get(`/api/employees/${employeeId}/leave-balance`)
                    .done(function(data) {
                        let html = '<div class="table-responsive"><table class="table table-sm">';
                        html += '<thead><tr><th>Leave Type</th><th>Balance</th></tr></thead><tbody>';

                        if (data.length > 0) {
                            data.forEach(function(balance) {
                                html += `<tr>
                            <td>${balance.leave_type_name}</td>
                            <td><span class="badge badge-info">${balance.remaining_days} days</span></td>
                        </tr>`;
                            });
                        } else {
                            html +=
                                '<tr><td colspan="2" class="text-center text-muted">No leave balance found</td></tr>';
                        }

                        html += '</tbody></table></div>';
                        $('#leave_balance_info').html(html);
                    })
                    .fail(function() {
                        $('#leave_balance_info').html(
                            '<p class="text-danger">Failed to load leave balance</p>');
                    });
            }

            function loadLeaveTypeInfo(leaveTypeId) {
                $.get(`/api/leave/types/${leaveTypeId}`)
                    .done(function(data) {
                        let html = `<div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-text">${data.name}</span>
                        <span class="info-box-number">${data.default_days} days</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            Eligible after ${data.eligible_after_years} years
                        </span>
                    </div>
                </div>`;

                        if (data.remarks) {
                            html += `<p class="text-muted"><small>${data.remarks}</small></p>`;
                        }

                        $('#leave_type_info').html(html);
                    })
                    .fail(function() {
                        $('#leave_type_info').html('<p class="text-danger">Failed to load leave type info</p>');
                    });
            }
        });
    </script>
@endpush
