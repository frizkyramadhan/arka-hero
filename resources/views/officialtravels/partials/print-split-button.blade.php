@php
    /** @var \App\Models\Officialtravel $officialtravel */
    $officialtravel->loadMissing('stops');
    $printStops = $officialtravel->stops->sortBy(['sort_order', 'id'])->values();
    $btnClass = $btnClass ?? 'btn-primary';
@endphp
@if ($printStops->isNotEmpty())
    <div class="btn-group {{ $wrapperClass ?? '' }}" role="group">
        <a href="{{ route('officialtravels.print', $officialtravel) }}" class="btn {{ $btnClass }}"
            target="_blank" rel="noopener noreferrer">
            <i class="fas fa-print"></i> {{ $label ?? 'Print' }}
        </a>
        <button type="button" class="btn {{ $btnClass }} dropdown-toggle dropdown-toggle-split"
            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            aria-label="Print one destination">
            <span class="sr-only">Toggle print menu</span>
        </button>
        <div class="dropdown-menu {{ ($menuRight ?? true) ? 'dropdown-menu-right' : '' }}">
            <h6 class="dropdown-header">Print one destination</h6>
            @foreach ($printStops as $idx => $stop)
                <a class="dropdown-item small" target="_blank" rel="noopener noreferrer"
                    href="{{ route('officialtravels.print', ['officialtravel' => $officialtravel, 'stop' => $stop->id]) }}">
                    <span class="text-muted mr-1 font-weight-bold">{{ $idx + 1 }}.</span>
                    {{ \Illuminate\Support\Str::limit($stop->destination, 72) }}
                </a>
            @endforeach
        </div>
    </div>
@else
    <a href="{{ route('officialtravels.print', $officialtravel) }}" class="btn {{ $btnClass }} {{ $wrapperClass ?? '' }}"
        target="_blank" rel="noopener noreferrer">
        <i class="fas fa-print"></i> {{ $label ?? 'Print' }}
    </a>
@endif
