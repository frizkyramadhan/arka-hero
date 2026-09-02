@extends('layouts.main')

@section('title', $title ?? 'Order Fulfillment Gap')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="m-0 font-weight-bold"><i class="fas fa-truck-loading text-warning mr-2"></i>{{ $title }}</h1>
                    <nav aria-label="breadcrumb" class="mt-1">
                        <ol class="breadcrumb mb-0 py-0 bg-transparent small">
                            <li class="breadcrumb-item"><a href="{{ route('supplies.reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">Fulfillment Gap</li>
                        </ol>
                    </nav>
                </div>
                @include('supplies-reports.partials.report-header-actions', ['parentRoute' => route('supplies.reports.index')])
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-1"></i>
                Shows approved supply order lines where received quantity is less than ordered quantity.
            </div>

            <div class="card card-outline card-warning shadow-sm mb-4">
                <div class="card-header py-3 bg-white border-bottom">
                    <h5 class="card-title mb-0"><i class="fas fa-filter text-warning mr-2"></i>Filter Options</h5>
                </div>
                <div class="card-body">
                    <form id="filterForm" onsubmit="return false">
                        <div class="row align-items-end">
                            <div class="col-md-6 col-lg-3 form-group mb-lg-0">
                                <label class="small font-weight-bold text-muted">Project</label>
                                <select class="form-control" id="project_id">
                                    <option value="">All</option>
                                    @foreach ($projects as $p)
                                        <option value="{{ $p->id }}" @selected(($filters['project_id'] ?? '') == $p->id)>{{ $p->project_code }} — {{ $p->project_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3 form-group mb-lg-0">
                                <label class="small font-weight-bold text-muted">Order No</label>
                                <input type="text" class="form-control" id="order_number" value="{{ $filters['order_number'] ?? '' }}" placeholder="ORD-">
                            </div>
                            <div class="col-md-6 col-lg-3 form-group mb-lg-0">
                                <label class="small font-weight-bold text-muted">Search item</label>
                                <input type="text" class="form-control" id="q" value="{{ $filters['q'] ?? '' }}" placeholder="Code or name">
                            </div>
                        </div>
                        <div class="row mt-3 pt-3 border-top">
                            <div class="col-12">
                                <button type="button" id="btn-show-data" class="btn btn-warning mr-2"><i class="fas fa-search mr-1"></i> Show data</button>
                                <a href="{{ route('supplies.reports.order-fulfillment') }}" class="btn btn-outline-secondary mr-2"><i class="fas fa-undo mr-1"></i> Reset</a>
                                <a href="#" id="btn-export-excel" class="btn btn-success btn-sm"><i class="fas fa-file-excel mr-1"></i> Export to Excel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="report-table" class="table table-hover table-bordered mb-0 supplies-report-table" style="width:100%">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Order No</th>
                                    <th>Project</th>
                                    <th>Item code</th>
                                    <th>Item name</th>
                                    <th class="text-center">Ordered</th>
                                    <th class="text-center">Received</th>
                                    <th class="text-center">Outstanding</th>
                                    <th class="text-center">Action</th>
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
    @include('supplies-reports.partials.datatable-styles', ['accent' => 'warning'])
@endsection

@section('scripts')
    @include('supplies-reports.partials.datatable-scripts')
    <script>
        $(function() {
            initSuppliesReport({
                dataUrl: @json(route('supplies.reports.order-fulfillment.data')),
                exportUrl: @json(route('supplies.reports.order-fulfillment.export')),
                requireProject: false,
                allowEmptyFilters: true,
                columns: [
                    { data: 'DT_RowIndex', className: 'text-center' },
                    { data: 'order_number' },
                    { data: 'project_code' },
                    { data: 'item_code' },
                    { data: 'item_name' },
                    { data: 'quantity_ordered', className: 'text-center' },
                    { data: 'quantity_received', className: 'text-center' },
                    { data: 'quantity_outstanding', className: 'text-center' },
                    { data: 'actions', className: 'text-center', orderable: false }
                ]
            });
        });
    </script>
@endsection
