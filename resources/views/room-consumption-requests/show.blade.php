@extends('layouts.main')

@section('title', $title ?? 'Room & Consumption Request')

@section('content')
    @php
        $doc = $requestDoc;
        $fromPersonal = $isPersonal ?? false;
        $backRoute = $fromPersonal
            ? route('room-consumption-requests.my-requests')
            : route('room-consumption-requests.index');
        $editRoute = $fromPersonal
            ? route('room-consumption-requests.my-requests.edit', $doc)
            : route('room-consumption-requests.edit', $doc);
        $submitRoute = $fromPersonal
            ? route('room-consumption-requests.my-requests.submit', $doc)
            : route('room-consumption-requests.submit', $doc);
        $cancelRoute = $fromPersonal
            ? route('room-consumption-requests.my-requests.cancel', $doc)
            : route('room-consumption-requests.cancel', $doc);

        $statusMap = [
            'draft' => ['label' => 'Draft', 'class' => 'overtime-pill-draft', 'icon' => 'fa-edit'],
            'submitted' => ['label' => 'Submitted', 'class' => 'overtime-pill-pending', 'icon' => 'fa-paper-plane'],
            'approved' => ['label' => 'Approved', 'class' => 'overtime-pill-approved', 'icon' => 'fa-check-circle'],
            'rejected' => ['label' => 'Rejected', 'class' => 'overtime-pill-rejected', 'icon' => 'fa-times-circle'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'overtime-pill-draft', 'icon' => 'fa-ban'],
            'completed' => ['label' => 'Completed', 'class' => 'overtime-pill-finished', 'icon' => 'fa-flag-checkered'],
        ];
        $pill = $doc->isPendingHr()
            ? ['label' => 'Menunggu Konfirmasi HR', 'class' => 'overtime-pill-pending', 'icon' => 'fa-clock']
            : ($statusMap[$doc->status] ?? [
                'label' => ucfirst($doc->status),
                'class' => 'overtime-pill-draft',
                'icon' => 'fa-question-circle',
            ]);

        $canEdit = $fromPersonal
            ? ($doc->isPendingHr() && (int) $doc->requested_by === (int) auth()->id())
            : $doc->canBeEditedBy(auth()->user());
        $canSubmit = $canEdit && $doc->canSubmitForApproval() && ! $doc->isPendingHr();
        $canCancel = $doc->canCancel();
        $canManageZoomItWo =
            auth()->user()->can('room-consumption-requests.edit') ||
            ((int) $doc->requested_by === (int) auth()->id() &&
                auth()->user()->can('personal.room-consumption.edit-own'));
        $requestZoomRoute = $fromPersonal
            ? route('room-consumption-requests.my-requests.request-zoom', $doc)
            : route('room-consumption-requests.request-zoom', $doc);
        $syncZoomRoute = $fromPersonal
            ? route('room-consumption-requests.my-requests.sync-zoom', $doc)
            : route('room-consumption-requests.sync-zoom', $doc);
        $resetZoomItWoRoute = $fromPersonal
            ? route('room-consumption-requests.my-requests.reset-zoom-it-wo', $doc)
            : route('room-consumption-requests.reset-zoom-it-wo', $doc);
        $itWoTrialMode = empty(config('it_wo.base_url'));
        $startTime = $doc->start_time ? \Carbon\Carbon::parse($doc->start_time)->format('H:i') : '—';
        $endTime = $doc->end_time ? \Carbon\Carbon::parse($doc->end_time)->format('H:i') : '—';
    @endphp

    @include('partials.official-travel-detail-styles')

    <style>
        .rcr-print-btn {
            background-color: #6c757d;
        }

        .rcr-print-btn:hover {
            color: white;
        }

        .rcr-cancel-btn {
            background-color: #e67e22;
        }

        .rcr-cancel-btn:hover {
            color: white;
        }

        .rcr-zoom-btn {
            background-color: #2d8cff;
        }

        .rcr-zoom-btn:hover {
            color: white;
        }

        .rcr-zoom-sync-btn {
            background-color: #17a2b8;
        }

        .rcr-zoom-sync-btn:hover {
            color: white;
        }

        .rcr-zoom-reset-btn {
            background-color: #dc3545;
        }

        .rcr-zoom-reset-btn:hover {
            color: white;
        }
    </style>

    <div class="content-wrapper-custom">
        <div class="travel-header">
            <div class="travel-header-content">
                <div class="travel-number">
                    {{ $doc->project->project_code ?? 'RCR' }}
                    @if ($doc->project?->project_name)
                        — {{ $doc->project->project_name }}
                    @endif
                </div>
                <h1 class="travel-destination">
                    {{ $doc->request_number ?: ($doc->meeting_title ?: 'Draft Request') }}
                </h1>
                <div class="travel-date">
                    <i class="far fa-calendar-alt"></i>
                    {{ $doc->formattedMeetingDateRange() }}
                    <span class="mx-2">·</span>
                    <i class="far fa-clock"></i>
                    {{ $startTime }} – {{ $endTime }}
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
                            <h2><i class="fas fa-info-circle"></i> Meeting Details</h2>
                        </div>
                        <div class="card-body p-0">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #3498db;">
                                        <i class="fas fa-hashtag"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Reg. No</div>
                                        <div class="info-value">{{ $doc->request_number ?: '—' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e67e22;">
                                        <i class="fas fa-heading"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Meeting Title</div>
                                        <div class="info-value">{{ $doc->meeting_title ?: '—' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #1abc9c;">
                                        <i class="fas fa-door-open"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Room</div>
                                        <div class="info-value">{{ $doc->meetingRoom->room_name ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #2ecc71;">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Location (Project)</div>
                                        <div class="info-value">
                                            {{ $doc->project->project_code ?? '—' }}
                                            @if ($doc->project?->project_name)
                                                — {{ $doc->project->project_name }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #34495e;">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Division / Department</div>
                                        <div class="info-value">{{ $doc->department->department_name ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #f39c12;">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Meeting Dates</div>
                                        <div class="info-value">
                                            {{ $doc->formattedMeetingDateRange() }}
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e74c3c;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Time</div>
                                        <div class="info-value">{{ $startTime }} – {{ $endTime }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #16a085;">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Attendees</div>
                                        <div class="info-value">{{ $doc->attendees_count ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #8e44ad;">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Requester</div>
                                        <div class="info-value">{{ $doc->requestedBy->name ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #7f8c8d;">
                                        <i class="fas fa-paper-plane"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Submitted At</div>
                                        <div class="info-value">
                                            {{ $doc->submitted_at ? format_datetime_with_weekday($doc->submitted_at) : '—' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($doc->facilities)
                                <div class="overtime-remarks-block border-top">
                                    <div class="info-item overtime-remarks-item">
                                        <div class="info-icon" style="background-color: #27ae60;">
                                            <i class="fas fa-couch"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Facilities</div>
                                            <div class="info-value overtime-remarks-value">{{ $doc->facilities }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($doc->notes)
                                <div class="overtime-remarks-block border-top">
                                    <div class="info-item overtime-remarks-item">
                                        <div class="info-icon" style="background-color: #1abc9c;">
                                            <i class="fas fa-comment-alt"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Notes</div>
                                            <div class="info-value overtime-remarks-value">{{ $doc->notes }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($doc->rejection_reason)
                                <div class="overtime-remarks-block border-top">
                                    <div class="info-item overtime-remarks-item">
                                        <div class="info-icon" style="background-color: #c0392b;">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Rejection Reason</div>
                                            <div class="info-value overtime-remarks-value">{{ $doc->rejection_reason }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="travel-card">
                        <div class="card-head">
                            <h2><i class="fas fa-utensils"></i> Consumption</h2>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center" style="width: 8%;">✓</th>
                                            <th style="width: 30%;">Type</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $itemsByType = $doc->items->keyBy('consumption_type');
                                        @endphp
                                        @foreach (\App\Models\RoomConsumptionRequest::CONSUMPTION_TYPES as $type => $label)
                                            @php $item = $itemsByType->get($type); @endphp
                                            <tr>
                                                <td class="text-center">
                                                    @if ($item?->is_selected)
                                                        <i class="fas fa-check text-success"></i>
                                                    @endif
                                                </td>
                                                <td>{{ $label }}</td>
                                                <td>{{ $item?->description ?: '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column --}}
                <div class="col-lg-4">
                    @if ($doc->need_zoom)
                        @php
                            $zoomStatus = $doc->zoom_sync_status ?: 'pending';
                            $zoomStatusMeta = match ($zoomStatus) {
                                'completed', 'done', 'synced' => [
                                    'label' => 'Completed',
                                    'badge' => 'badge-success',
                                    'note' => 'Zoom Meeting ID sudah tersedia dari IT Work Order.',
                                ],
                                'failed', 'error' => [
                                    'label' => 'Failed',
                                    'badge' => 'badge-danger',
                                    'note' => 'Gagal sinkronisasi ke IT Work Order. Hubungi IT untuk ditindaklanjuti.',
                                ],
                                'open' => [
                                    'label' => 'Open',
                                    'badge' => 'badge-info',
                                    'note' =>
                                        'IT Work Order sudah dibuat. Setelah IT mengisi Meeting ID, klik Refresh Zoom Status.',
                                ],
                                'processing' => [
                                    'label' => 'Processing',
                                    'badge' => 'badge-primary',
                                    'note' =>
                                        'IT Work Order sedang diproses. Klik Refresh Zoom Status untuk memperbarui data Zoom.',
                                ],
                                'pending' => [
                                    'label' => 'Pending',
                                    'badge' => 'badge-warning',
                                    'note' => 'Klik Request Zoom via IT WO untuk membuat work order di sistem.',
                                ],
                                'not_required' => [
                                    'label' => 'Not Required',
                                    'badge' => 'badge-secondary',
                                    'note' => 'Zoom Meeting ID tidak diperlukan untuk request ini.',
                                ],
                                default => [
                                    'label' => ucfirst(str_replace('_', ' ', $zoomStatus)),
                                    'badge' => 'badge-secondary',
                                    'note' => 'Status sinkronisasi Zoom: '.$zoomStatus.'.',
                                ],
                            };
                        @endphp
                        <div class="travel-card">
                            <div class="card-head">
                                <h2><i class="fas fa-video"></i> Zoom Meeting ID</h2>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted small mb-0">
                                        <i class="fas fa-headset mr-1"></i> IT Work Order
                                        @if ($itWoTrialMode)
                                            <span class="badge badge-secondary ml-1">Trial</span>
                                        @endif
                                    </span>
                                    <span
                                        class="badge {{ $zoomStatusMeta['badge'] }}">{{ $zoomStatusMeta['label'] }}</span>
                                </div>
                                <p class="small text-muted mb-3">{{ $zoomStatusMeta['note'] }}</p>

                                <div class="mb-2">
                                    <div class="info-label">IT WO Number</div>
                                    <div class="info-value">{{ $doc->it_wo_number ?: '—' }}</div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">Topic</div>
                                    <div class="info-value">
                                        @if ($doc->zoom_topic)
                                            {{ $doc->zoom_topic }}
                                        @else
                                            <span class="text-muted">Menunggu IT WO…</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">Meeting ID</div>
                                    <div class="info-value">
                                        @if ($doc->zoom_meeting_id)
                                            <code>{{ $doc->zoom_meeting_id }}</code>
                                        @else
                                            <span class="text-muted">Menunggu IT WO…</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="info-label">Passcode</div>
                                    <div class="info-value">
                                        @if ($doc->zoom_passcode)
                                            <code>{{ $doc->zoom_passcode }}</code>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <div class="info-label">Join URL</div>
                                    <div class="info-value">
                                        @if ($doc->zoom_join_url)
                                            <a href="{{ $doc->zoom_join_url }}" target="_blank" rel="noopener"
                                                class="d-inline-block text-break">
                                                <i class="fas fa-external-link-alt mr-1"></i> Buka Zoom
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="alert alert-light border mt-3 mb-0 py-2 px-3 small text-muted">
                                    <i class="fas fa-info-circle mr-1 text-info"></i>
                                    Hubungi <strong>IT HO Balikpapan</strong> untuk membuka Zoom meeting
                                    atau jika mengalami kendala.
                                </div>

                                @if ($canManageZoomItWo && ($doc->canRequestZoomItWo() || $doc->canSyncZoomItWo()))
                                    <div class="mt-3 pt-3 border-top">
                                        @if ($doc->canRequestZoomItWo())
                                            <form method="POST" action="{{ $requestZoomRoute }}"
                                                class="confirm-submit"
                                                data-confirm-message="Buat IT Work Order untuk Zoom Meeting ID?"
                                                data-confirm-yes="Ya, buat IT WO">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-sm btn-primary btn-block rcr-zoom-btn">
                                                    <i class="fas fa-video mr-1"></i>
                                                    Request Zoom via IT WO
                                                </button>
                                            </form>
                                        @endif

                                        @if ($doc->canSyncZoomItWo())
                                            <form method="POST" action="{{ $syncZoomRoute }}"
                                                class="{{ $doc->canRequestZoomItWo() ? 'mt-2' : '' }}">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-sm btn-info btn-block rcr-zoom-sync-btn">
                                                    <i class="fas fa-sync-alt mr-1"></i>
                                                    Refresh Zoom Status
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Debug Reset IT WO: hidden --}}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if (!empty($doc->manual_approvers) || $doc->approvalPlans->isNotEmpty())
                        <div class="travel-card">
                            <div class="card-head">
                                <h2><i class="fas fa-users"></i> Approval Status</h2>
                            </div>
                            <div class="card-body py-2">
                                @include('components.manual-approver-selector', [
                                    'selectedApprovers' => $doc->manual_approvers ?? [],
                                    'mode' => 'view',
                                    'documentType' => 'room_consumption_request',
                                    'documentId' => $doc->id,
                                ])
                            </div>
                        </div>
                    @endif

                    <div class="travel-action-buttons">
                        <a href="{{ $backRoute }}" class="btn-action back-btn">
                            <i class="fas fa-arrow-left"></i>
                            {{ $fromPersonal ? 'Back to my list' : 'Back to List' }}
                        </a>

                        @if ($canEdit)
                            <a href="{{ $editRoute }}" class="btn-action edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif

                        @if ($canSubmit)
                            <form method="POST" action="{{ $submitRoute }}"
                                class="confirm-submit"
                                data-confirm-message="Submit this request for approval?"
                                data-confirm-yes="Ya, submit">
                                @csrf
                                <button type="submit" class="btn-action submit-approval-btn w-100">
                                    <i class="fas fa-paper-plane"></i> Submit for Approval
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('room-consumption-requests.print', $doc) }}" target="_blank"
                            class="btn-action rcr-print-btn">
                            <i class="fas fa-print"></i> Print
                        </a>

                        @if ($canCancel)
                            <form method="POST" action="{{ $cancelRoute }}"
                                class="confirm-submit"
                                data-confirm-message="Cancel this request?"
                                data-confirm-yes="Ya, batalkan"
                                data-confirm-icon="warning">
                                @csrf
                                <button type="submit" class="btn-action rcr-cancel-btn w-100">
                                    <i class="fas fa-ban"></i> Cancel Request
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

                const message = form.getAttribute('data-confirm-message') || 'Lanjutkan aksi ini?';
                const title = form.getAttribute('data-confirm-title') || 'Konfirmasi';
                const confirmText = form.getAttribute('data-confirm-yes') || 'Ya';
                const cancelText = form.getAttribute('data-confirm-no') || 'Batal';
                const icon = form.getAttribute('data-confirm-icon') || 'warning';

                const proceed = () => {
                    form.dataset.submitting = 'true';
                    if (typeof toast_info === 'function') {
                        toast_info('Memproses...');
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
