<!-- Action column for approval requests -->
<div class="btn-group">
    <input type="checkbox" class="approval-checkbox" value="{{ $id }}">
    <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-cog"></i> Actions
    </button>
    <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('approval.requests.show', $id) }}">
            <i class="fas fa-eye"></i> View Details
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-success" href="#" onclick="showApprovalModal({{ $id }})">
            <i class="fas fa-check"></i> Approve/Reject
        </a>
    </div>
</div>
