@extends('layouts.main')

@section('title', $title)

@section('styles')
@if (! empty($canAdjustDestinations))
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endif
@endsection

@section('content')
@php
    $a = $assignment;
    $statusMap = [
        'draft' => [
            'label' => 'Draft',
            'class' => 'overtime-pill-draft',
            'icon' => 'fa-pencil-alt',
        ],
        'issued' => [
            'label' => 'Issued',
            'class' => 'overtime-pill-pending',
            'icon' => 'fa-paper-plane',
        ],
        'in_progress' => [
            'label' => 'In Progress',
            'class' => 'overtime-pill-finished',
            'icon' => 'fa-road',
        ],
        'closed' => [
            'label' => 'Closed',
            'class' => 'overtime-pill-approved',
            'icon' => 'fa-flag-checkered',
        ],
        'cancelled' => [
            'label' => 'Cancelled',
            'class' => 'overtime-pill-rejected',
            'icon' => 'fa-ban',
        ],
    ];
    $pill = $statusMap[$a->status] ?? [
        'label' => $a->statusLabel(),
        'class' => 'overtime-pill-draft',
        'icon' => 'fa-question-circle',
    ];
    $legs = $a->tripLegs();
@endphp

@include('partials.official-travel-detail-styles')

<style>
    .foa-trip-log-table {
        table-layout: fixed;
        width: 100%;
        margin-bottom: 0;
    }
    .foa-trip-log-table th,
    .foa-trip-log-table td {
        vertical-align: middle;
        word-wrap: break-word;
    }
    .foa-trip-log-table thead th {
        text-align: center;
        vertical-align: middle;
    }
    .foa-trip-log-table .col-leg { width: 20%; }
    .foa-trip-log-table .col-dest { width: 26%; }
    .foa-trip-log-table .col-time { width: 16%; }
    .foa-trip-log-table .col-km { width: 11%; }
    .foa-trip-log-table:not(.foa-trip-log-edit) .col-km {
        text-align: right;
    }
    .foa-trip-log-table:not(.foa-trip-log-edit) .col-time {
        text-align: center;
    }
    .foa-trip-log-edit td.col-time,
    .foa-trip-log-edit td.col-km {
        padding: 0.45rem 0.4rem;
    }
    .foa-trip-input {
        min-height: 2.15rem;
        height: 2.15rem;
        font-size: 0.9rem;
        padding: 0.3rem 0.45rem;
        width: 100%;
        line-height: 1.3;
    }
    .foa-trip-log-edit input[type="time"].foa-trip-input {
        min-width: 6.5rem;
    }
    .foa-trip-log-edit input[type="number"].foa-trip-input {
        min-width: 4.75rem;
    }
    .foa-trip-log-edit-card .card-body:last-child {
        padding-top: 0.75rem;
        padding-bottom: 0.85rem;
    }

    .followers-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.5rem;
        height: 1.5rem;
        padding: 0 0.4rem;
        margin-left: 0.4rem;
        border-radius: 999px;
        background: #e8eef3;
        color: #5a6a7a;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .followers-list {
        max-height: 420px;
        overflow-y: auto;
    }
    .follower-item {
        padding: 15px 20px;
        border-bottom: 1px solid #edf2f7;
    }
    .follower-item:last-child {
        border-bottom: 0;
    }
    .follower-name {
        font-size: 16px;
        font-weight: 500;
        color: #2c3e50;
        margin-bottom: 4px;
    }
    .follower-position {
        font-size: 14px;
        color: #64748b;
        margin-bottom: 6px;
    }
    .follower-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 16px;
        font-size: 13px;
        color: #64748b;
        margin-bottom: 6px;
    }
    .follower-nik,
    .follower-department {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .follower-project {
        font-size: 13px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .follower-meta i,
    .follower-project i {
        color: #94a3b8;
        width: 14px;
        text-align: center;
    }
    .follower-manual-badge {
        font-size: 0.7rem;
        vertical-align: middle;
    }
</style>

<div class="content-wrapper-custom">
    <div class="travel-header">
        <div class="travel-header-content">
            <div class="travel-number">{{ $a->form_number }}</div>
            <h1 class="travel-destination">{{ $a->destinationSummary() }}</h1>
            <div class="travel-date">
                <i class="fas fa-calendar-alt mr-1"></i>
                {{ optional($a->assignment_date)->format('d F Y') ?: '—' }}
                <span class="mx-2">·</span>
                <i class="fas fa-car mr-1"></i>
                {{ $a->license_plate }} ({{ $a->vehicle_kode }})
                <span class="mx-2">·</span>
                <i class="fas fa-user mr-1"></i>
                {{ $a->driver_name }}
            </div>
        </div>
        <div class="travel-status-pill">
            <span class="overtime-status-pill {{ $pill['class'] }}">
                <i class="fas {{ $pill['icon'] }}"></i> {{ $pill['label'] }}
            </span>
        </div>
    </div>

    @if ($conflictWarning)
        <div class="alert alert-warning mx-3">{{ $conflictWarning }}</div>
    @endif

    <div class="travel-content">
        <div class="row">
            <div class="col-lg-8">
                @include('vehicle-assignments.partials.assignment-info-card', ['assignment' => $a])

                @include('vehicle-assignments.partials.passengers-card', ['assignment' => $a])

                @php
                    $canEditTripLog = $a->status === 'in_progress'
                        && auth()->user()->can('vehicle-assignments.edit');
                @endphp

                @if ($canEditTripLog)
                    @include('vehicle-assignments.partials.trip-log-edit-form', [
                        'assignment' => $a,
                        'formAction' => route('vehicle-assignments.updateStops', $a),
                    ])
                @else
                    <div class="travel-card">
                        <div class="card-head">
                            <h2><i class="fas fa-route"></i> Trip Log (Jam Berangkat / Tiba)</h2>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered foa-trip-log-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="col-leg text-center">Leg</th>
                                            <th class="col-dest text-center">Tujuan</th>
                                            <th class="col-time text-center">Berangkat</th>
                                            <th class="col-km text-center">KM</th>
                                            <th class="col-time text-center">Tiba</th>
                                            <th class="col-km text-center">KM</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($legs as $stop)
                                            <tr>
                                                <td class="col-leg">
                                                    <strong>{{ $stop->legLabel() }}</strong>
                                                </td>
                                                <td class="col-dest">
                                                    {{ $stop->destination }}
                                                    @if ($stop->is_manual)
                                                        <span class="badge badge-secondary">Manual</span>
                                                    @endif
                                                </td>
                                                <td class="col-time">{{ $stop->formatTime($stop->depart_time) }}</td>
                                                <td class="col-km">{{ $stop->depart_km !== null ? number_format($stop->depart_km) : '—' }}</td>
                                                <td class="col-time">{{ $stop->formatTime($stop->arrive_time) }}</td>
                                                <td class="col-km">{{ $stop->arrive_km !== null ? number_format($stop->arrive_km) : '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">No destinations yet</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-muted small px-3 py-2 mb-0">
                                Setiap baris = satu perjalanan seperti form kertas:
                                <em>Berangkat</em> dari lokasi sebelumnya (baris pertama dari Origin),
                                <em>Tiba</em> di tujuan baris ini.
                                @if ($a->status === 'issued')
                                    Form isi jam/KM muncul setelah trip dimulai (In Progress).
                                @endif
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                @if (! empty($canAdjustDestinations))
                    @include('vehicle-assignments.partials.issued-destinations-adjust-form', [
                        'assignment' => $a,
                        'destinationProjects' => $destinationProjects,
                    ])
                @endif

                @if ($a->canClose())
                    @can('vehicle-assignments.edit')
                        @include('vehicle-assignments.partials.close-at-origin-form', [
                            'assignment' => $a,
                            'formAction' => route('vehicle-assignments.close', $a),
                        ])
                    @endcan
                @endif

                <div class="travel-action-buttons">
                    @can('vehicle-assignments.print')
                        <a href="{{ route('vehicle-assignments.print', $a) }}" class="btn-action"
                            style="background:#6c757d" target="_blank">
                            <i class="fas fa-print"></i> Print
                        </a>
                    @endcan
                    @if ($a->canIssue())
                        @can('vehicle-assignments.issue')
                            <form action="{{ route('vehicle-assignments.issue', $a) }}" method="POST" class="w-100">
                                @csrf
                                <button class="btn-action" style="background:#28a745;width:100%;border:0"
                                    onclick="return confirm('Issue this FOA for the driver?')">
                                    <i class="fas fa-paper-plane"></i> Issue
                                </button>
                            </form>
                        @endcan
                    @endif
                    @if ($a->isHeaderEditable())
                        @can('vehicle-assignments.edit')
                            <a href="{{ route('vehicle-assignments.edit', $a) }}" class="btn-action edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endcan
                    @endif
                    @if ($a->canCancelByRequestor() || $a->status === 'in_progress')
                        @can('vehicle-assignments.cancel')
                            <form action="{{ route('vehicle-assignments.cancel', $a) }}" method="POST" class="w-100">
                                @csrf
                                <button class="btn-action" style="background:#dc3545;width:100%;border:0"
                                    onclick="return confirm('Cancel this FOA?')">
                                    <i class="fas fa-ban"></i> Cancel
                                </button>
                            </form>
                        @endcan
                    @endif
                    <a href="{{ route('vehicle-assignments.index') }}" class="btn-action back-btn">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if (! empty($canAdjustDestinations))
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
@endif
@endsection
