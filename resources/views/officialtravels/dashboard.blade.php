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
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">{{ $subtitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Quick Access Cards -->
            <div class="row">
                <!-- Pending Recommendations -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $pendingRecommendations }}</h3>
                            <p>Pending Recommendations</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-thumbs-up"></i>
                        </div>
                        <a href="#" class="small-box-footer" data-toggle="modal" data-target="#modal-recommendations">
                            Take action <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $pendingApprovals }}</h3>
                            <p>Pending Approvals</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <a href="#" class="small-box-footer" data-toggle="modal" data-target="#modal-approvals">
                            Take action <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Pending Arrivals -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $pendingArrivals }}</h3>
                            <p>Pending Arrival Stamps</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-plane-arrival"></i>
                        </div>
                        <a href="#" class="small-box-footer" data-toggle="modal" data-target="#modal-arrivals">
                            View list <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Pending Departures -->
                <div class="col-lg-3 col-6">
                    <div class="small-box" style="background-color: #8e44ad; color: white;">
                        <div class="inner">
                            <h3>{{ $pendingDepartures }}</h3>
                            <p>Pending Departure Stamps</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-plane-departure"></i>
                        </div>
                        <a href="#" class="small-box-footer" data-toggle="modal" data-target="#modal-departures">
                            View list <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Travel & Summary -->
            <div class="row">
                <!-- Summary Stats -->
                <div class="col-md-3">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Official Travel Summary
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center border-bottom mb-3">
                                <p class="text-warning text-xl">
                                    <i class="fas fa-thumbs-up"></i>
                                </p>
                                <p class="d-flex flex-column">
                                    <span class="text-muted">Pending Recommendations</span>
                                    <span class="font-weight-bold text-right">{{ $pendingRecommendations }}</span>
                                </p>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom mb-3">
                                <p class="text-success text-xl">
                                    <i class="fas fa-check-circle"></i>
                                </p>
                                <p class="d-flex flex-column">
                                    <span class="text-muted">Pending Approvals</span>
                                    <span class="font-weight-bold text-right">{{ $pendingApprovals }}</span>
                                </p>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom mb-3">
                                <p class="text-info text-xl">
                                    <i class="fas fa-clipboard-check"></i>
                                </p>
                                <p class="d-flex flex-column">
                                    <span class="text-muted">Pending Stamps</span>
                                    <span
                                        class="font-weight-bold text-right">{{ $pendingArrivals + $pendingDepartures }}</span>
                                </p>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="text-success text-xl">
                                    <i class="fas fa-plane"></i>
                                </p>
                                <p class="d-flex flex-column">
                                    <span class="text-muted">Open Travels</span>
                                    <span class="font-weight-bold text-right">{{ $openTravel }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Travels -->
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header border-transparent">
                            <h3 class="card-title">Open Official Travels</h3>
                            <a href="{{ route('officialtravels.create') }}" class="btn btn-sm btn-primary float-right">
                                <i class="fas fa-plus"></i> Create New Official Travel
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table m-0">
                                    <thead>
                                        <tr>
                                            <th class="align-middle">Travel Number</th>
                                            <th class="align-middle">Traveler</th>
                                            <th class="align-middle">Destination</th>
                                            <th class="align-middle">Arrival</th>
                                            <th class="align-middle">Departure</th>
                                            <th class="align-middle">Status</th>
                                            <th class="align-middle">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($openTravels as $travel)
                                            <tr>
                                                <td>{{ $travel->official_travel_number }}</td>
                                                <td>
                                                    {{ $travel->traveler->employee->fullname ?? 'N/A' }}</td>
                                                <td>{{ $travel->destination }}</td>
                                                <td>{{ $travel->arrival_at_destination }}</td>
                                                <td>{{ $travel->departure_from_destination }}</td>
                                                <td class="text-center">
                                                    @if ($travel->official_travel_status == 'draft')
                                                        <span class="badge badge-secondary">Draft</span>
                                                    @elseif($travel->official_travel_status == 'open')
                                                        <span class="badge badge-primary">Open</span>
                                                    @elseif($travel->official_travel_status == 'closed')
                                                        <span class="badge badge-success">Closed</span>
                                                    @elseif($travel->official_travel_status == 'canceled')
                                                        <span class="badge badge-danger">Canceled</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('officialtravels.show', $travel->id) }}"
                                                        class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No recent official travels found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer clearfix">
                            <a href="{{ route('officialtravels.index') }}" class="btn btn-sm btn-secondary float-right">
                                View All Official Travels
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modals for Data Tables -->

    <!-- Pending Recommendations Modal -->
    <div class="modal fade" id="modal-recommendations">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title">Pending Recommendations</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="pending-recommendations-table" class="table table-sm table-bordered table-striped"
                            style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle">No</th>
                                    <th class="align-middle">Travel Number</th>
                                    <th class="align-middle">Date</th>
                                    <th class="align-middle">Traveler</th>
                                    <th class="align-middle">Destination</th>
                                    <th class="text-center align-middle">Action</th>
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

    <!-- Pending Approvals Modal -->
    <div class="modal fade" id="modal-approvals">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h4 class="modal-title">Pending Approvals</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="pending-approvals-table" class="table table-sm table-bordered table-striped"
                            style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle">No</th>
                                    <th class="align-middle">Travel Number</th>
                                    <th class="align-middle">Date</th>
                                    <th class="align-middle">Traveler</th>
                                    <th class="align-middle">Destination</th>
                                    <th class="text-center align-middle">Action</th>
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

    <!-- Pending Arrivals Modal -->
    <div class="modal fade" id="modal-arrivals">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h4 class="modal-title">Pending Arrival Stamps</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="pending-arrivals-table" class="table table-sm table-bordered table-striped"
                            style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Travel Number</th>
                                    <th>Date</th>
                                    <th>Traveler</th>
                                    <th>Destination</th>
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
    </div>

    <!-- Pending Departures Modal -->
    <div class="modal fade" id="modal-departures">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #8e44ad; color: white;">
                    <h4 class="modal-title">Pending Departure Stamps</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="pending-departures-table" class="table table-sm table-bordered table-striped"
                            style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Travel Number</th>
                                    <th>Date</th>
                                    <th>Traveler</th>
                                    <th>Destination</th>
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
    </div>
