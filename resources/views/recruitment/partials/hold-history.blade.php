@php
    $totalHoldDays = 0;
    foreach ($holds as $holdItem) {
        if ($holdItem->held_at) {
            $end = $holdItem->released_at ?? now();
            $totalHoldDays += $holdItem->held_at->diffInDays($end);
        }
    }
    $hasActiveHold = $holds->contains(fn ($h) => $h->released_at === null);
@endphp

<div class="hold-history-panel">
    <div class="hold-history-summary">
        <div class="hold-history-summary-item">
            <span class="hold-history-summary-value">{{ $holds->count() }}</span>
            <span class="hold-history-summary-label">Periode hold</span>
        </div>
        <div class="hold-history-summary-divider"></div>
        <div class="hold-history-summary-item">
            <span class="hold-history-summary-value">{{ $totalHoldDays }}</span>
            <span class="hold-history-summary-label">Total hari (kumulatif)</span>
        </div>
        @if ($hasActiveHold)
            <div class="hold-history-summary-divider"></div>
            <div class="hold-history-summary-item hold-history-summary-active">
                <i class="fas fa-circle-notch fa-spin mr-1"></i>
                <span>Sedang on hold</span>
            </div>
        @endif
    </div>

    <div class="hold-history-timeline">
        @foreach ($holds as $hold)
            @php
                $isActive = $hold->released_at === null;
                $heldEnd = $hold->released_at ?? now();
                $durationDays = $hold->held_at ? (int) $hold->held_at->diffInDays($heldEnd) : 0;
                if ($durationDays < 1 && $hold->held_at) {
                    $durationLabel = $hold->held_at->diffForHumans($heldEnd, true);
                } else {
                    $durationLabel = $durationDays.' hari';
                }
            @endphp
            <div class="hold-history-entry {{ $isActive ? 'hold-history-entry--active' : '' }}">
                <div class="hold-history-marker" aria-hidden="true">
                    <span class="hold-history-dot"></span>
                    @if (! $loop->last)
                        <span class="hold-history-line"></span>
                    @endif
                </div>
                <div class="hold-history-content">
                    <div class="hold-history-content-header">
                        <div class="hold-history-dates">
                            <span class="hold-history-date-chip hold-history-date-chip--start">
                                <i class="far fa-pause-circle"></i>
                                {{ $hold->held_at?->format('d M Y, H:i') }}
                            </span>
                            <i class="fas fa-long-arrow-alt-right hold-history-arrow text-muted mx-1"></i>
                            @if ($isActive)
                                <span class="hold-history-date-chip hold-history-date-chip--active">
                                    <i class="fas fa-hourglass-half"></i> Masih berjalan
                                </span>
                            @else
                                <span class="hold-history-date-chip hold-history-date-chip--end">
                                    <i class="far fa-play-circle"></i>
                                    {{ $hold->released_at->format('d M Y, H:i') }}
                                </span>
                            @endif
                        </div>
                        <span class="hold-history-duration badge">{{ $durationLabel }}</span>
                    </div>

                    <div class="hold-history-reason hold-history-reason--hold">
                        <span class="hold-history-reason-label">Alasan hold</span>
                        <p class="mb-0">{{ $hold->hold_reason }}</p>
                    </div>

                    @if ($hold->release_reason)
                        <div class="hold-history-reason hold-history-reason--release">
                            <span class="hold-history-reason-label">Alasan unhold</span>
                            <p class="mb-0">{{ $hold->release_reason }}</p>
                        </div>
                    @endif

                    <div class="hold-history-actors">
                        <span class="hold-history-actor">
                            <i class="fas fa-user-lock text-muted"></i>
                            <strong>{{ $hold->heldBy->name ?? '—' }}</strong>
                            <small class="text-muted">hold</small>
                        </span>
                        @if ($hold->releasedBy)
                            <span class="hold-history-actor-sep">·</span>
                            <span class="hold-history-actor">
                                <i class="fas fa-user-check text-muted"></i>
                                <strong>{{ $hold->releasedBy->name }}</strong>
                                <small class="text-muted">unhold</small>
                            </span>
                        @endif
                    </div>

                    @if ($isActive)
                        <span class="hold-history-active-pill">
                            <i class="fas fa-pause-circle"></i> Hold aktif
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
