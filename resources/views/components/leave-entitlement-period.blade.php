@props([
    'start',
    'end',
    'category' => null,
    'variant' => 'badge',
    'showHelp' => true,
    'entitlementCount' => null,
    'active' => null,
])

@php
    $period = \App\Support\LeaveEntitlementPeriodPresenter::make($start, $end, $category);
    $isListActive = $active ?? $period->isActive;
@endphp

@if ($variant === 'panel')
    <div class="leave-period-panel leave-period-panel--{{ $period->isLsl ? 'lsl' : 'annual' }} mb-3">
        <div class="leave-period-panel__header">
            <span class="badge badge-{{ $period->typeBadge }} leave-period-panel__type">
                <i class="fas fa-{{ $period->isLsl ? 'hourglass-half' : 'calendar-alt' }} mr-1"></i>
                {{ $period->typeLabel }}
            </span>
            @if ($period->isActive)
                <span class="badge badge-success ml-2"><i class="fas fa-check-circle mr-1"></i> Periode Aktif</span>
            @elseif($period->isExpired)
                <span class="badge badge-secondary ml-2"><i class="fas fa-history mr-1"></i> Periode Lalu</span>
            @endif
            @if ($period->isExpiringSoon && ! $period->isExpired)
                <span class="badge badge-warning ml-2"><i class="fas fa-clock mr-1"></i> Segera Berakhir</span>
            @endif
        </div>
        <div class="leave-period-panel__dates">
            <i class="far fa-calendar-alt text-muted mr-2"></i>
            <strong>{{ $period->labelLong }}</strong>
        </div>
        @if ($showHelp)
            <p class="leave-period-panel__help mb-0">
                <i class="fas fa-info-circle text-{{ $period->typeBadge }} mr-1"></i>
                {{ $period->helpText }}
            </p>
        @endif
    </div>
@elseif ($variant === 'list-item')
    <div class="leave-period-list-meta">
        <span class="badge badge-{{ $isListActive ? 'light' : $period->typeBadge }} badge-sm mb-1">
            {{ $period->isLsl ? 'Cuti Panjang' : 'Tahunan' }}
            @if ($period->isLsl)
                · {{ $period->durationYears }} th
            @endif
        </span>
        <small class="{{ $isListActive ? 'text-white-50' : 'text-muted' }} d-block" style="line-height: 1.35;">
            {{ $period->labelLong }}
        </small>
        @if ($period->isActive && ! $isListActive)
            <small class="text-success d-block"><i class="fas fa-circle" style="font-size: 0.45rem;"></i> Sedang berjalan</small>
        @endif
    </div>
@elseif ($variant === 'inline')
    <div class="leave-period-inline">
        <span class="badge badge-{{ $period->typeBadge }} mr-1">{{ $period->isLsl ? 'Cuti Panjang' : 'Tahunan' }}</span>
        <span class="text-muted">{{ $period->labelLong }}</span>
    </div>
@elseif ($variant === 'banner')
    <div class="leave-period-banner leave-period-banner--{{ $period->isLsl ? 'lsl' : 'annual' }}">
        <div class="leave-period-banner__type">{{ $period->typeLabel }}</div>
        <div class="leave-period-banner__dates">
            <i class="far fa-calendar-alt mr-1"></i>{{ $period->labelLong }}
        </div>
    </div>
@else
    <span {{ $attributes->merge(['class' => 'badge badge-' . $period->typeBadge]) }}
        title="{{ $period->labelLong }}">
        @if ($period->isLsl)
            <i class="fas fa-hourglass-half mr-1"></i>
        @else
            <i class="fas fa-calendar-alt mr-1"></i>
        @endif
        {{ $period->isLsl ? 'Cuti Panjang · '.$period->durationYears.' th' : 'Tahunan' }}
    </span>
@endif
