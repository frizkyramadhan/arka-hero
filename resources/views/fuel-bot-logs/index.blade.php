@extends('layouts.main')

@section('title', $title)

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $title }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item active">{{ $title }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['today'] }}</h3>
                        <p>Submissions today</p>
                    </div>
                    <div class="icon"><i class="fas fa-paper-plane"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['synced_today'] }}</h3>
                        <p>Synced today</p>
                    </div>
                    <div class="icon"><i class="fas fa-check-double"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['awaiting'] }}</h3>
                        <p>Awaiting confirm</p>
                    </div>
                    <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['failed_7d'] }}</h3>
                        <p>Failed (7 days)</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div id="accordion">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ $subtitle }}</h3>
                            <div class="card-tools">
                                @can('fuel-bot-subscribers.show')
                                <a href="{{ route('fuel-bot-subscribers.index') }}" class="btn btn-default btn-sm">
                                    <i class="fas fa-user-check"></i> Whitelist
                                </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                Setiap foto nota yang dikirim ke
                                <a href="https://t.me/ARKAHeroFuel_bot" target="_blank"
                                    rel="noopener">@ARKAHeroFuel_bot</a>
                                tercatat di sini, dari saat diterima sampai jadi fuel record.
                            </p>

                            <div class="card card-primary">
                                <div class="card-header">
                                    <h4 class="card-title w-100">
                                        <a class="d-block w-100" data-toggle="collapse" href="#collapseFilter">
                                            <i class="fas fa-filter"></i> Filter
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseFilter" class="collapse" data-parent="#accordion">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Status</label>
                                                    <select class="form-control select2bs4" id="filter_status">
                                                        <option value="">- All -</option>
                                                        @foreach ($statuses as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>From</label>
                                                    <input type="date" class="form-control" id="filter_from">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>To</label>
                                                    <input type="date" class="form-control" id="filter_to">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Search</label>
                                                    <input type="text" class="form-control" id="filter_q"
                                                        placeholder="User, Telegram ID, ref, error" autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="button" class="btn btn-secondary w-100"
                                                        id="btn-reset-filter">
                                                        <i class="fas fa-times"></i> Reset
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="fuel-bot-logs-table" class="table table-bordered table-striped" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="align-middle text-center" width="5%">No</th>
                                            <th class="align-middle">Received</th>
                                            <th class="align-middle">Driver</th>
                                            <th class="align-middle">Vehicle</th>
                                            <th class="align-middle">Qty / Total</th>
                                            <th class="align-middle">Status</th>
                                            <th class="align-middle text-center" width="8%">Record</th>
                                            <th class="align-middle text-center" width="8%">Action</th>
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

@section('scripts')
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    $(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

        var table = $('#fuel-bot-logs-table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            dom: 'rtip',
            ajax: {
                url: '{{ route('fuel-bot-logs.data') }}',
                data: function(d) {
                    d.status = $('#filter_status').val();
                    d.from = $('#filter_from').val();
                    d.to = $('#filter_to').val();
                    d.q = $('#filter_q').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'created_fmt', name: 'created_at' },
                { data: 'user_label', orderable: false, searchable: false },
                { data: 'vehicle_label', orderable: false, searchable: false },
                { data: 'amount_label', orderable: false, searchable: false },
                { data: 'status_badge', name: 'status' },
                { data: 'record_link', orderable: false, searchable: false, className: 'text-center' },
                { data: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[1, 'desc']]
        });

        var filterTextTimer;
        $('#filter_status, #filter_from, #filter_to').on('change', function() { table.ajax.reload(); });
        $('#filter_q').on('keyup', function() {
            clearTimeout(filterTextTimer);
            filterTextTimer = setTimeout(function() { table.ajax.reload(); }, 450);
        });
        $('#btn-reset-filter').on('click', function() {
            $('#filter_status').val('').trigger('change.select2');
            $('#filter_from').val('');
            $('#filter_to').val('');
            $('#filter_q').val('');
            table.ajax.reload();
        });
    });
</script>
@endsection
