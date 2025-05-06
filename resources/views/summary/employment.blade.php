@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">{{ $subtitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Summary Cards -->
            <div class="row">
                <!-- Permanent Employees -->
                <div class="col-lg-6">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user-check mr-1"></i>
                                Permanent Employees
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h2 class="mb-0">{{ $permanentCount }}</h2>
                                    <p class="text-muted">Total Permanent Employees</p>
                                </div>
                                <div class="col-md-4 text-right">
                                    <button class="btn btn-success" onclick="filterTable('permanent')">
                                        <i class="fas fa-users"></i> View List
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contract Employees -->
                <div class="col-lg-6">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user-clock mr-1"></i>
                                Contract Employees
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h2 class="mb-0">{{ $contractCount }}</h2>
                                    <p class="text-muted">Total Contract Employees</p>
                                </div>
                                <div class="col-md-4 text-right">
                                    <button class="btn btn-warning" onclick="filterTable('contract')">
                                        <i class="fas fa-users"></i> View List
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee List -->
            <div class="card">
                <div class="card-header bg-gradient-warning">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-1"></i>
                        Employee List
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="employees-table" class="table table-bordered table-striped table-hover"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>NIK</th>
                                    <th>Name</th>
                                    <th>Project</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th>End Date</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('scripts')
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
    <script>
        $(function() {
            var table = $('#employees-table').DataTable({
                processing: true,
                serverSide: true,
                searchDelay: 500,
                ajax: {
                    url: "{{ route('employees.employment.list') }}",
                    data: function(d) {
                        d.status = currentStatus;
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'nik',
                        name: 'nik'
                    },
                    {
                        data: 'fullname',
                        name: 'fullname'
                    },
                    {
                        data: 'project_code',
                        name: 'project_code'
                    },
                    {
                        data: 'position_name',
                        name: 'position_name'
                    },
                    {
                        data: 'employment_status',
                        name: 'employment_status'
                    },
                    {
                        data: 'foc',
                        name: 'foc',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [
                    [1, 'asc']
                ]
            });

            // Global variable to store current status filter
            var currentStatus = '';

            // Function to filter table by status
            window.filterTable = function(status) {
                currentStatus = status;
                table.ajax.reload();
            };
        });
    </script>
@endsection
