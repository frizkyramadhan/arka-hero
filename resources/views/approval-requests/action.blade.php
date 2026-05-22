@if ($model)
    @if ((int) $model->status === 0)
        <a href="{{ route('approval.requests.show', $model->id) }}" class="btn btn-sm btn-primary" title="Review">
            <i class="fas fa-eye"></i> Review
        </a>
    @else
        <a href="{{ route('approval.requests.show', $model->id) }}" class="btn btn-sm btn-secondary" title="View">
            <i class="fas fa-eye"></i> View
        </a>
    @endif
@else
    <span class="text-muted">No action available</span>
@endif
