@extends('layouts.main')

@section('title', 'Create Leave Entitlement')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create Leave Entitlement</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave.entitlements.index') }}">Leave Entitlements</a>
                        </li>
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
                            <h3 class="card-title">Leave Entitlement Form</h3>
                        </div>
                        <form method="POST" action="{{ route('leave.entitlements.store') }}">
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
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="period_start">Period Start <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" name="period_start" id="period_start"
                                                class="form-control @error('period_start') is-invalid @enderror"
                                                value="{{ old('period_start') }}" required>
                                            @error('period_start')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="period_end">Period End <span class="text-danger">*</span></label>
                                            <input type="date" name="period_end" id="period_end"
                                                class="form-control @error('period_end') is-invalid @enderror"
                                                value="{{ old('period_end') }}" required>
                                            @error('period_end')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="entitled_days">Entitled Days <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="entitled_days" id="entitled_days"
                                                class="form-control @error('entitled_days') is-invalid @enderror"
                                                value="{{ old('entitled_days') }}" min="0" required>
                                            @error('entitled_days')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="withdrawable_days">Withdrawable Days <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="withdrawable_days" id="withdrawable_days"
                                                class="form-control @error('withdrawable_days') is-invalid @enderror"
                                                value="{{ old('withdrawable_days') }}" min="0" required>
                                            @error('withdrawable_days')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="deposit_days">Deposit Days</label>
                                            <input type="number" name="deposit_days" id="deposit_days"
                                                class="form-control @error('deposit_days') is-invalid @enderror"
                                                value="{{ old('deposit_days', 0) }}" min="0">
                                            @error('deposit_days')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="carried_over">Carried Over</label>
                                            <input type="number" name="carried_over" id="carried_over"
                                                class="form-control @error('carried_over') is-invalid @enderror"
                                                value="{{ old('carried_over', 0) }}" min="0">
                                            @error('carried_over')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="taken_days">Taken Days</label>
                                            <input type="number" name="taken_days" id="taken_days"
                                                class="form-control @error('taken_days') is-invalid @enderror"
                                                value="{{ old('taken_days', 0) }}" min="0">
                                            @error('taken_days')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Remaining Days</label>
                                            <input type="text" id="remaining_days_display" class="form-control"
                                                readonly>
                                            <small class="form-text text-muted">Calculated automatically</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Entitlement
                                </button>
                                <a href="{{ route('leave.entitlements.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Leave Type Information -->
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

                    <!-- Employee Information -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Employee Information</h3>
                        </div>
                        <div class="card-body">
                            <div id="employee_info">
                                <p class="text-muted">Select an employee to view details</p>
                            </div>
                        </div>
                    </div>

                    <!-- Calculation Help -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Calculation Help</h3>
                        </div>
                        <div class="card-body">
                            <h6>Annual Leave:</h6>
                            <ul class="list-unstyled">
                                <li>• Entitled: 12 days</li>
                                <li>• Withdrawable: 12 days</li>
                                <li>• Deposit: 0 days</li>
                            </ul>

                            <h6>Long Service Leave (LSL):</h6>
                            <ul class="list-unstyled">
                                <li>• First Period: 40 withdrawable + 10 deposit</li>
                                <li>• Next Periods: 50 withdrawable + 0 deposit</li>
                                <li>• Carried Over: Previous remaining days</li>
                            </ul>
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
            // Calculate remaining days
            function calculateRemainingDays() {
                const withdrawable = parseInt($('#withdrawable_days').val()) || 0;
                const taken = parseInt($('#taken_days').val()) || 0;
                const remaining = withdrawable - taken;
                $('#remaining_days_display').val(remaining + ' days');
            }

            $('#withdrawable_days, #taken_days').on('input', calculateRemainingDays);

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

            // Load employee info when employee is selected
            $('#employee_id').on('change', function() {
                const employeeId = $(this).val();
                if (employeeId) {
                    loadEmployeeInfo(employeeId);
                } else {
                    $('#employee_info').html(
                        '<p class="text-muted">Select an employee to view details</p>');
                }
            });

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

                        // Auto-fill default values
                        $('#entitled_days').val(data.default_days);
                        $('#withdrawable_days').val(data.default_days);
                        $('#deposit_days').val(data.deposit_days_first || 0);

                        calculateRemainingDays();

                        $('#leave_type_info').html(html);
                    })
                    .fail(function() {
                        $('#leave_type_info').html('<p class="text-danger">Failed to load leave type info</p>');
                    });
            }

            function loadEmployeeInfo(employeeId) {
                $.get(`/api/employees/${employeeId}`)
                    .done(function(data) {
                        let html = `<div class="text-center">
                    <img src="{{ asset('images/default-avatar.png') }}" class="img-circle img-fluid" width="80" height="80" alt="Employee Photo">
                    <h5>${data.fullname}</h5>
                    <p class="text-muted">${data.employee_id}</p>
                </div>`;

                        if (data.position) {
                            html += `<p><strong>Position:</strong> ${data.position.position_name}</p>`;
                        }
                        if (data.department) {
                            html += `<p><strong>Department:</strong> ${data.department.department_name}</p>`;
                        }

                        $('#employee_info').html(html);
                    })
                    .fail(function() {
                        $('#employee_info').html('<p class="text-danger">Failed to load employee info</p>');
                    });
            }
        });
    </script>
@endpush
