<a class="btn btn-icon btn-info" href="{{ route('users.show', $model->id) }}"><i class="fas fa-eye"></i></a>
<a class="btn btn-icon btn-warning" href="{{ route('users.edit', $model->id) }}"><i class="fas fa-pen-square"></i></a>
<form action="{{ url('users/' . $model->id) }}" method="post"
    onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
    @method('delete')
    @csrf
    <button class="btn btn-icon btn-danger"><i class="fas fa-times"></i></button>
</form>
