@extends('layouts.main')


@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
    <div class="row">
        <div class="container-fluid">
            <section class="content">
                <div class="container-fluid">
                    <!-- Small boxes (Stat box) -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $hoCount }}</h3>

                                    <p>000H</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-bag"></i>
                                </div>
                                <a class="small-box-footer" data-toggle="modal" data-target="#modal-lg1">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $boCount }}</h3>

                                    <p>001H</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-stats-bars"></i>
                                </div>
                                <a class="small-box-footer" data-toggle="modal" data-target="#modal-lg2">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $malinauCount }}</h3>

                                    <p>017C</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-person-add"></i>
                                </div>
                                <a class="small-box-footer" data-toggle="modal" data-target="#modal-lg3">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3>{{ $sbiCount }}</h3>

                                    <p>021C</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-pie-graph"></i>
                                </div>
                                <a class="small-box-footer" data-toggle="modal" data-target="#modal-lg4">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-secondary">
                                <div class="inner">
                                    <h3>{{ $gpkCount }}</h3>

                                    <p>022C</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-pie-graph"></i>
                                </div>
                                <a class="small-box-footer" data-toggle="modal" data-target="#modal-lg5">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-gradient">
                                <div class="inner">
                                    <h3>{{ $bekCount }}</h3>

                                    <p>023C</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-pie-graph"></i>
                                </div>
                                <a class="small-box-footer" data-toggle="modal" data-target="#modal-lg6">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-dark">
                                <div class="inner">
                                    <h3>{{ $apsCount }}</h3>

                                    <p>APS</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-pie-graph"></i>
                                </div>
                                <a class="small-box-footer" data-toggle="modal" data-target="#modal-lg7">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $employeeCount }}</h3>

                                    <p>Employee </p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-pie-graph"></i>
                                </div>
                                <a class="small-box-footer" data-toggle="modal" data-target="#modal-lg8">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $terminationCount }}</h3>

                                    <p>Termination Employee</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-pie-graph"></i>
                                </div>
                                <a class="small-box-footer" data-toggle="modal" data-target="#modal-lg9">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <!-- small box -->
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $Contract }}</h3>

                                    <p>30 Day Will End The Contract</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-pie-graph"></i>
                                </div>
                                <a class="small-box-footer" data-toggle="modal" data-target="#modal-lg10">More info <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="modal fade" id="modal-lg1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Employee Project 000H</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example1" width="100%"
                                        class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th>NIK</th>
                                                <th>Full Name</th>
                                                <th class="text-center">POH</th>
                                                <th class="text-center">DOH</th>
                                                <th class="text-center">Department</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Project</th>
                                                <th class="text-center">Class</th>
                                                {{-- <th class="text-center">Detail</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal fade" id="modal-lg2">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Employee Project 001H</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example2" width="100%"
                                        class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th>NIK</th>
                                                <th>Full Name</th>
                                                <th class="text-center">POH</th>
                                                <th class="text-center">DOH</th>
                                                <th class="text-center">Department</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Project</th>
                                                <th class="text-center">Class</th>
                                                {{-- <th class="text-center">Detail</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal fade" id="modal-lg3">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Employee Project 017C</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example3" width="100%"
                                        class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th>NIK</th>
                                                <th>Full Name</th>
                                                <th class="text-center">POH</th>
                                                <th class="text-center">DOH</th>
                                                <th class="text-center">Department</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Project</th>
                                                <th class="text-center">Class</th>
                                                {{-- <th class="text-center">Detail</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal fade" id="modal-lg4">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Employee Project 021C</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example4" width="100%"
                                        class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th>NIK</th>
                                                <th>Full Name</th>
                                                <th class="text-center">POH</th>
                                                <th class="text-center">DOH</th>
                                                <th class="text-center">Department</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Project</th>
                                                <th class="text-center">Class</th>
                                                {{-- <th class="text-center">Detail</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal fade" id="modal-lg5">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Employee Project 022C</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example5" width="100%"
                                        class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th>NIK</th>
                                                <th>Full Name</th>
                                                <th class="text-center">POH</th>
                                                <th class="text-center">DOH</th>
                                                <th class="text-center">Department</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Project</th>
                                                <th class="text-center">Class</th>
                                                {{-- <th class="text-center">Detail</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal fade" id="modal-lg6">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Employee Project 023C</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example6" width="100%"
                                        class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th>NIK</th>
                                                <th>Full Name</th>
                                                <th class="text-center">POH</th>
                                                <th class="text-center">DOH</th>
                                                <th class="text-center">Department</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Project</th>
                                                <th class="text-center">Class</th>
                                                {{-- <th class="text-center">Detail</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal fade" id="modal-lg7">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Employee Project APS</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example7" width="100%"
                                        class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th>NIK</th>
                                                <th>Full Name</th>
                                                <th class="text-center">POH</th>
                                                <th class="text-center">DOH</th>
                                                <th class="text-center">Department</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Project</th>
                                                <th class="text-center">Class</th>
                                                {{-- <th class="text-center">Detail</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal fade" id="modal-lg8">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Employee All Project</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example8" width="100%"
                                        class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th>NIK</th>
                                                <th>Full Name</th>
                                                <th class="text-center">POH</th>
                                                <th class="text-center">DOH</th>
                                                <th class="text-center">FOC</th>
                                                <th class="text-center">Department</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Project</th>
                                                <th class="text-center">Class</th>
                                                {{-- <th class="text-center">Detail</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal fade" id="modal-lg9">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Termination Employee</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example9" width="100%"
                                        class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th>Full Name</th>
                                                <th class="text-center">Termination Date</th>
                                                <th class="text-center">Termination Reason</th>
                                                <th class="text-center">COE No</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal fade" id="modal-lg10">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">30 Day Will End The Contract Employee</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example10" width="100%"
                                        class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th>NIK</th>
                                                <th>Full Name</th>
                                                <th class="text-center">POH</th>
                                                <th class="text-center">DOH</th>
                                                <th class="text-center bg-warning text-warning">FOC</th>
                                                <th class="text-center">Department</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Project</th>
                                                <th class="text-center">Class</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </section>
        </div>
    </div>
@endsection

@section('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
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
                dom: 'frtpi',
                buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('hobpn.list') }}",
                    data: function(d) {
                        d.search = $("input[type=search][aria-controls=example1]").val()
                        console.log(d);
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
                }],
                fixedColumns: true,
            })
        });
    </script>

    <script>
        $(function() {
            var table = $("#example2").DataTable({
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
                    url: "{{ route('bojkt.list') }}",
                    data: function(d) {
                        d.search = $("input[type=search][aria-controls=example2]").val()
                        console.log(d);
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
                }],
                fixedColumns: true,
            })
        });
    </script>

    <script>
        $(function() {
            var table = $("#example3").DataTable({
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
                    url: "{{ route('kpuc.list') }}",
                    data: function(d) {
                        d.search = $("input[type=search][aria-controls=example3]").val()
                        console.log(d);
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
                }],
                fixedColumns: true,
            })
        });
    </script>

    <script>
        $(function() {
            var table = $("#example4").DataTable({
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
                    url: "{{ route('sbi.list') }}",
                    data: function(d) {
                        d.search = $("input[type=search][aria-controls=example4]").val()
                        console.log(d);
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
                }],
                fixedColumns: true,
            })
        });
    </script>

    <script>
        $(function() {
            var table = $("#example5").DataTable({
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
                    url: "{{ route('gpk.list') }}",
                    data: function(d) {
                        d.search = $("input[type=search][aria-controls=example5]").val()
                        console.log(d);
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
                }],
                fixedColumns: true,
            })
        });
    </script>

    <script>
        $(function() {
            var table = $("#example6").DataTable({
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
                    url: "{{ route('bek.list') }}",
                    data: function(d) {
                        d.search = $("input[type=search][aria-controls=example6]").val()
                        console.log(d);
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
                }],
                fixedColumns: true,
            })
        });
    </script>

    <script>
        $(function() {
            var table = $("#example7").DataTable({
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
                    url: "{{ route('aps.list') }}",
                    data: function(d) {
                        d.search = $("input[type=search][aria-controls=example7]").val()
                        console.log(d);
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
                }],
                fixedColumns: true,
            })
        });
    </script>

    <script>
        $(function() {
            var table = $("#example8").DataTable({
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
                    url: "{{ route('employee.list') }}",
                    data: function(d) {
                        d.search = $("input[type=search][aria-controls=example8]").val()
                        console.log(d);
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
                    data: "foc",
                    name: "foc",
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
                }],
                fixedColumns: true,
            })
        });
    </script>

    <script>
        $(function() {
            var table = $("#example9").DataTable({
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
                    url: "{{ route('termination.list') }}",
                    data: function(d) {
                        d.search = $("input[type=search][aria-controls=example9]").val()
                        console.log(d);
                    }
                },
                columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }, {
                    data: "fullname",
                    name: "fullname",
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
                }],
                fixedColumns: true,
            })
        });
    </script>

    <script>
        $(function() {
            var table = $("#example10").DataTable({
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
                    url: "{{ route('contract.list') }}",
                    data: function(d) {
                        d.search = $("input[type=search][aria-controls=example10]").val()
                        console.log(d);
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
                    data: "foc",
                    name: "foc",
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
                }],
                fixedColumns: true,
            })
        });
    </script>
@endsection
