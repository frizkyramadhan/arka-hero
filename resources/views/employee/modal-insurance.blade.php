<div class="modal fade text-left" id="modal-insurance">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Employee - Add Health Insurance Data</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('insurances/'.$employee->id) }}" method="POST">
        <input type="hidden" name="employee_id" value="{{ old('employee_id', $employee->id) }}">
        @csrf
        <div class="modal-body">
          <div class="card-body">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Insurance</label>
              <div class="col-sm-10">
                <select name="health_insurance_type" class="form-control @error('health_insurance_type') is-invalid @enderror">
                  <option value="bpjsks" {{ old('health_insurance_type') == 'bpjsks' ? 'selected' : '' }}>BPJS Kesehatan
                  </option>
                  <option value="bpjskt" {{ old('health_insurance_type') == 'bpjskt' ? 'selected' : '' }}>BPJS Ketenagakerjaan
                  </option>
                </select>
                @error('health_insurance_type')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Insurance No.</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('health_insurance_no') is-invalid @enderror" name="health_insurance_no" value="{{ old('health_insurance_no') }}">
                @error('health_insurance_no')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Health Facility</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('health_facility') is-invalid @enderror" name="health_facility" value="{{ old('health_facility') }}">
                @error('health_facility')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Remarks</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('health_insurance_remarks') is-invalid @enderror" name="health_insurance_remarks" value="{{ old('health_insurance_remarks') }}">
                @error('health_insurance_remarks')
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




@foreach ($insurances as $insurance)
<div class="modal fade text-left" id="modal-insurance-{{ $insurance->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Employee - Health Insurance Data</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('insurances/' . $insurance->id) }}" method="POST">
        <input type="hidden" name="employee_id" value="{{ old('employee_id', $insurance->employee_id) }}">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <div class="card-body">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Insurance</label>
              <div class="col-sm-10">
                <select name="health_insurance_type" class="form-control @error('health_insurance_type') is-invalid @enderror">
                  <option value="bpjsks" {{ old('health_insurance_type', $insurance->health_insurance_type) == 'bpjsks' ? 'selected' : '' }}>BPJS Kesehatan
                  </option>
                  <option value="bpjskt" {{ old('health_insurance_type', $insurance->health_insurance_type) == 'bpjskt' ? 'selected' : '' }}>BPJS Ketenagakerjaan
                  </option>
                </select>
                @error('health_insurance_type')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Insurance No.</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('health_insurance_no') is-invalid @enderror" name="health_insurance_no" value="{{ old('health_insurance_no', $insurance->health_insurance_no) }}">
                @error('health_insurance_no')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Health Facility</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('health_facility') is-invalid @enderror" name="health_facility" value="{{ old('health_facility', $insurance->health_facility) }}">
                @error('health_facility')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Remarks</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('health_insurance_remarks') is-invalid @enderror" name="health_insurance_remarks" value="{{ old('health_insurance_remarks', $insurance->health_insurance_remarks) }}">
                @error('health_insurance_remarks')
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
