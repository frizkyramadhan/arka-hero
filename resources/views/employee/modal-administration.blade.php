@push('styles')
    <!-- Remove Select2 styles -->
@endpush

<!-- Add Administration Modal -->
<div class="modal fade text-left" id="modal-administration">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Employee - Add Administration Data</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('administrations/' . $employee->id) }}" method="POST">
                @csrf
                <input type="hidden" name="employee_id" value="{{ old('employee_id', $employee->id) }}">
                <input type="hidden" name="is_active" value="1">

                <div class="modal-body">
                    <div class="card-body">
                        <!-- Employment Details Section -->
                        <h5 class="mb-3 border-bottom pb-2">Employment Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nik_add" class="form-label required-field">Employee ID (NIK)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                        </div>
                                        <input type="text" class="form-control @error('nik') is-invalid @enderror"
                                            id="nik_add" name="nik" value="{{ old('nik') }}"
                                            placeholder="Enter employee ID">
                                    </div>
                                    @error('nik')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="class_add" class="form-label required-field">Employee Class</label>
                                    <div class="d-flex mt-2">
                                        <div class="custom-control custom-radio custom-control-primary mr-4">
                                            <input class="custom-control-input" type="radio" id="class1_add"
                                                name="class" value="Staff"
                                                {{ old('class') == 'Staff' ? 'checked' : '' }}>
                                            <label for="class1_add" class="custom-control-label">Staff</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-primary">
                                            <input class="custom-control-input" type="radio" id="class2_add"
                                                name="class" value="Non Staff"
                                                {{ old('class') == 'Non Staff' ? 'checked' : '' }}>
                                            <label for="class2_add" class="custom-control-label">Non Staff</label>
                                        </div>
                                    </div>
                                    @error('class')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Hiring Information Section -->
                        <h5 class="mt-2 mb-3">Hiring Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="doh_add" class="form-label required-field">Date of Hire</label>
                                    <div class="input-group date">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="date" class="form-control @error('doh') is-invalid @enderror"
                                            id="doh_add" name="doh" value="{{ old('doh') }}">
                                    </div>
                                    @error('doh')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="poh_add" class="form-label required-field">Place of Hire</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        </div>
                                        <input type="text" class="form-control @error('poh') is-invalid @enderror"
                                            id="poh_add" name="poh" value="{{ old('poh') }}"
                                            placeholder="Enter place of hire">
                                    </div>
                                    @error('poh')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Contract Information Section -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="foc_add" class="form-label">FOC Date</label>
                                    <div class="input-group date">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="date" class="form-control @error('foc') is-invalid @enderror"
                                            id="foc_add" name="foc" value="{{ old('foc') }}">
                                    </div>
                                    @error('foc')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="agreement_add" class="form-label">Agreement Type</label>
                                    <select name="agreement" id="agreement_add"
                                        class="form-control @error('agreement') is-invalid @enderror">
                                        <option value="">-Select Agreement-</option>
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
                                    @error('agreement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Position Information Section -->
                        <h5 class="mt-2 mb-3">Position Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="position_id_add" class="form-label required-field">Position</label>
                                    <select name="position_id" id="position_id_add"
                                        class="form-control @error('position_id') is-invalid @enderror">
                                        <option value="">-Select Position-</option>
                                        @foreach ($positions as $position)
                                            <option value="{{ $position->id }}"
                                                {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                                {{ $position->position_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('position_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department_add" class="form-label">Department</label>
                                    <input type="text"
                                        class="form-control @error('department') is-invalid @enderror"
                                        id="department_add" name="department" readonly>
                                    @error('department')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Department will be automatically filled based
                                        on position selection</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="grade_id_add" class="form-label">Grade</label>
                                    <select name="grade_id" id="grade_id_add"
                                        class="form-control @error('grade_id') is-invalid @enderror">
                                        <option value="">-Select Grade-</option>
                                        @foreach ($grades as $grade)
                                            <option value="{{ $grade->id }}"
                                                {{ old('grade_id') == $grade->id ? 'selected' : '' }}>
                                                {{ $grade->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('grade_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="level_id_add" class="form-label">Level</label>
                                    <select name="level_id" id="level_id_add"
                                        class="form-control @error('level_id') is-invalid @enderror">
                                        <option value="">-Select Level-</option>
                                        @foreach ($levels as $level)
                                            <option value="{{ $level->id }}"
                                                {{ old('level_id') == $level->id ? 'selected' : '' }}>
                                                {{ $level->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('level_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Project Information Section -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project_id_add" class="form-label required-field">Project</label>
                                    <select name="project_id" id="project_id_add"
                                        class="form-control @error('project_id') is-invalid @enderror">
                                        <option value="">-Select Project-</option>
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}"
                                                {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                                {{ $project->project_code }} - {{ $project->project_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('project_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company_program_add" class="form-label">Company Program</label>
                                    <input type="text"
                                        class="form-control @error('company_program') is-invalid @enderror"
                                        id="company_program_add" name="company_program"
                                        value="{{ old('company_program') }}" placeholder="Enter company program">
                                    @error('company_program')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Certificates & References Section -->
                        <h5 class="mt-2 mb-3">Certificates & References</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_fptk_add" class="form-label">FPTK Number</label>
                                    <input type="text" class="form-control @error('no_fptk') is-invalid @enderror"
                                        id="no_fptk_add" name="no_fptk" value="{{ old('no_fptk') }}"
                                        placeholder="Enter FPTK number">
                                    @error('no_fptk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_sk_active_add" class="form-label">Certificate Number</label>
                                    <input type="text"
                                        class="form-control @error('no_sk_active') is-invalid @enderror"
                                        id="no_sk_active_add" name="no_sk_active" value="{{ old('no_sk_active') }}"
                                        placeholder="Enter certificate number">
                                    @error('no_sk_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Compensation Section -->
                        <h5 class="mt-2 mb-3">Compensation</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="basic_salary_add" class="form-label">Basic Salary</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number"
                                            class="form-control @error('basic_salary') is-invalid @enderror"
                                            id="basic_salary_add" name="basic_salary"
                                            value="{{ old('basic_salary') }}" placeholder="0">
                                    </div>
                                    @error('basic_salary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="site_allowance_add" class="form-label">Site Allowance</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number"
                                            class="form-control @error('site_allowance') is-invalid @enderror"
                                            id="site_allowance_add" name="site_allowance"
                                            value="{{ old('site_allowance') }}" placeholder="0">
                                    </div>
                                    @error('site_allowance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="other_allowance_add" class="form-label">Other Allowance</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number"
                                            class="form-control @error('other_allowance') is-invalid @enderror"
                                            id="other_allowance_add" name="other_allowance"
                                            value="{{ old('other_allowance') }}" placeholder="0">
                                    </div>
                                    @error('other_allowance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Termination Section -->
                        <div class="row bg-danger mt-4">
                            <div class="col-md-12 text-center">
                                <label class="form-label">TERMINATION SECTION</label>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="termination_date_add" class="form-label">Termination Date</label>
                                    <div class="input-group date">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="date"
                                            class="form-control @error('termination_date') is-invalid @enderror"
                                            id="termination_date_add" name="termination_date"
                                            value="{{ old('termination_date') }}">
                                    </div>
                                    @error('termination_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="termination_reason_add" class="form-label">Termination Reason</label>
                                    <select name="termination_reason" id="termination_reason_add"
                                        class="form-control @error('termination_reason') is-invalid @enderror"
                                        style="width: 100%;">
                                        <option value="">-Select Reason-</option>
                                        <option value="End of Contract"
                                            {{ old('termination_reason') == 'End of Contract' ? 'selected' : '' }}>
                                            End of Contract</option>
                                        <option value="End of Project"
                                            {{ old('termination_reason') == 'End of Project' ? 'selected' : '' }}>
                                            End of Project</option>
                                        <option value="Resign"
                                            {{ old('termination_reason') == 'Resign' ? 'selected' : '' }}>
                                            Resign</option>
                                        <option value="Termination"
                                            {{ old('termination_reason') == 'Termination' ? 'selected' : '' }}>
                                            Termination</option>
                                        <option value="Retired"
                                            {{ old('termination_reason') == 'Retired' ? 'selected' : '' }}>
                                            Retired</option>
                                    </select>
                                    @error('termination_reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="coe_no_add" class="form-label">Certificate of Employment</label>
                                    <input type="text" class="form-control @error('coe_no') is-invalid @enderror"
                                        id="coe_no_add" name="coe_no" value="{{ old('coe_no') }}"
                                        placeholder="Enter certificate number">
                                    @error('coe_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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

<!-- Edit Administration Modals -->
@foreach ($administrations as $administration)
    <div class="modal fade text-left" id="modal-administration-{{ $administration->id }}">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Employee - Edit Administration Data</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ url('administrations/' . $administration->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="employee_id"
                        value="{{ old('employee_id', $administration->employee_id) }}">
                    <input type="hidden" name="is_active"
                        value="{{ old('is_active', $administration->is_active) }}">

                    <div class="modal-body">
                        <div class="card-body">
                            <!-- Employment Details Section -->
                            <h5 class="mb-3 border-bottom pb-2">Employment Details</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nik_edit_{{ $administration->id }}"
                                            class="form-label required-field">Employee ID (NIK)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                            </div>
                                            <input type="text"
                                                class="form-control @error('nik') is-invalid @enderror"
                                                id="nik_edit_{{ $administration->id }}" name="nik"
                                                value="{{ old('nik', $administration->nik) }}"
                                                placeholder="Enter employee ID">
                                        </div>
                                        @error('nik')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="class_edit_{{ $administration->id }}"
                                            class="form-label required-field">Employee Class</label>
                                        <div class="d-flex mt-2">
                                            <div class="custom-control custom-radio custom-control-primary mr-4">
                                                <input class="custom-control-input" type="radio"
                                                    id="class1_edit_{{ $administration->id }}" name="class"
                                                    value="Staff"
                                                    {{ old('class', $administration->class) == 'Staff' ? 'checked' : '' }}>
                                                <label for="class1_edit_{{ $administration->id }}"
                                                    class="custom-control-label">Staff</label>
                                            </div>
                                            <div class="custom-control custom-radio custom-control-primary">
                                                <input class="custom-control-input" type="radio"
                                                    id="class2_edit_{{ $administration->id }}" name="class"
                                                    value="Non Staff"
                                                    {{ old('class', $administration->class) == 'Non Staff' ? 'checked' : '' }}>
                                                <label for="class2_edit_{{ $administration->id }}"
                                                    class="custom-control-label">Non Staff</label>
                                            </div>
                                        </div>
                                        @error('class')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Hiring Information Section -->
                            <h5 class="mt-2 mb-3">Hiring Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="doh_edit_{{ $administration->id }}"
                                            class="form-label required-field">Date of Hire</label>
                                        <div class="input-group date">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i
                                                        class="fas fa-calendar-alt"></i></span>
                                            </div>
                                            <input type="date"
                                                class="form-control @error('doh') is-invalid @enderror"
                                                id="doh_edit_{{ $administration->id }}" name="doh"
                                                value="{{ old('doh', $administration->doh) }}">
                                        </div>
                                        @error('doh')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="poh_edit_{{ $administration->id }}"
                                            class="form-label required-field">Place of Hire</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i
                                                        class="fas fa-map-marker-alt"></i></span>
                                            </div>
                                            <input type="text"
                                                class="form-control @error('poh') is-invalid @enderror"
                                                id="poh_edit_{{ $administration->id }}" name="poh"
                                                value="{{ old('poh', $administration->poh) }}"
                                                placeholder="Enter place of hire">
                                        </div>
                                        @error('poh')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Contract Information Section -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="foc_edit_{{ $administration->id }}" class="form-label">FOC
                                            Date</label>
                                        <div class="input-group date">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i
                                                        class="fas fa-calendar-alt"></i></span>
                                            </div>
                                            <input type="date"
                                                class="form-control @error('foc') is-invalid @enderror"
                                                id="foc_edit_{{ $administration->id }}" name="foc"
                                                value="{{ old('foc', $administration->foc) }}">
                                        </div>
                                        @error('foc')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="agreement_edit_{{ $administration->id }}"
                                            class="form-label">Agreement Type</label>
                                        <select name="agreement" id="agreement_edit_{{ $administration->id }}"
                                            class="form-control @error('agreement') is-invalid @enderror">
                                            <option value="">-Select Agreement-</option>
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
                                        @error('agreement')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Position Information Section -->
                            <h5 class="mt-2 mb-3">Position Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="position_id_edit_{{ $administration->id }}"
                                            class="form-label required-field">Position</label>
                                        <select name="position_id" id="position_id_edit_{{ $administration->id }}"
                                            class="form-control @error('position_id') is-invalid @enderror">
                                            <option value="">-Select Position-</option>
                                            @foreach ($positions as $position)
                                                <option value="{{ $position->id }}"
                                                    {{ old('position_id', $administration->position_id) == $position->id ? 'selected' : '' }}>
                                                    {{ $position->position_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('position_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="department_edit_{{ $administration->id }}"
                                            class="form-label">Department</label>
                                        <input type="text"
                                            class="form-control @error('department') is-invalid @enderror"
                                            id="department_edit_{{ $administration->id }}" name="department"
                                            readonly>
                                        @error('department')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Department will be automatically filled
                                            based on position selection</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="grade_id_edit_{{ $administration->id }}"
                                            class="form-label">Grade</label>
                                        <select name="grade_id" id="grade_id_edit_{{ $administration->id }}"
                                            class="form-control @error('grade_id') is-invalid @enderror">
                                            <option value="">-Select Grade-</option>
                                            @foreach ($grades as $grade)
                                                <option value="{{ $grade->id }}"
                                                    {{ old('grade_id', $administration->grade_id) == $grade->id ? 'selected' : '' }}>
                                                    {{ $grade->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('grade_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="level_id_edit_{{ $administration->id }}"
                                            class="form-label">Level</label>
                                        <select name="level_id" id="level_id_edit_{{ $administration->id }}"
                                            class="form-control @error('level_id') is-invalid @enderror">
                                            <option value="">-Select Level-</option>
                                            @foreach ($levels as $level)
                                                <option value="{{ $level->id }}"
                                                    {{ old('level_id', $administration->level_id) == $level->id ? 'selected' : '' }}>
                                                    {{ $level->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('level_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Project Information Section -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="project_id_edit_{{ $administration->id }}"
                                            class="form-label required-field">Project</label>
                                        <select name="project_id" id="project_id_edit_{{ $administration->id }}"
                                            class="form-control @error('project_id') is-invalid @enderror">
                                            <option value="">-Select Project-</option>
                                            @foreach ($projects as $project)
                                                <option value="{{ $project->id }}"
                                                    {{ old('project_id', $administration->project_id) == $project->id ? 'selected' : '' }}>
                                                    {{ $project->project_code }} - {{ $project->project_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('project_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="company_program_edit_{{ $administration->id }}"
                                            class="form-label">Company Program</label>
                                        <input type="text"
                                            class="form-control @error('company_program') is-invalid @enderror"
                                            id="company_program_edit_{{ $administration->id }}"
                                            name="company_program"
                                            value="{{ old('company_program', $administration->company_program) }}"
                                            placeholder="Enter company program">
                                        @error('company_program')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Certificates & References Section -->
                            <h5 class="mt-2 mb-3">Certificates & References</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="no_fptk_edit_{{ $administration->id }}" class="form-label">FPTK
                                            Number</label>
                                        <input type="text"
                                            class="form-control @error('no_fptk') is-invalid @enderror"
                                            id="no_fptk_edit_{{ $administration->id }}" name="no_fptk"
                                            value="{{ old('no_fptk', $administration->no_fptk) }}"
                                            placeholder="Enter FPTK number">
                                        @error('no_fptk')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="no_sk_active_edit_{{ $administration->id }}"
                                            class="form-label">Certificate Number</label>
                                        <input type="text"
                                            class="form-control @error('no_sk_active') is-invalid @enderror"
                                            id="no_sk_active_edit_{{ $administration->id }}" name="no_sk_active"
                                            value="{{ old('no_sk_active', $administration->no_sk_active) }}"
                                            placeholder="Enter certificate number">
                                        @error('no_sk_active')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Compensation Section -->
                            <h5 class="mt-2 mb-3">Compensation</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="basic_salary_edit_{{ $administration->id }}"
                                            class="form-label">Basic Salary</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="number"
                                                class="form-control @error('basic_salary') is-invalid @enderror"
                                                id="basic_salary_edit_{{ $administration->id }}" name="basic_salary"
                                                value="{{ old('basic_salary', $administration->basic_salary) }}"
                                                placeholder="0">
                                        </div>
                                        @error('basic_salary')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="site_allowance_edit_{{ $administration->id }}"
                                            class="form-label">Site Allowance</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="number"
                                                class="form-control @error('site_allowance') is-invalid @enderror"
                                                id="site_allowance_edit_{{ $administration->id }}"
                                                name="site_allowance"
                                                value="{{ old('site_allowance', $administration->site_allowance) }}"
                                                placeholder="0">
                                        </div>
                                        @error('site_allowance')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="other_allowance_edit_{{ $administration->id }}"
                                            class="form-label">Other Allowance</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="number"
                                                class="form-control @error('other_allowance') is-invalid @enderror"
                                                id="other_allowance_edit_{{ $administration->id }}"
                                                name="other_allowance"
                                                value="{{ old('other_allowance', $administration->other_allowance) }}"
                                                placeholder="0">
                                        </div>
                                        @error('other_allowance')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Termination Section -->
                            <div class="row bg-danger mt-4">
                                <div class="col-md-12 text-center">
                                    <label class="form-label">TERMINATION SECTION</label>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="termination_date_edit_{{ $administration->id }}"
                                            class="form-label">Termination Date</label>
                                        <div class="input-group date">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i
                                                        class="fas fa-calendar-alt"></i></span>
                                            </div>
                                            <input type="date"
                                                class="form-control @error('termination_date') is-invalid @enderror"
                                                id="termination_date_edit_{{ $administration->id }}"
                                                name="termination_date"
                                                value="{{ old('termination_date', $administration->termination_date) }}">
                                        </div>
                                        @error('termination_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="termination_reason_edit_{{ $administration->id }}"
                                            class="form-label">Termination Reason</label>
                                        <select name="termination_reason"
                                            id="termination_reason_edit_{{ $administration->id }}"
                                            class="form-control @error('termination_reason') is-invalid @enderror"
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
                                            <option value="Retired"
                                                {{ old('termination_reason', $administration->termination_reason) == 'Retired' ? 'selected' : '' }}>
                                                Retired</option>
                                        </select>
                                        @error('termination_reason')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="coe_no_edit_{{ $administration->id }}"
                                            class="form-label">Certificate of Employment</label>
                                        <input type="text"
                                            class="form-control @error('coe_no') is-invalid @enderror"
                                            id="coe_no_edit_{{ $administration->id }}" name="coe_no"
                                            value="{{ old('coe_no', $administration->coe_no) }}"
                                            placeholder="Enter certificate number">
                                        @error('coe_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
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
@endforeach

@push('scripts')
    <script>
        $(document).ready(function() {
            // Function to fetch department
            function fetchDepartment(positionId, departmentElement) {
                if (!positionId) {
                    $(departmentElement).val('');
                    return;
                }

                $.ajax({
                    url: "{{ route('employees.getDepartment') }}",
                    type: "GET",
                    data: {
                        position_id: positionId
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data && data.department_name) {
                            $(departmentElement).val(data.department_name);
                        } else {
                            $(departmentElement).val('');
                        }
                    },
                    error: function() {
                        $(departmentElement).val('');
                    }
                });
            }

            // Handle position change for add modal
            $('#position_id_add').on('change', function() {
                fetchDepartment($(this).val(), $('#department_add'));
            });

            // Handle position change for edit modals
            @foreach ($administrations as $administration)
                $('#position_id_edit_{{ $administration->id }}').on('change', function() {
                    fetchDepartment($(this).val(), $('#department_edit_{{ $administration->id }}'));
                });
            @endforeach

            // Initialize departments when modals are shown
            $('[id^="modal-administration"]').on('shown.bs.modal', function() {
                const modalId = $(this).attr('id');
                if (modalId === 'modal-administration') {
                    // Add modal
                    if ($('#position_id_add').val()) {
                        fetchDepartment($('#position_id_add').val(), $('#department_add'));
                    }
                } else {
                    // Edit modal
                    const id = modalId.replace('modal-administration-', '');
                    if ($(`#position_id_edit_${id}`).val()) {
                        fetchDepartment($(`#position_id_edit_${id}`).val(), $(`#department_edit_${id}`));
                    }
                }
            });
        });
    </script>
@endpush
