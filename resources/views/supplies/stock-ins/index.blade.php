@extends('layouts.main')

@section('title', $title)

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
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
                        <li class="breadcrumb-item">Supplies</li>
                        <li class="breadcrumb-item active">{{ $title }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div id="accordion">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ $subtitle }}</h3>
                        <div class="card-tools">
                            @can('supplies.stock-in.show')
                                <a href="{{ route('supplies.stock-ins.export') }}" id="btn-export-stock-in"
                                    class="btn btn-success">
                                    <i class="fas fa-download"></i> Export
                                </a>
                            @endcan
                            @canany(['supplies.stock-in.create', 'supplies.stock-in.edit'])
                                <button type="button" class="btn btn-info" data-toggle="modal"
                                    data-target="#importModal">
                                    <i class="fas fa-upload"></i> Import
                                </button>
                            @endcanany
                            @can('supplies.stock-in.create')
                                <a href="{{ route('supplies.stock-ins.create') }}" class="btn btn-warning">
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
                                                <label>Project</label>
                                                <select class="form-control select2bs4" id="filter_project">
                                                    <option value="">- All -</option>
                                                    @foreach ($projects as $project)
                                                        <option value="{{ $project->id }}">{{ $project->project_code }} - {{ $project->project_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Date from</label>
                                                <input type="date" class="form-control" id="filter_date1">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Date to</label>
                                                <input type="date" class="form-control" id="filter_date2">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="button" class="btn btn-secondary w-100" id="btn-reset-filter">
                                                    <i class="fas fa-times"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="stock-ins-table" class="table table-bordered table-striped" width="100%">
                                <thead>
                                    <tr>
                                        <th class="align-middle text-center" width="5%">No</th>
                                        <th class="align-middle">SI No</th>
                                        <th class="align-middle">Project</th>
                                        <th class="align-middle">Date</th>
                                        <th class="align-middle text-center">Items</th>
                                        <th class="align-middle">Supply Order</th>
                                        <th class="align-middle">Notes</th>
                                        <th class="align-middle text-center" width="12%">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @canany(['supplies.stock-in.create', 'supplies.stock-in.edit'])
                <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="importModalLabel">Import Stock In</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="{{ route('supplies.stock-ins.import') }}" method="POST" enctype="multipart/form-data">
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
                                            One row per line item. Leave <code>document_number</code> blank to create;
                                            fill an existing SI number to update. Rows with the same blank header
                                            (<code>project_code</code> + <code>stock_date</code> + <code>notes</code>
                                            are grouped into one document.
                                            <a href="{{ route('supplies.stock-ins.template') }}">Download template</a>
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
        </div>
    </section>
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
            $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });
            const table = $('#stock-ins-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                dom: 'rtip',
                ajax: {
                    url: "{{ route('supplies.stock-ins.data') }}",
                    data: function(d) {
                        d.project_id = $('#filter_project').val();
                        d.date1 = $('#filter_date1').val();
                        d.date2 = $('#filter_date2').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, className: 'text-center' },
                    { data: 'document_number' },
                    { data: 'project_label', orderable: false },
                    { data: 'stock_date' },
                    { data: 'items_count', className: 'text-center', orderable: false },
                    { data: 'order_label', orderable: false },
                    { data: 'notes', defaultContent: '—' },
                    { data: 'action', orderable: false, className: 'text-center' }
                ]
            });
            $('#filter_project, #filter_date1, #filter_date2').on('change', function() { table.ajax.reload(); });
            $('#btn-reset-filter').on('click', function() {
                $('#filter_project').val('').trigger('change');
                $('#filter_date1, #filter_date2').val('');
                table.ajax.reload();
            });
            $('#btn-export-stock-in').on('click', function(e) {
                e.preventDefault();
                const params = $.param({
                    project_id: $('#filter_project').val(),
                    date1: $('#filter_date1').val(),
                    date2: $('#filter_date2').val(),
                });
                window.location.href = "{{ route('supplies.stock-ins.export') }}?" + params;
            });
        });
    </script>
@endsection
