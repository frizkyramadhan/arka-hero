@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Employee Bond</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('employee-bonds.index') }}">Employee Bonds</a></li>
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
                            <h3 class="card-title">Edit Employee Bond</h3>
                        </div>

                        <form action="{{ route('employee-bonds.update', $employeeBond->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <!-- Letter Number (Hidden) -->
                                <input type="hidden" name="letter_number_id" id="letter_number_id"
                                    value="{{ old('letter_number_id', $employeeBond->letter_number_id) }}">
                                <input type="hidden" name="letter_number" id="letter_number"
                                    value="{{ old('letter_number', $employeeBond->letter_numbe) }}">

                                <!-- Employee Bond Number -->
                                <div class="form-group">
                                    <label for="employee_bond_number">Employee Bond Number <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                        </div>
                                        <input type="text" name="employee_bond_number" id="employee_bond_number"
                                            class="form-control alert-success @error('employee_bond_number') is-invalid @enderror"
                                            value="{{ old('employee_bond_number', $employeeBond->employee_bond_number) }}"
                                            placeholder="Auto-generated from letter number" readonly>
                                        @error('employee_bond_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="employee_id">Employee <span class="text-danger">*</span></label>
                                            <select name="employee_id" id="employee_id"
                                                class="form-control select2bs4 @error('employee_id') is-invalid @enderror"
                                                required>
                                                <option value="">Select Employee</option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{ $employee->id }}"
                                                        {{ old('employee_id', $employeeBond->employee_id) == $employee->id ? 'selected' : '' }}>
                                                        {{ $employee->nik }} - {{ $employee->fullname }}
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
                                            <label for="bond_name">Bond Name <span class="text-danger">*</span></label>
                                            <input type="text" name="bond_name" id="bond_name"
                                                class="form-control @error('bond_name') is-invalid @enderror"
                                                value="{{ old('bond_name', $employeeBond->bond_name) }}"
                                                placeholder="e.g., Ikatan Dinas Sertifikasi TOT" required>
                                            @error('bond_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                        rows="3" placeholder="Describe the bond details...">{{ old('description', $employeeBond->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="total_investment_value">Investment Value <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Rp</span>
                                                </div>
                                                <input type="number" name="total_investment_value"
                                                    id="total_investment_value"
                                                    class="form-control @error('total_investment_value') is-invalid @enderror"
                                                    value="{{ old('total_investment_value', $employeeBond->total_investment_value) }}"
                                                    min="0" step="0.01" required>
                                            </div>
                                            <small class="form-text text-info">
                                                <i class="fas fa-info-circle"></i> Duration will be calculated
                                                automatically
                                                based on investment value
                                            </small>
                                            @error('total_investment_value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="total_bond_duration_months">Duration (Months) <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="total_bond_duration_months"
                                                id="total_bond_duration_months"
                                                class="form-control @error('total_bond_duration_months') is-invalid @enderror"
                                                value="{{ old('total_bond_duration_months', $employeeBond->total_bond_duration_months) }}"
                                                min="1" readonly>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-lock"></i> Auto-calculated based on investment value
                                            </small>
                                            @error('total_bond_duration_months')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                            <input type="date" name="start_date" id="start_date"
                                                class="form-control @error('start_date') is-invalid @enderror"
                                                value="{{ old('start_date', $employeeBond->start_date->format('Y-m-d')) }}"
                                                required>
                                            @error('start_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="end_date">End Date <span class="text-danger">*</span></label>
                                            <input type="date" name="end_date" id="end_date"
                                                class="form-control @error('end_date') is-invalid @enderror"
                                                value="{{ old('end_date', $employeeBond->end_date->format('Y-m-d')) }}"
                                                readonly>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-lock"></i> Auto-calculated based on start date and
                                                duration
                                            </small>
                                            @error('end_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status">Status <span class="text-danger">*</span></label>
                                            <select name="status" id="status"
                                                class="form-control select2bs4 @error('status') is-invalid @enderror"
                                                required>
                                                <option value="active"
                                                    {{ old('status', $employeeBond->status) == 'active' ? 'selected' : '' }}>
                                                    Active</option>
                                                <option value="completed"
                                                    {{ old('status', $employeeBond->status) == 'completed' ? 'selected' : '' }}>
                                                    Completed</option>
                                                <option value="violated"
                                                    {{ old('status', $employeeBond->status) == 'violated' ? 'selected' : '' }}>
                                                    Violated</option>
                                                <option value="cancelled"
                                                    {{ old('status', $employeeBond->status) == 'cancelled' ? 'selected' : '' }}>
                                                    Cancelled</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="document">Document</label>
                                            <input type="file" name="document" id="document"
                                                class="form-control @error('document') is-invalid @enderror"
                                                accept=".pdf,.doc,.docx">
                                            <small class="form-text text-muted">Upload bond agreement document (PDF,
                                                DOC,
                                                DOCX)</small>
                                            @if ($employeeBond->document_path)
                                                <div class="mt-2">
                                                    <a href="{{ Storage::disk('private')->url($employeeBond->document_path) }}"
                                                        class="btn btn-sm btn-info" target="_blank">
                                                        <i class="fas fa-download"></i> Current Document
                                                    </a>
                                                </div>
                                            @endif
                                            @error('document')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Bond
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
                            <h3 class="card-title">Bond Information</h3>
                        </div>
                        <div class="card-body">
                            <div id="bond-info">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="bg-light">
                                            <tr>
                                                <th colspan="2" class="text-center">
                                                    <i class="fas fa-handshake"></i> Current Bond Information
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Investment Range:</strong></td>
                                                <td id="investment-range">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Bond Period:</strong></td>
                                                <td id="bond-period">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Duration:</strong></td>
                                                <td id="duration-display">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Investment Value:</strong></td>
                                                <td id="investment-value">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Penalty per Month:</strong></td>
                                                <td id="penalty-month">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Penalty per Day:</strong></td>
                                                <td id="penalty-day">-</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
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

    <style>
        /* Employee Bond Number feedback styles */
        #employee_bond_number.alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        #employee_bond_number.alert-warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }
    </style>
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

        document.addEventListener('DOMContentLoaded', function() {
            const investmentInput = document.getElementById('total_investment_value');
            const durationInput = document.getElementById('total_bond_duration_months');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            // Bond duration rules based on investment value
            function calculateDurationFromInvestment(investment) {
                const value = parseFloat(investment) || 0;

                if (value <= 6000000) {
                    return 12; // ≤ Rp. 6.000.000,- = 12 Bulan
                } else if (value <= 12000000) {
                    return 24; // > Rp. 6.000.000,- dan ≤ Rp. 12.000.000,- = 24 Bulan
                } else {
                    return 36; // > Rp. 12.000.000,- = 36 Bulan
                }
            }

            // Calculate end date from start date and duration
            function calculateEndDate(startDate, duration) {
                if (!startDate || !duration) return '';

                const start = new Date(startDate);
                const end = new Date(start);
                end.setMonth(end.getMonth() + parseInt(duration));

                return end.toISOString().split('T')[0];
            }

            // Update duration based on investment value
            function updateDurationFromInvestment() {
                const investment = investmentInput.value;
                if (investment) {
                    const duration = calculateDurationFromInvestment(investment);
                    durationInput.value = duration;
                    updateEndDate();
                    updateBondInfo();
                }
            }

            // Update end date based on start date and duration
            function updateEndDate() {
                const startDate = startDateInput.value;
                const duration = durationInput.value;

                if (startDate && duration) {
                    const endDate = calculateEndDate(startDate, duration);
                    endDateInput.value = endDate;
                    updateBondInfo();
                }
            }

            // Update bond information display
            function updateBondInfo() {
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;
                const duration = durationInput.value;
                const investment = investmentInput.value;

                if (startDate && endDate && duration && investment) {
                    const penaltyPerMonth = investment / duration;
                    const penaltyPerDay = penaltyPerMonth / 30;

                    // Get duration rule info
                    const value = parseFloat(investment) || 0;
                    let ruleInfo = '';
                    if (value <= 6000000) {
                        ruleInfo = '≤ Rp. 6.000.000,-';
                    } else if (value <= 12000000) {
                        ruleInfo = '> Rp. 6.000.000,- dan ≤ Rp. 12.000.000,-';
                    } else {
                        ruleInfo = '> Rp. 12.000.000,-';
                    }

                    // Update the bond info display
                    document.getElementById('investment-range').textContent = ruleInfo;
                    document.getElementById('bond-period').textContent = `${startDate} to ${endDate}`;
                    document.getElementById('duration-display').innerHTML =
                        `<span class="badge badge-primary">${duration} months</span>`;
                    document.getElementById('investment-value').innerHTML =
                        `<strong>Rp ${new Intl.NumberFormat('id-ID').format(investment)}</strong>`;
                    document.getElementById('penalty-month').textContent =
                        `Rp ${new Intl.NumberFormat('id-ID').format(penaltyPerMonth)}`;
                    document.getElementById('penalty-day').textContent =
                        `Rp ${new Intl.NumberFormat('id-ID').format(penaltyPerDay)}`;
                } else {
                    // Reset display
                    document.getElementById('investment-range').textContent = '-';
                    document.getElementById('bond-period').textContent = '-';
                    document.getElementById('duration-display').textContent = '-';
                    document.getElementById('investment-value').textContent = '-';
                    document.getElementById('penalty-month').textContent = '-';
                    document.getElementById('penalty-day').textContent = '-';
                }
            }

            // Event listeners
            investmentInput.addEventListener('input', updateDurationFromInvestment);
            startDateInput.addEventListener('change', updateEndDate);
            durationInput.addEventListener('input', updateEndDate);

            // Initialize calculations on page load
            if (investmentInput.value) {
                updateDurationFromInvestment();
            }

            // Auto-generate Employee Bond Number when letter number is selected
            updateEmployeeBondNumberDisplay();
        });

        // Employee Bond Number Auto-Generation Functions
        function updateEmployeeBondNumberDisplay() {
            // Listen for letter number selection changes
            $(document).on('change', '[name="letter_number_id"]', function() {
                const selectedOption = $(this).find('option:selected');
                const letterNumber = selectedOption.text().split(' - ')[0]; // Extract letter number part

                if (selectedOption.val() && letterNumber) {
                    // Generate Employee Bond Number with selected letter number
                    const currentMonth = '{{ now()->format('M') }}';
                    const currentYear = '{{ now()->year }}';
                    const romanMonth = getRomanMonth(currentMonth);
                    const bondNumber = `${letterNumber}/ARKA-HCS/${romanMonth}/${currentYear}`;

                    $('#employee_bond_number').val(bondNumber);

                    // Visual feedback
                    $('#employee_bond_number').addClass('alert-success').removeClass('alert-warning');
                } else {
                    // Reset to placeholder if no letter number selected
                    const currentMonth = '{{ now()->format('M') }}';
                    const currentYear = '{{ now()->year }}';
                    const romanMonth = getRomanMonth(currentMonth);
                    const defaultBond = `[Letter Number]/ARKA-HCS/${romanMonth}/${currentYear}`;

                    $('#employee_bond_number').val(defaultBond);
                    $('#employee_bond_number').addClass('alert-warning').removeClass('alert-success');
                }
            });
        }

        // Function to convert month to Roman numeral
        function getRomanMonth(month) {
            const monthMap = {
                'Jan': 'I',
                'Feb': 'II',
                'Mar': 'III',
                'Apr': 'IV',
                'May': 'V',
                'Jun': 'VI',
                'Jul': 'VII',
                'Aug': 'VIII',
                'Sep': 'IX',
                'Oct': 'X',
                'Nov': 'XI',
                'Dec': 'XII'
            };
            return monthMap[month] || 'I';
        }
    </script>
@endsection
