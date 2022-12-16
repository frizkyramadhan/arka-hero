<div class="modal fade text-left" id="modal-administration">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Employee - Add Administration Data</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('administrations/'.$employee->id) }}" method="POST">
        <input type="hidden" name="employee_id" value="{{ old('employee_id', $employee->id) }}">
        <input type="hidden" name="is_active" value="1">
        @csrf
        <div class="modal-body">
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="nik" class="form-label">NIK</label>
                  <input type="text" value="{{ old('nik') }}" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik">
                  @if ($errors->any('nik'))
                  <span class="text-danger">{{ ($errors->first('nik')) }}</span>
                  @endif
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="pants_size" class="form-label">Class</label>
                  <div class="row mt-2">
                    <div class="col-6">
                      <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="class1" name="class" value="Staff" {{ old('class') == "Staff" ? 'checked' : '' }}>
                        <label for="class1" class="custom-control-label">Staff</label>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="class2" name="class" value="Non Staff" {{ old('class') == "Non Staff" ? 'checked' : '' }}>
                        <label for="class2" class="custom-control-label">Non Staff</label>
                      </div>
                    </div>
                    @if ($errors->any('class'))
                    <span class="text-danger">{{ ($errors->first('class')) }}</span>
                    @endif
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="doh" class="form-label">DOH</label>
                  <input type="date" value="{{ old('doh') }}" class="form-control @error('doh') is-invalid @enderror" id="doh" name="doh">
                  @if ($errors->any('doh'))
                  <span class="text-danger">{{ ($errors->first('doh')) }}</span>
                  @endif
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="poh" class="form-label">POH</label>
                  <input type="text" value="{{ old('poh') }}" class="form-control @error('poh') is-invalid @enderror" id="poh" name="poh">
                  @if ($errors->any('poh'))
                  <span class="text-danger">{{ ($errors->first('poh')) }}</span>
                  @endif
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="basic_salary" class="form-label">Basic Salary</label>
                  <input type="number" value="{{ old('basic_salary') }}" class="form-control @error('basic_salary') is-invalid @enderror" id="basic_salary" name="basic_salary">
                  @if ($errors->any('basic_salary'))
                  <span class="text-danger">{{ ($errors->first('basic_salary')) }}</span>
                  @endif
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="position_id" class="form-label">Position</label>
                  <select name="position_id" class="form-control @error('position_id') is-invalid @enderror select2bs4 position_id">
                    <option value="">-Select Position-</option>
                    @foreach ($positions as $position)
                    <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>
                      {{ $position->position_name }}
                    </option>
                    @endforeach
                  </select>
                  @if ($errors->any('position_id'))
                  <span class="text-danger">{{ ($errors->first('position_id')) }}</span>
                  @endif
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="site_allowance" class="form-label">Site Allowance</label>
                  <input type="number" value="{{ old('site_allowance') }}" class="form-control @error('site_allowance') is-invalid @enderror" id="site_allowance" name="site_allowance">
                  @if ($errors->any('site_allowance'))
                  <span class="text-danger">{{ ($errors->first('site_allowance')) }}</span>
                  @endif
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="department" class="form-label">Department</label>
                  <input type="text" class="form-control @error('department') is-invalid @enderror department" readonly>
                  @if ($errors->any('department'))
                  <span class="text-danger">{{ ($errors->first('department')) }}</span>
                  @endif
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="other_allowance" class="form-label">Other Allowance</label>
                  <input type="number" value="{{ old('other_allowance') }}" class="form-control @error('other_allowance') is-invalid @enderror" id="other_allowance" name="other_allowance">
                  @if ($errors->any('other_allowance'))
                  <span class="text-danger">{{ ($errors->first('other_allowance')) }}</span>
                  @endif
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="project_id" class="form-label">Project</label>
                  <select name="project_id" class="form-control @error('project_id') is-invalid @enderror select2bs4">
                    <option value="">-Select Project-</option>
                    @foreach ($projects as $project)
                    <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                      {{ $project->project_code }} - {{ $project->project_name }}
                    </option>
                    @endforeach
                  </select>
                  @if ($errors->any('project_id'))
                  <span class="text-danger">{{ ($errors->first('project_id')) }}</span>
                  @endif
                </div>
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




