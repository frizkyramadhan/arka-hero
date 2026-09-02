@php
    $cardClass = $cardClass ?? 'travel-card';
    $showStockInHistory = $showStockInHistory ?? false;
    $totalOrdered = $order->items->sum('quantity_ordered');
    $totalReceived = $order->items->sum(fn ($line) => $line->quantityReceived());
    $totalOutstanding = $order->items->sum(fn ($line) => $line->quantityOutstanding());
@endphp

<div class="{{ $cardClass }} {{ $cardClass === 'document-card' ? 'document-info-card' : '' }}">
    <div class="card-head">
        <h2><i class="fas fa-info-circle"></i> Order Information</h2>
    </div>
    <div class="card-body p-0">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-icon" style="background-color: #3498db;">
                    <i class="fas fa-hashtag"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Order No</div>
                    <div class="info-value">{{ $order->order_number }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon" style="background-color: #2ecc71;">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Project</div>
                    <div class="info-value">
                        {{ $order->project->project_code ?? '—' }}
                        @if ($order->project?->project_name)
                            — {{ display_text($order->project->project_name) }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon" style="background-color: #f39c12;">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Order Date</div>
                    <div class="info-value">
                        {{ $order->order_date ? format_date_with_weekday($order->order_date) : '—' }}
                    </div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon" style="background-color: #34495e;">
                    <i class="fas fa-building"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Department</div>
                    <div class="info-value">{{ display_text($order->department->department_name ?? null) }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon" style="background-color: #9b59b6;">
                    <i class="fas fa-user"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Requester</div>
                    <div class="info-value">{{ display_text($order->requestedBy->name ?? null) }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon" style="background-color: #1abc9c;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Created at</div>
                    <div class="info-value">
                        {{ $order->created_at ? format_datetime_with_weekday($order->created_at) : '—' }}
                    </div>
                </div>
            </div>
            @if ($order->submitted_at)
                <div class="info-item">
                    <div class="info-icon" style="background-color: #e67e22;">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Submitted at</div>
                        <div class="info-value">{{ format_datetime_with_weekday($order->submitted_at) }}</div>
                    </div>
                </div>
            @endif
            @if ($order->approved_at)
                <div class="info-item">
                    <div class="info-icon" style="background-color: #28a745;">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Approved at</div>
                        <div class="info-value">{{ format_datetime_with_weekday($order->approved_at) }}</div>
                    </div>
                </div>
            @endif
            @if ($order->closed_at)
                <div class="info-item">
                    <div class="info-icon" style="background-color: #17a2b8;">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Closed at</div>
                        <div class="info-value">{{ format_datetime_with_weekday($order->closed_at) }}</div>
                    </div>
                </div>
            @endif
        </div>
        @if (filled($order->notes))
            <div class="overtime-remarks-block border-top">
                <div class="info-item overtime-remarks-item">
                    <div class="info-icon" style="background-color: #95a5a6;">
                        <i class="fas fa-comment-alt"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Notes</div>
                        <div class="info-value font-weight-normal">{{ display_text($order->notes, '') }}</div>
                    </div>
                </div>
            </div>
        @endif
        @if (filled($order->rejection_reason))
            <div class="overtime-remarks-block border-top">
                <div class="info-item overtime-remarks-item">
                    <div class="info-icon" style="background-color: #dc3545;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Rejection reason</div>
                        <div class="info-value font-weight-normal text-danger">{{ display_text($order->rejection_reason, '') }}</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="{{ $cardClass }}">
    <div class="card-head d-flex justify-content-between align-items-center">
        <h2 class="mb-0"><i class="fas fa-boxes"></i> Order Items</h2>
        <span class="badge badge-light">{{ $order->items->count() }} line(s)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center" style="width: 4%">No</th>
                        <th style="width: 12%">Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th class="text-center" style="width: 8%">Qty</th>
                        <th class="text-center" style="width: 9%">Received</th>
                        <th class="text-center" style="width: 10%">Outstanding</th>
                        <th style="width: 16%">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($order->items as $i => $line)
                        @php
                            $outstanding = $line->quantityOutstanding();
                            $received = $line->quantityReceived();
                        @endphp
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td><code>{{ $line->item->code ?? '—' }}</code></td>
                            <td>{{ display_text($line->item->name ?? null) }}</td>
                            <td class="text-muted">{{ display_text($line->item->description ?? null, '—') }}</td>
                            <td class="text-center">{{ $line->quantity_ordered }}</td>
                            <td class="text-center">
                                @if ($received > 0)
                                    <span class="text-success font-weight-bold">{{ $received }}</span>
                                @else
                                    {{ $received }}
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($outstanding > 0)
                                    <span class="badge badge-warning">{{ $outstanding }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>{{ display_text($line->remarks ?? null, '—') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No items on this order.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($order->items->isNotEmpty())
                    <tfoot class="thead-light">
                        <tr>
                            <th colspan="4" class="text-right">Total</th>
                            <th class="text-center">{{ $totalOrdered }}</th>
                            <th class="text-center">{{ $totalReceived }}</th>
                            <th class="text-center">{{ $totalOutstanding }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@if ($showStockInHistory && $order->relationLoaded('stockIns') && $order->stockIns->isNotEmpty())
    <div class="{{ $cardClass }}">
        <div class="card-head">
            <h2><i class="fas fa-dolly"></i> Stock In History</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>SI No</th>
                            <th>Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->stockIns->sortByDesc('stock_date') as $stockIn)
                            <tr>
                                <td>{{ $stockIn->document_number }}</td>
                                <td>{{ $stockIn->stock_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('supplies.stock-ins.show', $stockIn) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
