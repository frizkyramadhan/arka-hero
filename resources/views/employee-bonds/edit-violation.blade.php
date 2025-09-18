@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Bond Violation</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('employee-bonds.index') }}">Employee Bonds</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('bond-violations.index') }}">Violations</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Edit Bond Violation</h3>
                        </div>
                        <form action="{{ route('bond-violations.update', $bondViolation->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="employee_bond_id">Employee Bond <span class="text-danger">*</span></label>
                                    <select name="employee_bond_id" id="employee_bond_id"
                                        class="form-control select2bs4 @error('employee_bond_id') is-invalid @enderror"
                                        required>
                                        <option value="">Select Employee Bond</option>
                                        @foreach ($employeeBonds as $bond)
                                            <option value="{{ $bond->id }}"
                                                data-investment="{{ $bond->total_investment_value }}"
                                                data-start="{{ $bond->start_date->format('Y-m-d') }}"
                                                data-end="{{ $bond->end_date->format('Y-m-d') }}"
                                                {{ old('employee_bond_id', $bondViolation->employee_bond_id) == $bond->id ? 'selected' : '' }}>
                                                {{ $bond->nik }} - {{ $bond->employee->fullname }} -
                                                {{ $bond->bond_name }}
                                                ({{ $bond->start_date->format('d/m/Y') }} -
                                                {{ $bond->end_date->format('d/m/Y') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('employee_bond_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="violation_date">Violation Date <span class="text-danger">*</span></label>
                                    <input type="date" name="violation_date" id="violation_date"
                                        class="form-control @error('violation_date') is-invalid @enderror"
                                        value="{{ old('violation_date', $bondViolation->violation_date->format('Y-m-d')) }}"
                                        required>
                                    @error('violation_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="reason">Reason</label>
                                    <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" rows="3"
                                        placeholder="Enter the reason for the violation...">{{ old('reason', $bondViolation->reason) }}</textarea>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="penalty_paid_amount">Paid Amount</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Rp</span>
                                                </div>
                                                <input type="number" name="penalty_paid_amount" id="penalty_paid_amount"
                                                    class="form-control @error('penalty_paid_amount') is-invalid @enderror"
                                                    value="{{ old('penalty_paid_amount', $bondViolation->penalty_paid_amount) }}"
                                                    min="0" step="0.01">
                                            </div>
                                            @error('penalty_paid_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="payment_due_date">Payment Due Date</label>
                                            <input type="date" name="payment_due_date" id="payment_due_date"
                                                class="form-control @error('payment_due_date') is-invalid @enderror"
                                                value="{{ old('payment_due_date', $bondViolation->payment_due_date ? $bondViolation->payment_due_date->format('Y-m-d') : '') }}">
                                            @error('payment_due_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Violation
                                </button>
                                <a href="{{ route('bond-violations.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Current Penalty Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Calculated Penalty:</strong></td>
                                        <td>{{ $bondViolation->formatted_calculated_penalty }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Current Paid:</strong></td>
                                        <td>{{ $bondViolation->formatted_paid_penalty }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Remaining:</strong></td>
                                        <td>{{ $bondViolation->formatted_remaining_penalty }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Days Worked:</strong></td>
                                        <td>{{ $bondViolation->days_worked }} days</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Days Remaining:</strong></td>
                                        <td>{{ $bondViolation->days_remaining }} days</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Payment Status:</strong></td>
                                        <td>
                                            @if ($bondViolation->payment_status == 'paid')
                                                <span class="badge badge-success">Paid</span>
                                            @elseif($bondViolation->payment_status == 'partial')
                                                <span class="badge badge-warning">Partial</span>
                                            @else
                                                <span class="badge badge-danger">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('scripts')
    <!-- Bootstrap 4 -->
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        $(function() {
            // Initialize Select2 Elements with Bootstrap4 theme
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option',
                allowClear: true
            });

            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            });
        });
    </script>
@endsection
