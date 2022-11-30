<a class="btn btn-icon btn-primary" href="{{ url('departments/' . $model->id . '/edit') }}" data-toggle="modal" data-target="#modal-lg-{{ $model->id }}"><i class="fas fa-pen-square"></i></a>
<form action="{{ url('departments/' . $model->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
  @method('delete')
  @csrf
  <button class="btn btn-icon btn-danger"><i class="fas fa-times"></i></button>
</form>

<div class="modal fade text-left" id="modal-lg-{{ $model->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Department</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('departments/' . $model->id) }}" method="POST">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <div class="card-body">
            <div class="tab-content p-0">
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">Department</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control @error('department_name') is-invalid @enderror" name="department_name" value="{{ old('department_name', $model->department_name) }}" placeholder="Department Name">
                  @error('department_name')
                  <div class="error invalid-feedback">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">Slug</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control @error('slug') is-invalid @enderror" name="slug" value="{{ old('slug', $model->slug) }}" placeholder="Bowheer">
                  @error('slug')
                  <div class="error invalid-feedback">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">Status</label>
                <div class="col-sm-10">
                  <select name="department_status" class="form-control @error('department_status') is-invalid @enderror">
                    <option value="1" {{ old('department_status', $model->department_status) == '1' ? 'selected' : '' }}>
                      Active</option>
                    <option value="0" {{ old('department_status', $model->department_status) == '0' ? 'selected' : '' }}>Inactive
                    </option>
                  </select>
                  @error('department_status')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
