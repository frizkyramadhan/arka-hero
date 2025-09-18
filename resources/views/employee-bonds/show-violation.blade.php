@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Bond Violation Details</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('employee-bonds.index') }}">Employee Bonds</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('bond-violations.index') }}">Violations</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Violation Information -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Violation Information</h3>
                            <div class="card-tools">
                                <a href="{{ route('bond-violations.edit', $bondViolation->id) }}"
                                    class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Employee:</strong></td>
                                            <td>{{ $bondViolation->employeeBond->employee->fullname }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Identity Card:</strong></td>
                                            <td>{{ $bondViolation->employeeBond->employee->identity_card }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Bond Name:</strong></td>
                                            <td>{{ $bondViolation->employeeBond->bond_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Violation Date:</strong></td>
                                            <td>{{ $bondViolation->violation_date->format('d/m/Y') }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
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
                                        <tr>
                                            <td><strong>Payment Due Date:</strong></td>
                                            <td>{{ $bondViolation->payment_due_date ? $bondViolation->payment_due_date->format('d/m/Y') : '-' }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            @if ($bondViolation->reason)
                                <div class="row">
                                    <div class="col-12">
                                        <h5>Reason</h5>
                                        <p>{{ $bondViolation->reason }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Penalty Information -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Penalty Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-danger"><i
                                                class="fas fa-exclamation-triangle"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Calculated Penalty</span>
                                            <span
                                                class="info-box-number">{{ $bondViolation->formatted_calculated_penalty }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Paid Amount</span>
                                            <span
                                                class="info-box-number">{{ $bondViolation->formatted_paid_penalty }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Remaining</span>
                                            <span
                                                class="info-box-number">{{ $bondViolation->formatted_remaining_penalty }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bond Information -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Related Bond Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Bond Period:</strong></td>
                                        <td>{{ $bondViolation->employeeBond->start_date->format('d/m/Y') }} -
                                            {{ $bondViolation->employeeBond->end_date->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Duration:</strong></td>
                                        <td>{{ $bondViolation->employeeBond->total_bond_duration_months }} months</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Investment Value:</strong></td>
                                        <td>Rp
                                            {{ number_format($bondViolation->employeeBond->total_investment_value, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Penalty per Month:</strong></td>
                                        <td>Rp
                                            {{ number_format($bondViolation->employeeBond->penalty_per_month, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Penalty per Day:</strong></td>
                                        <td>Rp
                                            {{ number_format($bondViolation->employeeBond->penalty_per_day, 0, ',', '.') }}
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
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('employee-bonds.show', $bondViolation->employeeBond->id) }}"
                                    class="btn btn-info btn-sm">
                                    <i class="fas fa-handshake"></i> View Bond Details
                                </a>

                                <a href="{{ route('bond-violations.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to Violations
                                </a>
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
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}" defer></script>
@endsection