@foreach ($administrations as $administration)
<div class="modal fade text-left" id="modal-administration-{{ $administration->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Employee - Edit Administration Data</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('administrations/' . $administration->id) }}" method="POST">
        <input type="hidden" name="employee_id" value="{{ old('employee_id', $administration->employee_id) }}">
        <input type="hidden" name="is_active" value="1">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="nik" class="form-label">NIK</label>
                  <input type="text" value="{{ old('nik',$administration->nik) }}" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik">
                  @if ($errors->any('nik'))
                  <span class="text-danger">{{ ($errors->first('nik')) }}</span>
                  @endif
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="pants_size" class="form-label">Class</label>
                  <div class="row mt-2">
                    <div class="col-6">
                      <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="class{{ $administration->id*1 }}" name="class" value="Staff" {{ old('class', $administration->class) == "Staff" ? 'checked' : '' }}>
                        <label for="class{{ $administration->id*1 }}" class="custom-control-label">Staff</label>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="class{{ $administration->id*2 }}" name="class" value="Non Staff" {{ old('class', $administration->class) == "Non Staff" ? 'checked' : '' }}>
                        <label for="class{{ $administration->id*2 }}" class="custom-control-label">Non Staff</label>
                      </div>
                    </div>
                    @if ($errors->any('class'))
                    <span class="text-danger">{{ ($errors->first('class')) }}</span>
                    @endif
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="doh" class="form-label">DOH</label>
                  <input type="date" value="{{ old('doh', $administration->doh) }}" class="form-control @error('doh') is-invalid @enderror" id="doh" name="doh">
                  @if ($errors->any('doh'))
                  <span class="text-danger">{{ ($errors->first('doh')) }}</span>
                  @endif
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="poh" class="form-label">POH</label>
                  <input type="text" value="{{ old('poh', $administration->poh) }}" class="form-control @error('poh') is-invalid @enderror" id="poh" name="poh">
                  @if ($errors->any('poh'))
                  <span class="text-danger">{{ ($errors->first('poh')) }}</span>
                  @endif
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="basic_salary" class="form-label">Basic Salary</label>
                  <input type="number" value="{{ old('basic_salary', $administration->basic_salary) }}" class="form-control @error('basic_salary') is-invalid @enderror" id="basic_salary" name="basic_salary">
                  @if ($errors->any('basic_salary'))
                  <span class="text-danger">{{ ($errors->first('basic_salary')) }}</span>
                  @endif
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="position_id" class="form-label">Position</label>
                  <select name="position_id" class="form-control @error('position_id') is-invalid @enderror select2bs4 position_id{{ $administration->id }}">
                    <option value="">-Select Position-</option>
                    @foreach ($positions as $position)
                    <option value="{{ $position->id }}" {{ old('position_id', $administration->position_id) == $position->id ? 'selected' : '' }}>
                      {{ $position->position_name }}
                    </option>
                    @endforeach
                  </select>
                  @if ($errors->any('position_id'))
                  <span class="text-danger">{{ ($errors->first('position_id')) }}</span>
                  @endif
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="site_allowance" class="form-label">Site Allowance</label>
                  <input type="number" value="{{ old('site_allowance', $administration->site_allowance) }}" class="form-control @error('site_allowance') is-invalid @enderror" id="site_allowance" name="site_allowance">
                  @if ($errors->any('site_allowance'))
                  <span class="text-danger">{{ ($errors->first('site_allowance')) }}</span>
                  @endif
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="department" class="form-label">Department</label>
                  <input type="text" value="{{ old('department') }}" class="form-control @error('department') is-invalid @enderror department{{ $administration->id }}" name="department" readonly>
                  @if ($errors->any('department'))
                  <span class="text-danger">{{ ($errors->first('department')) }}</span>
                  @endif
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="other_allowance" class="form-label">Other Allowance</label>
                  <input type="number" value="{{ old('other_allowance', $administration->other_allowance) }}" class="form-control @error('other_allowance') is-invalid @enderror" id="other_allowance" name="other_allowance">
                  @if ($errors->any('other_allowance'))
                  <span class="text-danger">{{ ($errors->first('other_allowance')) }}</span>
                  @endif
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="project_id" class="form-label">Project</label>
                  <select name="project_id" class="form-control @error('project_id') is-invalid @enderror select2bs4">
                    <option value="">-Select Project-</option>
                    @foreach ($projects as $project)
                    <option value="{{ $project->id }}" {{ old('project_id', $administration->project_id) == $project->id ? 'selected' : '' }}>
                      {{ $project->project_code }} - {{ $project->project_name }}
                    </option>
                    @endforeach
                  </select>
                  @if ($errors->any('project_id'))
                  <span class="text-danger">{{ ($errors->first('project_id')) }}</span>
                  @endif
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
@endforeach
