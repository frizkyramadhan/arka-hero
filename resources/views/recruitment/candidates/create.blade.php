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
                        <li class="breadcrumb-item"><a
                                href="{{ route('recruitment.candidates.index') }}">{{ $title }}</a></li>
                        <li class="breadcrumb-item active">Add New</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form action="{{ route('recruitment.candidates.store') }}" method="POST" enctype="multipart/form-data"
                id="candidateForm">
                @csrf
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-8">
                        <!-- Personal Information Card -->
                        <div class="card card-primary card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user mr-2"></i>
                                    <strong>Personal Information</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fullname">Full Name <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                </div>
                                                <input type="text"
                                                    class="form-control @error('fullname') is-invalid @enderror"
                                                    name="fullname" id="fullname" value="{{ old('fullname') }}"
                                                    placeholder="Enter full name" required>
                                            </div>
                                            @error('fullname')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                </div>
                                                <input type="email"
                                                    class="form-control @error('email') is-invalid @enderror" name="email"
                                                    id="email" value="{{ old('email') }}"
                                                    placeholder="Enter email address" required>
                                            </div>
                                            @error('email')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                </div>
                                                <input type="text"
                                                    class="form-control @error('phone') is-invalid @enderror" name="phone"
                                                    id="phone" value="{{ old('phone') }}"
                                                    placeholder="Enter phone number" required>
                                            </div>
                                            @error('phone')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="date_of_birth">Date of Birth <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                </div>
                                                <input type="date"
                                                    class="form-control @error('date_of_birth') is-invalid @enderror"
                                                    name="date_of_birth" id="date_of_birth"
                                                    value="{{ old('date_of_birth') }}" required>
                                            </div>
                                            @error('date_of_birth')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="address">Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        </div>
                                        <textarea class="form-control @error('address') is-invalid @enderror" name="address" id="address" rows="3"
                                            placeholder="Enter complete address" required>{{ old('address') }}</textarea>
                                    </div>
                                    @error('address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Professional Information Card -->
                        <div class="card card-info card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-briefcase mr-2"></i>
                                    <strong>Professional Information</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="education_level">Last Education Level <span
                                                    class="text-danger">*</span></label>
                                            <select name="education_level" id="education_level"
                                                class="form-control select2-primary @error('education_level') is-invalid @enderror"
                                                style="width: 100%;" required>
                                                <option value="">Select Education Level</option>
                                                <option value="SMA/SMK"
                                                    {{ old('education_level') == 'SMA/SMK' ? 'selected' : '' }}>SMA/SMK
                                                </option>
                                                <option value="D1"
                                                    {{ old('education_level') == 'D1' ? 'selected' : '' }}>D1</option>
                                                <option value="D2"
                                                    {{ old('education_level') == 'D2' ? 'selected' : '' }}>D2</option>
                                                <option value="D3"
                                                    {{ old('education_level') == 'D3' ? 'selected' : '' }}>D3</option>
                                                <option value="S1"
                                                    {{ old('education_level') == 'S1' ? 'selected' : '' }}>S1
                                                </option>
                                                <option value="S2"
                                                    {{ old('education_level') == 'S2' ? 'selected' : '' }}>S2</option>
                                                <option value="S3"
                                                    {{ old('education_level') == 'S3' ? 'selected' : '' }}>S3</option>
                                            </select>
                                            @error('education_level')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="experience_years">Years of Experience <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                                </div>
                                                <input type="number"
                                                    class="form-control @error('experience_years') is-invalid @enderror"
                                                    name="experience_years" id="experience_years"
                                                    value="{{ old('experience_years') }}" min="0" max="50"
                                                    placeholder="Enter years of experience" required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">years</span>
                                                </div>
                                            </div>
                                            @error('experience_years')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="position_applied">Position Applied For</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                        </div>
                                        <input type="text"
                                            class="form-control @error('position_applied') is-invalid @enderror"
                                            name="position_applied" id="position_applied"
                                            value="{{ old('position_applied') }}" placeholder="Enter desired position">
                                    </div>
                                    @error('position_applied')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="skills">Skills & Competencies</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-tools"></i></span>
                                        </div>
                                        <textarea class="form-control @error('skills') is-invalid @enderror" name="skills" id="skills" rows="3"
                                            placeholder="Enter skills and competencies (optional)">{{ old('skills') }}</textarea>
                                    </div>
                                    @error('skills')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="previous_companies">Previous Companies</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                                        </div>
                                        <textarea class="form-control @error('previous_companies') is-invalid @enderror" name="previous_companies"
                                            id="previous_companies" rows="2" placeholder="Enter previous companies (optional)">{{ old('previous_companies') }}</textarea>
                                    </div>
                                    @error('previous_companies')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Salary Information Card -->
                        <div class="card card-warning card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-money-bill-wave mr-2"></i>
                                    <strong>Salary Information</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="current_salary">Current Salary</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Rp</span>
                                                </div>
                                                <input type="number"
                                                    class="form-control @error('current_salary') is-invalid @enderror"
                                                    name="current_salary" id="current_salary"
                                                    value="{{ old('current_salary') }}" min="0" step="1000"
                                                    placeholder="Enter current salary">
                                            </div>
                                            @error('current_salary')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="expected_salary">Expected Salary</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Rp</span>
                                                </div>
                                                <input type="number"
                                                    class="form-control @error('expected_salary') is-invalid @enderror"
                                                    name="expected_salary" id="expected_salary"
                                                    value="{{ old('expected_salary') }}" min="0" step="1000"
                                                    placeholder="Enter expected salary">
                                            </div>
                                            @error('expected_salary')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-4">
                        <!-- Remarks Card -->
                        <div class="card card-info card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-comment mr-2"></i>
                                    <strong>Remarks</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="remarks">Additional Notes</label>
                                    <textarea class="form-control @error('remarks') is-invalid @enderror" name="remarks" id="remarks" rows="4"
                                        placeholder="Enter additional remarks about the candidate (optional)">{{ old('remarks') }}</textarea>
                                    @error('remarks')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- CV Upload Card -->
                        <div class="card card-success card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-file-upload mr-2"></i>
                                    <strong>CV Upload</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="cv_file">CV File</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-file"></i></span>
                                        </div>
                                        <input type="file" class="form-control @error('cv_file') is-invalid @enderror"
                                            name="cv_file" id="cv_file" accept=".pdf,.doc,.docx,.zip,.rar">
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Accepted formats: PDF, DOC, DOCX, ZIP, RAR (Max: 10MB)
                                    </small>
                                    @error('cv_file')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons Card -->
                        <div class="card card-outline elevation-3">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Save Candidate
                                </button>
                                <a href="{{ route('recruitment.candidates.index') }}"
                                    class="btn btn-secondary btn-block">
                                    <i class="fas fa-arrow-left"></i> Back
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
@endsection

@section('scripts')
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        $(function() {
            // Initialize Select2
            $('.select2-primary').select2({
                theme: 'bootstrap4'
            });

            // Form validation
            $('#candidateForm').on('submit', function() {
                // Add loading state
                $(this).find('button[type="submit"]').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...'
                );
            });

            // Phone number formatting
            $('#phone').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if (value.length > 0) {
                    if (value.startsWith('0')) {
                        value = value.substring(1);
                    }
                    if (value.startsWith('62')) {
                        value = '0' + value.substring(2);
                    }
                }
                $(this).val(value);
            });

            // Date of birth validation
            // $('#date_of_birth').on('change', function() {
            //     const selectedDate = new Date($(this).val());
            //     const today = new Date();
            //     const age = today.getFullYear() - selectedDate.getFullYear();

            //     if (age < 17 || age > 65) {
            //         alert('Age must be between 17 and 65 years old.');
            //         $(this).val('');
            //     }
            // });
        });
    </script>
@endsection
