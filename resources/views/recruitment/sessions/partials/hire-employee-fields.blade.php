@php
    $suffix = $suffix ?? '';
    $showRegisterCheckbox = $showRegisterCheckbox ?? false;
    $registrationMode = old('registration_mode', 'new');
    $employmentType = optional($session->fptk)->employment_type;
    if (!$employmentType && $session->mpp_detail_id && $session->mppDetail) {
        $employmentType = $session->mppDetail->agreement_type ?? 'pkwt';
    }
    $employmentType = $employmentType ?: 'pkwt';
@endphp

@if ($showRegisterCheckbox)
    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input hire-register-employee-toggle" id="register_to_employee"
                name="register_to_employee" value="1" {{ old('register_to_employee') ? 'checked' : '' }}>
            <label class="custom-control-label" for="register_to_employee">
                Register to Employee Management
            </label>
        </div>
        <small class="form-text text-muted">Optional. When enabled, register a new employee or link an existing employee
            from Employee Management.</small>
    </div>
@endif
<div id="hire_employee_sections{{ $suffix }}"
    class="hire-employee-sections {{ $showRegisterCheckbox && !old('register_to_employee') ? 'd-none' : '' }}">
    <div class="form-group mb-3">
        <label class="d-block font-weight-bold">Registration Type</label>
        <div class="custom-control custom-radio custom-control-inline">
            <input type="radio" class="custom-control-input registration-mode-radio"
                id="registration_mode_new{{ $suffix }}" name="registration_mode" value="new"
                data-suffix="{{ $suffix }}" {{ $registrationMode === 'new' ? 'checked' : '' }}>
            <label class="custom-control-label" for="registration_mode_new{{ $suffix }}">New Employee</label>
        </div>
        <div class="custom-control custom-radio custom-control-inline">
            <input type="radio" class="custom-control-input registration-mode-radio"
                id="registration_mode_existing{{ $suffix }}" name="registration_mode" value="existing"
                data-suffix="{{ $suffix }}" {{ $registrationMode === 'existing' ? 'checked' : '' }}>
            <label class="custom-control-label" for="registration_mode_existing{{ $suffix }}">Existing
                Employee</label>
        </div>
        <small class="form-text text-muted">New: create employee with personal and administration data. Existing: link
            an employee already in the system — their current administration data is used as-is.</small>
        @error('registration_mode')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
        @error('employee_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div id="existing_employee_panel{{ $suffix }}"
        class="existing-employee-panel {{ $registrationMode === 'existing' ? '' : 'd-none' }}">
        <div class="card mb-3">
            <div class="card-header"><strong>Existing Employee</strong></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Search Employee <span class="text-danger">*</span></label>
                    <select id="existing_employee_id{{ $suffix }}" name="employee_id"
                        class="form-control existing-employee-select" data-suffix="{{ $suffix }}"
                        data-required-when-existing {{ $registrationMode === 'existing' ? 'required' : '' }}
                        {{ $registrationMode === 'existing' ? '' : 'disabled' }}>
                        @if (old('employee_id'))
                            <option value="{{ old('employee_id') }}" selected>{{ old('employee_id') }}</option>
                        @endif
                    </select>
                    <small class="form-text text-muted">Search by fullname, NIK, or identity card number. Only employees with an active administration are shown.</small>
                </div>
                <div id="existing_employee_summary{{ $suffix }}"
                    class="existing-employee-summary alert alert-light border {{ old('employee_id') ? '' : 'd-none' }}">
                    <h6 class="font-weight-bold mb-3">Employee &amp; Administration (from Employee Management)</h6>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Fullname</small>
                            <strong id="existing_summary_fullname{{ $suffix }}">-</strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Identity Card</small>
                            <strong id="existing_summary_identity{{ $suffix }}">-</strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">NIK</small>
                            <strong id="existing_summary_nik{{ $suffix }}">-</strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Position</small>
                            <strong id="existing_summary_position{{ $suffix }}">-</strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Department</small>
                            <strong id="existing_summary_department{{ $suffix }}">-</strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Project</small>
                            <strong id="existing_summary_project{{ $suffix }}">-</strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Date of Hire</small>
                            <strong id="existing_summary_doh{{ $suffix }}">-</strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Place of Hire</small>
                            <strong id="existing_summary_poh{{ $suffix }}">-</strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Class</small>
                            <strong id="existing_summary_class{{ $suffix }}">-</strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Level</small>
                            <strong id="existing_summary_level{{ $suffix }}">-</strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Grade</small>
                            <strong id="existing_summary_grade{{ $suffix }}">-</strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Agreement</small>
                            <strong id="existing_summary_agreement{{ $suffix }}">-</strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">FOC</small>
                            <strong id="existing_summary_foc{{ $suffix }}">-</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="new_employee_panel{{ $suffix }}"
        class="new-employee-panel {{ $registrationMode === 'existing' ? 'd-none' : '' }}">
        <div class="card mb-3">
            <div class="card-header"><strong>Personal Data</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Fullname <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control @error('employee.fullname') is-invalid @enderror"
                                name="employee[fullname]"
                                value="{{ old('employee.fullname', $session->candidate->fullname) }}"
                                data-required-when-register required>
                            @error('employee.fullname')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Identity Card No <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control @error('employee.identity_card') is-invalid @enderror"
                                name="employee[identity_card]" value="{{ old('employee.identity_card') }}"
                                placeholder="Enter KTP/ID number" data-required-when-register required>
                            @error('employee.identity_card')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Place of Birth <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('employee.emp_pob') is-invalid @enderror"
                                name="employee[emp_pob]" value="{{ old('employee.emp_pob') }}"
                                placeholder="Enter birthplace" data-required-when-register required>
                            @error('employee.emp_pob')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('employee.emp_dob') is-invalid @enderror"
                                name="employee[emp_dob]"
                                value="{{ old('employee.emp_dob', optional($session->candidate->date_of_birth)->format('Y-m-d')) }}"
                                data-required-when-register required>
                            @error('employee.emp_dob')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Religion <span class="text-danger">*</span></label>
                            <select class="form-control @error('employee.religion_id') is-invalid @enderror"
                                name="employee[religion_id]" data-required-when-register required>
                                <option value="">Select religion</option>
                                @foreach (\App\Models\Religion::get() as $religion)
                                    <option value="{{ $religion->id }}"
                                        {{ old('employee.religion_id') == $religion->id ? 'selected' : '' }}>
                                        {{ $religion->religion_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee.religion_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Gender</label>
                            <select class="form-control @error('employee.gender') is-invalid @enderror"
                                name="employee[gender]">
                                <option value="">Select gender</option>
                                <option value="male" {{ old('employee.gender') == 'male' ? 'selected' : '' }}>Male
                                </option>
                                <option value="female" {{ old('employee.gender') == 'female' ? 'selected' : '' }}>
                                    Female
                                </option>
                            </select>
                            @error('employee.gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Marital Status</label>
                            <input type="text" class="form-control @error('employee.marital') is-invalid @enderror"
                                name="employee[marital]" value="{{ old('employee.marital') }}"
                                placeholder="Single/Married/etc">
                            @error('employee.marital')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" class="form-control @error('employee.phone') is-invalid @enderror"
                                name="employee[phone]"
                                value="{{ old('employee.phone', $session->candidate->phone) }}">
                            @error('employee.phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" class="form-control @error('employee.address') is-invalid @enderror"
                                name="employee[address]"
                                value="{{ old('employee.address', $session->candidate->address) }}">
                            @error('employee.address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control @error('employee.email') is-invalid @enderror"
                                name="employee[email]"
                                value="{{ old('employee.email', $session->candidate->email) }}">
                            @error('employee.email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="administration_panel{{ $suffix }}"
        class="administration-panel {{ $registrationMode === 'existing' ? 'd-none' : '' }}">
        <div class="card">
            <div class="card-header"><strong>Administration Data</strong></div>
            <div class="card-body">
                @if (in_array(optional($session->fptk)->employment_type, ['magang', 'harian']))
                    {{-- Administration Data for Magang and Harian --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ optional($session->fptk)->employment_type === 'magang' ? 'NIM' : 'NID' }}
                                    <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control @error('administration.nik') is-invalid @enderror"
                                    name="administration[nik]" value="{{ old('administration.nik') }}"
                                    placeholder="Enter {{ optional($session->fptk)->employment_type === 'magang' ? 'NIM' : 'NID' }}"
                                    data-required-when-register required>
                                @error('administration.nik')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date of Hire <span class="text-danger">*</span></label>
                                <input type="date"
                                    class="form-control @error('administration.doh') is-invalid @enderror"
                                    name="administration[doh]"
                                    value="{{ old('administration.doh', now()->format('Y-m-d')) }}"
                                    data-required-when-register required>
                                @error('administration.doh')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Place of Hire (POH) <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control @error('administration.poh') is-invalid @enderror"
                                    name="administration[poh]" value="{{ old('administration.poh') }}"
                                    placeholder="Enter POH" data-required-when-register required>
                                @error('administration.poh')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Class <span class="text-danger">*</span></label>
                                <select class="form-control @error('administration.class') is-invalid @enderror"
                                    name="administration[class]" data-required-when-register required>
                                    <option value="">Select class</option>
                                    <option value="Staff"
                                        {{ old('administration.class') == 'Staff' ? 'selected' : '' }}>
                                        Staff
                                    </option>
                                    <option value="Non Staff"
                                        {{ old('administration.class') == 'Non Staff' ? 'selected' : '' }}>
                                        Non
                                        Staff</option>
                                </select>
                                @error('administration.class')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Position <span class="text-danger">*</span></label>
                                <select class="form-control @error('administration.position_id') is-invalid @enderror"
                                    name="administration[position_id]"
                                    id="hire_position_id_magang_harian{{ $suffix }}" data-required-when-register
                                    required>
                                    <option value="">Select position</option>
                                    @foreach (\App\Models\Position::orderBy('position_name', 'asc')->get() as $position)
                                        <option value="{{ $position->id }}"
                                            {{ old('administration.position_id', optional($session->fptk)->position_id) == $position->id ? 'selected' : '' }}>
                                            {{ $position->position_name }}</option>
                                    @endforeach
                                </select>
                                @error('administration.position_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Department</label>
                                <input type="text" class="form-control"
                                    id="hire_department_magang_harian{{ $suffix }}" readonly>
                                <small class="form-text text-muted">Department will be automatically filled
                                    based on position selection</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Project <span class="text-danger">*</span></label>
                                <select class="form-control @error('administration.project_id') is-invalid @enderror"
                                    name="administration[project_id]" data-required-when-register required>
                                    <option value="">Select project</option>
                                    @foreach (\App\Models\Project::orderBy('project_code', 'asc')->get() as $project)
                                        <option value="{{ $project->id }}"
                                            {{ old('administration.project_id', optional($session->fptk)->project_id) == $project->id ? 'selected' : '' }}>
                                            {{ $project->project_code }}</option>
                                    @endforeach
                                </select>
                                @error('administration.project_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>FPTK No</label>
                                <input type="text" class="form-control" name="administration[no_fptk]"
                                    value="{{ optional($session->fptk)->request_number }}" readonly>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Administration Data for PKWT and PKWTT --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NIK <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control @error('administration.nik') is-invalid @enderror"
                                    name="administration[nik]" value="{{ old('administration.nik') }}"
                                    placeholder="Enter employee NIK" data-required-when-register required>
                                @error('administration.nik')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date of Hire <span class="text-danger">*</span></label>
                                <input type="date"
                                    class="form-control @error('administration.doh') is-invalid @enderror"
                                    name="administration[doh]"
                                    value="{{ old('administration.doh', now()->format('Y-m-d')) }}"
                                    data-required-when-register required>
                                @error('administration.doh')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Place of Hire (POH) <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control @error('administration.poh') is-invalid @enderror"
                                    name="administration[poh]" value="{{ old('administration.poh') }}"
                                    placeholder="Enter POH" data-required-when-register required>
                                @error('administration.poh')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Class <span class="text-danger">*</span></label>
                                <select class="form-control @error('administration.class') is-invalid @enderror"
                                    name="administration[class]" data-required-when-register required>
                                    <option value="">Select class</option>
                                    <option value="Staff"
                                        {{ old('administration.class') == 'Staff' ? 'selected' : '' }}>
                                        Staff
                                    </option>
                                    <option value="Non Staff"
                                        {{ old('administration.class') == 'Non Staff' ? 'selected' : '' }}>
                                        Non
                                        Staff</option>
                                </select>
                                @error('administration.class')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Position <span class="text-danger">*</span></label>
                                <select class="form-control @error('administration.position_id') is-invalid @enderror"
                                    name="administration[position_id]" id="hire_position_id{{ $suffix }}"
                                    data-required-when-register required>
                                    <option value="">Select position</option>
                                    @foreach (\App\Models\Position::orderBy('position_name', 'asc')->get() as $position)
                                        <option value="{{ $position->id }}"
                                            {{ old('administration.position_id', optional($session->fptk)->position_id) == $position->id ? 'selected' : '' }}>
                                            {{ $position->position_name }}</option>
                                    @endforeach
                                </select>
                                @error('administration.position_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Department</label>
                                <input type="text" class="form-control" id="hire_department{{ $suffix }}"
                                    readonly>
                                <small class="form-text text-muted">Department will be automatically filled
                                    based on position selection</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Project <span class="text-danger">*</span></label>
                                <select class="form-control @error('administration.project_id') is-invalid @enderror"
                                    name="administration[project_id]" data-required-when-register required>
                                    <option value="">Select project</option>
                                    @foreach (\App\Models\Project::orderBy('project_code', 'asc')->get() as $project)
                                        <option value="{{ $project->id }}"
                                            {{ old('administration.project_id', optional($session->fptk)->project_id) == $project->id ? 'selected' : '' }}>
                                            {{ $project->project_code }}</option>
                                    @endforeach
                                </select>
                                @error('administration.project_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Grade</label>
                                <select class="form-control @error('administration.grade_id') is-invalid @enderror"
                                    name="administration[grade_id]">
                                    <option value="">Select grade</option>
                                    @foreach (\App\Models\Grade::where('is_active', 1)->orderBy('name', 'asc')->get() as $grade)
                                        <option value="{{ $grade->id }}"
                                            {{ old('administration.grade_id') == $grade->id ? 'selected' : '' }}>
                                            {{ $grade->name }}</option>
                                    @endforeach
                                </select>
                                @error('administration.grade_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Level <span class="text-danger">*</span></label>
                                <select class="form-control @error('administration.level_id') is-invalid @enderror"
                                    name="administration[level_id]" data-required-when-register required>
                                    <option value="">Select level</option>
                                    @foreach (\App\Models\Level::where('is_active', 1)->orderBy('name', 'asc')->get() as $level)
                                        <option value="{{ $level->id }}"
                                            {{ old('administration.level_id') == $level->id ? 'selected' : '' }}>
                                            {{ $level->name }}</option>
                                    @endforeach
                                </select>
                                @error('administration.level_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>FPTK No</label>
                                <input type="text" class="form-control" name="administration[no_fptk]"
                                    value="{{ optional($session->fptk)->request_number }}" readonly>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
