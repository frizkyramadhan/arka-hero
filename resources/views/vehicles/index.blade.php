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
                                @can('vehicles.show')
                                <a href="{{ route('vehicles.export') }}" id="btn-export-vehicles"
                                    class="btn btn-success">
                                    <i class="fas fa-download"></i> Export
                                </a>
                                @endcan
                                @canany(['vehicles.create', 'vehicles.edit'])
                                <button type="button" class="btn btn-info" data-toggle="modal"
                                    data-target="#importModal">
                                    <i class="fas fa-upload"></i> Import
                                </button>
                                @endcanany
                                @can('vehicles.create')
                                <a href="{{ route('vehicles.create') }}" class="btn btn-warning">
                                    <i class="fas fa-plus"></i> Add
                                </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            @if (session()->has('failures'))
                            <div class="card card-danger">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="icon fas fa-exclamation-triangle"></i> Import Validation Errors
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead>
                                                <tr>
                                                    <th style="width: 5%">Sheet</th>
                                                    <th class="text-center" style="width: 5%">Row</th>
                                                    <th style="width: 20%">Column</th>
                                                    <th style="width: 20%">Value</th>
                                                    <th>Error Message</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach (session()->get('failures') as $failure)
                                                <tr>
                                                    <td>{{ $failure['sheet'] }}</td>
                                                    <td class="text-center">{{ $failure['row'] }}</td>
                                                    <td>
                                                        <strong>{{ ucwords(str_replace('_', ' ', $failure['attribute'])) }}</strong>
                                                    </td>
                                                    <td>{{ $failure['value'] ?? '' }}</td>
                                                    <td>{!! nl2br(e($failure['errors'])) !!}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Please correct these errors in your Excel file and try importing again.
                                    </small>
                                </div>
                            </div>
                            @endif

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
                                                    <label>Search</label>
                                                    <input type="text" id="filter_q" class="form-control"
                                                        placeholder="Plate, code, PIC, or remarks" autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Status</label>
                                                    <select id="filter_status" class="form-control select2bs4">
                                                        <option value="">- All -</option>
                                                        <option value="active">Active</option>
                                                        <option value="inactive">Inactive</option>
                                                        <option value="maintenance">Maintenance</option>
                                                        <option value="sold">Sold</option>
                                                        <option value="accident">Accident</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Location</label>
                                                    <input type="text" id="filter_lokasi" class="form-control"
                                                        placeholder="Location / project" autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Validity</label>
                                                    <select id="filter_validity" class="form-control select2bs4">
                                                        <option value="">- All -</option>
                                                        <option value="expired">Expired</option>
                                                        <option value="expiring">Expiring soon (≤ days)</option>
                                                        <option value="valid">Valid (beyond days)</option>
                                                        <option value="missing">Missing STNK/PKB/KIR date</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label>Days</label>
                                                    <input type="number" id="filter_validity_days" class="form-control"
                                                        value="30" min="1" max="365">
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
                                <table id="vehicles-table" class="table table-bordered table-striped" width="100%">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="align-middle text-center" width="5%">No</th>
                                            <th rowspan="2" class="align-middle">License Plate</th>
                                            <th rowspan="2" class="align-middle">Code</th>
                                            <th rowspan="2" class="align-middle">PIC</th>
                                            <th colspan="4" class="text-center">Validity Period</th>
                                            <th rowspan="2" class="align-middle">Remarks</th>
                                            <th rowspan="2" class="align-middle text-center" width="8%">Status</th>
                                            <th rowspan="2" class="align-middle text-center" width="10%">Action</th>
                                        </tr>
                                        <tr>
                                            <th>STNK & Plate</th>
                                            <th>PKB</th>
                                            <th>KIR</th>
                                            <th>Location</th>
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

@canany(['vehicles.create', 'vehicles.edit'])
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Vehicles</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('vehicles.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="import_file">Excel file (.xls / .xlsx)</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="import_file" name="file"
                                    accept=".xls,.xlsx" required>
                                <label class="custom-file-label" for="import_file">Choose file</label>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Upserts by <code>kode</code> (or <code>license_plate</code>).
                            <a href="{{ route('vehicles.template') }}">Download template</a>
                            or use Export as a starting file.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcanany
@endsection

@section('scripts')
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<script>
    $(function() {
        if (typeof bsCustomFileInput !== 'undefined') {
            bsCustomFileInput.init();
        }

        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        function filterParams() {
            return {
                status: $('#filter_status').val(),
                lokasi: $('#filter_lokasi').val(),
                q: $('#filter_q').val(),
                validity: $('#filter_validity').val(),
                validity_days: $('#filter_validity_days').val() || 30
            };
        }

        var table = $('#vehicles-table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            dom: 'rtip',
            ajax: {
                url: "{{ route('vehicles.data') }}",
                data: function(d) {
                    Object.assign(d, filterParams());
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'license_plate'
                },
                {
                    data: 'kode'
                },
                {
                    data: 'pic',
                    defaultContent: '—'
                },
                {
                    data: 'stnk_cell',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'pkb_cell',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'kir_cell',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'lokasi',
                    defaultContent: '—'
                },
                {
                    data: 'keterangan',
                    defaultContent: ''
                },
                {
                    data: 'status_badge',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            order: [
                [2, 'asc']
            ]
        });

        var filterTextTimer;
        $('#filter_status, #filter_validity').on('change', function() {
            table.ajax.reload();
        });
        $('#filter_lokasi, #filter_q, #filter_validity_days').on('keyup change', function() {
            clearTimeout(filterTextTimer);
            filterTextTimer = setTimeout(function() {
                table.ajax.reload();
            }, 450);
        });
        $('#btn-reset-filter').on('click', function() {
            $('#filter_status, #filter_validity').val('').trigger('change');
            $('#filter_lokasi, #filter_q').val('');
            $('#filter_validity_days').val(30);
            table.ajax.reload();
        });

        $('#btn-export-vehicles').on('click', function(e) {
            e.preventDefault();
            var params = $.param(filterParams());
            window.location.href = "{{ route('vehicles.export') }}?" + params;
        });
    });
</script>
@endsection