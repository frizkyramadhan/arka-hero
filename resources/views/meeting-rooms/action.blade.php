@can('meeting-rooms.edit')
    <a class="btn btn-icon btn-primary btn-sm" href="javascript:void(0)"
        onclick="editMeetingRoom('{{ $model->id }}')" title="Edit">
        <i class="fas fa-pen-square"></i>
    </a>
@endcan
@can('meeting-rooms.delete')
    <form action="{{ route('meeting-rooms.destroy', $model) }}" method="post" class="d-inline"
        onsubmit="return confirm('Are you sure you want to delete this room?')">
        @method('delete')
        @csrf
        <button class="btn btn-icon btn-danger btn-sm" title="Delete"><i class="fas fa-times"></i></button>
    </form>
@endcan

@can('meeting-rooms.edit')
    <div class="modal fade text-left" id="modal-edit-{{ $model->id }}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('meeting-rooms.update', $model) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h4 class="modal-title">Edit Meeting Room</h4>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @include('meeting-rooms._form-fields', ['model' => $model])
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
