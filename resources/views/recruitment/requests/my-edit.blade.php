@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-0">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $subtitle }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.my-requests') }}">{{ $title }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form action="{{ route('recruitment.my-requests.update', $fptk->id) }}" method="POST" id="fptkForm">
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
                                            <label for="fptk_number">Request Number</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                                </div>
                                                <input type="text" class="form-control bg-light" id="fptk_number"
                                                    value="{{ $fptk->request_number }}" readonly>
                                            </div>
                                            <small class="form-text text-muted">
                                                Nomor sementara (user request). Nomor FPTK resmi diassign oleh HR.
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="request_date">Request Date <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date" class="form-control" name="request_date" id="request_date"
                                                    value="{{ old('request_date', $fptk->created_at->format('Y-m-d')) }}" readonly>
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
                                            <label for="required_qty">Required Quantity <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-users"></i></span>
                                                </div>
                                                <input type="number" name="required_qty" id="required_qty"
                                                    class="form-control @error('required_qty') is-invalid @enderror"
                                                    min="1" max="50" value="{{ old('required_qty', $fptk->required_qty) }}" required>
                                                @error('required_qty')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="required_date">Required Date <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date" name="required_date" id="required_date"
                                                    class="form-control @error('required_date') is-invalid @enderror"
                                                    value="{{ old('required_date', $fptk->required_date->format('Y-m-d')) }}" required>
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
                                            <label for="request_reason">Request Reason <span class="text-danger">*</span></label>
                                            <select name="request_reason" id="request_reason"
                                                class="form-control select2-success @error('request_reason') is-invalid @enderror"
                                                style="width: 100%;" required>
                                                <option value="">Select Request Reason</option>
                                                <option value="additional_workplan" {{ old('request_reason', $fptk->request_reason) == 'additional_workplan' ? 'selected' : '' }}>Additional - Workplan</option>
                                                <option value="replacement_promotion" {{ old('request_reason', $fptk->request_reason) == 'replacement_promotion' ? 'selected' : '' }}>Replacement - Promotion, Mutation, Demotion</option>
                                                <option value="replacement_resign" {{ old('request_reason', $fptk->request_reason) == 'replacement_resign' ? 'selected' : '' }}>Replacement - Resign, Termination, End of Contract</option>
                                            </select>
                                            @error('request_reason')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="employment_type">Employment Type <span class="text-danger">*</span></label>
                                            <select name="employment_type" id="employment_type"
                                                class="form-control select2-success @error('employment_type') is-invalid @enderror"
                                                style="width: 100%;" required>
                                                <option value="">Select Employment Type</option>
                                                <option value="pkwtt" {{ old('employment_type', $fptk->employment_type) == 'pkwtt' ? 'selected' : '' }}>PKWTT (Permanent)</option>
                                                <option value="pkwt" {{ old('employment_type', $fptk->employment_type) == 'pkwt' ? 'selected' : '' }}>PKWT (Contract)</option>
                                                <option value="harian" {{ old('employment_type', $fptk->employment_type) == 'harian' ? 'selected' : '' }}>Daily Worker</option>
                                                <option value="magang" {{ old('employment_type', $fptk->employment_type) == 'magang' ? 'selected' : '' }}>Internship</option>
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
                                                <option value="male" {{ old('required_gender', $fptk->required_gender) == 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ old('required_gender', $fptk->required_gender) == 'female' ? 'selected' : '' }}>Female</option>
                                                <option value="any" {{ old('required_gender', $fptk->required_gender) == 'any' ? 'selected' : '' }}>Any</option>
                                            </select>
                                            @error('required_gender')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="required_marital_status">Marital Status <span class="text-danger">*</span></label>
                                            <select name="required_marital_status" id="required_marital_status"
                                                class="form-control select2-warning @error('required_marital_status') is-invalid @enderror"
                                                style="width: 100%;" required>
                                                <option value="">Select Marital Status</option>
                                                <option value="single" {{ old('required_marital_status', $fptk->required_marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                                                <option value="married" {{ old('required_marital_status', $fptk->required_marital_status) == 'married' ? 'selected' : '' }}>Married</option>
                                                <option value="any" {{ old('required_marital_status', $fptk->required_marital_status) == 'any' ? 'selected' : '' }}>Any</option>
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
                                                min="17" max="65" value="{{ old('required_age_min', $fptk->required_age_min) }}" placeholder="17">
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
                                                min="17" max="65" value="{{ old('required_age_max', $fptk->required_age_max) }}" placeholder="65">
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
                                        value="{{ old('required_education', $fptk->required_education) }}" placeholder="e.g., Bachelor's Degree">
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
                                            name="requires_theory_test" value="1" {{ old('requires_theory_test', $fptk->requires_theory_test) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="requires_theory_test">
                                            <strong>Posisi ini memerlukan Tes Teori</strong><br>
                                            <small class="text-muted">Centang jika posisi adalah mekanik atau memerlukan kompetensi teknis</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons (no Approver Selection for my-requests) -->
                        <div class="card elevation-3">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-2"></i> Update Recruitment Request
                                </button>
                                <a href="{{ route('recruitment.my-requests.show', $fptk->id) }}" class="btn btn-info btn-block">
                                    <i class="fas fa-eye mr-2"></i> View Details
                                </a>
                                <a href="{{ route('recruitment.my-requests') }}" class="btn btn-secondary btn-block">
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
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .content-header { margin-bottom: 1rem; }
        .card { border-radius: 0.5rem; margin-bottom: 1.5rem; }
        .card-header { border-radius: calc(0.5rem - 1px) calc(0.5rem - 1px) 0 0; }
        .form-control { border-radius: 0.25rem; }
        .form-control:focus { border-color: #80bdff; box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); }
        .input-group-text, .btn { border-radius: 0.25rem; }
        .select2-container--bootstrap4.select2-container--focus .select2-selection { border-color: #80bdff; box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); }
        .select2-container--bootstrap4 .select2-selection--single { height: calc(2.25rem + 2px) !important; }
        .select2-container--bootstrap4 .select2-selection__rendered { line-height: 2.25rem !important; }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(function() {
            $('.select2-primary').select2({ theme: 'bootstrap4', placeholder: 'Select an option' });
            $('.select2-success').select2({ theme: 'bootstrap4', placeholder: 'Select an option' });
            $('.select2-warning').select2({ theme: 'bootstrap4', placeholder: 'Select an option' });
            $('.select2-secondary').select2({ theme: 'bootstrap4', placeholder: 'Select an option' });

            $('#request_reason').on('change', function() {
                var v = $(this).val();
                $('#other_reason').prop('disabled', v !== 'other').prop('required', v === 'other');
                if (v !== 'other') $('#other_reason').val('');
            });

            $('#fptkForm').on('submit', function(e) {
                var ok = true;
                $(this).find('[required]').each(function() {
                    if (!$(this).val()) { ok = false; $(this).addClass('is-invalid'); } else { $(this).removeClass('is-invalid'); }
                });
                var minA = parseInt($('#required_age_min').val());
                var maxA = parseInt($('#required_age_max').val());
                if (minA && maxA && minA > maxA) { ok = false; $('#required_age_max').addClass('is-invalid'); }
                if (!ok) { e.preventDefault(); alert('Please fill in all required fields and ensure min age ≤ max age.'); }
            });

            $('#department_id').focus();
        });
    </script>
@endsection
