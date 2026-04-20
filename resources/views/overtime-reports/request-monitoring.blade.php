@extends('layouts.main')

@section('title', $title ?? 'Report Overtime Requests')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="m-0 font-weight-bold">
                        <i class="fas fa-business-time text-warning mr-2"></i>
                        {{ $title ?? 'Report Overtime Requests' }}
                    </h1>
                    <nav aria-label="breadcrumb" class="mt-1">
                        <ol class="breadcrumb mb-0 py-0 bg-transparent small">
                            <li class="breadcrumb-item"><a href="{{ route('overtime.reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">Overtime Requests</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-auto">
                    <a href="{{ route('overtime.reports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Reports
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-warning shadow-sm mb-4">
                <div class="card-header py-3 bg-white border-bottom">
                    <h5 class="card-title mb-0 text-dark">
                        <i class="fas fa-filter text-warning mr-2"></i>Filter Options
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $statusChoice = null;
                        if (isset($filters['status']) && $filters['status'] !== null && (string) $filters['status'] !== '') {
                            $statusChoice = (string) $filters['status'];
                        }
                        $projectChoice = null;
                        if (isset($filters['project_id']) && $filters['project_id'] !== null && (string) $filters['project_id'] !== '') {
                            $projectChoice = (string) $filters['project_id'];
                        }
                    @endphp
                    <form id="filterForm" action="#" onsubmit="return false">
                        <div class="row align-items-end">
                            <div class="col-md-6 col-lg-2 form-group mb-lg-0">
                                <label for="status" class="small font-weight-bold text-muted">Status</label>
                                <select class="form-control" name="status" id="status">
                                    <option value="" {{ $statusChoice === null ? 'selected' : '' }}>Select status</option>
                                    <option value="all" {{ $statusChoice === 'all' ? 'selected' : '' }}>All status</option>
                                    <option value="draft" {{ $statusChoice === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="pending" {{ $statusChoice === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ $statusChoice === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ $statusChoice === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="finished" {{ $statusChoice === 'finished' ? 'selected' : '' }}>Finished</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3 form-group mb-lg-0">
                                <label for="project_id" class="small font-weight-bold text-muted">Project</label>
                                <select class="form-control" name="project_id" id="project_id">
                                    <option value="" {{ $projectChoice === null ? 'selected' : '' }}>Select project</option>
                                    <option value="all" {{ $projectChoice === 'all' ? 'selected' : '' }}>All projects</option>
                                    @foreach ($projects as $p)
                                        <option value="{{ $p->id }}"
                                            {{ $projectChoice !== null && $projectChoice !== 'all' && (string) $p->id === $projectChoice ? 'selected' : '' }}>
                                            {{ $p->project_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2 form-group mb-lg-0">
                                <label for="date_from" class="small font-weight-bold text-muted">OT date from</label>
                                <input type="date" class="form-control" name="date_from" id="date_from"
                                    value="{{ $filters['date_from'] ?? '' }}">
                            </div>
                            <div class="col-md-6 col-lg-2 form-group mb-lg-0">
                                <label for="date_to" class="small font-weight-bold text-muted">OT date to</label>
                                <input type="date" class="form-control" name="date_to" id="date_to"
                                    value="{{ $filters['date_to'] ?? '' }}">
                            </div>
                            <div class="col-md-6 col-lg-3 form-group mb-lg-0">
                                <label for="register_number" class="small font-weight-bold text-muted">Register No.</label>
                                <input type="text" class="form-control" name="register_number" id="register_number"
                                    value="{{ $filters['register_number'] ?? '' }}" placeholder="e.g. 26OT-">
                            </div>
                        </div>
                        <div class="row align-items-end mt-2">
                            <div class="col-md-4 form-group mb-lg-0">
                                <label for="requester_q" class="small font-weight-bold text-muted">Requester</label>
                                <input type="text" class="form-control" id="requester_q" name="requester_q"
                                    value="{{ $filters['requester_q'] ?? '' }}" placeholder="Name (partial)">
                            </div>
                            <div class="col-md-4 form-group mb-lg-0">
                                <label for="employee_q" class="small font-weight-bold text-muted">Employees</label>
                                <input type="text" class="form-control" id="employee_q" name="employee_q"
                                    value="{{ $filters['employee_q'] ?? '' }}" placeholder="NIK or name">
                            </div>
                            <div class="col-md-4 form-group mb-lg-0">
                                <label for="remarks_q" class="small font-weight-bold text-muted">Remarks</label>
                                <input type="text" class="form-control" id="remarks_q" name="remarks_q"
                                    value="{{ $filters['remarks_q'] ?? '' }}" placeholder="Text in remarks">
                            </div>
                        </div>
                        <div class="row mt-3 pt-3 border-top">
                            <div class="col-12 d-flex flex-wrap align-items-center">
                                <button type="button" id="btn-show-data" class="btn btn-warning mr-2 mb-1">
                                    <i class="fas fa-search mr-1"></i> Tampilkan data
                                </button>
                                <a href="{{ route('overtime.reports.request-monitoring') }}" class="btn btn-outline-secondary mb-1 mr-2">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </a>
                                <a href="#" id="btn-export-excel" class="btn btn-success btn-sm mb-1" title="Minimal satu filter, sama seperti Tampilkan data">
                                    <i class="fas fa-file-excel mr-1"></i> Export to Excel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header py-3 bg-white border-bottom">
                    <h5 class="card-title mb-0 text-dark">
                        <i class="fas fa-table text-warning mr-2"></i>Report Data
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive overtime-report-table-wrapper">
                        <table id="overtime-report-table" class="table table-hover table-bordered mb-0 overtime-report-table"
                            style="width: 100%">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center align-middle bg-light">No</th>
                                    <th class="align-middle bg-light text-nowrap">Register No.</th>
                                    <th class="align-middle bg-light">Project</th>
                                    <th class="text-center align-middle bg-light">OT date</th>
                                    <th class="text-center align-middle bg-light">Status</th>
                                    <th class="align-middle bg-light">Requester</th>
                                    <th class="align-middle bg-light">Employees</th>
                                    <th class="align-middle bg-light">Remarks</th>
                                    <th class="text-center align-middle bg-light">Requested at</th>
                                    <th class="text-center align-middle bg-light">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .overtime-report-table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .overtime-report-table {
            font-size: 0.875rem;
        }

        .overtime-report-table thead th {
            white-space: nowrap;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            padding: 0.75rem 0.5rem;
        }

        .overtime-report-table tbody td {
            padding: 0.6rem 0.5rem;
            vertical-align: middle;
        }

        .card-outline.card-warning {
            border-top: 3px solid #ffc107;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#status').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
            $('#project_id').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            function filterPayload(d) {
                d.status = $('#status').val() || '';
                d.project_id = $('#project_id').val() || '';
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
                d.register_number = $('#register_number').val();
                d.requester_q = $('#requester_q').val();
                d.employee_q = $('#employee_q').val();
                d.remarks_q = $('#remarks_q').val();
            }

            function filterFieldIsSet(v) {
                return v === 'all' || (v != null && v !== '');
            }

            function hasActiveFilter() {
                var st = $('#status').val();
                var pr = $('#project_id').val();
                return filterFieldIsSet(st) || filterFieldIsSet(pr) || $('#date_from').val() || $('#date_to').val() ||
                    $('#register_number').val() || $('#requester_q').val() || $('#employee_q').val() || $('#remarks_q').val();
            }

            function buildReportSearchParams() {
                var params = new URLSearchParams();
                var map = {
                    status: $('#status').val(),
                    project_id: $('#project_id').val(),
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val(),
                    register_number: $('#register_number').val(),
                    requester_q: $('#requester_q').val(),
                    employee_q: $('#employee_q').val(),
                    remarks_q: $('#remarks_q').val()
                };
                Object.keys(map).forEach(function(k) {
                    var v = map[k];
                    if (v !== null && v !== undefined && String(v).trim() !== '') {
                        params.append(k, v);
                    }
                });
                return params;
            }

            function syncFiltersToUrl() {
                var params = buildReportSearchParams();
                var qs = params.toString();
                var base = "{{ route('overtime.reports.request-monitoring') }}";
                history.replaceState(null, '', qs ? base + '?' + qs : base);
            }

            function loadReportTable() {
                if (!hasActiveFilter()) {
                    alert('Pilih Status (atau All status) dan/atau Project (atau All projects), atau isi filter lain, lalu klik Tampilkan data.');
                    return;
                }
                syncFiltersToUrl();
                table.ajax.reload();
            }

            var table = $('#overtime-report-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: "{{ route('overtime.reports.request-monitoring.data') }}",
                    data: function(d) {
                        filterPayload(d);
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'register_number',
                        name: 'register_number',
                        orderable: false,
                        className: 'text-nowrap font-monospace small'
                    },
                    {
                        data: 'project_name',
                        name: 'project_name',
                        orderable: false
                    },
                    {
                        data: 'overtime_date_fmt',
                        name: 'overtime_date_fmt',
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'status_badge',
                        name: 'status_badge',
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'requester',
                        name: 'requester',
                        orderable: false
                    },
                    {
                        data: 'employees_html',
                        name: 'employees_html',
                        orderable: false
                    },
                    {
                        data: 'remarks_html',
                        name: 'remarks_html',
                        orderable: false
                    },
                    {
                        data: 'requested_at_fmt',
                        name: 'requested_at_fmt',
                        orderable: false,
                        className: 'text-center text-nowrap'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, 250, 500],
                    [10, 25, 50, 100, 250, 500]
                ],
                language: {
                    emptyTable: 'Pilih Status / Project (atau All), atau isi filter lain, lalu klik Tampilkan data.',
                    zeroRecords: 'No matching records found.',
                    processing: 'Loading...',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'Showing 0 to 0 of 0 entries',
                    infoFiltered: '(filtered from _MAX_ total entries)',
                    lengthMenu: 'Show _MENU_ entries',
                    paginate: {
                        first: 'First',
                        last: 'Last',
                        next: 'Next',
                        previous: 'Previous'
                    }
                }
            });

            $('#btn-show-data').on('click', loadReportTable);

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                loadReportTable();
            });

            $('#btn-export-excel').on('click', function(e) {
                e.preventDefault();
                if (!hasActiveFilter()) {
                    alert('Minimal satu filter harus diisi sebelum export.');
                    return;
                }
                var params = buildReportSearchParams();
                window.location.href = "{{ route('overtime.reports.request-monitoring.export') }}?" + params.toString();
            });

            @if (request()->filled('status') || request()->filled('project_id') || request()->filled('date_from') || request()->filled('date_to') || request()->filled('register_number') || request()->filled('requester_q') || request()->filled('employee_q') || request()->filled('remarks_q'))
                syncFiltersToUrl();
                table.ajax.reload();
            @endif
        });
    </script>
@endsection
