@extends('layouts.main')

@section('title', $title)

@section('content')
    @php
        $totalQuantity = $stockOut->items->sum('quantity');
        $lineCount = $stockOut->items->count();
    @endphp

    @include('partials.official-travel-detail-styles')

    <style>
        .so-delete-btn {
            background-color: #dc3545;
        }

        .so-delete-btn:hover {
            color: #fff;
        }

        @media (max-width: 767.98px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="content-wrapper-custom">
        <div class="travel-header">
            <div class="travel-header-content">
                <div class="travel-number">STOCK OUT</div>
                <h1 class="travel-destination">{{ $stockOut->document_number }}</h1>
                <div class="travel-date">
                    <i class="far fa-calendar-alt"></i>
                    {{ $stockOut->stock_date ? format_date_with_weekday($stockOut->stock_date) : '—' }}
                    <span class="mx-2">·</span>
                    <i class="fas fa-project-diagram"></i>
                    {{ $stockOut->project->project_code ?? '—' }}
                    @if ($stockOut->project?->project_name)
                        — {{ display_text($stockOut->project->project_name) }}
                    @endif
                </div>
            </div>
            <div class="travel-status-pill">
                <span class="overtime-status-pill overtime-pill-approved">
                    <i class="fas fa-check-circle"></i> Recorded
                </span>
            </div>
        </div>

        <div class="travel-content">
            <div class="row">
                <div class="col-lg-8">
                    @include('supplies.stock-outs._detail-content', [
                        'stockOut' => $stockOut,
                        'cardClass' => 'travel-card',
                    ])
                </div>

                <div class="col-lg-4">
                    <div class="travel-card mb-3">
                        <div class="card-head">
                            <h2><i class="fas fa-chart-pie"></i> Summary</h2>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Line items</span>
                                <strong>{{ $lineCount }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total quantity out</span>
                                <strong class="text-danger">{{ $totalQuantity }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="travel-action-buttons">
                        <a href="{{ route('supplies.stock-outs.index') }}" class="btn-action back-btn">
                            <i class="fas fa-arrow-left"></i> Back to list
                        </a>

                        <a href="{{ route('supplies.stock-outs.print', $stockOut) }}" target="_blank" class="btn-action print-btn">
                            <i class="fas fa-print"></i> Print
                        </a>

                        @can('supplies.stock-out.delete')
                            <form method="POST" action="{{ route('supplies.stock-outs.destroy', $stockOut) }}"
                                class="confirm-submit"
                                data-confirm-message="Delete this Stock Out? Stock balances will be reversed."
                                data-confirm-yes="Yes, delete"
                                data-confirm-icon="warning">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn-action so-delete-btn w-100">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            $(document).on('submit', 'form.confirm-submit', function(e) {
                const form = this;
                if (form.dataset.submitting === 'true') {
                    return;
                }
                e.preventDefault();

                const message = form.getAttribute('data-confirm-message') || 'Continue with this action?';
                const title = form.getAttribute('data-confirm-title') || 'Confirm';
                const confirmText = form.getAttribute('data-confirm-yes') || 'Yes';
                const cancelText = form.getAttribute('data-confirm-no') || 'Cancel';
                const icon = form.getAttribute('data-confirm-icon') || 'warning';

                const proceed = () => {
                    form.dataset.submitting = 'true';
                    if (typeof toast_info === 'function') {
                        toast_info('Processing...');
                    }
                    form.submit();
                };

                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        title: title,
                        text: message,
                        icon: icon,
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: confirmText,
                        cancelButtonText: cancelText,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            proceed();
                        }
                    });
                } else if (confirm(message)) {
                    proceed();
                }
            });
        });
    </script>
@endsection
