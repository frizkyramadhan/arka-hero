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
                                    @foreach (\App\Models\Project::orderBy('project_name')->get() as $proj)
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
                                    <option value="submitted" {{ $status == 'submitted' ? 'selected' : '' }}>Submitted
                                    </option>
                                    <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Approved
                                    </option>
                                    <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rejected
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-primary mr-2">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <button type="button" id="exportExcelBtn" class="btn btn-success">
                                        <i class="fas fa-file-excel"></i> Export
                                    </button>
                                </div>
                            </div>
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
                                <th class="align-middle">SLA Target</th>
                                <th class="align-middle">SLA Status</th>
                                <th class="align-middle">SLA Days Remaining</th>
                                <th class="align-middle">Remarks</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        <strong>SLA Information:</strong>
                        SLA period is calculated based on approval flow type and request reason.
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
@endpush

@push('scripts')
    <!-- DataTables -->
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            var table = $('#agingTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('recruitment.reports.aging.data') }}',
                    type: 'GET',
                    data: function(d) {
                        d.date1 = '{{ $date1 }}';
                        d.date2 = '{{ $date2 }}';
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
                autoWidth: false,
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
                window.open('{{ route('recruitment.reports.aging.export') }}?' + params, '_blank');
            });

            // Update SLA summary counts
            function updateSLASummary() {
                var activeCount = 0;
                var overdueCount = 0;
                var pendingCount = 0;
                var totalCount = 0;

                table.rows().every(function() {
                    var data = this.data();
                    totalCount++;

                    if (data.sla_status === 'Active') {
                        activeCount++;
                    } else if (data.sla_status === 'Overdue') {
                        overdueCount++;
                    } else if (data.sla_status === 'Pending Approval') {
                        pendingCount++;
                    }
                });

                $('#activeCount').text(activeCount);
                $('#overdueCount').text(overdueCount);
                $('#pendingCount').text(pendingCount);
                $('#totalCount').text(totalCount);

                // Show alert if there are overdue items
                if (overdueCount > 0) {
                    $('#overdueAlertCount').text(overdueCount);
                    $('#slaAlert').removeClass('d-none');
                } else {
                    $('#slaAlert').addClass('d-none');
                }
            }

            // Update summary after table load
            table.on('draw', function() {
                updateSLASummary();
            });

            // Initial summary update
            updateSLASummary();
        });
    </script>
@endpush
