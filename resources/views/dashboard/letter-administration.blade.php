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

            <!-- Letter Summary Cards -->
            <div class="row">
                <!-- Total Letters -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ number_format($totalLetters) }}</h3>
                            <p>Total Letter Numbers</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <a href="{{ route('letter-numbers.index') }}" class="small-box-footer">
                            View all letters <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Reserved Letters -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ number_format($reservedLetters) }}</h3>
                            <p>Reserved Letters</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <a href="{{ route('letter-numbers.index') }}?status=reserved" class="small-box-footer">
                            View reserved <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Used Letters -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ number_format($usedLetters) }}</h3>
                            <p>Used Letters</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <a href="{{ route('letter-numbers.index') }}?status=used" class="small-box-footer">
                            View used <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Usage Efficiency -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $usageEfficiency }}%</h3>
                            <p>Usage Efficiency</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="small-box-footer">
                            <small>Used / Total Letters</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Statistics and Growth -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                Monthly Statistics
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-calendar"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">This Month</span>
                                            <span class="info-box-number">{{ number_format($thisMonthLetters) }}</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-info" style="width: 70%"></div>
                                            </div>
                                            <span class="progress-description">
                                                {{ $monthlyGrowth > 0 ? '+' : '' }}{{ $monthlyGrowth }}% from last month
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-secondary"><i class="fas fa-cogs"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Integrated</span>
                                            <span class="info-box-number">{{ number_format($integratedLetters) }}</span>
                                            <div class="progress">
                                                <div class="progress-bar bg-secondary"
                                                    style="width: {{ $totalLetters > 0 ? ($integratedLetters / $totalLetters) * 100 : 0 }}%">
                                                </div>
                                            </div>
                                            <span class="progress-description">
                                                <small>
                                                    LOT: {{ $officialTravelLetters }} |
                                                    FPTK: {{ $fptkLetters }} |
                                                    PKWT: {{ $pkwtLetters }} |
                                                    Offer: {{ $offeringLetters }}
                                                </small>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Distribution Chart -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Status Distribution
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart" style="height:250px"></canvas>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Categories and Recent Activity -->
            <div class="row">
                <!-- Categories Stats -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-tags mr-1"></i>
                                Letter Categories
                            </h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-sm table-bordered table-striped" id="categoriesTable">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables will populate this -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Letters -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history mr-1"></i>
                                Recent Letter Numbers
                            </h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-sm table-bordered table-striped" id="recentLettersTable">
                                <thead>
                                    <tr>
                                        <th>Letter #</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables will populate this -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yearly Trends and Top Users -->
            <div class="row">
                <!-- Yearly Statistics -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-1"></i>
                                Yearly Trends
                            </h3>
                        </div>
                        <div class="card-body">
                            @if ($yearlyStats->count() > 0)
                                <div class="row">
                                    @foreach ($yearlyStats as $yearStat)
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-gradient-primary">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">{{ $yearStat->year }}</span>
                                                    <span
                                                        class="info-box-number">{{ number_format($yearStat->total) }}</span>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-primary"
                                                            style="width: {{ $yearStat->total > 0 ? ($yearStat->used / $yearStat->total) * 100 : 0 }}%">
                                                        </div>
                                                    </div>
                                                    <span class="progress-description">
                                                        {{ number_format($yearStat->used) }} Used,
                                                        {{ number_format($yearStat->reserved) }} Reserved
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">No yearly data available yet.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Top Users -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users mr-1"></i>
                                Top Contributors
                            </h3>
                        </div>
                        <div class="card-body">
                            @if ($topUsers->count() > 0)
                                @foreach ($topUsers as $user)
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle fa-2x text-muted mr-3"></i>
                                            <div>
                                                <div class="text-dark">{{ $user->user->name ?? 'Unknown User' }}</div>
                                                <small class="text-muted">{{ $user->count }} letters generated</small>
                                            </div>
                                        </div>
                                        <span class="badge badge-primary">{{ $user->count }}</span>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">No user data available yet.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="card bg-gradient-primary">
                        <div class="card-body">
                            <div class="d-flex">
                                <p class="d-flex flex-column">
                                    <span class="text-bold text-lg">Create New</span>
                                    <span>Generate letter number</span>
                                </p>
                                <p class="ml-auto d-flex flex-column text-right">
                                    <span class="text-white">
                                        <i class="fas fa-plus fa-2x"></i>
                                    </span>
                                </p>
                            </div>
                            <div class="d-flex flex-row justify-content-end">
                                <a href="{{ route('letter-numbers.create') }}" class="btn btn-sm btn-light">
                                    Create Letter Number
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="card bg-gradient-success">
                        <div class="card-body">
                            <div class="d-flex">
                                <p class="d-flex flex-column">
                                    <span class="text-bold text-lg">Import</span>
                                    <span>Bulk import letters</span>
                                </p>
                                <p class="ml-auto d-flex flex-column text-right">
                                    <span class="text-white">
                                        <i class="fas fa-upload fa-2x"></i>
                                    </span>
                                </p>
                            </div>
                            <div class="d-flex flex-row justify-content-end">
                                <button class="btn btn-sm btn-light" data-toggle="modal" data-target="#importModal">
                                    Import Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="card bg-gradient-warning">
                        <div class="card-body">
                            <div class="d-flex">
                                <p class="d-flex flex-column">
                                    <span class="text-bold text-lg">Export</span>
                                    <span>Download report</span>
                                </p>
                                <p class="ml-auto d-flex flex-column text-right">
                                    <span class="text-white">
                                        <i class="fas fa-download fa-2x"></i>
                                    </span>
                                </p>
                            </div>
                            <div class="d-flex flex-row justify-content-end">
                                <a href="{{ route('letter-numbers.export') }}" class="btn btn-sm btn-light">
                                    Export Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="card bg-gradient-info">
                        <div class="card-body">
                            <div class="d-flex">
                                <p class="d-flex flex-column">
                                    <span class="text-bold text-lg">Manage</span>
                                    <span>Letter categories</span>
                                </p>
                                <p class="ml-auto d-flex flex-column text-right">
                                    <span class="text-white">
                                        <i class="fas fa-cogs fa-2x"></i>
                                    </span>
                                </p>
                            </div>
                            <div class="d-flex flex-row justify-content-end">
                                <a href="{{ route('letter-categories.index') }}" class="btn btn-sm btn-light">
                                    Manage Categories
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('letter-numbers.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">Import Letter Numbers</h4>
                        <button type="button" class="close" data-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="file">Select Excel File</label>
                            <input type="file" class="form-control" name="file" accept=".xls,.xlsx" required>
                            <small class="form-text text-muted">
                                Supported formats: .xls, .xlsx
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('scripts')
    <!-- DataTables  & Plugins -->
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
    <script src="{{ asset('assets/plugins/chart.js/Chart.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#categoriesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('dashboard.lettersByCategory') }}",
                columns: [{
                        data: 'category_name',
                        name: 'category_name'
                    },
                    {
                        data: 'total_letters',
                        name: 'total_letters',
                        searchable: false
                    },
                    {
                        data: 'status_breakdown',
                        name: 'status_breakdown',
                        orderable: false,
                        searchable: false
                    }
                ],
                pageLength: 4,
                lengthChange: false,
                info: false,
                searching: false,
                order: [
                    [1, 'desc']
                ]
            });

            $('#recentLettersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('dashboard.recentLetters') }}",
                columns: [{
                        data: 'letter_number',
                        name: 'letter_number'
                    },
                    {
                        data: 'category',
                        name: 'category'
                    },
                    {
                        data: 'status_badge',
                        name: 'status_badge',
                        orderable: false
                    },
                    {
                        data: 'created_date',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                pageLength: 5,
                lengthChange: false,
                info: false,
                searching: false,
                order: [
                    [3, 'desc']
                ]
            });

            // Status Distribution Chart
            var ctx = document.getElementById('statusChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Used', 'Reserved', 'Cancelled'],
                    datasets: [{
                        data: [{{ $usedLetters }}, {{ $reservedLetters }},
                            {{ $cancelledLetters }}
                        ],
                        backgroundColor: [
                            '#28a745', // Green for used
                            '#ffc107', // Yellow for reserved
                            '#dc3545' // Red for cancelled
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        position: 'bottom'
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var total = {{ $totalLetters }};
                                    var percentage = total > 0 ? ((context.parsed / total) * 100)
                                        .toFixed(1) : 0;
                                    return context.label + ': ' + context.parsed + ' (' + percentage +
                                        '%)';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
