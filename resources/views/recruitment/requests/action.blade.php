<!-- View button -->
<a href="{{ route('recruitment.requests.show', $fptk->id) }}" class="btn btn-icon btn-info btn-sm" title="View Details">
    <i class="fas fa-eye"></i>
</a>

{{-- <!-- Edit button - only for draft status -->
@if ($fptk->final_status == 'draft')
    @can('recruitment-requests.edit')
        <a href="{{ route('recruitment.requests.edit', $fptk->id) }}" class="btn btn-icon btn-primary btn-sm" title="Edit">
            <i class="fas fa-pen-square"></i>
        </a>
    @endcan
@endif

<!-- Delete button - only for draft status -->
@if ($fptk->final_status == 'draft')
    @can('recruitment-requests.delete')
        <form action="{{ route('recruitment.requests.destroy', $fptk->id) }}" method="post"
            onsubmit="return confirm('Are you sure you want to delete this FPTK?')" class="d-inline">
            @method('delete')
            @csrf
            <button class="btn btn-icon btn-danger btn-sm" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    @endcan
@endif

<!-- Submit button - for draft status -->
@if ($fptk->final_status == 'draft')
    @can('recruitment-requests.submit')
        <form action="{{ route('recruitment.requests.submit', $fptk->id) }}" method="post"
            onsubmit="return confirm('Are you sure you want to submit this FPTK for approval?')" class="d-inline">
            @csrf
            <button class="btn btn-icon btn-warning btn-sm" title="Submit for Approval">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    @endcan
@endif

<!-- HR Acknowledgment button - for submitted status -->
@if ($fptk->final_status == 'submitted' && $fptk->known_status == 'pending' && Auth::id() == $fptk->known_by)
    @can('recruitment-requests.acknowledge')
        <a href="{{ route('recruitment.requests.acknowledge-form', $fptk->id) }}" class="btn btn-icon btn-info btn-sm"
            title="HR Acknowledgment">
            <i class="fas fa-user-check"></i>
        </a>
    @endcan
@endif

<!-- Project Manager Approval button - for HR approved status -->
@if ($fptk->known_status == 'approved' && $fptk->pm_approval_status == 'pending' && Auth::id() == $fptk->approved_by_pm)
    @can('recruitment-requests.approve')
        <a href="{{ route('recruitment.requests.approve-pm-form', $fptk->id) }}" class="btn btn-icon btn-warning btn-sm"
            title="PM Approval">
            <i class="fas fa-user-shield"></i>
        </a>
    @endcan
@endif

<!-- Director Approval button - for PM approved status -->
@if ($fptk->pm_approval_status == 'approved' && $fptk->director_approval_status == 'pending' && Auth::id() == $fptk->approved_by_director)
    @can('recruitment-requests.approve')
        <a href="{{ route('recruitment.requests.approve-director-form', $fptk->id) }}"
            class="btn btn-icon btn-success btn-sm" title="Director Approval">
            <i class="fas fa-crown"></i>
        </a>
    @endcan
@endif

<!-- Legacy Approve button - for submitted status (backward compatibility) -->
@if ($fptk->final_status == 'submitted')
    @can('recruitment-requests.approve')
        <button class="btn btn-icon btn-success btn-sm" title="Approve" onclick="showApprovalModal({{ $fptk->id }})">
            <i class="fas fa-check-circle"></i>
        </button>
    @endcan
@endif

<!-- Legacy Reject button - for submitted status (backward compatibility) -->
@if ($fptk->final_status == 'submitted')
    @can('recruitment-requests.reject')
        <button class="btn btn-icon btn-danger btn-sm" title="Reject" onclick="showRejectionModal({{ $fptk->id }})">
            <i class="fas fa-times-circle"></i>
        </button>
    @endcan
@endif

<!-- Assign Letter Number button - for approved status without letter number -->
@if ($fptk->status == 'approved' && !$fptk->hasLetterNumber())
    @can('recruitment-requests.assign-letter-number')
        <form action="{{ route('recruitment.requests.assign-letter-number', $fptk->id) }}" method="post"
            onsubmit="return confirm('Are you sure you want to assign a letter number to this FPTK?')" class="d-inline">
            @csrf
            <button class="btn btn-icon btn-secondary btn-sm" title="Assign Letter Number">
                <i class="fas fa-file-signature"></i>
            </button>
        </form>
    @endcan
@endif --}}
