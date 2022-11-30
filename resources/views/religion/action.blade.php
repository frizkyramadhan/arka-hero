<a class="btn btn-icon btn-primary" href="{{ url('religions/' . $model->id . '/edit') }}" data-toggle="modal" data-target="#modal-lg-{{ $model->id }}"><i class="fas fa-pen-square"></i></a>
<form action="{{ url('religions/' . $model->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
  @method('delete')
  @csrf
  <button class="btn btn-icon btn-danger"><i class="fas fa-times"></i></button>
</form>

<div class="modal fade text-left" id="modal-lg-{{ $model->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Religion</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('religions/' . $model->id) }}" method="POST">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <div class="card-body">
            <div class="tab-content p-0">
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">Religion</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control @error('religion_name') is-invalid @enderror" name="religion_name" value="{{ old('religion_name', $model->religion_name) }}" placeholder="Religion Name">
                  @error('religion_name')
                  <div class="error invalid-feedback">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">Status</label>
                <div class="col-sm-10">
                  <select name="religion_status" class="form-control @error('religion_status') is-invalid @enderror">
                    <option value="1" {{ old('religion_status', $model->religion_status) == '1' ? 'selected' : '' }}>
                      Active</option>
                    <option value="0" {{ old('religion_status', $model->religion_status) == '0' ? 'selected' : '' }}>Inactive
                    </option>
                  </select>
                  @error('religion_status')
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
