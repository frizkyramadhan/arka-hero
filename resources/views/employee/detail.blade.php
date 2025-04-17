@extends('layouts.main')

@section('styles')
    <!-- Preload critical assets -->
    <link rel="preload" href="{{ asset('assets/plugins/ekko-lightbox/ekko-lightbox.css') }}" as="style">
    <link rel="preload" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" as="style">
    <link rel="preload" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}"
        as="style">
    <link rel="preload" href="{{ asset('assets/plugins/bs-stepper/css/bs-stepper.min.css') }}" as="style">

    <!-- Load stylesheets -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/ekko-lightbox/ekko-lightbox.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/bs-stepper/css/bs-stepper.min.css') }}">

    <style>
        /* Optimized stepper styling */
        .bs-stepper-header {
            overflow-x: auto;
            white-space: nowrap;
            border-bottom: 1px solid #dee2e6;
            scrollbar-width: thin;
            -webkit-overflow-scrolling: touch;
        }

        .bs-stepper-header::-webkit-scrollbar {
            height: 5px;
        }

        .bs-stepper-header::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .bs-stepper-header::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .bs-stepper .step-trigger {
            padding: 8px 5px;
            color: #6c757d;
            background-color: transparent;
            transition: color 0.2s ease;
            will-change: color;
        }

        .bs-stepper .step-trigger:hover {
            background-color: transparent;
            color: #0056b3;
        }

        .bs-stepper .bs-stepper-circle {
            background-color: #adb5bd;
            width: 35px;
            height: 35px;
            line-height: 32px;
            font-size: 1rem;
            transition: background-color 0.2s ease;
            will-change: background-color;
        }

        .bs-stepper .step-trigger .bs-stepper-label {
            color: #495057;
            font-weight: 500;
            margin-left: 8px;
            font-size: small;
            transition: color 0.2s ease;
            will-change: color;
        }

        .bs-stepper .step.active .step-trigger .bs-stepper-circle {
            background-color: #007bff;
        }

        .bs-stepper .step.active .step-trigger .bs-stepper-label {
            color: #007bff;
            font-weight: 600;
        }

        /* Empty step styling */
        .bs-stepper-header .step.step-empty .step-trigger .bs-stepper-label {
            color: #adb5bd;
            font-style: italic;
        }

        .bs-stepper-header .step.step-empty .step-trigger .bs-stepper-circle {
            background-color: #e9ecef;
            border: 1px dashed #adb5bd;
        }

        .bs-stepper-header .step.step-empty.active .step-trigger .bs-stepper-label {
            color: #6c757d;
            font-weight: 500;
            font-style: italic;
        }

        .bs-stepper-header .step.step-empty.active .step-trigger .bs-stepper-circle {
            background-color: #ced4da;
            border: 1px dashed #6c757d;
        }

        /* Optimized content display */
        .bs-stepper-content .content {
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease-out;
            will-change: opacity;
        }

        .bs-stepper-content .content.active {
            display: block;
            opacity: 1;
        }

        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 25px;
            right: 25px;
            display: none;
            z-index: 1030;
            transition: opacity 0.3s ease;
            will-change: opacity;
        }

        /* Optimized responsive design */
        @media (max-width: 768px) {
            .bs-stepper .step-trigger {
                padding: 6px 4px;
            }

            .bs-stepper .bs-stepper-circle {
                width: 30px;
                height: 30px;
                line-height: 28px;
                font-size: 0.9rem;
            }

            .bs-stepper .step-trigger .bs-stepper-label {
                font-size: 11px;
            }
        }

        /* General Form Styling Enhancements */
        .card-body h5,
        .card-body h6 {
            color: #0056b3;
            /* Match primary color */
        }
    </style>
@endsection

