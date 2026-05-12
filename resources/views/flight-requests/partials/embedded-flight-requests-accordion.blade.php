@php
    /** @var \Illuminate\Support\Collection|array $flightRequests */
    $flightRequests = collect($flightRequests ?? [])->sortBy('created_at')->values();
    $statusLabels = \App\Models\FlightRequest::getStatusOptions();
    $wrapperClass = $wrapperClass ?? 'travel-card flight-request-lot-card';
    $linkTarget = $linkTarget ?? null;
@endphp

@if ($flightRequests->isNotEmpty())
    <div class="{{ $wrapperClass }}">
        <div class="card-head flight-request-lot-card__head">
            <h2><i class="fas fa-plane"></i> Flight Request</h2>
        </div>
        <div class="card-body flight-request-lot-card__body p-0">
            @foreach ($flightRequests as $flightRequest)
                @php
                    $collapseId = 'embeddedFr' . $flightRequest->id;
                    $statusKey = $flightRequest->status ?? '';
                    $statusLabel = $statusLabels[$statusKey] ?? ucfirst(str_replace('_', ' ', $statusKey));
                @endphp
                <div class="flight-request-lot-item {{ !$loop->last ? 'flight-request-lot-item--bordered' : '' }}">
                    <button type="button"
                        class="flight-request-lot-accordion-btn collapsed d-flex align-items-start justify-content-between w-100 border-0 bg-white text-left"
                        data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="false"
                        aria-controls="{{ $collapseId }}">
                        <div class="flight-request-lot-accordion-btn__text pr-2 min-w-0 flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center">
                                <strong
                                    class="flight-request-lot-accordion-btn__number">{{ $flightRequest->form_number ?? '—' }}</strong>
                                <span
                                    class="badge badge-info flight-request-lot-accordion-status-badge ml-2">{{ $statusLabel }}</span>
                            </div>
                            @if ($flightRequest->requested_at)
                                <small class="text-muted d-block mt-1">Requested
                                    {{ $flightRequest->requested_at->format('d M Y, H:i') }}</small>
                            @endif
                        </div>
                        <i class="fas fa-chevron-down flight-request-lot-accordion-chevron flex-shrink-0 mt-1"
                            aria-hidden="true"></i>
                    </button>
                    <div id="{{ $collapseId }}" class="collapse flight-request-lot-accordion-panel">
                        <div class="flight-request-lot-accordion-panel__inner px-3 pb-3">
                            @can('flight-requests.show')
                                <p class="mb-3">
                                    <a href="{{ route('flight-requests.show', $flightRequest) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        @if ($linkTarget) target="{{ $linkTarget }}" rel="noopener noreferrer" @endif>
                                        <i class="fas fa-external-link-alt mr-1"></i> Open in Flight module
                                    </a>
                                </p>
                            @elsecan('personal.flight.view-own')
                                <p class="mb-3">
                                    <a href="{{ route('flight-requests.my-requests.show', $flightRequest) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        @if ($linkTarget) target="{{ $linkTarget }}" rel="noopener noreferrer" @endif>
                                        <i class="fas fa-external-link-alt mr-1"></i> View flight request
                                    </a>
                                </p>
                            @endcan

                            @php
                                $orderedDetails = $flightRequest->details
                                    ->sortBy(['segment_order', 'flight_date'])
                                    ->values();
                            @endphp

                            @if ($orderedDetails->isNotEmpty())
                                @foreach ($orderedDetails as $detailIndex => $detail)
                                    <div class="mb-3 {{ !$loop->last ? 'pb-3 border-bottom border-light' : '' }}">
                                        <h6 class="font-weight-bold mb-2 text-secondary">
                                            <i class="fas fa-plane-departure mr-1"></i>Flight {{ $detailIndex + 1 }}
                                        </h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped table-bordered mb-0">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>From</th>
                                                        <th>To</th>
                                                        <th>Airline</th>
                                                        <th>Time</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            {{ $detail->flight_date ? $detail->flight_date->format('d M Y') : '—' }}
                                                        </td>
                                                        <td>{{ $detail->departure_city ?? '—' }}</td>
                                                        <td>{{ $detail->arrival_city ?? '—' }}</td>
                                                        <td>{{ $detail->airline ?? '—' }}</td>
                                                        <td>
                                                            @if ($detail->flight_time)
                                                                {{ \Carbon\Carbon::parse($detail->flight_time)->format('H:i') }}
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted mb-0"><i class="fas fa-info-circle"></i> No segment details recorded.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@once
    @push('styles')
        <style>
            .flight-request-lot-card__head {
                border-bottom: 1px solid #e9ecef;
            }

            .flight-request-lot-item--bordered {
                border-bottom: 1px solid #e9ecef;
            }

            .flight-request-lot-accordion-btn {
                padding: 0.85rem 1rem;
                cursor: pointer;
                transition: background-color 0.15s ease;
            }

            .flight-request-lot-accordion-btn:hover {
                background-color: #f8f9fa !important;
            }

            .flight-request-lot-accordion-btn:focus {
                outline: none;
                box-shadow: inset 0 0 0 2px rgba(0, 123, 255, 0.25);
            }

            .flight-request-lot-accordion-btn__number {
                color: #212529;
                font-size: 1rem;
            }

            .flight-request-lot-accordion-chevron {
                font-size: 0.9rem;
                color: #212529;
                transition: transform 0.2s ease;
            }

            .flight-request-lot-accordion-btn:not(.collapsed) .flight-request-lot-accordion-chevron {
                transform: rotate(180deg);
            }

            .flight-request-lot-accordion-panel__inner {
                border-top: 1px solid #f1f3f5;
                padding-top: 0.75rem;
            }
        </style>
    @endpush
@endonce
