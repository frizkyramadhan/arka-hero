@if ($model)
    <a href="{{ route('approval.requests.show', $model->id) }}" class="btn btn-sm btn-primary" title="Review">
        <i class="fas fa-eye"></i>
    </a>
@else
    <span class="text-muted">No action available</span>
@endif
