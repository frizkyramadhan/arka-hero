<div class="modal fade text-left" id="modal-unit">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Employee - Add Job Experience Data</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('operableunits/'.$employee->id) }}" method="POST">
        <input type="hidden" name="employee_id" value="{{ old('employee_id', $employee->id) }}">
        @csrf
        <div class="modal-body">
          <div class="card-body">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Unit Name</label>
              <div class="col-sm-10">
                <select name="unit_name" class="form-control" style="width: 100%;">
                  <option value="LV / SARANA" {{ old('unit_name') == 'LV / SARANA' ? 'selected' : '' }}>LV / SARANA</option>
                  <option value="DUMP TRUCK" {{ old('unit_name') == 'DUMP TRUCK' ? 'selected' : '' }}>DUMP TRUCK</option>
                  <option value="ADT" {{ old('unit_name') == 'ADT' ? 'selected' : '' }}>ADT</option>
                  <option value="EXCAVATOR" {{ old('unit_name') == 'EXCAVATOR' ? 'selected' : '' }}>EXCAVATOR</option>
                  <option value="DOZER" {{ old('unit_name') == 'DOZER' ? 'selected' : '' }}>DOZER</option>
                  <option value="GRADER" {{ old('unit_name') == 'GRADER' ? 'selected' : '' }}>GRADER</option>
                </select>
                @error('unit_name')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Unit Type</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('unit_type') is-invalid @enderror" name="unit_type" value="{{ old('unit_type') }}">
                @error('unit_type')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Remarks</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('unit_remarks') is-invalid @enderror" name="unit_remarks" value="{{ old('unit_remarks') }}">
                @error('unit_remarks')
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




@foreach ($units as $unit)
<div class="modal fade text-left" id="modal-unit-{{ $unit->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Employee - Edit Course Data</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('operableunits/' . $unit->id) }}" method="POST">
        <input type="hidden" name="employee_id" value="{{ old('employee_id', $unit->employee_id) }}">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <div class="card-body">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Unit Name</label>
              <div class="col-sm-10">
                <select name="unit_name" class="form-control" style="width: 100%;">
                  <option value="LV / SARANA" {{ old('unit_name', $unit->unit_name) == 'LV / SARANA' ? 'selected' : '' }}>LV / SARANA</option>
                  <option value="DUMP TRUCK" {{ old('unit_name', $unit->unit_name) == 'DUMP TRUCK' ? 'selected' : '' }}>DUMP TRUCK</option>
                  <option value="ADT" {{ old('unit_name', $unit->unit_name) == 'ADT' ? 'selected' : '' }}>ADT</option>
                  <option value="EXCAVATOR" {{ old('unit_name', $unit->unit_name) == 'EXCAVATOR' ? 'selected' : '' }}>EXCAVATOR</option>
                  <option value="DOZER" {{ old('unit_name', $unit->unit_name) == 'DOZER' ? 'selected' : '' }}>DOZER</option>
                  <option value="GRADER" {{ old('unit_name', $unit->unit_name) == 'GRADER' ? 'selected' : '' }}>GRADER</option>
                </select>
                @error('unit_name')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Unit Type</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('unit_type') is-invalid @enderror" name="unit_type" value="{{ old('unit_type', $unit->unit_type) }}">
                @error('unit_type')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Remarks</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('unit_remarks') is-invalid @enderror" name="unit_remarks" value="{{ old('unit_remarks', $unit->unit_remarks) }}">
                @error('unit_remarks')
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
