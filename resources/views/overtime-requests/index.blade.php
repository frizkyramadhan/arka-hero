@extends('layouts.main')

@section('title', $title ?? 'Overtime Requests')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title ?? 'Overtime Requests' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active">{{ $title ?? 'Overtime Requests' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">{{ $title ?? 'Overtime Requests' }}</h3>
                                <div class="card-tools">
                                    @can('overtime-requests.create')
                                        <a href="{{ route('overtime.requests.create') }}" class="btn btn-warning">
                                            <i class="fas fa-plus"></i> Add
                                        </a>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h4 class="card-title w-100">
                                            <a class="d-block w-100" data-toggle="collapse" href="#collapseOne">
                                                <i class="fas fa-filter"></i> Filter
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapseOne" class="collapse" data-parent="#accordion">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select class="form-control select2bs4" id="filter_status" name="status">
                                                            <option value="">- All -</option>
                                                            <option value="draft">Draft</option>
                                                            <option value="pending">Pending</option>
                                                            <option value="approved">Approved</option>
                                                            <option value="rejected">Rejected</option>
                                                            <option value="finished">Finished</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Project</label>
                                                        <select class="form-control select2bs4" id="filter_project_id" name="project_id">
                                                            <option value="">- All -</option>
                                                            @foreach ($projects as $p)
                                                                <option value="{{ $p->id }}">{{ $p->project_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Date from</label>
                                                        <input type="date" class="form-control" id="filter_date_from" name="date_from">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Date to</label>
                                                        <input type="date" class="form-control" id="filter_date_to" name="date_to">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <button type="button" class="btn btn-secondary w-100" id="btn-reset-filter">
                                                            <i class="fas fa-times"></i> Reset
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="filter_requester_q">Requester</label>
                                                        <input type="text" class="form-control" id="filter_requester_q" name="requester_q" placeholder="Name (partial match)" autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="filter_employee_q">Employees</label>
                                                        <input type="text" class="form-control" id="filter_employee_q" name="employee_q" placeholder="NIK or employee name" autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="filter_remarks_q">Remarks</label>
                                                        <input type="text" class="form-control" id="filter_remarks_q" name="remarks_q" placeholder="Text in remarks" autocomplete="off">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="overtime-requests-table" class="table table-bordered table-striped" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="align-middle text-center" width="5%">No</th>
                                                <th class="align-middle">Project</th>
                                                <th class="align-middle">Date</th>
                                                <th class="align-middle text-center">Status</th>
                                                <th class="align-middle">Requester</th>
                                                <th class="align-middle">Employees</th>
                                                <th class="align-middle">Remarks</th>
                                                <th class="align-middle text-center" width="12%">Actions</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            var table = $('#overtime-requests-table').DataTable({
                responsive: true,
                autoWidth: true,
                processing: true,
                serverSide: true,
                searching: false,
                dom: 'rtip',
                ajax: {
                    url: "{{ route('overtime.requests.data') }}",
                    data: function(d) {
                        d.status = $('#filter_status').val();
                        d.project_id = $('#filter_project_id').val();
                        d.date_from = $('#filter_date_from').val();
                        d.date_to = $('#filter_date_to').val();
                        d.requester_q = $('#filter_requester_q').val();
                        d.employee_q = $('#filter_employee_q').val();
                        d.remarks_q = $('#filter_remarks_q').val();
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
                        data: 'project_name',
                        name: 'project_name',
                        orderable: false
                    },
                    {
                        data: 'overtime_date_fmt',
                        name: 'overtime_date',
                        className: 'text-center'
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
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
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [
                    [2, 'desc']
                ]
            });

            var filterTextTimer;
            $('#filter_status, #filter_project_id').on('change', function() {
                table.draw();
            });
            $('#filter_date_from, #filter_date_to').on('change', function() {
                table.draw();
            });
            $('#filter_requester_q, #filter_employee_q, #filter_remarks_q').on('keyup', function() {
                clearTimeout(filterTextTimer);
                filterTextTimer = setTimeout(function() {
                    table.draw();
                }, 450);
            });
            $('#btn-reset-filter').on('click', function() {
                $('#filter_status, #filter_project_id').val('').trigger('change');
                $('#filter_date_from, #filter_date_to').val('');
                $('#filter_requester_q, #filter_employee_q, #filter_remarks_q').val('');
                table.draw();
            });
        });
    </script>
@endsection
