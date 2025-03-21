<a class="btn btn-icon btn-primary" href="{{ url('permissions/' . $model->id . '/edit') }}"><i class="fas fa-pen-square"></i></a>
<form action="{{ url('permissions/' . $model->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
  @method('delete')
  @csrf
  <button class="btn btn-icon btn-danger"><i class="fas fa-times"></i></button>
</form>