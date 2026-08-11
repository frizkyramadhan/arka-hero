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
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $subtitle }}</h3>
                <div class="card-tools">
                    @can('vehicle-assignments.create')
                    <a href="{{ route('vehicle-assignments.create') }}" class="btn btn-warning">
                        <i class="fas fa-plus"></i> Create FOA
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Search</label>
                        <input type="text" id="filter_search" class="form-control" placeholder="No / driver / plate…">
                    </div>
                    <div class="col-md-3">
                        <label>Vehicle</label>
                        <select id="filter_vehicle" class="form-control select2bs4">
                            <option value="">- All -</option>
                            @foreach ($vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->kode }} — {{ $v->license_plate }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Status</label>
                        <select id="filter_status" class="form-control select2bs4">
                            <option value="">- All -</option>
                            <option value="draft">Draft</option>
                            <option value="issued">Issued</option>
                            <option value="in_progress">In Progress</option>
                            <option value="closed">Closed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>From</label>
                        <input type="date" id="filter_from" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label>To</label>
                        <input type="date" id="filter_to" class="form-control">
                    </div>
                </div>
                <table class="table table-bordered table-hover table-sm" id="foa-table" width="100%">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th>No.</th>
                            <th>Date</th>
                            <th>Driver</th>
                            <th>Vehicle</th>
                            <th>Destinations</th>
                            <th>Status</th>
                            <th width="110">Action</th>
                        </tr>
                    </thead>
                </table>
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
    $('.select2bs4').select2({ theme: 'bootstrap4' });
    var table = $('#foa-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('vehicle-assignments.data') }}",
            data: function(d) {
                d.search = $('#filter_search').val();
                d.vehicle_id = $('#filter_vehicle').val();
                d.status = $('#filter_status').val();
                d.from = $('#filter_from').val();
                d.to = $('#filter_to').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'form_number' },
            { data: 'date_fmt', name: 'assignment_date' },
            { data: 'driver_name' },
            { data: 'vehicle_label', orderable: false },
            { data: 'destinations', orderable: false },
            { data: 'status_badge', orderable: false },
            { data: 'action', orderable: false, searchable: false }
        ],
        order: [[2, 'desc']]
    });
    $('#filter_search, #filter_vehicle, #filter_status, #filter_from, #filter_to').on('change keyup', function() {
        table.ajax.reload();
    });
});
</script>
@endsection
