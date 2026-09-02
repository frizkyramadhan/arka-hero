@php
    $cardClass = $cardClass ?? 'travel-card';
    $totalQuantity = $stockOut->items->sum('quantity');
@endphp

<div class="{{ $cardClass }} {{ $cardClass === 'document-card' ? 'document-info-card' : '' }}">
    <div class="card-head">
        <h2><i class="fas fa-info-circle"></i> Stock Out Information</h2>
    </div>
    <div class="card-body p-0">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-icon" style="background-color: #3498db;">
                    <i class="fas fa-hashtag"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">SO No</div>
                    <div class="info-value">{{ $stockOut->document_number }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon" style="background-color: #2ecc71;">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Project</div>
                    <div class="info-value">
                        {{ $stockOut->project->project_code ?? '—' }}
                        @if ($stockOut->project?->project_name)
                            — {{ display_text($stockOut->project->project_name) }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon" style="background-color: #f39c12;">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Stock Date</div>
                    <div class="info-value">
                        {{ $stockOut->stock_date ? format_date_with_weekday($stockOut->stock_date) : '—' }}
                    </div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon" style="background-color: #34495e;">
                    <i class="fas fa-user"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Recorded by</div>
                    <div class="info-value">{{ display_text($stockOut->createdBy->name ?? null) }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon" style="background-color: #1abc9c;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Created at</div>
                    <div class="info-value">
                        {{ $stockOut->created_at ? format_datetime_with_weekday($stockOut->created_at) : '—' }}
                    </div>
                </div>
            </div>
        </div>
        @if (filled($stockOut->notes))
            <div class="overtime-remarks-block border-top">
                <div class="info-item overtime-remarks-item">
                    <div class="info-icon" style="background-color: #95a5a6;">
                        <i class="fas fa-comment-alt"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Notes</div>
                        <div class="info-value font-weight-normal">{{ display_text($stockOut->notes, '') }}</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="{{ $cardClass }}">
    <div class="card-head d-flex justify-content-between align-items-center">
        <h2 class="mb-0"><i class="fas fa-boxes"></i> Items Issued</h2>
        <span class="badge badge-light">{{ $stockOut->items->count() }} line(s)</span>
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
                        <th class="text-center" style="width: 8%">Qty out</th>
                        <th style="width: 14%">Location</th>
                        <th style="width: 14%">PIC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stockOut->items as $i => $line)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td><code>{{ $line->item->code ?? '—' }}</code></td>
                            <td>{{ display_text($line->item->name ?? null) }}</td>
                            <td class="text-muted">{{ display_text($line->item->description ?? null, '—') }}</td>
                            <td class="text-center">
                                <span class="text-danger font-weight-bold">{{ $line->quantity }}</span>
                            </td>
                            <td>{{ display_text($line->location ?? null, '—') }}</td>
                            <td>{{ display_text($line->person_in_charge ?? null, '—') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No items on this document.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($stockOut->items->isNotEmpty())
                    <tfoot class="thead-light">
                        <tr>
                            <th colspan="4" class="text-right">Total</th>
                            <th class="text-center">{{ $totalQuantity }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
