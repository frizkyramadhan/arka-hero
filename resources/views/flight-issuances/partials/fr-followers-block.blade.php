@php
    $hasLotFollowers =
        $flightRequest->request_type === \App\Models\FlightRequest::TYPE_TRAVEL_BASED &&
        $flightRequest->officialTravel &&
        $flightRequest->officialTravel->details->isNotEmpty();

    $hasStandaloneFollowers =
        $flightRequest->request_type === \App\Models\FlightRequest::TYPE_STANDALONE &&
        $flightRequest->relationLoaded('followers') &&
        $flightRequest->followers->isNotEmpty();
@endphp

@if ($hasLotFollowers || $hasStandaloneFollowers)
    @once
        @push('styles')
            <style>
                .fr-followers-block .followers-card .card-head h2 {
                    font-size: 1rem;
                }

                .fr-followers-block .follower-name {
                    font-size: 1rem;
                    font-weight: 600;
                }

                .fr-followers-block .follower-position {
                    font-size: 0.9375rem;
                }

                .fr-followers-block .follower-meta,
                .fr-followers-block .follower-project {
                    font-size: 0.9375rem;
                }

                .fr-followers-block .follower-item {
                    padding: 12px 14px;
                }
            </style>
        @endpush
    @endonce

    <div class="fr-followers-block border-top pt-2 mt-2">
        @include('flight-requests.partials.followers-display-card', ['flightRequest' => $flightRequest])
    </div>
@endif
