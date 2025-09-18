@extends('layouts.main')

@section('content')
    <div class="content-wrapper-custom">
        <div class="bond-header">
            <div class="bond-header-content">
                <div class="bond-employee">{{ $employeeBond->employee->fullname }}</div>
                <h1 class="bond-title">{{ $employeeBond->bond_name }}</h1>
                <div class="bond-number">
                    <i class="fas fa-hashtag"></i> {{ $employeeBond->employee_bond_number ?? 'No Bond Number' }}
                </div>
                @php
                    $statusMap = [
                        'active' => [
                            'label' => 'Active',
                            'class' => 'badge badge-success',
                            'icon' => 'fa-check-circle',
                        ],
                        'completed' => [
                            'label' => 'Completed',
                            'class' => 'badge badge-info',
                            'icon' => 'fa-check-double',
                        ],
                        'violated' => [
                            'label' => 'Violated',
                            'class' => 'badge badge-danger',
                            'icon' => 'fa-exclamation-triangle',
                        ],
                        'cancelled' => ['label' => 'Cancelled', 'class' => 'badge badge-secondary', 'icon' => 'fa-ban'],
                    ];
                    $status = $employeeBond->status;
                    $pill = $statusMap[$status] ?? [
                        'label' => 'Unknown',
                        'class' => 'badge badge-secondary',
                        'icon' => 'fa-question-circle',
                    ];
                @endphp
                <div class="bond-status-pill">
                    <span class="{{ $pill['class'] }}">
                        <i class="fas {{ $pill['icon'] }}"></i> {{ $pill['label'] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bond-content">
            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Main Bond Info -->
                    <div class="bond-card bond-info-card">
                        <div class="card-head">
                            <h2><i class="fas fa-info-circle"></i> Bond Details</h2>
                            <div class="card-tools">
                                <a href="{{ route('employee-bonds.edit', $employeeBond->id) }}"
                                    class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #007bff;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Employee</div>
                                        <div class="info-value">{{ $employeeBond->employee->fullname }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #6c757d;">
                                        <i class="fas fa-id-card"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">NIK</div>
                                        <div class="info-value">
                                            {{ $employeeBond->employee->administrations->first()->nik ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #28a745;">
                                        <i class="fas fa-calendar-plus"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Start Date</div>
                                        <div class="info-value">{{ $employeeBond->start_date->format('d F Y') }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #dc3545;">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">End Date</div>
                                        <div class="info-value">{{ $employeeBond->end_date->format('d F Y') }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #ffc107;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Duration</div>
                                        <div class="info-value">{{ $employeeBond->total_bond_duration_months }} months
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #17a2b8;">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Investment Value</div>
                                        <div class="info-value">Rp
                                            {{ number_format($employeeBond->total_investment_value, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>

                            @if ($employeeBond->description)
                                <div class="description-section">
                                    <h5><i class="fas fa-align-left"></i> Description</h5>
                                    <p>{{ $employeeBond->description }}</p>
                                </div>
                            @endif

                            @if ($employeeBond->document_path)
                                <div class="document-section">
                                    <h5><i class="fas fa-file-pdf"></i> Document</h5>
                                    <div class="document-actions">
                                        <a href="{{ route('employee-bonds.download', $employeeBond->id) }}"
                                            class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-download"></i> Download Document
                                        </a>
                                        <button class="btn btn-sm btn-danger"
                                            onclick="deleteDocument({{ $employeeBond->id }})">
                                            <i class="fas fa-trash"></i> Delete Document
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Bond Violations -->
                    <div class="bond-card violations-card">
                        <div class="card-head">
                            <h2><i class="fas fa-exclamation-triangle"></i> Bond Violations</h2>
                            <div class="card-tools">
                                <a href="{{ route('bond-violations.create') }}?employee_bond_id={{ $employeeBond->id }}"
                                    class="btn btn-sm btn-warning">
                                    <i class="fas fa-plus"></i> Add Violation
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @if ($employeeBond->violations->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th class="align-middle">Violation Date</th>
                                                <th class="align-middle">Reason</th>
                                                <th class="align-middle">Days Worked</th>
                                                <th class="align-middle">Days Remaining</th>
                                                <th class="align-middle">Penalty Amount</th>
                                                <th class="align-middle">Paid Amount</th>
                                                <th class="align-middle">Status</th>
                                                <th class="align-middle text-center" width="13%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($employeeBond->violations as $violation)
                                                <tr>
                                                    <td>{{ $violation->violation_date->format('d/m/Y') }}</td>
                                                    <td>{{ $violation->reason ?? '-' }}</td>
                                                    <td>{{ $violation->days_worked }} days</td>
                                                    <td>{{ $violation->days_remaining }} days</td>
                                                    <td>{{ $violation->formatted_calculated_penalty }}</td>
                                                    <td>{{ $violation->formatted_paid_penalty }}</td>
                                                    <td>
                                                        @if ($violation->payment_status == 'paid')
                                                            <span class="badge badge-success">Paid</span>
                                                        @elseif($violation->payment_status == 'partial')
                                                            <span class="badge badge-warning">Partial</span>
                                                        @else
                                                            <span class="badge badge-danger">Pending</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('bond-violations.show', $violation->id) }}"
                                                            class="btn btn-sm btn-info" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('bond-violations.edit', $violation->id) }}"
                                                            class="btn btn-sm btn-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                                    <p>No violations recorded for this bond.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Bond Statistics -->
                    <div class="bond-card statistics-card">
                        <div class="card-head">
                            <h2><i class="fas fa-chart-bar"></i> Bond Statistics</h2>
                        </div>
                        <div class="card-body">
                            <div class="statistics-grid">
                                <div class="stat-item">
                                    <div class="stat-icon" style="background-color: #007bff;">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Remaining Days</div>
                                        <div class="stat-value">
                                            @if ($employeeBond->status == 'active')
                                                <span
                                                    class="text-{{ $employeeBond->remaining_days < 30 ? 'danger' : 'success' }}">
                                                    {{ $employeeBond->remaining_days }} days
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon" style="background-color: #28a745;">
                                        <i class="fas fa-calendar-week"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Penalty per Month</div>
                                        <div class="stat-value">Rp
                                            {{ number_format($employeeBond->penalty_per_month, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon" style="background-color: #ffc107;">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Penalty per Day</div>
                                        <div class="stat-value">Rp
                                            {{ number_format($employeeBond->penalty_per_day, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon" style="background-color: #dc3545;">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Total Violations</div>
                                        <div class="stat-value">{{ $employeeBond->violations->count() }}</div>
                                    </div>
                                </div>
                                @if ($employeeBond->violations->count() > 0)
                                    <div class="stat-item">
                                        <div class="stat-icon" style="background-color: #6c757d;">
                                            <i class="fas fa-dollar-sign"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-label">Total Penalty</div>
                                            <div class="stat-value">Rp
                                                {{ number_format($employeeBond->violations->sum('calculated_penalty_amount'), 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-icon" style="background-color: #17a2b8;">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-label">Total Paid</div>
                                            <div class="stat-value">Rp
                                                {{ number_format($employeeBond->violations->sum('penalty_paid_amount'), 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-icon" style="background-color: #fd7e14;">
                                            <i class="fas fa-balance-scale"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-label">Remaining Penalty</div>
                                            <div class="stat-value">Rp
                                                {{ number_format($employeeBond->violations->sum('calculated_penalty_amount') - $employeeBond->violations->sum('penalty_paid_amount'), 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bond-card actions-card">
                        <div class="card-head">
                            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                        </div>
                        <div class="card-body">
                            <div class="action-buttons">
                                @if ($employeeBond->status == 'active')
                                    <button class="btn btn-success btn-block mb-2"
                                        onclick="markAsCompleted({{ $employeeBond->id }})">
                                        <i class="fas fa-check"></i> Mark as Completed
                                    </button>
                                @endif
                                <a href="{{ route('employee-bonds.index') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        /* Custom Styles for Employee Bond Detail */
        .content-wrapper-custom {
            background-color: #f8fafc;
            min-height: 100vh;
            padding-bottom: 40px;
        }

        /* Header */
        .bond-header {
            position: relative;
            height: 120px;
            color: white;
            padding: 20px 30px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .bond-header-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .bond-employee {
            font-size: 13px;
            margin-bottom: 4px;
            opacity: 0.9;
            letter-spacing: 1px;
        }

        .bond-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .bond-number {
            font-size: 14px;
            opacity: 0.9;
        }

        .bond-status-pill {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .bond-status-pill .badge {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Content Styles */
        .bond-content {
            padding: 0 20px;
        }

        /* Cards */
        .bond-card {
            background: white;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-head {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-head h2 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .card-body {
            padding: 20px;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid #007bff;
        }

        .info-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .info-value {
            font-size: 1rem;
            color: #2c3e50;
            font-weight: 600;
        }

        /* Statistics Grid */
        .statistics-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid #007bff;
        }

        .stat-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .stat-content {
            flex: 1;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 2px;
            font-weight: 500;
        }

        .stat-value {
            font-size: 0.9rem;
            color: #2c3e50;
            font-weight: 600;
        }

        /* Description and Document Sections */
        .description-section,
        .document-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .description-section h5,
        .document-section h5 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .document-actions {
            margin-top: 10px;
        }

        .document-actions .btn {
            margin-right: 10px;
        }

        /* Action Buttons */
        .action-buttons .btn {
            margin-bottom: 10px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .bond-header {
                height: auto;
                padding: 15px;
            }

            .bond-header-content {
                padding-right: 80px;
            }

            .bond-title {
                font-size: 20px;
            }

            .bond-status-pill {
                position: absolute;
                top: 15px;
                right: 15px;
                margin-top: 0;
                align-self: flex-start;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .card-body {
                padding: 15px;
            }

            .info-item {
                padding: 10px;
            }
        }

        @media (min-width: 993px) {
            .bond-content .row {
                display: flex;
                flex-wrap: wrap;
            }

            .bond-content .col-lg-8 {
                flex: 0 0 66.666667%;
                max-width: 66.666667%;
            }

            .bond-content .col-lg-4 {
                flex: 0 0 33.333333%;
                max-width: 33.333333%;
            }
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}" defer></script>
    <script>
        function markAsCompleted(bondId) {
            if (confirm('Are you sure you want to mark this bond as completed?')) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('employee-bonds.complete', ':id') }}'.replace(':id', bondId);

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'PATCH';

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteDocument(bondId) {
            if (confirm('Are you sure you want to delete this document?')) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('employee-bonds.delete-document', ':id') }}'.replace(':id', bondId);

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
@endsection
