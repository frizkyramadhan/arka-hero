<div class="d-flex">
    <a href="{{ route('letter-numbers.show', $row->id) }}" class="btn btn-sm btn-info mr-1" title="View Details">
        <i class="fas fa-eye"></i>
    </a>

    @if ($row->status === 'reserved')
        <a href="{{ route('letter-numbers.edit', $row->id) }}" class="btn btn-sm btn-warning mr-1" title="Edit">
            <i class="fas fa-edit"></i>
        </a>
        <button type="button" class="btn btn-sm btn-secondary btn-cancel mr-1" data-id="{{ $row->id }}"
            data-letter-number="{{ $row->letter_number }}" title="Cancel"
            onclick="cancelLetterNumber({{ $row->id }}, '{{ $row->letter_number }}')">
            <i class="fas fa-ban"></i>
        </button>
    @endif

    @if ($row->status === 'reserved' && !$row->related_document_id)
        <button type="button" class="btn btn-sm btn-danger btn-delete mr-1" data-id="{{ $row->id }}"
            data-letter-number="{{ $row->letter_number }}" title="Delete"
            onclick="deleteLetterNumber({{ $row->id }}, '{{ $row->letter_number }}')">
            <i class="fas fa-trash"></i>
        </button>
    @endif

    @if ($row->related_document_type && $row->related_document_id)
        @php
            $documentLink = '#';
            switch ($row->related_document_type) {
                case 'officialtravel':
                    $documentLink = route('officialtravels.show', $row->related_document_id);
                    break;
                case 'recruitment_request':
                    $documentLink = route('recruitment.requests.show', $row->related_document_id);
                    break;
            }
        @endphp
        <a href="{{ $documentLink }}" class="btn btn-sm btn-success" title="View Document">
            <i class="fas fa-external-link-alt"></i>
        </a>
    @endif
</div>
