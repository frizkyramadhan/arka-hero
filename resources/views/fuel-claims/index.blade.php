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
            <div class="col-12">
                <div id="accordion">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ $subtitle }}</h3>
                            <div class="card-tools">
                                @can('fuel-claims.create')
                                <a href="{{ route('fuel-claims.create') }}" class="btn btn-warning">
                                    <i class="fas fa-plus"></i> Add
                                </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
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
                                                    <select id="filter_status" class="form-control select2bs4">
                                                        <option value="">- All -</option>
                                                        <option value="draft">Draft</option>
                                                        <option value="ready">Ready</option>
                                                        <option value="sent">Sent</option>
                                                        <option value="realized">Realized</option>
                                                        <option value="cancelled">Cancelled</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="button" class="btn btn-secondary w-100"
                                                        id="btn-reset-filter" title="Reset">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="fuel-claims-table" class="table table-bordered table-striped" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="align-middle text-center" width="5%">No</th>
                                            <th class="align-middle">Claim #</th>
                                            <th class="align-middle">Period</th>
                                            <th class="align-middle text-center">Items</th>
                                            <th class="align-middle">Total</th>
                                            <th class="align-middle text-center">Status</th>
                                            <th class="align-middle text-center" width="10%">Action</th>
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
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        var table = $('#fuel-claims-table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            dom: 'rtip',
            ajax: {
                url: '{{ route('fuel-claims.data') }}',
                data: function(d) {
                    d.status = $('#filter_status').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'claim_number' },
                { data: 'period', orderable: false },
                { data: 'records_count', name: 'records_count', className: 'text-center' },
                { data: 'total_cost_fmt', orderable: false },
                { data: 'status_badge', orderable: false, searchable: false, className: 'text-center' },
                { data: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[1, 'desc']]
        });

        $('#filter_status').on('change', function() {
            table.ajax.reload();
        });
        $('#btn-reset-filter').on('click', function() {
            $('#filter_status').val('').trigger('change');
            table.ajax.reload();
        });
    });
</script>
@endsection
