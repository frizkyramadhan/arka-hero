    @can('flight-issuances.show')
        <a href="{{ route('flight-issuances.show', $issuance->id) }}" class="btn btn-sm btn-info" title="View">
            <i class="fas fa-eye"></i>
        </a>
    @endcan
    @can('flight-issuances.edit')
        @if (!$issuance->flightRequests->contains('status', 'completed'))
            <a href="{{ route('flight-issuances.edit', $issuance->id) }}" class="btn btn-sm btn-warning" title="Edit">
                <i class="fas fa-edit"></i>
            </a>
        @endif
    @endcan
    <a href="{{ route('flight-issuances.print', $issuance->id) }}" class="btn btn-sm btn-primary" target="_blank"
        title="Print">
        <i class="fas fa-print"></i>
    </a>
