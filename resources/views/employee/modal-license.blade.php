<div class="modal fade text-left" id="modal-license">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Employee - Add License Data</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('licenses/'.$employee->id) }}" method="POST">
        <input type="hidden" name="employee_id" value="{{ old('employee_id', $employee->id) }}">
        @csrf
        <div class="modal-body">
          <div class="card-body">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">License Type</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('driver_license_type') is-invalid @enderror" name="driver_license_type" value="{{ old('driver_license_type') }}">
                @error('driver_license_name')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">License No</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('driver_license_no') is-invalid @enderror" name="driver_license_no" value="{{ old('driver_license_no') }}">
                @error('driver_license_no')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Validity Period</label>
              <div class="col-sm-10">
                <input type="date" class="form-control @error('driver_license_exp') is-invalid @enderror" name="driver_license_exp" value="{{ old('driver_license_exp') }}">
                @error('driver_license_exp')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>




@foreach ($licenses as $license)
<div class="modal fade text-left" id="modal-license-{{ $license->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Employee - Edit Course Data</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('licenses/' . $license->id) }}" method="POST">
        <input type="hidden" name="employee_id" value="{{ old('employee_id', $license->employee_id) }}">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <div class="card-body">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">License Type</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('driver_license_type') is-invalid @enderror" name="driver_license_type" value="{{ old('driver_license_type', $license->driver_license_type) }}">
                @error('driver_license_name')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">License No</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('driver_license_no') is-invalid @enderror" name="driver_license_no" value="{{ old('driver_license_no', $license->driver_license_no) }}">
                @error('driver_license_no')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Validity Period</label>
              <div class="col-sm-10">
                <input type="date" class="form-control @error('driver_license_exp') is-invalid @enderror" name="driver_license_exp" value="{{ old('driver_license_exp', $license->driver_license_exp) }}">
                @error('driver_license_exp')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
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
@endforeach
