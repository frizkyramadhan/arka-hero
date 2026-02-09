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
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.personal') }}">My Dashboard</a></li>
                        <li class="breadcrumb-item active">My LOT Request</li>
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
                                <h3 class="card-title">{{ $title }}</h3>
                                <div class="card-tools">
                                    @can('personal.official-travel.create-own')
                                        <a href="{{ route('officialtravels.my-travels.create') }}" class="btn btn-warning">
                                            <i class="fas fa-plus"></i> New Request
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
                                                        <label>Travel Number</label>
                                                        <input type="text" class="form-control" id="travel_number"
                                                            name="travel_number" placeholder="Search travel number">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select class="form-control select2bs4" id="status"
                                                            name="status">
                                                            <option value="">- All -</option>
                                                            <option value="draft">Draft</option>
                                                            <option value="pending_hr">Menunggu Konfirmasi HR</option>
                                                            <option value="submitted">Submitted</option>
                                                            <option value="approved">Approved</option>
                                                            <option value="rejected">Rejected</option>
                                                            <option value="closed">Closed</option>
                                                            <option value="cancelled">Cancelled</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Role</label>
                                                        <select class="form-control select2bs4" id="role"
                                                            name="role">
                                                            <option value="">- All -</option>
                                                            <option value="main">Main Traveler</option>
                                                            <option value="follower">Follower</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Destination</label>
                                                        <input type="text" class="form-control" id="destination"
                                                            name="destination" placeholder="Search destination">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Traveler</label>
                                                        <input type="text" class="form-control" id="traveler"
                                                            name="traveler" placeholder="Search traveler name">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Start Date</label>
                                                        <input type="date" class="form-control" id="start_date"
                                                            name="start_date">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>End Date</label>
                                                        <input type="date" class="form-control" id="end_date"
                                                            name="end_date">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
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
                                    <table id="official-travels-table" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center" width="5%">No</th>
                                                <th>Travel Number</th>
                                                <th>Date</th>
                                                <th>Traveler</th>
                                                <th>Project</th>
                                                <th>Destination</th>
                                                <th>Status</th>
                                                <th>Creator</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
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
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            var table = $('#official-travels-table').DataTable({
                responsive: true,
                autoWidth: true,
                dom: 'rtip',
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('officialtravels.my-travels.data') }}',
                    data: function(d) {
                        d.travel_number = $('#travel_number').val();
                        d.status = $('#status').val();
                        d.role = $('#role').val();
                        d.destination = $('#destination').val();
                        d.traveler = $('#traveler').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.search = $("input[type=search][aria-controls=official-travels-table]").val();
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
                        data: 'travel_number',
                        name: 'travel_number',
                        orderable: false
                    },
                    {
                        data: 'travel_date',
                        name: 'travel_date',
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'traveler_name',
                        name: 'traveler_name',
                        orderable: false
                    },
                    {
                        data: 'project',
                        name: 'project',
                        orderable: false
                    },
                    {
                        data: 'destination',
                        name: 'destination',
                        orderable: false
                    },
                    {
                        data: 'status_badge',
                        name: 'status_badge',
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'created_by',
                        name: 'created_by',
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
                ],
                order: [
                    [2, 'desc']
                ],
                pageLength: 25
            });

            // Handle filter changes
            $('#travel_number, #destination, #traveler').on('keyup', function() {
                table.draw();
            });

            $('#status, #role').change(function() {
                table.draw();
            });

            $('#start_date, #end_date').change(function() {
                table.draw();
            });

            // Handle reset button
            $('#btn-reset').click(function() {
                $('#travel_number, #destination, #traveler').val('');
                $('#status, #role').val('').trigger('change');
                $('#start_date, #end_date').val('');
                table.draw();
            });
        });
    </script>
@endsection
