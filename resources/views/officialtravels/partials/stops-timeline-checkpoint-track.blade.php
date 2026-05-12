@php
    /** @var \App\Models\OfficialtravelStop $stop */
    $arrivalDone = $stop->hasArrival();
    $departureDone = $stop->hasDeparture();
@endphp
<div class="stop-checkpoint-track" aria-label="Arrival and departure progress for this destination">
    <div
        class="stop-checkpoint-track__segment {{ $arrivalDone ? 'stop-checkpoint-track__segment--done' : 'stop-checkpoint-track__segment--pending' }}">
        <span class="stop-checkpoint-track__dot"><i class="fas fa-plane-arrival"></i></span>
        <span class="stop-checkpoint-track__text">Arrival</span>
    </div>
    @php
        $connectorClass = 'stop-checkpoint-track__connector';
        if ($arrivalDone && $departureDone) {
            $connectorClass .= ' stop-checkpoint-track__connector--done';
        } elseif ($arrivalDone) {
            $connectorClass .= ' stop-checkpoint-track__connector--active';
        }
    @endphp
    <div class="{{ $connectorClass }}" role="presentation"></div>
    <div
        class="stop-checkpoint-track__segment {{ $departureDone ? 'stop-checkpoint-track__segment--done' : 'stop-checkpoint-track__segment--pending' }}">
        <span class="stop-checkpoint-track__dot"><i class="fas fa-plane-departure"></i></span>
        <span class="stop-checkpoint-track__text">Departure</span>
    </div>
</div>
