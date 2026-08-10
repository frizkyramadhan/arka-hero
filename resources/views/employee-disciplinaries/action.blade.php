@can('employee-disciplinaries.show')
<a class="btn btn-icon btn-info me-1" href="{{ route('employee-disciplinaries.show', $model->id) }}" title="View">
    <i class="fas fa-eye"></i>
</a>
@endcan

@can('employee-disciplinaries.edit')
<a class="btn btn-icon btn-primary me-1" href="{{ route('employee-disciplinaries.edit', $model->id) }}" title="Edit">
    <i class="fas fa-pen-square"></i>
</a>
@if ($model->allowsDeferredDocument())
<button type="button" class="btn btn-icon btn-success me-1 btn-upload-document"
    data-id="{{ $model->id }}"
    data-employee="{{ $model->employee->fullname ?? 'Employee' }}"
    data-type="{{ $model->type_label }}"
    title="Upload Document">
    <i class="fas fa-file-upload"></i>
</button>
@endif
@endcan

@can('employee-disciplinaries.delete')
<form action="{{ route('employee-disciplinaries.destroy', $model->id) }}" method="POST" style="display:inline;"
    onsubmit="return confirm('Are you sure you want to delete this disciplinary record?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-icon btn-danger" title="Delete">
        <i class="fas fa-times"></i>
    </button>
</form>
@endcan
