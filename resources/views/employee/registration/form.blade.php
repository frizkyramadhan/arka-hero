<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Employee Self Registration - HCSSIS</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <!-- Select2 -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <!-- BS Stepper -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">

        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            .registration-container {
                background: white;
                border-radius: 15px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                margin: 2rem auto;
                max-width: 1200px;
                overflow: hidden;
            }

            .header-section {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 2rem;
                text-align: center;
            }

            .header-section h1 {
                margin: 0;
                font-size: 2.5rem;
                font-weight: 300;
            }

            .header-section p {
                margin: 0.5rem 0 0 0;
                opacity: 0.9;
            }

            .form-section {
                padding: 2rem;
            }

            .bs-stepper .step-trigger {
                padding: 8px 5px;
                color: #6c757d;
                background-color: transparent;
                transition: all 0.3s ease;
            }

            .bs-stepper .step-trigger:hover {
                background-color: #f8f9fa;
                color: #0056b3;
            }

            .bs-stepper .bs-stepper-circle {
                background-color: #adb5bd;
                transition: all 0.3s ease;
                width: 35px;
                height: 35px;
                line-height: 32px;
                font-size: 1rem;
            }

            .bs-stepper .step.active .step-trigger .bs-stepper-circle {
                background-color: #667eea;
                box-shadow: 0 2px 5px rgba(102, 126, 234, 0.4);
            }

            .bs-stepper .step.active .step-trigger .bs-stepper-label {
                color: #667eea;
                font-weight: 600;
            }

            .required-field::after {
                content: " *";
                color: #dc3545;
                font-weight: bold;
            }

            .alert-info {
                background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
                border: 1px solid #90caf9;
                color: #1565c0;
            }

            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                transition: all 0.3s ease;
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            }

            .security-notice {
                background: #f8f9fa;
                border-left: 4px solid #28a745;
                padding: 1rem;
                margin: 1rem 0;
            }

            @media (max-width: 768px) {
                .bs-stepper-header {
                    overflow-x: auto;
                    white-space: nowrap;
                    padding-bottom: 10px;
                }

                .bs-stepper .step {
                    display: inline-block;
                    vertical-align: top;
                    width: auto;
                    margin-right: 15px;
                }
            }
        </style>
    </head>

    <body>
        <div class="container-fluid">
            <div class="registration-container">
                <!-- Header -->
                <div class="header-section">
                    <h1><i class="fas fa-user-plus me-3"></i>Employee Self Registration</h1>
                    <p>Complete your employee information to join our team</p>
                </div>

                <!-- Security Notice -->
                <div class="form-section pt-3 pb-0">
                    <div class="security-notice">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Secure Registration:</strong> This form uses encrypted connection and your data is
                        protected.
                        Only authorized personnel can access your information.
                    </div>
                </div>

                <!-- Main Form -->
                <div class="form-section">
                    <form method="POST" action="{{ route('employee.registration.store', $token) }}"
                        enctype="multipart/form-data" id="employeeRegistrationForm">
                        @csrf

                        <div class="bs-stepper">
                            <div class="bs-stepper-header" role="tablist">
                                <div class="step" data-target="#personal-step">
                                    <button type="button" class="step-trigger" role="tab"
                                        aria-controls="personal-step" id="personal-step-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-user"></i></span>
                                        <span class="bs-stepper-label">Personal Info</span>
                                    </button>
                                </div>

                                <div class="step" data-target="#contact-step">
                                    <button type="button" class="step-trigger" role="tab"
                                        aria-controls="contact-step" id="contact-step-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-address-card"></i></span>
                                        <span class="bs-stepper-label">Contact</span>
                                    </button>
                                </div>

                                <div class="step" data-target="#documents-step">
                                    <button type="button" class="step-trigger" role="tab"
                                        aria-controls="documents-step" id="documents-step-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-file-upload"></i></span>
                                        <span class="bs-stepper-label">Documents</span>
                                    </button>
                                </div>

                                <div class="step" data-target="#review-step">
                                    <button type="button" class="step-trigger" role="tab"
                                        aria-controls="review-step" id="review-step-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-check"></i></span>
                                        <span class="bs-stepper-label">Review</span>
                                    </button>
                                </div>
                            </div>

                            <div class="bs-stepper-content">
                                <!-- Personal Information Step -->
                                <div id="personal-step" class="content" role="tabpanel"
                                    aria-labelledby="personal-step-trigger">
                                    <h4 class="mb-4">Personal Information</h4>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="fullname" class="form-label required-field">Full Name</label>
                                            <input type="text" class="form-control" id="fullname" name="fullname"
                                                value="{{ old('fullname', $registration->personal_data['fullname'] ?? '') }}"
                                                required>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="identity_card" class="form-label required-field">Identity Card
                                                (KTP)</label>
                                            <input type="text" class="form-control" id="identity_card"
                                                name="identity_card"
                                                value="{{ old('identity_card', $registration->personal_data['identity_card'] ?? '') }}"
                                                required>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="emp_pob" class="form-label required-field">Place of
                                                Birth</label>
                                            <input type="text" class="form-control" id="emp_pob" name="emp_pob"
                                                value="{{ old('emp_pob', $registration->personal_data['emp_pob'] ?? '') }}"
                                                required>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="emp_dob" class="form-label required-field">Date of
                                                Birth</label>
                                            <input type="date" class="form-control" id="emp_dob" name="emp_dob"
                                                value="{{ old('emp_dob', $registration->personal_data['emp_dob'] ?? '') }}"
                                                required>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="religion_id" class="form-label">Religion</label>
                                            <select class="form-select" id="religion_id" name="religion_id">
                                                <option value="">Select Religion</option>
                                                @foreach ($religions as $religion)
                                                    <option value="{{ $religion->id }}"
                                                        {{ old('religion_id', $registration->personal_data['religion_id'] ?? '') == $religion->id ? 'selected' : '' }}>
                                                        {{ $religion->religion_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Gender</label>
                                            <div class="mt-2">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" id="gender_male"
                                                        name="gender" value="male"
                                                        {{ old('gender', $registration->personal_data['gender'] ?? '') == 'male' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="gender_male">Male</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" id="gender_female"
                                                        name="gender" value="female"
                                                        {{ old('gender', $registration->personal_data['gender'] ?? '') == 'female' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="gender_female">Female</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="button" class="btn btn-primary btn-next">
                                            <i class="fas fa-arrow-right me-1"></i> Next
                                        </button>
                                    </div>
                                </div>

                                <!-- Contact Information Step -->
                                <div id="contact-step" class="content" role="tabpanel"
                                    aria-labelledby="contact-step-trigger">
                                    <h4 class="mb-4">Contact Information</h4>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="email" class="form-label required-field">Email
                                                Address</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="{{ old('email', $registration->personal_data['email'] ?? $tokenRecord->email) }}"
                                                required readonly>
                                            <div class="form-text">This email was used to send you the registration
                                                link</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="phone" class="form-label required-field">Phone
                                                Number</label>
                                            <input type="tel" class="form-control" id="phone" name="phone"
                                                value="{{ old('phone', $registration->personal_data['phone'] ?? '') }}"
                                                required>
                                        </div>

                                        <div class="col-12">
                                            <label for="address" class="form-label required-field">Address</label>
                                            <textarea class="form-control" id="address" name="address" rows="3" required>{{ old('address', $registration->personal_data['address'] ?? '') }}</textarea>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" class="form-control" id="city" name="city"
                                                value="{{ old('city', $registration->personal_data['city'] ?? '') }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="nationality" class="form-label">Nationality</label>
                                            <input type="text" class="form-control" id="nationality"
                                                name="nationality"
                                                value="{{ old('nationality', $registration->personal_data['nationality'] ?? 'Indonesia') }}">
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary btn-previous">
                                            <i class="fas fa-arrow-left me-1"></i> Previous
                                        </button>
                                        <button type="button" class="btn btn-primary btn-next">
                                            <i class="fas fa-arrow-right me-1"></i> Next
                                        </button>
                                    </div>
                                </div>

                                <!-- Documents Step -->
                                <div id="documents-step" class="content" role="tabpanel"
                                    aria-labelledby="documents-step-trigger">
                                    <h4 class="mb-4">Upload Documents</h4>

                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Please upload clear, legible copies of the required documents. Supported
                                        formats: PDF, JPG, PNG (Max 5MB each)
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="ktp_file" class="form-label required-field">Identity Card
                                                (KTP)</label>
                                            <input type="file" class="form-control" id="ktp_file"
                                                name="ktp_file" accept=".pdf,.jpg,.jpeg,.png" required>
                                            <div class="form-text">Upload a clear copy of your KTP</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="cv_file" class="form-label">CV/Resume</label>
                                            <input type="file" class="form-control" id="cv_file" name="cv_file"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <div class="form-text">Upload your latest CV or resume</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="photo_file" class="form-label">Passport Photo</label>
                                            <input type="file" class="form-control" id="photo_file"
                                                name="photo_file" accept=".jpg,.jpeg,.png">
                                            <div class="form-text">Professional passport-style photo</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="certificate_file" class="form-label">Education
                                                Certificate</label>
                                            <input type="file" class="form-control" id="certificate_file"
                                                name="certificate_file" accept=".pdf,.jpg,.jpeg,.png">
                                            <div class="form-text">Highest education certificate</div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary btn-previous">
                                            <i class="fas fa-arrow-left me-1"></i> Previous
                                        </button>
                                        <button type="button" class="btn btn-primary btn-next">
                                            <i class="fas fa-arrow-right me-1"></i> Next
                                        </button>
                                    </div>
                                </div>

                                <!-- Review Step -->
                                <div id="review-step" class="content" role="tabpanel"
                                    aria-labelledby="review-step-trigger">
                                    <h4 class="mb-4">Review Your Information</h4>

                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Please review all information carefully before submitting. Once submitted, you
                                        cannot modify the information.
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal
                                                        Information</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div id="review-personal-info">
                                                        <!-- Will be populated by JavaScript -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="mb-0"><i class="fas fa-address-card me-2"></i>Contact
                                                        Information</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div id="review-contact-info">
                                                        <!-- Will be populated by JavaScript -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="mb-0"><i class="fas fa-file-upload me-2"></i>Uploaded
                                                        Documents</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div id="review-documents">
                                                        <!-- Will be populated by JavaScript -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary btn-previous">
                                            <i class="fas fa-arrow-left me-1"></i> Previous
                                        </button>
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="fas fa-paper-plane me-2"></i> Submit Registration
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function() {
                // Initialize stepper
                const stepper = new Stepper(document.querySelector('.bs-stepper'), {
                    linear: false,
                    animation: true
                });

                // Navigation handlers
                $('.btn-next').on('click', function() {
                    if (validateCurrentStep()) {
                        stepper.next();
                        updateReviewSection();
                    }
                });

                $('.btn-previous').on('click', function() {
                    stepper.previous();
                });

                // Initialize Select2
                $('.form-select').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });

                // Form validation
                function validateCurrentStep() {
                    const currentStep = $('.bs-stepper .step.active');
                    const requiredFields = currentStep.find('[required]');
                    let isValid = true;

                    requiredFields.each(function() {
                        if (!$(this).val()) {
                            $(this).addClass('is-invalid');
                            isValid = false;
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    });

                    return isValid;
                }

                // Update review section
                function updateReviewSection() {
                    // Personal Info
                    const personalInfo = `
                    <p><strong>Full Name:</strong> ${$('#fullname').val()}</p>
                    <p><strong>Identity Card:</strong> ${$('#identity_card').val()}</p>
                    <p><strong>Place of Birth:</strong> ${$('#emp_pob').val()}</p>
                    <p><strong>Date of Birth:</strong> ${$('#emp_dob').val()}</p>
                    <p><strong>Gender:</strong> ${$('input[name="gender"]:checked').val() || 'Not specified'}</p>
                `;
                    $('#review-personal-info').html(personalInfo);

                    // Contact Info
                    const contactInfo = `
                    <p><strong>Email:</strong> ${$('#email').val()}</p>
                    <p><strong>Phone:</strong> ${$('#phone').val()}</p>
                    <p><strong>Address:</strong> ${$('#address').val()}</p>
                    <p><strong>City:</strong> ${$('#city').val()}</p>
                `;
                    $('#review-contact-info').html(contactInfo);

                    // Documents
                    let documentsInfo = '';
                    $('input[type="file"]').each(function() {
                        if (this.files.length > 0) {
                            documentsInfo +=
                                `<p><strong>${$(this).prev('label').text()}:</strong> ${this.files[0].name}</p>`;
                        }
                    });
                    $('#review-documents').html(documentsInfo || '<p class="text-muted">No documents uploaded</p>');
                }

                // CSRF token setup for AJAX
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                // Auto-save functionality
                let saveTimeout;
                $('input, select, textarea').on('change', function() {
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(function() {
                        saveFormData();
                    }, 2000);
                });

                function saveFormData() {
                    const formData = new FormData($('#employeeRegistrationForm')[0]);

                    $.ajax({
                        url: "{{ route('employee.registration.store', $token) }}",
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                console.log('Auto-saved successfully');
                            }
                        },
                        error: function(xhr) {
                            console.log('Auto-save failed');
                        }
                    });
                }

                // Handle form submission
                $('#employeeRegistrationForm').on('submit', function(e) {
                    e.preventDefault();

                    // Validate all required fields
                    let isValid = true;
                    $('[required]').each(function() {
                        if (!$(this).val()) {
                            $(this).addClass('is-invalid');
                            isValid = false;
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    });

                    if (!isValid) {
                        alert('Please fill in all required fields.');
                        return;
                    }

                    // Confirm submission
                    if (confirm(
                            'Are you sure you want to submit your registration? You cannot modify it after submission.'
                        )) {
                        // Add submit flag to form
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'submit',
                            value: 'true'
                        }).appendTo('#employeeRegistrationForm');

                        // Disable submit button
                        const submitBtn = $('.btn-success');
                        submitBtn.prop('disabled', true).html(
                            '<i class="fas fa-spinner fa-spin me-2"></i> Submitting...');

                        // Submit form normally
                        this.submit();
                    }
                });
            });
        </script>
    </body>

</html>
