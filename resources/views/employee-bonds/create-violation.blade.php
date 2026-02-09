@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create Bond Violation</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('employee-bonds.index') }}">Employee Bonds</a></li>
                        <li class="breadcrumb-item active">Create Violation</li>
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
                            <h3 class="card-title">Create Bond Violation</h3>
                        </div>
                        <form action="{{ route('bond-violations.store') }}" method="POST">
                            @csrf
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
                                                {{ old('employee_bond_id') == $bond->id || (isset($selectedBondId) && $selectedBondId == $bond->id) ? 'selected' : '' }}>
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
                                        value="{{ old('violation_date') }}" required>
                                    @error('violation_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="reason">Reason</label>
                                    <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" rows="3"
                                        placeholder="Enter the reason for the violation...">{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="payment_due_date">Payment Due Date</label>
                                    <input type="date" name="payment_due_date" id="payment_due_date"
                                        class="form-control @error('payment_due_date') is-invalid @enderror"
                                        value="{{ old('payment_due_date') }}">
                                    @error('payment_due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Violation
                                </button>
                                <a href="{{ route('employee-bonds.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Penalty Calculation</h3>
                        </div>
                        <div class="card-body">
                            <div id="penalty-calculation">
                                <p class="text-muted">Select employee bond and violation date to see penalty calculation</p>
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

            // Auto-select bond if employee_bond_id is provided in URL
            @if (isset($selectedBondId) && $selectedBondId)
                $('#employee_bond_id').val('{{ $selectedBondId }}').trigger('change');
            @endif
        });

        document.addEventListener('DOMContentLoaded', function() {
            const bondSelect = document.getElementById('employee_bond_id');
            const violationDateInput = document.getElementById('violation_date');
            const penaltyDiv = document.getElementById('penalty-calculation');

            function calculatePenalty() {
                const bondId = bondSelect.value;
                const violationDate = violationDateInput.value;

                if (!bondId || !violationDate) {
                    penaltyDiv.innerHTML =
                        '<p class="text-muted">Select employee bond and violation date to see penalty calculation</p>';
                    return;
                }

                fetch('{{ route('bond-violations.calculate-penalty') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            employee_bond_id: bondId,
                            violation_date: violationDate
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.is_valid) {
                            const details = data.calculation_details;
                            penaltyDiv.innerHTML = `
                    <p class="small text-muted mb-2"><i class="fas fa-info-circle"></i> Penalty = jumlah tetap (biaya pelatihan).</p>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Total Investment (Biaya Pelatihan):</strong></td>
                                <td>Rp ${new Intl.NumberFormat('id-ID').format(details.investment_value)}</td>
                            </tr>
                            <tr>
                                <td><strong>Bond Period:</strong></td>
                                <td>${details.total_days} days</td>
                            </tr>
                            <tr>
                                <td><strong>Days Worked:</strong></td>
                                <td>${details.days_worked} days (${details.percentage_worked}%)</td>
                            </tr>
                            <tr>
                                <td><strong>Remaining Days:</strong></td>
                                <td>${details.remaining_days} days (${details.percentage_remaining}%)</td>
                            </tr>
                            <tr class="table-danger">
                                <td><strong>Total Penalty (Fixed):</strong></td>
                                <td><strong>Rp ${new Intl.NumberFormat('id-ID').format(data.penalty_amount)}</strong></td>
                            </tr>
                        </table>
                    </div>
                `;
                        } else {
                            penaltyDiv.innerHTML = `<p class="text-danger">${data.message}</p>`;
                        }
                    })
                    .catch(error => {
                        penaltyDiv.innerHTML = '<p class="text-danger">Error calculating penalty</p>';
                    });
            }

            bondSelect.addEventListener('change', calculatePenalty);
            violationDateInput.addEventListener('change', calculatePenalty);
        });
    </script>
@endsection
