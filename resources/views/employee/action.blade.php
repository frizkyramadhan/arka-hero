<div class="btn-group">
  <a title="Detail" class="btn btn-sm btn-icon btn-success" href="{{ url('employees/' . $model->id) }}"><i class="fas fa-info-circle"></i></a>
  @can('superadmin')
  <button type="button" class="btn btn-sm btn-success dropdown-toggle dropdown-icon" data-toggle="dropdown">
    <span class="sr-only">Toggle Dropdown</span>
  </button>
  <div class="dropdown-menu" role="menu">
    <form action="{{ url('employees/' . $model->id) }}" method="post" onsubmit="return confirm('This employee and all his/her data will be deleted. Are you sure?')" class="d-inline">
      @method('delete')
      @csrf
      <button class="dropdown-item bg-danger" title="Delete"><i class="fas fa-times"></i> Delete</button>
    </form>
  </div>
  @endcan
</div>
