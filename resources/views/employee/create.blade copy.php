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
                    <li class="breadcrumb-item active">Employee</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <form method="POST" action="{{ url('employees') }}" enctype="multipart/form-data" id="employeeForm">
                    @csrf
                    <div class="card card-primary card-outline card-tabs">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user-plus mr-1"></i>
                                <strong>{{ $subtitle }}</strong>
                            </h3>
                            <div class="card-tools">
                                <button type="submit" class="btn btn-primary"><i
                                        class="fas fa-save mr-1"></i>Save</button>
                                <a href="{{ url('employees') }}" class="btn btn-warning ml-2"><i
                                        class="fas fa-undo mr-1"></i>Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-3">
                                    <div class="nav flex-column nav-tabs nav-tabs-vertical" id="v-tabs"
                                        role="tablist">
                                        <a class="nav-link active" id="v-tabs-employee-tab" data-toggle="pill"
                                            href="#v-tabs-employee" role="tab" aria-controls="v-tabs-employee"
                                            aria-selected="true">
                                            <i class="fas fa-id-card mr-2"></i>Personal Detail
                                        </a>
                                        <a class="nav-link" id="v-tabs-administration-tab" data-toggle="pill"
                                            href="#v-tabs-administration" role="tab"
                                            aria-controls="v-tabs-administration" aria-selected="false">
                                            <i class="fas fa-briefcase mr-2"></i>Administrations
                                        </a>
                                        <a class="nav-link" id="v-tabs-bank-tab" data-toggle="pill" href="#v-tabs-bank"
                                            role="tab" aria-controls="v-tabs-bank" aria-selected="false">
                                            <i class="fas fa-money-check-alt mr-2"></i>Bank Accounts
                                        </a>
                                        <a class="nav-link" id="v-tabs-taxidentification-tab" data-toggle="pill"
                                            href="#v-tabs-taxidentification" role="tab"
                                            aria-controls="v-tabs-taxidentification" aria-selected="false">
                                            <i class="fas fa-file-invoice-dollar mr-2"></i>Tax Identification
                                        </a>
                                        <a class="nav-link" id="v-tabs-insurance-tab" data-toggle="pill"
                                            href="#v-tabs-insurance" role="tab" aria-controls="v-tabs-insurance"
                                            aria-selected="false">
                                            <i class="fas fa-heartbeat mr-2"></i>Health Insurance
                                        </a>
                                        <a class="nav-link" id="v-tabs-licence-tab" data-toggle="pill"
                                            href="#v-tabs-licence" role="tab" aria-controls="v-tabs-licence"
                                            aria-selected="false">
                                            <i class="fas fa-id-badge mr-2"></i>Licences
                                        </a>
                                        <a class="nav-link" id="v-tabs-family-tab" data-toggle="pill"
                                            href="#v-tabs-family" role="tab" aria-controls="v-tabs-family"
                                            aria-selected="false">
                                            <i class="fas fa-users mr-2"></i>Families
                                        </a>
                                        <a class="nav-link" id="v-tabs-education-tab" data-toggle="pill"
                                            href="#v-tabs-education" role="tab" aria-controls="v-tabs-education"
                                            aria-selected="false">
                                            <i class="fas fa-graduation-cap mr-2"></i>Educations
                                        </a>
                                        <a class="nav-link" id="v-tabs-course-tab" data-toggle="pill"
                                            href="#v-tabs-course" role="tab" aria-controls="v-tabs-course"
                                            aria-selected="false">
                                            <i class="fas fa-certificate mr-2"></i>Courses
                                        </a>
                                        <a class="nav-link" id="v-tabs-jobexp-tab" data-toggle="pill"
                                            href="#v-tabs-jobexp" role="tab" aria-controls="v-tabs-jobexp"
                                            aria-selected="false">
                                            <i class="fas fa-history mr-2"></i>Job Experiences
                                        </a>
                                        <a class="nav-link" id="v-tabs-unit-tab" data-toggle="pill"
                                            href="#v-tabs-unit" role="tab" aria-controls="v-tabs-unit"
                                            aria-selected="false">
                                            <i class="fas fa-truck mr-2"></i>Operable Units
                                        </a>
                                        <a class="nav-link" id="v-tabs-emergency-tab" data-toggle="pill"
                                            href="#v-tabs-emergency" role="tab" aria-controls="v-tabs-emergency"
                                            aria-selected="false">
                                            <i class="fas fa-phone-alt mr-2"></i>Emergency Calls
                                        </a>
                                        <a class="nav-link" id="v-tabs-additional-tab" data-toggle="pill"
                                            href="#v-tabs-additional" role="tab"
                                            aria-controls="v-tabs-additional" aria-selected="false">
                                            <i class="fas fa-info-circle mr-2"></i>Additional Data
                                        </a>
                                        <a class="nav-link" id="v-tabs-image-tab" data-toggle="pill"
                                            href="#v-tabs-image" role="tab" aria-controls="v-tabs-image"
                                            aria-selected="false">
                                            <i class="fas fa-images mr-2"></i>Images
                                        </a>
                                    </div>
                                </div>
                                <div class="col-9">
                                    <div class="tab-content" id="v-tabs-tabContent">
                                        <!-- Personal Detail Tab -->
                                        <div class="tab-pane fade show active" id="v-tabs-employee" role="tabpanel"
                                            aria-labelledby="v-tabs-employee-tab">
                                            <div class="card card-body shadow-sm">
                                                <h5 class="mb-3 border-bottom pb-2">Personal Information</h5>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="fullname"
                                                                class="form-label required-field">Full Name</label>
                                                            <input type="text" value="{{ old('fullname') }}"
                                                                class="form-control @error('fullname') is-invalid @enderror"
                                                                id="fullname" name="fullname" autofocus="true"
                                                                placeholder="Enter full name">
                                                            @if ($errors->any('fullname'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('fullname') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="identity_card"
                                                                class="form-label required-field">Identity Card</label>
                                                            <input type="text" value="{{ old('identity_card') }}"
                                                                class="form-control @error('identity_card') is-invalid @enderror"
                                                                id="identity_card" name="identity_card"
                                                                placeholder="Enter KTP number">
                                                            @if ($errors->any('identity_card'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('identity_card') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="nationality"
                                                                class="form-label">Nationality</label>
                                                            <input type="text"
                                                                value="{{ old('nationality', 'Indonesia') }}"
                                                                class="form-control @error('nationality') is-invalid @enderror"
                                                                id="nationality" name="nationality"
                                                                placeholder="Enter nationality">
                                                            @if ($errors->any('nationality'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('nationality') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <h6 class="mt-4 mb-3 text-muted">Birth Information</h6>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="emp_pob"
                                                                class="form-label required-field">Place of
                                                                Birth</label>
                                                            <input type="text" value="{{ old('emp_pob') }}"
                                                                class="form-control @error('emp_pob') is-invalid @enderror"
                                                                id="emp_pob" name="emp_pob"
                                                                placeholder="Enter birth place">
                                                            @if ($errors->any('emp_pob'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('emp_pob') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="emp_dob"
                                                                class="form-label required-field">Date of Birth</label>
                                                            <input type="date" value="{{ old('emp_dob') }}"
                                                                class="form-control @error('emp_dob') is-invalid @enderror"
                                                                id="emp_dob" name="emp_dob">
                                                            @if ($errors->any('emp_dob'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('emp_dob') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="blood_type" class="form-label">Blood
                                                                Type</label>
                                                            <select
                                                                class="form-control select2bs4 @error('blood_type') is-invalid @enderror"
                                                                name="blood_type" id="blood_type">
                                                                <option value="">Select blood type</option>
                                                                <option value="A"
                                                                    {{ old('blood_type') == 'A' ? 'selected' : '' }}>A
                                                                </option>
                                                                <option value="B"
                                                                    {{ old('blood_type') == 'B' ? 'selected' : '' }}>B
                                                                </option>
                                                                <option value="AB"
                                                                    {{ old('blood_type') == 'AB' ? 'selected' : '' }}>
                                                                    AB</option>
                                                                <option value="O"
                                                                    {{ old('blood_type') == 'O' ? 'selected' : '' }}>O
                                                                </option>
                                                            </select>
                                                            @if ($errors->any('blood_type'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('blood_type') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <h6 class="mt-4 mb-3 text-muted">Personal Details</h6>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="religion_id"
                                                                class="form-label">Religion</label>
                                                            <select name="religion_id"
                                                                class="form-control select2bs4 @error('religion_id') is-invalid @enderror">
                                                                <option value="">Select Religion</option>
                                                                @foreach ($religions as $religion)
                                                                <option value="{{ $religion->id }}"
                                                                    {{ old('religion_id') == $religion->id ? 'selected' : '' }}>
                                                                    {{ $religion->religion_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            @if ($errors->any('religion_id'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('religion_id') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="gender" class="form-label">Gender</label>
                                                            <div class="d-flex mt-2">
                                                                <div class="custom-control custom-radio mr-4">
                                                                    <input class="custom-control-input" type="radio"
                                                                        id="gender_male" name="gender"
                                                                        value="male"
                                                                        {{ old('gender') == 'male' ? 'checked' : '' }}>
                                                                    <label for="gender_male"
                                                                        class="custom-control-label">Male</label>
                                                                </div>
                                                                <div class="custom-control custom-radio">
                                                                    <input class="custom-control-input" type="radio"
                                                                        id="gender_female" name="gender"
                                                                        value="female"
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
                                                            <label for="marital" class="form-label">Marital
                                                                Status</label>
                                                            <select
                                                                class="form-control select2bs4 @error('marital') is-invalid @enderror"
                                                                name="marital" id="marital">
                                                                <option value="">Select marital status</option>
                                                                <option value="Single"
                                                                    {{ old('marital') == 'Single' ? 'selected' : '' }}>
                                                                    Single</option>
                                                                <option value="Married"
                                                                    {{ old('marital') == 'Married' ? 'selected' : '' }}>
                                                                    Married</option>
                                                                <option value="Divorced"
                                                                    {{ old('marital') == 'Divorced' ? 'selected' : '' }}>
                                                                    Divorced</option>
                                                                <option value="Widowed"
                                                                    {{ old('marital') == 'Widowed' ? 'selected' : '' }}>
                                                                    Widowed</option>
                                                            </select>
                                                            @if ($errors->any('marital'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('marital') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <h6 class="mt-4 mb-3 text-muted">Contact Information</h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="phone" class="form-label">Phone
                                                                Number</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i
                                                                            class="fas fa-phone"></i></span>
                                                                </div>
                                                                <input type="text" value="{{ old('phone') }}"
                                                                    class="form-control @error('phone') is-invalid @enderror"
                                                                    id="phone" name="phone"
                                                                    placeholder="Enter phone number">
                                                            </div>
                                                            @if ($errors->any('phone'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('phone') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="email" class="form-label">Email
                                                                Address</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i
                                                                            class="fas fa-envelope"></i></span>
                                                                </div>
                                                                <input type="email" value="{{ old('email') }}"
                                                                    class="form-control @error('email') is-invalid @enderror"
                                                                    id="email" name="email"
                                                                    placeholder="Enter email address">
                                                            </div>
                                                            @if ($errors->any('email'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('email') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <h6 class="mt-4 mb-3 text-muted">Address Information</h6>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="address" class="form-label">Street
                                                                Address</label>
                                                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2"
                                                                placeholder="Enter street address">{{ old('address') }}</textarea>
                                                            @if ($errors->any('address'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('address') }}</span>
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
                                                                id="village" name="village"
                                                                placeholder="Desa/Dusun">
                                                            @if ($errors->any('village'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('village') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="ward" class="form-label">Ward</label>
                                                            <input type="text" value="{{ old('ward') }}"
                                                                class="form-control @error('ward') is-invalid @enderror"
                                                                id="ward" name="ward"
                                                                placeholder="Kelurahan">
                                                            @if ($errors->any('ward'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('ward') }}</span>
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
                                                                id="district" name="district"
                                                                placeholder="Kecamatan">
                                                            @if ($errors->any('district'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('district') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="city" class="form-label">City</label>
                                                            <input type="text" value="{{ old('city') }}"
                                                                class="form-control @error('city') is-invalid @enderror"
                                                                id="city" name="city"
                                                                placeholder="Kota/Kabupaten">
                                                            @if ($errors->any('city'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('city') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Administration Tab -->
                                        <div class="tab-pane fade" id="v-tabs-administration" role="tabpanel"
                                            aria-labelledby="v-tabs-administration-tab">
                                            <div class="card card-body shadow-sm">
                                                <h5 class="mb-3 border-bottom pb-2">Employment Details</h5>
                                                <input type="hidden" value="1"
                                                    class="form-control @error('is_active') is-invalid @enderror"
                                                    id="is_active" name="is_active">

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="nik"
                                                                class="form-label required-field">Employee ID
                                                                (NIK)</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i
                                                                            class="fas fa-hashtag"></i></span>
                                                                </div>
                                                                <input type="text" value="{{ old('nik') }}"
                                                                    class="form-control @error('nik') is-invalid @enderror"
                                                                    id="nik" name="nik"
                                                                    placeholder="Enter employee ID">
                                                            </div>
                                                            @if ($errors->any('nik'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('nik') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="class"
                                                                class="form-label required-field">Employee
                                                                Class</label>
                                                            <div class="d-flex mt-2">
                                                                <div
                                                                    class="custom-control custom-radio custom-control-primary mr-4">
                                                                    <input class="custom-control-input" type="radio"
                                                                        id="class1" name="class" value="Staff"
                                                                        {{ old('class') == 'Staff' ? 'checked' : '' }}>
                                                                    <label for="class1"
                                                                        class="custom-control-label">Staff</label>
                                                                </div>
                                                                <div
                                                                    class="custom-control custom-radio custom-control-primary">
                                                                    <input class="custom-control-input" type="radio"
                                                                        id="class2" name="class"
                                                                        value="Non Staff"
                                                                        {{ old('class') == 'Non Staff' ? 'checked' : '' }}>
                                                                    <label for="class2"
                                                                        class="custom-control-label">Non Staff</label>
                                                                </div>
                                                            </div>
                                                            @if ($errors->any('class'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('class') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <h6 class="mt-4 mb-3 text-muted">Hiring Information</h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="doh"
                                                                class="form-label required-field">Date of Hire</label>
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
                                                            <span
                                                                class="text-danger">{{ $errors->first('doh') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="poh"
                                                                class="form-label required-field">Place of Hire</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i
                                                                            class="fas fa-map-marker-alt"></i></span>
                                                                </div>
                                                                <input type="text" value="{{ old('poh') }}"
                                                                    class="form-control @error('poh') is-invalid @enderror"
                                                                    id="poh" name="poh"
                                                                    placeholder="Enter place of hire">
                                                            </div>
                                                            @if ($errors->any('poh'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('poh') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="foc" class="form-label">First of
                                                                Contract</label>
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
                                                            <span
                                                                class="text-danger">{{ $errors->first('foc') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="agreement" class="form-label">Agreement
                                                                Type</label>
                                                            <select name="agreement"
                                                                class="form-control select2bs4 @error('agreement') is-invalid @enderror">
                                                                <option value=""
                                                                    {{ old('agreement') == '' ? 'selected' : '' }}>
                                                                    -Select Agreement-
                                                                </option>
                                                                <option value="PKWT1"
                                                                    {{ old('agreement') == 'PKWT1' ? 'selected' : '' }}>
                                                                    PKWT1
                                                                </option>
                                                                <option value="PKWT2"
                                                                    {{ old('agreement') == 'PKWT2' ? 'selected' : '' }}>
                                                                    PKWT2
                                                                </option>
                                                                <option value="PKWT3"
                                                                    {{ old('agreement') == 'PKWT3' ? 'selected' : '' }}>
                                                                    PKWT3
                                                                </option>
                                                                <option value="PKWT4"
                                                                    {{ old('agreement') == 'PKWT4' ? 'selected' : '' }}>
                                                                    PKWT4
                                                                </option>
                                                                <option value="PKWTT"
                                                                    {{ old('agreement') == 'PKWTT' ? 'selected' : '' }}>
                                                                    PKWTT
                                                                </option>
                                                                <option value="Daily"
                                                                    {{ old('agreement') == 'Daily' ? 'selected' : '' }}>
                                                                    Daily
                                                                </option>
                                                            </select>
                                                            @if ($errors->any('agreement'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('agreement') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <h6 class="mt-4 mb-3 text-muted">Position Information</h6>
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
                                                                    {{ $position->position_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            @if ($errors->any('position_id'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('position_id') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="department"
                                                                class="form-label">Department</label>
                                                            <input type="text" value="{{ old('department') }}"
                                                                class="form-control @error('department') is-invalid @enderror"
                                                                id="department" name="department" readonly>
                                                            @if ($errors->any('department'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('department') }}</span>
                                                            @endif
                                                            <small class="form-text text-muted">Department will be
                                                                automatically filled based on position selection</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="project_id"
                                                                class="form-label required-field">Project</label>
                                                            <select name="project_id"
                                                                class="form-control select2bs4 @error('project_id') is-invalid @enderror">
                                                                <option value="">-Select Project-</option>
                                                                @foreach ($projects as $project)
                                                                <option value="{{ $project->id }}"
                                                                    {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                                                    {{ $project->project_code }} -
                                                                    {{ $project->project_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            @if ($errors->any('project_id'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('project_id') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="company_program" class="form-label">Company
                                                                Program</label>
                                                            <input type="text"
                                                                value="{{ old('company_program') }}"
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

                                                <h6 class="mt-4 mb-3 text-muted">Certificates & References</h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="no_fptk" class="form-label">FPTK
                                                                Number</label>
                                                            <input type="text" value="{{ old('no_fptk') }}"
                                                                class="form-control @error('no_fptk') is-invalid @enderror"
                                                                id="no_fptk" name="no_fptk"
                                                                placeholder="Enter FPTK number">
                                                            @if ($errors->any('no_fptk'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('no_fptk') }}</span>
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
                                                            <span
                                                                class="text-danger">{{ $errors->first('no_sk_active') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <h6 class="mt-4 mb-3 text-muted">Compensation</h6>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="basic_salary" class="form-label">Basic
                                                                Salary</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">Rp</span>
                                                                </div>
                                                                <input type="number"
                                                                    value="{{ old('basic_salary') }}"
                                                                    class="form-control @error('basic_salary') is-invalid @enderror"
                                                                    id="basic_salary" name="basic_salary"
                                                                    placeholder="0">
                                                            </div>
                                                            @if ($errors->any('basic_salary'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('basic_salary') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="site_allowance" class="form-label">Site
                                                                Allowance</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">Rp</span>
                                                                </div>
                                                                <input type="number"
                                                                    value="{{ old('site_allowance') }}"
                                                                    class="form-control @error('site_allowance') is-invalid @enderror"
                                                                    id="site_allowance" name="site_allowance"
                                                                    placeholder="0">
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
                                                                <input type="number"
                                                                    value="{{ old('other_allowance') }}"
                                                                    class="form-control @error('other_allowance') is-invalid @enderror"
                                                                    id="other_allowance" name="other_allowance"
                                                                    placeholder="0">
                                                            </div>
                                                            @if ($errors->any('other_allowance'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('other_allowance') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Bank Account Tab -->
                                        <div class="tab-pane fade" id="v-tabs-bank" role="tabpanel"
                                            aria-labelledby="v-tabs-bank-tab">
                                            <div class="card card-body shadow-sm">
                                                <h5 class="mb-3 border-bottom pb-2">Bank Account Information</h5>
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
                                                                    {{ $bank->bank_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            @if ($errors->any('bank_id'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('bank_id') }}</span>
                                                            @endif
                                                            <small class="form-text text-muted">Select the bank where
                                                                the employee has an account</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="form-group">
                                                            <label for="bank_account_no" class="form-label">Account
                                                                Number</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i
                                                                            class="fas fa-credit-card"></i></span>
                                                                </div>
                                                                <input type="text"
                                                                    value="{{ old('bank_account_no') }}"
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
                                                            <label for="bank_account_name" class="form-label">Account
                                                                Name</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i
                                                                            class="fas fa-user"></i></span>
                                                                </div>
                                                                <input type="text"
                                                                    value="{{ old('bank_account_name') }}"
                                                                    class="form-control @error('bank_account_name') is-invalid @enderror"
                                                                    id="bank_account_name" name="bank_account_name"
                                                                    placeholder="Enter account holder name">
                                                            </div>
                                                            @if ($errors->any('bank_account_name'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('bank_account_name') }}</span>
                                                            @endif
                                                            <small class="form-text text-muted">Name as it appears on
                                                                the bank account</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="form-group">
                                                            <label for="bank_account_branch"
                                                                class="form-label">Branch</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i
                                                                            class="fas fa-building"></i></span>
                                                                </div>
                                                                <input type="text"
                                                                    value="{{ old('bank_account_branch') }}"
                                                                    class="form-control @error('bank_account_branch') is-invalid @enderror"
                                                                    id="bank_account_branch"
                                                                    name="bank_account_branch"
                                                                    placeholder="Enter branch name">
                                                            </div>
                                                            @if ($errors->any('bank_account_branch'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('bank_account_branch') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="alert alert-info mt-3">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    Bank account information is used for payroll and other financial
                                                    transactions.
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tax Identification Tab -->
                                        <div class="tab-pane fade" id="v-tabs-taxidentification" role="tabpanel"
                                            aria-labelledby="v-tabs-taxidentification-tab">
                                            <div class="card card-body shadow-sm">
                                                <h5 class="mb-3 border-bottom pb-2">Tax Information</h5>
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="form-group">
                                                            <label for="tax_no" class="form-label">Tax
                                                                Identification Number (NPWP)</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i
                                                                            class="fas fa-file-invoice"></i></span>
                                                                </div>
                                                                <input type="text" value="{{ old('tax_no') }}"
                                                                    class="form-control @error('tax_no') is-invalid @enderror"
                                                                    id="tax_no" name="tax_no"
                                                                    placeholder="Enter tax number">
                                                            </div>
                                                            @if ($errors->any('tax_no'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('tax_no') }}</span>
                                                            @endif
                                                            <small class="form-text text-muted">Format:
                                                                00.000.000.0-000.000</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <div class="form-group">
                                                            <label for="tax_valid_date" class="form-label">Tax
                                                                Registration Date</label>
                                                            <div class="input-group date">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i
                                                                            class="fas fa-calendar-alt"></i></span>
                                                                </div>
                                                                <input type="date"
                                                                    value="{{ old('tax_valid_date') }}"
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

                                                <div class="alert alert-warning mt-3">
                                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                                    Tax information is required for payroll tax deductions and annual
                                                    tax reporting.
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Health Insurance Tab -->
                                        <div class="tab-pane fade" id="v-tabs-insurance" role="tabpanel"
                                            aria-labelledby="v-tabs-insurance-tab">
                                            <div class="card card-body shadow-sm">
                                                <div
                                                    class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                                    <h5 class="mb-0">Health Insurance Information</h5>
                                                    <button type="button" id="add-insurance"
                                                        class="btn btn-sm btn-primary">
                                                        <i class="fas fa-plus mr-1"></i> Add Insurance
                                                    </button>
                                                </div>

                                                <div class="alert alert-info mb-3">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    Add health insurance information for the employee. Click the button
                                                    above to add a new entry.
                                                </div>

                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-hover"
                                                        id="table-insurance">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th width="25%">Insurance Type</th>
                                                                <th width="20%">Insurance No</th>
                                                                <th width="25%">Health Facility</th>
                                                                <th width="20%">Remarks</th>
                                                                <th width="10%">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- Dynamic content will be added here -->
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div class="text-muted mt-3 small">
                                                    <i class="fas fa-circle-info mr-1"></i>
                                                    Insurance types typically include BPJS Kesehatan and BPJS
                                                    Ketenagakerjaan
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Licenses Tab -->
                                        <div class="tab-pane fade" id="v-tabs-licence" role="tabpanel"
                                            aria-labelledby="v-tabs-licence-tab">
                                            <div class="card card-body shadow-sm">
                                                <div
                                                    class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                                    <h5 class="mb-0">License Information</h5>
                                                    <button type="button" id="add-license"
                                                        class="btn btn-sm btn-primary">
                                                        <i class="fas fa-plus mr-1"></i> Add License
                                                    </button>
                                                </div>

                                                <div class="alert alert-info mb-3">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    Add license information such as driving licenses or professional
                                                    certifications.
                                                </div>

                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-hover"
                                                        id="table-license">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th width="30%">License Type</th>
                                                                <th width="30%">License Number</th>
                                                                <th width="30%">Expiration Date</th>
                                                                <th width="10%">Action</th>
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
                                            </div>
                                        </div>

                                        <!-- Families Tab -->
                                        <div class="tab-pane fade" id="v-tabs-family" role="tabpanel"
                                            aria-labelledby="v-tabs-family-tab">
                                            <div class="card card-body shadow-sm">
                                                <div
                                                    class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                                    <h5 class="mb-0">Family Information</h5>
                                                    <button type="button" id="add-family"
                                                        class="btn btn-sm btn-primary">
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
                                                                <th width="5%">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- Dynamic content will be added here -->
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div class="text-muted mt-3 small">
                                                    <i class="fas fa-users mr-1"></i>
                                                    Family information is important for benefits administration and
                                                    emergency contacts
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Education Tab -->
                                        <div class="tab-pane fade" id="v-tabs-education" role="tabpanel"
                                            aria-labelledby="v-tabs-education-tab">
                                            <div class="card card-body shadow-sm">
                                                <div
                                                    class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                                    <h5 class="mb-0">Educational Background</h5>
                                                    <button type="button" id="add-education"
                                                        class="btn btn-sm btn-primary">
                                                        <i class="fas fa-plus mr-1"></i> Add Education
                                                    </button>
                                                </div>

                                                <div class="alert alert-info mb-3">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    Add formal education history of the employee (schools, universities,
                                                    etc.).
                                                </div>

                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-hover"
                                                        id="table-education">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th width="30%">Institution Name</th>
                                                                <th width="30%">Address</th>
                                                                <th width="15%">Year</th>
                                                                <th width="15%">Remarks</th>
                                                                <th width="10%">Action</th>
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
                                            </div>
                                        </div>

                                        <!-- Courses Tab -->
                                        <div class="tab-pane fade" id="v-tabs-course" role="tabpanel"
                                            aria-labelledby="v-tabs-course-tab">
                                            <div class="card card-body shadow-sm">
                                                <div
                                                    class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                                    <h5 class="mb-0">Training & Courses</h5>
                                                    <button type="button" id="add-course"
                                                        class="btn btn-sm btn-primary">
                                                        <i class="fas fa-plus mr-1"></i> Add Course
                                                    </button>
                                                </div>

                                                <div class="alert alert-info mb-3">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    Add professional training, certifications, or courses completed by
                                                    the employee.
                                                </div>

                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-hover" id="table-course">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th width="30%">Course Name</th>
                                                                <th width="30%">Institution</th>
                                                                <th width="15%">Year</th>
                                                                <th width="15%">Remarks</th>
                                                                <th width="10%">Action</th>
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
                                            </div>
                                        </div>

                                        <!-- Job Experience Tab -->
                                        <div class="tab-pane fade" id="v-tabs-jobexp" role="tabpanel"
                                            aria-labelledby="v-tabs-jobexp-tab">
                                            <div class="card card-body shadow-sm">
                                                <div
                                                    class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                                    <h5 class="mb-0">Work Experience</h5>
                                                    <button type="button" id="add-jobexp"
                                                        class="btn btn-sm btn-primary">
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
                                                                <th width="10%">Action</th>
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
                                            </div>
                                        </div>

                                        <!-- Operable Units Tab -->
                                        <div class="tab-pane fade" id="v-tabs-unit" role="tabpanel"
                                            aria-labelledby="v-tabs-unit-tab">
                                            <div class="card card-body shadow-sm">
                                                <div
                                                    class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                                    <h5 class="mb-0">Operable Units</h5>
                                                    <button type="button" id="add-operableunit"
                                                        class="btn btn-sm btn-primary">
                                                        <i class="fas fa-plus mr-1"></i> Add Unit
                                                    </button>
                                                </div>

                                                <div class="alert alert-info mb-3">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    Add equipment, vehicles, or units that the employee is qualified to
                                                    operate.
                                                </div>

                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-hover"
                                                        id="table-operableunit">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th width="30%">Unit Name</th>
                                                                <th width="30%">Unit Type</th>
                                                                <th width="30%">Remarks</th>
                                                                <th width="10%">Action</th>
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
                                            </div>
                                        </div>

                                        <!-- Emergency Calls Tab -->
                                        <div class="tab-pane fade" id="v-tabs-emergency" role="tabpanel"
                                            aria-labelledby="v-tabs-emergency-tab">
                                            <div class="card card-body shadow-sm">
                                                <div
                                                    class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                                    <h5 class="mb-0">Emergency Contacts</h5>
                                                    <button type="button" id="add-emergency"
                                                        class="btn btn-sm btn-primary">
                                                        <i class="fas fa-plus mr-1"></i> Add Contact
                                                    </button>
                                                </div>

                                                <div class="alert alert-info mb-3">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    Add emergency contact information for the employee.
                                                </div>

                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-hover"
                                                        id="table-emergency">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th width="20%">Relationship</th>
                                                                <th width="25%">Name</th>
                                                                <th width="30%">Address</th>
                                                                <th width="15%">Phone</th>
                                                                <th width="10%">Action</th>
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
                                            </div>
                                        </div>

                                        <!-- Additional Data Tab -->
                                        <div class="tab-pane fade" id="v-tabs-additional" role="tabpanel"
                                            aria-labelledby="v-tabs-additional-tab">
                                            <div class="card card-body shadow-sm">
                                                <h5 class="mb-3 border-bottom pb-2">Additional Information</h5>

                                                <div class="alert alert-info mb-3">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    Add additional personal information that may be needed for company
                                                    uniforms and equipment.
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="cloth_size" class="form-label">Clothes
                                                                Size</label>
                                                            <select
                                                                class="form-control select2bs4 @error('cloth_size') is-invalid @enderror"
                                                                id="cloth_size" name="cloth_size">
                                                                <option value="">Select Size</option>
                                                                <option value="XS"
                                                                    {{ old('cloth_size') == 'XS' ? 'selected' : '' }}>
                                                                    XS</option>
                                                                <option value="S"
                                                                    {{ old('cloth_size') == 'S' ? 'selected' : '' }}>S
                                                                </option>
                                                                <option value="M"
                                                                    {{ old('cloth_size') == 'M' ? 'selected' : '' }}>M
                                                                </option>
                                                                <option value="L"
                                                                    {{ old('cloth_size') == 'L' ? 'selected' : '' }}>L
                                                                </option>
                                                                <option value="XL"
                                                                    {{ old('cloth_size') == 'XL' ? 'selected' : '' }}>
                                                                    XL</option>
                                                                <option value="XXL"
                                                                    {{ old('cloth_size') == 'XXL' ? 'selected' : '' }}>
                                                                    XXL</option>
                                                            </select>
                                                            @if ($errors->any('cloth_size'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('cloth_size') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="pants_size" class="form-label">Pants
                                                                Size</label>
                                                            <select
                                                                class="form-control select2bs4 @error('pants_size') is-invalid @enderror"
                                                                id="pants_size" name="pants_size">
                                                                <option value="">Select Size</option>
                                                                <option value="28"
                                                                    {{ old('pants_size') == '28' ? 'selected' : '' }}>
                                                                    28</option>
                                                                <option value="30"
                                                                    {{ old('pants_size') == '30' ? 'selected' : '' }}>
                                                                    30</option>
                                                                <option value="32"
                                                                    {{ old('pants_size') == '32' ? 'selected' : '' }}>
                                                                    32</option>
                                                                <option value="34"
                                                                    {{ old('pants_size') == '34' ? 'selected' : '' }}>
                                                                    34</option>
                                                                <option value="36"
                                                                    {{ old('pants_size') == '36' ? 'selected' : '' }}>
                                                                    36</option>
                                                                <option value="38"
                                                                    {{ old('pants_size') == '38' ? 'selected' : '' }}>
                                                                    38</option>
                                                                <option value="40"
                                                                    {{ old('pants_size') == '40' ? 'selected' : '' }}>
                                                                    40</option>
                                                            </select>
                                                            @if ($errors->any('pants_size'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('pants_size') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="shoes_size" class="form-label">Shoes
                                                                Size</label>
                                                            <input type="text" value="{{ old('shoes_size') }}"
                                                                class="form-control @error('shoes_size') is-invalid @enderror"
                                                                id="shoes_size" name="shoes_size"
                                                                placeholder="Enter shoes size (e.g., 42)">
                                                            @if ($errors->any('shoes_size'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('shoes_size') }}</span>
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
                                                                    {{ old('glasses') == 'Yes' ? 'selected' : '' }}>
                                                                    Yes</option>
                                                                <option value="No"
                                                                    {{ old('glasses') == 'No' ? 'selected' : '' }}>No
                                                                </option>
                                                            </select>
                                                            @if ($errors->any('glasses'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('glasses') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="height" class="form-label">Height
                                                                (cm)</label>
                                                            <div class="input-group">
                                                                <input type="number" value="{{ old('height') }}"
                                                                    class="form-control @error('height') is-invalid @enderror"
                                                                    id="height" name="height"
                                                                    placeholder="Enter height">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text">cm</span>
                                                                </div>
                                                            </div>
                                                            @if ($errors->any('height'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('height') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="weight" class="form-label">Weight
                                                                (kg)</label>
                                                            <div class="input-group">
                                                                <input type="number" value="{{ old('weight') }}"
                                                                    class="form-control @error('weight') is-invalid @enderror"
                                                                    id="weight" name="weight"
                                                                    placeholder="Enter weight">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text">kg</span>
                                                                </div>
                                                            </div>
                                                            @if ($errors->any('weight'))
                                                            <span
                                                                class="text-danger">{{ $errors->first('weight') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Images Tab -->
                                        <div class="tab-pane fade" id="v-tabs-image" role="tabpanel"
                                            aria-labelledby="v-tabs-image-tab">
                                            <div class="card card-body shadow-sm">
                                                <h5 class="mb-3 border-bottom pb-2">Employee Images</h5>

                                                <div class="alert alert-info mb-3">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    Upload employee images including ID cards, profile photos, and other
                                                    relevant documents.
                                                </div>

                                                <div class="form-group">
                                                    <label for="images" class="form-label">Upload Images</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input"
                                                            id="images" name="filename[]" multiple>
                                                        <label class="custom-file-label" for="images">Choose
                                                            files</label>
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        Supported formats: JPG, PNG, GIF. Maximum file size: 2MB.
                                                    </small>
                                                </div>

                                                <div class="card mt-3 bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title">
                                                            <i class="fas fa-lightbulb text-warning mr-2"></i>Image
                                                            Guidelines
                                                        </h6>
                                                        <ul class="mb-0 pl-3">
                                                            <li>Profile photos should be clear and professional</li>
                                                            <li>ID card images must be legible</li>
                                                            <li>All uploads must be appropriate for workplace use</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
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
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
    .nav-tabs-vertical {
        border-right: 1px solid #dee2e6;
        height: 100%;
    }

    .nav-tabs-vertical .nav-link {
        border-radius: 0.25rem 0 0 0.25rem;
        border-right: none;
        margin-bottom: 5px;
        transition: all 0.3s;
        position: relative;
    }

    .nav-tabs-vertical .nav-link.active {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }

    .nav-tabs-vertical .nav-link.active::after {
        content: '';
        position: absolute;
        right: -10px;
        top: 50%;
        transform: translateY(-50%);
        border-top: 10px solid transparent;
        border-bottom: 10px solid transparent;
        border-left: 10px solid #007bff;
    }

    .nav-tabs-vertical .nav-link:not(.active):hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }

    .tab-pane {
        animation: fadeIn 0.5s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-group label {
        font-weight: 500;
        color: #555;
    }

    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .required-field::after {
        content: " *";
        color: red;
    }

    .sticky-controls {
        position: sticky;
        bottom: 0;
        background: white;
        padding: 15px 0;
        border-top: 1px solid #dee2e6;
        z-index: 100;
    }
</style>
@endsection

@section('scripts')
<!-- Select2 -->
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
<!-- SweetAlert2 -->
<script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
    $(function() {
        // Initialize select2bs4 for all select elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        // Focus search field when select2 opens
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Reinitialize select2 after adding new rows
        function initializeSelect2(container) {
            $(container).find('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        }

        //  insurances
        $("#add-insurance").on('click', function() {
            addInsuranceDetail();
            initializeSelect2('#table-insurance tr:last');
        });

        function addInsuranceDetail() {
            var tr =
                `<tr>
              <td>
                <select name="health_insurance_type[]" class="form-control select2bs4" style="width: 100%;" required>
                    <option value="">-Select Insurance-</option>
                    <option value="BPJS Kesehatan">BPJS Kesehatan</option>
                    <option value="BPJS Ketenagakerjaan">BPJS Ketenagakerjaan</option>
                </select>
              </td>
              <td>
                <input type="text" class="form-control" name="health_insurance_no[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="health_facility[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="health_insurance_remarks[]" required>
              </td>
              <td>
                <button type="button" class="btn btn-outline-danger remove-input-field"><i class="fas fa-trash-alt"></i></button>
              </td>
            </tr>`;
            $("#table-insurance").append(tr);
        };

        //  families
        $("#add-family").on('click', function() {
            addFamilyDetail();
            initializeSelect2('#table-family tr:last');
        });

        function addFamilyDetail() {
            var tr =
                `<tr>
              <td>
                <select name="family_relationship[]" class="form-control select2bs4" style="width: 100%;">
                    <option value="Husband">Husband</option>
                    <option value="Wife">Wife</option>
                    <option value="Child">Child</option>
                </select>
              </td>
              <td>
                <input type="text" class="form-control" name="family_name[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="family_birthplace[]" required>
              </td>
              <td>
                <input type="date" class="form-control" name="family_birthdate[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="family_remarks[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="bpjsks_no[]">
              </td>
              <td>
                <button type="button" class="btn btn-outline-danger remove-input-field"><i class="fas fa-trash-alt"></i></button>
              </td>
            </tr>`;
            $("#table-family").append(tr);
        };

        // units
        $("#add-operableunit").on('click', function() {
            addOperableunitDetail();
            initializeSelect2('#table-operableunit tr:last');
        });

        function addOperableunitDetail() {
            var tr =
                `<tr>
              <td>
                <select name="unit_name[]" class="form-control select2bs4" style="width: 100%;">
                    <option value="LV / SARANA">LV / SARANA</option>
                    <option value="DUMP TRUCK">DUMP TRUCK</option>
                    <option value="ADT">ADT</option>
                    <option value="EXCAVATOR">EXCAVATOR</option>
                    <option value="DOZER">DOZER</option>
                    <option value="GRADER">GRADER</option>
                </select>
              </td>
              <td>
                <input type="text" class="form-control" name="unit_type[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="unit_remarks[]" required>
              </td>
              <td>
                <button type="button" class="btn btn-outline-danger remove-input-field"><i class="fas fa-trash-alt"></i></button>
              </td>
            </tr>`;
            $("#table-operableunit").append(tr);
        };

        $(document).on('click', '.remove-input-field', function() {
            $(this).parents('tr').remove();
        });

        // autofill department placeholder based on position_id
        $('#position_id').on('change', function() {
            var position_id = $(this).val();
            $.ajax({
                url: "{{ route('employees.getDepartment') }}",
                type: "GET",
                data: {
                    position_id: position_id
                },
                success: function(data) {
                    $('#department').val(data.department_name);
                }
            });
        });

        // educations
        $("#add-education").on('click', function() {
            addEducationDetail();
        });

        function addEducationDetail() {
            var tr =
                `<tr>
              <td>
                <input type="text" class="form-control" name="education_name[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="education_address[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="education_year[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="education_remarks[]" required>
              </td>
              <td>
                <button type="button" class="btn btn-outline-danger remove-input-field"><i class="fas fa-trash-alt"></i></button>
              </td>
            </tr>`;
            $("#table-education").append(tr);
        };

        // courses
        $("#add-course").on('click', function() {
            addCourseDetail();
        });

        function addCourseDetail() {
            var tr =
                `<tr>
              <td>
                <input type="text" class="form-control" name="course_name[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="course_address[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="course_year[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="course_remarks[]" required>
              </td>
              <td>
                <button type="button" class="btn btn-outline-danger remove-input-field"><i class="fas fa-trash-alt"></i></button>
              </td>
            </tr>`;
            $("#table-course").append(tr);
        };

        // jobexps
        $("#add-jobexp").on('click', function() {
            addJobexpDetail();
        });

        function addJobexpDetail() {
            var tr =
                `<tr>
              <td>
                <input type="text" class="form-control" name="company_name[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="company_address[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="job_position[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="job_duration[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="quit_reason[]" required>
              </td>
              <td>
                <button type="button" class="btn btn-outline-danger remove-input-field"><i class="fas fa-trash-alt"></i></button>
              </td>
            </tr>`;
            $("#table-jobexp").append(tr);
        };

        // license
        $("#add-license").on('click', function() {
            addLicenseDetail();
        });

        function addLicenseDetail() {
            var tr =
                `<tr>
              <td>
                <input type="text" class="form-control" name="driver_license_type[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="driver_license_no[]" required>
              </td>
              <td>
                <input type="date" class="form-control" name="driver_license_exp[]" required>
              </td>
              <td>
                <button type="button" class="btn btn-outline-danger remove-input-field"><i class="fas fa-trash-alt"></i></button>
              </td>
            </tr>`;
            $("#table-license").append(tr);
        };

        // emergency
        $("#add-emergency").on('click', function() {
            addEmergencyDetail();
        });

        function addEmergencyDetail() {
            var tr =
                `<tr>
              <td>
                <input type="text" class="form-control" name="emrg_call_relation[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="emrg_call_name[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="emrg_call_address[]" required>
              </td>
              <td>
                <input type="text" class="form-control" name="emrg_call_phone[]" required>
              </td>
              <td>
                <button type="button" class="btn btn-outline-danger remove-input-field"><i class="fas fa-trash-alt"></i></button>
              </td>
            </tr>`;
            $("#table-emergency").append(tr);
        };
    });
</script>
@endsection
