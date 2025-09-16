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

            <div class="row mb-4">
                <!-- Total Travels -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="info-box bg-gradient-primary">
                        <span class="info-box-icon"><i class="fas fa-plane"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Travels</span>
                            <span class="info-box-number">{{ $totalTravels ?? 0 }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">All time official travels</span>
                        </div>
                    </div>
                </div>

                <!-- Active Travels -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="info-box bg-gradient-success">
                        <span class="info-box-icon"><i class="fas fa-plane-departure"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Active Travels</span>
                            <span class="info-box-number">{{ $activeTravels ?? 0 }}</span>
                            <div class="progress">
                                <div class="progress-bar"
                                    style="width: {{ $activeTravels > 0 ? ($activeTravels / ($totalTravels ?? 1)) * 100 : 0 }}%">
                                </div>
                            </div>
                            <span class="progress-description">Currently on travel</span>
                        </div>
                    </div>
                </div>

                <!-- Pending Arrivals -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="info-box bg-gradient-warning">
                        <span class="info-box-icon"><i class="fas fa-plane-arrival"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pending Arrivals</span>
                            <span class="info-box-number">{{ $pendingArrivals }}</span>
                            <div class="progress">
                                <div class="progress-bar"
                                    style="width: {{ $pendingArrivals > 0 ? ($pendingArrivals / ($totalTravels ?? 1)) * 100 : 0 }}%">
                                </div>
                            </div>
                            <span class="progress-description">Waiting for arrival stamp</span>
                        </div>
                    </div>
                </div>

                <!-- This Month Travels -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="info-box bg-gradient-info">
                        <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">This Month</span>
                            <span class="info-box-number">{{ $thisMonthTravels ?? 0 }}</span>
                            <div class="progress">
                                <div class="progress-bar"
                                    style="width: {{ $thisMonthTravels > 0 ? ($thisMonthTravels / ($totalTravels ?? 1)) * 100 : 0 }}%">
                                </div>
                            </div>
                            <span class="progress-description">Travels in {{ date('M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Overview & Quick Actions -->
            <div class="row mb-4">
                <!-- Status Overview -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar mr-2"></i>
                                Travel Status Overview
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('officialtravels.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> New Official Travel
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center mb-3">
                                    <div class="status-card draft">
                                        <div class="status-icon"><i class="fas fa-edit"></i></div>
                                        <div class="status-number">{{ $draftTravels ?? 0 }}</div>
                                        <div class="status-label">Draft</div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center mb-3">
                                    <div class="status-card submitted">
                                        <div class="status-icon"><i class="fas fa-paper-plane"></i></div>
                                        <div class="status-number">{{ $submittedTravels ?? 0 }}</div>
                                        <div class="status-label">Submitted</div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center mb-3">
                                    <div class="status-card approved">
                                        <div class="status-icon"><i class="fas fa-check-circle"></i></div>
                                        <div class="status-number">{{ $approvedTravels ?? 0 }}</div>
                                        <div class="status-label">Approved</div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center mb-3">
                                    <div class="status-card rejected">
                                        <div class="status-icon"><i class="fas fa-times-circle"></i></div>
                                        <div class="status-number">{{ $rejectedTravels ?? 0 }}</div>
                                        <div class="status-label">Rejected</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-bolt mr-2"></i>
                                Quick Actions
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="quick-actions">
                                <a href="#" class="btn btn-info btn-block mb-2" data-toggle="modal"
                                    data-target="#modal-arrivals">
                                    <i class="fas fa-plane-arrival mr-2"></i>
                                    Pending Arrivals ({{ $pendingArrivals }})
                                </a>
                                <a href="#" class="btn btn-purple btn-block mb-2" data-toggle="modal"
                                    data-target="#modal-departures">
                                    <i class="fas fa-plane-departure mr-2"></i>
                                    Pending Departures ({{ $pendingDepartures }})
                                </a>
                                <a href="{{ route('officialtravels.index') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-list mr-2"></i>
                                    View All Travels
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Travels & Analytics -->
            <div class="row">
                <!-- Recent Travels Table -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-2"></i>
                                Open Official Travels
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover m-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Travel Number</th>
                                            <th>Traveler</th>
                                            <th>Destination</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($openTravels as $travel)
                                            <tr>
                                                <td><strong>{{ $travel->official_travel_number }}</strong></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm mr-2"><i
                                                                class="fas fa-user-circle fa-2x text-primary"></i>
                                                        </div>
                                                        <div>
                                                            <div class="font-weight-bold">
                                                                {{ $travel->traveler->employee->fullname ?? 'N/A' }}
                                                            </div>
                                                            <small
                                                                class="text-muted">{{ $travel->traveler->position->position_name ?? 'N/A' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="font-weight-bold">{{ $travel->destination }}
                                                        </div>
                                                        <small class="text-muted">{{ $travel->duration }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="font-weight-bold">
                                                            {{ date('d M Y', strtotime($travel->official_travel_date)) }}
                                                        </div>
                                                        <small
                                                            class="text-muted">{{ date('H:i', strtotime($travel->official_travel_date)) }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $statusMap = [
                                                            'draft' => [
                                                                'label' => 'Draft',
                                                                'class' => 'badge badge-secondary',
                                                            ],
                                                            'submitted' => [
                                                                'label' => 'Submitted',
                                                                'class' => 'badge badge-info',
                                                            ],
                                                            'approved' => [
                                                                'label' => 'Open',
                                                                'class' => 'badge badge-success',
                                                            ],
                                                            'rejected' => [
                                                                'label' => 'Rejected',
                                                                'class' => 'badge badge-danger',
                                                            ],
                                                            'closed' => [
                                                                'label' => 'Closed',
                                                                'class' => 'badge badge-primary',
                                                            ],
                                                            'cancelled' => [
                                                                'label' => 'Cancelled',
                                                                'class' => 'badge badge-warning',
                                                            ],
                                                        ];
                                                        $status = $travel->status;
                                                        $pill = $statusMap[$status] ?? [
                                                            'label' => ucfirst($status),
                                                            'class' => 'badge badge-secondary',
                                                        ];
                                                    @endphp
                                                    <span class="{{ $pill['class'] }}">{{ $pill['label'] }}</span>
                                                </td>
                                                <td class="text-left">
                                                    <div class="btn-group">
                                                        <a href="{{ route('officialtravels.show', $travel->id) }}"
                                                            class="btn btn-sm btn-outline-info" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        @if ($travel->status === 'approved')
                                                            @php
                                                                $canRecordArrival = $travel->canRecordArrival();
                                                                $canRecordDeparture = $travel->canRecordDeparture();
                                                                $canClose = $travel->canClose();
                                                            @endphp
                                                            @if ($canRecordArrival)
                                                                <a href="{{ route('officialtravels.showArrivalForm', $travel->id) }}"
                                                                    class="btn btn-sm btn-outline-primary"
                                                                    title="Record Arrival">
                                                                    <i class="fas fa-plane-arrival"></i>
                                                                </a>
                                                            @endif
                                                            @if ($canRecordDeparture)
                                                                <a href="{{ route('officialtravels.showDepartureForm', $travel->id) }}"
                                                                    class="btn btn-sm btn-outline-success"
                                                                    title="Record Departure">
                                                                    <i class="fas fa-plane-departure"></i>
                                                                </a>
                                                            @endif
                                                            @if ($canClose)
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-warning close-travel-btn"
                                                                    data-travel-id="{{ $travel->id }}"
                                                                    data-travel-number="{{ $travel->official_travel_number }}"
                                                                    title="Close Travel">
                                                                    <i class="fas fa-lock"></i>
                                                                </button>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <div class="empty-state">
                                                        <i class="fas fa-plane fa-3x text-muted mb-3"></i>
                                                        <h5>No Official Travels Found</h5>
                                                        <p class="text-muted">Start by creating a new official
                                                            travel request.</p>
                                                        <a href="{{ route('officialtravels.create') }}"
                                                            class="btn btn-primary">
                                                            <i class="fas fa-plus mr-2"></i>Create New Travel
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analytics & Stats -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-2"></i>
                                Analytics
                            </h3>
                        </div>
                        <div class="card-body">
                            <!-- Monthly Trend -->
                            <div class="analytics-item mb-4">
                                <h6 class="text-muted mb-2">Monthly Trend</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h4 mb-0">{{ $thisMonthTravels ?? 0 }}</span>
                                    <span class="badge badge-success"><i class="fas fa-arrow-up"></i>
                                        {{ $monthlyGrowth ?? 0 }}%</span>
                                </div>
                                <small class="text-muted">vs last month</small>
                            </div>

                            <!-- Top Destinations -->
                            <div class="analytics-item mb-4">
                                <h6 class="text-muted mb-2">Top Destinations</h6>
                                @if (isset($topDestinations) && count($topDestinations) > 0)
                                    @foreach ($topDestinations as $destination)
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span>{{ $destination->destination }}</span>
                                            <span class="badge badge-light">{{ $destination->count }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted small">No data available</p>
                                @endif
                            </div>

                            <!-- Department Stats -->
                            <div class="analytics-item">
                                <h6 class="text-muted mb-2">By Department</h6>
                                @if (isset($departmentStats) && count($departmentStats) > 0)
                                    @foreach ($departmentStats as $dept)
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span>{{ $dept->department_name }}</span>
                                            <span class="badge badge-info">{{ $dept->count }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted small">No data available</p>
                                @endif
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
                            <h4 class="modal-title">Pending Arrivals</h4>
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
                                            <th class="text-center align-middle">No</th>
                                            <th class="align-middle">Travel Number</th>
                                            <th class="align-middle">Date</th>
                                            <th class="align-middle">Traveler</th>
                                            <th class="align-middle">Destination</th>
                                            <th class="text-center align-middle">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
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
                        <div class="modal-header text-white" style="background-color: #8e44ad;">
                            <h4 class="modal-title">Pending Departures</h4>
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
                                            <th class="text-center align-middle">No</th>
                                            <th class="align-middle">Travel Number</th>
                                            <th class="align-middle">Date</th>
                                            <th class="align-middle">Traveler</th>
                                            <th class="align-middle">Destination</th>
                                            <th class="text-center align-middle">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Close Travel Modal -->
            <div class="modal fade custom-modal" id="closeTravelModal" tabindex="-1" role="dialog"
                aria-labelledby="closeTravelModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="closeTravelModalLabel">Close Travel Request</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="close-icon">
                                <i class="fas fa-lock text-warning"></i>
                            </div>
                            <p class="close-message">Are you sure you want to close this official travel?</p>
                            <p class="close-warning">This action cannot be undone and no further changes will be allowed.
                            </p>
                            <div class="travel-info">
                                <strong>Travel Number:</strong> <span id="modal-travel-number"></span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-cancel" data-dismiss="modal">Cancel</button>
                            <form id="close-travel-form" action="" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-confirm-close">Yes, Close Travel</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <style>
        /* Official Travel Dashboard Styles */
        .status-card {
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .status-card.draft {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }

        .status-card.submitted {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }

        .status-card.approved {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            color: white;
        }

        .status-card.rejected {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .status-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            opacity: 0.9;
        }

        .status-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .status-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .quick-actions .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .quick-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-purple {
            background-color: #8e44ad;
            border-color: #8e44ad;
            color: white;
        }

        .btn-purple:hover {
            background-color: #7d3c98;
            border-color: #7d3c98;
            color: white;
        }

        .avatar-sm {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .empty-state {
            padding: 40px 20px;
            text-align: center;
        }

        .empty-state i {
            color: #6c757d;
        }

        .analytics-item {
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .analytics-item:last-child {
            border-bottom: none;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .btn-group .btn {
            margin-right: 5px;
        }

        .btn-group .btn:last-child {
            margin-right: 0;
        }

        .info-box {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .info-box-icon {
            border-radius: 0;
        }

        .progress {
            height: 6px;
            border-radius: 3px;
        }

        .progress-bar {
            border-radius: 3px;
        }

        /* Close Travel Modal Styles */
        .custom-modal .modal-content {
            border-radius: 6px;
            border: none;
        }

        .custom-modal .modal-header {
            background: #f8fafc;
            padding: 15px 20px;
        }

        .custom-modal .modal-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
        }

        .close-icon {
            text-align: center;
            font-size: 48px;
            margin-bottom: 15px;
        }

        .close-message {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 10px;
            text-align: center;
        }

        .close-warning {
            font-size: 13px;
            color: #e74c3c;
            text-align: center;
        }

        .travel-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            margin-top: 15px;
            text-align: center;
        }

        .btn-cancel {
            padding: 8px 16px;
            border-radius: 4px;
            background: #e2e8f0;
            color: #475569;
            font-weight: 500;
            border: none;
        }

        .btn-confirm-close {
            padding: 8px 16px;
            border-radius: 4px;
            background-color: #f1c40f;
            color: #2c3e50;
            font-weight: 500;
            border: none;
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
            // Close Travel Modal Handler
            $('.close-travel-btn').on('click', function() {
                var travelId = $(this).data('travel-id');
                var travelNumber = $(this).data('travel-number');

                // Set travel number in modal
                $('#modal-travel-number').text(travelNumber);

                // Set form action using Laravel route
                $('#close-travel-form').attr('action', '{{ url('officialtravels') }}/' + travelId +
                    '/close');

                // Show modal
                $('#closeTravelModal').modal('show');
            });

            // Arrivals
            if ($('#pending-arrivals-table').length) {
                $('#pending-arrivals-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('dashboard.pendingArrivals') }}",
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
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
                            className: 'text-center',
                            orderable: false,
                            searchable: false
                        },
                    ],
                    order: [
                        [1, 'asc']
                    ]
                });
            }

            // Departures
            if ($('#pending-departures-table').length) {
                $('#pending-departures-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('dashboard.pendingDepartures') }}",
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
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
                            className: 'text-center',
                            orderable: false,
                            searchable: false
                        },
                    ],
                    order: [
                        [1, 'asc']
                    ]
                });
            }
        });
    </script>
@endsection
