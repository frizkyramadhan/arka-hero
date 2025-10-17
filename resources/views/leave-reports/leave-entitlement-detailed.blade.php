@extends('layouts.main')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <a href="{{ route('leave.reports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter"></i> Filter Options
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('leave.reports.entitlement-detailed') }}" class="row"
                        id="filterForm">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="year">Year</label>
                                <select name="year" id="year" class="form-control select2">
                                    @for ($i = now()->year - 2; $i <= now()->year + 1; $i++)
                                        <option value="{{ $i }}"
                                            {{ request('year', now()->year) == $i ? 'selected' : '' }}>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="employee_id">Employee</label>
                                <select name="employee_id" id="employee_id" class="form-control select2">
                                    <option value="">All Employees</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}"
                                            {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->administrations->first()->nik ?? 'N/A' }} -
                                            {{ $employee->fullname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="leave_type_id">Leave Type</label>
                                <select name="leave_type_id" id="leave_type_id" class="form-control select2">
                                    <option value="">All Leave Types</option>
                                    @foreach ($leaveTypes as $leaveType)
                                        <option value="{{ $leaveType->id }}"
                                            {{ request('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                                            {{ $leaveType->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                    <div class="row mt-2">
                        <div class="col-12">
                            <button type="submit" form="filterForm" class="btn btn-primary mr-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('leave.reports.entitlement-detailed', ['show_all' => 1]) }}"
                                class="btn btn-info mr-2">
                                <i class="fas fa-list"></i> Show All
                            </a>
                            <a href="{{ route('leave.reports.entitlement-detailed') }}" class="btn btn-warning mr-2">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Period</th>
                                    <th class="text-center">Entitled</th>
                                    <th class="text-center">Deposit</th>
                                    <th class="text-center">Withdrawable</th>
                                    <th class="text-center">Taken</th>
                                    <th class="text-center">Cancelled</th>
                                    <th class="text-center">Effective</th>
                                    <th class="text-center">Remaining</th>
                                    <th class="text-center">Utilization</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entitlements as $entitlement)
                                    <tr>
                                        <td>
                                            <strong>{{ $entitlement['employee_name'] }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $entitlement['leave_type'] }}</span>
                                        </td>
                                        <td>
                                            {{ $entitlement['period_start'] }}
                                            <br><small class="text-muted">to {{ $entitlement['period_end'] }}</small>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $entitlement['total_entitlement'] }}</strong>
                                        </td>
                                        <td class="text-center">
                                            @if (isset($entitlement['deposit_days']) && $entitlement['deposit_days'] > 0)
                                                <span class="badge badge-warning">{{ $entitlement['deposit_days'] }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $entitlement['total_entitlement'] }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-primary">{{ $entitlement['taken_days'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if ($entitlement['total_cancelled_days'] > 0)
                                                <span
                                                    class="text-warning">{{ $entitlement['total_cancelled_days'] }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $entitlement['total_effective_days'] }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $entitlement['remaining_days'] }}</strong>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $utilization =
                                                    $entitlement['calculation_summary']['utilization_percentage'];
                                            @endphp
                                            @if ($utilization >= 80)
                                                <span class="badge badge-success">{{ $utilization }}%</span>
                                            @elseif($utilization >= 50)
                                                <span class="badge badge-warning">{{ $utilization }}%</span>
                                            @else
                                                <span class="badge badge-danger">{{ $utilization }}%</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center">No entitlements found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($entitlements->hasPages())
                        <div class="card-footer">
                            {{ $entitlements->links() }}
                        </div>
                    @endif
                </div>
            </div>

            @if (count($entitlements) > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle"></i> Calculation Legend
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><strong>Calculation Breakdown:</strong></h6>
                                <ul class="list-unstyled">
                                    <li><strong>Entitled:</strong> Total days allocated for the period</li>
                                    <li><strong>Deposit:</strong> Days held in deposit (LSL first period: 10 days)</li>
                                    <li><strong>Withdrawable:</strong> Days available for immediate use</li>
                                    <li><strong>Taken:</strong> Total days from approved leave requests</li>
                                    <li><strong>Cancelled:</strong> Days cancelled from approved requests</li>
                                    <li><strong>Effective:</strong> Taken days minus cancelled days</li>
                                    <li><strong>Remaining:</strong> Withdrawable days minus effective days</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><strong>Utilization Rate:</strong></h6>
                                <ul class="list-unstyled">
                                    <li><span class="badge badge-success">Green ≥80%</span> High utilization</li>
                                    <li><span class="badge badge-warning">Yellow 50-79%</span> Moderate utilization</li>
                                    <li><span class="badge badge-danger">Red <50% </span> Low utilization</li>
                                </ul>
                                <h6><strong>Special Notes:</strong></h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-info-circle text-info"></i> LSL first period has 40 days
                                        withdrawable + 10 days deposit</li>
                                    <li><i class="fas fa-info-circle text-info"></i> Cancelled days are returned to
                                        entitlement pool</li>
                                    <li><i class="fas fa-info-circle text-info"></i> Effective days represent actual leave
                                        taken</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

@section('styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
@endsection

@section('scripts')
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        });
    </script>
@endsection
