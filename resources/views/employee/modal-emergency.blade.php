<div class="modal fade text-left" id="modal-emergency">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Employee - Add Emergency Data</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('emrgcalls/'.$employee->id) }}" method="POST">
        <input type="hidden" name="employee_id" value="{{ old('employee_id', $employee->id) }}">
        @csrf
        <div class="modal-body">
          <div class="card-body">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Status</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('emrg_call_relation') is-invalid @enderror" name="emrg_call_relation" value="{{ old('emrg_call_relation') }}">
                @error('emrg_call_relation')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Name</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('emrg_call_name') is-invalid @enderror" name="emrg_call_name" value="{{ old('emrg_call_name') }}">
                @error('emrg_call_name')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Address</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('emrg_call_address') is-invalid @enderror" name="emrg_call_address" value="{{ old('emrg_call_address') }}">
                @error('emrg_call_address')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Phone</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('emrg_call_phone') is-invalid @enderror" name="emrg_call_phone" value="{{ old('emrg_call_phone') }}">
                @error('emrg_call_phone')
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




@foreach ($emergencies as $emergency)
<div class="modal fade text-left" id="modal-emergency-{{ $emergency->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Employee - Edit Emergency Data</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('emrgcalls/' . $emergency->id) }}" method="POST">
        <input type="hidden" name="employee_id" value="{{ old('employee_id', $emergency->employee_id) }}">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <div class="card-body">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Status</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('emrg_call_relation') is-invalid @enderror" name="emrg_call_relation" value="{{ old('emrg_call_relation', $emergency->emrg_call_relation) }}">
                @error('emrg_call_relation')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Name</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('emrg_call_name') is-invalid @enderror" name="emrg_call_name" value="{{ old('emrg_call_name', $emergency->emrg_call_name) }}">
                @error('emrg_call_name')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Address</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('emrg_call_address') is-invalid @enderror" name="emrg_call_address" value="{{ old('emrg_call_address', $emergency->emrg_call_address) }}">
                @error('emrg_call_address')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Phone</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('emrg_call_phone') is-invalid @enderror" name="emrg_call_phone" value="{{ old('emrg_call_phone', $emergency->emrg_call_phone) }}">
                @error('emrg_call_phone')
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
