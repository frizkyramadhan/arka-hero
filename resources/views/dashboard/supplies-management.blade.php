@extends('layouts.main')

@section('title', $title)

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">{{ $subtitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <div class="btn-group btn-group-sm flex-wrap">
                        @can('supplies.catalog.show')
                            <a href="{{ route('supplies.catalog.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-book mr-1"></i> Catalog
                            </a>
                        @endcan
                        @can('supplies.stock-in.show')
                            <a href="{{ route('supplies.stock-ins.index') }}" class="btn btn-outline-success">
                                <i class="fas fa-dolly mr-1"></i> Stock In
                            </a>
                        @endcan
                        @can('supplies.stock-out.show')
                            <a href="{{ route('supplies.stock-outs.index') }}" class="btn btn-outline-warning">
                                <i class="fas fa-shipping-fast mr-1"></i> Stock Out
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

            <div class="row">
                <div class="col-12 mb-3">
                    <div class="card card-outline card-secondary mb-0">
                        <div class="card-header py-2">
                            <h3 class="card-title text-sm mb-0"><i class="fas fa-chart-bar mr-1"></i> Supply order status</h3>
                        </div>
                        <div class="card-body py-2 px-3">
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless mb-0 text-center">
                                    <thead>
                                        <tr class="text-muted small text-uppercase">
                                            <th class="border-0">Total</th>
                                            <th class="border-0">Draft</th>
                                            <th class="border-0">Submitted</th>
                                            <th class="border-0">Approved</th>
                                            <th class="border-0">Rejected</th>
                                            <th class="border-0">Cancelled</th>
                                            <th class="border-0">Closed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="align-middle"><strong class="h5 mb-0">{{ number_format($totalOrders) }}</strong></td>
                                            <td class="align-middle"><span class="badge badge-secondary">{{ number_format($countDraft) }}</span></td>
                                            <td class="align-middle"><span class="badge badge-warning">{{ number_format($countSubmitted) }}</span></td>
                                            <td class="align-middle"><span class="badge badge-success">{{ number_format($countApproved) }}</span></td>
                                            <td class="align-middle"><span class="badge badge-danger">{{ number_format($countRejected) }}</span></td>
                                            <td class="align-middle"><span class="badge badge-dark">{{ number_format($countCancelled) }}</span></td>
                                            <td class="align-middle"><span class="badge badge-info">{{ number_format($countClosed) }}</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-3">
                    <div class="card card-outline card-light">
                        <div class="card-body py-3">
                            <div class="row text-center">
                                <div class="col-6 col-md-3 mb-3 mb-md-0">
                                    <div class="text-muted text-uppercase small">Orders this month</div>
                                    <div class="h4 mb-0">{{ number_format($thisMonthOrders) }}</div>
                                    <small class="text-muted">vs {{ number_format($lastMonthOrders) }}
                                        @if ($ordersMonthGrowthPct != 0)
                                            <span class="{{ $ordersMonthGrowthPct >= 0 ? 'text-success' : 'text-danger' }}">({{ $ordersMonthGrowthPct >= 0 ? '+' : '' }}{{ $ordersMonthGrowthPct }}%)</span>
                                        @endif
                                    </small>
                                </div>
                                <div class="col-6 col-md-3 mb-3 mb-md-0">
                                    <div class="text-muted text-uppercase small">Awaiting receipt</div>
                                    <div class="h4 mb-0 text-warning">{{ number_format($approvedAwaitingReceipt) }}</div>
                                    <small class="text-muted">Approved with outstanding qty</small>
                                </div>
                                <div class="col-6 col-md-3 mb-3 mb-md-0">
                                    <div class="text-muted text-uppercase small">Stock In ({{ now()->format('M Y') }})</div>
                                    <div class="h4 mb-0 text-success">{{ number_format($stockInThisMonth) }}</div>
                                    <small class="text-muted">Documents this month</small>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-muted text-uppercase small">Stock Out ({{ now()->format('M Y') }})</div>
                                    <div class="h4 mb-0 text-danger">{{ number_format($stockOutThisMonth) }}</div>
                                    <small class="text-muted">Documents this month</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="card card-outline card-primary h-100">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-project-diagram mr-1"></i> Top projects by orders</h3>
                        </div>
                        <div class="card-body p-0">
                            @if ($byProject->isEmpty())
                                <p class="text-muted p-3 mb-0">No supply order data yet.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Project</th>
                                                <th class="text-right">Orders</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($byProject as $row)
                                                <tr>
                                                    <td>{{ $row->project_code }} — {{ display_text($row->project_name) }}</td>
                                                    <td class="text-right">{{ number_format($row->order_count) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <div class="card card-outline card-warning h-100">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-fire mr-1"></i> Top consumed items (30 days)</h3>
                        </div>
                        <div class="card-body p-0">
                            @if ($topConsumed->isEmpty())
                                <p class="text-muted p-3 mb-0">No stock out activity in the last 30 days.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Name</th>
                                                <th class="text-right">Qty out</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($topConsumed as $row)
                                                <tr>
                                                    <td><code>{{ $row->code }}</code></td>
                                                    <td>{{ display_text($row->name) }}</td>
                                                    <td class="text-right">{{ number_format($row->total_out) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="card card-outline card-info h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-file-invoice mr-1"></i> Recent orders</h3>
                            @can('supplies.orders.show')
                                <a href="{{ route('supplies.orders.index') }}" class="btn btn-xs btn-outline-primary">View all</a>
                            @endcan
                        </div>
                        <div class="card-body p-0">
                            @if ($recentOrders->isEmpty())
                                <p class="text-muted p-3 mb-0">No orders yet.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Order No</th>
                                                <th>Project</th>
                                                <th>Status</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($recentOrders as $order)
                                                <tr>
                                                    <td>{{ $order->order_number }}</td>
                                                    <td>{{ $order->project->project_code ?? '—' }}</td>
                                                    <td><span class="badge badge-light">{{ $order->statusLabel() }}</span></td>
                                                    <td class="text-right">
                                                        <a href="{{ route('supplies.orders.show', $order) }}" class="btn btn-xs btn-outline-info"><i class="fas fa-eye"></i></a>
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

                <div class="col-lg-6 mb-3">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="card card-outline card-success">
                                <div class="card-header d-flex justify-content-between align-items-center py-2">
                                    <h3 class="card-title mb-0 text-sm"><i class="fas fa-dolly mr-1"></i> Recent Stock In</h3>
                                    @can('supplies.stock-in.show')
                                        <a href="{{ route('supplies.stock-ins.index') }}" class="btn btn-xs btn-outline-success">View all</a>
                                    @endcan
                                </div>
                                <div class="card-body p-0">
                                    @forelse ($recentStockIns as $si)
                                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                            <div>
                                                <strong>{{ $si->document_number }}</strong>
                                                <small class="text-muted d-block">{{ $si->stock_date?->format('d/m/Y') }} · {{ $si->project->project_code ?? '—' }}</small>
                                            </div>
                                            <a href="{{ route('supplies.stock-ins.show', $si) }}" class="btn btn-xs btn-outline-success"><i class="fas fa-eye"></i></a>
                                        </div>
                                    @empty
                                        <p class="text-muted p-3 mb-0 small">No stock in documents yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card card-outline card-danger">
                                <div class="card-header d-flex justify-content-between align-items-center py-2">
                                    <h3 class="card-title mb-0 text-sm"><i class="fas fa-shipping-fast mr-1"></i> Recent Stock Out</h3>
                                    @can('supplies.stock-out.show')
                                        <a href="{{ route('supplies.stock-outs.index') }}" class="btn btn-xs btn-outline-danger">View all</a>
                                    @endcan
                                </div>
                                <div class="card-body p-0">
                                    @forelse ($recentStockOuts as $so)
                                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                            <div>
                                                <strong>{{ $so->document_number }}</strong>
                                                <small class="text-muted d-block">{{ $so->stock_date?->format('d/m/Y') }} · {{ $so->project->project_code ?? '—' }}</small>
                                            </div>
                                            <a href="{{ route('supplies.stock-outs.show', $so) }}" class="btn btn-xs btn-outline-danger"><i class="fas fa-eye"></i></a>
                                        </div>
                                    @empty
                                        <p class="text-muted p-3 mb-0 small">No stock out documents yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($pendingApprovalSteps > 0)
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>{{ number_format($pendingApprovalSteps) }}</strong> open supply order approval step(s) pending action.
                            <a href="{{ route('approval-requests.index') }}" class="alert-link ml-1">Go to approvals</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
