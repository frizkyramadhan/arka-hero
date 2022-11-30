<a class="btn btn-icon btn-primary" href="{{ url('positions/' . $position->id . '/edit') }}" data-toggle="modal" data-target="#modal-lg-{{ $position->id }}"><i class="fas fa-pen-square"></i></a>
<form action="{{ url('positions/' . $position->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
  @method('delete')
  @csrf
  <button class="btn btn-icon btn-danger"><i class="fas fa-times"></i></button>
</form>

<div class="modal fade text-left" id="modal-lg-{{ $position->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Position</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('positions/' . $position->id) }}" method="POST">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <div class="card-body">
            <div class="tab-content p-0">
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">Position</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control @error('position_name') is-invalid @enderror" name="position_name" value="{{ old('position_name', $position->position_name) }}" placeholder="Position Name">
                  @error('position_name')
                  <div class="error invalid-feedback">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">Department</label>
                <div class="col-sm-10">
                  <select name="department_id" class="form-control @error('department_id') is-invalid @enderror select2bs4">
                    <option value="">- Select Department -</option>
                    @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" {{ old('department_id', $position->department_id) == $dept->id ? 'selected' : null }}>
                      {{ $dept->department_name }}</option>
                    @endforeach
                  </select>
                  @error('department_id')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">Status</label>
                <div class="col-sm-10">
                  <select name="position_status" class="form-control @error('position_status') is-invalid @enderror">
                    <option value="1" {{ old('position_status', $position->position_status) == '1' ? 'selected' : '' }}>
                      Active</option>
                    <option value="0" {{ old('position_status', $position->position_status) == '0' ? 'selected' : '' }}>Inactive
                    </option>
                  </select>
                  @error('position_status')
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
