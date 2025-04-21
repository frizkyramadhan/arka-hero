<div class="modal fade text-left" id="modal-employee-{{ $employee->id }}">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Employee - Personal Detail</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('employees/' . $employee->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="card-body">
                        <!-- Personal Information Section -->
                        <h5 class="mb-3 border-bottom pb-2">Personal Information</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fullname" class="form-label required-field">Full
                                        Name</label>
                                    <input type="text" value="{{ old('fullname', $employee->fullname) }}"
                                        class="form-control @error('fullname') is-invalid @enderror" id="fullname"
                                        name="fullname" autofocus="true" placeholder="Enter full name">
                                    @if ($errors->any('fullname'))
                                        <span class="text-danger">{{ $errors->first('fullname') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="identity_card" class="form-label required-field">Identity
                                        Card</label>
                                    <input type="text" value="{{ old('identity_card', $employee->identity_card) }}"
                                        class="form-control @error('identity_card') is-invalid @enderror"
                                        id="identity_card" name="identity_card" placeholder="Enter KTP number">
                                    @if ($errors->any('identity_card'))
                                        <span class="text-danger">{{ $errors->first('identity_card') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nationality" class="form-label">Nationality</label>
                                    <input type="text" value="{{ old('nationality', $employee->nationality) }}"
                                        class="form-control @error('nationality') is-invalid @enderror" id="nationality"
                                        name="nationality" placeholder="Enter nationality">
                                    @if ($errors->any('nationality'))
                                        <span class="text-danger">{{ $errors->first('nationality') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3">Birth Information</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="emp_pob" class="form-label required-field">Place of
                                        Birth</label>
                                    <input type="text" value="{{ old('emp_pob', $employee->emp_pob) }}"
                                        class="form-control @error('emp_pob') is-invalid @enderror" id="emp_pob"
                                        name="emp_pob" placeholder="Enter birth place">
                                    @if ($errors->any('emp_pob'))
                                        <span class="text-danger">{{ $errors->first('emp_pob') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="emp_dob" class="form-label required-field">Date of
                                        Birth</label>
                                    <input type="date" value="{{ old('emp_dob', $employee->emp_dob) }}"
                                        class="form-control @error('emp_dob') is-invalid @enderror" id="emp_dob"
                                        name="emp_dob">
                                    @if ($errors->any('emp_dob'))
                                        <span class="text-danger">{{ $errors->first('emp_dob') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="blood_type" class="form-label">Blood Type</label>
                                    <select class="form-control select2bs4 @error('blood_type') is-invalid @enderror"
                                        name="blood_type" id="blood_type">
                                        <option value="">Select blood type</option>
                                        <option value="A"
                                            {{ old('blood_type', $employee->blood_type) == 'A' ? 'selected' : '' }}>A
                                        </option>
                                        <option value="B"
                                            {{ old('blood_type', $employee->blood_type) == 'B' ? 'selected' : '' }}>B
                                        </option>
                                        <option value="AB"
                                            {{ old('blood_type', $employee->blood_type) == 'AB' ? 'selected' : '' }}>AB
                                        </option>
                                        <option value="O"
                                            {{ old('blood_type', $employee->blood_type) == 'O' ? 'selected' : '' }}>O
                                        </option>
                                    </select>
                                    @if ($errors->any('blood_type'))
                                        <span class="text-danger">{{ $errors->first('blood_type') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3">Personal Details</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="religion_id" class="form-label">Religion</label>
                                    <select name="religion_id"
                                        class="form-control select2bs4 @error('religion_id') is-invalid @enderror">
                                        <option value="">Select Religion</option>
                                        @foreach ($religions as $religion)
                                            <option value="{{ $religion->id }}"
                                                {{ old('religion_id', $employee->religion_id) == $religion->id ? 'selected' : '' }}>
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
                                            <input class="custom-control-input" type="radio" id="gender_male"
                                                name="gender" value="male"
                                                {{ old('gender', $employee->gender) == 'male' ? 'checked' : '' }}>
                                            <label for="gender_male" class="custom-control-label">Male</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            <input class="custom-control-input" type="radio" id="gender_female"
                                                name="gender" value="female"
                                                {{ old('gender', $employee->gender) == 'female' ? 'checked' : '' }}>
                                            <label for="gender_female" class="custom-control-label">Female</label>
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
                                    <select class="form-control select2bs4 @error('marital') is-invalid @enderror"
                                        name="marital" id="marital">
                                        <option value="">Select marital status</option>
                                        <option value="Single"
                                            {{ old('marital', $employee->marital) == 'Single' ? 'selected' : '' }}>
                                            Single
                                        </option>
                                        <option value="Married"
                                            {{ old('marital', $employee->marital) == 'Married' ? 'selected' : '' }}>
                                            Married
                                        </option>
                                        <option value="Divorced"
                                            {{ old('marital', $employee->marital) == 'Divorced' ? 'selected' : '' }}>
                                            Divorced
                                        </option>
                                        <option value="Widowed"
                                            {{ old('marital', $employee->marital) == 'Widowed' ? 'selected' : '' }}>
                                            Widowed
                                        </option>
                                    </select>
                                    @if ($errors->any('marital'))
                                        <span class="text-danger">{{ $errors->first('marital') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3">Contact Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        </div>
                                        <input type="text" value="{{ old('phone', $employee->phone) }}"
                                            class="form-control @error('phone') is-invalid @enderror" id="phone"
                                            name="phone" placeholder="Enter phone number">
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
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        </div>
                                        <input type="email" value="{{ old('email', $employee->email) }}"
                                            class="form-control @error('email') is-invalid @enderror" id="email"
                                            name="email" placeholder="Enter email address">
                                    </div>
                                    @if ($errors->any('email'))
                                        <span class="text-danger">{{ $errors->first('email') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3">Address Information</h5>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="address" class="form-label">Street Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2"
                                        placeholder="Enter street address">{{ old('address', $employee->address) }}</textarea>
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
                                    <input type="text" value="{{ old('village', $employee->village) }}"
                                        class="form-control @error('village') is-invalid @enderror" id="village"
                                        name="village" placeholder="Desa/Dusun">
                                    @if ($errors->any('village'))
                                        <span class="text-danger">{{ $errors->first('village') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ward" class="form-label">Ward</label>
                                    <input type="text" value="{{ old('ward', $employee->ward) }}"
                                        class="form-control @error('ward') is-invalid @enderror" id="ward"
                                        name="ward" placeholder="Kelurahan">
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
                                    <input type="text" value="{{ old('district', $employee->district) }}"
                                        class="form-control @error('district') is-invalid @enderror" id="district"
                                        name="district" placeholder="Kecamatan">
                                    @if ($errors->any('district'))
                                        <span class="text-danger">{{ $errors->first('district') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" value="{{ old('city', $employee->city) }}"
                                        class="form-control @error('city') is-invalid @enderror" id="city"
                                        name="city" placeholder="Kota/Kabupaten">
                                    @if ($errors->any('city'))
                                        <span class="text-danger">{{ $errors->first('city') }}</span>
                                    @endif
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
