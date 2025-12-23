@extends('layouts.main')

@section('title', $title)

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Roster & Periodic Leave</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row mb-3">
                <!-- Total Rosters -->
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box bg-gradient-primary" style="padding: 8px;">
                        <span class="info-box-icon" style="width: 50px; height: 50px; line-height: 50px;">
                            <i class="fas fa-calendar-week"></i>
                        </span>
                        <div class="info-box-content" style="padding-left: 8px;">
                            <span class="info-box-text" style="font-size: 0.9rem;">Total Rosters</span>
                            <span class="info-box-number"
                                style="font-size: 1.4rem;">{{ number_format($totalRosters) }}</span>
                            <div class="progress" style="height: 3px; margin: 4px 0;">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description" style="font-size: 0.75rem;">
                                All roster employees
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Active Cycles -->
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box bg-gradient-success" style="padding: 8px;">
                        <span class="info-box-icon" style="width: 50px; height: 50px; line-height: 50px;">
                            <i class="fas fa-play-circle"></i>
                        </span>
                        <div class="info-box-content" style="padding-left: 8px;">
                            <span class="info-box-text" style="font-size: 0.9rem;">Active Cycles</span>
                            <span class="info-box-number"
                                style="font-size: 1.4rem;">{{ number_format($activeCycles) }}</span>
                            <div class="progress" style="height: 3px; margin: 4px 0;">
                                <div class="progress-bar"
                                    style="width: {{ $totalCycles > 0 ? ($activeCycles / $totalCycles) * 100 : 0 }}%"></div>
                            </div>
                            <span class="progress-description" style="font-size: 0.75rem;">
                                {{ $totalCycles > 0 ? round(($activeCycles / $totalCycles) * 100, 1) : 0 }}% of total cycles
                            </span>
                        </div>
                    </div>
                </div>

                <!-- On Leave -->
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box bg-gradient-warning" style="padding: 8px;">
                        <span class="info-box-icon" style="width: 50px; height: 50px; line-height: 50px;">
                            <i class="fas fa-umbrella-beach"></i>
                        </span>
                        <div class="info-box-content" style="padding-left: 8px;">
                            <span class="info-box-text" style="font-size: 0.9rem;">On Leave</span>
                            <span class="info-box-number"
                                style="font-size: 1.4rem;">{{ number_format($onLeaveCycles) }}</span>
                            <div class="progress" style="height: 3px; margin: 4px 0;">
                                <div class="progress-bar"
                                    style="width: {{ $totalCycles > 0 ? ($onLeaveCycles / $totalCycles) * 100 : 0 }}%">
                                </div>
                            </div>
                            <span class="progress-description" style="font-size: 0.75rem;">
                                Currently on leave
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Periodic Requests This Month -->
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box bg-gradient-info" style="padding: 8px;">
                        <span class="info-box-icon" style="width: 50px; height: 50px; line-height: 50px;">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                        <div class="info-box-content" style="padding-left: 8px;">
                            <span class="info-box-text" style="font-size: 0.9rem;">Periodic Requests</span>
                            <span class="info-box-number"
                                style="font-size: 1.4rem;">{{ number_format($thisMonthPeriodicRequests) }}</span>
                            <div class="progress" style="height: 3px; margin: 4px 0;">
                                <div class="progress-bar"
                                    style="width: {{ $totalPeriodicRequests > 0 ? ($thisMonthPeriodicRequests / $totalPeriodicRequests) * 100 : 0 }}%">
                                </div>
                            </div>
                            <span class="progress-description" style="font-size: 0.75rem;">
                                This month
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Content -->
            <div class="row">
                <!-- Balancing Roster Cycle -->
                <div class="col-lg-6 mb-4">
                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-balance-scale mr-2"></i>Balancing Roster Cycle
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm"
                                    id="employeesNeedingBalancingTable" width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="py-1 px-2">Employee</th>
                                            <th class="py-1 px-2">NIK</th>
                                            <th class="py-1 px-2">Project</th>
                                            <th class="py-1 px-2">Work Days Diff</th>
                                            <th class="py-1 px-2" style="text-align: center">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Periodic Leave Requests -->
                <div class="col-lg-6 mb-4">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-check mr-2"></i>Recent Periodic Leave Requests
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm"
                                    id="recentPeriodicRequestsTable" width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="py-1 px-2">Batch ID</th>
                                            <th class="py-1 px-2">Notes</th>
                                            <th class="py-1 px-2">Total</th>
                                            <th class="py-1 px-2">Status</th>
                                            <th class="py-1 px-2" style="text-align: center">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Statistics -->
            <div class="row">
                <div class="col-lg-12 mb-4">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-building mr-2"></i>Project Statistics
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm" id="projectStatisticsTable"
                                    width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="py-1 px-2">Project Code</th>
                                            <th class="py-1 px-2">Total Rosters</th>
                                            <th class="py-1 px-2">Active Employees</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rosterProjects as $project)
                                            <tr>
                                                <td>{{ $project->project_code }}</td>
                                                <td>{{ number_format($project->rosters_count) }}</td>
                                                <td>{{ number_format($project->administrations_count) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">No roster projects found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <style>
        /* Compact table styling */
        .table-sm th,
        .table-sm td {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.25;
        }

        .table-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            line-height: 1.2;
        }

        .table-sm .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.4rem;
        }

        /* Compact card styling */
        .card-body {
            padding: 0.75rem;
        }

        .card-header {
            padding: 0.5rem 0.75rem;
        }

        /* Compact info box styling */
        .info-box {
            margin-bottom: 0.5rem;
        }

        .info-box .info-box-content {
            padding: 0.5rem;
        }

        .info-box .info-box-text {
            font-size: 0.875rem;
            font-weight: 600;
        }

        .info-box .info-box-number {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .btn-xs {
            padding: 0.125rem 0.25rem;
            font-size: 0.65rem;
            line-height: 1.2;
            border-radius: 0.2rem;
        }

        .btn-xs i {
            font-size: 0.7rem;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Employees Needing Balancing Table
            $('#employeesNeedingBalancingTable').DataTable({
                data: @json($employeesNeedingBalancingData),
                columns: [{
                        data: 'employee_name'
                    },
                    {
                        data: 'employee_nik'
                    },
                    {
                        data: 'project'
                    },
                    {
                        data: 'work_days_difference',
                        render: function(data, type, row) {
                            const diff = parseFloat(data);
                            const badgeClass = diff > 0 ? 'badge-success' : 'badge-danger';
                            return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                        }
                    },
                    {
                        data: 'roster_id',
                        render: function(data, type, row) {
                            const url = '{{ route('rosters.show', ':id') }}'.replace(':id', data);
                            return '<a href="' + url +
                                '" class="btn btn-xs btn-primary"><i class="fas fa-eye"></i> View</a>';
                        },
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                pageLength: 5,
                lengthMenu: [
                    [5, 10, 25],
                    [5, 10, 25]
                ],
                order: [
                    [3, 'desc']
                ]
            });

            // Recent Periodic Requests Table
            $('#recentPeriodicRequestsTable').DataTable({
                data: @json($recentPeriodicRequestsData),
                columns: [{
                        data: 'batch_id'
                    },
                    {
                        data: 'notes'
                    },
                    {
                        data: 'total'
                    },
                    {
                        data: 'status_badge',
                        orderable: false
                    },
                    {
                        data: 'batch_id_url',
                        render: function(data, type, row) {
                            return '<a href="' + data +
                                '" class="btn btn-xs btn-primary"><i class="fas fa-eye"></i> View</a>';
                        },
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                pageLength: 5,
                lengthMenu: [
                    [5, 10, 25],
                    [5, 10, 25]
                ],
                order: [
                    [0, 'desc']
                ]
            });

            // Project Statistics Table
            $('#projectStatisticsTable').DataTable({
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50],
                    [10, 25, 50]
                ],
                order: [
                    [0, 'asc']
                ]
            });
        });
    </script>
@endpush
