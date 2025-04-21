@extends('layouts.main')
@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Employee</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('employees') }}">Employees</a></li>
                        <li class="breadcrumb-item active">{{ $subtitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form method="POST" action="{{ url('employees') }}" enctype="multipart/form-data" id="employeeForm">
                @csrf
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-plus mr-1"></i>
                            <strong>{{ $subtitle }}</strong>
                        </h3>
                        {{-- Placeholder for final Save Button if needed at top --}}
                        <div class="card-tools">
                            <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Save</button>
                            <a href="{{ url('employees') }}" class="btn btn-warning ml-2"><i class="fas fa-undo mr-1"></i>
                                Back</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <!-- Stepper Navigation -->
                        <div class="bs-stepper">
                            <div class="bs-stepper-header" role="tablist">
                                <div class="step" data-target="#personal-detail-part">
                                    <button type="button" class="step-trigger" role="tab"
                                        aria-controls="personal-detail-part" id="personal-detail-part-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-id-card"></i></span>
                                        <span class="bs-stepper-label">Personal</span>
                                    </button>
                                </div>

                                <div class="step" data-target="#administration-part">
                                    <button type="button" class="step-trigger" role="tab"
                                        aria-controls="administration-part" id="administration-part-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-briefcase"></i></span>
                                        <span class="bs-stepper-label">Employment</span>
                                    </button>
                                </div>

                                <div class="step" data-target="#bank-part">
                                    <button type="button" class="step-trigger" role="tab" aria-controls="bank-part"
                                        id="bank-part-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-money-check-alt"></i></span>
                                        <span class="bs-stepper-label">&nbsp;&nbsp;Bank&nbsp;&nbsp;</span>
                                    </button>
                                </div>

                                <div class="step" data-target="#tax-part">
                                    <button type="button" class="step-trigger" role="tab" aria-controls="tax-part"
                                        id="tax-part-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-file-invoice-dollar"></i></span>
                                        <span class="bs-stepper-label">&nbsp;&nbsp;Tax&nbsp;&nbsp;</span>
                                    </button>
                                </div>

                                <div class="step" data-target="#insurance-part">
                                    <button type="button" class="step-trigger" role="tab"
                                        aria-controls="insurance-part" id="insurance-part-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-heartbeat"></i></span>
                                        <span class="bs-stepper-label">Insurances</span>
                                    </button>
                                </div>

                                <div class="step" data-target="#license-part">
                                    <button type="button" class="step-trigger" role="tab" aria-controls="license-part"
                                        id="license-part-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-id-badge"></i></span>
                                        <span class="bs-stepper-label">Licenses</span>
                                    </button>
                                </div>

                                <div class="step" data-target="#family-part">
                                    <button type="button" class="step-trigger" role="tab" aria-controls="family-part"
                                        id="family-part-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-users"></i></span>
                                        <span class="bs-stepper-label">Families</span>
                                    </button>
                                </div>

                                <div class="step" data-target="#education-part">
                                    <button type="button" class="step-trigger" role="tab"
                                        aria-controls="education-part" id="education-part-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-graduation-cap"></i></span>
                                        <span class="bs-stepper-label">Educations</span>
                                    </button>
                                </div>

                                <div class="step" data-target="#course-part">
                                    <button type="button" class="step-trigger" role="tab"
                                        aria-controls="course-part" id="course-part-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-certificate"></i></span>
                                        <span class="bs-stepper-label">Courses</span>
                                    </button>
                                </div>

                                <div class="step" data-target="#jobexp-part">
                                    <button type="button" class="step-trigger" role="tab"
                                        aria-controls="jobexp-part" id="jobexp-part-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-history"></i></span>
                                        <span class="bs-stepper-label">Experiences</span>
                                    </button>
                                </div>

                                <div class="step" data-target="#unit-part">
                                    <button type="button" class="step-trigger" role="tab" aria-controls="unit-part"
                                        id="unit-part-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-truck"></i></span>
                                        <span class="bs-stepper-label">Units</span>
                                    </button>
                                </div>

                                <div class="step" data-target="#emergency-part">
                                    <button type="button" class="step-trigger" role="tab"
                                        aria-controls="emergency-part" id="emergency-part-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-phone-alt"></i></span>
                                        <span class="bs-stepper-label">Emergencies</span>
                                    </button>
                                </div>

                                <div class="step" data-target="#additional-part">
                                    <button type="button" class="step-trigger" role="tab"
                                        aria-controls="additional-part" id="additional-part-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-info-circle"></i></span>
                                        <span class="bs-stepper-label">Additional</span>
                                    </button>
                                </div>

                                <div class="step" data-target="#image-part">
                                    <button type="button" class="step-trigger" role="tab"
                                        aria-controls="image-part" id="image-part-trigger">
                                        <span class="bs-stepper-circle"><i class="fas fa-images"></i></span>
                                        <span class="bs-stepper-label">Images</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Stepper Content -->
                            <div class="bs-stepper-content p-3"> <!-- Restore padding -->
                                <!-- Personal Detail Part -->
                                <div id="personal-detail-part" class="content" role="tabpanel"
                                    aria-labelledby="personal-detail-part-trigger">
                                    {{-- START Removing inner card div --}}
                                    <h5 class="mb-3 border-bottom pb-2">Personal Information</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="fullname" class="form-label required-field">Full
                                                    Name</label>
                                                <input type="text" value="{{ old('fullname') }}"
                                                    class="form-control @error('fullname') is-invalid @enderror"
                                                    id="fullname" name="fullname" autofocus="true"
                                                    placeholder="Enter full name">
                                                @if ($errors->any('fullname'))
                                                    <span class="text-danger">{{ $errors->first('fullname') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="identity_card" class="form-label required-field">Identity
                                                    Card</label>
                                                <input type="text" value="{{ old('identity_card') }}"
                                                    class="form-control @error('identity_card') is-invalid @enderror"
                                                    id="identity_card" name="identity_card"
                                                    placeholder="Enter KTP number">
                                                @if ($errors->any('identity_card'))
                                                    <span class="text-danger">{{ $errors->first('identity_card') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="nationality" class="form-label">Nationality</label>
                                                <input type="text" value="{{ old('nationality', 'Indonesia') }}"
                                                    class="form-control @error('nationality') is-invalid @enderror"
                                                    id="nationality" name="nationality" placeholder="Enter nationality">
                                                @if ($errors->any('nationality'))
                                                    <span class="text-danger">{{ $errors->first('nationality') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="mt-2 mb-3">Birth Information</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="emp_pob" class="form-label required-field">Place of
                                                    Birth</label>
                                                <input type="text" value="{{ old('emp_pob') }}"
                                                    class="form-control @error('emp_pob') is-invalid @enderror"
                                                    id="emp_pob" name="emp_pob" placeholder="Enter birth place">
                                                @if ($errors->any('emp_pob'))
                                                    <span class="text-danger">{{ $errors->first('emp_pob') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="emp_dob" class="form-label required-field">Date of
                                                    Birth</label>
                                                <input type="date" value="{{ old('emp_dob') }}"
                                                    class="form-control @error('emp_dob') is-invalid @enderror"
                                                    id="emp_dob" name="emp_dob">
                                                @if ($errors->any('emp_dob'))
                                                    <span class="text-danger">{{ $errors->first('emp_dob') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="blood_type" class="form-label">Blood Type</label>
                                                <select
                                                    class="form-control select2bs4 @error('blood_type') is-invalid @enderror"
                                                    name="blood_type" id="blood_type">
                                                    <option value="">Select blood type</option>
                                                    <option value="A"
                                                        {{ old('blood_type') == 'A' ? 'selected' : '' }}>A</option>
                                                    <option value="B"
                                                        {{ old('blood_type') == 'B' ? 'selected' : '' }}>B</option>
                                                    <option value="AB"
                                                        {{ old('blood_type') == 'AB' ? 'selected' : '' }}>AB</option>
                                                    <option value="O"
                                                        {{ old('blood_type') == 'O' ? 'selected' : '' }}>O</option>
                                                </select>
                                                @if ($errors->any('blood_type'))
                                                    <span class="text-danger">{{ $errors->first('blood_type') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="mt-2 mb-3">Personal Details</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="religion_id" class="form-label">Religion</label>
                                                <select name="religion_id"
                                                    class="form-control select2bs4 @error('religion_id') is-invalid @enderror">
                                                    <option value="">Select Religion</option>
                                                    @foreach ($religions as $religion)
                                                        <option value="{{ $religion->id }}"
                                                            {{ old('religion_id') == $religion->id ? 'selected' : '' }}>
                                                            {{ $religion->religion_name }}</option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->any('religion_id'))
                                                    <span class="text-danger">{{ $errors->first('religion_id') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="gender" class="form-label">Gender</label>
                                                <div class="d-flex mt-2">
                                                    <div class="custom-control custom-radio mr-4">
                                                        <input class="custom-control-input" type="radio"
                                                            id="gender_male" name="gender" value="male"
                                                            {{ old('gender') == 'male' ? 'checked' : '' }}>
                                                        <label for="gender_male" class="custom-control-label">Male</label>
                                                    </div>
                                                    <div class="custom-control custom-radio">
                                                        <input class="custom-control-input" type="radio"
                                                            id="gender_female" name="gender" value="female"
                                                            {{ old('gender') == 'female' ? 'checked' : '' }}>
                                                        <label for="gender_female"
                                                            class="custom-control-label">Female</label>
                                                    </div>
                                                </div>
                                                @error('gender')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="marital" class="form-label">Marital Status</label>
                                                <select
                                                    class="form-control select2bs4 @error('marital') is-invalid @enderror"
                                                    name="marital" id="marital">
                                                    <option value="">Select marital status</option>
                                                    <option value="Single"
                                                        {{ old('marital') == 'Single' ? 'selected' : '' }}>Single
                                                    </option>
                                                    <option value="Married"
                                                        {{ old('marital') == 'Married' ? 'selected' : '' }}>Married
                                                    </option>
                                                    <option value="Divorced"
                                                        {{ old('marital') == 'Divorced' ? 'selected' : '' }}>Divorced
                                                    </option>
                                                    <option value="Widowed"
                                                        {{ old('marital') == 'Widowed' ? 'selected' : '' }}>Widowed
                                                    </option>
                                                </select>
                                                @if ($errors->any('marital'))
                                                    <span class="text-danger">{{ $errors->first('marital') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="mt-2 mb-3">Contact Information</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="phone" class="form-label">Phone Number</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                    </div>
                                                    <input type="text" value="{{ old('phone') }}"
                                                        class="form-control @error('phone') is-invalid @enderror"
                                                        id="phone" name="phone" placeholder="Enter phone number">
                                                </div>
                                                @if ($errors->any('phone'))
                                                    <span class="text-danger">{{ $errors->first('phone') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email" class="form-label">Email Address</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-envelope"></i></span>
                                                    </div>
                                                    <input type="email" value="{{ old('email') }}"
                                                        class="form-control @error('email') is-invalid @enderror"
                                                        id="email" name="email" placeholder="Enter email address">
                                                </div>
                                                @if ($errors->any('email'))
                                                    <span class="text-danger">{{ $errors->first('email') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="mt-2 mb-3">Address Information</h5>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="address" class="form-label">Street Address</label>
                                                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2"
                                                    placeholder="Enter street address">{{ old('address') }}</textarea>
                                                @if ($errors->any('address'))
                                                    <span class="text-danger">{{ $errors->first('address') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="village" class="form-label">Village</label>
                                                <input type="text" value="{{ old('village') }}"
                                                    class="form-control @error('village') is-invalid @enderror"
                                                    id="village" name="village" placeholder="Desa/Dusun">
                                                @if ($errors->any('village'))
                                                    <span class="text-danger">{{ $errors->first('village') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="ward" class="form-label">Ward</label>
                                                <input type="text" value="{{ old('ward') }}"
                                                    class="form-control @error('ward') is-invalid @enderror"
                                                    id="ward" name="ward" placeholder="Kelurahan">
                                                @if ($errors->any('ward'))
                                                    <span class="text-danger">{{ $errors->first('ward') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="district" class="form-label">District</label>
                                                <input type="text" value="{{ old('district') }}"
                                                    class="form-control @error('district') is-invalid @enderror"
                                                    id="district" name="district" placeholder="Kecamatan">
                                                @if ($errors->any('district'))
                                                    <span class="text-danger">{{ $errors->first('district') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="city" class="form-label">City</label>
                                                <input type="text" value="{{ old('city') }}"
                                                    class="form-control @error('city') is-invalid @enderror"
                                                    id="city" name="city" placeholder="Kota/Kabupaten">
                                                @if ($errors->any('city'))
                                                    <span class="text-danger">{{ $errors->first('city') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-2">
                                        <button type="button" class="btn btn-primary" onclick="stepper.next()"><i
                                                class="fas fa-arrow-right mr-1"></i> Next</button>
                                    </div>
                                    {{-- END Removing inner card div --}}
                                </div>

                                <!-- Administration Part -->
                                <div id="administration-part" class="content" role="tabpanel"
                                    aria-labelledby="administration-part-trigger">
                                    {{-- START Removing inner card div --}}
                                    <h5 class="mb-3 border-bottom pb-2">Employment Details</h5>
                                    <input type="hidden" value="1"
                                        class="form-control @error('is_active') is-invalid @enderror" id="is_active"
                                        name="is_active">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nik" class="form-label required-field">Employee ID
                                                    (NIK)</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-hashtag"></i></span>
                                                    </div>
                                                    <input type="text" value="{{ old('nik') }}"
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
                                                <label for="class" class="form-label required-field">Employee
                                                    Class</label>
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
                                                        <label for="class2" class="custom-control-label">Non
                                                            Staff</label>
                                                    </div>
                                                </div>
                                                @if ($errors->any('class'))
                                                    <span class="text-danger">{{ $errors->first('class') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="mt-2 mb-3">Hiring Information</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="doh" class="form-label required-field">Date of
                                                    Hire</label>
                                                <div class="input-group date">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-calendar-alt"></i></span>
                                                    </div>
                                                    <input type="date" value="{{ old('doh') }}"
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
                                                <label for="poh" class="form-label required-field">Place of
                                                    Hire</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-map-marker-alt"></i></span>
                                                    </div>
                                                    <input type="text" value="{{ old('poh') }}"
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
                                                    <input type="date" value="{{ old('foc') }}"
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
                                                    class="form-control select2bs4 @error('agreement') is-invalid @enderror">
                                                    <option value="" {{ old('agreement') == '' ? 'selected' : '' }}>
                                                        -Select
                                                        Agreement-</option>
                                                    <option value="PKWT1"
                                                        {{ old('agreement') == 'PKWT1' ? 'selected' : '' }}>PKWT1
                                                    </option>
                                                    <option value="PKWT2"
                                                        {{ old('agreement') == 'PKWT2' ? 'selected' : '' }}>PKWT2
                                                    </option>
                                                    <option value="PKWT3"
                                                        {{ old('agreement') == 'PKWT3' ? 'selected' : '' }}>PKWT3
                                                    </option>
                                                    <option value="PKWT4"
                                                        {{ old('agreement') == 'PKWT4' ? 'selected' : '' }}>PKWT4
                                                    </option>
                                                    <option value="PKWTT"
                                                        {{ old('agreement') == 'PKWTT' ? 'selected' : '' }}>PKWTT
                                                    </option>
                                                    <option value="Daily"
                                                        {{ old('agreement') == 'Daily' ? 'selected' : '' }}>Daily
                                                    </option>
                                                </select>
                                                @if ($errors->any('agreement'))
                                                    <span class="text-danger">{{ $errors->first('agreement') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="mt-2 mb-3">Position Information</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="position_id"
                                                    class="form-label required-field">Position</label>
                                                <select id="position_id" name="position_id"
                                                    class="form-control select2bs4 @error('position_id') is-invalid @enderror">
                                                    <option value="">-Select Position-</option>
                                                    @foreach ($positions as $position)
                                                        <option value="{{ $position->id }}"
                                                            {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                                            {{ $position->position_name }}</option>
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
                                                    class="form-control @error('department') is-invalid @enderror"
                                                    id="department" name="department" readonly>
                                                @if ($errors->any('department'))
                                                    <span class="text-danger">{{ $errors->first('department') }}</span>
                                                @endif
                                                <small class="form-text text-muted">Department will be automatically
                                                    filled based on position selection</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="project_id" class="form-label required-field">Project</label>
                                                <select name="project_id"
                                                    class="form-control select2bs4 @error('project_id') is-invalid @enderror">
                                                    <option value="">-Select Project-</option>
                                                    @foreach ($projects as $project)
                                                        <option value="{{ $project->id }}"
                                                            {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                                            {{ $project->project_code }} -
                                                            {{ $project->project_name }}</option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->any('project_id'))
                                                    <span class="text-danger">{{ $errors->first('project_id') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="company_program" class="form-label">Company
                                                    Program</label>
                                                <input type="text" value="{{ old('company_program') }}"
                                                    class="form-control @error('company_program') is-invalid @enderror"
                                                    id="company_program" name="company_program"
                                                    placeholder="Enter company program">
                                                @if ($errors->any('company_program'))
                                                    <span
                                                        class="text-danger">{{ $errors->first('company_program') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="mt-2 mb-3">Certificates & References</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="no_fptk" class="form-label">FPTK Number</label>
                                                <input type="text" value="{{ old('no_fptk') }}"
                                                    class="form-control @error('no_fptk') is-invalid @enderror"
                                                    id="no_fptk" name="no_fptk" placeholder="Enter FPTK number">
                                                @if ($errors->any('no_fptk'))
                                                    <span class="text-danger">{{ $errors->first('no_fptk') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="no_sk_active" class="form-label">Certificate
                                                    Number</label>
                                                <input type="text" value="{{ old('no_sk_active') }}"
                                                    class="form-control @error('no_sk_active') is-invalid @enderror"
                                                    id="no_sk_active" name="no_sk_active"
                                                    placeholder="Enter certificate number">
                                                @if ($errors->any('no_sk_active'))
                                                    <span class="text-danger">{{ $errors->first('no_sk_active') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="mt-2 mb-3">Compensation</h5>
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
                                                    <span
                                                        class="text-danger">{{ $errors->first('site_allowance') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="other_allowance" class="form-label">Other
                                                    Allowance</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">Rp</span>
                                                    </div>
                                                    <input type="number" value="{{ old('other_allowance') }}"
                                                        class="form-control @error('other_allowance') is-invalid @enderror"
                                                        id="other_allowance" name="other_allowance" placeholder="0">
                                                </div>
                                                @if ($errors->any('other_allowance'))
                                                    <span
                                                        class="text-danger">{{ $errors->first('other_allowance') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary" onclick="stepper.previous()"><i
                                                class="fas fa-arrow-left mr-1"></i>
                                            Previous</button>
                                        <button type="button" class="btn btn-primary" onclick="stepper.next()"><i
                                                class="fas fa-arrow-right mr-1"></i> Next</button>
                                    </div>
                                    {{-- END Removing inner card div --}}
                                </div>

                                <!-- Bank Account Part -->
                                <div id="bank-part" class="content" role="tabpanel" aria-labelledby="bank-part-trigger">
                                    {{-- START Removing inner card div --}}
                                    <h5 class="mb-3 border-bottom pb-2">Bank Account Information</h5>
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Bank account information is used for payroll and other financial transactions.
                                    </div>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="bank_id" class="form-label">Bank Name</label>
                                                <select name="bank_id"
                                                    class="form-control select2bs4 @error('bank_id') is-invalid @enderror">
                                                    <option value="">-Select Bank-</option>
                                                    @foreach ($banks as $bank)
                                                        <option value="{{ $bank->id }}"
                                                            {{ old('bank_id') == $bank->id ? 'selected' : '' }}>
                                                            {{ $bank->bank_name }}</option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->any('bank_id'))
                                                    <span class="text-danger">{{ $errors->first('bank_id') }}</span>
                                                @endif
                                                <small class="form-text text-muted">Select the bank where the employee
                                                    has an account</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="bank_account_no" class="form-label">Account Number</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-credit-card"></i></span>
                                                    </div>
                                                    <input type="text" value="{{ old('bank_account_no') }}"
                                                        class="form-control @error('bank_account_no') is-invalid @enderror"
                                                        id="bank_account_no" name="bank_account_no"
                                                        placeholder="Enter account number">
                                                </div>
                                                @if ($errors->any('bank_account_no'))
                                                    <span
                                                        class="text-danger">{{ $errors->first('bank_account_no') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="bank_account_name" class="form-label">Account Name</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                    </div>
                                                    <input type="text" value="{{ old('bank_account_name') }}"
                                                        class="form-control @error('bank_account_name') is-invalid @enderror"
                                                        id="bank_account_name" name="bank_account_name"
                                                        placeholder="Enter account holder name">
                                                </div>
                                                @if ($errors->any('bank_account_name'))
                                                    <span
                                                        class="text-danger">{{ $errors->first('bank_account_name') }}</span>
                                                @endif
                                                <small class="form-text text-muted">Name as it appears on the bank
                                                    account</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="bank_account_branch" class="form-label">Branch</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-building"></i></span>
                                                    </div>
                                                    <input type="text" value="{{ old('bank_account_branch') }}"
                                                        class="form-control @error('bank_account_branch') is-invalid @enderror"
                                                        id="bank_account_branch" name="bank_account_branch"
                                                        placeholder="Enter branch name">
                                                </div>
                                                @if ($errors->any('bank_account_branch'))
                                                    <span
                                                        class="text-danger">{{ $errors->first('bank_account_branch') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary" onclick="stepper.previous()"><i
                                                class="fas fa-arrow-left mr-1"></i>
                                            Previous</button>
                                        <button type="button" class="btn btn-primary" onclick="stepper.next()"><i
                                                class="fas fa-arrow-right mr-1"></i> Next</button>
                                    </div>
                                    {{-- END Removing inner card div --}}
                                </div>

                                <!-- Tax Identification Part -->
                                <div id="tax-part" class="content" role="tabpanel" aria-labelledby="tax-part-trigger">
                                    {{-- START Removing inner card div --}}
                                    <h5 class="mb-3 border-bottom pb-2">Tax Information</h5>
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Tax information is required for payroll tax deductions and annual tax reporting.
                                    </div>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="tax_no" class="form-label">Tax Identification Number
                                                    (NPWP)</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-file-invoice"></i></span>
                                                    </div>
                                                    <input type="text" value="{{ old('tax_no') }}"
                                                        class="form-control @error('tax_no') is-invalid @enderror"
                                                        id="tax_no" name="tax_no" placeholder="Enter tax number">
                                                </div>
                                                @if ($errors->any('tax_no'))
                                                    <span class="text-danger">{{ $errors->first('tax_no') }}</span>
                                                @endif
                                                <small class="form-text text-muted">Format:
                                                    00.000.000.0-000.000</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="tax_valid_date" class="form-label">Tax Registration
                                                    Date</label>
                                                <div class="input-group date">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-calendar-alt"></i></span>
                                                    </div>
                                                    <input type="date" value="{{ old('tax_valid_date') }}"
                                                        class="form-control @error('tax_valid_date') is-invalid @enderror"
                                                        id="tax_valid_date" name="tax_valid_date">
                                                </div>
                                                @if ($errors->any('tax_valid_date'))
                                                    <span
                                                        class="text-danger">{{ $errors->first('tax_valid_date') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary" onclick="stepper.previous()"><i
                                                class="fas fa-arrow-left mr-1"></i>
                                            Previous</button>
                                        <button type="button" class="btn btn-primary" onclick="stepper.next()"><i
                                                class="fas fa-arrow-right mr-1"></i> Next</button>
                                    </div>
                                    {{-- END Removing inner card div --}}
                                </div>

                                <!-- Health Insurance Part -->
                                <div id="insurance-part" class="content" role="tabpanel"
                                    aria-labelledby="insurance-part-trigger">
                                    {{-- START Removing inner card div --}}
                                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                        <h5 class="mb-0">Health Insurance Information</h5>
                                        <button type="button" id="add-insurance" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus mr-1"></i> Add Insurance
                                        </button>
                                    </div>

                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Add health insurance information for the employee. Click the button above to add
                                        a new entry.
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="table-insurance">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="25%">Insurance Type</th>
                                                    <th width="20%">Insurance No</th>
                                                    <th width="25%">Health Facility</th>
                                                    <th width="20%">Remarks</th>
                                                    <th width="10%" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dynamic content will be added here -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="text-muted mt-3 small">
                                        <i class="fas fa-circle-info mr-1"></i>
                                        Insurance types typically include BPJS Kesehatan and BPJS Ketenagakerjaan
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary" onclick="stepper.previous()"><i
                                                class="fas fa-arrow-left mr-1"></i>
                                            Previous</button>
                                        <button type="button" class="btn btn-primary" onclick="stepper.next()"><i
                                                class="fas fa-arrow-right mr-1"></i> Next</button>
                                    </div>
                                    {{-- END Removing inner card div --}}
                                </div>

                                <!-- Licenses Part -->
                                <div id="license-part" class="content" role="tabpanel"
                                    aria-labelledby="license-part-trigger">
                                    {{-- START Removing inner card div --}}
                                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                        <h5 class="mb-0">License Information</h5>
                                        <button type="button" id="add-license" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus mr-1"></i> Add License
                                        </button>
                                    </div>

                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Add license information such as driving licenses or professional certifications.
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="table-license">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="30%">License Type</th>
                                                    <th width="30%">License Number</th>
                                                    <th width="30%">Expiration Date</th>
                                                    <th width="10%" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dynamic content will be added here -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="text-muted mt-3 small">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        Remember to track expiration dates to ensure timely renewals
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary" onclick="stepper.previous()"><i
                                                class="fas fa-arrow-left mr-1"></i>
                                            Previous</button>
                                        <button type="button" class="btn btn-primary" onclick="stepper.next()"><i
                                                class="fas fa-arrow-right mr-1"></i> Next</button>
                                    </div>
                                    {{-- END Removing inner card div --}}
                                </div>

                                <!-- Families Part -->
                                <div id="family-part" class="content" role="tabpanel"
                                    aria-labelledby="family-part-trigger">
                                    {{-- START Removing inner card div --}}
                                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                        <h5 class="mb-0">Family Information</h5>
                                        <button type="button" id="add-family" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus mr-1"></i> Add Family Member
                                        </button>
                                    </div>

                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Add information about employee's immediate family members.
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="table-family">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="15%">Relationship</th>
                                                    <th width="20%">Name</th>
                                                    <th width="15%">Birth Place</th>
                                                    <th width="15%">Birth Date</th>
                                                    <th width="15%">Remarks</th>
                                                    <th width="15%">BPJS Kesehatan</th>
                                                    <th width="5%" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dynamic content will be added here -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="text-muted mt-3 small">
                                        <i class="fas fa-users mr-1"></i>
                                        Family information is important for benefits administration and emergency
                                        contacts
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary" onclick="stepper.previous()"><i
                                                class="fas fa-arrow-left mr-1"></i>
                                            Previous</button>
                                        <button type="button" class="btn btn-primary" onclick="stepper.next()"><i
                                                class="fas fa-arrow-right mr-1"></i> Next</button>
                                    </div>
                                    {{-- END Removing inner card div --}}
                                </div>

                                <!-- Education Part -->
                                <div id="education-part" class="content" role="tabpanel"
                                    aria-labelledby="education-part-trigger">
                                    {{-- START Removing inner card div --}}
                                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                        <h5 class="mb-0">Educational Background</h5>
                                        <button type="button" id="add-education" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus mr-1"></i> Add Education
                                        </button>
                                    </div>

                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Add formal education history of the employee (schools, universities, etc.).
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="table-education">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="30%">Institution Name</th>
                                                    <th width="30%">Address</th>
                                                    <th width="15%">Year</th>
                                                    <th width="15%">Remarks</th>
                                                    <th width="10%" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dynamic content will be added here -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="text-muted mt-3 small">
                                        <i class="fas fa-graduation-cap mr-1"></i>
                                        List education from the most recent to the oldest
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary" onclick="stepper.previous()"><i
                                                class="fas fa-arrow-left mr-1"></i>
                                            Previous</button>
                                        <button type="button" class="btn btn-primary" onclick="stepper.next()"><i
                                                class="fas fa-arrow-right mr-1"></i> Next</button>
                                    </div>
                                    {{-- END Removing inner card div --}}
                                </div>

                                <!-- Courses Part -->
                                <div id="course-part" class="content" role="tabpanel"
                                    aria-labelledby="course-part-trigger">
                                    {{-- START Removing inner card div --}}
                                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                        <h5 class="mb-0">Training & Courses</h5>
                                        <button type="button" id="add-course" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus mr-1"></i> Add Course
                                        </button>
                                    </div>

                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Add professional training, certifications, or courses completed by the employee.
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="table-course">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="30%">Course Name</th>
                                                    <th width="30%">Institution</th>
                                                    <th width="15%">Year</th>
                                                    <th width="15%">Remarks</th>
                                                    <th width="10%" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dynamic content will be added here -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="text-muted mt-3 small">
                                        <i class="fas fa-certificate mr-1"></i>
                                        Include relevant certifications and specialized training
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary" onclick="stepper.previous()"><i
                                                class="fas fa-arrow-left mr-1"></i>
                                            Previous</button>
                                        <button type="button" class="btn btn-primary" onclick="stepper.next()"><i
                                                class="fas fa-arrow-right mr-1"></i> Next</button>
                                    </div>
                                    {{-- END Removing inner card div --}}
                                </div>

                                <!-- Job Experience Part -->
                                <div id="jobexp-part" class="content" role="tabpanel"
                                    aria-labelledby="jobexp-part-trigger">
                                    {{-- START Removing inner card div --}}
                                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                        <h5 class="mb-0">Work Experience</h5>
                                        <button type="button" id="add-jobexp" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus mr-1"></i> Add Experience
                                        </button>
                                    </div>

                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Add previous employment history of the employee.
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="table-jobexp">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="20%">Company Name</th>
                                                    <th width="20%">Address</th>
                                                    <th width="15%">Position</th>
                                                    <th width="15%">Period</th>
                                                    <th width="20%">Reason for Leaving</th>
                                                    <th width="10%" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dynamic content will be added here -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="text-muted mt-3 small">
                                        <i class="fas fa-briefcase mr-1"></i>
                                        List work experience from the most recent to the oldest
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary" onclick="stepper.previous()"><i
                                                class="fas fa-arrow-left mr-1"></i>
                                            Previous</button>
                                        <button type="button" class="btn btn-primary" onclick="stepper.next()"><i
                                                class="fas fa-arrow-right mr-1"></i> Next</button>
                                    </div>
                                    {{-- END Removing inner card div --}}
                                </div>

                                <!-- Operable Units Part -->
                                <div id="unit-part" class="content" role="tabpanel" aria-labelledby="unit-part-trigger">
                                    {{-- START Removing inner card div --}}
                                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                        <h5 class="mb-0">Operable Units</h5>
                                        <button type="button" id="add-operableunit" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus mr-1"></i> Add Unit
                                        </button>
                                    </div>

                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Add equipment, vehicles, or units that the employee is qualified to operate.
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="table-operableunit">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="30%">Unit Name</th>
                                                    <th width="30%">Unit Type</th>
                                                    <th width="30%">Remarks</th>
                                                    <th width="10%" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dynamic content will be added here -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="text-muted mt-3 small">
                                        <i class="fas fa-truck mr-1"></i>
                                        Add relevant qualifications and equipment skills
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary" onclick="stepper.previous()"><i
                                                class="fas fa-arrow-left mr-1"></i>
                                            Previous</button>
                                        <button type="button" class="btn btn-primary" onclick="stepper.next()"><i
                                                class="fas fa-arrow-right mr-1"></i> Next</button>
                                    </div>
                                    {{-- END Removing inner card div --}}
                                </div>

                                <!-- Emergency Calls Part -->
                                <div id="emergency-part" class="content" role="tabpanel"
                                    aria-labelledby="emergency-part-trigger">
                                    {{-- START Removing inner card div --}}
                                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                        <h5 class="mb-0">Emergency Contacts</h5>
                                        <button type="button" id="add-emergency" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus mr-1"></i> Add Contact
                                        </button>
                                    </div>

                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Add emergency contact information for the employee.
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="table-emergency">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="20%">Relationship</th>
                                                    <th width="25%">Name</th>
                                                    <th width="30%">Address</th>
                                                    <th width="15%">Phone</th>
                                                    <th width="10%" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dynamic content will be added here -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="text-muted mt-3 small">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        Emergency contacts are important for safety and emergency situations
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-secondary"
                                            onclick="stepper.previous()"><i class="fas fa-arrow-left mr-1"></i>
                                            Previous</button>
                                        <button type="button" class="btn btn-primary" onclick="stepper.next()"><i
                                                class="fas fa-arrow-right mr-1"></i> Next</button>
                                    </div>
                                    {{-- END Removing inner card div --}}
                                </div>

                                <!-- Additional Data Part -->
                                <div id="additional-part" class="content" role="tabpanel"
                                    aria-labelledby="additional-part-trigger">
                                    {{-- START Removing inner card div --}}
                                    <h5 class="mb-3 border-bottom pb-2">Additional Information</h5>
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Add additional personal information that may be needed for company uniforms and
                                        equipment.
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="cloth_size" class="form-label">Clothes Size</label>
                                                <select
                                                    class="form-control select2bs4 @error('cloth_size') is-invalid @enderror"
                                                    id="cloth_size" name="cloth_size">
                                                    <option value="">Select Size</option>
                                                    <option value="XS"
                                                        {{ old('cloth_size') == 'XS' ? 'selected' : '' }}>XS</option>
                                                    <option value="S"
                                                        {{ old('cloth_size') == 'S' ? 'selected' : '' }}>S</option>
                                                    <option value="M"
                                                        {{ old('cloth_size') == 'M' ? 'selected' : '' }}>M</option>
                                                    <option value="L"
                                                        {{ old('cloth_size') == 'L' ? 'selected' : '' }}>L</option>
                                                    <option value="XL"
                                                        {{ old('cloth_size') == 'XL' ? 'selected' : '' }}>XL</option>
                                                    <option value="XXL"
                                                        {{ old('cloth_size') == 'XXL' ? 'selected' : '' }}>XXL
                                                    </option>
                                                </select>
                                                @if ($errors->any('cloth_size'))
                                                    <span class="text-danger">{{ $errors->first('cloth_size') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="pants_size" class="form-label">Pants Size</label>
                                                <select
                                                    class="form-control select2bs4 @error('pants_size') is-invalid @enderror"
                                                    id="pants_size" name="pants_size">
                                                    <option value="">Select Size</option>
                                                    <option value="28"
                                                        {{ old('pants_size') == '28' ? 'selected' : '' }}>28</option>
                                                    <option value="30"
                                                        {{ old('pants_size') == '30' ? 'selected' : '' }}>30</option>
                                                    <option value="32"
                                                        {{ old('pants_size') == '32' ? 'selected' : '' }}>32</option>
                                                    <option value="34"
                                                        {{ old('pants_size') == '34' ? 'selected' : '' }}>34</option>
                                                    <option value="36"
                                                        {{ old('pants_size') == '36' ? 'selected' : '' }}>36</option>
                                                    <option value="38"
                                                        {{ old('pants_size') == '38' ? 'selected' : '' }}>38</option>
                                                    <option value="40"
                                                        {{ old('pants_size') == '40' ? 'selected' : '' }}>40</option>
                                                </select>
                                                @if ($errors->any('pants_size'))
                                                    <span class="text-danger">{{ $errors->first('pants_size') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="shoes_size" class="form-label">Shoes Size</label>
                                                <input type="text" value="{{ old('shoes_size') }}"
                                                    class="form-control @error('shoes_size') is-invalid @enderror"
                                                    id="shoes_size" name="shoes_size"
                                                    placeholder="Enter shoes size (e.g., 42)">
                                                @if ($errors->any('shoes_size'))
                                                    <span class="text-danger">{{ $errors->first('shoes_size') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="glasses" class="form-label">Glasses</label>
                                                <select
                                                    class="form-control select2bs4 @error('glasses') is-invalid @enderror"
                                                    id="glasses" name="glasses">
                                                    <option value="">Select Option</option>
                                                    <option value="Yes"
                                                        {{ old('glasses') == 'Yes' ? 'selected' : '' }}>Yes</option>
                                                    <option value="No"
                                                        {{ old('glasses') == 'No' ? 'selected' : '' }}>No</option>
                                                </select>
                                                @if ($errors->any('glasses'))
                                                    <span class="text-danger">{{ $errors->first('glasses') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="height" class="form-label">Height (cm)</label>
                                                <div class="input-group">
                                                    <input type="number" value="{{ old('height') }}"
                                                        class="form-control @error('height') is-invalid @enderror"
                                                        id="height" name="height" placeholder="Enter height">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">cm</span>
                                                    </div>
                                                </div>
                                                @if ($errors->any('height'))
                                                    <span class="text-danger">{{ $errors->first('height') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="weight" class="form-label">Weight (kg)</label>
                                                <div class="input-group">
                                                    <input type="number" value="{{ old('weight') }}"
                                                        class="form-control @error('weight') is-invalid @enderror"
                                                        id="weight" name="weight" placeholder="Enter weight">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">kg</span>
                                                    </div>
                                                </div>
                                                @if ($errors->any('weight'))
                                                    <span class="text-danger">{{ $errors->first('weight') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="button" class="btn btn-secondary"
                                            onclick="stepper.previous()"><i class="fas fa-arrow-left mr-1"></i>
                                            Previous</button>
                                        <button type="button" class="btn btn-primary" onclick="stepper.next()"><i
                                                class="fas fa-arrow-right mr-1"></i> Next</button>
                                    </div>
                                    {{-- END Removing inner card div --}}
                                </div>

                                <!-- Images Part -->
                                <div id="image-part" class="content" role="tabpanel"
                                    aria-labelledby="image-part-trigger">
                                    {{-- START Removing inner card div --}}
                                    <h5 class="mb-3 border-bottom pb-2">Employee Images</h5>
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Upload employee images including ID cards, profile photos, and other relevant
                                        documents.
                                    </div>

                                    <div class="form-group">
                                        <label for="images" class="form-label">Upload Images</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="images"
                                                name="filename[]" multiple>
                                            <label class="custom-file-label" for="images">Choose files</label>
                                        </div>
                                    </div>

                                    <div class="card mt-3 bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="fas fa-lightbulb text-warning mr-2"></i>Image Guidelines
                                            </h6><br>
                                            <ul class="mb-0 pl-3">
                                                <li>Supported formats: JPG, PNG. Maximum file size: 2MB.</li>
                                                <li>Profile photos should be clear and professional</li>
                                                <li>ID card images must be legible</li>
                                                <li>All uploads must be appropriate for workplace use</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="button" class="btn btn-secondary"
                                            onclick="stepper.previous()"><i class="fas fa-arrow-left mr-1"></i>
                                            Previous</button>
                                    </div>
                                    {{-- END Removing inner card div --}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </form>
        </div><!-- /.container-fluid -->

        <a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button"
            aria-label="Scroll to top">
            <i class="fas fa-chevron-up"></i>
        </a>
    </section>
@endsection

@section('styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- BS Stepper -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/bs-stepper/css/bs-stepper.min.css') }}">
    <style>
        /* Custom Stepper Styling */
        .bs-stepper .step-trigger {
            padding: 8px 5px;
            /* Reduced padding */
            color: #6c757d;
            /* Softer inactive color */
            background-color: transparent;
            /* Ensure no background */
            transition: all 0.3s ease;
        }

        .bs-stepper .step-trigger:hover {
            background-color: #f8f9fa;
            /* Subtle hover */
            color: #0056b3;
        }

        .bs-stepper .bs-stepper-circle {
            background-color: #adb5bd;
            /* Grey inactive circle */
            transition: all 0.3s ease;
            width: 35px;
            /* Slightly larger circle */
            height: 35px;
            line-height: 32px;
            /* Adjust line height */
            font-size: 1rem;
            /* Adjust icon size */
        }

        .bs-stepper .step-trigger .bs-stepper-label {
            color: #495057;
            /* Darker label text */
            font-weight: 500;
            margin-left: 8px;
            font-size: small;
            /* Space between circle and label */
        }

        .bs-stepper .step.active .step-trigger .bs-stepper-circle {
            background-color: #007bff;
            /* Primary color for active step */
            box-shadow: 0 2px 5px rgba(0, 123, 255, 0.4);
        }

        .bs-stepper .step.active .step-trigger .bs-stepper-label {
            color: #007bff;
            font-weight: 600;
            font-size: small;
        }

        .bs-stepper .step-content .content {
            animation: fadeIn 0.5s ease-out;
            /* Existing fade, maybe adjust timing */
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* General Form Styling Enhancements */
        .card-body h5,
        .card-body h6 {
            color: #0056b3;
            /* Match primary color */
        }

        .form-group label {
            font-weight: 600;
            /* Bolder labels */
            color: #343a40;
            /* Darker label color */
            margin-bottom: 0.3rem;
            /* Slightly less margin */
        }

        .form-control,
        .select2-container--bootstrap4 .select2-selection {
            border-radius: 0.25rem;
            /* Consistent border radius */
            border-color: #ced4da;
            /* Standard border */
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus,
        .select2-container--bootstrap4.select2-container--focus .select2-selection {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            /* Standard focus */
        }

        /* Style required field indicator */
        .required-field::after {
            content: " *";
            color: #dc3545;
            /* Bootstrap danger color */
            font-weight: bold;
        }

        .input-group-text {
            background-color: #e9ecef;
            /* Light background for input groups */
            border-color: #ced4da;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        /* Table styling improvements */
        #table-insurance thead,
        #table-license thead,
        #table-family thead,
        #table-education thead,
        #table-course thead,
        #table-jobexp thead,
        #table-operableunit thead,
        #table-emergency thead {
            background-color: #e9ecef;
            /* Light header */
            color: #495057;
            font-weight: 600;
        }

        .table-bordered th,
        .table-bordered td {
            border-color: #dee2e6;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            /* Subtle hover */
        }

        .btn-outline-danger {
            color: #dc3545;
            border-color: #dc3545;
        }

        .btn-outline-danger:hover {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .alert {
            border-radius: 0.25rem;
            padding: 0.75rem 1.25rem;
        }

        .alert-info {
            background-color: #e2f3ff;
            border-color: #b8e0ff;
            color: #0c5460;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }

        /* --- Global Horizontal Scroll for Stepper Header --- */
        .bs-stepper-header {
            overflow-x: auto;
            /* Enable horizontal scroll */
            white-space: nowrap;
            /* Prevent steps from wrapping */
            /* padding: 10px 15px; */
            /* Add padding top/bottom and left/right */
            /* background-color: #f8f9fa; /* Optional: Add subtle background */
            border-bottom: 1px solid #dee2e6;
            /* Add a bottom border */
        }

        .bs-stepper .step {
            display: inline-block;
            /* Make steps sit side-by-side */
            vertical-align: top;
            /* Align steps nicely */
            width: auto;
            /* Allow steps to take natural width */
            /* margin-right: 20px; */
            /* Add some space between steps */
        }

        .bs-stepper .step:last-child {
            margin-right: 0;
            /* No margin on the last step */
        }

        /* --- End Global Horizontal Scroll --- */

        /* Responsive adjustments for stepper */
        @media (max-width: 768px) {
            /* Header adjustments already handled globally */
            /* .bs-stepper-header { */
            /* overflow-x: auto;     */
            /* white-space: nowrap;  */
            /* padding-bottom: 10px; */
            /* } */

            /* Step adjustments already handled globally */
            /* .bs-stepper .step { */
            /* display: inline-block; */
            /* vertical-align: top;   */
            /* width: auto;           */
            /* margin-right: 15px;    */
            /* } */
            /* .bs-stepper .step:last-child { */
            /* margin-right: 0; */
            /* } */

            /* Keep Trigger and Label adjustments for mobile */
            .bs-stepper .step-trigger {
                padding: 10px;
                /* Keep user's padding preference */
                white-space: normal;
                /* Allow label text to wrap if needed */
                text-align: center;
                /* Center label below circle */
            }

            .bs-stepper .step-trigger .bs-stepper-label {
                display: block;
                /* Make label appear below circle */
                margin-left: 0;
                /* Reset margin */
                margin-top: 5px;
                /* Add space between circle and label */
            }

        }

        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 25px;
            right: 25px;
            display: none;
            /* Hidden by default */
            z-index: 1030;
            /* Ensure it's above most content */
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}" defer></script>
    <script src="{{ asset('assets/plugins/bs-stepper/js/bs-stepper.min.js') }}" defer></script>
    <script src="{{ asset('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}" defer></script>

    <script>
        $(document).ready(function() {
            // Initialize stepper
            var stepper = new Stepper(document.querySelector('.bs-stepper'), {
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
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Update hash when clicking stepper buttons
            document.querySelectorAll('.step-trigger').forEach(trigger => {
                trigger.addEventListener('click', function() {
                    const paneId = this.getAttribute('aria-controls');
                    if (paneId) {
                        const hash = paneId.replace('-part', '');
                        window.location.hash = hash;
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Initialize Select2 Elements
            function initializeSelect2(container) {
                $(container).find('.select2bs4').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: $(this).data('placeholder') || 'Select an option',
                    allowClear: true
                });
            }

            // Initialize components
            initializeSelect2(document);
            bsCustomFileInput.init();

            // Focus search field when select2 opens
            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            });

            // Common function to add dynamic rows
            function addDynamicRow(tableId, rowHtml) {
                const tableBody = $(`#${tableId} tbody`);
                tableBody.append(rowHtml);
                // Initialize select2 for the new row if it contains select2 elements
                initializeSelect2(tableBody.find('tr:last'));
            }

            // Remove row functionality
            $(document).on('click', '.remove-input-field', function() {
                $(this).closest('tr').remove();
            });

            // Insurances
            $("#add-insurance").click(function() {
                const rowHtml = `<tr>
                    <td>
                        <select name="health_insurance_type[]" class="form-control select2bs4" data-placeholder="Select Insurance" required>
                            <option value=""></option>
                    <option value="bpjsks">BPJS Kesehatan</option>
                    <option value="bpjskt">BPJS Ketenagakerjaan</option>
                </select>
              </td>
                    <td><input type="text" class="form-control" name="health_insurance_no[]" placeholder="Insurance No" required></td>
                    <td><input type="text" class="form-control" name="health_facility[]" placeholder="Health Facility" required></td>
                    <td><input type="text" class="form-control" name="health_insurance_remarks[]" placeholder="Remarks"></td>
                    <td class="text-center"><button type="button" class="btn btn-outline-danger remove-input-field"><i class="fas fa-trash-alt"></i></button></td>
            </tr>`;
                addDynamicRow('table-insurance', rowHtml);
            });

            // Families
            $("#add-family").click(function() {
                const rowHtml = `<tr>
                    <td>
                        <select name="family_relationship[]" class="form-control select2bs4" data-placeholder="Select Relationship" required>
                            <option value=""></option>
                    <option value="Husband">Husband</option>
                    <option value="Wife">Wife</option>
                    <option value="Child">Child</option>
                            <option value="Parent">Parent</option>
                            <option value="Sibling">Sibling</option>
                </select>
              </td>
                    <td><input type="text" class="form-control" name="family_name[]" placeholder="Full Name" required></td>
                    <td><input type="text" class="form-control" name="family_birthplace[]" placeholder="Birth Place"></td>
                    <td><input type="date" class="form-control" name="family_birthdate[]"></td>
                    <td><input type="text" class="form-control" name="family_remarks[]" placeholder="Remarks"></td>
                    <td><input type="text" class="form-control" name="bpjsks_no[]" placeholder="BPJS No (if any)"></td>
                    <td class="text-center"><button type="button" class="btn btn-outline-danger remove-input-field"><i class="fas fa-trash-alt"></i></button></td>
            </tr>`;
                addDynamicRow('table-family', rowHtml);
            });

            // Operable Units
            $("#add-operableunit").click(function() {
                const rowHtml = `<tr>
                    <td>
                        <select name="unit_name[]" class="form-control select2bs4" data-placeholder="Select Unit" required>
                            <option value=""></option>
                    <option value="LV / SARANA">LV / SARANA</option>
                    <option value="DUMP TRUCK">DUMP TRUCK</option>
                    <option value="ADT">ADT</option>
                    <option value="EXCAVATOR">EXCAVATOR</option>
                    <option value="DOZER">DOZER</option>
                    <option value="GRADER">GRADER</option>
                            <option value="COMPACTOR">COMPACTOR</option>
                            <option value="CRANE">CRANE</option>
                            <option value="OTHER">OTHER</option>
                </select>
              </td>
                    <td><input type="text" class="form-control" name="unit_type[]" placeholder="Specific Type/Model" required></td>
                    <td><input type="text" class="form-control" name="unit_remarks[]" placeholder="Remarks/License Ref"></td>
                    <td class="text-center"><button type="button" class="btn btn-outline-danger remove-input-field"><i class="fas fa-trash-alt"></i></button></td>
            </tr>`;
                addDynamicRow('table-operableunit', rowHtml);
            });

            // Educations
            $("#add-education").click(function() {
                const rowHtml = `<tr>
                    <td><input type="text" class="form-control" name="education_name[]" placeholder="Institution Name" required></td>
                    <td><input type="text" class="form-control" name="education_address[]" placeholder="City/Address"></td>
                    <td><input type="text" class="form-control" name="education_year[]" placeholder="Year Graduated"></td>
                    <td><input type="text" class="form-control" name="education_remarks[]" placeholder="Major/Degree/Remarks"></td>
                    <td class="text-center"><button type="button" class="btn btn-outline-danger remove-input-field"><i class="fas fa-trash-alt"></i></button></td>
                </tr>`;
                addDynamicRow('table-education', rowHtml);
            });

            // Courses
            $("#add-course").click(function() {
                const rowHtml = `<tr>
                    <td><input type="text" class="form-control" name="course_name[]" placeholder="Course/Training Name" required></td>
                    <td><input type="text" class="form-control" name="course_address[]" placeholder="Provider/Institution"></td>
                    <td><input type="text" class="form-control" name="course_year[]" placeholder="Year Completed"></td>
                    <td><input type="text" class="form-control" name="course_remarks[]" placeholder="Certificate No/Remarks"></td>
                    <td class="text-center"><button type="button" class="btn btn-outline-danger remove-input-field"><i class="fas fa-trash-alt"></i></button></td>
            </tr>`;
                addDynamicRow('table-course', rowHtml);
            });

            // Job Experiences
            $("#add-jobexp").click(function() {
                const rowHtml = `<tr>
                    <td><input type="text" class="form-control" name="company_name[]" placeholder="Company Name" required></td>
                    <td><input type="text" class="form-control" name="company_address[]" placeholder="City/Address"></td>
                    <td><input type="text" class="form-control" name="job_position[]" placeholder="Last Position"></td>
                    <td><input type="text" class="form-control" name="job_duration[]" placeholder="Period (e.g., 2018-2022)"></td>
                    <td><input type="text" class="form-control" name="quit_reason[]" placeholder="Reason for Leaving"></td>
                    <td class="text-center"><button type="button" class="btn btn-outline-danger remove-input-field"><i class="fas fa-trash-alt"></i></button></td>
            </tr>`;
                addDynamicRow('table-jobexp', rowHtml);
            });

            // Licenses
            $("#add-license").click(function() {
                const rowHtml = `<tr>
                    <td><input type="text" class="form-control" name="driver_license_type[]" placeholder="License/Certification Type" required></td>
                    <td><input type="text" class="form-control" name="driver_license_no[]" placeholder="License Number"></td>
                    <td><input type="date" class="form-control" name="driver_license_exp[]"></td>
                    <td class="text-center"><button type="button" class="btn btn-outline-danger remove-input-field"><i class="fas fa-trash-alt"></i></button></td>
            </tr>`;
                addDynamicRow('table-license', rowHtml);
            });

            // Emergency Contacts
            $("#add-emergency").click(function() {
                const rowHtml = `<tr>
                    <td><input type="text" class="form-control" name="emrg_call_relation[]" placeholder="Relationship" required></td>
                    <td><input type="text" class="form-control" name="emrg_call_name[]" placeholder="Contact Name" required></td>
                    <td><input type="text" class="form-control" name="emrg_call_address[]" placeholder="Address"></td>
                    <td><input type="text" class="form-control" name="emrg_call_phone[]" placeholder="Phone Number" required></td>
                    <td class="text-center"><button type="button" class="btn btn-outline-danger remove-input-field"><i class="fas fa-trash-alt"></i></button></td>
            </tr>`;
                addDynamicRow('table-emergency', rowHtml);
            });

            // Autofill department based on position_id
            $('#position_id').on('change', function() {
                var position_id = $(this).val();
                if (position_id) {
                    $.ajax({
                        url: "{{ route('employees.getDepartment') }}",
                        type: "GET",
                        data: {
                            position_id: position_id
                        },
                        dataType: 'json',
                        success: function(data) {
                            if (data && data.department_name) {
                                $('#department').val(data.department_name).trigger('change');
                            } else {
                                $('#department').val('').trigger('change');
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error("Error fetching department: ", textStatus,
                                errorThrown);
                            $('#department').val('').trigger('change');
                        }
                    });
                } else {
                    $('#department').val('').trigger('change');
                }
            });
        });
    </script>
@endsection
