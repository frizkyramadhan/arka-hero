@extends('layouts.main')

@section('title', 'Edit Leave Type')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Leave Type</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave.types.index') }}">Leave Types</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('leave.types.show', $leaveType) }}">{{ $leaveType->name }}</a></li>
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
                            <h3 class="card-title">Edit Leave Type</h3>
                            <div class="card-tools">
                                <a href="{{ route('leave.types.show', $leaveType) }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('leave.types.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                        <form action="{{ route('leave.types.update', $leaveType) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                id="name" name="name" value="{{ old('name', $leaveType->name) }}"
                                                required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="code">Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('code') is-invalid @enderror"
                                                id="code" name="code" value="{{ old('code', $leaveType->code) }}"
                                                required>
                                            @error('code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="category">Category <span class="text-danger">*</span></label>
                                            <select class="form-control @error('category') is-invalid @enderror"
                                                id="category" name="category" required>
                                                <option value="">Select Category</option>
                                                <option value="annual"
                                                    {{ old('category', $leaveType->category) == 'annual' ? 'selected' : '' }}>
                                                    Annual</option>
                                                <option value="paid"
                                                    {{ old('category', $leaveType->category) == 'paid' ? 'selected' : '' }}>
                                                    Paid</option>
                                                <option value="unpaid"
                                                    {{ old('category', $leaveType->category) == 'unpaid' ? 'selected' : '' }}>
                                                    Unpaid</option>
                                                <option value="lsl"
                                                    {{ old('category', $leaveType->category) == 'lsl' ? 'selected' : '' }}>
                                                    Long Service Leave</option>
                                                <option value="periodic"
                                                    {{ old('category', $leaveType->category) == 'periodic' ? 'selected' : '' }}>
                                                    Periodic Leave</option>
                                            </select>
                                            @error('category')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="default_days">Default Days</label>
                                            <input type="number"
                                                class="form-control @error('default_days') is-invalid @enderror"
                                                id="default_days" name="default_days"
                                                value="{{ old('default_days', $leaveType->default_days) }}" min="0">
                                            @error('default_days')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="eligible_after_years">Eligible After (Years)</label>
                                            <input type="number"
                                                class="form-control @error('eligible_after_years') is-invalid @enderror"
                                                id="eligible_after_years" name="eligible_after_years"
                                                value="{{ old('eligible_after_years', $leaveType->eligible_after_years) }}"
                                                min="0">
                                            @error('eligible_after_years')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="deposit_days_first">Deposit Days (First Period)</label>
                                            <input type="number"
                                                class="form-control @error('deposit_days_first') is-invalid @enderror"
                                                id="deposit_days_first" name="deposit_days_first"
                                                value="{{ old('deposit_days_first', $leaveType->deposit_days_first) }}"
                                                min="0">
                                            @error('deposit_days_first')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="carry_over"
                                                    name="carry_over" value="1"
                                                    {{ old('carry_over', $leaveType->carry_over) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="carry_over">
                                                    Allow Carry Over
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="is_active"
                                                    name="is_active" value="1"
                                                    {{ old('is_active', $leaveType->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    Active
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="remarks">Remarks</label>
                                    <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="3">{{ old('remarks', $leaveType->remarks) }}</textarea>
                                    @error('remarks')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Leave Type
                                </button>
                                <a href="{{ route('leave.types.show', $leaveType) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h5><i class="icon fas fa-info"></i> Note!</h5>
                                <ul class="mb-0">
                                    <li>Leave type code must be unique</li>
                                    <li>Default days represent the standard entitlement</li>
                                    <li>Eligible after years determines minimum service requirement</li>
                                    <li>Deposit days apply to first period of long service leave</li>
                                    <li>Carry over allows unused days to be transferred</li>
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
            // Auto-fill code based on name
            $('#name').on('input', function() {
                if ($('#code').val() === '') {
                    let code = $(this).val()
                        .toLowerCase()
                        .replace(/[^a-z0-9]/g, '')
                        .substring(0, 10);
                    $('#code').val(code);
                }
            });

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

                if (!isValid) {
                    e.preventDefault();
                    toastr.error('Please fill in all required fields.');
                }
            });
        });
    </script>
@endpush
