@php
    /** @var \App\Models\Officialtravel $officialtravel */
    $itineraryStops = $officialtravel->stops;
@endphp
<div class="info-item info-item-itinerary">
    <div class="info-icon" style="background-color: #3498db;">
        <i class="fas fa-route"></i>
    </div>
    <div class="info-content">
        <div class="info-label">Destinations</div>
        <div class="info-value">
            @if ($itineraryStops->isNotEmpty())
                <ol class="mb-0 pl-3 officialtravel-itinerary-list">
                    @foreach ($itineraryStops as $stop)
                        <li class="mb-1">
                            <span class="font-weight-medium">{{ $stop->destination }}</span>
                        </li>
                    @endforeach
                </ol>
            @else
                {{ $officialtravel->destination ?: '—' }}
            @endif
        </div>
    </div>
</div>
