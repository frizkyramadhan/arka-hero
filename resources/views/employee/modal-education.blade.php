<div class="modal fade text-left" id="modal-education">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Employee - Add Education Data</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('educations/'.$employee->id) }}" method="POST">
        <input type="hidden" name="employee_id" value="{{ old('employee_id', $employee->id) }}">
        @csrf
        <div class="modal-body">
          <div class="card-body">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Name</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('education_name') is-invalid @enderror" name="education_name" value="{{ old('education_name') }}">
                @error('education_name')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Address</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('education_address') is-invalid @enderror" name="education_address" value="{{ old('education_address') }}">
                @error('education_address')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Year</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('education_year') is-invalid @enderror" name="education_year" value="{{ old('education_year') }}">
                @error('education_year')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Remarks</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('education_remarks') is-invalid @enderror" name="education_remarks" value="{{ old('education_remarks') }}">
                @error('education_remarks')
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




@foreach ($educations as $education)
<div class="modal fade text-left" id="modal-education-{{ $education->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Employee - Edit Education Data</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('educations/' . $education->id) }}" method="POST">
        <input type="hidden" name="employee_id" value="{{ old('employee_id', $education->employee_id) }}">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <div class="card-body">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Name</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('education_name') is-invalid @enderror" name="education_name" value="{{ old('education_name', $education->education_name) }}">
                @error('education_name')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Address</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('education_address') is-invalid @enderror" name="education_address" value="{{ old('education_address', $education->education_address) }}">
                @error('education_address')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Year</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('education_year') is-invalid @enderror" name="education_year" value="{{ old('education_year', $education->education_year) }}">
                @error('education_year')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Remarks</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('education_remarks') is-invalid @enderror" name="education_remarks" value="{{ old('education_remarks', $education->education_remarks) }}">
                @error('education_remarks')
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
