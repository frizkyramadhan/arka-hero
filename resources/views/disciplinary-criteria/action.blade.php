@can('disciplinary-criteria.edit')
<button type="button" class="btn btn-icon btn-primary me-1" data-toggle="modal" data-target="#edit-criterion-modal"
    data-id="{{ $id }}"
    data-code="{{ $model->code }}"
    data-title="{{ $model->title }}"
    data-description="{{ $model->description }}"
    data-article="{{ $model->article_reference }}"
    data-sanction-type="{{ $model->sanction_type }}"
    data-sort-order="{{ $model->sort_order }}"
    data-status="{{ $model->is_active ? 1 : 0 }}"
    title="Edit">
    <i class="fas fa-pen-square"></i>
</button>
@endcan

@can('disciplinary-criteria.edit')
<form action="{{ route('disciplinary-criteria.status', $id) }}" method="POST" style="display:inline;">
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
@endcan

@can('disciplinary-criteria.delete')
<form action="{{ route('disciplinary-criteria.destroy', $id) }}" method="POST" style="display:inline;"
    onsubmit="return confirm('Are you sure you want to delete this criterion?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-icon btn-danger" title="Delete">
        <i class="fas fa-times"></i>
    </button>
</form>
@endcan
