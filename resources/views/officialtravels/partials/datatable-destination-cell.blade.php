@php
    $destList = $travel->itineraryDestinationList();
@endphp
@if ($destList->count() > 1)
    <ul class="mb-0 pl-3 small">
        @foreach ($destList as $dest)
            <li>{{ $dest }}</li>
        @endforeach
    </ul>
@elseif ($destList->count() === 1)
    <span class="small">{{ $destList->first() }}</span>
@else
    <span class="text-muted small">—</span>
@endif
