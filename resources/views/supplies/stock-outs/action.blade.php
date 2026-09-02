<a href="{{ route('supplies.stock-outs.show', $model) }}" class="btn btn-info btn-sm" title="View">
    <i class="fas fa-eye"></i>
</a>
@can('supplies.stock-out.delete')
    <form action="{{ route('supplies.stock-outs.destroy', $model) }}" method="post" class="d-inline"
        onsubmit="return confirm('Delete this Stock Out?')">
        @method('delete')
        @csrf
        <button class="btn btn-icon btn-danger btn-sm" title="Delete"><i class="fas fa-times"></i></button>
    </form>
@endcan
