@can('supplies.item-categories.edit')
    <a class="btn btn-icon btn-primary btn-sm" href="javascript:void(0)"
        onclick="editItemCategory('{{ $model->id }}')" title="Edit">
        <i class="fas fa-pen-square"></i>
    </a>
@endcan
@can('supplies.item-categories.delete')
    <form action="{{ route('supplies.item-categories.destroy', $model) }}" method="post" class="d-inline"
        onsubmit="return confirm('Delete this Item Category?')">
        @method('delete')
        @csrf
        <button class="btn btn-icon btn-danger btn-sm" title="Delete"><i class="fas fa-times"></i></button>
    </form>
@endcan

@can('supplies.item-categories.edit')
    <div class="modal fade text-left" id="modal-edit-{{ $model->id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('supplies.item-categories.update', $model) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h4 class="modal-title">Edit Item Category</h4>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @include('supplies.categories._form-fields', ['model' => $model])
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan
