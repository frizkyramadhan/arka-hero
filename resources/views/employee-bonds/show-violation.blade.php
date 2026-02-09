@extends('layouts.main')

@section('content')
    <div class="content-wrapper-custom">
        <div class="bond-header">
            <div class="bond-header-content">
                <div class="bond-employee">{{ $bondViolation->employeeBond->employee->fullname }}</div>
                <h1 class="bond-title">Bond Violation - {{ $bondViolation->employeeBond->bond_name }}</h1>
                <div class="bond-number">
                    <i class="fas fa-exclamation-triangle"></i> Violation #{{ $bondViolation->id }}
                </div>
                @php
                    $statusMap = [
                        'paid' => [
                            'label' => 'Paid',
                            'class' => 'badge badge-success',
                            'icon' => 'fa-check-circle',
                        ],
                        'partial' => [
                            'label' => 'Partial',
                            'class' => 'badge badge-warning',
                            'icon' => 'fa-clock',
                        ],
                        'pending' => [
                            'label' => 'Pending',
                            'class' => 'badge badge-danger',
                            'icon' => 'fa-exclamation-triangle',
                        ],
                    ];
                    $status = $bondViolation->payment_status;
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
                    <!-- Violation Details -->
                    <div class="bond-card">
                        <div class="card-head">
                            <h2><i class="fas fa-exclamation-triangle"></i> Violation Details</h2>
                            <div class="card-tools">
                                <a href="{{ route('bond-violations.edit', $bondViolation->id) }}"
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
                                        <div class="info-value">{{ $bondViolation->employeeBond->employee->fullname }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #6c757d;">
                                        <i class="fas fa-id-card"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">NIK</div>
                                        <div class="info-value">
                                            {{ $bondViolation->employeeBond->employee->administrations->isNotEmpty() ? $bondViolation->employeeBond->employee->administrations->first()->nik : '-' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #28a745;">
                                        <i class="fas fa-handshake"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Bond Name</div>
                                        <div class="info-value">{{ $bondViolation->employeeBond->bond_name }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #dc3545;">
                                        <i class="fas fa-calendar-times"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Violation Date</div>
                                        <div class="info-value">{{ $bondViolation->violation_date->format('d F Y') }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #ffc107;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Days Worked</div>
                                        <div class="info-value">{{ $bondViolation->days_worked }} days</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #17a2b8;">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Days Remaining</div>
                                        <div class="info-value">{{ $bondViolation->days_remaining }} days</div>
                                    </div>
                                </div>
                            </div>

                            @if ($bondViolation->reason)
                                <div class="description-section">
                                    <h5><i class="fas fa-align-left"></i> Reason</h5>
                                    <p>{{ $bondViolation->reason }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Penalty Statistics -->
                    <div class="bond-card">
                        <div class="card-head">
                            <h2><i class="fas fa-calculator"></i> Penalty Statistics</h2>
                        </div>
                        <div class="card-body">
                            <div class="statistics-grid">
                                <div class="stat-item">
                                    <div class="stat-icon" style="background-color: #dc3545;">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Calculated Penalty</div>
                                        <div class="stat-value">{{ $bondViolation->formatted_calculated_penalty }}</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon" style="background-color: #28a745;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Paid Amount</div>
                                        <div class="stat-value">{{ $bondViolation->formatted_paid_penalty }}</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon" style="background-color: #ffc107;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Remaining</div>
                                        <div class="stat-value">{{ $bondViolation->formatted_remaining_penalty }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Bond Information -->
                    <div class="bond-card">
                        <div class="card-head">
                            <h3><i class="fas fa-handshake"></i> Bond Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th colspan="2" class="text-center">
                                                <i class="fas fa-info-circle"></i> Related Bond Details
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Bond Period:</strong></td>
                                            <td>{{ $bondViolation->employeeBond->start_date->format('d/m/Y') }} -
                                                {{ $bondViolation->employeeBond->end_date->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Duration:</strong></td>
                                            <td><span
                                                    class="badge badge-primary">{{ $bondViolation->employeeBond->total_bond_duration_months }}
                                                    months</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Investment Value:</strong></td>
                                            <td><strong>Rp
                                                    {{ number_format($bondViolation->employeeBond->total_investment_value, 0, ',', '.') }}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Penalty on Violation (Fixed):</strong></td>
                                            <td><strong>Rp
                                                    {{ number_format($bondViolation->employeeBond->total_investment_value, 0, ',', '.') }}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="small text-muted">
                                                <i class="fas fa-info-circle"></i> Sesuai kebijakan: penalty = jumlah tetap (biaya pelatihan).
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Bond Status:</strong></td>
                                            <td>
                                                @if ($bondViolation->employeeBond->status == 'active')
                                                    <span class="badge badge-success">Active</span>
                                                @elseif($bondViolation->employeeBond->status == 'completed')
                                                    <span class="badge badge-info">Completed</span>
                                                @elseif($bondViolation->employeeBond->status == 'violated')
                                                    <span class="badge badge-danger">Violated</span>
                                                @else
                                                    <span class="badge badge-secondary">Cancelled</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bond-card">
                        <div class="card-head">
                            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="action-buttons">
                                <a href="{{ route('employee-bonds.show', $bondViolation->employeeBond->id) }}"
                                    class="btn btn-info btn-block mb-2">
                                    <i class="fas fa-handshake"></i> View Bond Details
                                </a>
                                <a href="{{ route('bond-violations.index') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-arrow-left"></i> Back to Violations
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
        /* Custom Styles for Bond Violation Detail - matching Employee Bond */
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
@endsection
