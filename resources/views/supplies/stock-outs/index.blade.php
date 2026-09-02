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
                            @can('supplies.stock-out.create')
                                <a href="{{ route('supplies.stock-outs.create') }}" class="btn btn-warning">
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
                            <table id="stock-outs-table" class="table table-bordered table-striped" width="100%">
                                <thead>
                                    <tr>
                                        <th class="align-middle text-center" width="5%">No</th>
                                        <th class="align-middle">SO No</th>
                                        <th class="align-middle">Project</th>
                                        <th class="align-middle">Date</th>
                                        <th class="align-middle text-center">Items</th>
                                        <th class="align-middle">Notes</th>
                                        <th class="align-middle text-center" width="10%">Action</th>
                                    </tr>
                                </thead>
                            </table>
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
            const table = $('#stock-outs-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                dom: 'rtip',
                ajax: {
                    url: "{{ route('supplies.stock-outs.data') }}",
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
        });
    </script>
@endsection