@endsection

@section('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <style>
        .small-box {
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .small-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .card {
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-purple {
            background-color: #8e44ad;
            color: white;
        }

        .btn-purple:hover {
            background-color: #7d3c98;
            color: white;
        }
    </style>
@endsection

@section('scripts')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>

    <script>
        $(function() {
            // Pending Recommendations Table
            var recTable = $("#pending-recommendations-table").DataTable({
                responsive: true,
                autoWidth: true,
                processing: true,
                serverSide: true,
                ajax: "{{ route('officialtravel.pending-recommendations') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'official_travel_number',
                        name: 'official_travel_number'
                    },
                    {
                        data: 'official_travel_date',
                        name: 'official_travel_date'
                    },
                    {
                        data: 'traveler',
                        name: 'traveler'
                    },
                    {
                        data: 'destination',
                        name: 'destination'
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

            // Pending Approvals Table
            var appTable = $("#pending-approvals-table").DataTable({
                responsive: true,
                autoWidth: true,
                processing: true,
                serverSide: true,
                ajax: "{{ route('officialtravel.pending-approvals') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'official_travel_number',
                        name: 'official_travel_number'
                    },
                    {
                        data: 'official_travel_date',
                        name: 'official_travel_date'
                    },
                    {
                        data: 'traveler',
                        name: 'traveler'
                    },
                    {
                        data: 'destination',
                        name: 'destination'
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

            // Pending Arrivals Table
            var arrTable = $("#pending-arrivals-table").DataTable({
                responsive: true,
                autoWidth: true,
                processing: true,
                serverSide: true,
                ajax: "{{ route('officialtravel.pending-arrivals') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'official_travel_number',
                        name: 'official_travel_number'
                    },
                    {
                        data: 'official_travel_date',
                        name: 'official_travel_date'
                    },
                    {
                        data: 'traveler',
                        name: 'traveler'
                    },
                    {
                        data: 'destination',
                        name: 'destination'
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

            // Pending Departures Table
            var depTable = $("#pending-departures-table").DataTable({
                responsive: true,
                autoWidth: true,
                processing: true,
                serverSide: true,
                ajax: "{{ route('officialtravel.pending-departures') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'official_travel_number',
                        name: 'official_travel_number'
                    },
                    {
                        data: 'official_travel_date',
                        name: 'official_travel_date'
                    },
                    {
                        data: 'traveler',
                        name: 'traveler'
                    },
                    {
                        data: 'destination',
                        name: 'destination'
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

            // Reload tables when modals are opened
            $('#modal-recommendations').on('shown.bs.modal', function() {
                recTable.ajax.reload();
            });

            $('#modal-approvals').on('shown.bs.modal', function() {
                appTable.ajax.reload();
            });

            $('#modal-arrivals').on('shown.bs.modal', function() {
                arrTable.ajax.reload();
            });

            $('#modal-departures').on('shown.bs.modal', function() {
                depTable.ajax.reload();
            });
        });
    </script>
@endsection
