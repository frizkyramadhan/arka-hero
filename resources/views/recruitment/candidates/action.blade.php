<!-- View Button -->
@can('recruitment-candidates.show')
    <a href="{{ route('recruitment.candidates.show', $candidate->id) }}" class="btn btn-sm btn-info" title="View Details">
        <i class="fas fa-eye"></i>
    </a>
@endcan

<!-- Edit Button -->
@can('recruitment-candidates.edit')
    <a href="{{ route('recruitment.candidates.edit', $candidate->id) }}" class="btn btn-sm btn-warning" title="Edit Candidate">
        <i class="fas fa-edit"></i>
    </a>
@endcan

<!-- Delete Button -->
@can('recruitment-candidates.delete')
    <form action="{{ route('recruitment.candidates.destroy', $candidate->id) }}" method="POST" style="display: inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" title="Delete Candidate"
            onclick="return confirm('Are you sure you want to delete this candidate? This action cannot be undone.')">
            <i class="fas fa-trash"></i>
        </button>
    </form>
@endcan

<!-- Apply to FPTK Button -->
@if ($candidate->global_status === 'available')
    <button type="button" class="btn btn-sm btn-primary btn-apply" data-id="{{ $candidate->id }}"
        title="Apply to FPTK">
        <i class="fas fa-plus"></i>
    </button>
@endif
