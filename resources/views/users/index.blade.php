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
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $stats['users'] }}</h3>
                            <p>Total Users</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="{{ route('users.index') }}" class="small-box-footer">
                            Manage Users <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $stats['roles'] }}</h3>
                            <p>Total Roles</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <a href="{{ route('roles.index') }}" class="small-box-footer">
                            Manage Roles <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $stats['permissions'] }}</h3>
                            <p>Total Permissions</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <a href="{{ route('permissions.index') }}" class="small-box-footer">
                            Manage Permissions <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
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
                                            <a class="btn btn-warning" href="{{ route('users.create') }}"><i
                                                    class="fas fa-plus"></i>
                                                Add</a>
                                        </li>
                                    </ul>
                                </div>
                            </div><!-- /.card-header -->
                            <div class="card-body">
                                <!-- Filter Form -->
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
                                                        <label class="form-control-label">Name</label>
                                                        <input type="text" class="form-control" name="filter_name"
                                                            id="filter_name" value="{{ request('filter_name') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Email</label>
                                                        <input type="text" class="form-control" name="filter_email"
                                                            id="filter_email" value="{{ request('filter_email') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Employee</label>
                                                        <input type="text" class="form-control" name="filter_employee"
                                                            id="filter_employee" value="{{ request('filter_employee') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Project</label>
                                                        <select name="filter_project" class="form-control select2bs4"
                                                            id="filter_project" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            @foreach ($projects as $project)
                                                                <option value="{{ $project->id }}"
                                                                    {{ request('filter_project') == $project->id ? 'selected' : '' }}>
                                                                    {{ $project->project_code }} -
                                                                    {{ $project->project_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Department</label>
                                                        <select name="filter_department" class="form-control select2bs4"
                                                            id="filter_department" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            @foreach ($departments as $department)
                                                                <option value="{{ $department->id }}"
                                                                    {{ request('filter_department') == $department->id ? 'selected' : '' }}>
                                                                    {{ $department->department_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Role</label>
                                                        <select name="filter_role" class="form-control select2bs4"
                                                            id="filter_role" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            @foreach ($roles as $role)
                                                                <option value="{{ $role->id }}"
                                                                    {{ request('filter_role') == $role->id ? 'selected' : '' }}>
                                                                    {{ $role->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Status</label>
                                                        <select name="filter_status" class="form-control select2bs4"
                                                            id="filter_status" style="width: 100%;">
                                                            <option value=""
                                                                {{ request('filter_status') == '' ? 'selected' : '' }}>
                                                                - All -</option>
                                                            <option value="1"
                                                                {{ request('filter_status') == '1' ? 'selected' : '' }}>
                                                                Active</option>
                                                            <option value="0"
                                                                {{ request('filter_status') == '0' ? 'selected' : '' }}>
                                                                Inactive</option>
                                                        </select>
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
                                        class="table table-bordered table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th class="align-middle" width="5%">No</th>
                                                <th class="align-middle">Name</th>
                                                <th class="align-middle">Email</th>
                                                <th class="align-middle">Employee</th>
                                                <th class="align-middle">Projects</th>
                                                <th class="align-middle">Departments</th>
                                                <th class="align-middle">Roles</th>
                                                <th class="align-middle" width="10%">Status</th>
                                                <th class="align-middle" width="10%">Action</th>
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
            <!-- Setelah tabel users, tambahkan ringkasan roles dan permissions -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><strong>Roles Overview</strong></h3>
                        </div>
                        <div class="card-body">
                            <table id="roles-overview-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Role Name</th>
                                        <th>Users Count</th>
                                        <th>Permissions Count</th>
                                        <th style="width: 15%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rolesSummary as $role)
                                        <tr>
                                            <td>{{ $role->name }}</td>
                                            <td>{{ $role->users_count }}</td>
                                            <td>{{ $role->permissions_count }}</td>
                                            <td class="text-center">
                                                <a class="btn btn-icon btn-warning"
                                                    href="{{ route('roles.edit', $role->id) }}"><i
                                                        class="fas fa-pen-square"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><strong>Permissions Overview</strong></h3>
                        </div>
                        <div class="card-body">
                            <table id="permissions-overview-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Permission Name</th>
                                        <th>Used by Roles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($permissionsSummary as $permission)
                                        <tr>
                                            <td>{{ $permission->name }}</td>
                                            <td>{{ $permission->roles_count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row (main row) -->
        </div>
        <!-- /.modal -->
    </section>
@endsection

@section('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        /* DataTable Header Styling */
        #example1 thead th {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            font-weight: 600;
            color: #495057;
        }

        #example1 {
            width: 100% !important;
            font-size: 0.875rem;
        }

        #example1 th,
        #example1 td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding: 0.5rem;
        }

        /* Allow wrapping for Name and Email columns */
        #example1 th:nth-child(2),
        #example1 td:nth-child(2),
        #example1 th:nth-child(3),
        #example1 td:nth-child(3) {
            white-space: normal;
            word-wrap: break-word;
        }

        /* Projects, Departments, and Roles columns - vertical display */
        #example1 td:nth-child(5),
        #example1 td:nth-child(6),
        #example1 td:nth-child(7) {
            white-space: normal;
            vertical-align: top;
        }

        /* Badge styling for vertical layout */
        #example1 td .d-flex.flex-column .badge {
            display: inline-block;
            width: fit-content;
            max-width: 100%;
            word-wrap: break-word;
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
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        $(function() {
            // Variable to store pending AJAX request
            var xhr = null;

            //Initialize Select2 Elements
            $('.select2').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#modal-lg')
            });

            //Initialize Select2 Elements
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });

            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            });

            var table = $("#example1").DataTable({
                processing: true,
                serverSide: true,
                searchDelay: 500, // Add delay for search input to prevent rapid requests
                ajax: {
                    url: "{{ route('users.data') }}",
                    type: 'GET',
                    data: function(d) {
                        d.filter_name = $('#filter_name').val();
                        d.filter_email = $('#filter_email').val();
                        d.filter_employee = $('#filter_employee').val();
                        d.filter_project = $('#filter_project').val();
                        d.filter_department = $('#filter_department').val();
                        d.filter_role = $('#filter_role').val();
                        d.filter_status = $('#filter_status').val();
                        d.search = $("input[type=search][aria-controls=example1]").val();
                    },
                    beforeSend: function(jqXHR, settings) {
                        // Cancel previous pending request
                        if (xhr && xhr.readyState !== 4) {
                            xhr.abort();
                        }
                        // Store current request
                        xhr = jqXHR;
                    },
                    error: function(xhr, error, thrown) {
                        // Suppress error if request was aborted
                        if (xhr.statusText === 'abort') {
                            return;
                        }
                        // Handle other errors silently to prevent DataTables warning
                        console.error('DataTables Ajax Error:', error);
                    },
                    complete: function() {
                        // Clear xhr reference when request completes
                        xhr = null;
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: "name",
                        name: "name",
                        orderable: false,
                    },
                    {
                        data: "email",
                        name: "email",
                        orderable: false,
                    },
                    {
                        data: "employee",
                        name: "employee",
                        orderable: false,
                    },
                    {
                        data: "projects",
                        name: "projects",
                        orderable: false,
                    },
                    {
                        data: "departments",
                        name: "departments",
                        orderable: false,
                    },
                    {
                        data: "roles",
                        name: "roles",
                        orderable: false,
                    },
                    {
                        data: "user_status",
                        name: "user_status",
                        orderable: false,
                        className: "text-center",
                    },
                    {
                        data: "action",
                        name: "action",
                        orderable: false,
                        searchable: false,
                        className: "text-center"
                    }
                ],
                dom: 'rtpi',
                lengthChange: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    ['10', '25', '50', '100', 'All']
                ],
                language: {
                    processing: "Processing...",
                    lengthMenu: "Show _MENU_ entries per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    zeroRecords: "No matching records found",
                    emptyTable: "No data available in table",
                    paginate: {
                        first: "First",
                        previous: "Previous",
                        next: "Next",
                        last: "Last"
                    }
                }
            });

            // Enable tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Debounce function to prevent rapid requests
            var debounceTimer = null;

            function debounceDraw(callback, delay) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(callback, delay);
            }

            // Apply debouncing to text inputs (keyup events)
            $('#filter_name, #filter_email, #filter_employee')
                .on('keyup', function() {
                    debounceDraw(function() {
                        table.draw();
                    }, 500); // 500ms delay
                });

            // Immediate draw for select changes (no debounce needed)
            $('#filter_project, #filter_department, #filter_role, #filter_status')
                .on('change', function() {
                    table.draw();
                });

            $('#btn-reset').click(function() {
                $('#filter_name, #filter_email, #filter_employee').val('');
                $('#filter_project, #filter_department, #filter_role, #filter_status').val('').trigger(
                    'change');
                table.draw();
            });
        });

        // Initialize DataTables for Roles Overview
        $('#roles-overview-table').DataTable({
            responsive: true,
            autoWidth: false,
            lengthChange: true,
            lengthMenu: [
                [5, 10, 25, 50, -1],
                ['5', '10', '25', '50', 'Show all']
            ],
            pageLength: 5,
            dom: 'frtpi',
            columnDefs: [{
                targets: [3],
                orderable: false
            }]
        });

        // Initialize DataTables for Permissions Overview
        $('#permissions-overview-table').DataTable({
            responsive: true,
            autoWidth: false,
            lengthChange: true,
            lengthMenu: [
                [5, 10, 25, 50, -1],
                ['5', '10', '25', '50', 'Show all']
            ],
            pageLength: 5,
            dom: 'frtpi'
        });

        // Function to handle edit user
        function editUser(id) {
            $('#modal-edit-' + id).modal('show');
            setTimeout(function() {
                $('.select2-edit-' + id).select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    dropdownParent: $('#modal-edit-' + id)
                });
            }, 100);
        }
    </script>
@endsection
