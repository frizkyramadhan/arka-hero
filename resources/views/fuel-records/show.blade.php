@extends('layouts.main')

@section('title', $title)

@section('content')
    @php
        $statusMap = [
            'submitted' => [
                'label' => 'Submitted',
                'class' => 'overtime-pill-pending',
                'icon' => 'fa-clock',
                'tone' => 'pending',
                'hint' => 'Waiting for office verification',
            ],
            'verified' => [
                'label' => 'Verified',
                'class' => 'overtime-pill-approved',
                'icon' => 'fa-check-circle',
                'tone' => 'approved',
                'hint' => 'Ready to include in a fuel claim',
            ],
            'rejected' => [
                'label' => 'Rejected',
                'class' => 'overtime-pill-rejected',
                'icon' => 'fa-times-circle',
                'tone' => 'rejected',
                'hint' => 'Driver can edit and resubmit',
            ],
            'claimed' => [
                'label' => 'Claimed',
                'class' => 'overtime-pill-finished',
                'icon' => 'fa-file-invoice-dollar',
                'tone' => 'claimed',
                'hint' => 'Bundled into a fuel claim',
            ],
        ];
        $pill = $statusMap[$fuelRecord->status] ?? [
            'label' => ucfirst((string) $fuelRecord->status),
            'class' => 'overtime-pill-draft',
            'icon' => 'fa-question-circle',
            'tone' => 'draft',
            'hint' => '',
        ];
        $vehicle = $fuelRecord->vehicle;
        $vehicleLabel = $vehicle
            ? $vehicle->kode.' — '.$vehicle->license_plate
            : 'Unknown vehicle';
        $driverName = null;
        if ($fuelRecord->driver) {
            $driverName = $fuelRecord->driver->fullname
                ?? trim(($fuelRecord->driver->first_name ?? '').' '.($fuelRecord->driver->last_name ?? ''));
            $driverName = $driverName !== '' ? $driverName : null;
        }
        $driverName = $driverName ?: (optional($fuelRecord->creator)->name ?? null);
    @endphp

    @include('partials.official-travel-detail-styles')

    <style>
        .fuel-receipt-preview {
            max-width: 100%;
            max-height: 480px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #eef2f5;
            background: #f8f9fa;
        }

        .fuel-delete-btn {
            background-color: #dc3545;
        }

        .fuel-delete-btn:hover {
            color: white;
        }

        .fuel-verify-btn {
            background-color: #27ae60;
        }

        .fuel-verify-btn:hover {
            color: white;
        }

        .fuel-verify-card .card-body {
            padding: 0.9rem 1rem;
        }

        .fuel-verify-status {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.9rem 1rem;
            border-radius: 0.75rem;
            margin-bottom: 0.95rem;
            border: 1px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .fuel-verify-status::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
        }

        .fuel-verify-status.is-approved {
            background: linear-gradient(135deg, #f0faf4 0%, #e8f7ee 100%);
            border-color: #c9ebd5;
        }

        .fuel-verify-status.is-approved::before {
            background: #28a745;
        }

        .fuel-verify-status.is-pending {
            background: linear-gradient(135deg, #fff8f0 0%, #fff1e3 100%);
            border-color: #f5d9b8;
        }

        .fuel-verify-status.is-pending::before {
            background: #e67e22;
        }

        .fuel-verify-status.is-rejected {
            background: linear-gradient(135deg, #fff5f5 0%, #fdeaea 100%);
            border-color: #f0c7c7;
        }

        .fuel-verify-status.is-rejected::before {
            background: #dc3545;
        }

        .fuel-verify-status.is-claimed {
            background: linear-gradient(135deg, #f0f9fb 0%, #e4f4f8 100%);
            border-color: #bfe3ec;
        }

        .fuel-verify-status.is-claimed::before {
            background: #17a2b8;
        }

        .fuel-verify-status.is-draft {
            background: linear-gradient(135deg, #f8f9fa 0%, #eef1f4 100%);
            border-color: #dde2e7;
        }

        .fuel-verify-status.is-draft::before {
            background: #6c757d;
        }

        .fuel-verify-status-icon {
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.1rem;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        .fuel-verify-status.is-approved .fuel-verify-status-icon {
            color: #1e7e34;
            background: #d4edda;
        }

        .fuel-verify-status.is-pending .fuel-verify-status-icon {
            color: #c45f0a;
            background: #ffe8cc;
        }

        .fuel-verify-status.is-rejected .fuel-verify-status-icon {
            color: #bd2130;
            background: #f8d7da;
        }

        .fuel-verify-status.is-claimed .fuel-verify-status-icon {
            color: #117a8b;
            background: #d1ecf1;
        }

        .fuel-verify-status.is-draft .fuel-verify-status-icon {
            color: #5a6268;
            background: #e2e6ea;
        }

        .fuel-verify-status-body {
            min-width: 0;
            flex: 1;
        }

        .fuel-verify-status-label {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #8a97a0;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .fuel-verify-status-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #243447;
            line-height: 1.25;
            margin: 0;
        }

        .fuel-verify-status-hint {
            font-size: 0.78rem;
            color: #7b8a96;
            margin: 0.2rem 0 0;
            line-height: 1.35;
        }

        .fuel-verify-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.28rem 0.7rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .fuel-verify-status.is-approved .fuel-verify-badge {
            background: #28a745;
            color: #fff;
        }

        .fuel-verify-status.is-pending .fuel-verify-badge {
            background: #e67e22;
            color: #fff;
        }

        .fuel-verify-status.is-rejected .fuel-verify-badge {
            background: #dc3545;
            color: #fff;
        }

        .fuel-verify-status.is-claimed .fuel-verify-badge {
            background: #17a2b8;
            color: #fff;
        }

        .fuel-verify-status.is-draft .fuel-verify-badge {
            background: #6c757d;
            color: #fff;
        }

        .fuel-verify-row {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            padding: 0.6rem 0;
            border-bottom: 1px solid #f0f3f6;
        }

        .fuel-verify-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .fuel-verify-icon {
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex-shrink: 0;
            font-size: 0.85rem;
        }

        .fuel-verify-meta {
            min-width: 0;
            flex: 1;
        }

        .fuel-verify-meta .info-label {
            margin-bottom: 0.1rem;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #95a5a6;
        }

        .fuel-verify-meta .info-value {
            font-size: 0.92rem;
            font-weight: 600;
            color: #2c3e50;
            word-break: break-word;
        }
    </style>

    <div class="content-wrapper-custom">
        <div class="travel-header">
            <div class="travel-header-content">
                <div class="travel-number">
                    Fuel Record
                </div>
                <h1 class="travel-destination">
                    {{ $vehicleLabel }}
                </h1>
                <div class="travel-date">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    {{ optional($fuelRecord->fuel_date)->format('d F Y') ?: '—' }}
                    <span class="mx-2">·</span>
                    <i class="fas fa-tachometer-alt mr-1"></i>
                    {{ number_format((int) $fuelRecord->odometer) }} km
                    <span class="mx-2">·</span>
                    <i class="fas fa-gas-pump mr-1"></i>
                    {{ number_format((float) $fuelRecord->quantity, 2) }} L
                    @if ($driverName)
                        <span class="mx-2">·</span>
                        <i class="fas fa-user mr-1"></i>
                        {{ $driverName }}
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
                {{-- Left Column --}}
                <div class="col-lg-8">
                    <div class="travel-card travel-info-card">
                        <div class="card-head">
                            <h2><i class="fas fa-gas-pump"></i> Fuel Details</h2>
                        </div>
                        <div class="card-body p-0">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #3498db;">
                                        <i class="fas fa-car"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Vehicle</div>
                                        <div class="info-value">
                                            @if ($vehicle)
                                                <a href="{{ route('vehicles.show', $vehicle) }}">{{ $vehicleLabel }}</a>
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e67e22;">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Date</div>
                                        <div class="info-value">{{ optional($fuelRecord->fuel_date)->format('Y-m-d') ?: '—' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #7f8c8d;">
                                        <i class="fas fa-tachometer-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Odometer</div>
                                        <div class="info-value">{{ number_format((int) $fuelRecord->odometer) }} km</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e74c3c;">
                                        <i class="fas fa-oil-can"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Fuel Type</div>
                                        <div class="info-value">{{ $fuelRecord->fuel_type ?: '—' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #2ecc71;">
                                        <i class="fas fa-tint"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Quantity</div>
                                        <div class="info-value">{{ number_format((float) $fuelRecord->quantity, 2) }} L</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #9b59b6;">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Price / Liter</div>
                                        <div class="info-value">Rp {{ number_format((float) $fuelRecord->price_per_liter, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #16a085;">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Total Cost</div>
                                        <div class="info-value">Rp {{ number_format((float) $fuelRecord->total_cost, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #2980b9;">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Fuel Station</div>
                                        <div class="info-value">{{ $fuelRecord->fuel_station ?: '—' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #34495e;">
                                        <i class="fas fa-receipt"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">No. Trans / Receipt No.</div>
                                        <div class="info-value">{{ $fuelRecord->receipt_number ?: '—' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #8e44ad;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Driver</div>
                                        <div class="info-value">{{ $driverName ?: '—' }}</div>
                                    </div>
                                </div>
                            </div>

                            @if ($fuelRecord->notes)
                                <div class="overtime-remarks-block border-top">
                                    <div class="info-item overtime-remarks-item">
                                        <div class="info-icon" style="background-color: #1abc9c;">
                                            <i class="fas fa-comment-alt"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Notes</div>
                                            <div class="info-value overtime-remarks-value">{{ $fuelRecord->notes }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="travel-card">
                        <div class="card-head">
                            <h2><i class="fas fa-image"></i> Receipt</h2>
                        </div>
                        <div class="card-body text-center">
                            @if ($fuelRecord->receipt_image)
                                @php
                                    $ext = strtolower(pathinfo($fuelRecord->receipt_image, PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
                                @endphp
                                @if ($isImage)
                                    <a href="{{ route('fuel-records.receipt', $fuelRecord) }}" target="_blank">
                                        <img src="{{ route('fuel-records.receipt', $fuelRecord) }}"
                                            alt="Receipt" class="fuel-receipt-preview mb-3">
                                    </a>
                                @else
                                    <div class="py-4">
                                        <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                        <p class="text-muted mb-0">PDF receipt</p>
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('fuel-records.receipt', $fuelRecord) }}" target="_blank"
                                        class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-external-link-alt"></i> Open full size
                                    </a>
                                </div>
                            @else
                                <p class="text-muted mb-0 py-4">No receipt image</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Right Column: verification + actions --}}
                <div class="col-lg-4">
                    <div class="travel-card fuel-verify-card">
                        <div class="card-head">
                            <h2><i class="fas fa-clipboard-check"></i> Verification & Claim</h2>
                        </div>
                        <div class="card-body">
                            <div class="fuel-verify-status is-{{ $pill['tone'] }}">
                                <div class="fuel-verify-status-icon">
                                    <i class="fas {{ $pill['icon'] }}"></i>
                                </div>
                                <div class="fuel-verify-status-body">
                                    <div class="fuel-verify-status-label">Current status</div>
                                    <p class="fuel-verify-status-title">{{ $pill['label'] }}</p>
                                    @if (! empty($pill['hint']))
                                        <p class="fuel-verify-status-hint">{{ $pill['hint'] }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="fuel-verify-row">
                                <div class="fuel-verify-icon" style="background-color: #27ae60;">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div class="fuel-verify-meta">
                                    <div class="info-label">Verified by</div>
                                    <div class="info-value">{{ optional($fuelRecord->verifier)->name ?: '—' }}</div>
                                </div>
                            </div>

                            <div class="fuel-verify-row">
                                <div class="fuel-verify-icon" style="background-color: #2980b9;">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="fuel-verify-meta">
                                    <div class="info-label">Verified / Rejected at</div>
                                    <div class="info-value">
                                        {{ optional($fuelRecord->verified_at ?? $fuelRecord->rejected_at)->format('d M Y H:i') ?: '—' }}
                                    </div>
                                </div>
                            </div>

                            <div class="fuel-verify-row">
                                <div class="fuel-verify-icon" style="background-color: #f39c12;">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div class="fuel-verify-meta">
                                    <div class="info-label">Fuel Claim</div>
                                    <div class="info-value">
                                        @if ($fuelRecord->claim)
                                            <a href="{{ route('fuel-claims.show', $fuelRecord->claim) }}">
                                                {{ $fuelRecord->claim->claim_number }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="fuel-verify-row">
                                <div class="fuel-verify-icon" style="background-color: {{ $fuelRecord->status === 'rejected' ? '#e74c3c' : '#1abc9c' }};">
                                    <i class="fas fa-sticky-note"></i>
                                </div>
                                <div class="fuel-verify-meta">
                                    <div class="info-label">Verification notes</div>
                                    <div class="info-value">{{ $fuelRecord->verification_notes ?: '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="travel-action-buttons">
                        <a href="{{ route('fuel-records.index') }}" class="btn-action back-btn">
                            <i class="fas fa-arrow-left"></i>
                            Back to List
                        </a>

                        @can('fuel-records.edit')
                            @if ($fuelRecord->status !== 'claimed')
                                <a href="{{ route('fuel-records.edit', $fuelRecord) }}" class="btn-action edit-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            @endif
                        @endcan

                        @can('fuel-records.verify')
                            @if ($fuelRecord->status === 'submitted')
                                <form method="POST" action="{{ route('fuel-records.verify', $fuelRecord) }}"
                                    class="confirm-submit"
                                    data-confirm-title="Verify fuel record?"
                                    data-confirm-message="Mark this fuel receipt as verified?"
                                    data-confirm-yes="Yes, verify"
                                    data-confirm-no="Cancel"
                                    data-confirm-icon="question">
                                    @csrf
                                    <button type="submit" class="btn-action fuel-verify-btn w-100">
                                        <i class="fas fa-check"></i> Verify
                                    </button>
                                </form>
                                <form id="reject-fuel-form" method="POST"
                                    action="{{ route('fuel-records.reject', $fuelRecord) }}" class="d-none">
                                    @csrf
                                    <input type="hidden" name="verification_notes" id="reject-verification-notes">
                                </form>
                                <button type="button" id="btn-reject-fuel"
                                    class="btn-action fuel-delete-btn w-100">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            @endif
                        @endcan

                        @can('fuel-records.delete')
                            @if ($fuelRecord->status !== 'claimed')
                                <form method="POST" action="{{ route('fuel-records.destroy', $fuelRecord) }}"
                                    class="confirm-submit"
                                    data-confirm-title="Delete fuel record?"
                                    data-confirm-message="Are you sure you want to delete this fuel record?"
                                    data-confirm-yes="Yes, delete"
                                    data-confirm-no="Cancel"
                                    data-confirm-icon="warning">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action fuel-delete-btn w-100">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            @endif
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

            $('#btn-reject-fuel').on('click', function() {
                const form = document.getElementById('reject-fuel-form');
                if (!form || form.dataset.submitting === 'true') {
                    return;
                }

                const submitReject = (notes) => {
                    $('#reject-verification-notes').val(notes);
                    form.dataset.submitting = 'true';
                    if (typeof toast_info === 'function') {
                        toast_info('Processing...');
                    }
                    form.submit();
                };

                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        title: 'Reject fuel record?',
                        text: 'Please provide a reason for rejection.',
                        icon: 'warning',
                        input: 'textarea',
                        inputPlaceholder: 'Rejection reason...',
                        inputAttributes: {
                            'aria-label': 'Rejection reason'
                        },
                        showCancelButton: true,
                        confirmButtonColor: '#e74c3c',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, reject',
                        cancelButtonText: 'Cancel',
                        inputValidator: (value) => {
                            if (!value || !String(value).trim()) {
                                return 'Reason is required';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitReject(String(result.value).trim());
                        }
                    });
                } else {
                    const notes = prompt('Rejection reason:');
                    if (notes && notes.trim()) {
                        submitReject(notes.trim());
                    }
                }
            });
        });
    </script>
@endsection
