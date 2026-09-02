@extends('layouts.main')

@section('title', $title)

@section('content')
    @php
        $listRoute = $isPersonal ? route('supplies.orders.my-orders') : route('supplies.orders.index');
        $editRoute = $isPersonal ? route('supplies.orders.my-orders.edit', $order) : route('supplies.orders.edit', $order);
        $submitRoute = $isPersonal ? route('supplies.orders.my-orders.submit', $order) : route('supplies.orders.submit', $order);
        $printRoute = $isPersonal ? route('supplies.orders.my-orders.print', $order) : route('supplies.orders.print', $order);
        $hasOutstanding = $order->items->contains(fn ($line) => $line->quantityOutstanding() > 0);

        $statusMap = [
            'draft' => ['label' => 'Draft', 'class' => 'overtime-pill-draft', 'icon' => 'fa-edit'],
            'submitted' => ['label' => 'Submitted', 'class' => 'overtime-pill-pending', 'icon' => 'fa-paper-plane'],
            'approved' => ['label' => 'Approved', 'class' => 'overtime-pill-approved', 'icon' => 'fa-check-circle'],
            'rejected' => ['label' => 'Rejected', 'class' => 'overtime-pill-rejected', 'icon' => 'fa-times-circle'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'overtime-pill-draft', 'icon' => 'fa-ban'],
            'closed' => ['label' => 'Closed', 'class' => 'overtime-pill-finished', 'icon' => 'fa-flag-checkered'],
        ];
        $pill = $statusMap[$order->status] ?? [
            'label' => $order->statusLabel(),
            'class' => 'overtime-pill-draft',
            'icon' => 'fa-question-circle',
        ];

        $canEdit = $order->canBeEditedBy(auth()->user());
        $canSubmit = $canEdit && $order->canSubmitForApproval();
        $canCancelPersonal = $isPersonal && $order->canCancel() && auth()->user()?->can('personal.supplies.orders.cancel-own');
        $canCancelAdmin = ! $isPersonal && $order->canCancel() && auth()->user()?->can('supplies.orders.delete');
        $canRecordStockIn = ! $isPersonal && $order->canReceive() && $hasOutstanding && auth()->user()?->can('supplies.stock-in.create');
        $canClose = ! $isPersonal && $order->canClose() && auth()->user()?->can('supplies.orders.close');

        $totalOrdered = $order->items->sum('quantity_ordered');
        $totalReceived = $order->items->sum(fn ($line) => $line->quantityReceived());
        $totalOutstanding = $order->items->sum(fn ($line) => $line->quantityOutstanding());
    @endphp

    @include('partials.official-travel-detail-styles')

    <style>
        .so-stock-in-btn {
            background-color: #f39c12;
        }

        .so-stock-in-btn:hover {
            color: #fff;
        }

        .so-close-btn {
            background-color: #6c757d;
        }

        .so-close-btn:hover {
            color: #fff;
        }

        .so-cancel-btn {
            background-color: #e67e22;
        }

        .so-cancel-btn:hover {
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
                <div class="travel-number">{{ $isPersonal ? 'OFFICE SUPPLY ORDER' : 'SUPPLY ORDER' }}</div>
                <h1 class="travel-destination">{{ $order->order_number }}</h1>
                <div class="travel-date">
                    <i class="far fa-calendar-alt"></i>
                    {{ $order->order_date ? format_date_with_weekday($order->order_date) : '—' }}
                    <span class="mx-2">·</span>
                    <i class="fas fa-project-diagram"></i>
                    {{ $order->project->project_code ?? '—' }}
                    @if ($order->project?->project_name)
                        — {{ display_text($order->project->project_name) }}
                    @endif
                </div>
            </div>
            <div class="travel-status-pill">
                <span class="overtime-status-pill {{ $pill['class'] }}">
                    <i class="fas {{ $pill['icon'] }}"></i> {{ $pill['label'] }}
                </span>
            </div>
        </div>

        <div class="travel-content">
            <div class="row">
                <div class="col-lg-8">
                    @include('supplies.orders._detail-content', [
                        'order' => $order,
                        'cardClass' => 'travel-card',
                        'showStockInHistory' => ! $isPersonal,
                    ])
                </div>

                <div class="col-lg-4">
                    @if ($order->status !== 'draft' && (! empty($order->manual_approvers) || $order->approvalPlans->isNotEmpty()))
                        <div class="travel-card mb-3">
                            <div class="card-head">
                                <h2><i class="fas fa-users"></i> Approval Status</h2>
                            </div>
                            <div class="card-body py-2">
                                @include('components.manual-approver-selector', [
                                    'selectedApprovers' => $order->manual_approvers ?? [],
                                    'documentType' => 'supply_order',
                                    'documentId' => $order->id,
                                    'mode' => 'view',
                                    'required' => false,
                                ])
                            </div>
                        </div>
                    @endif

                    <div class="travel-card mb-3">
                        <div class="card-head">
                            <h2><i class="fas fa-chart-pie"></i> Fulfillment</h2>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total ordered</span>
                                <strong>{{ $totalOrdered }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total received</span>
                                <strong class="text-success">{{ $totalReceived }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Outstanding</span>
                                <strong class="{{ $totalOutstanding > 0 ? 'text-warning' : 'text-muted' }}">{{ $totalOutstanding }}</strong>
                            </div>
                            @if ($order->status === 'approved' && $totalOrdered > 0)
                                @php
                                    $pct = min(100, round(($totalReceived / $totalOrdered) * 100));
                                @endphp
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted d-block mt-1">{{ $pct }}% received</small>
                            @endif
                        </div>
                    </div>

                    <div class="travel-action-buttons">
                        <a href="{{ $listRoute }}" class="btn-action back-btn">
                            <i class="fas fa-arrow-left"></i>
                            {{ $isPersonal ? 'Back to my list' : 'Back to list' }}
                        </a>

                        <a href="{{ $printRoute }}" target="_blank" class="btn-action print-btn">
                            <i class="fas fa-print"></i> Print
                        </a>

                        @if ($canEdit)
                            <a href="{{ $editRoute }}" class="btn-action edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif

                        @if ($canSubmit)
                            <form method="POST" action="{{ $submitRoute }}"
                                class="confirm-submit"
                                data-confirm-message="Submit this Supply Order for approval?"
                                data-confirm-yes="Yes, submit">
                                @csrf
                                <button type="submit" class="btn-action submit-approval-btn w-100">
                                    <i class="fas fa-paper-plane"></i> Submit for Approval
                                </button>
                            </form>
                        @endif

                        @if ($canRecordStockIn)
                            <a href="{{ route('supplies.stock-ins.create', ['supply_order_id' => $order->id]) }}"
                                class="btn-action so-stock-in-btn">
                                <i class="fas fa-dolly"></i> Record Stock In
                            </a>
                        @endif

                        @if ($canClose)
                            <form method="POST" action="{{ route('supplies.orders.close', $order) }}"
                                class="confirm-submit"
                                data-confirm-message="Close this Supply Order?"
                                data-confirm-yes="Yes, close"
                                data-confirm-icon="warning">
                                @csrf
                                <button type="submit" class="btn-action so-close-btn w-100">
                                    <i class="fas fa-lock"></i> Close Order
                                </button>
                            </form>
                        @endif

                        @if ($canCancelPersonal)
                            <form method="POST" action="{{ route('supplies.orders.my-orders.cancel', $order) }}"
                                class="confirm-submit"
                                data-confirm-message="Cancel this Supply Order?"
                                data-confirm-yes="Yes, cancel"
                                data-confirm-icon="warning">
                                @csrf
                                <button type="submit" class="btn-action so-cancel-btn w-100">
                                    <i class="fas fa-ban"></i> Cancel Order
                                </button>
                            </form>
                        @endif

                        @if ($canCancelAdmin)
                            <form method="POST" action="{{ route('supplies.orders.cancel', $order) }}"
                                class="confirm-submit"
                                data-confirm-message="Cancel this Supply Order?"
                                data-confirm-yes="Yes, cancel"
                                data-confirm-icon="warning">
                                @csrf
                                <button type="submit" class="btn-action so-cancel-btn w-100">
                                    <i class="fas fa-ban"></i> Cancel Order
                                </button>
                            </form>
                        @endif
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
