@php
    /** @var \App\Models\Officialtravel $officialtravel */
@endphp

<div class="travel-card stops-timeline-card">
    <div class="card-head">
        <h2><i class="fas fa-route"></i> Travel Stops Timeline</h2>
    </div>
    <div class="card-body stops-timeline-card__body">
        @if ($officialtravel->stops->count() > 0)
            <div class="timeline timeline-stops-enhanced">
                @foreach ($officialtravel->stops as $index => $stop)
                    @php
                        $needsArrival = !$stop->hasArrival();
                        $tripNum = $index + 1;
                        $collapseId = 'stopTimelineDetail' . $tripNum;
                        $headingId = 'stopTimelineHeading' . $tripNum;
                        $tripState = $stop->isComplete()
                            ? 'complete'
                            : ($needsArrival
                                ? 'awaiting-arrival'
                                : 'awaiting-departure');
                    @endphp
                    <div class="timeline-item stop-destination stop-destination--{{ $tripState }}">
                        <div class="timeline-marker" aria-hidden="true">
                            @if ($stop->isComplete())
                                <i class="fas fa-check-circle"></i>
                            @elseif ($needsArrival)
                                <i class="fas fa-map-marker-alt"></i>
                            @else
                                <i class="fas fa-plane-departure"></i>
                            @endif
                        </div>
                        <div class="timeline-content stop-destination-card">
                            <button type="button" class="stop-destination-accordion-header" id="{{ $headingId }}"
                                data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="false"
                                aria-controls="{{ $collapseId }}">
                                <div class="stop-destination-accordion-header__text">
                                    <span class="stop-destination-head__badge">Destination {{ $tripNum }}</span>
                                    <span class="stop-destination-head__dest"
                                        title="{{ $stop->destination }}">{{ $stop->destination }}</span>
                                </div>
                                <i class="fas fa-chevron-down stop-destination-accordion-header__chevron"
                                    aria-hidden="true"></i>
                            </button>
                            <div id="{{ $collapseId }}" class="collapse stop-destination-accordion-panel"
                                role="region" aria-labelledby="{{ $headingId }}">
                                <div class="stop-destination-accordion-panel__inner">
                                    <div class="stop-destination-panel-status">
                                        @if ($stop->isComplete())
                                            <span class="badge badge-success">Complete</span>
                                        @elseif ($needsArrival)
                                            <span
                                                class="badge stop-destination-panel-status__pill stop-destination-panel-status__pill--neutral">Awaiting
                                                arrival</span>
                                        @else
                                            <span
                                                class="badge stop-destination-panel-status__pill stop-destination-panel-status__pill--warn">Awaiting
                                                departure</span>
                                        @endif
                                    </div>

                                    @include('officialtravels.partials.stops-timeline-checkpoint-track', [
                                        'stop' => $stop,
                                    ])

                                    <div class="timeline-details timeline-details--accordion">
                                        @if ($stop->hasArrival())
                                            <div class="timeline-detail-item">
                                                <i class="fas fa-plane-arrival text-success"></i>
                                                <div class="detail-content">
                                                    <div class="detail-label">Arrival</div>
                                                    <div class="detail-value">
                                                        {{ $stop->arrival_at_destination ? $stop->arrival_at_destination->format('d F Y H:i') : '—' }}
                                                        @if ($stop->arrivalChecker)
                                                            <br><small class="text-muted">by
                                                                {{ $stop->arrivalChecker->name }}</small>
                                                        @endif
                                                        @if ($stop->arrival_remark)
                                                            <br><small
                                                                class="text-muted">{{ $stop->arrival_remark }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="timeline-detail-item timeline-detail-item--muted">
                                                <i class="fas fa-plane-arrival text-muted"></i>
                                                <div class="detail-content">
                                                    <div class="detail-label text-muted">Arrival</div>
                                                    <div class="detail-value text-muted small">Not recorded yet</div>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($stop->hasDeparture())
                                            <div class="timeline-detail-item">
                                                <i class="fas fa-plane-departure text-danger"></i>
                                                <div class="detail-content">
                                                    <div class="detail-label">Departure</div>
                                                    <div class="detail-value">
                                                        {{ $stop->departure_from_destination ? $stop->departure_from_destination->format('d F Y H:i') : '—' }}
                                                        @if ($stop->departureChecker)
                                                            <br><small class="text-muted">by
                                                                {{ $stop->departureChecker->name }}</small>
                                                        @endif
                                                        @if ($stop->departure_remark)
                                                            <br><small
                                                                class="text-muted">{{ $stop->departure_remark }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="timeline-detail-item timeline-detail-item--muted">
                                                <i class="fas fa-plane-departure text-muted"></i>
                                                <div class="detail-content">
                                                    <div class="detail-label text-muted">Departure</div>
                                                    <div class="detail-value text-muted small">Not recorded yet</div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="no-stops">
                <i class="fas fa-route text-muted"></i>
                <p class="text-muted mb-0">No stops recorded yet</p>
            </div>
        @endif
    </div>
</div>

@once
    @push('styles')
        <style>
            /* Travel stops timeline (detail / my-travels) */
            .stops-timeline-card {
                margin-bottom: 20px;
            }

            .stops-timeline-card__body {
                padding: 0.75rem 1rem 1rem;
            }

            .timeline.timeline-stops-enhanced {
                position: relative;
                padding-left: 30px;
            }

            .timeline.timeline-stops-enhanced::before {
                content: '';
                position: absolute;
                left: 15px;
                top: 0;
                bottom: 0;
                width: 2px;
                background: #e9ecef;
            }

            .stop-destination {
                position: relative;
                margin-bottom: 22px;
                padding-left: 30px;
            }

            .stop-destination:last-child {
                margin-bottom: 0;
            }

            .stop-destination .timeline-marker {
                position: absolute;
                left: -27px;
                top: 14px;
                width: 30px;
                height: 30px;
                font-size: 12px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #fff;
                border: 3px solid #e9ecef;
                z-index: 2;
            }

            .stop-destination--complete .timeline-marker {
                background: #28a745;
                border-color: #28a745;
                color: #fff;
            }

            .stop-destination--awaiting-arrival .timeline-marker {
                background: #495057;
                border-color: #fff;
                color: #fff;
                box-shadow: 0 0 0 2px #ced4da;
            }

            .stop-destination--awaiting-departure .timeline-marker {
                background: #ffc107;
                border-color: #fff;
                color: #212529;
                box-shadow: 0 0 0 2px #e0a800;
            }

            .stop-destination-card.timeline-content {
                padding: 0;
                overflow: hidden;
                background: #fff;
                border: 1px solid #e9ecef;
                border-radius: 10px;
                border-left: 6px solid #495057;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            }

            .stop-destination--complete .stop-destination-card.timeline-content {
                border-left-color: #28a745;
            }

            .stop-destination--awaiting-arrival .stop-destination-card.timeline-content {
                border-left-color: #495057;
            }

            .stop-destination--awaiting-departure .stop-destination-card.timeline-content {
                border-left-color: #ffc107;
            }

            .stop-destination-accordion-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                width: 100%;
                margin: 0;
                padding: 16px 18px;
                text-align: left;
                border: none;
                background: #fff;
                cursor: pointer;
                transition: background 0.15s ease;
            }

            .stop-destination-accordion-header:hover {
                background: #f8f9fa;
            }

            .stop-destination-accordion-header:focus {
                outline: none;
                box-shadow: inset 0 0 0 2px rgba(0, 123, 255, 0.35);
            }

            .stop-destination-accordion-header__text {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
                min-width: 0;
                flex: 1;
            }

            .stop-destination-head__badge {
                display: inline-flex;
                align-items: center;
                padding: 2px 7px;
                border-radius: 4px;
                font-size: 9px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                background: #e9ecef;
                color: #343a40;
            }

            .stop-destination-head__dest {
                font-weight: 600;
                font-size: 0.8125rem;
                color: #212529;
                line-height: 1.35;
                word-break: break-word;
            }

            .stop-destination-accordion-header__chevron {
                flex-shrink: 0;
                margin-top: 4px;
                font-size: 1.1rem;
                color: #495057;
                transition: transform 0.25s ease;
            }

            .stop-destination-accordion-header[aria-expanded='true'] .stop-destination-accordion-header__chevron {
                transform: rotate(180deg);
            }

            .stop-destination-accordion-panel__inner {
                padding: 0 18px 16px;
                border-top: 1px solid #f1f3f5;
            }

            .stop-destination-panel-status {
                padding-top: 12px;
                margin-bottom: 10px;
            }

            .stop-destination-panel-status__pill {
                font-weight: 600;
                font-size: 12px;
                padding: 6px 10px;
                border-radius: 6px;
            }

            .stop-destination-panel-status__pill--neutral {
                background: #fff;
                border: 1px solid #343a40;
                color: #343a40;
            }

            .stop-destination-panel-status__pill--warn {
                background: #fff3cd;
                border: 1px solid #ffc107;
                color: #856404;
            }

            .stop-checkpoint-track {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-wrap: wrap;
                padding: 10px 12px;
                background: #f8f9fa;
                border-radius: 8px;
                border: 1px dashed #dee2e6;
            }

            .stop-checkpoint-track__segment {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-size: 12px;
                font-weight: 600;
                color: #adb5bd;
            }

            .stop-checkpoint-track__segment--done {
                color: #28a745;
            }

            .stop-checkpoint-track__segment--pending {
                color: #868e96;
            }

            .stop-checkpoint-track__dot {
                display: inline-flex;
                width: 26px;
                height: 26px;
                border-radius: 50%;
                align-items: center;
                justify-content: center;
                background: #fff;
                border: 2px solid currentColor;
                font-size: 11px;
            }

            .stop-checkpoint-track__segment--done .stop-checkpoint-track__dot {
                background: #d4edda;
                border-color: #28a745;
                color: #28a745;
            }

            .stop-checkpoint-track__segment--pending .stop-checkpoint-track__dot {
                border-color: #ced4da;
                color: #adb5bd;
            }

            .stop-checkpoint-track__connector {
                flex: 1;
                min-width: 16px;
                height: 2px;
                background: #dee2e6;
                border-radius: 1px;
            }

            .stop-checkpoint-track__connector--done {
                background: #28a745;
            }

            .stop-checkpoint-track__connector--active {
                background: linear-gradient(90deg, #28a745 0%, #dee2e6 100%);
            }

            .timeline-details--accordion {
                display: flex;
                flex-direction: column;
                gap: 12px;
                margin-top: 14px;
            }

            .stop-destination .timeline-detail-item {
                display: flex;
                align-items: flex-start;
                gap: 10px;
            }

            .stop-destination .timeline-detail-item i {
                margin-top: 3px;
                font-size: 16px;
            }

            .stop-destination .timeline-detail-item .detail-content {
                flex: 1;
            }

            .stop-destination .timeline-detail-item .detail-label {
                font-weight: 600;
                color: #666;
                font-size: 14px;
                margin-bottom: 5px;
            }

            .stop-destination .timeline-detail-item .detail-value {
                color: #333;
                font-size: 14px;
                line-height: 1.4;
            }

            .timeline-detail-item--muted .detail-label {
                font-weight: 500;
            }

            .no-stops {
                text-align: center;
                padding: 32px 16px;
                color: #6c757d;
            }

            .no-stops i {
                font-size: 40px;
                margin-bottom: 12px;
                display: block;
            }

            @media (max-width: 768px) {
                .stop-destination-accordion-header {
                    padding: 14px 14px;
                }

                .stop-destination-accordion-panel__inner {
                    padding: 0 14px 14px;
                }
            }
        </style>
    @endpush
@endonce
