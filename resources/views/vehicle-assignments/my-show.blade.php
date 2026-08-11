@extends('layouts.main')

@section('title', $title)

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
@php
    $a = $assignment;
    $hintKm = (int) ($a->vehicle?->odometer ?? 0);
    $legs = $a->tripLegs();
    $firstLeg = $a->firstLeg();
@endphp
@include('partials.official-travel-detail-styles')

<style>
    .foa-trip-log-table {
        table-layout: fixed;
        width: 100%;
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
    .foa-trip-log-table .col-leg { width: 18%; }
    .foa-trip-log-table .col-dest { width: 24%; }
    .foa-trip-log-table .col-time { width: 17%; }
    .foa-trip-log-table .col-km { width: 12%; }
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
    .followers-list { max-height: 420px; overflow-y: auto; }
    .follower-item {
        padding: 15px 20px;
        border-bottom: 1px solid #edf2f7;
    }
    .follower-item:last-child { border-bottom: 0; }
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
                {{ $a->license_plate }}
                <span class="mx-2">·</span>
                <i class="fas fa-map-marker-alt mr-1"></i>
                Origin: {{ $a->origin_destination }}
            </div>
        </div>
        <div class="travel-status-pill">
            <span class="badge badge-{{ $a->statusBadgeClass() }} p-2">{{ $a->statusLabel() }}</span>
        </div>
    </div>

    <div class="travel-content">
        <div class="row">
            <div class="col-lg-8">
                @include('vehicle-assignments.partials.assignment-info-card', ['assignment' => $a])

                @include('vehicle-assignments.partials.passengers-card', ['assignment' => $a])

                @if ($a->canStart())
                    @can('personal.vehicle-assignments.update-trip')
                        <div class="travel-card">
                            <div class="card-head">
                                <h2><i class="fas fa-play"></i> Start Trip — Berangkat dari Origin</h2>
                            </div>
                            <form method="POST" action="{{ route('vehicle-assignments.my-trips.start', $a) }}">
                                @csrf
                                <div class="card-body">
                                    <p class="text-muted small mb-2">
                                        Mengisi <strong>Berangkat</strong> pada baris
                                        <em>{{ $firstLeg?->legLabel() ?? 'Jam Berangkat/Tiba' }}</em>
                                        (menuju {{ $firstLeg?->destination ?? 'tujuan pertama' }}).
                                        Baris pertama = keluar dari Origin (sama seperti form kertas).
                                    </p>
                                    <p class="mb-3"><strong>Current vehicle KM:</strong> {{ number_format($hintKm) }}</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Pukul Berangkat <span class="text-danger">*</span></label>
                                            <input type="time" name="depart_time" class="form-control" required
                                                value="{{ old('depart_time') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Km Berangkat <span class="text-danger">*</span></label>
                                            <input type="number" name="depart_km" class="form-control" min="0" required
                                                value="{{ old('depart_km', $hintKm) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <button class="btn btn-success"><i class="fas fa-play"></i> Start Trip</button>
                                </div>
                            </form>
                        </div>
                    @endcan
                @endif

                @if ($a->status === 'in_progress')
                    @can('personal.vehicle-assignments.update-trip')
                        @include('vehicle-assignments.partials.trip-log-edit-form', [
                            'assignment' => $a,
                            'formAction' => route('vehicle-assignments.my-trips.update-stops', $a),
                        ])
                    @endcan
                @endif

                @if ($a->status === 'closed')
                    <div class="alert alert-success mx-3">FOA closed
                        {{ $a->closed_at?->format('d M Y H:i') }}.</div>
                @endif
            </div>

            <div class="col-lg-4">
                @if (! empty($canAdjustDestinations))
                    @include('vehicle-assignments.partials.issued-destinations-adjust-form', [
                        'assignment' => $a,
                        'destinationProjects' => $destinationProjects,
                        'formAction' => route('vehicle-assignments.my-trips.adjustDestinations', $a),
                    ])
                @endif

                @if ($a->canClose())
                    @can('personal.vehicle-assignments.close-own')
                        @include('vehicle-assignments.partials.close-at-origin-form', [
                            'assignment' => $a,
                            'formAction' => route('vehicle-assignments.my-trips.close', $a),
                        ])
                    @endcan
                @endif

                <div class="travel-action-buttons">
                    <a href="{{ route('vehicle-assignments.my-trips') }}" class="btn-action back-btn">
                        <i class="fas fa-arrow-left"></i> Back to My FOA
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
@endsection
