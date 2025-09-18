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
                        <li class="breadcrumb-item active">Employee Bonds</li>
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
                                <div class="d-flex flex-row justify-content-between align-items-start mb-1">
                                    <h3 class="card-title mb-0">
                                        <strong>{{ $subtitle }}</strong>
                                    </h3>
                                    <div class="d-flex flex-column flex-md-row ms-auto gap-2">
                                        @can('employee-bonds.create')
                                            <a href="{{ route('employee-bonds.create') }}"
                                                class="btn btn-success mb-md-0 ml-1 mb-1">
                                                <i class="fas fa-plus"></i> Add Bond
                                            </a>
                                        @endcan
                                        @can('bond-violations.create')
                                            <a href="{{ route('bond-violations.create') }}"
                                                class="btn btn-warning mb-md-0 ml-1 mb-1">
                                                <i class="fas fa-exclamation-triangle"></i> Add Violation
                                            </a>
                                        @endcan
                                    </div>
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
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select class="form-control select2bs4" id="status-filter">
                                                            <option value="">All Status</option>
                                                            <option value="active">Active</option>
                                                            <option value="completed">Completed</option>
                                                            <option value="violated">Violated</option>
                                                            <option value="cancelled">Cancelled</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Employee</label>
                                                        <select class="form-control select2bs4" id="employee-filter">
                                                            <option value="">All Employees</option>
                                                            @foreach ($employees as $employee)
                                                                <option value="{{ $employee->id }}">{{ $employee->nik }} -
                                                                    {{ $employee->fullname }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Bond Name</label>
                                                        <input type="text" class="form-control" id="bond_name"
                                                            placeholder="Search by bond name">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Bond Number</label>
                                                        <input type="text" class="form-control" id="bond_number"
                                                            placeholder="Search by bond number">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Date From</label>
                                                        <input type="date" class="form-control" id="date_from">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Date To</label>
                                                        <input type="date" class="form-control" id="date_to">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <button type="button" id="btn-reset"
                                                            class="btn btn-danger btn-block">
                                                            <i class="fas fa-times"></i> Reset Filter
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="bonds-table" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center align-middle" width="5%">No</th>
                                                <th class="align-middle">Employee</th>
                                                <th class="align-middle">Bond Name</th>
                                                <th class="text-center align-middle">Bond Number</th>
                                                <th class="text-center align-middle">Start Date</th>
                                                <th class="text-center align-middle">End Date</th>
                                                <th class="text-center align-middle">Duration</th>
                                                <th class="text-center align-middle">Investment Value</th>
                                                <th class="text-center align-middle">Status</th>
                                                <th class="text-center align-middle">Remaining Days</th>
                                                <th class="text-center align-middle" width="11%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div><!-- /.card-body -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this employee bond? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('scripts')
    <!-- Bootstrap 4 -->
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
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
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        function deleteBond(bondId) {
            document.getElementById('deleteForm').action = `{{ route('employee-bonds.destroy', '') }}/${bondId}`;
            $('#deleteModal').modal('show');
        }

        $(function() {
            // Initialize Select2 Elements with Bootstrap4 theme
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option',
                allowClear: true
            });

            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            });

            // Initialize DataTable
            var table = $("#bonds-table").DataTable({
                responsive: true,
                autoWidth: true,
                dom: 'rtip',
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('employee-bonds.data') }}",
                    data: function(d) {
                        d.status = $('#status-filter').val(),
                            d.employee_id = $('#employee-filter').val(),
                            d.bond_name = $('#bond_name').val(),
                            d.bond_number = $('#bond_number').val(),
                            d.date_from = $('#date_from').val(),
                            d.date_to = $('#date_to').val(),
                            d.search = $("input[type=search][aria-controls=bonds-table]").val()
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
                        data: 'employee',
                        name: 'employee',
                        orderable: false
                    },
                    {
                        data: 'bond_name',
                        name: 'bond_name',
                        orderable: false
                    },
                    {
                        data: 'bond_number',
                        name: 'bond_number',
                        className: 'text-center'
                    },
                    {
                        data: 'start_date',
                        name: 'start_date',
                        className: 'text-center'
                    },
                    {
                        data: 'end_date',
                        name: 'end_date',
                        className: 'text-center'
                    },
                    {
                        data: 'duration',
                        name: 'duration',
                        className: 'text-center'
                    },
                    {
                        data: 'investment_value',
                        name: 'investment_value',
                        className: 'text-center'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'remaining_days',
                        name: 'remaining_days',
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ]
            });

            // Handle filter changes
            $('#status-filter, #employee-filter').change(function() {
                table.draw();
            });

            $('#bond_name, #bond_number, #date_from, #date_to').keyup(function() {
                table.draw();
            });

            // Handle reset button
            $('#btn-reset').click(function() {
                $('#status-filter, #employee-filter').val('').trigger('change');
                $('#bond_name, #bond_number, #date_from, #date_to').val('');
                table.draw();
            });
        });
    </script>
@endsection
