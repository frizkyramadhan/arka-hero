@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $subtitle }}</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.personal') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">My Profile</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-id-card mr-1"></i>
                        <strong>{{ $employee->fullname }}</strong>
                    </h3>
                </div>
                <div class="card-body p-0">
                    <!-- HR Contact Information -->
                    <div class="alert alert-light alert-dismissible m-3"
                        style="border-left: 3px solid #95a5a6; background-color: #f8f9fa;">
                        <p class="mb-0" style="font-size: 0.9rem; color: #6c757d;">
                            <i class="fas fa-info-circle mr-2" style="color: #95a5a6;"></i>
                            <strong>Need to update your profile?</strong> Please contact HR Department with a written
                            request and supporting documents (ID card, bank statement, tax certificate, etc.). Visit the HR
                            office during business hours.
                        </p>
                    </div>
                    <div class="bs-stepper">
                        <div class="bs-stepper-header" role="tablist">
                            <div class="step active" data-target="#personal-detail-pane">
                                <button type="button" class="step-trigger" role="tab"
                                    aria-controls="personal-detail-pane" id="personal-detail-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-id-card"></i></span>
                                    <span class="bs-stepper-label">Personal</span>
                                </button>
                            </div>
                            <div class="step" data-target="#administration-pane">
                                <button type="button" class="step-trigger" role="tab"
                                    aria-controls="administration-pane" id="administration-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-briefcase"></i></span>
                                    <span class="bs-stepper-label">Employment</span>
                                </button>
                            </div>
                            <div class="step{{ $bank == null ? ' step-empty' : '' }}" data-target="#bank-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="bank-pane"
                                    id="bank-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-money-check-alt"></i></span>
                                    <span class="bs-stepper-label">&nbsp;&nbsp;Bank&nbsp;&nbsp;</span>
                                </button>
                            </div>
                            <div class="step{{ $tax == null ? ' step-empty' : '' }}" data-target="#tax-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="tax-pane"
                                    id="tax-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-file-invoice-dollar"></i></span>
                                    <span class="bs-stepper-label">&nbsp;&nbsp;Tax&nbsp;&nbsp;</span>
                                </button>
                            </div>
                            <div class="step{{ $insurances->isEmpty() ? ' step-empty' : '' }}"
                                data-target="#insurance-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="insurance-pane"
                                    id="insurance-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-heartbeat"></i></span>
                                    <span class="bs-stepper-label">Insurances</span>
                                </button>
                            </div>
                            <div class="step{{ $licenses->isEmpty() ? ' step-empty' : '' }}" data-target="#license-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="license-pane"
                                    id="license-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-id-badge"></i></span>
                                    <span class="bs-stepper-label">Licenses</span>
                                </button>
                            </div>
                            <div class="step{{ $families->isEmpty() ? ' step-empty' : '' }}" data-target="#family-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="family-pane"
                                    id="family-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-users"></i></span>
                                    <span class="bs-stepper-label">Families</span>
                                </button>
                            </div>
                            <div class="step{{ $educations->isEmpty() ? ' step-empty' : '' }}"
                                data-target="#education-pane">
                                <button type="button" class="step-trigger" role="tab"
                                    aria-controls="education-pane" id="education-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-graduation-cap"></i></span>
                                    <span class="bs-stepper-label">Educations</span>
                                </button>
                            </div>
                            <div class="step{{ $courses->isEmpty() ? ' step-empty' : '' }}" data-target="#course-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="course-pane"
                                    id="course-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-certificate"></i></span>
                                    <span class="bs-stepper-label">Courses</span>
                                </button>
                            </div>
                            <div class="step{{ $jobs->isEmpty() ? ' step-empty' : '' }}" data-target="#jobexp-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="jobexp-pane"
                                    id="jobexp-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-history"></i></span>
                                    <span class="bs-stepper-label">Experiences</span>
                                </button>
                            </div>
                            <div class="step{{ $emergencies->isEmpty() ? ' step-empty' : '' }}"
                                data-target="#emergency-pane">
                                <button type="button" class="step-trigger" role="tab"
                                    aria-controls="emergency-pane" id="emergency-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-phone-alt"></i></span>
                                    <span class="bs-stepper-label">Emergencies</span>
                                </button>
                            </div>
                            <div class="step{{ $units->isEmpty() ? ' step-empty' : '' }}" data-target="#unit-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="unit-pane"
                                    id="unit-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-truck"></i></span>
                                    <span class="bs-stepper-label">Units</span>
                                </button>
                            </div>
                            <div class="step{{ $additional == null ? ' step-empty' : '' }}"
                                data-target="#additional-pane">
                                <button type="button" class="step-trigger" role="tab"
                                    aria-controls="additional-pane" id="additional-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-info-circle"></i></span>
                                    <span class="bs-stepper-label">Additional</span>
                                </button>
                            </div>
                            <div class="step{{ $images->isEmpty() ? ' step-empty' : '' }}" data-target="#image-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="image-pane"
                                    id="image-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-images"></i></span>
                                    <span class="bs-stepper-label">Images</span>
                                </button>
                            </div>
                        </div>

                        <div class="bs-stepper-content p-3">
                            <!-- Personal Detail Pane -->
                            <div id="personal-detail-pane" class="content active" role="tabpanel"
                                aria-labelledby="personal-detail-pane-trigger"
                                style="opacity: 1; visibility: visible; display: block;">
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                    <h5 class="mb-0"><i class="fas fa-id-card mr-2 text-primary"></i>Personal Detail
                                    </h5>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 text-center">
                                        @if ($profile)
                                            <img class="img-fluid img-thumbnail" style="max-height: 250px;"
                                                src="{{ asset('images/' . $profile->employee_id . '/' . $profile->filename) }}"
                                                alt="User profile picture">
                                        @else
                                            <img class="img-fluid img-thumbnail" style="max-height: 250px;"
                                                src="{{ asset('assets/dist/img/avatar6.png') }}"
                                                alt="Default profile picture">
                                        @endif
                                    </div>
                                    <div class="col-md-9">
                                        <dl class="row">
                                            <dt class="col-sm-4">Full Name</dt>
                                            <dd class="col-sm-8">{{ $employee->fullname ?? '-' }}</dd>
                                            <dt class="col-sm-4">ID Card No.</dt>
                                            <dd class="col-sm-8">{{ $employee->identity_card ?? '-' }}</dd>
                                            <dt class="col-sm-4">Place/Date of Birth</dt>
                                            <dd class="col-sm-8">{{ $employee->emp_pob ?? '-' }},
                                                {{ $employee->emp_dob ? date('d M Y', strtotime($employee->emp_dob)) : '-' }}
                                            </dd>
                                            <dt class="col-sm-4">Blood Type</dt>
                                            <dd class="col-sm-8">{{ $employee->blood_type ?? '-' }}</dd>
                                            <dt class="col-sm-4">Religion</dt>
                                            <dd class="col-sm-8">{{ $employee->religion->religion_name ?? '-' }}</dd>
                                            <dt class="col-sm-4">Nationality</dt>
                                            <dd class="col-sm-8">{{ $employee->nationality ?? '-' }}</dd>
                                            <dt class="col-sm-4">Gender</dt>
                                            <dd class="col-sm-8">
                                                {{ $employee->gender == 'male' ? 'Male' : ($employee->gender == 'female' ? 'Female' : '-') }}
                                            </dd>
                                            <dt class="col-sm-4">Marital</dt>
                                            <dd class="col-sm-8">{{ $employee->marital ?? '-' }}</dd>
                                        </dl>
                                        <h6 class="mt-4 mb-3 text-muted border-top pt-3">Address & Contact</h6>
                                        <dl class="row">
                                            <dt class="col-sm-4">Address</dt>
                                            <dd class="col-sm-8">{{ $employee->address ?? '-' }}</dd>
                                            @if ($additional)
                                                <dt class="col-sm-4">City</dt>
                                                <dd class="col-sm-8">{{ $additional->city ?? '-' }}</dd>
                                                <dt class="col-sm-4">Postal Code</dt>
                                                <dd class="col-sm-8">{{ $additional->postal_code ?? '-' }}</dd>
                                            @endif
                                            <dt class="col-sm-4">Phone</dt>
                                            <dd class="col-sm-8">{{ $employee->phone ?? '-' }}</dd>
                                            <dt class="col-sm-4">Email</dt>
                                            <dd class="col-sm-8">{{ $employee->email ?? '-' }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>

                            <!-- Administration Pane -->
                            <div id="administration-pane" class="content" role="tabpanel"
                                aria-labelledby="administration-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                    <h5 class="mb-0"><i class="fas fa-briefcase mr-2 text-primary"></i>Employment
                                        Information</h5>
                                </div>
                                @if ($activeAdministration)
                                    <div class="mb-2">
                                        <h6 class="text-muted mb-2">Current Employment</h6>
                                        <dl class="row">
                                            <dt class="col-sm-4">NIK</dt>
                                            <dd class="col-sm-8"><strong>{{ $activeAdministration->nik ?? '-' }}</strong>
                                            </dd>
                                            <dt class="col-sm-4">Position</dt>
                                            <dd class="col-sm-8">
                                                {{ $activeAdministration->position->position_name ?? '-' }}</dd>
                                            <dt class="col-sm-4">Department</dt>
                                            <dd class="col-sm-8">
                                                {{ $activeAdministration->position->department->department_name ?? '-' }}
                                            </dd>
                                            <dt class="col-sm-4">Project</dt>
                                            <dd class="col-sm-8">{{ $activeAdministration->project->project_code ?? '-' }}
                                            </dd>
                                            <dt class="col-sm-4">Grade</dt>
                                            <dd class="col-sm-8">{{ $activeAdministration->grade->name ?? '-' }}</dd>
                                            <dt class="col-sm-4">Level</dt>
                                            <dd class="col-sm-8">{{ $activeAdministration->level->name ?? '-' }}</dd>
                                            <dt class="col-sm-4">Class</dt>
                                            <dd class="col-sm-8">
                                                {{ ucfirst($activeAdministration->class ?? '-') }}</dd>
                                            <dt class="col-sm-4">Date of Hire (DOH)</dt>
                                            <dd class="col-sm-8">
                                                {{ $activeAdministration->doh ? date('d M Y', strtotime($activeAdministration->doh)) : '-' }}
                                            </dd>
                                            @if ($activeAdministration->foc)
                                                <dt class="col-sm-4">End of Contract (FOC)</dt>
                                                <dd class="col-sm-8">
                                                    {{ date('d M Y', strtotime($activeAdministration->foc)) }}</dd>
                                            @endif
                                        </dl>
                                    </div>
                                @endif

                                @if ($administrations->count() > 1)
                                    <div class="mt-2">
                                        <h6 class="text-muted mb-2">Employment History</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>NIK</th>
                                                        <th>Project</th>
                                                        <th>Position</th>
                                                        <th>Department</th>
                                                        <th>DOH</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($administrations as $admin)
                                                        <tr>
                                                            <td>{{ $admin->nik }}</td>
                                                            <td>{{ $admin->project_code ?? '-' }}</td>
                                                            <td>{{ $admin->position_name ?? '-' }}</td>
                                                            <td>{{ $admin->department_name ?? '-' }}</td>
                                                            <td>{{ $admin->doh ? date('d M Y', strtotime($admin->doh)) : '-' }}
                                                            </td>
                                                            <td>
                                                                @if ($admin->is_active == '1')
                                                                    <span class="badge badge-success">Active</span>
                                                                @else
                                                                    <span class="badge badge-secondary">Inactive</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Bank Pane -->
                            <div id="bank-pane" class="content" role="tabpanel" aria-labelledby="bank-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                    <h5 class="mb-0"><i class="fas fa-money-check-alt mr-2 text-primary"></i>Bank
                                        Account
                                    </h5>
                                </div>
                                @if ($bank)
                                    <dl class="row">
                                        <dt class="col-sm-4">Bank Name</dt>
                                        <dd class="col-sm-8">{{ $bank->banks->bank_name ?? '-' }}</dd>
                                        <dt class="col-sm-4">Account Number</dt>
                                        <dd class="col-sm-8">{{ $bank->bank_account_no ?? '-' }}</dd>
                                        <dt class="col-sm-4">Account Name</dt>
                                        <dd class="col-sm-8">{{ $bank->bank_account_name ?? '-' }}</dd>
                                        @if ($bank->bank_account_branch)
                                            <dt class="col-sm-4">Branch</dt>
                                            <dd class="col-sm-8">{{ $bank->bank_account_branch ?? '-' }}</dd>
                                        @endif
                                    </dl>
                                @else
                                    <div class="empty-state-container">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-exclamation"></i>
                                        </div>
                                        <h6 class="empty-state-title">No Data Available</h6>
                                        <p class="empty-state-message">No additional information found for this employee
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Tax Pane -->
                            <div id="tax-pane" class="content" role="tabpanel" aria-labelledby="tax-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                    <h5 class="mb-0"><i class="fas fa-file-invoice-dollar mr-2 text-primary"></i>Tax
                                        Identification</h5>
                                </div>
                                @if ($tax)
                                    <dl class="row">
                                        <dt class="col-sm-4">NPWP Number</dt>
                                        <dd class="col-sm-8">{{ $tax->tax_no ?? '-' }}</dd>
                                        <dt class="col-sm-4">Registration Date</dt>
                                        <dd class="col-sm-8">
                                            {{ $tax->tax_valid_date ? date('d M Y', strtotime($tax->tax_valid_date)) : '-' }}
                                        </dd>
                                    </dl>
                                @else
                                    <div class="empty-state-container">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-exclamation"></i>
                                        </div>
                                        <h6 class="empty-state-title">No Data Available</h6>
                                        <p class="empty-state-message">No additional information found for this employee
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Insurance Pane -->
                            <div id="insurance-pane" class="content" role="tabpanel"
                                aria-labelledby="insurance-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                    <h5 class="mb-0"><i class="fas fa-heartbeat mr-2 text-primary"></i>Insurances</h5>
                                </div>
                                @if ($insurances->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Insurance Type</th>
                                                    <th>Insurance Number</th>
                                                    <th>Health Facility</th>
                                                    <th>Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($insurances as $insurance)
                                                    <tr>
                                                        <td>{{ $insurance->health_insurance_type == 'BPJS Ketenagakerjaan' ? 'BPJS Ketenagakerjaan' : 'BPJS Kesehatan' }}
                                                        </td>
                                                        <td>{{ $insurance->health_insurance_no ?? '-' }}</td>
                                                        <td>{{ $insurance->health_facility ?? '-' }}</td>
                                                        <td>{{ $insurance->health_insurance_remarks ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="empty-state-container">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-exclamation"></i>
                                        </div>
                                        <h6 class="empty-state-title">No Data Available</h6>
                                        <p class="empty-state-message">No additional information found for this employee
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- License Pane -->
                            <div id="license-pane" class="content" role="tabpanel"
                                aria-labelledby="license-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                    <h5 class="mb-0"><i class="fas fa-id-badge mr-2 text-primary"></i>Licenses</h5>
                                </div>
                                @if ($licenses->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>License Type</th>
                                                    <th>License Number</th>
                                                    <th>Expiry Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($licenses as $license)
                                                    <tr>
                                                        <td>{{ $license->driver_license_type ?? '-' }}</td>
                                                        <td>{{ $license->driver_license_no ?? '-' }}</td>
                                                        <td>{{ $license->driver_license_exp ? date('d M Y', strtotime($license->driver_license_exp)) : '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="empty-state-container">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-exclamation"></i>
                                        </div>
                                        <h6 class="empty-state-title">No Data Available</h6>
                                        <p class="empty-state-message">No additional information found for this employee
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Family Pane -->
                            <div id="family-pane" class="content" role="tabpanel" aria-labelledby="family-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                    <h5 class="mb-0"><i class="fas fa-users mr-2 text-primary"></i>Family</h5>
                                </div>
                                @if ($families->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Relationship</th>
                                                    <th>Name</th>
                                                    <th>Birthplace</th>
                                                    <th>Date of Birth</th>
                                                    <th>Remarks</th>
                                                    <th>BPJS Kesehatan No</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($families as $family)
                                                    <tr>
                                                        <td>{{ $family->family_relationship ?? '-' }}</td>
                                                        <td>{{ $family->family_name ?? '-' }}</td>
                                                        <td>{{ $family->family_birthplace ?? '-' }}</td>
                                                        <td>{{ $family->family_birthdate ? date('d M Y', strtotime($family->family_birthdate)) : '-' }}
                                                        </td>
                                                        <td>{{ $family->family_remarks ?? '-' }}</td>
                                                        <td>{{ $family->bpjsks_no ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="empty-state-container">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-exclamation"></i>
                                        </div>
                                        <h6 class="empty-state-title">No Data Available</h6>
                                        <p class="empty-state-message">No additional information found for this employee
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Education Pane -->
                            <div id="education-pane" class="content" role="tabpanel"
                                aria-labelledby="education-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                    <h5 class="mb-0"><i class="fas fa-graduation-cap mr-2 text-primary"></i>Education
                                    </h5>
                                </div>
                                @if ($educations->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Education Name</th>
                                                    <th>Address</th>
                                                    <th>Year</th>
                                                    <th>Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($educations as $education)
                                                    <tr>
                                                        <td>{{ $education->education_name ?? '-' }}</td>
                                                        <td>{{ $education->education_address ?? '-' }}</td>
                                                        <td>{{ $education->education_year ?? '-' }}</td>
                                                        <td>{{ $education->education_remarks ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="empty-state-container">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-exclamation"></i>
                                        </div>
                                        <h6 class="empty-state-title">No Data Available</h6>
                                        <p class="empty-state-message">No additional information found for this employee
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Course Pane -->
                            <div id="course-pane" class="content" role="tabpanel" aria-labelledby="course-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                    <h5 class="mb-0"><i class="fas fa-certificate mr-2 text-primary"></i>Courses</h5>
                                </div>
                                @if ($courses->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Course Name</th>
                                                    <th>Address</th>
                                                    <th>Year</th>
                                                    <th>Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($courses as $course)
                                                    <tr>
                                                        <td>{{ $course->course_name ?? '-' }}</td>
                                                        <td>{{ $course->course_address ?? '-' }}</td>
                                                        <td>{{ $course->course_year ?? '-' }}</td>
                                                        <td>{{ $course->course_remarks ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="empty-state-container">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-exclamation"></i>
                                        </div>
                                        <h6 class="empty-state-title">No Data Available</h6>
                                        <p class="empty-state-message">No additional information found for this employee
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Job Experience Pane -->
                            <div id="jobexp-pane" class="content" role="tabpanel" aria-labelledby="jobexp-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                    <h5 class="mb-0"><i class="fas fa-history mr-2 text-primary"></i>Job Experience</h5>
                                </div>
                                @if ($jobs->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Company Name</th>
                                                    <th>Company Address</th>
                                                    <th>Position</th>
                                                    <th>Duration</th>
                                                    <th>Quit Reason</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($jobs as $job)
                                                    <tr>
                                                        <td>{{ $job->company_name ?? '-' }}</td>
                                                        <td>{{ $job->company_address ?? '-' }}</td>
                                                        <td>{{ $job->job_position ?? '-' }}</td>
                                                        <td>{{ $job->job_duration ?? '-' }}</td>
                                                        <td>{{ $job->quit_reason ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="empty-state-container">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-exclamation"></i>
                                        </div>
                                        <h6 class="empty-state-title">No Data Available</h6>
                                        <p class="empty-state-message">No additional information found for this employee
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Emergency Pane -->
                            <div id="emergency-pane" class="content" role="tabpanel"
                                aria-labelledby="emergency-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                    <h5 class="mb-0"><i class="fas fa-phone-alt mr-2 text-primary"></i>Emergency
                                        Contacts</h5>
                                </div>
                                @if ($emergencies->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Relationship</th>
                                                    <th>Name</th>
                                                    <th>Address</th>
                                                    <th>Phone</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($emergencies as $emergency)
                                                    <tr>
                                                        <td>{{ $emergency->emrg_call_relation ?? '-' }}</td>
                                                        <td>{{ $emergency->emrg_call_name ?? '-' }}</td>
                                                        <td>{{ $emergency->emrg_call_address ?? '-' }}</td>
                                                        <td>{{ $emergency->emrg_call_phone ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="empty-state-container">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-exclamation"></i>
                                        </div>
                                        <h6 class="empty-state-title">No Data Available</h6>
                                        <p class="empty-state-message">No additional information found for this employee
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Unit Pane -->
                            <div id="unit-pane" class="content" role="tabpanel" aria-labelledby="unit-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                    <h5 class="mb-0"><i class="fas fa-truck mr-2 text-primary"></i>Operable Units</h5>
                                </div>
                                @if ($units->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Unit Name</th>
                                                    <th>Unit Type / Class</th>
                                                    <th>Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($units as $unit)
                                                    <tr>
                                                        <td>{{ $unit->unit_name ?? '-' }}</td>
                                                        <td>{{ $unit->unit_type ?? '-' }}</td>
                                                        <td>{{ $unit->unit_remarks ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="empty-state-container">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-exclamation"></i>
                                        </div>
                                        <h6 class="empty-state-title">No Data Available</h6>
                                        <p class="empty-state-message">No additional information found for this employee
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Additional Pane -->
                            <div id="additional-pane" class="content" role="tabpanel"
                                aria-labelledby="additional-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                    <h5 class="mb-0"><i class="fas fa-info-circle mr-2 text-primary"></i>Additional Data
                                    </h5>
                                </div>
                                @if ($additional)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="mt-3 mb-3 text-muted border-bottom pb-2">Clothing Information</h6>
                                            <dl class="row">
                                                @if ($additional->shirt_size)
                                                    <dt class="col-sm-6">Shirt Size</dt>
                                                    <dd class="col-sm-6">{{ $additional->shirt_size }}</dd>
                                                @endif
                                                @if ($additional->pants_size)
                                                    <dt class="col-sm-6">Pants Size</dt>
                                                    <dd class="col-sm-6">{{ $additional->pants_size }}</dd>
                                                @endif
                                                @if ($additional->shoes_size)
                                                    <dt class="col-sm-6">Shoes Size</dt>
                                                    <dd class="col-sm-6">{{ $additional->shoes_size }}</dd>
                                                @endif
                                            </dl>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="mt-3 mb-3 text-muted border-bottom pb-2">Address Information</h6>
                                            <dl class="row">
                                                @if ($additional->city)
                                                    <dt class="col-sm-6">City</dt>
                                                    <dd class="col-sm-6">{{ $additional->city }}</dd>
                                                @endif
                                                @if ($additional->postal_code)
                                                    <dt class="col-sm-6">Postal Code</dt>
                                                    <dd class="col-sm-6">{{ $additional->postal_code }}</dd>
                                                @endif
                                            </dl>
                                        </div>
                                    </div>
                                @else
                                    <div class="empty-state-container">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-exclamation"></i>
                                        </div>
                                        <h6 class="empty-state-title">No Data Available</h6>
                                        <p class="empty-state-message">No additional information found for this employee
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Image Pane -->
                            <div id="image-pane" class="content" role="tabpanel" aria-labelledby="image-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                    <h5 class="mb-0"><i class="fas fa-images mr-2 text-primary"></i>Employee Images</h5>
                                </div>
                                @if ($images->count() > 0)
                                    <div class="row mt-3">
                                        @foreach ($images as $image)
                                            <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                                                <div class="card h-100">
                                                    <a href="{{ asset('images/' . $image->employee_id . '/' . $image->filename) }}"
                                                        data-toggle="lightbox" data-title="{{ $image->filename }}"
                                                        data-gallery="gallery">
                                                        <img src="{{ asset('images/' . $image->employee_id . '/' . $image->filename) }}"
                                                            class="card-img-top" alt="{{ $image->filename }}"
                                                            style="height: 200px; object-fit: cover;" />
                                                    </a>
                                                    <div class="card-body p-2 text-center">
                                                        @if ($image->is_profile == 1)
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-check mr-1"></i> Profile Picture
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="empty-state-container">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-exclamation"></i>
                                        </div>
                                        <h6 class="empty-state-title">No Data Available</h6>
                                        <p class="empty-state-message">No additional information found for this employee
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button"
            aria-label="Scroll to top">
            <i class="fas fa-chevron-up"></i>
        </a>

    </section>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/ekko-lightbox/ekko-lightbox.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bs-stepper/css/bs-stepper.min.css') }}">
    <style>
        /* Critical CSS - Load First */
        section.content {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out;
        }

        section.content.loaded {
            opacity: 1;
            visibility: visible;
        }

        .card-body h5,
        .card-body h6 {
            color: #0056b3;
        }

        /* Table Styles */
        .table-modern {
            width: 100%;
            margin-bottom: 1rem;
            background-color: transparent;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-modern thead th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            padding: 1rem;
            border-bottom: 2px solid #dee2e6;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .table-modern tbody tr {
            transition: all 0.2s ease;
        }

        .table-modern tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .table-modern tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
            color: #212529;
        }

        .table-modern .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
            border-radius: 0.25rem;
        }

        .table-modern .text-center {
            text-align: center;
        }

        .table-modern .text-muted {
            color: #6c757d !important;
        }

        .table-modern .empty-state {
            padding: 2rem;
            text-align: center;
            color: #6c757d;
        }

        .table-modern .empty-state i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #adb5bd;
        }

        /* Responsive Table */
        .table-responsive {
            position: relative;
            width: 100%;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
            box-shadow: 0 0 1px rgba(0, 0, 0, 0.1);
        }

        .table-responsive::-webkit-scrollbar {
            height: 6px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        /* Status Badges */
        .badge-status {
            padding: 0.5em 0.75em;
            font-weight: 500;
            border-radius: 0.25rem;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .badge-status.active {
            background-color: #28a745;
            color: white;
        }

        .badge-status.inactive {
            background-color: #dc3545;
            color: white;
        }

        .badge-status.pending {
            background-color: #ffc107;
            color: #212529;
        }

        /* Empty State */
        .empty-state {
            padding: 2rem;
            text-align: center;
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            margin: 1rem 0;
        }

        .empty-state i {
            font-size: 2.5rem;
            color: #adb5bd;
            margin-bottom: 1rem;
        }

        .empty-state h6 {
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #adb5bd;
            margin-bottom: 0;
        }

        /* Loading Indicator */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .page-loader.hidden {
            display: none;
        }

        /* Optimized Stepper Styles */
        .bs-stepper {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .bs-stepper.initialized {
            opacity: 1;
        }

        .bs-stepper .step-trigger {
            padding: 8px 5px;
            color: #6c757d;
            background-color: transparent;
            transition: background-color 0.2s ease;
            user-select: none;
            -webkit-user-select: none;
        }

        .bs-stepper .step-trigger:hover {
            background-color: #f8f9fa;
            color: #0056b3;
        }

        .bs-stepper .bs-stepper-circle {
            background-color: #adb5bd;
            width: 35px;
            height: 35px;
            line-height: 32px;
            font-size: 1rem;
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
            will-change: transform;
        }

        .bs-stepper .step-trigger .bs-stepper-label {
            color: #495057;
            font-weight: 500;
            margin-left: 8px;
            font-size: small;
            transition: color 0.2s ease;
        }

        .bs-stepper .step.active .step-trigger .bs-stepper-circle {
            background-color: #007bff;
            box-shadow: 0 2px 5px rgba(0, 123, 255, 0.4);
        }

        .bs-stepper .step.active .step-trigger .bs-stepper-label {
            color: #007bff;
            font-weight: 600;
        }

        /* Optimized Header Styles */
        .bs-stepper-header {
            overflow-x: auto;
            overflow-y: hidden;
            white-space: nowrap;
            border-bottom: 1px solid #dee2e6;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .bs-stepper-header::-webkit-scrollbar {
            height: 6px;
        }

        .bs-stepper-header::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .bs-stepper-header::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        /* Optimized Content Animation */
        .bs-stepper-content .content {
            opacity: 0;
            visibility: hidden;
            display: none;
            transform: translateZ(0);
            transition: opacity 0.3s ease-out, visibility 0.3s ease-out;
            will-change: opacity;
        }

        .bs-stepper-content .content.active {
            opacity: 1;
            visibility: visible;
            display: block;
        }

        /* No special CSS for personal-detail-pane - let JavaScript control it */

        /* Optimized Table Styles */
        .table-sm th,
        .table-sm td {
            padding: 0.4rem;
        }

        .table-responsive {
            -webkit-overflow-scrolling: touch;
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 25px;
            right: 25px;
            display: none;
            z-index: 1030;
            transform: translateZ(0);
            will-change: transform, opacity;
            transition: opacity 0.2s ease;
        }

        /* Empty Step Styles */
        .bs-stepper-header .step.step-empty .step-trigger {
            opacity: 0.7;
            cursor: pointer;
        }

        .bs-stepper-header .step.step-empty .step-trigger:hover {
            opacity: 1;
            background-color: #f8f9fa;
        }

        .bs-stepper-header .step.step-empty .step-trigger .bs-stepper-label {
            color: #adb5bd;
            font-style: italic;
        }

        .bs-stepper-header .step.step-empty .step-trigger .bs-stepper-circle {
            background-color: #e9ecef;
            border: 1px dashed #adb5bd;
        }

        .bs-stepper-header .step.step-empty.active .step-trigger {
            opacity: 1;
        }

        .bs-stepper-header .step.step-empty.active .step-trigger .bs-stepper-label {
            color: #6c757d;
            font-weight: 500;
        }

        .bs-stepper-header .step.step-empty.active .step-trigger .bs-stepper-circle {
            background-color: #ced4da;
            border: 1px dashed #6c757d;
        }

        /* Responsive Optimizations */
        @media (max-width: 768px) {
            .bs-stepper .step-trigger {
                padding: 10px;
                text-align: center;
            }

            .bs-stepper .step-trigger .bs-stepper-label {
                display: block;
                margin: 5px 0 0;
                white-space: normal;
            }
        }

        /* Print Optimizations */
        @media print {
            .bs-stepper-header {
                overflow: visible;
            }

            .back-to-top {
                display: none !important;
            }
        }

        /* Empty State Styles */
        .empty-state-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem 2rem;
            text-align: center;
            min-height: 300px;
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            background-color: #f8f9fa;
        }

        .empty-state-icon i {
            font-size: 2.5rem;
            color: #6c757d;
        }

        .empty-state-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .empty-state-message {
            font-size: 0.95rem;
            color: #6c757d;
            margin: 0;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/ekko-lightbox/ekko-lightbox.min.js') }}" defer></script>
    <script src="{{ asset('assets/plugins/bs-stepper/js/bs-stepper.min.js') }}" defer></script>
    <script>
        // App initialization
        (function() {
            // Add loading indicator to body
            document.body.insertAdjacentHTML('afterbegin',
                '<div class="page-loader"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>'
            );

            // Global variables
            let stepper;
            const stepMap = {
                '#personal': 1,
                '#administration': 2,
                '#bank': 3,
                '#tax': 4,
                '#insurance': 5,
                '#license': 6,
                '#family': 7,
                '#education': 8,
                '#course': 9,
                '#jobexp': 10,
                '#emergency': 11,
                '#unit': 12,
                '#additional': 13,
                '#image': 14
            };

            // Main initialization when DOM is ready
            document.addEventListener('DOMContentLoaded', function() {
                // Immediately show first pane before any other initialization
                const firstPane = document.querySelector('#personal-detail-pane');
                if (firstPane) {
                    // Hide all other panes first
                    document.querySelectorAll('.bs-stepper-content .content').forEach(pane => {
                        if (pane.id !== 'personal-detail-pane') {
                            pane.classList.remove('active');
                            pane.style.opacity = '0';
                            pane.style.visibility = 'hidden';
                            pane.style.display = 'none';
                        }
                    });
                    // Show first pane with inline styles for immediate visibility
                    firstPane.classList.add('active');
                    firstPane.style.opacity = '1';
                    firstPane.style.visibility = 'visible';
                    firstPane.style.display = 'block';
                }

                hideLoader();
                initComponents();
                attachEventListeners();
            });

            /**
             * Remove loader and show content
             */
            function hideLoader() {
                const pageLoader = document.querySelector('.page-loader');
                if (pageLoader) {
                    pageLoader.classList.add('hidden');
                }
                const contentSection = document.querySelector('section.content');
                if (contentSection) {
                    contentSection.classList.add('loaded');
                }
            }

            /**
             * Initialize all page components
             */
            function initComponents() {
                initStepper();
                initBackToTop();
                initLightbox();
                handleInitialHash();
            }

            /**
             * Attach global event listeners
             */
            function attachEventListeners() {
                // Handle hash changes
                window.addEventListener('hashchange', handleHashChange);

                // Optimize back to top click
                const backToTop = document.getElementById('back-to-top');
                backToTop.addEventListener('click', scrollToTop);

                // Add scroll listener for back-to-top button visibility
                let ticking = false;
                window.addEventListener('scroll', function() {
                    if (!ticking) {
                        window.requestAnimationFrame(function() {
                            backToTop.style.display = window.pageYOffset > 100 ? 'block' : 'none';
                            ticking = false;
                        });
                        ticking = true;
                    }
                });

                // Attach step trigger click listeners
                document.querySelectorAll('.step-trigger').forEach(trigger => {
                    trigger.addEventListener('click', function() {
                        const paneId = this.getAttribute('aria-controls');
                        if (paneId) {
                            // Hide all panes first - use setProperty for important
                            document.querySelectorAll('.bs-stepper-content .content').forEach(pane => {
                                pane.classList.remove('active');
                                pane.style.setProperty('opacity', '0', 'important');
                                pane.style.setProperty('visibility', 'hidden', 'important');
                                pane.style.setProperty('display', 'none', 'important');
                            });

                            // Show target pane
                            const targetPane = document.getElementById(paneId);
                            if (targetPane) {
                                targetPane.classList.add('active');
                                targetPane.style.setProperty('opacity', '1', 'important');
                                targetPane.style.setProperty('visibility', 'visible', 'important');
                                targetPane.style.setProperty('display', 'block', 'important');
                            }

                            let hash = paneId.replace('-pane', '');
                            // Special case for personal-detail-pane
                            if (hash === 'personal-detail') {
                                hash = 'personal';
                            }
                            window.location.hash = hash;
                            scrollToTop();
                        }
                    });
                });
            }

            /**
             * Initialize the stepper component
             */
            function initStepper() {
                stepper = new Stepper(document.querySelector('.bs-stepper'), {
                    linear: false,
                    animation: true,
                    selectors: {
                        steps: '.step',
                        trigger: '.step-trigger',
                        stepper: '.bs-stepper'
                    }
                });

                // Ensure all panes can be activated when steps are clicked
                document.querySelectorAll('.step').forEach(function(step) {
                    step.addEventListener('click', function() {
                        let targetPane = this.getAttribute('data-target');
                        if (targetPane) {
                            // Hide all panes (including personal-detail-pane) - use setProperty for important
                            document.querySelectorAll('.bs-stepper-content .content').forEach(pane => {
                                pane.classList.remove('active');
                                pane.style.setProperty('opacity', '0', 'important');
                                pane.style.setProperty('visibility', 'hidden', 'important');
                                pane.style.setProperty('display', 'none', 'important');
                            });
                            // Show target pane
                            const targetPaneElement = document.querySelector(targetPane);
                            if (targetPaneElement) {
                                targetPaneElement.classList.add('active');
                                targetPaneElement.style.setProperty('opacity', '1', 'important');
                                targetPaneElement.style.setProperty('visibility', 'visible',
                                    'important');
                                targetPaneElement.style.setProperty('display', 'block', 'important');
                            }
                        }
                    });
                });

                // Activate first step and pane by default if no hash
                if (!window.location.hash) {
                    stepper.to(1);
                    const firstPane = document.querySelector('#personal-detail-pane');
                    if (firstPane) {
                        // Hide all other panes except first pane
                        document.querySelectorAll('.bs-stepper-content .content').forEach(pane => {
                            if (pane.id !== 'personal-detail-pane') {
                                pane.classList.remove('active');
                                pane.style.opacity = '0';
                                pane.style.visibility = 'hidden';
                                pane.style.display = 'none';
                            }
                        });
                        // Ensure first pane is visible and active
                        firstPane.classList.add('active');
                        firstPane.style.opacity = '1';
                        firstPane.style.visibility = 'visible';
                        firstPane.style.display = 'block';
                    }
                    const firstStep = document.querySelector('.step[data-target="#personal-detail-pane"]');
                    if (firstStep) {
                        firstStep.classList.add('active');
                    }
                }

                document.querySelector('.bs-stepper').classList.add('initialized');
            }

            /**
             * Initialize back to top button
             */
            function initBackToTop() {
                document.getElementById('back-to-top').style.display = 'none';
            }

            /**
             * Initialize lightbox for images
             */
            function initLightbox() {
                document.querySelectorAll('[data-toggle="lightbox"]').forEach(function(el) {
                    el.addEventListener('click', function(e) {
                        e.preventDefault();
                        $(this).ekkoLightbox({
                            alwaysShowClose: true,
                            loadingMessage: 'Loading...'
                        });
                    });
                });
            }

            /**
             * Handle smooth scrolling to top
             */
            function scrollToTop(e) {
                if (e) e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }

            /**
             * Process URL hash and navigate to correct tab
             */
            function handleInitialHash() {
                const hash = window.location.hash.toLowerCase();
                if (hash && stepMap.hasOwnProperty(hash)) {
                    stepper.to(stepMap[hash]);
                    // Activate corresponding pane
                    const stepNumber = stepMap[hash];
                    const stepElement = document.querySelectorAll('.step')[stepNumber - 1];
                    if (stepElement) {
                        const targetPane = stepElement.getAttribute('data-target');
                        if (targetPane) {
                            // Hide all panes - use setProperty for important
                            document.querySelectorAll('.bs-stepper-content .content').forEach(pane => {
                                pane.classList.remove('active');
                                pane.style.setProperty('opacity', '0', 'important');
                                pane.style.setProperty('visibility', 'hidden', 'important');
                                pane.style.setProperty('display', 'none', 'important');
                            });
                            // Show target pane
                            const paneElement = document.querySelector(targetPane);
                            if (paneElement) {
                                paneElement.classList.add('active');
                                paneElement.style.setProperty('opacity', '1', 'important');
                                paneElement.style.setProperty('visibility', 'visible', 'important');
                                paneElement.style.setProperty('display', 'block', 'important');
                            }
                        }
                    }
                    scrollToTop();
                } else {
                    // No hash, show first pane (personal detail)
                    stepper.to(1);
                    const firstPane = document.querySelector('#personal-detail-pane');
                    if (firstPane) {
                        // Hide all panes except first
                        document.querySelectorAll('.bs-stepper-content .content').forEach(pane => {
                            if (pane.id !== 'personal-detail-pane') {
                                pane.classList.remove('active');
                                pane.style.setProperty('opacity', '0', 'important');
                                pane.style.setProperty('visibility', 'hidden', 'important');
                                pane.style.setProperty('display', 'none', 'important');
                            }
                        });
                        // Show first pane
                        firstPane.classList.add('active');
                        firstPane.style.setProperty('opacity', '1', 'important');
                        firstPane.style.setProperty('visibility', 'visible', 'important');
                        firstPane.style.setProperty('display', 'block', 'important');
                    }
                }
            }

            /**
             * Handle hash change events
             */
            function handleHashChange() {
                const hash = window.location.hash.toLowerCase();
                if (hash && stepMap.hasOwnProperty(hash)) {
                    stepper.to(stepMap[hash]);
                    // Activate corresponding pane
                    const stepNumber = stepMap[hash];
                    const stepElement = document.querySelectorAll('.step')[stepNumber - 1];
                    if (stepElement) {
                        const targetPane = stepElement.getAttribute('data-target');
                        if (targetPane) {
                            // Hide all panes - use setProperty for important
                            document.querySelectorAll('.bs-stepper-content .content').forEach(pane => {
                                pane.classList.remove('active');
                                pane.style.setProperty('opacity', '0', 'important');
                                pane.style.setProperty('visibility', 'hidden', 'important');
                                pane.style.setProperty('display', 'none', 'important');
                            });
                            // Show target pane
                            const paneElement = document.querySelector(targetPane);
                            if (paneElement) {
                                paneElement.classList.add('active');
                                paneElement.style.setProperty('opacity', '1', 'important');
                                paneElement.style.setProperty('visibility', 'visible', 'important');
                                paneElement.style.setProperty('display', 'block', 'important');
                            }
                        }
                    }
                    scrollToTop();
                }
            }
        })();
    </script>
@endsection
