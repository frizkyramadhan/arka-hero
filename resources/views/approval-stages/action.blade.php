<!-- Action column for approval stages -->

<a href="{{ route('approval.stages.edit', $id) }}" class="btn btn-primary" title="Edit">
    <i class="fas fa-edit"></i>
</a>
<button type="button" class="btn btn-danger" onclick="deleteApprovalStage({{ $id }})" title="Delete">
    <i class="fas fa-trash"></i>
</button>
