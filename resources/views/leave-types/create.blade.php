@extends('layouts.main')

@section('title', 'Create Leave Type')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create Leave Type</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave.types.index') }}">Leave Types</a></li>
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
                            <h3 class="card-title">Leave Type Form</h3>
                        </div>
                        <form method="POST" action="{{ route('leave.types.store') }}">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" id="name"
                                                class="form-control @error('name') is-invalid @enderror"
                                                value="{{ old('name') }}" placeholder="e.g., Annual Leave" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="code">Code <span class="text-danger">*</span></label>
                                            <input type="text" name="code"
                                                class="form-control @error('code') is-invalid @enderror"
                                                value="{{ old('code') }}" required>
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
                                            <select name="category" id="category"
                                                class="form-control @error('category') is-invalid @enderror" required>
                                                <option value="">Select Category</option>
                                                <option value="annual" {{ old('category') == 'annual' ? 'selected' : '' }}>
                                                    Annual</option>
                                                <option value="paid" {{ old('category') == 'paid' ? 'selected' : '' }}>
                                                    Paid
                                                </option>
                                                <option value="unpaid" {{ old('category') == 'unpaid' ? 'selected' : '' }}>
                                                    Unpaid</option>
                                                <option value="lsl" {{ old('category') == 'lsl' ? 'selected' : '' }}>
                                                    Long Service Leave
                                                </option>
                                                <option value="periodic"
                                                    {{ old('category') == 'periodic' ? 'selected' : '' }}>
                                                    Periodic Leave
                                                </option>
                                            </select>
                                            @error('category')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="default_days">Default Days <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="default_days" id="default_days"
                                                class="form-control @error('default_days') is-invalid @enderror"
                                                value="{{ old('default_days') }}" min="0" required>
                                            @error('default_days')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="eligible_after_years">Eligible After (Years) <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="eligible_after_years" id="eligible_after_years"
                                                class="form-control @error('eligible_after_years') is-invalid @enderror"
                                                value="{{ old('eligible_after_years') }}" min="0" required>
                                            @error('eligible_after_years')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="deposit_days_first">Deposit Days (First Period)</label>
                                            <input type="number" name="deposit_days_first" id="deposit_days_first"
                                                class="form-control @error('deposit_days_first') is-invalid @enderror"
                                                value="{{ old('deposit_days_first', 0) }}" min="0">
                                            <small class="form-text text-muted">For LSL first period only</small>
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
                                                <input type="checkbox" name="carry_over" id="carry_over"
                                                    class="form-check-input" value="1"
                                                    {{ old('carry_over') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="carry_over">
                                                    Allow Carry Over
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">Allow unused days to carry over to next
                                                period</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" name="is_active" id="is_active"
                                                    class="form-check-input" value="1"
                                                    {{ old('is_active', true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    Active
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">Leave type is available for use</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="remarks">Remarks</label>
                                    <textarea name="remarks" id="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror"
                                        placeholder="Additional notes or special conditions...">{{ old('remarks') }}</textarea>
                                    @error('remarks')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Leave Type
                                </button>
                                <a href="{{ route('leave.types.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Category Information -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Category Information</h3>
                        </div>
                        <div class="card-body">
                            <h6>Annual Leave:</h6>
                            <ul class="list-unstyled">
                                <li>• Regular yearly leave entitlement</li>
                                <li>• Usually 12 days per year</li>
                                <li>• Eligible after 1 year of service</li>
                            </ul>

                            <h6>Paid Leave:</h6>
                            <ul class="list-unstyled">
                                <li>• Leave with pay for special occasions</li>
                                <li>• Marriage, childbirth, etc.</li>
                                <li>• Usually 2-3 days per occasion</li>
                            </ul>

                            <h6>Unpaid Leave:</h6>
                            <ul class="list-unstyled">
                                <li>• Leave without pay</li>
                                <li>• No salary deduction</li>
                                <li>• For personal reasons</li>
                            </ul>

                            <h6>Long Service Leave (LSL):</h6>
                            <ul class="list-unstyled">
                                <li>• Extended leave after long service</li>
                                <li>• Usually 50 days every 5-6 years</li>
                                <li>• First period: 40 withdrawable + 10 deposit</li>
                            </ul>

                            <h6>Periodic Leave:</h6>
                            <ul class="list-unstyled">
                                <li>• Leave that occurs at regular intervals</li>
                                <li>• Monthly, quarterly, or yearly cycles</li>
                                <li>• For maintenance, training, or special events</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Validation Rules -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Validation Rules</h3>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Name must be unique</li>
                                <li><i class="fas fa-check text-success"></i> Code must be unique</li>
                                <li><i class="fas fa-check text-success"></i> Default days must be ≥ 0</li>
                                <li><i class="fas fa-check text-success"></i> Eligible years must be ≥ 0</li>
                                <li><i class="fas fa-check text-success"></i> Deposit days must be ≥ 0</li>
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
            // Set default values based on category
            $('#category').on('change', function() {
                const category = $(this).val();

                switch (category) {
                    case 'annual':
                        $('#default_days').val(12);
                        $('#eligible_after_years').val(1);
                        $('#deposit_days_first').val(0);
                        $('#carry_over').prop('checked', false);
                        break;
                    case 'paid':
                        $('#default_days').val(0);
                        $('#eligible_after_years').val(0);
                        $('#deposit_days_first').val(0);
                        $('#carry_over').prop('checked', false);
                        break;
                    case 'unpaid':
                        $('#default_days').val(0);
                        $('#eligible_after_years').val(0);
                        $('#deposit_days_first').val(0);
                        $('#carry_over').prop('checked', false);
                        break;
                    case 'lsl':
                        $('#default_days').val(50);
                        $('#eligible_after_years').val(5);
                        $('#deposit_days_first').val(10);
                        $('#carry_over').prop('checked', true);
                        break;
                    case 'periodic':
                        $('#default_days').val(1);
                        $('#eligible_after_years').val(0);
                        $('#deposit_days_first').val(0);
                        $('#carry_over').prop('checked', false);
                        break;
                }
            });
        });
    </script>
@endpush
