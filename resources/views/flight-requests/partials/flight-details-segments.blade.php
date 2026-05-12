@php
    /** @var \App\Models\FlightRequest $flightRequest */
    $orderedFlightDetails = $flightRequest->details
        ->sortBy(['segment_order', 'flight_date'])
        ->values();
@endphp
@if ($orderedFlightDetails->count() > 0)
    <div class="flight-details-list">
        @foreach ($orderedFlightDetails as $index => $detail)
            <div class="flight-detail-card">
                <div class="flight-detail-header">
                    <h4>Flight {{ $index + 1 }}</h4>
                </div>
                <div class="flight-detail-content">
                    <div class="flight-detail-grid">
                        <div class="flight-detail-item">
                            <div class="flight-detail-icon" style="background-color: #e74c3c;">
                                <i class="fas fa-plane-departure"></i>
                            </div>
                            <div class="flight-detail-info">
                                <div class="flight-detail-label">Departure City</div>
                                <div class="flight-detail-value">{{ $detail->departure_city }}</div>
                            </div>
                        </div>
                        <div class="flight-detail-item">
                            <div class="flight-detail-icon" style="background-color: #27ae60;">
                                <i class="fas fa-plane-arrival"></i>
                            </div>
                            <div class="flight-detail-info">
                                <div class="flight-detail-label">Arrival City</div>
                                <div class="flight-detail-value">{{ $detail->arrival_city }}</div>
                            </div>
                        </div>
                        <div class="flight-detail-item">
                            <div class="flight-detail-icon" style="background-color: #3498db;">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="flight-detail-info">
                                <div class="flight-detail-label">Flight Date</div>
                                <div class="flight-detail-value">{{ $detail->flight_date->format('d F Y') }}</div>
                            </div>
                        </div>
                        @if ($detail->flight_time)
                            <div class="flight-detail-item">
                                <div class="flight-detail-icon" style="background-color: #9b59b6;">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="flight-detail-info">
                                    <div class="flight-detail-label">Flight Time</div>
                                    <div class="flight-detail-value">
                                        {{ \Carbon\Carbon::parse($detail->flight_time)->format('H:i') }}</div>
                                </div>
                            </div>
                        @endif
                        @if ($detail->airline)
                            <div class="flight-detail-item">
                                <div class="flight-detail-icon" style="background-color: #f1c40f;">
                                    <i class="fas fa-plane"></i>
                                </div>
                                <div class="flight-detail-info">
                                    <div class="flight-detail-label">Airline</div>
                                    <div class="flight-detail-value">{{ $detail->airline }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="no-flight-details">
        <i class="fas fa-plane-slash"></i>
        <p>No flight details available</p>
    </div>
@endif
