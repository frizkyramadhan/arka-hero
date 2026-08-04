@extends('layouts.main')

@section('title', $title)

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
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
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ $subtitle }}</h3>
                        <div class="card-tools">
                            @can('fuel-claims.create')
                            <a href="{{ route('fuel-claims.create') }}" class="btn btn-warning">
                                <i class="fas fa-plus"></i> Create claim
                            </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="pending-fuel-table" class="table table-bordered table-striped" width="100%">
                                <thead>
                                    <tr>
                                        <th class="align-middle text-center" width="5%">No</th>
                                        <th class="align-middle">Date</th>
                                        <th class="align-middle">Vehicle</th>
                                        <th class="align-middle">Driver</th>
                                        <th class="align-middle">Fuel Type</th>
                                        <th class="align-middle">Qty (L)</th>
                                        <th class="align-middle">Total</th>
                                        <th class="align-middle text-center">Receipt</th>
                                        <th class="align-middle text-center" width="12%">Action</th>
                                    </tr>
                                </thead>
                            </table>
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
<script>
    $(function() {
        $('#pending-fuel-table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            dom: 'rtip',
            ajax: '{{ route('fuel-records.pending.data') }}',
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'fuel_date_fmt', name: 'fuel_date' },
                { data: 'vehicle_label', orderable: false, searchable: false },
                { data: 'driver_label', orderable: false, searchable: false },
                { data: 'fuel_type' },
                { data: 'quantity_fmt', name: 'quantity' },
                { data: 'total_cost_fmt', orderable: false },
                { data: 'receipt_thumb', orderable: false, searchable: false, className: 'text-center' },
                { data: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[1, 'asc']]
        });
    });
</script>
@endsection
