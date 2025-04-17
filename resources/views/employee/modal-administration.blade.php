<div class="modal fade text-left" id="modal-administration">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Employment Details</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('administrations/' . $employee->id) }}" method="POST">
                <input type="hidden" name="employee_id" value="{{ old('employee_id', $employee->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="card-body">
                        <h5 class="mb-3 border-bottom pb-2">Employment Details</h5>
                        <input type="hidden" value="1" class="form-control" id="is_active" name="is_active">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nik" class="form-label required-field">Employee ID (NIK)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                        </div>
                                        <input type="text" value="{{ old('nik') }}"
                                            class="form-control @error('nik') is-invalid @enderror" id="nik"
                                            name="nik" placeholder="Enter employee ID">
                                    </div>
                                    @if ($errors->any('nik'))
                                        <span class="text-danger">{{ $errors->first('nik') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="class" class="form-label required-field">Employee Class</label>
                                    <div class="d-flex mt-2">
                                        <div class="custom-control custom-radio custom-control-primary mr-4">
                                            <input class="custom-control-input" type="radio" id="class1"
                                                name="class" value="Staff"
                                                {{ old('class') == 'Staff' ? 'checked' : '' }}>
                                            <label for="class1" class="custom-control-label">Staff</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-primary">
                                            <input class="custom-control-input" type="radio" id="class2"
                                                name="class" value="Non Staff"
                                                {{ old('class') == 'Non Staff' ? 'checked' : '' }}>
                                            <label for="class2" class="custom-control-label">Non Staff</label>
                                        </div>
                                    </div>
                                    @if ($errors->any('class'))
                                        <span class="text-danger">{{ $errors->first('class') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-4 mb-3 text-muted">Hiring Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="doh" class="form-label required-field">Date of Hire</label>
                                    <div class="input-group date">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="date" value="{{ old('doh') }}"
                                            class="form-control @error('doh') is-invalid @enderror" id="doh"
                                            name="doh">
                                    </div>
                                    @if ($errors->any('doh'))
                                        <span class="text-danger">{{ $errors->first('doh') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="poh" class="form-label required-field">Place of Hire</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        </div>
                                        <input type="text" value="{{ old('poh') }}"
                                            class="form-control @error('poh') is-invalid @enderror" id="poh"
                                            name="poh" placeholder="Enter place of hire">
                                    </div>
                                    @if ($errors->any('poh'))
                                        <span class="text-danger">{{ $errors->first('poh') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="foc" class="form-label">First of Contract</label>
                                    <div class="input-group date">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="date" value="{{ old('foc') }}"
                                            class="form-control @error('foc') is-invalid @enderror" id="foc"
                                            name="foc">
                                    </div>
                                    @if ($errors->any('foc'))
                                        <span class="text-danger">{{ $errors->first('foc') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="agreement" class="form-label">Agreement Type</label>
                                    <select name="agreement"
                                        class="form-control select2bs4 @error('agreement') is-invalid @enderror"
                                        style="width: 100%;">
                                        <option value="" {{ old('agreement') == '' ? 'selected' : '' }}>-Select
                                            Agreement-</option>
                                        <option value="PKWT1" {{ old('agreement') == 'PKWT1' ? 'selected' : '' }}>
                                            PKWT1</option>
                                        <option value="PKWT2" {{ old('agreement') == 'PKWT2' ? 'selected' : '' }}>
                                            PKWT2</option>
                                        <option value="PKWT3" {{ old('agreement') == 'PKWT3' ? 'selected' : '' }}>
                                            PKWT3</option>
                                        <option value="PKWT4" {{ old('agreement') == 'PKWT4' ? 'selected' : '' }}>
                                            PKWT4</option>
                                        <option value="PKWTT" {{ old('agreement') == 'PKWTT' ? 'selected' : '' }}>
                                            PKWTT</option>
                                        <option value="Daily" {{ old('agreement') == 'Daily' ? 'selected' : '' }}>
                                            Daily</option>
                                    </select>
                                    @if ($errors->any('agreement'))
                                        <span class="text-danger">{{ $errors->first('agreement') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-4 mb-3 text-muted">Position Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="position_id" class="form-label required-field">Position</label>
                                    <select id="position_id" name="position_id"
                                        class="form-control select2bs4 @error('position_id') is-invalid @enderror"
                                        style="width: 100%;">
                                        <option value="">-Select Position-</option>
                                        @foreach ($positions as $position)
                                            <option value="{{ $position->id }}"
                                                {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                                {{ $position->position_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->any('position_id'))
                                        <span class="text-danger">{{ $errors->first('position_id') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department" class="form-label">Department</label>
                                    <input type="text" value="{{ old('department') }}"
                                        class="form-control @error('department') is-invalid @enderror" id="department"
                                        name="department" readonly>
                                    @if ($errors->any('department'))
                                        <span class="text-danger">{{ $errors->first('department') }}</span>
                                    @endif
                                    <small class="form-text text-muted">Department will be automatically filled based
                                        on
                                        position selection</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project_id" class="form-label required-field">Project</label>
                                    <select name="project_id"
                                        class="form-control select2bs4 @error('project_id') is-invalid @enderror"
                                        style="width: 100%;">
                                        <option value="">-Select Project-</option>
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}"
                                                {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                                {{ $project->project_code }} - {{ $project->project_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->any('project_id'))
                                        <span class="text-danger">{{ $errors->first('project_id') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company_program" class="form-label">Company Program</label>
                                    <input type="text" value="{{ old('company_program') }}"
                                        class="form-control @error('company_program') is-invalid @enderror"
                                        id="company_program" name="company_program"
                                        placeholder="Enter company program">
                                    @if ($errors->any('company_program'))
                                        <span class="text-danger">{{ $errors->first('company_program') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-4 mb-3 text-muted">Certificates & References</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_fptk" class="form-label">FPTK Number</label>
                                    <input type="text" value="{{ old('no_fptk') }}"
                                        class="form-control @error('no_fptk') is-invalid @enderror" id="no_fptk"
                                        name="no_fptk" placeholder="Enter FPTK number">
                                    @if ($errors->any('no_fptk'))
                                        <span class="text-danger">{{ $errors->first('no_fptk') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_sk_active" class="form-label">Certificate Number</label>
                                    <input type="text" value="{{ old('no_sk_active') }}"
                                        class="form-control @error('no_sk_active') is-invalid @enderror"
                                        id="no_sk_active" name="no_sk_active" placeholder="Enter certificate number">
                                    @if ($errors->any('no_sk_active'))
                                        <span class="text-danger">{{ $errors->first('no_sk_active') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-4 mb-3 text-muted">Compensation</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="basic_salary" class="form-label">Basic Salary</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" value="{{ old('basic_salary') }}"
                                            class="form-control @error('basic_salary') is-invalid @enderror"
                                            id="basic_salary" name="basic_salary" placeholder="0">
                                    </div>
                                    @if ($errors->any('basic_salary'))
                                        <span class="text-danger">{{ $errors->first('basic_salary') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="site_allowance" class="form-label">Site Allowance</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" value="{{ old('site_allowance') }}"
                                            class="form-control @error('site_allowance') is-invalid @enderror"
                                            id="site_allowance" name="site_allowance" placeholder="0">
                                    </div>
                                    @if ($errors->any('site_allowance'))
                                        <span class="text-danger">{{ $errors->first('site_allowance') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="other_allowance" class="form-label">Other Allowance</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" value="{{ old('other_allowance') }}"
                                            class="form-control @error('other_allowance') is-invalid @enderror"
                                            id="other_allowance" name="other_allowance" placeholder="0">
                                    </div>
                                    @if ($errors->any('other_allowance'))
                                        <span class="text-danger">{{ $errors->first('other_allowance') }}</span>
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
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Employment Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ url('administrations/' . $administration->id) }}" method="POST">
                    <input type="hidden" name="employee_id"
                        value="{{ old('employee_id', $administration->employee_id) }}">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="card-body">
                            <h5 class="mb-3 border-bottom pb-2">Employment Details</h5>
                            <input type="hidden" value="{{ old('is_active', $administration->is_active) }}"
                                class="form-control" id="is_active" name="is_active">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nik" class="form-label required-field">Employee ID
                                            (NIK)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                            </div>
                                            <input type="text" value="{{ old('nik', $administration->nik) }}"
                                                class="form-control @error('nik') is-invalid @enderror"
                                                id="nik" name="nik" placeholder="Enter employee ID">
                                        </div>
                                        @if ($errors->any('nik'))
                                            <span class="text-danger">{{ $errors->first('nik') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="class" class="form-label required-field">Employee Class</label>
                                        <div class="d-flex mt-2">
                                            <div class="custom-control custom-radio custom-control-primary mr-4">
                                                <input class="custom-control-input" type="radio" id="class1"
                                                    name="class" value="Staff"
                                                    {{ old('class', $administration->class) == 'Staff' ? 'checked' : '' }}>
                                                <label for="class1" class="custom-control-label">Staff</label>
                                            </div>
                                            <div class="custom-control custom-radio custom-control-primary">
                                                <input class="custom-control-input" type="radio" id="class2"
                                                    name="class" value="Non Staff"
                                                    {{ old('class', $administration->class) == 'Non Staff' ? 'checked' : '' }}>
                                                <label for="class2" class="custom-control-label">Non Staff</label>
                                            </div>
                                        </div>
                                        @if ($errors->any('class'))
                                            <span class="text-danger">{{ $errors->first('class') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-4 mb-3 text-muted">Hiring Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="doh" class="form-label required-field">Date of Hire</label>
                                        <div class="input-group date">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i
                                                        class="fas fa-calendar-alt"></i></span>
                                            </div>
                                            <input type="date" value="{{ old('doh', $administration->doh) }}"
                                                class="form-control @error('doh') is-invalid @enderror"
                                                id="doh" name="doh">
                                        </div>
                                        @if ($errors->any('doh'))
                                            <span class="text-danger">{{ $errors->first('doh') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="poh" class="form-label required-field">Place of Hire</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i
                                                        class="fas fa-map-marker-alt"></i></span>
                                            </div>
                                            <input type="text" value="{{ old('poh', $administration->poh) }}"
                                                class="form-control @error('poh') is-invalid @enderror"
                                                id="poh" name="poh" placeholder="Enter place of hire">
                                        </div>
                                        @if ($errors->any('poh'))
                                            <span class="text-danger">{{ $errors->first('poh') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="foc" class="form-label">First of Contract</label>
                                        <div class="input-group date">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i
                                                        class="fas fa-calendar-alt"></i></span>
                                            </div>
                                            <input type="date" value="{{ old('foc', $administration->foc) }}"
                                                class="form-control @error('foc') is-invalid @enderror"
                                                id="foc" name="foc">
                                        </div>
                                        @if ($errors->any('foc'))
                                            <span class="text-danger">{{ $errors->first('foc') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="agreement" class="form-label">Agreement Type</label>
                                        <select name="agreement"
                                            class="form-control select2bs4 @error('agreement') is-invalid @enderror"
                                            style="width: 100%;">
                                            <option value=""
                                                {{ old('agreement', $administration->agreement) == '' ? 'selected' : '' }}>
                                                -Select Agreement-</option>
                                            <option value="PKWT1"
                                                {{ old('agreement', $administration->agreement) == 'PKWT1' ? 'selected' : '' }}>
                                                PKWT1</option>
                                            <option value="PKWT2"
                                                {{ old('agreement', $administration->agreement) == 'PKWT2' ? 'selected' : '' }}>
                                                PKWT2</option>
                                            <option value="PKWT3"
                                                {{ old('agreement', $administration->agreement) == 'PKWT3' ? 'selected' : '' }}>
                                                PKWT3</option>
                                            <option value="PKWT4"
                                                {{ old('agreement', $administration->agreement) == 'PKWT4' ? 'selected' : '' }}>
                                                PKWT4</option>
                                            <option value="PKWTT"
                                                {{ old('agreement', $administration->agreement) == 'PKWTT' ? 'selected' : '' }}>
                                                PKWTT</option>
                                            <option value="Daily"
                                                {{ old('agreement', $administration->agreement) == 'Daily' ? 'selected' : '' }}>
                                                Daily</option>
                                        </select>
                                        @if ($errors->any('agreement'))
                                            <span class="text-danger">{{ $errors->first('agreement') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-4 mb-3 text-muted">Position Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="position_id" class="form-label required-field">Position</label>
                                        <select id="position_id" name="position_id"
                                            class="form-control select2bs4 @error('position_id') is-invalid @enderror"
                                            style="width: 100%;">
                                            <option value="">-Select Position-</option>
                                            @foreach ($positions as $position)
                                                <option value="{{ $position->id }}"
                                                    {{ old('position_id', $administration->position_id) == $position->id ? 'selected' : '' }}>
                                                    {{ $position->position_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($errors->any('position_id'))
                                            <span class="text-danger">{{ $errors->first('position_id') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="department" class="form-label">Department</label>
                                        <input type="text" value="{{ old('department') }}"
                                            class="form-control @error('department') is-invalid @enderror department{{ $administration->id }}"
                                            name="department" readonly>
                                        @if ($errors->any('department'))
                                            <span class="text-danger">{{ $errors->first('department') }}</span>
                                        @endif
                                        <small class="form-text text-muted">Department will be automatically filled
                                            based on
                                            position selection</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="project_id" class="form-label required-field">Project</label>
                                        <select name="project_id"
                                            class="form-control select2bs4 @error('project_id') is-invalid @enderror"
                                            style="width: 100%;">
                                            <option value="">-Select Project-</option>
                                            @foreach ($projects as $project)
                                                <option value="{{ $project->id }}"
                                                    {{ old('project_id', $administration->project_id) == $project->id ? 'selected' : '' }}>
                                                    {{ $project->project_code }} - {{ $project->project_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($errors->any('project_id'))
                                            <span class="text-danger">{{ $errors->first('project_id') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="company_program" class="form-label">Company Program</label>
                                        <input type="text"
                                            value="{{ old('company_program', $administration->company_program) }}"
                                            class="form-control @error('company_program') is-invalid @enderror"
                                            id="company_program" name="company_program"
                                            placeholder="Enter company program">
                                        @if ($errors->any('company_program'))
                                            <span class="text-danger">{{ $errors->first('company_program') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-4 mb-3 text-muted">Certificates & References</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="no_fptk" class="form-label">FPTK Number</label>
                                        <input type="text" value="{{ old('no_fptk', $administration->no_fptk) }}"
                                            class="form-control @error('no_fptk') is-invalid @enderror"
                                            id="no_fptk" name="no_fptk" placeholder="Enter FPTK number">
                                        @if ($errors->any('no_fptk'))
                                            <span class="text-danger">{{ $errors->first('no_fptk') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="no_sk_active" class="form-label">Certificate Number</label>
                                        <input type="text"
                                            value="{{ old('no_sk_active', $administration->no_sk_active) }}"
                                            class="form-control @error('no_sk_active') is-invalid @enderror"
                                            id="no_sk_active" name="no_sk_active"
                                            placeholder="Enter certificate number">
                                        @if ($errors->any('no_sk_active'))
                                            <span class="text-danger">{{ $errors->first('no_sk_active') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-4 mb-3 text-muted">Compensation</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="basic_salary" class="form-label">Basic Salary</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="number"
                                                value="{{ old('basic_salary', $administration->basic_salary) }}"
                                                class="form-control @error('basic_salary') is-invalid @enderror"
                                                id="basic_salary" name="basic_salary" placeholder="0">
                                        </div>
                                        @if ($errors->any('basic_salary'))
                                            <span class="text-danger">{{ $errors->first('basic_salary') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="site_allowance" class="form-label">Site Allowance</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="number"
                                                value="{{ old('site_allowance', $administration->site_allowance) }}"
                                                class="form-control @error('site_allowance') is-invalid @enderror"
                                                id="site_allowance" name="site_allowance" placeholder="0">
                                        </div>
                                        @if ($errors->any('site_allowance'))
                                            <span class="text-danger">{{ $errors->first('site_allowance') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="other_allowance" class="form-label">Other Allowance</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="number"
                                                value="{{ old('other_allowance', $administration->other_allowance) }}"
                                                class="form-control @error('other_allowance') is-invalid @enderror"
                                                id="other_allowance" name="other_allowance" placeholder="0">
                                        </div>
                                        @if ($errors->any('other_allowance'))
                                            <span class="text-danger">{{ $errors->first('other_allowance') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <h6 class="mt-4 mb-3 text-muted">Termination Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="termination_date" class="form-label">Termination Date</label>
                                        <div class="input-group date">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i
                                                        class="fas fa-calendar-alt"></i></span>
                                            </div>
                                            <input type="date"
                                                value="{{ old('termination_date', $administration->termination_date) }}"
                                                class="form-control @error('termination_date') is-invalid @enderror"
                                                id="termination_date" name="termination_date">
                                        </div>
                                        @if ($errors->any('termination_date'))
                                            <span class="text-danger">{{ $errors->first('termination_date') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="termination_reason" class="form-label">Termination Reason</label>
                                        <select name="termination_reason"
                                            class="form-control select2bs4 @error('termination_reason') is-invalid @enderror"
                                            style="width: 100%;">
                                            <option value="">-Select Reason-</option>
                                            <option value="End of Contract"
                                                {{ old('termination_reason', $administration->termination_reason) == 'End of Contract' ? 'selected' : '' }}>
                                                End of Contract</option>
                                            <option value="End of Project"
                                                {{ old('termination_reason', $administration->termination_reason) == 'End of Project' ? 'selected' : '' }}>
                                                End of Project</option>
                                            <option value="Resign"
                                                {{ old('termination_reason', $administration->termination_reason) == 'Resign' ? 'selected' : '' }}>
                                                Resign</option>
                                            <option value="Termination"
                                                {{ old('termination_reason', $administration->termination_reason) == 'Termination' ? 'selected' : '' }}>
                                                Termination</option>
                                        </select>
                                        @if ($errors->any('termination_reason'))
                                            <span
                                                class="text-danger">{{ $errors->first('termination_reason') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="coe_no" class="form-label">Certificate of Employment</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                                            </div>
                                            <input type="text"
                                                value="{{ old('coe_no', $administration->coe_no) }}"
                                                class="form-control @error('coe_no') is-invalid @enderror"
                                                id="coe_no" name="coe_no" placeholder="Enter certificate number">
                                        </div>
                                        @if ($errors->any('coe_no'))
                                            <span class="text-danger">{{ $errors->first('coe_no') }}</span>
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
