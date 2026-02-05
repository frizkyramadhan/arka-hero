@extends('layouts.main')

@section('title', 'Flight Requests')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Flight Requests</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Flight Requests</li>
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
                                <h3 class="card-title"><strong>Flight Requests</strong></h3>
                                <div class="card-tools">
                                    <a href="{{ route('flight-requests.create') }}" class="btn btn-primary">
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
                                                        <label>Status</label>
                                                        <select class="form-control select2bs4" id="status" name="status">
                                                            <option value="">- All -</option>
                                                            <option value="draft">Draft</option>
                                                            <option value="submitted">Submitted</option>
                                                            <option value="approved">Approved</option>
                                                            <option value="issued">Issued</option>
                                                            <option value="completed">Completed</option>
                                                            <option value="rejected">Rejected</option>
                                                            <option value="cancelled">Cancelled</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Request Type</label>
                                                        <select class="form-control select2bs4" id="request_type" name="request_type">
                                                            <option value="">- All -</option>
                                                            <option value="standalone">Standalone</option>
                                                            <option value="leave_based">Leave Based</option>
                                                            <option value="travel_based">Travel Based</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Form Number</label>
                                                        <input type="text" class="form-control" id="form_number" name="form_number" placeholder="Search...">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Date From</label>
                                                        <input type="date" class="form-control" id="date_from" name="date_from">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Date To</label>
                                                        <input type="date" class="form-control" id="date_to" name="date_to">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <button type="button" class="btn btn-secondary w-100" id="btn-reset" style="margin-bottom: 6px;">
                                                            <i class="fas fa-times"></i> Reset
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="flight-requests-table" class="table table-bordered table-striped" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="align-middle text-center" width="5%">No</th>
                                                <th class="align-middle">Form Number</th>
                                                <th class="align-middle">Employee Name</th>
                                                <th class="align-middle">NIK</th>
                                                <th class="align-middle">Request Type</th>
                                                <th class="align-middle">Purpose</th>
                                                <th class="align-middle text-center">Status</th>
                                                <th class="align-middle text-center">Requested At</th>
                                                <th class="align-middle text-center" width="12%">Actions</th>
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
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
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

            var table = $("#flight-requests-table").DataTable({
                responsive: true,
                autoWidth: true,
                dom: 'rtip',
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('flight-requests.data') }}",
                    data: function(d) {
                        d.status = $('#status').val(),
                        d.request_type = $('#request_type').val(),
                        d.form_number = $('#form_number').val(),
                        d.date_from = $('#date_from').val(),
                        d.date_to = $('#date_to').val()
                    }
                },
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'form_number',
                        name: 'form_number'
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name',
                        orderable: false
                    },
                    {
                        data: 'nik',
                        name: 'nik',
                        orderable: false
                    },
                    {
                        data: 'request_type',
                        name: 'request_type',
                        orderable: false
                    },
                    {
                        data: 'purpose_of_travel',
                        name: 'purpose_of_travel',
                        orderable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
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
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [[7, 'desc']]
            });

            // Handle filter changes
            $('#status, #request_type').change(function() {
                table.draw();
            });

            $('#form_number, #date_from, #date_to').change(function() {
                table.draw();
            });

            $('#form_number').keyup(function() {
                table.draw();
            });

            // Handle reset button
            $('#btn-reset').click(function() {
                $('#status, #request_type').val('').trigger('change');
                $('#form_number, #date_from, #date_to').val('');
                table.draw();
            });
        });
    </script>
@endsection
