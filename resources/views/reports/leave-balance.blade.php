@extends('layouts.main')

@section('title', 'Leave Balance Report')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Leave Balance Report</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">Leave Balance</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Filter Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Report Filters</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('reports.leave-balance') }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Employee</label>
                                        <select name="employee_id" class="form-control">
                                            <option value="">All Employees</option>
                                            @foreach ($employees ?? [] as $employee)
                                                <option value="{{ $employee->id }}"
                                                    {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                                    {{ $employee->fullname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Leave Type</label>
                                        <select name="leave_type_id" class="form-control">
                                            <option value="">All Leave Types</option>
                                            @foreach ($leaveTypes ?? [] as $leaveType)
                                                <option value="{{ $leaveType->id }}"
                                                    {{ request('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                                                    {{ $leaveType->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search"></i> Generate Report
                                            </button>
                                            <a href="{{ route('reports.leave-balance') }}" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Clear
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Statistics -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $balances->count() }}</h3>
                                <p>Total Entitlements</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $balances->sum('remaining_days') }}</h3>
                                <p>Total Remaining Days</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $balances->where('remaining_days', '>', 0)->count() }}</h3>
                                <p>Active Entitlements</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ $balances->where('remaining_days', 0)->count() }}</h3>
                                <p>Exhausted Entitlements</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Export Options</h3>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="{{ route('reports.export', ['type' => 'balance']) }}" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </a>
                            <a href="{{ route('reports.export', ['type' => 'balance', 'format' => 'pdf']) }}"
                                class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> Export to PDF
                            </a>
                            <button type="button" class="btn btn-info" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Report
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Leave Balance Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Leave Balance Report</h3>
                        <div class="card-tools">
                            <span class="badge badge-info">{{ $balances->count() }} records</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Employee ID</th>
                                        <th>Position</th>
                                        <th>Department</th>
                                        <th>Leave Type</th>
                                        <th>Period</th>
                                        <th>Entitled</th>
                                        <th>Withdrawable</th>
                                        <th>Deposit</th>
                                        <th>Carried Over</th>
                                        <th>Taken</th>
                                        <th>Remaining</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($balances as $balance)
                                        <tr>
                                            <td>{{ $balance->employee->fullname ?? 'N/A' }}</td>
                                            <td>{{ $balance->employee->employee_id ?? 'N/A' }}</td>
                                            <td>{{ $balance->employee->position->position_name ?? 'N/A' }}</td>
                                            <td>{{ $balance->employee->department->department_name ?? 'N/A' }}</td>
                                            <td>
                                                <span
                                                    class="badge badge-info">{{ $balance->leaveType->name ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                {{ $balance->period_start->format('M Y') }} -
                                                {{ $balance->period_end->format('M Y') }}
                                            </td>
                                            <td>{{ $balance->entitled_days }}</td>
                                            <td>{{ $balance->withdrawable_days }}</td>
                                            <td>{{ $balance->deposit_days }}</td>
                                            <td>{{ $balance->carried_over }}</td>
                                            <td>{{ $balance->taken_days }}</td>
                                            <td>
                                                <span
                                                    class="badge badge-{{ $balance->remaining_days > 0 ? 'success' : 'secondary' }}">
                                                    {{ $balance->remaining_days }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($balance->remaining_days > 20)
                                                    <span class="badge badge-success">High</span>
                                                @elseif($balance->remaining_days > 10)
                                                    <span class="badge badge-warning">Medium</span>
                                                @elseif($balance->remaining_days > 0)
                                                    <span class="badge badge-danger">Low</span>
                                                @else
                                                    <span class="badge badge-secondary">Exhausted</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="13" class="text-center">No leave balances found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Balance Distribution Chart -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Balance Distribution</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="balanceChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Leave Type Distribution</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="leaveTypeChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Balance Alert -->
                @if ($balances->where('remaining_days', '>', 0)->where('remaining_days', '<=', 5)->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title text-warning">
                                <i class="fas fa-exclamation-triangle"></i> Low Balance Alert
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <h5>Employees with low leave balance (≤ 5 days):</h5>
                                <ul class="list-unstyled">
                                    @foreach ($balances->where('remaining_days', '>', 0)->where('remaining_days', '<=', 5) as $balance)
<li>
                                        <strong>{{ $balance->employee->fullname ?? 'N/A' }}</strong> -
                                        {{ $balance->leaveType->name ?? 'N/A' }}:
                                        <span class="badge badge-warning">{{ $balance->remaining_days }} days</span>
                                    </li>
@endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Auto-submit form on filter change
            $('select[name="employee_id"], select[name="leave_type_id"]').change(function() {
                $(this).closest('form').submit();
            });

            // Balance Distribution Chart
            const balanceData = @json(
                $balances->map(function ($balance) {
                    return $balance->remaining_days;
                }));

            const balanceCtx = document.getElementById('balanceChart').getContext('2d');
            new Chart(balanceCtx, {
                type: 'doughnut',
                data: {
                    labels: ['High Balance (>20 days)', 'Medium Balance (10-20 days)',
                        'Low Balance (1-9 days)', 'Exhausted (0 days)'
                    ],
                    datasets: [{
                        data: [
                            balanceData.filter(d => d > 20).length,
                            balanceData.filter(d => d >= 10 && d <= 20).length,
                            balanceData.filter(d => d >= 1 && d < 10).length,
                            balanceData.filter(d => d === 0).length
                        ],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.5)',
                            'rgba(255, 206, 86, 0.5)',
                            'rgba(255, 99, 132, 0.5)',
                            'rgba(201, 203, 207, 0.5)'
                        ],
                        borderColor: [
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(201, 203, 207, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Leave Type Distribution Chart
            const leaveTypeData = @json(
                $balances->groupBy('leaveType.name')->map(function ($group) {
                    return $group->sum('remaining_days');
                }));

            const leaveTypeCtx = document.getElementById('leaveTypeChart').getContext('2d');
            new Chart(leaveTypeCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(leaveTypeData),
                    datasets: [{
                        label: 'Remaining Days',
                        data: Object.values(leaveTypeData),
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
@endpush)
