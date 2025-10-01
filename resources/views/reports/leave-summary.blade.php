@extends('layouts.main')

@section('title', 'Leave Summary Report')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Leave Summary Report</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">Leave Summary</li>
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
                        <form method="GET" action="{{ route('reports.leave-summary') }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Year</label>
                                        <select name="year" class="form-control">
                                            @for ($year = now()->year - 2; $year <= now()->year + 1; $year++)
                                                <option value="{{ $year }}"
                                                    {{ request('year', now()->year) == $year ? 'selected' : '' }}>
                                                    {{ $year }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
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
                                <div class="col-md-3">
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
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search"></i> Generate Report
                                            </button>
                                            <a href="{{ route('reports.leave-summary') }}" class="btn btn-secondary">
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
                                <h3>{{ $summary->count() }}</h3>
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
                                <h3>{{ $summary->sum('total_entitled') }}</h3>
                                <p>Total Entitled Days</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $summary->sum('total_taken') }}</h3>
                                <p>Total Taken Days</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ $summary->sum('total_remaining') }}</h3>
                                <p>Total Remaining Days</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calendar-plus"></i>
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
                            <a href="{{ route('reports.export', ['type' => 'summary', 'year' => request('year', now()->year)]) }}"
                                class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </a>
                            <a href="{{ route('reports.export', ['type' => 'summary', 'year' => request('year', now()->year), 'format' => 'pdf']) }}"
                                class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> Export to PDF
                            </a>
                            <button type="button" class="btn btn-info" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Report
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Leave Summary Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Leave Summary Report</h3>
                        <div class="card-tools">
                            <span class="badge badge-info">{{ $summary->count() }} records</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>Period</th>
                                        <th>Entitled Days</th>
                                        <th>Taken Days</th>
                                        <th>Remaining Days</th>
                                        <th>Utilization %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($summary as $item)
                                        @php
                                            $utilization =
                                                $item->total_entitled > 0
                                                    ? round(($item->total_taken / $item->total_entitled) * 100, 2)
                                                    : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $item->employee->fullname ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge badge-info">{{ $item->leaveType->name ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($item->period_start)->format('M Y') }} -
                                                {{ \Carbon\Carbon::parse($item->period_end)->format('M Y') }}
                                            </td>
                                            <td>{{ $item->total_entitled }}</td>
                                            <td>{{ $item->total_taken }}</td>
                                            <td>
                                                <span
                                                    class="badge badge-{{ $item->total_remaining > 0 ? 'success' : 'secondary' }}">
                                                    {{ $item->total_remaining }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-{{ $utilization > 80 ? 'danger' : ($utilization > 60 ? 'warning' : 'success') }}"
                                                        style="width: {{ $utilization }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $utilization }}%</small>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No data available for the selected
                                                criteria.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Leave Utilization by Type</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="leaveTypeChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Leave Balance Distribution</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="balanceChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Auto-submit form on filter change
            $('select[name="year"], select[name="employee_id"], select[name="leave_type_id"]').change(function() {
                $(this).closest('form').submit();
            });

            // Leave Type Chart
            const leaveTypeData = @json(
                $summary->groupBy('leaveType.name')->map(function ($group) {
                    return [
                        'entitled' => $group->sum('total_entitled'),
                        'taken' => $group->sum('total_taken'),
                        'remaining' => $group->sum('total_remaining'),
                    ];
                }));

            const leaveTypeCtx = document.getElementById('leaveTypeChart').getContext('2d');
            new Chart(leaveTypeCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(leaveTypeData),
                    datasets: [{
                        label: 'Entitled',
                        data: Object.values(leaveTypeData).map(item => item.entitled),
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Taken',
                        data: Object.values(leaveTypeData).map(item => item.taken),
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Remaining',
                        data: Object.values(leaveTypeData).map(item => item.remaining),
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
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

            // Balance Distribution Chart
            const balanceData = @json(
                $summary->map(function ($item) {
                    return $item->total_remaining;
                }));

            const balanceCtx = document.getElementById('balanceChart').getContext('2d');
            new Chart(balanceCtx, {
                type: 'doughnut',
                data: {
                    labels: ['High Balance (>20 days)', 'Medium Balance (10-20 days)',
                        'Low Balance (1-9 days)', 'No Balance (0 days)'
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
        });
    </script>
@endpush
