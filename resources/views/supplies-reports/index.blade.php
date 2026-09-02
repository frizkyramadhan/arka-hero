@extends('layouts.main')

@section('title', $title ?? 'Supplies Reports')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $subtitle ?? 'Supplies Reports' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Supplies Reports</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6 col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-clipboard-list text-primary"></i> Stock Card</h3>
                        </div>
                        <div class="card-body">
                            <p>Ending balance per item for a selected project: Stock In, Stock Out, and ending balance columns. Filter by category, status, or item search.</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('supplies.reports.stock-card') }}" class="btn btn-primary">
                                <i class="fas fa-table"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-exchange-alt text-info"></i> Stock Movement</h3>
                        </div>
                        <div class="card-body">
                            <p>Transaction ledger combining Stock In and Stock Out line items. Filter by project, date range, document type, or item/document search.</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('supplies.reports.stock-movement') }}" class="btn btn-info">
                                <i class="fas fa-table"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-file-invoice text-success"></i> Supply Order Monitoring</h3>
                        </div>
                        <div class="card-body">
                            <p>Supply order headers with status, project, department, requester, and fulfillment summary (lines, qty ordered, qty received).</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('supplies.reports.order-monitoring') }}" class="btn btn-success">
                                <i class="fas fa-table"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-truck-loading text-warning"></i> Order Fulfillment Gap</h3>
                        </div>
                        <div class="card-body">
                            <p>Approved supply order lines with outstanding quantity not yet received via Stock In. Use this to plan partial receipts.</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('supplies.reports.order-fulfillment') }}" class="btn btn-warning">
                                <i class="fas fa-table"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
