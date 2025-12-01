@extends('layouts.main')

@section('title', 'My Leave Request')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.personal') }}">My Dashboard</a></li>
                        <li class="breadcrumb-item active">My Leave Request</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Leave Requests Section -->
            <div class="row">
                <div class="col-12">
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><strong>{{ auth()->user()->name }}'s Leave Requests</strong></h3>
                                <div class="card-tools">
                                    @can('personal.leave.view-entitlements')
                                        <a href="{{ route('leave.my-entitlements') }}" class="btn btn-info mr-2">
                                            <i class="fas fa-calendar-week"></i> My Leave Entitlement
                                        </a>
                                    @endcan
                                    @can('personal.leave.create-own')
                                        <a href="{{ route('leave.my-requests.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add
                                        </a>
                                    @endcan
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
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select class="form-control select2bs4" id="status"
                                                            name="status">
                                                            <option value="">- All -</option>
                                                            <option value="draft">Draft</option>
                                                            <option value="pending">Pending</option>
                                                            <option value="approved">Approved</option>
                                                            <option value="rejected">Rejected</option>
                                                            <option value="cancelled">Cancelled</option>
                                                            <option value="closed">Closed</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Leave Type</label>
                                                        <select class="form-control select2bs4" id="leave_type_id"
                                                            name="leave_type_id">
                                                            <option value="">- All -</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Start Date</label>
                                                        <input type="date" class="form-control" id="start_date"
                                                            name="start_date">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>End Date</label>
                                                        <input type="date" class="form-control" id="end_date"
                                                            name="end_date">
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
                                    <table id="leave-requests-table" class="table table-bordered table-striped"
                                        width="100%">
                                        <thead>
                                            <tr>
                                                <th class="align-middle">No</th>
                                                <th class="align-middle">Leave Type</th>
                                                <th class="align-middle">Start Date</th>
                                                <th class="align-middle">End Date</th>
                                                <th class="align-middle">Total Days</th>
                                                <th class="align-middle">Status</th>
                                                <th class="align-middle">Requested At</th>
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
    <style>
    </style>
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

            // Load leave types for filter
            $.get('{{ route('api.leave.types') }}', function(data) {
                var options = '<option value="">- All -</option>';
                $.each(data, function(index, leaveType) {
                    options += '<option value="' + leaveType.id + '">' + leaveType.name +
                        '</option>';
                });
                $('#leave_type_id').html(options);
            });

            var table = $("#leave-requests-table").DataTable({
                responsive: true,
                autoWidth: true,
                dom: 'rtip',
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('leave.my-requests.data') }}",
                    data: function(d) {
                        d.status = $('#status').val(),
                            d.leave_type_id = $('#leave_type_id').val(),
                            d.start_date = $('#start_date').val(),
                            d.end_date = $('#end_date').val(),
                            d.search = $("input[type=search][aria-controls=leave-requests-table]").val()
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
                        data: 'leave_type',
                        name: 'leave_type',
                        orderable: false
                    },
                    {
                        data: 'start_date',
                        name: 'start_date',
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'end_date',
                        name: 'end_date',
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'total_days',
                        name: 'total_days',
                        orderable: false,
                        className: 'text-right'
                    },
                    {
                        data: 'status_badge',
                        name: 'status_badge',
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'requested_at',
                        name: 'requested_at',
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
            $('#status, #leave_type_id').change(function() {
                table.draw();
            });

            $('#start_date, #end_date').change(function() {
                table.draw();
            });

            // Handle reset button
            $('#btn-reset').click(function() {
                $('#status, #leave_type_id').val('').trigger('change');
                $('#start_date, #end_date').val('');
                table.draw();
            });

        });
    </script>
@endsection
