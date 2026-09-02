<a href="{{ route('supplies.stock-ins.show', $model) }}" class="btn btn-info btn-sm" title="View">
    <i class="fas fa-eye"></i>
</a>
@can('supplies.stock-in.delete')
    <form action="{{ route('supplies.stock-ins.destroy', $model) }}" method="post" class="d-inline"
        onsubmit="return confirm('Delete this Stock In?')">
        @method('delete')
        @csrf
        <button class="btn btn-icon btn-danger btn-sm" title="Delete"><i class="fas fa-times"></i></button>
    </form>
@endcan
