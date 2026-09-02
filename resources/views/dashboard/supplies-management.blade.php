@extends('layouts.main')

@section('title', $title)

@section('styles')
    <style>
        .sp-info-box .progress { height: 3px; margin: 4px 0; }
        .sp-info-box .info-box-icon { width: 50px; height: 50px; line-height: 50px; font-size: 1.25rem; }
        .sp-info-box .info-box-content { padding-left: 8px; }
        .sp-info-box .info-box-number { font-size: 1.45rem; font-weight: 600; }
        .sp-info-box .info-box-text { font-size: 0.88rem; }
        .sp-info-box .progress-description { font-size: 0.75rem; }
        .sp-status-pill { min-width: 2.5rem; display: inline-block; }
        .sp-order-status-bar { height: 8px; border-radius: 4px; overflow: hidden; background: #e9ecef; }
        .sp-order-status-bar > span { display: block; height: 100%; float: left; }
        .sp-low-stock-badge { font-size: 0.72rem; }
        .sp-activity-item { border-left: 3px solid transparent; transition: background-color .15s; }
        .sp-activity-item:hover { background-color: #f8f9fa; }
        .sp-activity-item.sp-in { border-left-color: #28a745; }
        .sp-activity-item.sp-out { border-left-color: #dc3545; }
        .sp-chart-wrap { position: relative; height: 280px; }
        .sp-mini-stat { border-radius: .5rem; border: 1px solid #e9ecef; padding: .75rem 1rem; height: 100%; }
        .sp-mini-stat .value { font-size: 1.35rem; font-weight: 600; line-height: 1.2; }
        .sp-mini-stat .label { font-size: .72rem; text-transform: uppercase; color: #6c757d; letter-spacing: .03em; }
        .sp-scroll-card {
            display: flex;
            flex-direction: column;
            height: 320px;
        }
        .sp-scroll-card .card-header { flex-shrink: 0; }
        .sp-scroll-card__body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .sp-scroll-card__body .table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #f8f9fa;
            box-shadow: 0 1px 0 #dee2e6;
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                    <p class="text-muted mb-0 small">{{ $subtitle }}</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Supplies</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            {{-- Quick actions --}}
            <div class="row mb-2">
                <div class="col-12">
                    <div class="btn-group btn-group-sm flex-wrap shadow-sm">
                        @can('supplies.catalog.show')
                            <a href="{{ route('supplies.catalog.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-book mr-1"></i> Catalog
                            </a>
                        @endcan
                        @can('supplies.stock-in.create')
                            <a href="{{ route('supplies.stock-ins.create') }}" class="btn btn-outline-success">
                                <i class="fas fa-plus mr-1"></i> Stock In
                            </a>
                        @endcan
                        @can('supplies.stock-out.create')
                            <a href="{{ route('supplies.stock-outs.create') }}" class="btn btn-outline-warning">
                                <i class="fas fa-plus mr-1"></i> Stock Out
                            </a>
                        @endcan
                        @can('supplies.orders.show')
                            <a href="{{ route('supplies.orders.index') }}" class="btn btn-outline-info">
                                <i class="fas fa-file-invoice mr-1"></i> Orders
                            </a>
                        @endcan
                        @can('supplies.reports.show')
                            <a href="{{ route('supplies.reports.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-chart-pie mr-1"></i> Reports
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            @if ($pendingApprovalSteps > 0 || $approvedAwaitingReceipt > 0)
                <div class="row mb-2">
                    <div class="col-12">
                        @if ($pendingApprovalSteps > 0)
                            <div class="alert alert-warning py-2 mb-2 shadow-sm">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <strong>{{ number_format($pendingApprovalSteps) }}</strong> supply order approval step(s) need action.
                                <a href="{{ route('approval-requests.index') }}" class="alert-link ml-1">Open approvals</a>
                            </div>
                        @endif
                        @if ($approvedAwaitingReceipt > 0)
                            <div class="alert alert-info py-2 mb-0 shadow-sm">
                                <i class="fas fa-dolly mr-1"></i>
                                <strong>{{ number_format($approvedAwaitingReceipt) }}</strong> approved order(s) still awaiting stock receipt.
                                @can('supplies.stock-in.create')
                                    <a href="{{ route('supplies.stock-ins.create') }}" class="alert-link ml-1">Record Stock In</a>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Primary KPI row --}}
            <div class="row mb-2">
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box bg-gradient-primary mb-0 shadow-sm sp-info-box">
                        <span class="info-box-icon"><i class="fas fa-boxes"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Catalog Items</span>
                            <span class="info-box-number">{{ number_format($totalCatalogItems) }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                @foreach ($catalogByCategory as $cat)
                                    {{ $cat->prefix }} {{ $cat->items_count }}@if (! $loop->last) · @endif
                                @endforeach
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box bg-gradient-success mb-0 shadow-sm sp-info-box">
                        <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Stock In ({{ now()->format('M Y') }})</span>
                            <span class="info-box-number">{{ number_format($stockInQtyThisMonth) }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ $stockInQtyThisMonth + $stockOutQtyThisMonth > 0 ? min(100, ($stockInQtyThisMonth / max(1, $stockInQtyThisMonth + $stockOutQtyThisMonth)) * 100) : 0 }}%"></div>
                            </div>
                            <span class="progress-description">
                                {{ number_format($stockInThisMonth) }} docs · {{ number_format($stockInQtyToday) }} qty today
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box bg-gradient-danger mb-0 shadow-sm sp-info-box">
                        <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Stock Out ({{ now()->format('M Y') }})</span>
                            <span class="info-box-number">{{ number_format($stockOutQtyThisMonth) }}</span>
                            <div class="progress">
                                <div class="progress-bar bg-danger" style="width: {{ $stockInQtyThisMonth + $stockOutQtyThisMonth > 0 ? min(100, ($stockOutQtyThisMonth / max(1, $stockInQtyThisMonth + $stockOutQtyThisMonth)) * 100) : 0 }}%"></div>
                            </div>
                            <span class="progress-description">
                                {{ number_format($stockOutThisMonth) }} docs · {{ number_format($stockOutQtyToday) }} qty today
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    @php
                        $netClass = $netMovementThisMonth >= 0 ? 'bg-gradient-info' : 'bg-gradient-warning';
                        $netIcon = $netMovementThisMonth >= 0 ? 'fa-balance-scale' : 'fa-exclamation-circle';
                    @endphp
                    <div class="info-box {{ $netClass }} mb-0 shadow-sm sp-info-box">
                        <span class="info-box-icon"><i class="fas {{ $netIcon }}"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Net Movement</span>
                            <span class="info-box-number">{{ $netMovementThisMonth >= 0 ? '+' : '' }}{{ number_format($netMovementThisMonth) }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">In minus out qty this month</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Secondary KPI row --}}
            <div class="row mb-3">
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="sp-mini-stat shadow-sm">
                        <div class="label">Supply Orders</div>
                        <div class="value text-primary">{{ number_format($totalOrders) }}</div>
                        <small class="text-muted">
                            +{{ number_format($thisMonthOrders) }} this month
                            @if ($ordersMonthGrowthPct != 0)
                                <span class="{{ $ordersMonthGrowthPct >= 0 ? 'text-success' : 'text-danger' }}">
                                    ({{ $ordersMonthGrowthPct >= 0 ? '+' : '' }}{{ $ordersMonthGrowthPct }}%)
                                </span>
                            @endif
                        </small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="sp-mini-stat shadow-sm">
                        <div class="label">Active Orders</div>
                        <div class="value text-warning">{{ number_format($ordersActive) }}</div>
                        <small class="text-muted">{{ number_format($countSubmitted) }} submitted · {{ number_format($countApproved) }} approved</small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="sp-mini-stat shadow-sm">
                        <div class="label">Stock Documents</div>
                        <div class="value">{{ number_format($totalStockInDocs + $totalStockOutDocs) }}</div>
                        <small class="text-muted">
                            <span class="text-success">{{ number_format($totalStockInDocs) }} SI</span> ·
                            <span class="text-danger">{{ number_format($totalStockOutDocs) }} SO</span>
                        </small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="sp-mini-stat shadow-sm">
                        <div class="label">Needs Attention</div>
                        <div class="value text-danger">{{ number_format($pendingApprovalSteps + $approvedAwaitingReceipt) }}</div>
                        <small class="text-muted">{{ number_format($pendingApprovalSteps) }} approval · {{ number_format($approvedAwaitingReceipt) }} receipt</small>
                    </div>
                </div>
            </div>

            {{-- Charts + order status --}}
            <div class="row mb-3">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <div class="card card-outline card-primary shadow-sm h-100 mb-0">
                        <div class="card-header py-2">
                            <h3 class="card-title mb-0"><i class="fas fa-chart-line mr-1"></i> Stock movement trend (6 months)</h3>
                        </div>
                        <div class="card-body">
                            <div class="sp-chart-wrap">
                                <canvas id="spMovementChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-outline card-secondary shadow-sm mb-3">
                        <div class="card-header py-2">
                            <h3 class="card-title mb-0 text-sm"><i class="fas fa-chart-pie mr-1"></i> Catalog by category</h3>
                        </div>
                        <div class="card-body p-2">
                            <div style="position: relative; height: 160px;">
                                <canvas id="spCategoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="card card-outline card-info shadow-sm mb-0">
                        <div class="card-header py-2">
                            <h3 class="card-title mb-0 text-sm"><i class="fas fa-tasks mr-1"></i> Order status</h3>
                        </div>
                        <div class="card-body py-2">
                            @php
                                $statusRows = [
                                    ['label' => 'Draft', 'count' => $countDraft, 'color' => '#6c757d'],
                                    ['label' => 'Submitted', 'count' => $countSubmitted, 'color' => '#ffc107'],
                                    ['label' => 'Approved', 'count' => $countApproved, 'color' => '#28a745'],
                                    ['label' => 'Closed', 'count' => $countClosed, 'color' => '#17a2b8'],
                                    ['label' => 'Rejected', 'count' => $countRejected, 'color' => '#dc3545'],
                                    ['label' => 'Cancelled', 'count' => $countCancelled, 'color' => '#343a40'],
                                ];
                            @endphp
                            <div class="sp-order-status-bar mb-3">
                                @foreach ($statusRows as $s)
                                    @if ($totalOrders > 0 && $s['count'] > 0)
                                        <span style="width: {{ ($s['count'] / $totalOrders) * 100 }}%; background: {{ $s['color'] }};" title="{{ $s['label'] }}: {{ $s['count'] }}"></span>
                                    @endif
                                @endforeach
                            </div>
                            @foreach ($statusRows as $s)
                                <div class="d-flex justify-content-between align-items-center small mb-1">
                                    <span>
                                        <span class="rounded-circle d-inline-block mr-1" style="width:8px;height:8px;background:{{ $s['color'] }}"></span>
                                        {{ $s['label'] }}
                                    </span>
                                    <strong class="sp-status-pill text-right">{{ number_format($s['count']) }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Low stock + top items --}}
            <div class="row mb-3">
                <div class="col-lg-4 mb-3 mb-lg-0">
                    <div class="card card-outline card-warning shadow-sm sp-scroll-card mb-0">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0 text-sm"><i class="fas fa-exclamation-triangle mr-1"></i> Low stock (≤ 10)</h3>
                            @can('supplies.reports.show')
                                <a href="{{ route('supplies.reports.stock-card') }}" class="btn btn-xs btn-outline-warning">Stock card</a>
                            @endcan
                        </div>
                        <div class="card-body p-0 sp-scroll-card__body">
                            @if ($lowStockItems->isEmpty())
                                <p class="text-muted p-3 mb-0 small">No items at or below threshold in your project scope.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Item</th>
                                                <th class="text-right">Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($lowStockItems as $row)
                                                <tr>
                                                    <td>
                                                        <code class="small">{{ $row->code }}</code>
                                                        <div class="text-truncate small" style="max-width: 12rem;" title="{{ display_text($row->name) }}">
                                                            {{ display_text($row->name) }}
                                                        </div>
                                                        <span class="badge badge-light sp-low-stock-badge">{{ $row->category }}</span>
                                                    </td>
                                                    <td class="text-right align-middle">
                                                        @php $ending = (int) $row->ending; @endphp
                                                        <span class="badge badge-{{ $ending <= 0 ? 'danger' : ($ending <= 5 ? 'warning' : 'secondary') }}">
                                                            {{ number_format($ending) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-3 mb-lg-0">
                    <div class="card card-outline card-danger shadow-sm sp-scroll-card mb-0">
                        <div class="card-header py-2">
                            <h3 class="card-title mb-0 text-sm"><i class="fas fa-fire mr-1"></i> Top issued (30 days)</h3>
                        </div>
                        <div class="card-body p-0 sp-scroll-card__body">
                            @if ($topConsumed->isEmpty())
                                <p class="text-muted p-3 mb-0 small">No stock out activity in the last 30 days.</p>
                            @else
                                @php $maxOut = max(1, (int) $topConsumed->max('total_out')); @endphp
                                <ul class="list-group list-group-flush">
                                    @foreach ($topConsumed as $row)
                                        <li class="list-group-item py-2 px-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="small text-truncate" style="max-width: 70%;">
                                                    <code>{{ $row->code }}</code> {{ display_text($row->name) }}
                                                </span>
                                                <strong class="small">{{ number_format($row->total_out) }}</strong>
                                            </div>
                                            <div class="progress" style="height: 4px;">
                                                <div class="progress-bar bg-danger" style="width: {{ ($row->total_out / $maxOut) * 100 }}%"></div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-outline card-success shadow-sm sp-scroll-card mb-0">
                        <div class="card-header py-2">
                            <h3 class="card-title mb-0 text-sm"><i class="fas fa-truck-loading mr-1"></i> Top received (30 days)</h3>
                        </div>
                        <div class="card-body p-0 sp-scroll-card__body">
                            @if ($topReceived->isEmpty())
                                <p class="text-muted p-3 mb-0 small">No stock in activity in the last 30 days.</p>
                            @else
                                @php $maxIn = max(1, (int) $topReceived->max('total_in')); @endphp
                                <ul class="list-group list-group-flush">
                                    @foreach ($topReceived as $row)
                                        <li class="list-group-item py-2 px-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="small text-truncate" style="max-width: 70%;">
                                                    <code>{{ $row->code }}</code> {{ display_text($row->name) }}
                                                </span>
                                                <strong class="small">{{ number_format($row->total_in) }}</strong>
                                            </div>
                                            <div class="progress" style="height: 4px;">
                                                <div class="progress-bar bg-success" style="width: {{ ($row->total_in / $maxIn) * 100 }}%"></div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Projects + recent orders --}}
            <div class="row mb-3">
                <div class="col-lg-5 mb-3 mb-lg-0">
                    <div class="card card-outline card-primary shadow-sm sp-scroll-card mb-0">
                        <div class="card-header py-2">
                            <h3 class="card-title mb-0 text-sm"><i class="fas fa-project-diagram mr-1"></i> Top projects by orders</h3>
                        </div>
                        <div class="card-body p-0 sp-scroll-card__body">
                            @if ($byProject->isEmpty())
                                <p class="text-muted p-3 mb-0 small">No supply order data yet.</p>
                            @else
                                @php $maxProj = max(1, (int) $byProject->max('order_count')); @endphp
                                <ul class="list-group list-group-flush">
                                    @foreach ($byProject as $row)
                                        <li class="list-group-item py-2 px-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="small">
                                                    <strong>{{ $row->project_code }}</strong>
                                                    <span class="text-muted">— {{ display_text($row->project_name) }}</span>
                                                </span>
                                                <span class="badge badge-primary">{{ number_format($row->order_count) }}</span>
                                            </div>
                                            <div class="progress" style="height: 4px;">
                                                <div class="progress-bar bg-primary" style="width: {{ ($row->order_count / $maxProj) * 100 }}%"></div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card card-outline card-info shadow-sm sp-scroll-card mb-0">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0 text-sm"><i class="fas fa-file-invoice mr-1"></i> Recent orders</h3>
                            @can('supplies.orders.show')
                                <a href="{{ route('supplies.orders.index') }}" class="btn btn-xs btn-outline-primary">View all</a>
                            @endcan
                        </div>
                        <div class="card-body p-0 sp-scroll-card__body">
                            @if ($recentOrders->isEmpty())
                                <p class="text-muted p-3 mb-0 small">No orders yet.</p>
                            @else
                                <table class="table table-sm table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Order No</th>
                                                <th>Project</th>
                                                <th>Requester</th>
                                                <th>Status</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($recentOrders as $order)
                                                @php
                                                    $badge = match ($order->status) {
                                                        'draft' => 'secondary',
                                                        'submitted' => 'warning',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        'cancelled' => 'dark',
                                                        'closed' => 'info',
                                                        default => 'light',
                                                    };
                                                @endphp
                                                <tr>
                                                    <td><strong class="small">{{ $order->order_number }}</strong></td>
                                                    <td class="small">{{ $order->project->project_code ?? '—' }}</td>
                                                    <td class="small text-truncate" style="max-width: 8rem;">{{ $order->requestedBy->name ?? '—' }}</td>
                                                    <td><span class="badge badge-{{ $badge }}">{{ $order->statusLabel() }}</span></td>
                                                    <td class="text-right">
                                                        <a href="{{ route('supplies.orders.show', $order) }}" class="btn btn-xs btn-outline-info"><i class="fas fa-eye"></i></a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent SI / SO activity --}}
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="card card-outline card-success shadow-sm sp-scroll-card mb-0">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0 text-sm"><i class="fas fa-dolly mr-1"></i> Recent Stock In</h3>
                            @can('supplies.stock-in.show')
                                <a href="{{ route('supplies.stock-ins.index') }}" class="btn btn-xs btn-outline-success">View all</a>
                            @endcan
                        </div>
                        <div class="card-body p-0 sp-scroll-card__body">
                            @forelse ($recentStockIns as $si)
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom sp-activity-item sp-in">
                                    <div class="min-width-0">
                                        <strong class="small">{{ $si->document_number }}</strong>
                                        <span class="badge badge-light badge-sm ml-1">{{ $si->items_count }} line(s)</span>
                                        <small class="text-muted d-block">
                                            {{ $si->stock_date?->format('d M Y') }} · {{ $si->project->project_code ?? '—' }}
                                            @if ($si->order)
                                                · <span class="text-info">{{ $si->order->order_number }}</span>
                                            @endif
                                        </small>
                                    </div>
                                    <a href="{{ route('supplies.stock-ins.show', $si) }}" class="btn btn-xs btn-outline-success flex-shrink-0"><i class="fas fa-eye"></i></a>
                                </div>
                            @empty
                                <p class="text-muted p-3 mb-0 small">No stock in documents yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card card-outline card-danger shadow-sm sp-scroll-card mb-0">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0 text-sm"><i class="fas fa-shipping-fast mr-1"></i> Recent Stock Out</h3>
                            @can('supplies.stock-out.show')
                                <a href="{{ route('supplies.stock-outs.index') }}" class="btn btn-xs btn-outline-danger">View all</a>
                            @endcan
                        </div>
                        <div class="card-body p-0 sp-scroll-card__body">
                            @forelse ($recentStockOuts as $so)
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom sp-activity-item sp-out">
                                    <div class="min-width-0">
                                        <strong class="small">{{ $so->document_number }}</strong>
                                        <span class="badge badge-light badge-sm ml-1">{{ $so->items_count }} line(s)</span>
                                        <small class="text-muted d-block">
                                            {{ $so->stock_date?->format('d M Y') }} · {{ $so->project->project_code ?? '—' }}
                                        </small>
                                    </div>
                                    <a href="{{ route('supplies.stock-outs.show', $so) }}" class="btn btn-xs btn-outline-danger flex-shrink-0"><i class="fas fa-eye"></i></a>
                                </div>
                            @empty
                                <p class="text-muted p-3 mb-0 small">No stock out documents yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/chart.js/Chart.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var trendLabels = @json(collect($stockMovementTrend)->pluck('label'));
            var trendIn = @json(collect($stockMovementTrend)->pluck('in_qty'));
            var trendOut = @json(collect($stockMovementTrend)->pluck('out_qty'));

            var movementCtx = document.getElementById('spMovementChart');
            if (movementCtx) {
                new Chart(movementCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: trendLabels,
                        datasets: [
                            {
                                label: 'Stock In (qty)',
                                data: trendIn,
                                backgroundColor: 'rgba(40, 167, 69, 0.75)',
                                borderColor: 'rgba(40, 167, 69, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Stock Out (qty)',
                                data: trendOut,
                                backgroundColor: 'rgba(220, 53, 69, 0.75)',
                                borderColor: 'rgba(220, 53, 69, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }],
                            xAxes: [{ stacked: false }]
                        },
                        tooltips: { mode: 'index', intersect: false },
                        legend: { position: 'bottom' }
                    }
                });
            }

            var categoryCtx = document.getElementById('spCategoryChart');
            if (categoryCtx) {
                new Chart(categoryCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: @json($catalogByCategory->pluck('name')),
                        datasets: [{
                            data: @json($catalogByCategory->pluck('items_count')),
                            backgroundColor: ['#007bff', '#fd7e14', '#6f42c1', '#20c997'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: { position: 'bottom', labels: { boxWidth: 10, fontSize: 11 } }
                    }
                });
            }
        });
    </script>
@endsection
