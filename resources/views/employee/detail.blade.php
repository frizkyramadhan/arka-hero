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
                        <li class="breadcrumb-item"><a href="{{ url('employees') }}">Employees</a></li>
                        <li class="breadcrumb-item active">Detail</li>
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
                    <div class="card-tools">
                        <a href="{{ url('employees/print/' . $employee->id) }}" class="btn btn-success" target="blank">
                            <i class="fas fa-print mr-1"></i> Print
                        </a>
                        @role('administrator')
                            <form action="{{ url('employees/' . $employee->id) }}" method="post"
                                onsubmit="return confirm('This employee and all associated data will be deleted. Are you sure?')"
                                class="d-inline ml-1">
                                @method('delete')
                                @csrf
                                <button class="btn btn-danger"><i class="fas fa-trash mr-1"></i> Delete Employee</button>
                            </form>
                        @endrole
                        <a href="{{ url('employees') }}" class="btn btn-warning ml-1">
                            <i class="fas fa-undo mr-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="bs-stepper">
                        <div class="bs-stepper-header" role="tablist">
                            <div class="step" data-target="#personal-detail-pane">
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
                            <div class="step{{ $units->isEmpty() ? ' step-empty' : '' }}" data-target="#unit-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="unit-pane"
                                    id="unit-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-truck"></i></span>
                                    <span class="bs-stepper-label">Units</span>
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
                            <div id="personal-detail-pane" class="content" role="tabpanel"
                                aria-labelledby="personal-detail-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 class="mb-0">Personal Detail</h5>
                                    <button class="btn btn-primary" data-toggle="modal"
                                        data-target="#modal-employee-{{ $employee->id }}">
                                        <i class="fas fa-pen-square mr-1"></i> Edit Personal
                                    </button>
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
                                            <dt class="col-sm-4">Village</dt>
                                            <dd class="col-sm-8">{{ $employee->village ?? '-' }}</dd>
                                            <dt class="col-sm-4">Ward</dt>
                                            <dd class="col-sm-8">{{ $employee->ward ?? '-' }}</dd>
                                            <dt class="col-sm-4">District</dt>
                                            <dd class="col-sm-8">{{ $employee->district ?? '-' }}</dd>
                                            <dt class="col-sm-4">City</dt>
                                            <dd class="col-sm-8">{{ $employee->city ?? '-' }}</dd>
                                            <dt class="col-sm-4">Phone</dt>
                                            <dd class="col-sm-8">{{ $employee->phone ?? '-' }}</dd>
                                            <dt class="col-sm-4">Email</dt>
                                            <dd class="col-sm-8">{{ $employee->email ?? '-' }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>

                            <div id="administration-pane" class="content" role="tabpanel"
                                aria-labelledby="administration-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 class="mb-0">Employment History</h5>
                                    <div>
                                        <button class="btn btn-primary" data-toggle="modal"
                                            data-target="#modal-administration" title="Add Administration Data">
                                            <i class="fas fa-plus mr-1"></i> Add Employment
                                        </button>
                                        @if ($administrations->isNotEmpty())
                                            <form action="{{ url('administrations/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all administration records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-danger" title="Delete All Administration Records">
                                                    <i class="fas fa-trash mr-1"></i> Delete All
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table-modern">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">NIK</th>
                                                <th>POH</th>
                                                <th>DOH</th>
                                                <th>Department</th>
                                                <th>Position</th>
                                                <th class="text-center">Project</th>
                                                <th>Class</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($administrations as $administration)
                                                <tr>
                                                    <td class="text-center">
                                                        @if ($administration->is_active == 1)
                                                            <span class="badge-status active">Active</span>
                                                        @else
                                                            <form
                                                                action="{{ url('administrations/changeStatus/' . $employee->id . '/' . $administration->id) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit"
                                                                    class="badge-status inactive">Inactive</button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $administration->nik }}</td>
                                                    <td>{{ $administration->poh }}</td>
                                                    <td>{{ $administration->doh ? date('d M Y', strtotime($administration->doh)) : '-' }}
                                                    </td>
                                                    <td>{{ $administration->department_name }}</td>
                                                    <td>{{ $administration->position_name }}</td>
                                                    <td>{{ $administration->project_code }}</td>
                                                    <td>{{ $administration->class }}</td>
                                                    <td class="action-buttons">
                                                        <button class="btn btn-primary btn-action" data-toggle="modal"
                                                            data-target="#modal-administration-{{ $administration->id }}">
                                                            <i class="fas fa-pen-square"></i>
                                                        </button>
                                                        <form
                                                            action="{{ url('administrations/' . $employee->id . '/' . $administration->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-danger btn-action">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9">
                                                        <div class="empty-state">
                                                            <i class="fas fa-exclamation-circle"></i>
                                                            <h6>No Data Available</h6>
                                                            <p>No administration records found for this employee</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="bank-pane" class="content" role="tabpanel" aria-labelledby="bank-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 class="mb-0">Bank Account</h5>
                                    <div>
                                        @if ($bank == null)
                                            <button class="btn btn-primary" data-toggle="modal"
                                                data-target="#modal-bank">
                                                <i class="fas fa-plus mr-1"></i> Add Bank
                                            </button>
                                        @else
                                            <button class="btn btn-primary" data-toggle="modal"
                                                data-target="#modal-bank-{{ $bank->id }}">
                                                <i class="fas fa-pen-square mr-1"></i> Edit Bank
                                            </button>
                                            <form action="{{ url('employeebanks/' . $employee->id . '/' . $bank->id) }}"
                                                method="post"
                                                onsubmit="return confirm('Are you sure want to delete this bank account data?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-danger" title="Delete Bank Account Data">
                                                    <i class="fas fa-trash mr-1"></i> Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                @if ($bank == null)
                                    <div class="text-center py-5">
                                        <img src="{{ asset('assets/dist/img/bank-empty.png') }}" alt="No Bank Data"
                                            class="img-fluid mb-3" style="max-height: 120px; opacity: 0.5;">
                                        <h6 class="text-muted">No bank account information available</h6>
                                        <p class="text-muted small">Click "Add Bank" button to register employee's bank
                                            account details</p>
                                    </div>
                                @else
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card card-primary card-outline">
                                                <div class="card-header">
                                                    <h3 class="card-title">
                                                        <i class="fas fa-university mr-2"></i>
                                                        Bank Information
                                                    </h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="info-box bg-light">
                                                        <span class="info-box-icon bg-primary"><i
                                                                class="fas fa-university"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Bank Name</span>
                                                            <span
                                                                class="info-box-number">{{ $bank->banks->bank_name ?? '-' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="info-box bg-light">
                                                        <span class="info-box-icon bg-primary"><i
                                                                class="fas fa-map-marker-alt"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Branch</span>
                                                            <span
                                                                class="info-box-number">{{ $bank->bank_account_branch ?? '-' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card card-primary card-outline">
                                                <div class="card-header">
                                                    <h3 class="card-title">
                                                        <i class="fas fa-user mr-2"></i>
                                                        Account Holder
                                                    </h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="info-box bg-light">
                                                        <span class="info-box-icon bg-primary"><i
                                                                class="fas fa-hashtag"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Account Number</span>
                                                            <span
                                                                class="info-box-number">{{ $bank->bank_account_no ?? '-' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="info-box bg-light">
                                                        <span class="info-box-icon bg-primary"><i
                                                                class="fas fa-user"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Account Name</span>
                                                            <span
                                                                class="info-box-number">{{ $bank->bank_account_name ?? '-' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div id="tax-pane" class="content" role="tabpanel" aria-labelledby="tax-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 class="mb-0">Tax Identification Number (NPWP)</h5>
                                    <div>
                                        @if ($tax == null)
                                            <button class="btn btn-primary" data-toggle="modal" data-target="#modal-tax">
                                                <i class="fas fa-plus mr-1"></i> Add Tax Info
                                            </button>
                                        @else
                                            <button class="btn btn-primary" data-toggle="modal"
                                                data-target="#modal-tax-{{ $tax->id }}">
                                                <i class="fas fa-pen-square mr-1"></i> Edit Tax Info
                                            </button>
                                            <form
                                                action="{{ url('taxidentifications/' . $employee->id . '/' . $tax->id) }}"
                                                method="post"
                                                onsubmit="return confirm('Are you sure want to delete this tax identification data?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-danger" title="Delete Tax Identification Data">
                                                    <i class="fas fa-trash mr-1"></i> Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                @if ($tax == null)
                                    <div class="text-center py-5">
                                        <img src="{{ asset('assets/dist/img/tax-empty.png') }}" alt="No Tax Data"
                                            class="img-fluid mb-3" style="max-height: 120px; opacity: 0.5;">
                                        <h6 class="text-muted">No tax identification information available</h6>
                                        <p class="text-muted small">Click "Add Tax Info" button to register employee's tax
                                            identification details</p>
                                    </div>
                                @else
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="card card-primary card-outline">
                                                <div class="card-header">
                                                    <h3 class="card-title">
                                                        <i class="fas fa-file-invoice-dollar mr-2"></i>
                                                        Tax Information
                                                    </h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="info-box bg-light">
                                                        <span class="info-box-icon bg-primary"><i
                                                                class="fas fa-file-invoice-dollar"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">NPWP Number</span>
                                                            <span class="info-box-number">{{ $tax->tax_no ?? '-' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="info-box bg-light">
                                                        <span class="info-box-icon bg-primary"><i
                                                                class="fas fa-calendar-alt"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Registration Date</span>
                                                            <span
                                                                class="info-box-number">{{ $tax ? ($tax->tax_valid_date ? date('d M Y', strtotime($tax->tax_valid_date)) : '-') : '-' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div id="insurance-pane" class="content" role="tabpanel"
                                aria-labelledby="insurance-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 class="mb-0">Health Insurance</h5>
                                    <div>
                                        <button class="btn btn-primary" data-toggle="modal"
                                            data-target="#modal-insurance" title="Add Insurance">
                                            <i class="fas fa-plus mr-1"></i> Add Insurance
                                        </button>
                                        @if ($insurances->isNotEmpty())
                                            <form action="{{ url('insurances/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all insurance records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-danger" title="Delete All Insurance Records">
                                                    <i class="fas fa-trash mr-1"></i> Delete All
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table-modern">
                                        <thead>
                                            <tr>
                                                <th>Insurance</th>
                                                <th>Insurance No</th>
                                                <th>Health Facility</th>
                                                <th>Remarks</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($insurances as $insurance)
                                                <tr>
                                                    <td>{{ $insurance->health_insurance_type == 'bpjskt' ? 'BPJS Ketenagakerjaan' : 'BPJS Kesehatan' }}
                                                    </td>
                                                    <td>{{ $insurance->health_insurance_no }}</td>
                                                    <td>{{ $insurance->health_facility }}</td>
                                                    <td>{{ $insurance->health_insurance_remarks }}</td>
                                                    <td class="action-buttons">
                                                        <button class="btn btn-primary btn-action" data-toggle="modal"
                                                            data-target="#modal-insurance-{{ $insurance->id }}">
                                                            <i class="fas fa-pen-square"></i>
                                                        </button>
                                                        <form
                                                            action="{{ url('insurances/' . $employee->id . '/' . $insurance->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-danger btn-action">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5">
                                                        <div class="empty-state">
                                                            <i class="fas fa-exclamation-circle"></i>
                                                            <h6>No Data Available</h6>
                                                            <p>No insurance records found for this employee</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="license-pane" class="content" role="tabpanel"
                                aria-labelledby="license-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 class="mb-0">Licenses</h5>
                                    <div>
                                        <button class="btn btn-primary" data-toggle="modal" data-target="#modal-license"
                                            title="Add License">
                                            <i class="fas fa-plus mr-1"></i> Add License
                                        </button>
                                        @if ($licenses->isNotEmpty())
                                            <form action="{{ url('licenses/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all license records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-danger" title="Delete All License Records">
                                                    <i class="fas fa-trash mr-1"></i> Delete All
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table-modern">
                                        <thead>
                                            <tr>
                                                <th>License Type</th>
                                                <th>License No</th>
                                                <th>Expiration Date</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($licenses as $license)
                                                <tr>
                                                    <td>{{ $license->driver_license_type }}</td>
                                                    <td>{{ $license->driver_license_no }}</td>
                                                    <td>{{ $license->driver_license_exp ? date('d M Y', strtotime($license->driver_license_exp)) : '-' }}
                                                    </td>
                                                    <td class="action-buttons">
                                                        <button class="btn btn-primary btn-action" data-toggle="modal"
                                                            data-target="#modal-license-{{ $license->id }}">
                                                            <i class="fas fa-pen-square"></i>
                                                        </button>
                                                        <form
                                                            action="{{ url('licenses/' . $employee->id . '/' . $license->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-danger btn-action">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4">
                                                        <div class="empty-state">
                                                            <i class="fas fa-exclamation-circle"></i>
                                                            <h6>No Data Available</h6>
                                                            <p>No license records found for this employee</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="family-pane" class="content" role="tabpanel" aria-labelledby="family-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 class="mb-0">Families</h5>
                                    <div>
                                        <button class="btn btn-primary" data-toggle="modal" data-target="#modal-family"
                                            title="Add Family">
                                            <i class="fas fa-plus mr-1"></i> Add Family
                                        </button>
                                        @if ($families->isNotEmpty())
                                            <form action="{{ url('families/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all family records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-danger" title="Delete All Family Records">
                                                    <i class="fas fa-trash mr-1"></i> Delete All
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table-modern">
                                        <thead>
                                            <tr>
                                                <th>Relationship</th>
                                                <th>Name</th>
                                                <th>Birth Place</th>
                                                <th>Birth Date</th>
                                                <th>Remarks</th>
                                                <th>BPJS Kesehatan</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($families as $family)
                                                <tr>
                                                    <td>{{ $family->family_relationship }}</td>
                                                    <td>{{ $family->family_name }}</td>
                                                    <td>{{ $family->family_birthplace }}</td>
                                                    <td>{{ $family->family_birthdate ? date('d M Y', strtotime($family->family_birthdate)) : '-' }}
                                                    </td>
                                                    <td>{{ $family->family_remarks }}</td>
                                                    <td>{{ $family->bpjsks_no }}</td>
                                                    <td class="action-buttons">
                                                        <button class="btn btn-primary btn-action" data-toggle="modal"
                                                            data-target="#modal-family-{{ $family->id }}">
                                                            <i class="fas fa-pen-square"></i>
                                                        </button>
                                                        <form
                                                            action="{{ url('families/' . $employee->id . '/' . $family->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-danger btn-action">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7">
                                                        <div class="empty-state">
                                                            <i class="fas fa-exclamation-circle"></i>
                                                            <h6>No Data Available</h6>
                                                            <p>No family records found for this employee</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="education-pane" class="content" role="tabpanel"
                                aria-labelledby="education-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 class="mb-0">Educations</h5>
                                    <div>
                                        <button class="btn btn-primary" data-toggle="modal"
                                            data-target="#modal-education" title="Add Education">
                                            <i class="fas fa-plus mr-1"></i> Add Education
                                        </button>
                                        @if ($educations->isNotEmpty())
                                            <form action="{{ url('educations/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all education records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-danger" title="Delete All Education Records">
                                                    <i class="fas fa-trash mr-1"></i> Delete All
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table-modern">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Address</th>
                                                <th>Year</th>
                                                <th>Remarks</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($educations as $education)
                                                <tr>
                                                    <td>{{ $education->education_name }}</td>
                                                    <td>{{ $education->education_address }}</td>
                                                    <td>{{ $education->education_year }}</td>
                                                    <td>{{ $education->education_remarks }}</td>
                                                    <td class="action-buttons">
                                                        <button class="btn btn-primary btn-action" data-toggle="modal"
                                                            data-target="#modal-education-{{ $education->id }}">
                                                            <i class="fas fa-pen-square"></i>
                                                        </button>
                                                        <form
                                                            action="{{ url('educations/' . $employee->id . '/' . $education->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-danger btn-action">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5">
                                                        <div class="empty-state">
                                                            <i class="fas fa-exclamation-circle"></i>
                                                            <h6>No Data Available</h6>
                                                            <p>No education records found for this employee</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="course-pane" class="content" role="tabpanel" aria-labelledby="course-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 class="mb-0">Courses</h5>
                                    <div>
                                        <button class="btn btn-primary" data-toggle="modal" data-target="#modal-course"
                                            title="Add Course">
                                            <i class="fas fa-plus mr-1"></i> Add Course
                                        </button>
                                        @if ($courses->isNotEmpty())
                                            <form action="{{ url('courses/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all course records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-danger" title="Delete All Course Records">
                                                    <i class="fas fa-trash mr-1"></i> Delete All
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table-modern">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Address</th>
                                                <th>Year</th>
                                                <th>Remarks</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($courses as $course)
                                                <tr>
                                                    <td>{{ $course->course_name }}</td>
                                                    <td>{{ $course->course_address }}</td>
                                                    <td>{{ $course->course_year }}</td>
                                                    <td>{{ $course->course_remarks }}</td>
                                                    <td class="action-buttons">
                                                        <button class="btn btn-primary btn-action" data-toggle="modal"
                                                            data-target="#modal-course-{{ $course->id }}">
                                                            <i class="fas fa-pen-square"></i>
                                                        </button>
                                                        <form
                                                            action="{{ url('courses/' . $employee->id . '/' . $course->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-danger btn-action">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5">
                                                        <div class="empty-state">
                                                            <i class="fas fa-exclamation-circle"></i>
                                                            <h6>No Data Available</h6>
                                                            <p>No course records found for this employee</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="jobexp-pane" class="content" role="tabpanel" aria-labelledby="jobexp-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 class="mb-0">Job Experiences</h5>
                                    <div>
                                        <button class="btn btn-primary" data-toggle="modal" data-target="#modal-job"
                                            title="Add Job Experience">
                                            <i class="fas fa-plus mr-1"></i> Add Job
                                        </button>
                                        @if ($jobs->isNotEmpty())
                                            <form action="{{ url('jobexperiences/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all job experience records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-danger" title="Delete All Job Experience Records">
                                                    <i class="fas fa-trash mr-1"></i> Delete All
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table-modern">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Address</th>
                                                <th>Position</th>
                                                <th>Duration</th>
                                                <th>Quit Reason</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($jobs as $job)
                                                <tr>
                                                    <td>{{ $job->company_name }}</td>
                                                    <td>{{ $job->company_address }}</td>
                                                    <td>{{ $job->job_position }}</td>
                                                    <td>{{ $job->job_duration }}</td>
                                                    <td>{{ $job->quit_reason }}</td>
                                                    <td class="action-buttons">
                                                        <button class="btn btn-primary btn-action" data-toggle="modal"
                                                            data-target="#modal-job-{{ $job->id }}">
                                                            <i class="fas fa-pen-square"></i>
                                                        </button>
                                                        <form
                                                            action="{{ url('jobexperiences/' . $employee->id . '/' . $job->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-danger btn-action">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6">
                                                        <div class="empty-state">
                                                            <i class="fas fa-exclamation-circle"></i>
                                                            <h6>No Data Available</h6>
                                                            <p>No job experience records found for this employee</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="unit-pane" class="content" role="tabpanel" aria-labelledby="unit-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 class="mb-0">Operable Units</h5>
                                    <div>
                                        <button class="btn btn-primary" data-toggle="modal" data-target="#modal-unit"
                                            title="Add Operable Unit">
                                            <i class="fas fa-plus mr-1"></i> Add Unit
                                        </button>
                                        @if ($units->isNotEmpty())
                                            <form action="{{ url('operableunits/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all operable unit records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-danger" title="Delete All Operable Unit Records">
                                                    <i class="fas fa-trash mr-1"></i> Delete All
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table-modern">
                                        <thead>
                                            <tr>
                                                <th>Unit Name</th>
                                                <th>Unit Type / Class</th>
                                                <th>Remarks</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($units as $unit)
                                                <tr>
                                                    <td>{{ $unit->unit_name }}</td>
                                                    <td>{{ $unit->unit_type }}</td>
                                                    <td>{{ $unit->unit_remarks }}</td>
                                                    <td class="action-buttons">
                                                        <button class="btn btn-primary btn-action" data-toggle="modal"
                                                            data-target="#modal-unit-{{ $unit->id }}">
                                                            <i class="fas fa-pen-square"></i>
                                                        </button>
                                                        <form
                                                            action="{{ url('operableunits/' . $employee->id . '/' . $unit->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-danger btn-action">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4">
                                                        <div class="empty-state">
                                                            <i class="fas fa-exclamation-circle"></i>
                                                            <h6>No Data Available</h6>
                                                            <p>No unit records found for this employee</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="emergency-pane" class="content" role="tabpanel"
                                aria-labelledby="emergency-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 class="mb-0">Emergency Calls</h5>
                                    <div>
                                        <button class="btn btn-primary" data-toggle="modal"
                                            data-target="#modal-emergency" title="Add Emergency Call">
                                            <i class="fas fa-plus mr-1"></i> Add Contact
                                        </button>
                                        @if ($emergencies->isNotEmpty())
                                            <form action="{{ url('emrgcalls/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all emergency contact records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-danger" title="Delete All Emergency Records">
                                                    <i class="fas fa-trash mr-1"></i> Delete All
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table-modern">
                                        <thead>
                                            <tr>
                                                <th>Relationship</th>
                                                <th>Full Name</th>
                                                <th>Address</th>
                                                <th>Phone</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($emergencies as $emergency)
                                                <tr>
                                                    <td>{{ $emergency->emrg_call_relation }}</td>
                                                    <td>{{ $emergency->emrg_call_name }}</td>
                                                    <td>{{ $emergency->emrg_call_address }}</td>
                                                    <td>{{ $emergency->emrg_call_phone }}</td>
                                                    <td class="action-buttons">
                                                        <button class="btn btn-primary btn-action" data-toggle="modal"
                                                            data-target="#modal-emergency-{{ $emergency->id }}">
                                                            <i class="fas fa-pen-square"></i>
                                                        </button>
                                                        <form
                                                            action="{{ url('emrgcalls/' . $employee->id . '/' . $emergency->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-danger btn-action">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5">
                                                        <div class="empty-state">
                                                            <i class="fas fa-exclamation-circle"></i>
                                                            <h6>No Data Available</h6>
                                                            <p>No emergency contact records found for this employee</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="additional-pane" class="content" role="tabpanel"
                                aria-labelledby="additional-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 class="mb-0">Additional Data</h5>
                                    <div>
                                        @if ($additional == null)
                                            <button class="btn btn-primary" data-toggle="modal"
                                                data-target="#modal-additional">
                                                <i class="fas fa-plus mr-1"></i> Add Data
                                            </button>
                                        @else
                                            <button class="btn btn-primary" data-toggle="modal"
                                                data-target="#modal-additional-{{ $additional->id }}">
                                                <i class="fas fa-pen-square mr-1"></i> Edit Data
                                            </button>
                                            <form
                                                action="{{ url('additionaldatas/' . $employee->id . '/' . $additional->id) }}"
                                                method="post"
                                                onsubmit="return confirm('Are you sure want to delete this additional data?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-danger" title="Delete Additional Data">
                                                    <i class="fas fa-trash mr-1"></i> Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                @if ($additional == null)
                                    <div class="text-center py-5">
                                        <img src="{{ asset('assets/dist/img/additional-empty.png') }}"
                                            alt="No Additional Data" class="img-fluid mb-3"
                                            style="max-height: 120px; opacity: 0.5;">
                                        <h6 class="text-muted">No additional information available</h6>
                                        <p class="text-muted small">Click "Add Data" button to register employee's
                                            additional details</p>
                                    </div>
                                @else
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card card-primary card-outline">
                                                <div class="card-header">
                                                    <h3 class="card-title">
                                                        <i class="fas fa-tshirt mr-2"></i>
                                                        Clothing Information
                                                    </h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="info-box bg-light">
                                                                <span class="info-box-icon bg-primary"><i
                                                                        class="fas fa-tshirt"></i></span>
                                                                <div class="info-box-content">
                                                                    <span class="info-box-text">Cloth Size</span>
                                                                    <span
                                                                        class="info-box-number">{{ $additional->cloth_size ?? '-' }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="info-box bg-light">
                                                                <span class="info-box-icon bg-primary"><i
                                                                        class="fas fa-socks"></i></span>
                                                                <div class="info-box-content">
                                                                    <span class="info-box-text">Pants Size</span>
                                                                    <span
                                                                        class="info-box-number">{{ $additional->pants_size ?? '-' }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="info-box bg-light">
                                                                <span class="info-box-icon bg-primary"><i
                                                                        class="fas fa-shoe-prints"></i></span>
                                                                <div class="info-box-content">
                                                                    <span class="info-box-text">Shoes Size</span>
                                                                    <span
                                                                        class="info-box-number">{{ $additional->shoes_size ?? '-' }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="info-box bg-light">
                                                                <span class="info-box-icon bg-primary"><i
                                                                        class="fas fa-glasses"></i></span>
                                                                <div class="info-box-content">
                                                                    <span class="info-box-text">Glasses</span>
                                                                    <span
                                                                        class="info-box-number">{{ $additional->glasses ?? '-' }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card card-primary card-outline">
                                                <div class="card-header">
                                                    <h3 class="card-title">
                                                        <i class="fas fa-ruler-combined mr-2"></i>
                                                        Physical Information
                                                    </h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="info-box bg-light">
                                                                <span class="info-box-icon bg-primary"><i
                                                                        class="fas fa-ruler-vertical"></i></span>
                                                                <div class="info-box-content">
                                                                    <span class="info-box-text">Height</span>
                                                                    <span
                                                                        class="info-box-number">{{ $additional->height ?? '-' }}
                                                                        cm</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="info-box bg-light">
                                                                <span class="info-box-icon bg-primary"><i
                                                                        class="fas fa-weight"></i></span>
                                                                <div class="info-box-content">
                                                                    <span class="info-box-text">Weight</span>
                                                                    <span
                                                                        class="info-box-number">{{ $additional->weight ?? '-' }}
                                                                        kg</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div id="image-pane" class="content" role="tabpanel" aria-labelledby="image-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 class="mb-0">Employee Images</h5>
                                    <div>
                                        @if ($images->isNotEmpty())
                                            <a href="{{ url('employees/deleteImages/' . $employee->id) }}"
                                                class="btn btn-danger"
                                                onclick="return confirm('Are you sure you want to delete all images?');">
                                                <i class="fas fa-trash mr-1"></i> Delete All Images
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <div class="alert alert-info mb-3">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Upload employee images including ID cards, profile photos, and other relevant
                                    documents.
                                </div>

                                <form action="{{ url('employees/addImages/' . $employee->id) }}" method="POST"
                                    enctype="multipart/form-data" class="mb-4">
                                    @csrf
                                    <div class="form-group">
                                        <label for="images_upload" class="form-label">Upload Images</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="images_upload"
                                                name="filename[]" multiple required>
                                            <label class="custom-file-label" for="images_upload">Choose
                                                files...</label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload mr-1"></i> Upload
                                    </button>
                                </form>

                                <div class="card bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-lightbulb text-warning mr-2"></i>Image Guidelines
                                        </h6>
                                        <br>
                                        <ul class="mb-0 pl-3">
                                            <li>Supported formats: JPG, PNG. Maximum file size: 2MB.</li>
                                            <li>Profile photos should be clear and professional</li>
                                            <li>ID card images must be legible</li>
                                            <li>All uploads must be appropriate for workplace use</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    @forelse ($images as $image)
                                        <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                                            <div class="card h-100">
                                                <a href="{{ asset('images/' . $image->employee_id . '/' . $image->filename) }}"
                                                    data-toggle="lightbox" data-title="{{ $image->filename }}"
                                                    data-gallery="gallery">
                                                    <img src="{{ asset('images/' . $image->employee_id . '/' . $image->filename) }}"
                                                        class="card-img-top" alt="{{ $image->filename }}"
                                                        style="height: 200px; object-fit: cover;" />
                                                </a>
                                                <div class="card-body p-2">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        @if ($image->is_profile == 0)
                                                            <a href="{{ url('employees/setProfile/' . $employee->id . '/' . $image->id) }}"
                                                                class="btn btn-primary btn-sm"
                                                                title="Set Profile Picture">
                                                                <i class="fas fa-id-badge mr-1"></i> Set Profile
                                                            </a>
                                                        @else
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-check mr-1"></i> Profile Picture
                                                            </span>
                                                        @endif
                                                        <a href="{{ url('employees/deleteImage/' . $employee->id . '/' . $image->id) }}"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Are you sure you want to delete this image?');"
                                                            title="Delete Image">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center">
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-circle mr-2"></i>
                                                No images available. Please upload some images.
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
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

    @include('employee.modal-employee')
    @include('employee.modal-bank')
    @include('employee.modal-tax')
    @include('employee.modal-insurance')
    @include('employee.modal-family')
    @include('employee.modal-education')
    @include('employee.modal-course')
    @include('employee.modal-job')
    @include('employee.modal-unit')
    @include('employee.modal-license')
    @include('employee.modal-emergency')
    @include('employee.modal-additional')
    @include('employee.modal-administration')
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/ekko-lightbox/ekko-lightbox.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bs-stepper/css/bs-stepper.min.css') }}">
    <style>
        /* Critical CSS - Load First */
        .content {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out;
        }

        .content.loaded {
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

        .table-modern .action-buttons {
            white-space: nowrap;
            text-align: center;
        }

        .table-modern .action-buttons .btn {
            padding: 0.375rem 0.75rem;
            margin: 0 0.25rem;
            border-radius: 0.25rem;
            transition: all 0.2s ease;
        }

        .table-modern .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

        /* Action Buttons */
        .btn-action {
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            transition: all 0.2s ease;
            margin: 0 0.25rem;
        }

        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-action i {
            margin-right: 0.25rem;
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
            transform: translateZ(0);
            transition: opacity 0.3s ease-out;
            will-change: opacity;
        }

        .bs-stepper-content .content.active {
            opacity: 1;
        }

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
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}" defer></script>
    <script src="{{ asset('assets/plugins/ekko-lightbox/ekko-lightbox.min.js') }}" defer></script>
    <script src="{{ asset('assets/plugins/bs-stepper/js/bs-stepper.min.js') }}" defer></script>
    <script src="{{ asset('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}" defer></script>

    <script>
        // Add loading indicator to body
        document.body.insertAdjacentHTML('afterbegin',
            '<div class="page-loader"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>'
        );

        // Initialize variables
        var stepper;

        // DOM Ready handler with performance optimizations
        document.addEventListener('DOMContentLoaded', function() {
            // Hide loader when DOM is ready
            document.querySelector('.page-loader').classList.add('hidden');
            document.querySelector('.content').classList.add('loaded');

            // Initialize stepper with performance optimizations
            stepper = new Stepper(document.querySelector('.bs-stepper'), {
                linear: false,
                animation: true,
                selectors: {
                    steps: '.step',
                    trigger: '.step-trigger',
                    stepper: '.bs-stepper'
                }
            });

            // Handle hash-based navigation
            function handleHash() {
                const hash = window.location.hash.toLowerCase();
                if (hash) {
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
                        '#unit': 11,
                        '#emergency': 12,
                        '#additional': 13,
                        '#image': 14
                    };

                    if (stepMap.hasOwnProperty(hash)) {
                        stepper.to(stepMap[hash]);
                        // Scroll to top when hash changes
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                }
            }

            // Handle initial hash on page load
            handleHash();

            // Handle hash changes while on the page
            window.addEventListener('hashchange', function() {
                handleHash();
                // Scroll to top when hash changes
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Update hash and scroll position when clicking stepper buttons
            document.querySelectorAll('.step-trigger').forEach(trigger => {
                trigger.addEventListener('click', function() {
                    const paneId = this.getAttribute('aria-controls');
                    if (paneId) {
                        const hash = paneId.replace('-pane', '');
                        window.location.hash = hash;
                        // Always scroll to top when any step is clicked
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Ensure all panes are properly initialized
            document.querySelectorAll('.step').forEach(function(step) {
                step.addEventListener('click', function(e) {
                    let targetPane = this.getAttribute('data-target');
                    if (targetPane) {
                        document.querySelectorAll('.content').forEach(pane => pane.classList.remove(
                            'active'));
                        document.querySelector(targetPane).classList.add('active');
                    }
                });
            });

            document.querySelector('.bs-stepper').classList.add('initialized');

            // Optimize scroll handler
            let backToTop = document.getElementById('back-to-top');
            let ticking = false;

            window.addEventListener('scroll', function() {
                if (!ticking) {
                    window.requestAnimationFrame(function() {
                        if (window.pageYOffset > 100) {
                            backToTop.style.display = 'block';
                        } else {
                            backToTop.style.display = 'none';
                        }
                        ticking = false;
                    });
                    ticking = true;
                }
            });

            // Optimize back to top click
            backToTop.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Initialize lightbox with lazy loading
            document.querySelectorAll('[data-toggle="lightbox"]').forEach(function(el) {
                el.addEventListener('click', function(e) {
                    e.preventDefault();
                    $(this).ekkoLightbox({
                        alwaysShowClose: true,
                        loadingMessage: 'Loading...'
                    });
                });
            });

            // Initialize Select2 with performance optimizations
            function initializeSelect2(container) {
                $(container).find('.select2bs4').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: $(this).data('placeholder') || '-Select-',
                    allowClear: Boolean($(this).data('allow-clear')),
                    dropdownParent: $(this).closest('.modal')
                });
            }

            // Initialize components
            initializeSelect2(document);
            bsCustomFileInput.init();

            // Modal handlers with optimizations
            $('.modal').on('shown.bs.modal', function() {
                initializeSelect2(this);
                bsCustomFileInput.init();
            });

            // Department fetching optimization
            function fetchDepartment(position_id, targetElement) {
                if (!position_id) {
                    $(targetElement).val('').trigger('change');
                    return;
                }

                $.ajax({
                    url: "{{ route('employees.getDepartment') }}",
                    type: "GET",
                    data: {
                        position_id: position_id
                    },
                    dataType: 'json',
                    success: function(data) {
                        $(targetElement).val(data ? data.department_name : '').trigger('change');
                    },
                    error: function() {
                        $(targetElement).val('').trigger('change');
                    }
                });
            }

            // Position change handler
            $('#modal-administration .position_id').on('change', function() {
                fetchDepartment($(this).val(), '#modal-administration .department');
            });

            // Dynamic administration modals
            @foreach ($administrations as $administration)
                $('#modal-administration-{{ $administration->id }} .position_id{{ $administration->id }}').on(
                    'change',
                    function() {
                        fetchDepartment($(this).val(),
                            '#modal-administration-{{ $administration->id }} .department{{ $administration->id }}'
                        );
                    });

                $('#modal-administration-{{ $administration->id }}').on('shown.bs.modal', function() {
                    var initial_position_id = $(this).find('.position_id{{ $administration->id }}').val();
                    if (initial_position_id && !$(this).find('.department{{ $administration->id }}')
                        .val()) {
                        fetchDepartment(initial_position_id,
                            '#modal-administration-{{ $administration->id }} .department{{ $administration->id }}'
                        );
                    }
                });
            @endforeach
        });
    </script>
@endsection
