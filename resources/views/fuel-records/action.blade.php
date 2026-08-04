@can('fuel-records.show')
<a href="{{ route('fuel-records.show', $model) }}" class="btn btn-info btn-sm" title="Detail">
    <i class="fas fa-eye"></i>
</a>
@endcan
@can('fuel-records.edit')
<a href="{{ route('fuel-records.edit', $model) }}" class="btn btn-primary btn-sm" title="Edit">
    <i class="fas fa-edit"></i>
</a>
@endcan
@can('fuel-records.delete')
<form action="{{ route('fuel-records.destroy', $model) }}" method="POST" class="d-inline"
    onsubmit="return confirm('Are you sure you want to delete this fuel record?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
        <i class="fas fa-trash"></i>
    </button>
</form>
@endcan
