<div class="modal fade text-left" id="modal-job">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Employee - Add Job Experience Data</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('jobexperiences/'.$employee->id) }}" method="POST">
        <input type="hidden" name="employee_id" value="{{ old('employee_id', $employee->id) }}">
        @csrf
        <div class="modal-body">
          <div class="card-body">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Name</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('company_name') is-invalid @enderror" name="company_name" value="{{ old('company_name') }}">
                @error('company_name')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Address</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('company_address') is-invalid @enderror" name="company_address" value="{{ old('company_address') }}">
                @error('company_address')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Position</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('job_position') is-invalid @enderror" name="job_position" value="{{ old('job_position') }}">
                @error('job_position')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Duration</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('job_duration') is-invalid @enderror" name="job_duration" value="{{ old('job_duration') }}">
                @error('job_duration')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Quit Reason</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('quit_reason') is-invalid @enderror" name="quit_reason" value="{{ old('quit_reason') }}">
                @error('quit_reason')
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




@foreach ($jobs as $job)
<div class="modal fade text-left" id="modal-job-{{ $job->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Employee - Edit Course Data</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('jobexperiences/' . $job->id) }}" method="POST">
        <input type="hidden" name="employee_id" value="{{ old('employee_id', $job->employee_id) }}">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <div class="card-body">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Name</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('company_name') is-invalid @enderror" name="company_name" value="{{ old('company_name', $job->company_name) }}">
                @error('company_name')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Address</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('company_address') is-invalid @enderror" name="company_address" value="{{ old('company_address', $job->company_address) }}">
                @error('company_address')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Position</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('job_position') is-invalid @enderror" name="job_position" value="{{ old('job_position',$job->job_position) }}">
                @error('job_position')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Duration</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('job_duration') is-invalid @enderror" name="job_duration" value="{{ old('job_duration', $job->job_duration) }}">
                @error('job_duration')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Quit Reason</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('quit_reason') is-invalid @enderror" name="quit_reason" value="{{ old('quit_reason',$job->quit_reason) }}">
                @error('quit_reason')
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
