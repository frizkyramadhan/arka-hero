@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-0">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Recruitment Request</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ Request::is('recruitment/my-requests*') ? route('recruitment.my-requests') : route('recruitment.requests.index') }}">{{ $title ?? 'Recruitment Requests' }}</a>
                        </li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form
                action="{{ Request::is('recruitment/my-requests*') ? route('recruitment.my-requests.update', $fptk->id) : route('recruitment.requests.update', $fptk->id) }}"
                method="POST" id="fptkForm">
                @csrf
                @method('PUT')
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-8">

                        <!-- Main FPTK Info Card -->
                        <div class="card card-primary card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user-tie mr-2"></i>
                                    <strong>FPTK Information</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fptk_number">FPTK Number <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                                </div>
                                                <input type="text" class="form-control alert-success" id="fptk_number"
                                                    value="{{ $fptk->request_number }}" readonly>
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Current FPTK number (auto-generated)
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="request_date">Request Date <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date" class="form-control" name="request_date"
                                                    id="request_date"
                                                    value="{{ old('request_date', $fptk->created_at->format('Y-m-d')) }}"
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="department_id">Department <span class="text-danger">*</span></label>
                                            <select name="department_id" id="department_id"
                                                class="form-control select2-primary @error('department_id') is-invalid @enderror"
                                                style="width: 100%;" required>
                                                <option value="">Select Department</option>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->id }}"
                                                        {{ old('department_id', $fptk->department_id) == $department->id ? 'selected' : '' }}>
                                                        {{ $department->department_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('department_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="project_id">Project <span class="text-danger">*</span></label>
                                            <select name="project_id" id="project_id"
                                                class="form-control select2-primary @error('project_id') is-invalid @enderror"
                                                style="width: 100%;" required>
                                                <option value="">Select Project</option>
                                                @foreach ($projects as $project)
                                                    <option value="{{ $project->id }}"
                                                        {{ old('project_id', $fptk->project_id) == $project->id ? 'selected' : '' }}>
                                                        {{ $project->project_code }} - {{ $project->project_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('project_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="position_id">Position <span class="text-danger">*</span></label>
                                            <select name="position_id" id="position_id"
                                                class="form-control select2-primary @error('position_id') is-invalid @enderror"
                                                style="width: 100%;" required>
                                                <option value="">Select Position</option>
                                                @foreach ($positions as $position)
                                                    <option value="{{ $position->id }}"
                                                        {{ old('position_id', $fptk->position_id) == $position->id ? 'selected' : '' }}>
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
                                            <label for="level_id">Level <span class="text-danger">*</span></label>
                                            <select name="level_id" id="level_id"
                                                class="form-control select2-primary @error('level_id') is-invalid @enderror"
                                                style="width: 100%;" required>
                                                <option value="">Select Level</option>
                                                @foreach ($levels as $level)
                                                    <option value="{{ $level->id }}"
                                                        {{ old('level_id', $fptk->level_id) == $level->id ? 'selected' : '' }}>
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

                                <div class="form-group">
                                    <label for="job_description">Job Description <span class="text-danger">*</span></label>
                                    <textarea name="job_description" id="job_description"
                                        class="form-control @error('job_description') is-invalid @enderror" rows="3"
                                        placeholder="Describe the job responsibilities and duties..." required>{{ old('job_description', $fptk->job_description) }}</textarea>
                                    @error('job_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Request Details Card -->
                        <div class="card card-success card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-clipboard-list mr-2"></i>
                                    <strong>Request Details</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="required_qty">Required Quantity <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-users"></i></span>
                                                </div>
                                                <input type="number" name="required_qty" id="required_qty"
                                                    class="form-control @error('required_qty') is-invalid @enderror"
                                                    min="1" max="50"
                                                    value="{{ old('required_qty', $fptk->required_qty) }}" required>
                                                @error('required_qty')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="required_date">Required Date <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date" name="required_date" id="required_date"
                                                    class="form-control @error('required_date') is-invalid @enderror"
                                                    value="{{ old('required_date', $fptk->required_date->format('Y-m-d')) }}"
                                                    required>
                                                @error('required_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="request_reason">Request Reason <span
                                                    class="text-danger">*</span></label>
                                            <select name="request_reason" id="request_reason"
                                                class="form-control select2-success @error('request_reason') is-invalid @enderror"
                                                style="width: 100%;" required>
                                                <option value="">Select Request Reason</option>
                                                <option value="additional_workplan"
                                                    {{ old('request_reason', $fptk->request_reason) == 'additional_workplan' ? 'selected' : '' }}>
                                                    Additional - Workplan</option>
                                                <option value="replacement_promotion"
                                                    {{ old('request_reason', $fptk->request_reason) == 'replacement_promotion' ? 'selected' : '' }}>
                                                    Replacement - Promotion, Mutation, Demotion</option>
                                                <option value="replacement_resign"
                                                    {{ old('request_reason', $fptk->request_reason) == 'replacement_resign' ? 'selected' : '' }}>
                                                    Replacement - Resign, Termination, End of Contract</option>
                                            </select>
                                            @error('request_reason')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="employment_type">Employment Type <span
                                                    class="text-danger">*</span></label>
                                            <select name="employment_type" id="employment_type"
                                                class="form-control select2-success @error('employment_type') is-invalid @enderror"
                                                style="width: 100%;" required>
                                                <option value="">Select Employment Type</option>
                                                <option value="pkwtt"
                                                    {{ old('employment_type', $fptk->employment_type) == 'pkwtt' ? 'selected' : '' }}>
                                                    PKWTT (Permanent)</option>
                                                <option value="pkwt"
                                                    {{ old('employment_type', $fptk->employment_type) == 'pkwt' ? 'selected' : '' }}>
                                                    PKWT (Contract)</option>
                                                <option value="harian"
                                                    {{ old('employment_type', $fptk->employment_type) == 'harian' ? 'selected' : '' }}>
                                                    Daily Worker</option>
                                                <option value="magang"
                                                    {{ old('employment_type', $fptk->employment_type) == 'magang' ? 'selected' : '' }}>
                                                    Internship</option>
                                            </select>
                                            @error('employment_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Requirements Card -->
                        <div class="card card-warning card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user-check mr-2"></i>
                                    <strong>Requirements</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="required_gender">Gender <span class="text-danger">*</span></label>
                                            <select name="required_gender" id="required_gender"
                                                class="form-control select2-warning @error('required_gender') is-invalid @enderror"
                                                style="width: 100%;" required>
                                                <option value="">Select Gender</option>
                                                <option value="male"
                                                    {{ old('required_gender', $fptk->required_gender) == 'male' ? 'selected' : '' }}>
                                                    Male</option>
                                                <option value="female"
                                                    {{ old('required_gender', $fptk->required_gender) == 'female' ? 'selected' : '' }}>
                                                    Female</option>
                                                <option value="any"
                                                    {{ old('required_gender', $fptk->required_gender) == 'any' ? 'selected' : '' }}>
                                                    Any</option>
                                            </select>
                                            @error('required_gender')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="required_marital_status">Marital Status <span
                                                    class="text-danger">*</span></label>
                                            <select name="required_marital_status" id="required_marital_status"
                                                class="form-control select2-warning @error('required_marital_status') is-invalid @enderror"
                                                style="width: 100%;" required>
                                                <option value="">Select Marital Status</option>
                                                <option value="single"
                                                    {{ old('required_marital_status', $fptk->required_marital_status) == 'single' ? 'selected' : '' }}>
                                                    Single</option>
                                                <option value="married"
                                                    {{ old('required_marital_status', $fptk->required_marital_status) == 'married' ? 'selected' : '' }}>
                                                    Married</option>
                                                <option value="any"
                                                    {{ old('required_marital_status', $fptk->required_marital_status) == 'any' ? 'selected' : '' }}>
                                                    Any</option>
                                            </select>
                                            @error('required_marital_status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="required_age_min">Min Age</label>
                                            <input type="number" name="required_age_min" id="required_age_min"
                                                class="form-control @error('required_age_min') is-invalid @enderror"
                                                min="17" max="65"
                                                value="{{ old('required_age_min', $fptk->required_age_min) }}"
                                                placeholder="17">
                                            @error('required_age_min')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="required_age_max">Max Age</label>
                                            <input type="number" name="required_age_max" id="required_age_max"
                                                class="form-control @error('required_age_max') is-invalid @enderror"
                                                min="17" max="65"
                                                value="{{ old('required_age_max', $fptk->required_age_max) }}"
                                                placeholder="65">
                                            @error('required_age_max')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="required_education">Education</label>
                                    <input type="text" name="required_education" id="required_education"
                                        class="form-control @error('required_education') is-invalid @enderror"
                                        value="{{ old('required_education', $fptk->required_education) }}"
                                        placeholder="e.g., Bachelor's Degree">
                                    @error('required_education')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="required_skills">Required Skills</label>
                                            <textarea name="required_skills" id="required_skills"
                                                class="form-control @error('required_skills') is-invalid @enderror" rows="3"
                                                placeholder="List specific skills required...">{{ old('required_skills', $fptk->required_skills) }}</textarea>
                                            @error('required_skills')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="required_experience">Required Experience</label>
                                            <textarea name="required_experience" id="required_experience"
                                                class="form-control @error('required_experience') is-invalid @enderror" rows="3"
                                                placeholder="Describe minimum experience...">{{ old('required_experience', $fptk->required_experience) }}</textarea>
                                            @error('required_experience')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-4">
                        <!-- Additional Requirements Card -->
                        <div class="card card-secondary card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-plus-circle mr-2"></i>
                                    <strong>Additional Requirements</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="required_physical">Physical Requirements</label>
                                    <textarea name="required_physical" id="required_physical"
                                        class="form-control @error('required_physical') is-invalid @enderror" rows="3"
                                        placeholder="Any physical requirements...">{{ old('required_physical', $fptk->required_physical) }}</textarea>
                                    @error('required_physical')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="required_mental">Mental Requirements</label>
                                    <textarea name="required_mental" id="required_mental"
                                        class="form-control @error('required_mental') is-invalid @enderror" rows="3"
                                        placeholder="Any mental/cognitive requirements...">{{ old('required_mental', $fptk->required_mental) }}</textarea>
                                    @error('required_mental')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="other_requirements">Other Requirements</label>
                                    <textarea name="other_requirements" id="other_requirements"
                                        class="form-control @error('other_requirements') is-invalid @enderror" rows="3"
                                        placeholder="Any other specific requirements...">{{ old('other_requirements', $fptk->other_requirements) }}</textarea>
                                    @error('other_requirements')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="requires_theory_test"
                                            name="requires_theory_test" value="1"
                                            {{ old('requires_theory_test', $fptk->requires_theory_test) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="requires_theory_test">
                                            <strong>Posisi ini memerlukan Tes Teori</strong><br>
                                            <small class="text-muted">Centang jika posisi adalah mekanik atau memerlukan
                                                kompetensi teknis</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Approver Selection Card -->
                        <div class="card card-info card-outline elevation-2">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <i class="fas fa-users mr-2"></i>
                                    <strong>Approver Selection</strong>
                                </h3>
                            </div>
                            <div class="card-body py-2">
                                @include('components.manual-approver-selector', [
                                    'selectedApprovers' => old('manual_approvers', $fptk->manual_approvers ?? []),
                                    'required' => false,
                                    'multiple' => true,
                                    'helpText' => 'Pilih minimal 1 approver dengan role approver',
                                    'documentType' => 'recruitment_request',
                                ])
                            </div>
                        </div>

                        <!-- Approval Status Card -->
                        {{-- <x-approval-status-card :documentType="'recruitment_request'" :documentId="$fptk->id" mode="preview" :projectId="old('project_id', $fptk->project_id)"
                            :departmentId="old('department_id', $fptk->department_id)" :requestReason="old('request_reason', $fptk->request_reason)" title="Approval Preview" id="dynamicApprovalCard" /> --}}

                        <!-- Action Buttons -->
                        <div class="card elevation-3">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-2"></i> Update Recruitment Request
                                </button>
                                <a href="{{ route('recruitment.requests.show', $fptk->id) }}"
                                    class="btn btn-info btn-block">
                                    <i class="fas fa-eye mr-2"></i> View Details
                                </a>
                                <a href="{{ route('recruitment.requests.index') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times-circle mr-2"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        /* === FPTK Edit Form Styles === */

        /* Content Header */
        .content-header {
            margin-bottom: 1rem;
        }

        /* Card Styling */
        .card {
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            border-radius: calc(0.5rem - 1px) calc(0.5rem - 1px) 0 0;
        }

        .card.elevation-2 .card-body {
            padding: 0.75rem;
        }

        /* Form Controls */
        .form-control {
            border-radius: 0.25rem;
        }

        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .input-group-text,
        .btn {
            border-radius: 0.25rem;
        }

        /* Select2 Enhancements */
        .select2-container--bootstrap4.select2-container--focus .select2-selection {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(2.25rem + 2px) !important;
        }

        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 2.25rem !important;
        }

        /* Select2 Theme Colors */
        .select2-container--bootstrap4.select2-container--primary .select2-selection {
            border-color: #007bff;
        }

        .select2-container--bootstrap4.select2-container--success .select2-selection {
            border-color: #28a745;
        }

        .select2-container--bootstrap4.select2-container--warning .select2-selection {
            border-color: #ffc107;
        }

        .select2-container--bootstrap4.select2-container--secondary .select2-selection {
            border-color: #6c757d;
        }

        .select2-container--bootstrap4.select2-container--info .select2-selection {
            border-color: #17a2b8;
        }

        /* FPTK Number Status */
        #fptk_number.alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        #fptk_number.alert-warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }

        /* Letter Number Selector */
        .letter-number-selector .form-label {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .letter-number-selector .btn-group-vertical .btn {
            margin-bottom: 0.25rem;
        }

        .alert-sm {
            padding: 0.375rem 0.75rem;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        /* Button Theme Colors */
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #545b62;
            border-color: #545b62;
        }

        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }

        .btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
        }
    </style>
@endsection

@section('scripts')
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        // FPTK Edit Form Manager
        const FPTKEditForm = {

            // Initialize all form components
            init() {
                this.initSelect2();
                this.initDateFields();
                this.initFormValidation();
                this.initApprovalStatusCard();
                this.bindEvents();
                this.focusFirstField();
            },

            // Initialize Select2 dropdowns
            initSelect2() {
                const select2Config = {
                    theme: 'bootstrap4',
                    placeholder: 'Select an option'
                };

                const select2Focus = function() {
                    document.querySelector('.select2-search__field')?.focus();
                };

                // Initialize all Select2 elements with unified config
                ['.select2-primary', '.select2-success', '.select2-warning',
                    '.select2-secondary', '.select2-info'
                ].forEach(selector => {
                    $(selector).select2(select2Config).on('select2:open', select2Focus);
                });
            },

            // Initialize date fields
            initDateFields() {
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                const minDate = tomorrow.toISOString().split('T')[0];
                // $('#required_date').attr('min', minDate);
            },

            // Initialize form validation
            initFormValidation() {
                const self = this;

                // Form submission validation
                $('#fptkForm').on('submit', function(e) {
                    if (!self.validateForm()) {
                        e.preventDefault();
                    }
                });

                // Age validation
                $('#required_age_min, #required_age_max').on('input', this.validateAgeRange);
            },

            // Initialize approval status card
            initApprovalStatusCard() {
                const self = this;

                // Listen for changes that affect approval flow
                $('#project_id, #department_id, #request_reason').on('change', function() {
                    self.updateApprovalStatusCard();
                });

                // Initial load
                this.updateApprovalStatusCard();
            },

            // Bind all event handlers
            bindEvents() {
                this.bindReasonField();
                this.bindLetterNumberIntegration();
            },

            // Focus on first field
            focusFirstField() {
                $('#department_id').focus();
            },

            // Handle request reason field logic
            bindReasonField() {
                const currentReason = $('#request_reason').val();
                this.toggleReasonField(currentReason);

                $('#request_reason').on('change', (e) => {
                    this.toggleReasonField(e.target.value);
                });
            },

            // Toggle other reason field visibility
            toggleReasonField(selectedValue) {
                const $otherReason = $('#other_reason');
                const isOther = selectedValue === 'other';

                $otherReason
                    .prop('disabled', !isOther)
                    .prop('required', isOther);

                if (!isOther) {
                    $otherReason.val('');
                }
            },

            // Validate form fields
            validateForm() {
                let isValid = true;
                let errorMessage = '';

                // Check required fields
                let missingFields = [];
                $('#fptkForm').find('[required]').each(function() {
                    if (!$(this).val()) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                        // Ambil label field jika ada, atau pakai name/id
                        let fieldLabel = $(this).closest('.form-group, .form-row').find('label').first().text()
                            .replace(':', '').trim();
                        if (!fieldLabel) {
                            fieldLabel = $(this).attr('name') || $(this).attr('id') || 'Unknown field';
                        }
                        missingFields.push(fieldLabel);
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                if (missingFields.length > 0) {
                    errorMessage += "Please fill in all required fields:\n- " + missingFields.join('\n- ') + "\n";
                }

                // Age validation
                if (!this.isAgeRangeValid()) {
                    isValid = false;
                    errorMessage += 'Minimum age cannot be greater than maximum age.\n';
                }

                if (!isValid) {
                    alert(errorMessage);
                }

                return isValid;
            },

            // Validate age range
            validateAgeRange() {
                const minAge = parseInt($('#required_age_min').val());
                const maxAge = parseInt($('#required_age_max').val());

                if (minAge && maxAge && minAge > maxAge) {
                    $('#required_age_max').val('');
                }
            },

            // Check if age range is valid
            isAgeRangeValid() {
                const minAge = parseInt($('#required_age_min').val());
                const maxAge = parseInt($('#required_age_max').val());
                return !(minAge && maxAge && minAge > maxAge);
            },

            // Letter Number Integration
            bindLetterNumberIntegration() {
                const self = this;
                // Monitor letter number selection changes
                $(document).on('change', 'select[name="letter_number_id"]', function() {
                    const selectedOption = $(this).find('option:selected');
                    const letterNumberId = $(this).val();

                    if (letterNumberId && selectedOption.data('number')) {
                        const letterData = selectedOption.data('number');
                        self.generateFPTKNumber(letterData);
                    } else {
                        // Keep current FPTK number if no letter selected
                        $('#fptk_number').val('{{ $fptk->request_number }}');
                    }
                });

                // Monitor project selection changes to update FPTK number
                $('#project_id').on('change', function() {
                    const selectedLetterOption = $('select[name="letter_number_id"]').find('option:selected');
                    if (selectedLetterOption.data('number')) {
                        const letterData = selectedLetterOption.data('number');
                        self.generateFPTKNumber(letterData);
                    }
                });
            },

            // Generate FPTK number based on selected letter
            generateFPTKNumber(letterData) {
                if (letterData && letterData.letter_number) {
                    const currentYear = new Date().getFullYear();
                    const currentMonth = new Date().getMonth() + 1;
                    const romanMonth = this.convertToRoman(currentMonth);

                    // Extract numeric part from letter number (remove FPTK prefix)
                    let letterNumber = letterData.letter_number;
                    if (letterNumber.startsWith('FPTK')) {
                        letterNumber = letterNumber.replace('FPTK', '');
                    }
                    letterNumber = parseInt(letterNumber).toString().padStart(4, '0');

                    // Get selected project code
                    const projectCode = this.getSelectedProjectCode();

                    // Generate FPTK number: [Letter Number]/HCS-[Project Code]/FPTK/[Roman Month]/[Year]
                    const fptkNumber = `${letterNumber}/HCS-${projectCode}/FPTK/${romanMonth}/${currentYear}`;

                    $('#fptk_number').val(fptkNumber).removeClass('alert-warning').addClass('alert-success');

                    // Show success message
                    this.showLetterNumberStatus('success', `FPTK Number updated: ${fptkNumber}`);
                }
            },

            // Get selected project code
            getSelectedProjectCode() {
                const selectedProjectText = $('#project_id').find('option:selected').text().trim();

                if (selectedProjectText && selectedProjectText !== 'Select Project' && selectedProjectText !== '') {
                    const projectParts = selectedProjectText.split(' - ');
                    if (projectParts.length > 0 && projectParts[0].trim() !== '') {
                        return projectParts[0].trim();
                    }
                }

                return '000H'; // default
            },

            // Convert number to Roman numeral
            convertToRoman(num) {
                const values = [1000, 900, 500, 400, 100, 90, 50, 40, 10, 9, 5, 4, 1];
                const symbols = ['M', 'CM', 'D', 'CD', 'C', 'XC', 'L', 'XL', 'X', 'IX', 'V', 'IV', 'I'];
                let result = '';

                for (let i = 0; i < values.length; i++) {
                    while (num >= values[i]) {
                        result += symbols[i];
                        num -= values[i];
                    }
                }
                return result;
            },

            // Show letter number status message
            showLetterNumberStatus(type, message) {
                const $statusAlert = $('.letter-number-selector .status-alert');
                const $statusMessage = $('.letter-number-selector .status-message');

                if ($statusAlert.length && $statusMessage.length) {
                    $statusAlert
                        .removeClass('alert-info alert-success alert-warning alert-danger')
                        .addClass(`alert-${type}`)
                        .show();

                    $statusMessage.text(message);

                    // Auto-hide success messages after 5 seconds
                    if (type === 'success') {
                        setTimeout(() => $statusAlert.fadeOut(), 5000);
                    }
                }
            },

            // Update Approval Status Card
            updateApprovalStatusCard() {
                const projectId = $('#project_id').val();
                const departmentId = $('#department_id').val();
                const requestReason = $('#request_reason').val();
                const $approvalCard = $('#dynamicApprovalCard');


                if (!$approvalCard.length) {
                    return;
                }

                // Show appropriate message based on form state
                if (!projectId || !departmentId) {
                    this.showApprovalMessage($approvalCard, 'info',
                        'Select project, department, and request reason to see approval flow');
                    return;
                }

                if (!requestReason) {
                    this.showApprovalMessage($approvalCard, 'warning',
                        'Select request reason to see specific approval flow');
                    return;
                }

                // Show loading state
                this.showApprovalMessage($approvalCard, 'loading', 'Loading approval flow...');

                // Fetch approval stages
                this.fetchApprovalStages(projectId, departmentId, requestReason, $approvalCard);
            },

            // Show approval message
            showApprovalMessage($card, type, message) {
                const iconClass = {
                    'info': 'fas fa-info-circle',
                    'warning': 'fas fa-info-circle text-warning',
                    'loading': 'fas fa-spinner fa-spin text-info',
                    'error': 'fas fa-exclamation-triangle'
                };

                $card.find('.card-body').html(`
                    <div class="text-center py-3">
                        <i class="${iconClass[type]}"></i>
                        <div class="mt-2">${message}</div>
                    </div>
                `);
            },

            // Fetch approval stages from server
            fetchApprovalStages(projectId, departmentId, requestReason, $approvalCard) {
                const self = this;

                $.ajax({
                    url: '{{ route('approval.stages.preview') }}',
                    method: 'GET',
                    data: {
                        project_id: projectId,
                        department_id: departmentId,
                        document_type: 'recruitment_request',
                        request_reason: requestReason
                    },
                    success: function(response) {
                        if (response.success && response.approvers.length > 0) {
                            self.renderApprovalFlow(response.approvers, $approvalCard);
                        } else {
                            self.showNoApprovalFlow($approvalCard, projectId, departmentId);
                        }
                    },
                    error: function(xhr, status, error) {
                        self.showApprovalMessage($approvalCard, 'error',
                            `Failed to load approval flow<br><small class="text-muted">${error}</small>`
                        );
                    }
                });
            },

            // Render approval flow HTML
            renderApprovalFlow(approvers, $approvalCard) {
                let html = '<div class="approval-flow preview-mode">';

                approvers.forEach((approver, index) => {
                    html += `
                        <div class="approval-step preview-step">
                            <div class="step-number">${approver.order || index + 1}</div>
                            <div class="step-content">
                                <div class="approver-name">${approver.name}</div>
                                <div class="approver-department">${approver.department}</div>
                                <div class="step-label">Step ${approver.order || index + 1}</div>
                            </div>
                        </div>
                    `;
                });

                html += '</div>';
                $approvalCard.find('.card-body').html(html);
            },

            // Show no approval flow message
            showNoApprovalFlow($approvalCard, projectId, departmentId) {
                $approvalCard.find('.card-body').html(`
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-info-circle"></i>
                        <div class="mt-2">No approval flow configured for this project and department</div>
                        <small class="text-muted">Project ID: ${projectId}, Department ID: ${departmentId}</small>
                    </div>
                `);
            }
        };

        // Initialize the form when document is ready
        $(function() {
            FPTKEditForm.init();
        });
    </script>
@endsection
