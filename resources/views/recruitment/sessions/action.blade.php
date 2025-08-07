<!-- View Button -->
@can('recruitment-sessions.show')
    <a href="{{ route('recruitment.sessions.show', $fptk->id) }}" class="btn btn-sm btn-info" title="View FPTK Details">
        <i class="fas fa-eye"></i>
    </a>
@endcan

<!-- Add Candidate Button -->
@can('recruitment-sessions.create')
    <button type="button" class="btn btn-sm btn-primary add-candidate-btn" data-fptk-id="{{ $fptk->id }}"
        data-fptk-number="{{ $fptk->request_number }}" data-position="{{ $fptk->position->position_name ?? 'N/A' }}"
        title="Add Candidate to FPTK">
        <i class="fas fa-plus"></i>
    </button>
@endcan
