@can('fuel-bot-subscribers.edit')
<a class="btn btn-icon btn-primary btn-sm" href="javascript:void(0)"
    onclick="editFuelBotSubscriber('{{ $model->id }}')" title="Edit">
    <i class="fas fa-pen-square"></i>
</a>
@endcan
@can('fuel-bot-subscribers.delete')
<form action="{{ route('fuel-bot-subscribers.destroy', $model) }}" method="post" class="d-inline"
    onsubmit="return confirm('Remove this subscriber from whitelist?')">
    @method('delete')
    @csrf
    <button type="submit" class="btn btn-icon btn-danger btn-sm" title="Delete">
        <i class="fas fa-times"></i>
    </button>
</form>
@endcan

@can('fuel-bot-subscribers.edit')
<div class="modal fade text-left" id="modal-edit-{{ $model->id }}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('fuel-bot-subscribers.update', $model) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h4 class="modal-title">Edit Subscriber</h4>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    @include('fuel-bot-subscribers._form-fields', [
                        'model' => $model,
                        'users' => $users,
                        'prefix' => 'edit_'.$model->id.'_',
                    ])
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
