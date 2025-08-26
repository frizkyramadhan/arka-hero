@extends('layouts.main')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $subtitle }}</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <a href="{{ route('recruitment.reports.index') }}" class="btn btn-secondary">
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
                    <form method="GET" action="{{ route('recruitment.reports.aging') }}" class="row" id="filterForm">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date1">Date From</label>
                                <input type="date" name="date1" id="date1" class="form-control"
                                    value="{{ $date1 }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date2">Date To</label>
                                <input type="date" name="date2" id="date2" class="form-control"
                                    value="{{ $date2 }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="department">Department</label>
                                <select name="department" id="department" class="form-control">
                                    <option value="">All Departments</option>
                                    @foreach (\App\Models\Department::orderBy('department_name')->get() as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ $department == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->department_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="project">Project</label>
                                <select name="project" id="project" class="form-control">
                                    <option value="">All Projects</option>
                                    @foreach (\App\Models\Project::where('project_status', 1)->orderBy('project_code')->get() as $proj)
                                        <option value="{{ $proj->id }}" {{ $project == $proj->id ? 'selected' : '' }}>
                                            {{ $proj->project_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Draft
                                    </option>
                                    <option value="submitted" {{ $status == 'submitted' ? 'selected' : '' }}>Submitted
                                    </option>
                                    <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Approved
                                    </option>
                                    <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rejected
                                    </option>
                                    <option value="closed" {{ $status == 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                        </div>
                    </form>
                    <div class="row mt-2">
                        <div class="col-12">
                            <button type="submit" form="filterForm" class="btn btn-primary mr-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('recruitment.reports.aging') }}" class="btn btn-warning mr-2">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                            <button type="button" id="exportExcelBtn" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- SLA Summary -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <!-- SLA Alert Warning -->
                            <div class="alert alert-warning d-none" id="slaAlert">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Warning:</strong> There are <span id="overdueAlertCount">0</span> requests that have
                                exceeded the SLA period. Please review and take action.
                            </div>

                            <!-- SLA Summary - Ultra Compact Design -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-gradient-primary text-white py-1">
                                    <h6 class="mb-0">
                                        <i class="fas fa-chart-pie mr-2"></i> SLA Summary
                                    </h6>
                                </div>
                                <div class="card-body py-2">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="sla-metric-compact active">
                                                <div class="metric-icon-compact">
                                                    <i class="fas fa-check-circle text-success"></i>
                                                </div>
                                                <div class="metric-number-compact" id="activeCount">0</div>
                                                <div class="metric-label-compact">Active</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="sla-metric-compact overdue">
                                                <div class="metric-icon-compact">
                                                    <i class="fas fa-exclamation-triangle text-danger"></i>
                                                </div>
                                                <div class="metric-number-compact" id="overdueCount">0</div>
                                                <div class="metric-label-compact">Overdue</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="sla-metric-compact pending">
                                                <div class="metric-icon-compact">
                                                    <i class="fas fa-clock text-warning"></i>
                                                </div>
                                                <div class="metric-number-compact" id="pendingCount">0</div>
                                                <div class="metric-label-compact">Pending</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="sla-metric-compact total">
                                                <div class="metric-icon-compact">
                                                    <i class="fas fa-list-alt text-secondary"></i>
                                                </div>
                                                <div class="metric-number-compact" id="totalCount">0</div>
                                                <div class="metric-label-compact">Total</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="agingTable" class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="align-middle">Request No</th>
                                    <th class="align-middle">Department</th>
                                    <th class="align-middle">Position</th>
                                    <th class="align-middle">Project</th>
                                    <th class="align-middle">Requested By</th>
                                    <th class="align-middle">Requested At</th>
                                    <th class="align-middle">Status</th>
                                    <th class="align-middle">Days Open</th>
                                    <th class="align-middle">Latest Approval</th>
                                    <th class="align-middle">Approved At</th>
                                    <th class="align-middle">Days to Approve</th>
                                    <th class="align-middle"
                                        title="SLA period is 6 months (180 days) from approval completion">SLA Target
                                        (Days) <i class="fas fa-info-circle text-info"></i></th>
                                    <th class="align-middle"
                                        title="Active = Within SLA period, Overdue = Exceeded SLA period, Pending Approval = Still in approval process">
                                        SLA Status <i class="fas fa-info-circle text-info"></i></th>
                                    <th class="align-middle"
                                        title="Positive number = days remaining, Negative number = days overdue">SLA Days
                                        Remaining <i class="fas fa-info-circle text-info"></i></th>
                                    <th class="align-middle">Remarks</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            <strong>SLA Information:</strong>
                            SLA period is 6 months (180 days) from approval completion.
                            <span class="badge badge-success">Active</span> = Within SLA period,
                            <span class="badge badge-danger">Overdue</span> = Exceeded SLA period,
                            <span class="badge badge-warning">Pending Approval</span> = Still in approval process.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">

    <style>
        .table-responsive {
            overflow-x: auto;
        }

        #agingTable th {
            white-space: nowrap;
            min-width: 120px;
        }

        #agingTable th:nth-child(1) {
            min-width: 140px;
        }

        /* Request No */
        #agingTable th:nth-child(2) {
            min-width: 130px;
        }

        /* Department */
        #agingTable th:nth-child(3) {
            min-width: 130px;
        }

        /* Position */
        #agingTable th:nth-child(4) {
            min-width: 130px;
        }

        /* Project */
        #agingTable th:nth-child(5) {
            min-width: 120px;
        }

        /* Requested By */
        #agingTable th:nth-child(6) {
            min-width: 130px;
        }

        /* Requested At */
        #agingTable th:nth-child(7) {
            min-width: 100px;
        }

        /* Status */
        #agingTable th:nth-child(8) {
            min-width: 100px;
        }

        /* Days Open */
        #agingTable th:nth-child(9) {
            min-width: 130px;
        }

        /* Latest Approval */
        #agingTable th:nth-child(10) {
            min-width: 130px;
        }

        /* Approved At */
        #agingTable th:nth-child(11) {
            min-width: 130px;
        }

        /* Days to Approve */
        #agingTable th:nth-child(12) {
            min-width: 120px;
        }

        /* SLA Target */
        #agingTable th:nth-child(13) {
            min-width: 120px;
        }

        /* SLA Status */
        #agingTable th:nth-child(14) {
            min-width: 150px;
        }

        /* SLA Days Remaining */
        #agingTable th:nth-child(15) {
            min-width: 120px;
        }

        /* Remarks */
        #agingTable th:nth-child(16) {
            min-width: 120px;
        }

        .badge {
            font-size: 0.8em;
            padding: 0.4em 0.6em;
        }

        .badge-lg {
            font-size: 1.2em;
            padding: 0.6em 0.8em;
        }

        .text-danger {
            font-weight: bold;
        }

        .text-success {
            font-weight: bold;
        }

        .alert-info {
            background-color: #f8f9fa;
            border-color: #bee5eb;
            color: #0c5460;
        }

        .alert-heading {
            color: #0c5460;
            font-weight: 600;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }

        .text-info {
            color: #17a2b8 !important;
        }

        /* SLA Summary Ultra Compact Design */
        .sla-metric-compact {
            padding: 8px 5px;
            border-radius: 6px;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
            margin: 0 2px;
        }

        .sla-metric-compact:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }

        .sla-metric-compact.active {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 1px solid #c3e6cb;
        }

        .sla-metric-compact.overdue {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border: 1px solid #f5c6cb;
        }

        .sla-metric-compact.pending {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 1px solid #ffeaa7;
        }

        .sla-metric-compact.total {
            background: linear-gradient(135deg, #e2e3e5 0%, #d6d8db 100%);
            border: 1px solid #d6d8db;
        }

        .metric-icon-compact {
            font-size: 1.1em;
            margin-bottom: 4px;
        }

        .metric-number-compact {
            font-size: 1.6em;
            font-weight: bold;
            margin-bottom: 2px;
            color: #2c3e50;
            line-height: 1;
        }

        .metric-label-compact {
            font-size: 0.9em;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0;
            line-height: 1;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
        }

        .card.shadow-sm {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }
    </style>
@endpush

@push('scripts')
    <!-- DataTables -->
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            var table = $('#agingTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '{{ route('recruitment.reports.aging.data') }}',
                    type: 'GET',
                    data: function(d) {
                        d.date1 = $('input[name="date1"]').val();
                        d.date2 = $('input[name="date2"]').val();
                        d.department = $('select[name="department"]').val();
                        d.project = $('select[name="project"]').val();
                        d.status = $('select[name="status"]').val();
                    }
                },
                columns: [{
                        data: 'request_no',
                        render: function(data, type, row) {
                            return '<a href="{{ route('recruitment.sessions.show', '') }}/' +
                                row.request_id +
                                '" target="_blank" title="View Request Details">' + data + '</a>';
                        }
                    },
                    {
                        data: 'department'
                    },
                    {
                        data: 'position'
                    },
                    {
                        data: 'project'
                    },
                    {
                        data: 'requested_by'
                    },
                    {
                        data: 'requested_at'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'days_open'
                    },
                    {
                        data: 'latest_approval'
                    },
                    {
                        data: 'approved_at'
                    },
                    {
                        data: 'days_to_approve'
                    },
                    {
                        data: 'sla_target'
                    },
                    {
                        data: 'sla_status',
                        render: function(data, type, row) {
                            // Debug logging
                            console.log('SLA Status Data:', data, 'Type:', typeof data, 'Row:',
                                row);

                            if (data === 'Active') {
                                return '<span class="badge badge-success">' + data + '</span>';
                            } else if (data === 'Overdue') {
                                return '<span class="badge badge-danger">' + data + '</span>';
                            } else if (data === 'Pending Approval') {
                                return '<span class="badge badge-warning">' + data + '</span>';
                            } else {
                                return data || '-';
                            }
                        }
                    },
                    {
                        data: 'sla_days_remaining',
                        render: function(data, type, row) {
                            // Debug logging
                            console.log('SLA Days Remaining Data:', data, 'Type:', typeof data,
                                'Row:', row);

                            if (data === null || data === undefined || data === '-') {
                                return '-';
                            } else if (data < 0) {
                                return '<span class="text-danger">' + data + ' days overdue</span>';
                            } else {
                                return '<span class="text-success">' + data +
                                    ' days remaining</span>';
                            }
                        }
                    },
                    {
                        data: 'remarks'
                    }
                ],
                responsive: true,
                pageLength: 10,
                order: [
                    [5, 'desc']
                ],
                scrollX: true,
                scrollCollapse: true,
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
                }
            });

            // Refresh table when form is submitted
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            // Export Excel with current filters
            $('#exportExcelBtn').on('click', function() {
                var params = $.param({
                    date1: $('input[name="date1"]').val(),
                    date2: $('input[name="date2"]').val(),
                    department: $('select[name="department"]').val(),
                    project: $('select[name="project"]').val(),
                    status: $('select[name="status"]').val()
                });
                window.location.href = '{{ route('recruitment.reports.aging.export') }}' + '?' + params;
            });

            // Update SLA Summary when table data changes
            table.on('xhr', function() {
                var json = table.ajax.json();
                console.log('Full AJAX Response:', json);
                if (json && json.data) {
                    console.log('Table Data:', json.data);
                    updateSLASummary(json.data);
                }
            });

            // Function to update SLA Summary
            function updateSLASummary(data) {
                var activeCount = 0;
                var overdueCount = 0;
                var pendingCount = 0;
                var totalCount = data.length;

                data.forEach(function(row) {
                    if (row.sla_status === 'Active') {
                        activeCount++;
                    } else if (row.sla_status === 'Overdue') {
                        overdueCount++;
                    } else if (row.sla_status === 'Pending Approval') {
                        pendingCount++;
                    }
                });

                $('#activeCount').text(activeCount);
                $('#overdueCount').text(overdueCount);
                $('#pendingCount').text(pendingCount);
                $('#totalCount').text(totalCount);

                // Add percentage information
                if (totalCount > 0) {
                    var activePercent = Math.round((activeCount / totalCount) * 100);
                    var overduePercent = Math.round((overdueCount / totalCount) * 100);
                    var pendingPercent = Math.round((pendingCount / totalCount) * 100);

                    $('#activeCount').attr('title', activeCount + ' (' + activePercent + '%)');
                    $('#overdueCount').attr('title', overdueCount + ' (' + overduePercent + '%)');
                    $('#pendingCount').attr('title', pendingCount + ' (' + pendingPercent + '%)');
                    $('#totalCount').attr('title', totalCount + ' total requests');

                    // Show/hide SLA alert warning
                    if (overdueCount > 0) {
                        $('#overdueAlertCount').text(overdueCount);
                        $('#slaAlert').removeClass('d-none');
                    } else {
                        $('#slaAlert').addClass('d-none');
                    }
                }
            }
        });
    </script>
@endpush
