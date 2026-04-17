@php
    $flightRequests = $officialtravel->flightRequests->sortBy('created_at')->values();
    $statusLabels = \App\Models\FlightRequest::getStatusOptions();
@endphp

@if ($flightRequests->isNotEmpty())
    <div class="travel-card flight-request-lot-card">
        <div class="card-head">
            <h2><i class="fas fa-plane"></i> Flight Request</h2>
        </div>
        <div class="card-body">
            @foreach ($flightRequests as $flightRequest)
                <div class="mb-4 {{ !$loop->last ? 'pb-4 border-bottom' : '' }}">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                        <div>
                            <strong class="d-block">{{ $flightRequest->form_number ?? '—' }}</strong>
                            <small class="text-muted">
                                @if ($flightRequest->requested_at)
                                    Requested {{ $flightRequest->requested_at->format('d M Y, H:i') }}
                                @endif
                            </small>
                        </div>
                        <div class="mt-2 mt-md-0">
                            @php
                                $statusKey = $flightRequest->status ?? '';
                                $statusLabel = $statusLabels[$statusKey] ?? ucfirst(str_replace('_', ' ', $statusKey));
                            @endphp
                            <span class="badge badge-info">{{ $statusLabel }}</span>
                        </div>
                    </div>

                    @can('flight-requests.show')
                        <p class="mb-3">
                            <a href="{{ route('flight-requests.show', $flightRequest) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt mr-1"></i> Open in Flight module
                            </a>
                        </p>
                    @elsecan('personal.flight.view-own')
                        <p class="mb-3">
                            <a href="{{ route('flight-requests.my-requests.show', $flightRequest) }}"
                                class="btn btn-sm btn-outline-primary">
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
            @endforeach
        </div>
    </div>
@endif
