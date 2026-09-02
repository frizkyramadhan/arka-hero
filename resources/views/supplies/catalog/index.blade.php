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
                        <li class="breadcrumb-item">Supplies</li>
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
                                    @can('supplies.catalog.show')
                                        <a href="{{ route('supplies.catalog.export') }}" id="btn-export-catalog"
                                            class="btn btn-success">
                                            <i class="fas fa-download"></i> Export
                                        </a>
                                    @endcan
                                    @canany(['supplies.catalog.create', 'supplies.catalog.edit'])
                                        <button type="button" class="btn btn-info" data-toggle="modal"
                                            data-target="#importModal">
                                            <i class="fas fa-upload"></i> Import
                                        </button>
                                    @endcanany
                                    @can('supplies.catalog.create')
                                        <a class="btn btn-warning" data-toggle="modal" data-target="#modal-add">
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
                                    <div id="collapseFilter" class="collapse show" data-parent="#accordion">
                                        <div class="card-body">
                                            <p class="text-muted small mb-3">
                                                <i class="fas fa-info-circle"></i>
                                                Stock In, Stock Out, and Ending balance are calculated per project. Select a project below to see quantities.
                                            </p>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Project (for balances)</label>
                                                        <select class="form-control select2bs4" id="filter_project">
                                                            <option value="">- Select project -</option>
                                                            @foreach ($projects as $project)
                                                                <option value="{{ $project->id }}" @selected(($defaultProjectId ?? null) == $project->id)>
                                                                    {{ $project->project_code }} - {{ $project->project_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Item Category</label>
                                                        <select class="form-control select2bs4" id="filter_category">
                                                            <option value="">- All -</option>
                                                            @foreach ($categories as $category)
                                                                <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->prefix }})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select class="form-control select2bs4" id="filter_status">
                                                            <option value="">- All -</option>
                                                            <option value="active">Active</option>
                                                            <option value="inactive">Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Search</label>
                                                        <input type="text" class="form-control" id="filter_q" placeholder="Code, name, description" autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
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
                                    <table id="catalog-table" class="table table-bordered table-striped" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="align-middle text-center" width="5%">No</th>
                                                <th class="align-middle">Item code</th>
                                                <th class="align-middle">Name</th>
                                                <th class="align-middle">Item Category</th>
                                                <th class="align-middle">Stock unit</th>
                                                <th class="align-middle text-center">Stock In</th>
                                                <th class="align-middle text-center">Stock Out</th>
                                                <th class="align-middle text-center">Ending balance</th>
                                                <th class="align-middle text-center" width="10%">Status</th>
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

            @can('supplies.catalog.create')
                <div class="modal fade" id="modal-add">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('supplies.catalog.store') }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h4 class="modal-title">Add catalog item</h4>
                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    @include('supplies.catalog._form-fields', ['model' => null, 'categories' => $categories, 'categoryCodePreviews' => $categoryCodePreviews])
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan

            @canany(['supplies.catalog.create', 'supplies.catalog.edit'])
                <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="importModalLabel">Import Catalog</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="{{ route('supplies.catalog.import') }}" method="POST" enctype="multipart/form-data">
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
                                            Upserts by <code>code</code>. New rows need <code>name</code>,
                                            <code>stock_unit</code>, and <code>category_prefix</code> or
                                            <code>category_name</code>.
                                            <a href="{{ route('supplies.catalog.template') }}">Download template</a>
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
            var categoryCodePreviews = @json($categoryCodePreviews ?? []);

            function updateItemCodePreview() {
                var categoryId = $('#modal-add #supply_item_category_id').val();
                var preview = categoryId && categoryCodePreviews[categoryId]
                    ? categoryCodePreviews[categoryId]
                    : '';
                $('#modal-add #item-code-preview').val(preview);
            }

            $('#modal-add #supply_item_category_id').on('change', updateItemCodePreview);
            $('#modal-add').on('shown.bs.modal', updateItemCodePreview);

            $(document).on('shown.bs.modal', '.modal', function() {
                var $modal = $(this);
                $modal.find('select.select2bs4').each(function() {
                    var $el = $(this);
                    if ($el.hasClass('select2-hidden-accessible')) {
                        $el.select2('destroy');
                    }
                    $el.select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $modal });
                });
            });

            const table = $('#catalog-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                dom: 'rtip',
                ajax: {
                    url: "{{ route('supplies.catalog.data') }}",
                    data: function(d) {
                        d.project_id = $('#filter_project').val();
                        d.category_id = $('#filter_category').val();
                        d.status = $('#filter_status').val();
                        d.q = $('#filter_q').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'code' },
                    { data: 'name' },
                    { data: 'category_label', orderable: false },
                    { data: 'stock_unit' },
                    { data: 'stock_in', className: 'text-center', orderable: false },
                    { data: 'stock_out', className: 'text-center', orderable: false },
                    { data: 'ending_balance', className: 'text-center', orderable: false },
                    { data: 'status_badge', className: 'text-center', orderable: false },
                    { data: 'action', orderable: false, searchable: false, className: 'text-center' }
                ]
            });

            var filterTextTimer;
            $('#filter_project, #filter_category, #filter_status').on('change', function() { table.ajax.reload(); });
            $('#filter_q').on('keyup', function() {
                clearTimeout(filterTextTimer);
                filterTextTimer = setTimeout(function() { table.ajax.reload(); }, 450);
            });
            $('#btn-reset-filter').on('click', function() {
                $('#filter_project, #filter_category, #filter_status').val('').trigger('change');
                $('#filter_q').val('');
                table.ajax.reload();
            });

            $('#btn-export-catalog').on('click', function(e) {
                e.preventDefault();
                const params = $.param({
                    category_id: $('#filter_category').val(),
                    status: $('#filter_status').val(),
                    q: $('#filter_q').val()
                });
                window.location.href = "{{ route('supplies.catalog.export') }}?" + params;
            });
        });

        function editSupplyItem(id) {
            $('#modal-edit-' + id).modal('show');
        }
    </script>
@endsection