@section('content')
    <!-- Loading indicator -->
    <div id="page-loading"></div>

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
                        <a href="{{ url('employees/print/' . $employee->id) }}" class="btn btn-primary" target="blank"><i
                                class="fas fa-print mr-1"></i> Print</a>
                        @role('administrator')
                            <form action="{{ url('employees/' . $employee->id) }}" method="post"
                                onsubmit="return confirm('This employee and all associated data will be deleted. Are you sure?')"
                                class="d-inline ml-1">
                                @method('delete')
                                @csrf
                                <button class="btn btn-danger"><i class="fas fa-trash mr-1"></i> Delete Employee</button>
                            </form>
                        @endrole
                        <a href="{{ url('employees') }}" class="btn btn-warning ml-1"><i class="fas fa-undo mr-1"></i>
                            Back</a>
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
                                    <span class="bs-stepper-label">Bank Account</span>
                                </button>
                            </div>
                            <div class="step{{ $tax == null ? ' step-empty' : '' }}" data-target="#tax-pane">
                                <button type="button" class="step-trigger" role="tab" aria-controls="tax-pane"
                                    id="tax-pane-trigger">
                                    <span class="bs-stepper-circle"><i class="fas fa-file-invoice-dollar"></i></span>
                                    <span class="bs-stepper-label">Tax Info</span>
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
                                    <button class="btn btn-sm btn-primary" data-toggle="modal"
                                        data-target="#modal-employee-{{ $employee->id }}"><i
                                            class="fas fa-pen-square mr-1"></i> Edit Personal</button>
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
                                        <button class="btn btn-sm btn-warning" data-toggle="modal"
                                            data-target="#modal-administration" title="Add Administration Data"><i
                                                class="fas fa-plus mr-1"></i> Add Record</button>
                                        @if ($administrations->isNotEmpty())
                                            <form action="{{ url('administrations/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all administration records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-sm btn-danger"
                                                    title="Delete All Administration Records"><i
                                                        class="fas fa-trash mr-1"></i> Delete All</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center">Status</th>
                                                <th>NIK</th>
                                                <th>POH</th>
                                                <th>DOH</th>
                                                <th>Department</th>
                                                <th>Position</th>
                                                <th>Project</th>
                                                <th>Class</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($administrations as $administration)
                                                <tr>
                                                    <td class="text-center">
                                                        @if ($administration->is_active == 1)
                                                            <span class="badge bg-success">Active</span>
                                                        @else
                                                            <form
                                                                action="{{ url('administrations/changeStatus/' . $employee->id . '/' . $administration->id) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit"
                                                                    class="btn badge bg-danger p-0 border-0">Inactive</button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                    <td>{{ $administration->nik }}</td>
                                                    <td>{{ $administration->poh }}</td>
                                                    <td>{{ $administration->doh ? date('d M Y', strtotime($administration->doh)) : '-' }}
                                                    </td>
                                                    <td>{{ $administration->department_name }}</td>
                                                    <td>{{ $administration->position_name }}</td>
                                                    <td>{{ $administration->project_code }}</td>
                                                    <td>{{ $administration->class }}</td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-primary" data-toggle="modal"
                                                            data-target="#modal-administration-{{ $administration->id }}"><i
                                                                class="fas fa-pen-square"></i></button>
                                                        <form
                                                            action="{{ url('administrations/' . $employee->id . '/' . $administration->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-sm btn-danger"><i
                                                                    class="fas fa-times"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center"><span
                                                            class="badge bg-warning">No Data Available</span></td>
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
                                            <button class="btn btn-sm btn-warning" data-toggle="modal"
                                                data-target="#modal-bank"><i class="fas fa-plus mr-1"></i> Add
                                                Bank</button>
                                        @else
                                            <button class="btn btn-sm btn-primary" data-toggle="modal"
                                                data-target="#modal-bank-{{ $bank->id }}"><i
                                                    class="fas fa-pen-square mr-1"></i> Edit Bank</button>
                                            <form action="{{ url('employeebanks/' . $employee->id . '/' . $bank->id) }}"
                                                method="post"
                                                onsubmit="return confirm('Are you sure want to delete this bank account data?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-sm btn-danger" title="Delete Bank Account Data"><i
                                                        class="fas fa-trash mr-1"></i> Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <dl class="row">
                                    <dt class="col-sm-3">Bank</dt>
                                    <dd class="col-sm-9">{{ $bank->banks->bank_name ?? '-' }}</dd>
                                    <dt class="col-sm-3">Account No.</dt>
                                    <dd class="col-sm-9">{{ $bank->bank_account_no ?? '-' }}</dd>
                                    <dt class="col-sm-3">Account Name</dt>
                                    <dd class="col-sm-9">{{ $bank->bank_account_name ?? '-' }}</dd>
                                    <dt class="col-sm-3">Branch</dt>
                                    <dd class="col-sm-9">{{ $bank->bank_account_branch ?? '-' }}</dd>
                                </dl>
                            </div>

                            <div id="tax-pane" class="content" role="tabpanel" aria-labelledby="tax-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 class="mb-0">Tax Identification Number (NPWP)</h5>
                                    <div>
                                        @if ($tax == null)
                                            <button class="btn btn-sm btn-warning" data-toggle="modal"
                                                data-target="#modal-tax"><i class="fas fa-plus mr-1"></i> Add Tax
                                                Info</button>
                                        @else
                                            <button class="btn btn-sm btn-primary" data-toggle="modal"
                                                data-target="#modal-tax-{{ $tax->id }}"><i
                                                    class="fas fa-pen-square mr-1"></i> Edit Tax Info</button>
                                            <form
                                                action="{{ url('taxidentifications/' . $employee->id . '/' . $tax->id) }}"
                                                method="post"
                                                onsubmit="return confirm('Are you sure want to delete this tax identification data?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-sm btn-danger"
                                                    title="Delete Tax Identification Data"><i
                                                        class="fas fa-trash mr-1"></i>
                                                    Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <dl class="row">
                                    <dt class="col-sm-3">Tax Identification No.</dt>
                                    <dd class="col-sm-9">{{ $tax->tax_no ?? '-' }}</dd>
                                    <dt class="col-sm-3">Registration Date</dt>
                                    <dd class="col-sm-9">
                                        {{ $tax ? ($tax->tax_valid_date ? date('d M Y', strtotime($tax->tax_valid_date)) : '-') : '-' }}
                                    </dd>
                                </dl>
                            </div>

                            <div id="insurance-pane" class="content" role="tabpanel"
                                aria-labelledby="insurance-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 class="mb-0">Health Insurance</h5>
                                    <div>
                                        <button class="btn btn-sm btn-warning" data-toggle="modal"
                                            data-target="#modal-insurance" title="Add Insurance"><i
                                                class="fas fa-plus mr-1"></i> Add Insurance</button>
                                        @if ($insurances->isNotEmpty())
                                            <form action="{{ url('insurances/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all insurance records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-sm btn-danger"
                                                    title="Delete All Insurance Records"><i class="fas fa-trash mr-1"></i>
                                                    Delete All</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Insurance</th>
                                                <th>Insurance No</th>
                                                <th>Health Facility</th>
                                                <th>Remarks</th>
                                                <th>Action</th>
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
                                                    <td>
                                                        <button class="btn btn-xs btn-primary" data-toggle="modal"
                                                            data-target="#modal-insurance-{{ $insurance->id }}"><i
                                                                class="fas fa-pen-square"></i></button>
                                                        <form
                                                            action="{{ url('insurances/' . $employee->id . '/' . $insurance->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-xs btn-danger"><i
                                                                    class="fas fa-times"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center"><span
                                                            class="badge bg-warning">No Data Available</span></td>
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
                                        <button class="btn btn-sm btn-warning" data-toggle="modal"
                                            data-target="#modal-license" title="Add License"><i
                                                class="fas fa-plus mr-1"></i>
                                            Add License</button>
                                        @if ($licenses->isNotEmpty())
                                            <form action="{{ url('licenses/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all license records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-sm btn-danger"
                                                    title="Delete All License Records"><i class="fas fa-trash mr-1"></i>
                                                    Delete All</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>License Type</th>
                                                <th>License No</th>
                                                <th>Expiration Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($licenses as $license)
                                                <tr>
                                                    <td>{{ $license->driver_license_type }}</td>
                                                    <td>{{ $license->driver_license_no }}</td>
                                                    <td>{{ $license->driver_license_exp ? date('d M Y', strtotime($license->driver_license_exp)) : '-' }}
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-xs btn-primary" data-toggle="modal"
                                                            data-target="#modal-license-{{ $license->id }}"><i
                                                                class="fas fa-pen-square"></i></button>
                                                        <form
                                                            action="{{ url('licenses/' . $employee->id . '/' . $license->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-xs btn-danger"><i
                                                                    class="fas fa-times"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center"><span
                                                            class="badge bg-warning">No Data Available</span></td>
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
                                        <button class="btn btn-sm btn-warning" data-toggle="modal"
                                            data-target="#modal-family" title="Add Family"><i
                                                class="fas fa-plus mr-1"></i>
                                            Add Family</button>
                                        @if ($families->isNotEmpty())
                                            <form action="{{ url('families/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all family records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-sm btn-danger" title="Delete All Family Records"><i
                                                        class="fas fa-trash mr-1"></i> Delete All</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Relationship</th>
                                                <th>Name</th>
                                                <th>Birth Place</th>
                                                <th>Birth Date</th>
                                                <th>Remarks</th>
                                                <th>BPJS Kesehatan</th>
                                                <th>Action</th>
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
                                                    <td>
                                                        <button class="btn btn-xs btn-primary" data-toggle="modal"
                                                            data-target="#modal-family-{{ $family->id }}"><i
                                                                class="fas fa-pen-square"></i></button>
                                                        <form
                                                            action="{{ url('families/' . $employee->id . '/' . $family->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-xs btn-danger"><i
                                                                    class="fas fa-times"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center"><span
                                                            class="badge bg-warning">No Data Available</span></td>
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
                                        <button class="btn btn-sm btn-warning" data-toggle="modal"
                                            data-target="#modal-education" title="Add Education"><i
                                                class="fas fa-plus mr-1"></i> Add Education</button>
                                        @if ($educations->isNotEmpty())
                                            <form action="{{ url('educations/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all education records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-sm btn-danger"
                                                    title="Delete All Education Records"><i class="fas fa-trash mr-1"></i>
                                                    Delete All</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Address</th>
                                                <th>Year</th>
                                                <th>Remarks</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($educations as $education)
                                                <tr>
                                                    <td>{{ $education->education_name }}</td>
                                                    <td>{{ $education->education_address }}</td>
                                                    <td>{{ $education->education_year }}</td>
                                                    <td>{{ $education->education_remarks }}</td>
                                                    <td>
                                                        <button class="btn btn-xs btn-primary" data-toggle="modal"
                                                            data-target="#modal-education-{{ $education->id }}"><i
                                                                class="fas fa-pen-square"></i></button>
                                                        <form
                                                            action="{{ url('educations/' . $employee->id . '/' . $education->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-xs btn-danger"><i
                                                                    class="fas fa-times"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center"><span
                                                            class="badge bg-warning">No Data Available</span></td>
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
                                        <button class="btn btn-sm btn-warning" data-toggle="modal"
                                            data-target="#modal-course" title="Add Course"><i
                                                class="fas fa-plus mr-1"></i>
                                            Add Course</button>
                                        @if ($courses->isNotEmpty())
                                            <form action="{{ url('courses/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all course records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-sm btn-danger" title="Delete All Course Records"><i
                                                        class="fas fa-trash mr-1"></i> Delete All</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Address</th>
                                                <th>Year</th>
                                                <th>Remarks</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($courses as $course)
                                                <tr>
                                                    <td>{{ $course->course_name }}</td>
                                                    <td>{{ $course->course_address }}</td>
                                                    <td>{{ $course->course_year }}</td>
                                                    <td>{{ $course->course_remarks }}</td>
                                                    <td>
                                                        <button class="btn btn-xs btn-primary" data-toggle="modal"
                                                            data-target="#modal-course-{{ $course->id }}"><i
                                                                class="fas fa-pen-square"></i></button>
                                                        <form
                                                            action="{{ url('courses/' . $employee->id . '/' . $course->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-xs btn-danger"><i
                                                                    class="fas fa-times"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center"><span
                                                            class="badge bg-warning">No Data Available</span></td>
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
                                        <button class="btn btn-sm btn-warning" data-toggle="modal"
                                            data-target="#modal-job" title="Add Job Experience"><i
                                                class="fas fa-plus mr-1"></i> Add Job</button>
                                        @if ($jobs->isNotEmpty())
                                            <form action="{{ url('jobexperiences/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all job experience records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-sm btn-danger"
                                                    title="Delete All Job Experience Records"><i
                                                        class="fas fa-trash mr-1"></i> Delete All</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Address</th>
                                                <th>Position</th>
                                                <th>Duration</th>
                                                <th>Quit Reason</th>
                                                <th>Action</th>
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
                                                    <td>
                                                        <button class="btn btn-xs btn-primary" data-toggle="modal"
                                                            data-target="#modal-job-{{ $job->id }}"><i
                                                                class="fas fa-pen-square"></i></button>
                                                        <form
                                                            action="{{ url('jobexperiences/' . $employee->id . '/' . $job->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-xs btn-danger"><i
                                                                    class="fas fa-times"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center"><span
                                                            class="badge bg-warning">No Data Available</span></td>
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
                                        <button class="btn btn-sm btn-warning" data-toggle="modal"
                                            data-target="#modal-unit" title="Add Operable Unit"><i
                                                class="fas fa-plus mr-1"></i> Add Unit</button>
                                        @if ($units->isNotEmpty())
                                            <form action="{{ url('operableunits/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all operable unit records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-sm btn-danger"
                                                    title="Delete All Operable Unit Records"><i
                                                        class="fas fa-trash mr-1"></i>
                                                    Delete All</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Unit Name</th>
                                                <th>Unit Type / Class</th>
                                                <th>Remarks</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($units as $unit)
                                                <tr>
                                                    <td>{{ $unit->unit_name }}</td>
                                                    <td>{{ $unit->unit_type }}</td>
                                                    <td>{{ $unit->unit_remarks }}</td>
                                                    <td>
                                                        <button class="btn btn-xs btn-primary" data-toggle="modal"
                                                            data-target="#modal-unit-{{ $unit->id }}"><i
                                                                class="fas fa-pen-square"></i></button>
                                                        <form
                                                            action="{{ url('operableunits/' . $employee->id . '/' . $unit->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-xs btn-danger"><i
                                                                    class="fas fa-times"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center"><span
                                                            class="badge bg-warning">No Data Available</span></td>
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
                                        <button class="btn btn-sm btn-warning" data-toggle="modal"
                                            data-target="#modal-emergency" title="Add Emergency Call"><i
                                                class="fas fa-plus mr-1"></i> Add Contact</button>
                                        @if ($emergencies->isNotEmpty())
                                            <form action="{{ url('emrgcalls/' . $employee->id) }}" method="post"
                                                onsubmit="return confirm('Are you sure want to delete all emergency contact records?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-sm btn-danger"
                                                    title="Delete All Emergency Records"><i class="fas fa-trash mr-1"></i>
                                                    Delete All</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Relationship</th>
                                                <th>Full Name</th>
                                                <th>Address</th>
                                                <th>Phone</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($emergencies as $emergency)
                                                <tr>
                                                    <td>{{ $emergency->emrg_call_relation }}</td>
                                                    <td>{{ $emergency->emrg_call_name }}</td>
                                                    <td>{{ $emergency->emrg_call_address }}</td>
                                                    <td>{{ $emergency->emrg_call_phone }}</td>
                                                    <td>
                                                        <button class="btn btn-xs btn-primary" data-toggle="modal"
                                                            data-target="#modal-emergency-{{ $emergency->id }}"><i
                                                                class="fas fa-pen-square"></i></button>
                                                        <form
                                                            action="{{ url('emrgcalls/' . $employee->id . '/' . $emergency->id) }}"
                                                            method="post"
                                                            onsubmit="return confirm('Are you sure want to delete this record?')"
                                                            class="d-inline">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="btn btn-xs btn-danger"><i
                                                                    class="fas fa-times"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center"><span
                                                            class="badge bg-warning">No Data Available</span></td>
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
                                            <button class="btn btn-sm btn-warning" data-toggle="modal"
                                                data-target="#modal-additional"><i class="fas fa-plus mr-1"></i> Add
                                                Data</button>
                                        @else
                                            <button class="btn btn-sm btn-primary" data-toggle="modal"
                                                data-target="#modal-additional-{{ $additional->id }}"><i
                                                    class="fas fa-pen-square mr-1"></i> Edit Data</button>
                                            <form
                                                action="{{ url('additionaldatas/' . $employee->id . '/' . $additional->id) }}"
                                                method="post"
                                                onsubmit="return confirm('Are you sure want to delete this additional data?')"
                                                class="d-inline">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-sm btn-danger" title="Delete Additional Data"><i
                                                        class="fas fa-trash mr-1"></i> Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <dl class="row">
                                    <dt class="col-sm-3">Cloth Size</dt>
                                    <dd class="col-sm-9">{{ $additional->cloth_size ?? '-' }}</dd>
                                    <dt class="col-sm-3">Pants Size</dt>
                                    <dd class="col-sm-9">{{ $additional->pants_size ?? '-' }}</dd>
                                    <dt class="col-sm-3">Shoes Size</dt>
                                    <dd class="col-sm-9">{{ $additional->shoes_size ?? '-' }}</dd>
                                    <dt class="col-sm-3">Height</dt>
                                    <dd class="col-sm-9">{{ $additional->height ?? '-' }} cm</dd>
                                    <dt class="col-sm-3">Weight</dt>
                                    <dd class="col-sm-9">{{ $additional->weight ?? '-' }} kg</dd>
                                    <dt class="col-sm-3">Glasses</dt>
                                    <dd class="col-sm-9">{{ $additional->glasses ?? '-' }}</dd>
                                </dl>
                            </div>

                            <div id="image-pane" class="content" role="tabpanel" aria-labelledby="image-pane-trigger">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h5 class="mb-0">Images</h5>
                                    <div>
                                        @if ($images->isNotEmpty())
                                            <a href="{{ url('employees/deleteImages/' . $employee->id) }}"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure you want to delete all images?');"><i
                                                    class="fas fa-trash mr-1"></i> Delete All Images</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="container">
                                    <div class="row justify-content-between mb-3">
                                        <div class="col-md-6">
                                            <form class="form-inline"
                                                action="{{ url('employees/addImages/' . $employee->id) }}"
                                                method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="form-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input"
                                                            id="images_upload" name="filename[]" multiple required>
                                                        <label class="custom-file-label" for="images_upload">Choose
                                                            files...</label>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-sm btn-primary ml-2">Upload</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    @forelse ($images as $image)
                                        <div class="col-sm-6 col-md-4 col-lg-3 mb-3 text-center">
                                            <a href="{{ asset('images/' . $image->employee_id . '/' . $image->filename) }}"
                                                data-toggle="lightbox" data-title="{{ $image->filename }}"
                                                data-gallery="gallery">
                                                <img src="{{ asset('images/' . $image->employee_id . '/' . $image->filename) }}"
                                                    class="img-fluid img-thumbnail mb-2" alt="{{ $image->filename }}"
                                                    style="max-height: 150px; object-fit: cover;" />
                                            </a>
                                            <div>
                                                @if ($image->is_profile == 0)
                                                    <a href="{{ url('employees/setProfile/' . $employee->id . '/' . $image->id) }}"
                                                        class="btn btn-xs btn-primary" title="Set Profile Picture"><i
                                                            class="fas fa-id-badge"></i> Set Profile</a>
                                                @else
                                                    <span class="badge badge-success"><i class="fas fa-check"></i>
                                                        Profile</span>
                                                @endif
                                                <a href="{{ url('employees/deleteImage/' . $employee->id . '/' . $image->id) }}"
                                                    class="btn btn-xs btn-danger ml-1"
                                                    onclick="return confirm('Are you sure you want to delete this image?');"
                                                    title="Delete Image"><i class="fas fa-trash"></i></a>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center">
                                            <span class="badge bg-warning">No Images Available</span>
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

    {{-- modal section --}}
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

