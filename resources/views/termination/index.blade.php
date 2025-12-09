@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active">Termination</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Left col -->
                <div class="col-lg-12">
                    <!-- Custom tabs (Charts with tabs)-->
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <strong>{{ $subtitle }}</strong>
                                </h3>
                                <div class="card-tools">
                                    <ul class="nav nav-pills ml-auto">
                                        <li class="nav-item mr-2">
                                            <a href="{{ url('terminations/create') }}" class="btn btn-success"><i
                                                    class="fas fa-plus"></i>
                                                Add</a>
                                            <a href="{{ url('employees') }}" class="btn btn-warning"><i
                                                    class="fas fa-undo"></i>
                                                Back</a>
                                        </li>
                                    </ul>
                                </div>
                            </div><!-- /.card-header -->
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
                                            <div class="row form-group">
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">DOH From</label>
                                                        <input type="date" class="form-control" name="date1"
                                                            id="date1" value="{{ request('date1') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">DOH To</label>
                                                        <input type="date" class="form-control" name="date2"
                                                            id="date2" value="{{ request('date2') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">NIK</label>
                                                        <input type="text" class="form-control" name="nik"
                                                            id="nik" value="{{ request('nik') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Full Name</label>
                                                        <input type="text" class="form-control" name="fullname"
                                                            id="fullname" value="{{ request('fullname') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Project</label>
                                                        <select name="project_code" class="form-control select2bs4"
                                                            id="project_code" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            @foreach ($projects as $project => $data)
                                                                <option value="{{ $data->project_code }}"
                                                                    {{ request('project_code') == $data->project_code ? 'selected' : '' }}>
                                                                    {{ $data->project_code }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">POH</label>
                                                        <input type="text" class="form-control" name="poh"
                                                            id="poh" value="{{ request('poh') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Department</label>
                                                        <select name="department_name" class="form-control select2bs4"
                                                            id="department_name" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            @foreach ($departments as $department => $data)
                                                                <option value="{{ $data->department_name }}"
                                                                    {{ request('department_name') == $data->department_name ? 'selected' : '' }}>
                                                                    {{ $data->department_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Position</label>
                                                        <select name="position_name" class="form-control select2bs4"
                                                            id="position_name" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            @foreach ($positions as $position => $data)
                                                                <option value="{{ $data->position_name }}"
                                                                    {{ request('position_name') == $data->position_name ? 'selected' : '' }}>
                                                                    {{ $data->position_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Termination Date From</label>
                                                        <input type="date" class="form-control"
                                                            name="termination_date_from" id="termination_date_from"
                                                            value="{{ request('termination_date_from') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Termination Date To</label>
                                                        <input type="date" class="form-control"
                                                            name="termination_date_to" id="termination_date_to"
                                                            value="{{ request('termination_date_to') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Termination Reason</label>
                                                        <select name="termination_reason" class="form-control select2bs4"
                                                            id="termination_reason" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            <option value="End of Contract"
                                                                {{ request('termination_reason') == 'End of Contract' ? 'selected' : '' }}>
                                                                End of Contract</option>
                                                            <option value="End of Project"
                                                                {{ request('termination_reason') == 'End of Project' ? 'selected' : '' }}>
                                                                End of Project</option>
                                                            <option value="Resign"
                                                                {{ request('termination_reason') == 'Resign' ? 'selected' : '' }}>
                                                                Resign</option>
                                                            <option value="Termination"
                                                                {{ request('termination_reason') == 'Termination' ? 'selected' : '' }}>
                                                                Termination</option>
                                                            <option value="Retired"
                                                                {{ request('termination_reason') == 'Retired' ? 'selected' : '' }}>
                                                                Retired</option>
                                                            <option value="Efficiency"
                                                                {{ request('termination_reason') == 'Efficiency' ? 'selected' : '' }}>
                                                                Efficiency</option>
                                                            <option value="Passed Away"
                                                                {{ request('termination_reason') == 'Passed Away' ? 'selected' : '' }}>
                                                                Passed Away</option>
                                                            <option value="Canceled"
                                                                {{ request('termination_reason') == 'Canceled' ? 'selected' : '' }}>
                                                                Canceled</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">CoE No</label>
                                                        <input type="text" class="form-control" name="coe_no"
                                                            id="coe_no" value="{{ request('coe_no') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">&nbsp;</label>
                                                        <button id="btn-reset" type="button"
                                                            class="btn btn-danger btn-block">Reset</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="example1" width="100%"
                                        class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="align-middle text-center">No</th>
                                                <th class="align-middle text-center">NIK</th>
                                                <th class="align-middle">Full Name</th>
                                                <th class="align-middle">Department</th>
                                                <th class="align-middle">Position</th>
                                                <th class="align-middle">Project</th>
                                                <th class="align-middle">POH</th>
                                                <th class="align-middle">DOH</th>
                                                <th class="align-middle">Termination Date</th>
                                                <th class="align-middle">Reason</th>
                                                <th class="align-middle">CoE No</th>
                                                <th class="align-middle text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div><!-- /.card-body -->
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
                <!-- right col -->
            </div>
            <!-- /.row (main row) -->
        </div>
    </section>
@endsection

@section('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('scripts')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    {{-- <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script> --}}
    {{-- <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script> --}}
    <script src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        $(function() {
            var table = $("#example1").DataTable({
                responsive: true,
                autoWidth: true,
                lengthChange: true,
                lengthMenu: [
                        [10, 25, 50, 100, -1],
                        ['10', '25', '50', '100', 'Show all']
                    ]
                    //, dom: 'lBfrtpi'
                    ,
                dom: 'frtpi',
                buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('terminations.list') }}",
                    data: function(d) {
                        d.date1 = $('#date1').val();
                        d.date2 = $('#date2').val();
                        d.nik = $('#nik').val();
                        d.fullname = $('#fullname').val();
                        d.poh = $('#poh').val();
                        d.department_name = $('#department_name').val();
                        d.position_name = $('#position_name').val();
                        d.project_code = $('#project_code').val();
                        d.termination_date_from = $('#termination_date_from').val();
                        d.termination_date_to = $('#termination_date_to').val();
                        d.termination_reason = $('#termination_reason').val();
                        d.coe_no = $('#coe_no').val();
                        d.search = $("input[type=search][aria-controls=example1]").val();
                    }
                },
                columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }, {
                    data: "nik",
                    name: "nik",
                    orderable: false,
                }, {
                    data: "fullname",
                    name: "fullname",
                    orderable: false,
                }, {
                    data: "department_name",
                    name: "department_name",
                    orderable: false,
                }, {
                    data: "position_name",
                    name: "position_name",
                    orderable: false,
                }, {
                    data: "project_code",
                    name: "project_code",
                    orderable: false,
                }, {
                    data: "poh",
                    name: "poh",
                    orderable: false,
                }, {
                    data: "doh",
                    name: "doh",
                    orderable: false,
                }, {
                    data: "termination_date",
                    name: "termination_date",
                    orderable: false,
                }, {
                    data: "termination_reason",
                    name: "termination_reason",
                    orderable: false,
                }, {
                    data: "coe_no",
                    name: "coe_no",
                    orderable: false,
                }, {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false,
                    className: "text-center"
                }],
                fixedColumns: true,
            })

            // Initialize Select2
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });

            // Filter change event
            $('#date1, #date2, #nik, #fullname, #poh, #department_name, #position_name, #project_code, #termination_date_from, #termination_date_to, #termination_reason, #coe_no')
                .on('change', function() {
                    table.draw();
                });

            // Reset button
            $('#btn-reset').on('click', function() {
                $('#date1, #date2, #nik, #fullname, #poh, #department_name, #position_name, #project_code, #termination_date_from, #termination_date_to, #termination_reason, #coe_no')
                    .val('').trigger('change');
                table.draw();
            });
        });
    </script>
@endsection
