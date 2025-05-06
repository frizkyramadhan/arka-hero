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
            <!-- Summary Card -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-birthday">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-birthday-cake mr-1"></i>
                                Birthday Celebrants
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h2 class="mb-0">{{ $birthdayCount }}</h2>
                                    <p class="text-muted">Employees with Birthday in {{ date('F') }}</p>
                                </div>
                                <div class="col-md-4 text-right">
                                    <button class="btn btn-birthday" onclick="reloadTable()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee List -->
            <div class="card">
                <div class="card-header bg-gradient-birthday">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-1"></i>
                        Birthday List
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
                                    <th>Birthday</th>
                                    <th>Age</th>
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
    <style>
        .card-birthday {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 50%, #f6d365 100%);
            color: #fff;
        }

        .card-birthday .card-header {
            background: rgba(0, 0, 0, 0.1);
            border-bottom: none;
        }

        .card-birthday .card-title {
            color: #fff;
        }

        .card-birthday .text-muted {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .btn-birthday {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .btn-birthday:hover {
            background: rgba(255, 255, 255, 0.3);
            color: #fff;
        }

        .bg-gradient-birthday {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 50%, #f6d365 100%);
            color: #fff;
        }
    </style>
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
                ajax: "{{ route('employees.birthday.list') }}",
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
                        data: 'birthday',
                        name: 'birthday'
                    },
                    {
                        data: 'age',
                        name: 'age',
                        className: 'text-right',
                        render: function(data) {
                            return data + ' y/o';
                        }
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
                    [5, 'asc']
                ]
            });

            // Function to reload table
            window.reloadTable = function() {
                table.ajax.reload();
            };
        });
    </script>
@endsection
