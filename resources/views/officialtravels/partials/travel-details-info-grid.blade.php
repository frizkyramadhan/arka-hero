@php
    /** @var \App\Models\Officialtravel $officialtravel */
@endphp

<div class="travel-details-info-cols">
    <div class="travel-detail-cell travel-detail-cell--itinerary">
        @include('officialtravels.partials.destination-itinerary-display')
    </div>
    <div class="travel-detail-cell travel-detail-cell--purpose">
        <div class="info-item">
            <div class="info-icon" style="background-color: #e74c3c;">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="info-content">
                <div class="info-label">Purpose</div>
                <div class="info-value">{{ $officialtravel->purpose }}</div>
            </div>
        </div>
    </div>
    <div class="travel-detail-cell travel-detail-cell--departure">
        <div class="info-item">
            <div class="info-icon" style="background-color: #9b59b6;">
                <i class="fas fa-calendar-plus"></i>
            </div>
            <div class="info-content">
                <div class="info-label">Departure Date</div>
                <div class="info-value">
                    {{ format_date_with_weekday($officialtravel->departure_from) }}</div>
            </div>
        </div>
    </div>
    <div class="travel-detail-cell travel-detail-cell--transport">
        <div class="info-item">
            <div class="info-icon" style="background-color: #1abc9c;">
                <i class="fas fa-bus"></i>
            </div>
            <div class="info-content">
                <div class="info-label">Transportation</div>
                <div class="info-value">
                    {{ $officialtravel->transportation->transportation_name ?? 'No Transportation' }}
                </div>
            </div>
        </div>
    </div>
    <div class="travel-detail-cell travel-detail-cell--accommodation">
        <div class="info-item">
            <div class="info-icon" style="background-color: #e67e22;">
                <i class="fas fa-hotel"></i>
            </div>
            <div class="info-content">
                <div class="info-label">Accommodation</div>
                <div class="info-value">
                    {{ $officialtravel->accommodation->accommodation_name ?? 'No Accommodation' }}
                </div>
            </div>
        </div>
    </div>
    <div class="travel-detail-cell travel-detail-cell--duration">
        <div class="info-item">
            <div class="info-icon" style="background-color: #f1c40f;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="info-content">
                <div class="info-label">Duration</div>
                <div class="info-value">{{ $officialtravel->duration }}</div>
            </div>
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            .travel-details-info-cols {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
                column-gap: 1.5rem;
                row-gap: 1.25rem;
                align-items: start;
                width: 100%;
            }

            .travel-details-info-cols .travel-detail-cell {
                min-width: 0;
            }

            .travel-details-info-cols .travel-detail-cell .info-item {
                margin: 0;
            }

            /* Kolom kiri */
            .travel-detail-cell--itinerary {
                grid-column: 1;
                grid-row: 1;
            }

            .travel-detail-cell--purpose {
                grid-column: 1;
                grid-row: 2;
            }

            .travel-detail-cell--departure {
                grid-column: 1;
                grid-row: 3;
            }

            /* Kolom kanan — sejajar dengan baris kiri (Transportation | Accommodation | Duration) */
            .travel-detail-cell--transport {
                grid-column: 2;
                grid-row: 1;
            }

            .travel-detail-cell--accommodation {
                grid-column: 2;
                grid-row: 2;
            }

            .travel-detail-cell--duration {
                grid-column: 2;
                grid-row: 3;
            }

            @media (max-width: 992px) {
                .travel-details-info-cols {
                    display: flex;
                    flex-direction: column;
                    gap: 1.25rem;
                    row-gap: unset;
                    column-gap: unset;
                }

                .travel-details-info-cols .travel-detail-cell {
                    grid-column: unset !important;
                    grid-row: unset !important;
                }
            }
        </style>
    @endpush
@endonce
