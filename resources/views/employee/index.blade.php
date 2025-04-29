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
                        <li class="breadcrumb-item active">Employees</li>
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
                                            <a href="{{ url('terminations') }}" class="btn btn-danger"><i
                                                    class="fas fa-ban"></i>
                                                Terminated</a>
                                            <a class="btn btn-primary" href="{{ url('employees/export/') }}"><i
                                                    class="fas fa-download"></i>
                                                Export</a>
                                            @role('administrator')
                                                <a class="btn btn-success" data-toggle="modal" data-target="#modal-import"><i
                                                        class="fas fa-upload"></i>
                                                    Import</a>
                                            @endrole
                                            <a href="{{ url('employees/create') }}" class="btn btn-warning"><i
                                                    class="fas fa-plus"></i>
                                                Add</a>
                                        </li>
                                    </ul>
                                </div>
                            </div><!-- /.card-header -->
                            <div class="card-body">
                                @if (session()->has('failures'))
                                    <div class="card card-danger">
                                        <div class="card-header">
                                            <h3 class="card-title"><i class="icon fas fa-exclamation-triangle"></i> Import
                                                Validation Errors</h3>

                                            <div class="card-tools">
                                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </div>
                                            <!-- /.card-tools -->
                                        </div>
                                        <div class="card-body" style="display: block;">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 5%">Sheet</th>
                                                            <th class="text-center" style="width: 5%">Row</th>
                                                            <th style="width: 20%">Column</th>
                                                            <th style="width: 20%">Value</th>
                                                            <th>Error Message</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach (session()->get('failures') as $failure)
                                                            <tr>
                                                                <td>{{ $failure['sheet'] }}</td>
                                                                <td class="text-center">{{ $failure['row'] }}</td>
                                                                <td>
                                                                    <strong>{{ ucwords(str_replace('_', ' ', $failure['attribute'])) }}</strong>
                                                                </td>
                                                                <td>
                                                                    @if (isset($failure['value']))
                                                                        {{ $failure['value'] }}
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    {{ $failure['errors'] }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="mt-1">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle"></i>
                                                    Please correct these errors in your Excel file and try importing again.
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- <div class="alert alert-warning alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert"
                                            aria-hidden="true">&times;</button>
                                        <h5 class="mb-3"><i class="icon fas fa-exclamation-triangle"></i> Import
                                            Validation Errors</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 15%">Sheet</th>
                                                        <th style="width: 10%">Row</th>
                                                        <th style="width: 20%">Column</th>
                                                        <th style="width: 20%">Value</th>
                                                        <th style="width: 50%">Error Message</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach (session()->get('failures') as $failure)
                                                        <tr>
                                                            <td class="text-center">{{ $failure['sheet'] }}</td>
                                                            <td class="text-center">{{ $failure['row'] }}</td>
                                                            <td>
                                                                <strong>{{ ucwords(str_replace('_', ' ', $failure['attribute'])) }}</strong>
                                                            </td>
                                                            <td>
                                                                @if (isset($failure['value']))
                                                                    {{ $failure['value'] }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                {{ $failure['errors'] }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Please correct these errors in your Excel file and try importing again.
                                            </small>
                                        </div>
                                    </div> --}}
                                @endif
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
                                                        <label class=" form-control-label">DOH From</label>
                                                        <input type="date" class="form-control" name="date1"
                                                            id="date1" value="{{ request('date1') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class=" form-control-label">DOH To</label>
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
                                                        <label class="form-control-label">Staff</label>
                                                        <select name="class" class="form-control select2bs4"
                                                            id="class" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            <option value="Staff">Staff</option>
                                                            <option value="Non Staff">Non Staff</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class=" form-control-label">&nbsp;</label>
                                                        <button id="btn-reset" type="button"
                                                            class="btn btn-danger btn-block">Reset</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="example1" width="100%" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="align-middle text-center">No</th>
                                                <th class="align-middle text-center">NIK</th>
                                                <th class="align-middle">Full Name</th>
                                                <th class="align-middle">POH</th>
                                                <th class="align-middle">DOH</th>
                                                <th class="align-middle">Department</th>
                                                <th class="align-middle">Position</th>
                                                <th class="align-middle">Project</th>
                                                <th class="align-middle">Class</th>
                                                <th class="align-middle text-center">Created Date</th>
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

        <div class="modal fade" id="modal-import">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Import Data</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form class="form-horizontal" action="{{ url('employees/import') }}" method="POST"
                        enctype="multipart/form-data">
                        <div class="modal-body">
                            @csrf
                            <div class="card-body">
                                <div class="tab-content p-0">
                                    <div class="form-group row">
                                        <label class="col-sm-5 col-form-label">Import Employee</label>
                                        <div class="col-sm-7">
                                            <input type="file" name="employee">
                                            @error('employee')
                                                <div class="error invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div><!-- /.card-body -->
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
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
                dom: 'rtpi',
                buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('employees.data') }}",
                    data: function(d) {
                        d.date1 = $('#date1').val(), d.date2 = $('#date2').val(), d.nik = $('#nik')
                            .val(), d.fullname = $('#fullname').val(), d.poh = $('#poh').val(), d
                            .department_name = $('#department_name').val(), d.position_name = $(
                                '#position_name').val(), d.project_code = $('#project_code').val(), d
                            .class = $('#class').val(), d.search = $(
                                "input[type=search][aria-controls=example1]").val()
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
                    data: "poh",
                    name: "poh",
                    orderable: false,
                }, {
                    data: "doh",
                    name: "doh",
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
                    data: "class",
                    name: "class",
                    orderable: false,
                }, {
                    data: "created_date",
                    name: "created_date",
                    orderable: false,
                    className: "text-center",
                }, {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false,
                    className: "text-center"
                }],
                fixedColumns: true,
            })
            $('#date1, #date2, #nik, #fullname, #poh, #department_name, #position_name, #project_code, #class')
                .keyup(function() {
                    table.draw();
                });
            $('#date1, #date2, #department_name, #position_name, #project_code, #class').change(function() {
                table.draw();
            });
            $('#btn-reset').click(function() {
                $('#date1, #date2, #nik, #fullname, #poh, #department_name, #position_name, #project_code, #class')
                    .val('');
                $('#date1, #date2, #department_name, #position_name, #project_code, #class').change();
            });
        });
    </script>

    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(function() {
            //Initialize Select2 Elements
            $('.select2').select2()

            //Initialize Select2 Elements
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            })

            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            })
        })
    </script>
@endsection
