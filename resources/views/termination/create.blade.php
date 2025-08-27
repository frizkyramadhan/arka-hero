@extends('layouts.main')
@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Termination</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
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
                <div class="col-12">
                    <div id="accordion">
                        <div class="card">
                            <form action="{{ url('terminations/massTermination') }}" method="POST">
                                @csrf
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <strong>{{ $subtitle }}</strong>
                                    </h3>
                                    <div class="card-tools">
                                        <ul class="nav nav-pills ml-auto">
                                            <li class="nav-item mr-2">
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i>
                                                    Save</button>
                                                <a href="{{ url('terminations') }}" class="btn btn-warning"><i
                                                        class="fas fa-undo"></i>
                                                    Back</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div><!-- /.card-header -->
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label">Termination Date</label>
                                                    <div class="col-sm-10">
                                                        <input type="date"
                                                            class="form-control @error('termination_date') is-invalid @enderror"
                                                            name="termination_date" value="{{ old('termination_date') }}">
                                                        @error('termination_date')
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label">Termination Reason</label>
                                                    <div class="col-sm-10">
                                                        <select name="termination_reason" class="form-control select2bs4"
                                                            style="width: 100%;">
                                                            <option value="">-Select Reason-</option>
                                                            <option value="End of Contract"
                                                                {{ old('termination_reason') == 'End of Contract' ? 'selected' : '' }}>
                                                                End of Contract</option>
                                                            <option value="End of Project"
                                                                {{ old('termination_reason') == 'End of Project' ? 'selected' : '' }}>
                                                                End of Project</option>
                                                            <option value="Resign"
                                                                {{ old('termination_reason') == 'Resign' ? 'selected' : '' }}>
                                                                Resign</option>
                                                            <option value="Termination"
                                                                {{ old('termination_reason') == 'Termination' ? 'selected' : '' }}>
                                                                Termination</option>
                                                            <option value="Retired"
                                                                {{ old('termination_reason') == 'Retired' ? 'selected' : '' }}>
                                                                Retired</option>
                                                            <option value="Efficiency"
                                                                {{ old('termination_reason') == 'Efficiency' ? 'selected' : '' }}>
                                                                Efficiency</option>
                                                            <option value="Passed Away"
                                                                {{ old('termination_reason') == 'Passed Away' ? 'selected' : '' }}>
                                                                Passed Away</option>
                                                        </select>
                                                        @error('termination_reson')
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <h4 class="card-title">
                                                    <strong>Active Employee</strong>
                                                </h4>
                                            </div>
                                        </div>
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
                                                                <select name="department_name"
                                                                    class="form-control select2bs4" id="department_name"
                                                                    style="width: 100%;">
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
                                                                <select name="position_name"
                                                                    class="form-control select2bs4" id="position_name"
                                                                    style="width: 100%;">
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
                                                                <select name="project_code"
                                                                    class="form-control select2bs4" id="project_code"
                                                                    style="width: 100%;">
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
                                        <table id="example1" width="100%"
                                            class="table table-sm table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        <div class="form-check">
                                                            <input type="checkbox" class="mr-4" id="checkAll">
                                                        </div>
                                                    </th>
                                                    <th class="align-middle text-center">NIK</th>
                                                    <th class="align-middle">Full Name</th>
                                                    <th class="align-middle">POH</th>
                                                    <th class="align-middle">DOH</th>
                                                    <th class="align-middle">Department</th>
                                                    <th class="align-middle">Position</th>
                                                    <th class="align-middle">Project</th>
                                                    <th class="align-middle">Class</th>
                                                    <th class="align-middle">CoE No</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.col -->
        </div>
        <!-- /.row -->
        </div>
        <!-- /.container-fluid -->

        <a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button"
            aria-label="Scroll to top">
            <i class="fas fa-chevron-up"></i>
        </a>
    </section>
@endsection

@section('styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('scripts')
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
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
                dom: 'lrtpi',
                buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('terminations.getActiveEmployees') }}",
                    data: function(d) {
                        d.date1 = $('#date1').val(), d.date2 = $('#date2').val(), d.nik = $('#nik')
                            .val(), d.fullname = $('#fullname').val(), d.poh = $('#poh').val(), d
                            .department_name = $('#department_name').val(), d.position_name = $(
                                '#position_name').val(), d.project_code = $('#project_code').val(), d
                            .class = $('#class').val()
                        d.search = $("input[type=search][aria-controls=example1]").val()
                        console.log(d);
                    }
                },
                columns: [{
                    data: 'checkbox',
                    name: 'checkbox',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }, {
                    data: "nik",
                    name: "nik",
                    orderable: false,
                    className: 'text-center',
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
                    data: "coe_no",
                    name: "coe_no",
                    orderable: false,
                    searchable: false,
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

            // Handle click on "Select all" control
            $(document).on('click', '#checkAll', function() {
                $('input:checkbox').not(this).prop('checked', this.checked);
            });

            // select2
            $('.select2').select2()
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            })
            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            })

        });
    </script>
@endsection
