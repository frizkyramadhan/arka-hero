@extends('layouts.main')

@section('title', 'Edit Leave Entitlement')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Leave Entitlement</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave.entitlements.index') }}">Leave Entitlements</a>
                        </li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('leave.entitlements.show', $entitlement) }}">Entitlement
                                #{{ $entitlement->id }}</a></li>
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
                            <h3 class="card-title">Edit Leave Entitlement</h3>
                            <div class="card-tools">
                                <a href="{{ route('leave.entitlements.show', $entitlement) }}"
                                    class="btn btn-secondary btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('leave.entitlements.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                        <form action="{{ route('leave.entitlements.update', $entitlement) }}" method="POST">
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
                                                        {{ old('employee_id', $entitlement->employee_id) == $employee->id ? 'selected' : '' }}>
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
                                                        {{ old('leave_type_id', $entitlement->leave_type_id) == $leaveType->id ? 'selected' : '' }}>
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
                                            <label for="period_start">Period Start <span
                                                    class="text-danger">*</span></label>
                                            <input type="date"
                                                class="form-control @error('period_start') is-invalid @enderror"
                                                id="period_start" name="period_start"
                                                value="{{ old('period_start', $entitlement->period_start->format('Y-m-d')) }}"
                                                required>
                                            @error('period_start')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="period_end">Period End <span class="text-danger">*</span></label>
                                            <input type="date"
                                                class="form-control @error('period_end') is-invalid @enderror"
                                                id="period_end" name="period_end"
                                                value="{{ old('period_end', $entitlement->period_end->format('Y-m-d')) }}"
                                                required>
                                            @error('period_end')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="entitled_days">Entitled Days</label>
                                            <input type="number"
                                                class="form-control @error('entitled_days') is-invalid @enderror"
                                                id="entitled_days" name="entitled_days"
                                                value="{{ old('entitled_days', $entitlement->entitled_days) }}"
                                                min="0">
                                            @error('entitled_days')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="withdrawable_days">Withdrawable Days</label>
                                            <input type="number"
                                                class="form-control @error('withdrawable_days') is-invalid @enderror"
                                                id="withdrawable_days" name="withdrawable_days"
                                                value="{{ old('withdrawable_days', $entitlement->withdrawable_days) }}"
                                                min="0">
                                            @error('withdrawable_days')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="deposit_days">Deposit Days</label>
                                            <input type="number"
                                                class="form-control @error('deposit_days') is-invalid @enderror"
                                                id="deposit_days" name="deposit_days"
                                                value="{{ old('deposit_days', $entitlement->deposit_days) }}"
                                                min="0">
                                            @error('deposit_days')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="carried_over">Carried Over</label>
                                            <input type="number"
                                                class="form-control @error('carried_over') is-invalid @enderror"
                                                id="carried_over" name="carried_over"
                                                value="{{ old('carried_over', $entitlement->carried_over) }}"
                                                min="0">
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
                                            <input type="number"
                                                class="form-control @error('taken_days') is-invalid @enderror"
                                                id="taken_days" name="taken_days"
                                                value="{{ old('taken_days', $entitlement->taken_days) }}" min="0">
                                            @error('taken_days')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="remaining_days">Remaining Days</label>
                                            <input type="number"
                                                class="form-control @error('remaining_days') is-invalid @enderror"
                                                id="remaining_days" name="remaining_days"
                                                value="{{ old('remaining_days', $entitlement->remaining_days) }}"
                                                min="0" readonly>
                                            @error('remaining_days')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Entitlement
                                </button>
                                <a href="{{ route('leave.entitlements.show', $entitlement) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Current Values</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th>Entitled:</th>
                                    <td>{{ $entitlement->entitled_days }} days</td>
                                </tr>
                                <tr>
                                    <th>Withdrawable:</th>
                                    <td>{{ $entitlement->withdrawable_days }} days</td>
                                </tr>
                                <tr>
                                    <th>Deposit:</th>
                                    <td>{{ $entitlement->deposit_days }} days</td>
                                </tr>
                                <tr>
                                    <th>Carried Over:</th>
                                    <td>{{ $entitlement->carried_over }} days</td>
                                </tr>
                                <tr>
                                    <th>Taken:</th>
                                    <td>{{ $entitlement->taken_days }} days</td>
                                </tr>
                                <tr>
                                    <th>Remaining:</th>
                                    <td>
                                        <span
                                            class="badge badge-{{ $entitlement->remaining_days > 0 ? 'success' : 'danger' }}">
                                            {{ $entitlement->remaining_days }} days
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h5><i class="icon fas fa-info"></i> Note!</h5>
                                <ul class="mb-0">
                                    <li>Remaining days are calculated automatically</li>
                                    <li>Deposit days apply to first period of long service leave</li>
                                    <li>Withdrawable days are the amount that can be taken or cashed out</li>
                                    <li>Carried over days come from previous periods</li>
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
            // Calculate remaining days
            function calculateRemainingDays() {
                const entitled = parseInt($('#entitled_days').val()) || 0;
                const withdrawable = parseInt($('#withdrawable_days').val()) || 0;
                const deposit = parseInt($('#deposit_days').val()) || 0;
                const carriedOver = parseInt($('#carried_over').val()) || 0;
                const taken = parseInt($('#taken_days').val()) || 0;

                const remaining = entitled - taken;
                $('#remaining_days').val(remaining);
            }

            $('#entitled_days, #taken_days').on('input', calculateRemainingDays);

            // Form validation
            $('form').on('submit', function(e) {
                let isValid = true;

                // Check required fields
                $('input[required], select[required]').each(function() {
                    if ($(this).val() === '') {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                // Check date validation
                const startDate = new Date($('#period_start').val());
                const endDate = new Date($('#period_end').val());

                if (startDate && endDate && startDate > endDate) {
                    $('#period_end').addClass('is-invalid');
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
