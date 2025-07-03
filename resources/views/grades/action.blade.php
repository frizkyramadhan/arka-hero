<button class="btn btn-icon btn-primary me-1" data-toggle="modal" data-target="#edit-grade-modal"
    data-id="{{ $id }}" data-name="{{ $model->name }}" data-status="{{ $model->is_active }}">
    <i class="fas fa-pen-square"></i>
</button>

<form action="{{ route('grades.destroy', $id) }}" method="POST" style="display:inline;"
    onsubmit="return confirm('Are you sure you want to delete this grade?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-icon btn-danger"><i class="fas fa-times"></i></button>
</form>

<form action="{{ route('grades.status', $id) }}" method="POST" style="display:inline;">
    @csrf
    <button type="submit" class="btn btn-icon {{ $model->is_active ? 'btn-success' : 'btn-secondary' }}"
        title="{{ $model->is_active ? 'Deactivate' : 'Activate' }}">
        @if ($model->is_active)
            <i class="fas fa-toggle-on"></i>
        @else
            <i class="fas fa-toggle-off"></i>
        @endif
    </button>
</form>
