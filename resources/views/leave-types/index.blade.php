@extends('layouts.main')

@section('title', 'Leave Types')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Leave Types</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Leave Types</li>
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
                                <h3 class="card-title"><strong>Leave Types</strong></h3>
                                <div class="card-tools">
                                    <a href="{{ route('leave.types.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add
                                    </a>
                                </div>
                            </div>
                            <!-- /.card-header -->
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
                                                        <label>Category</label>
                                                        <select class="form-control select2bs4" id="category"
                                                            name="category">
                                                            <option value="">- All -</option>
                                                            <option value="annual">Annual</option>
                                                            <option value="paid">Paid</option>
                                                            <option value="unpaid">Unpaid</option>
                                                            <option value="lsl">Long Service Leave</option>
                                                            <option value="periodic">Periodic Leave</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select class="form-control select2bs4" id="status"
                                                            name="status">
                                                            <option value="">- All -</option>
                                                            <option value="active">Active</option>
                                                            <option value="inactive">Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Name</label>
                                                        <input type="text" class="form-control" id="name"
                                                            name="name" placeholder="Search by name">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Code</label>
                                                        <input type="text" class="form-control" id="code"
                                                            name="code" placeholder="Search by code">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <button type="button" class="btn btn-secondary w-100"
                                                            id="btn-reset" style="margin-bottom: 6px;">
                                                            <i class="fas fa-times"></i> Reset
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="leave-types-table" class="table table-bordered table-striped" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="align-middle">No</th>
                                                <th class="align-middle">Name</th>
                                                <th class="align-middle">Code</th>
                                                <th class="align-middle">Category</th>
                                                <th class="align-middle">Default Days</th>
                                                <th class="align-middle">Eligible After</th>
                                                <th class="align-middle">Deposit Days</th>
                                                <th class="align-middle">Carry Over</th>
                                                <th class="align-middle">Status</th>
                                                <th class="align-middle" width="12%">Actions</th>
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

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            var table = $("#leave-types-table").DataTable({
                responsive: true,
                autoWidth: true,
                dom: 'rtip',
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('leave.types.data') }}",
                    data: function(d) {
                        d.category = $('#category').val(),
                            d.status = $('#status').val(),
                            d.name = $('#name').val(),
                            d.code = $('#code').val(),
                            d.search = $("input[type=search][aria-controls=leave-types-table]").val()
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
                        data: 'name',
                        name: 'name',
                        orderable: false
                    },
                    {
                        data: 'code',
                        name: 'code',
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'category',
                        name: 'category',
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'default_days',
                        name: 'default_days',
                        orderable: false,
                        className: 'text-right'
                    },
                    {
                        data: 'eligible_after',
                        name: 'eligible_after',
                        orderable: false,
                        className: 'text-right'
                    },
                    {
                        data: 'deposit_days',
                        name: 'deposit_days',
                        orderable: false,
                        className: 'text-right'
                    },
                    {
                        data: 'carry_over',
                        name: 'carry_over',
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'status',
                        name: 'status',
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
            $('#category, #status').change(function() {
                table.draw();
            });

            $('#name, #code').keyup(function() {
                table.draw();
            });

            // Handle reset button
            $('#btn-reset').click(function() {
                $('#category, #status').val('').trigger('change');
                $('#name, #code').val('');
                table.draw();
            });

            // Handle filter button
            $('#btn-filter').click(function() {
                table.draw();
            });

        });
    </script>
@endsection
