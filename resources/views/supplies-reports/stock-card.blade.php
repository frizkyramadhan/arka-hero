@extends('layouts.main')

@section('title', $title ?? 'Stock Card Report')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="m-0 font-weight-bold"><i class="fas fa-clipboard-list text-primary mr-2"></i>{{ $title }}</h1>
                    <nav aria-label="breadcrumb" class="mt-1">
                        <ol class="breadcrumb mb-0 py-0 bg-transparent small">
                            <li class="breadcrumb-item"><a href="{{ route('supplies.reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">Stock Card</li>
                        </ol>
                    </nav>
                </div>
                @include('supplies-reports.partials.report-header-actions', ['parentRoute' => route('supplies.reports.index')])
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary shadow-sm mb-4">
                <div class="card-header py-3 bg-white border-bottom">
                    <h5 class="card-title mb-0"><i class="fas fa-filter text-primary mr-2"></i>Filter Options</h5>
                </div>
                <div class="card-body">
                    <form id="filterForm" onsubmit="return false">
                        <div class="row align-items-end">
                            <div class="col-md-6 col-lg-3 form-group mb-lg-0">
                                <label class="small font-weight-bold text-muted">Project <span class="text-danger">*</span></label>
                                <select class="form-control" id="project_id">
                                    <option value="">Select project</option>
                                    @foreach ($projects as $p)
                                        <option value="{{ $p->id }}" @selected(($filters['project_id'] ?? '') == $p->id)>{{ $p->project_code }} — {{ $p->project_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2 form-group mb-lg-0">
                                <label class="small font-weight-bold text-muted">Category</label>
                                <select class="form-control" id="category_id">
                                    <option value="">All</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" @selected(($filters['category_id'] ?? '') == $cat->id)>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2 form-group mb-lg-0">
                                <label class="small font-weight-bold text-muted">Status</label>
                                <select class="form-control" id="status">
                                    <option value="">All</option>
                                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3 form-group mb-lg-0">
                                <label class="small font-weight-bold text-muted">Search item</label>
                                <input type="text" class="form-control" id="q" value="{{ $filters['q'] ?? '' }}" placeholder="Code or name">
                            </div>
                        </div>
                        <div class="row mt-3 pt-3 border-top">
                            <div class="col-12">
                                <button type="button" id="btn-show-data" class="btn btn-primary mr-2"><i class="fas fa-search mr-1"></i> Show data</button>
                                <a href="{{ route('supplies.reports.stock-card') }}" class="btn btn-outline-secondary mr-2"><i class="fas fa-undo mr-1"></i> Reset</a>
                                <a href="#" id="btn-export-excel" class="btn btn-success btn-sm"><i class="fas fa-file-excel mr-1"></i> Export to Excel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header py-3 bg-white border-bottom">
                    <h5 class="card-title mb-0"><i class="fas fa-table text-primary mr-2"></i>Report Data</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="report-table" class="table table-hover table-bordered mb-0 supplies-report-table" style="width:100%">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Item code</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Unit</th>
                                    <th class="text-center">Stock In</th>
                                    <th class="text-center">Stock Out</th>
                                    <th class="text-center">Ending balance</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    @include('supplies-reports.partials.datatable-styles', ['accent' => 'primary'])
@endsection

@section('scripts')
    @include('supplies-reports.partials.datatable-scripts')
    <script>
        $(function() {
            initSuppliesReport({
                dataUrl: @json(route('supplies.reports.stock-card.data')),
                exportUrl: @json(route('supplies.reports.stock-card.export')),
                requireProject: true,
                columns: [
                    { data: 'DT_RowIndex', orderable: false, className: 'text-center' },
                    { data: 'code' },
                    { data: 'name' },
                    { data: 'category_label' },
                    { data: 'stock_unit' },
                    { data: 'stock_in', className: 'text-center' },
                    { data: 'stock_out', className: 'text-center' },
                    { data: 'ending_balance', className: 'text-center' },
                    { data: 'status_badge', className: 'text-center', orderable: false }
                ]
            });
        });
    </script>
@endsection
