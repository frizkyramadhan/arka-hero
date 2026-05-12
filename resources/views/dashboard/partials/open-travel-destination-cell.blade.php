@php
    $destList = $travel->itineraryDestinationList();
@endphp
<div>
    @if ($destList->isNotEmpty())
        <ul class="mb-1 pl-3 small font-weight-bold text-dark">
            @foreach ($destList as $dest)
                <li>{{ $dest }}</li>
            @endforeach
        </ul>
    @else
        <div class="font-weight-bold text-muted small mb-1">—</div>
    @endif
    <small class="text-muted">{{ $travel->duration }}</small>
</div>
