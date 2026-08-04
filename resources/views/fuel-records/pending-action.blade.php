@can('fuel-records.show')
<a href="{{ route('fuel-records.show', $model) }}" class="btn btn-info btn-sm" title="Detail">
    <i class="fas fa-eye"></i>
</a>
@endcan
<form action="{{ route('fuel-records.verify', $model) }}" method="POST" class="d-inline">
    @csrf
    <button type="submit" class="btn btn-success btn-sm" title="Verify"
        onclick="return confirm('Verify this receipt?')">
        <i class="fas fa-check"></i>
    </button>
</form>
<button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
    data-target="#reject-{{ $model->id }}" title="Reject">
    <i class="fas fa-times"></i>
</button>

<div class="modal fade" id="reject-{{ $model->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('fuel-records.reject', $model) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject fuel record</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Reason <span class="text-danger">*</span></label>
                        <textarea name="verification_notes" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </div>
        </form>
    </div>
</div>