@section('scripts')
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Ekko Lightbox -->
    <script src="{{ asset('assets/plugins/ekko-lightbox/ekko-lightbox.min.js') }}"></script>
    <!-- BS Stepper -->
    <script src="{{ asset('assets/plugins/bs-stepper/js/bs-stepper.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>

    <script>
        // Hide loading indicator when page is fully loaded
        $(window).on('load', function() {
            $('#page-loading').fadeOut(300);
        });

        // Make sure DOM is ready before initializing components
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize BS Stepper after DOM is fully loaded
            setTimeout(function() {
                try {
                    window.stepper = new Stepper(document.querySelector('.bs-stepper'), {
                        linear: false,
                        animation: true
                    });
                } catch (e) {
                    console.error('Stepper initialization error:', e);
                }

                // Initialize other components
                initializeComponents();
            }, 100);
        });

        function initializeComponents() {
            // Lightbox
            $(document).on('click', '[data-toggle="lightbox"]', function(event) {
                event.preventDefault();
                $(this).ekkoLightbox({
                    alwaysShowClose: true
                });
            });

            // Initialize Select2 for main page
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder') || 'Select an option';
                },
                allowClear: true
            });

            // Initialize for modals
            $('.modal').on('shown.bs.modal', function() {
                $(this).find('.select2bs4').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    dropdownParent: $(this),
                    placeholder: 'Select an option'
                });

                bsCustomFileInput.init();
            });

            // Initialize file input
            bsCustomFileInput.init();

            // Back to top button
            $(window).scroll(function() {
                if ($(this).scrollTop() > 100) {
                    $('#back-to-top').fadeIn();
                } else {
                    $('#back-to-top').fadeOut();
                }
            });

            $('#back-to-top').click(function() {
                $('html, body').animate({
                    scrollTop: 0
                }, 600);
                return false;
            });

            // Department fetch function
            function fetchDepartment(position_id, targetElement) {
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
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Error fetching department: ", textStatus, errorThrown);
                    }
                });
            }

            // Position change events
            $('#modal-administration').find('.position_id').on('change', function() {
                var position_id = $(this).val();
                if (position_id) {
                    fetchDepartment(position_id, '#modal-administration .department');
                }
            });

            @foreach ($administrations as $administration)
                $('#modal-administration-{{ $administration->id }}').find('.position_id{{ $administration->id }}').on(
                    'change',
                    function() {
                        var position_id = $(this).val();
                        if (position_id) {
                            fetchDepartment(position_id,
                                '#modal-administration-{{ $administration->id }} .department{{ $administration->id }}'
                            );
                        }
                    });

                $('#modal-administration-{{ $administration->id }}').on('shown.bs.modal', function() {
                    var initial_position_id = $(this).find('.position_id{{ $administration->id }}').val();
                    if (initial_position_id && !$(this).find('.department{{ $administration->id }}').val()) {
                        fetchDepartment(initial_position_id,
                            '#modal-administration-{{ $administration->id }} .department{{ $administration->id }}'
                        );
                    }
                });
            @endforeach
        }
    </script>
@endsection
