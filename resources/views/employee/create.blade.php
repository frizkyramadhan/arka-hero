@extends('layouts.main')
@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Employee</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">Home</a></li>
          <li class="breadcrumb-item active">Employee</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>

<!-- /.content-header -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <strong>{{ $subtitle }}</strong>
            </h3>
            <div class="card-tools">
              <ul class="nav nav-pills ml-auto">
                <li class="nav-item mr-2">
                  <a href="{{ url('employees') }}" class="btn btn-warning"><i class="fas fa-undo"></i>
                    Back</a>
                </li>
              </ul>
            </div>
          </div><!-- /.card-header -->
          <div class="card-body">
            <form method="POST" action="{{ url('employees') }}" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-12">
                  <div class="form-group">
                    <button type="submit" class="btn btn-primary float-right">Submit</button>
                  </div>
                </div>
                <div class="col-5 col-sm-3">
                  <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
                    <a class="nav-link active" id="vert-tabs-employee-tab" data-toggle="pill" href="#vert-tabs-employee" role="tab" aria-controls="vert-tabs-employee" aria-selected="true">Personal Detail</a>
                    <a class="nav-link" id="vert-tabs-bank-tab" data-toggle="pill" href="#vert-tabs-bank" role="tab" aria-controls="vert-tabs-bank" aria-selected="false">Bank Accounts</a>
                    <a class="nav-link" id="vert-tabs-insurance-tab" data-toggle="pill" href="#vert-tabs-insurance" role="tab" aria-controls="vert-tabs-insurance" aria-selected="false">Health Insurance</a>
                    <a class="nav-link" id="vert-tabs-family-tab" data-toggle="pill" href="#vert-tabs-family" role="tab" aria-controls="vert-tabs-family" aria-selected="false">Families</a>
                    <a class="nav-link" id="vert-tabs-education-tab" data-toggle="pill" href="#vert-tabs-education" role="tab" aria-controls="vert-tabs-education" aria-selected="false">Educations</a>
                    <a class="nav-link" id="vert-tabs-course-tab" data-toggle="pill" href="#vert-tabs-course" role="tab" aria-controls="vert-tabs-course" aria-selected="false">Courses</a>
                    <a class="nav-link" id="vert-tabs-jobexp-tab" data-toggle="pill" href="#vert-tabs-jobexp" role="tab" aria-controls="vert-tabs-jobexp" aria-selected="false">Job Experiences</a>
                    <a class="nav-link" id="vert-tabs-unit-tab" data-toggle="pill" href="#vert-tabs-unit" role="tab" aria-controls="vert-tabs-unit" aria-selected="false">Operable Units</a>
                    <a class="nav-link" id="vert-tabs-licence-tab" data-toggle="pill" href="#vert-tabs-licence" role="tab" aria-controls="vert-tabs-licence" aria-selected="false">Licences</a>
                    <a class="nav-link" id="vert-tabs-emergency-tab" data-toggle="pill" href="#vert-tabs-emergency" role="tab" aria-controls="vert-tabs-emergency" aria-selected="false">Emergency Calls</a>
                    <a class="nav-link" id="vert-tabs-additional-tab" data-toggle="pill" href="#vert-tabs-additional" role="tab" aria-controls="vert-tabs-additional" aria-selected="false">Additional Data</a>
                    <a class="nav-link" id="vert-tabs-administration-tab" data-toggle="pill" href="#vert-tabs-administration" role="tab" aria-controls="vert-tabs-administration" aria-selected="false">Administrations</a>
                    <a class="nav-link" id="vert-tabs-taxidentification-tab" data-toggle="pill" href="#vert-tabs-taxidentification" role="tab" aria-controls="vert-tabs-taxidentification" aria-selected="false">Tax Identification</a>
                    <a class="nav-link" id="vert-tabs-image-tab" data-toggle="pill" href="#vert-tabs-image" role="tab" aria-controls="vert-tabs-image" aria-selected="false">Images</a>
                  </div>
                </div>
                <div class="col-7 col-sm-9">
                  <div class="tab-content" id="vert-tabs-tabContent">
                    <div class="tab-pane text-left fade show active" id="vert-tabs-employee" role="tabpanel" aria-labelledby="vert-tabs-employee-tab">
                      <div class="row">
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input type="text" value="{{ old('fullname') }}" class="form-control @error('fullname') is-invalid @enderror" id="fullname" name="fullname" autofocus="true">
                            @if ($errors->any('fullname'))
                            <span class="text-danger">{{ ($errors->first('fullname')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="emp_pob" class="form-label">POB</label>
                            <input type="text" value="{{ old('emp_pob') }}" class="form-control @error('emp_pob') is-invalid @enderror" id="emp_pob" name="emp_pob">
                            @if ($errors->any('emp_pob'))
                            <span class="text-danger">{{ ($errors->first('emp_pob')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="emp_dob" class="form-label">DOB</label>
                            <input type="date" value="{{ old('emp_dob') }}" class="form-control @error('emp_dob') is-invalid @enderror" id="emp_dob" placeholder="" name="emp_dob">
                            @if ($errors->any('emp_dob'))
                            <span class="text-danger">{{ ($errors->first('emp_dob')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="blood_type" class="form-label">Blood Type</label>
                            <input type="text" value="{{ old('blood_type') }}" class="form-control @error('blood_type') is-invalid @enderror" name="blood_type" id="blood_type" placeholder="A, B, AB, O" style="text-transform:uppercase">
                            @if ($errors->any('blood_type'))
                            <span class="text-danger">{{ ($errors->first('blood_type')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="religion_id" class="form-label">Religion</label>
                            <select name="religion_id" class="form-control @error('religion_id') is-invalid @enderror">
                              @foreach ($religions as $religions)
                              <option value="{{ $religions->id }}" {{ old('religion_id') == $religions->id ? 'selected' : '' }}>
                                {{ $religions->religion_name }}
                              </option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="nationality" class="form-label">Nationality</label>
                            <input type="text" value="{{ old('nationality') }}" class="form-control @error('nationality') is-invalid @enderror" id="nationality" name="nationality">
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="blood_type" class="form-label">Gender</label>
                            <select name="gender" class="form-control @error('gender') is-invalid @enderror">
                              <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male
                              </option>
                              <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female
                              </option>
                            </select>
                            @error('gender')
                            <div class="invalid-feedback">
                              {{ $message }}
                            </div>
                            @enderror
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="marital" class="form-label">Marital</label>
                            <input type="text" value="{{ old('marital') }}" class="form-control @error('marital') is-invalid @enderror" id="marital" name="marital">
                            @if ($errors->any('marital'))
                            <span class="text-danger">{{ ($errors->first('marital')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" value="{{ old('address') }}" class="form-control @error('address') is-invalid @enderror" id="address" name="address" placeholder="Jalan">
                            @if ($errors->any('address'))
                            <span class="text-danger">{{ ($errors->first('address')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="village" class="form-label">Village</label>
                            <input type="text" value="{{ old('village') }}" class="form-control @error('village') is-invalid @enderror" id="village" name="village" placeholder="Desa/Dusun">
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="ward" class="form-label">Ward</label>
                            <input type="text" value="{{ old('ward') }}" class="form-control @error('ward') is-invalid @enderror" id="ward" name="ward" placeholder="Kelurahan">
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="district" class="form-label">District</label>
                            <input type="text" value="{{ old('district') }}" class="form-control @error('district') is-invalid @enderror" id="district" name="district" placeholder="Kecamatan">
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="city" class="form-label">City</label>
                            <input type="text" value="{{ old('city') }}" class="form-control @error('city') is-invalid @enderror" id="city" name="city" placeholder="Kota/Kabupaten">
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone">
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="text" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" id="email" name="email">
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label for="identity_card" class="form-label">Identity Card</label>
                            <input type="text" value="{{ old('identity_card') }}" class="form-control @error('identity_card') is-invalid @enderror" id="identity_card" name="identity_card" placeholder="No. KTP">
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="vert-tabs-bank" role="tabpanel" aria-labelledby="vert-tabs-bank-tab">
                      <div class="row">
                        <div class="col-md-8">
                          <div class="form-group">
                            <label for="bank_id" class="form-label">Bank</label>
                            <select name="bank_id" class="form-control @error('bank_id') is-invalid @enderror">
                              <option value="">-Select Bank-</option>
                              @foreach ($banks as $bank)
                              <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>
                                {{ $bank->bank_name }}
                              </option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="col-md-8">
                          <div class="form-group">
                            <label for="bank_account_no" class="form-label">Account No</label>
                            <input type="number" value="{{ old('bank_account_no') }}" class="form-control @error('bank_account_no') is-invalid @enderror" id="bank_account_no" name="bank_account_no">
                            @if ($errors->any('bank_account_no'))
                            <span class="text-danger">{{ ($errors->first('bank_account_no')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-8">
                          <div class="form-group">
                            <label for="bank_account_name" class="form-label">Account Name</label>
                            <input type="text" value="{{ old('bank_account_name') }}" class="form-control @error('bank_account_name') is-invalid @enderror" id="bank_account_name" name="bank_account_name">
                            @if ($errors->any('bank_account_name'))
                            <span class="text-danger">{{ ($errors->first('bank_account_name')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-8">
                          <div class="form-group">
                            <label for="bank_account_branch" class="form-label">Branch</label>
                            <input type="text" value="{{ old('bank_account_branch') }}" class="form-control @error('bank_account_branch') is-invalid @enderror" id="bank_account_branch" name="bank_account_branch">
                            @if ($errors->any('bank_account_branch'))
                            <span class="text-danger">{{ ($errors->first('bank_account_branch')) }}</span>
                            @endif
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="vert-tabs-insurance" role="tabpanel" aria-labelledby="vert-tabs-insurance-tab">
                      <div class="row">
                        <div class="table-responsive mt-3">
                          <table class="table table-sm table-bordered" id="table-insurance">
                            <thead>
                              <tr>
                                <th class="align-middle">Insurance</th>
                                <th class="align-middle">Insurance No</th>
                                <th class="align-middle">Health Facility</th>
                                <th class="align-middle">Remarks</th>
                                <th style="width: 40px"><button type="button" id="add-insurance" class="btn btn-outline-primary"><i class="fas fa-plus"></i></button></th>
                              </tr>
                            </thead>
                          </table>
                        </div>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="vert-tabs-family" role="tabpanel" aria-labelledby="vert-tabs-family-tab">
                      <div class="row">
                        <div class="table-responsive mt-3">
                          <table class="table table-sm table-bordered" id="table-family">
                            <thead>
                              <tr>
                                <th class="align-middle">Relationship</th>
                                <th class="align-middle">Name</th>
                                <th class="align-middle">Birth Place</th>
                                <th class="align-middle">Birth Date</th>
                                <th class="align-middle">Remarks</th>
                                <th style="width: 40px"><button type="button" id="add-family" class="btn btn-outline-primary"><i class="fas fa-plus"></i></button></th>
                              </tr>
                            </thead>
                          </table>
                        </div>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="vert-tabs-education" role="tabpanel" aria-labelledby="vert-tabs-education-tab">
                      <div class="row">
                        <div class="table-responsive mt-3">
                          <table class="table table-sm table-bordered" id="table-education">
                            <thead>
                              <tr>
                                <th class="align-middle">Name</th>
                                <th class="align-middle">Address</th>
                                <th class="align-middle">Year</th>
                                <th class="align-middle">Remarks</th>
                                <th style="width: 40px"><button type="button" id="add-education" class="btn btn-outline-primary"><i class="fas fa-plus"></i></button></th>
                              </tr>
                            </thead>
                          </table>
                        </div>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="vert-tabs-course" role="tabpanel" aria-labelledby="vert-tabs-course-tab">
                      <div class="row">
                        <div class="table-responsive mt-3">
                          <table class="table table-sm table-bordered" id="table-course">
                            <thead>
                              <tr>
                                <th class="align-middle">Name</th>
                                <th class="align-middle">Address</th>
                                <th class="align-middle">Year</th>
                                <th class="align-middle">Remarks</th>
                                <th style="width: 40px"><button type="button" id="add-course" class="btn btn-outline-primary"><i class="fas fa-plus"></i></button></th>
                              </tr>
                            </thead>
                          </table>
                        </div>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="vert-tabs-jobexp" role="tabpanel" aria-labelledby="vert-tabs-jobexp-tab">
                      <div class="row">
                        <div class="table-responsive mt-3">
                          <table class="table table-sm table-bordered" id="table-jobexp">
                            <thead>
                              <tr>
                                <th class="align-middle">Name</th>
                                <th class="align-middle">Address</th>
                                <th class="align-middle">Position</th>
                                <th class="align-middle">Periode</th>
                                <th class="align-middle">Quit Reason</th>
                                <th style="width: 40px"><button type="button" id="add-jobexp" class="btn btn-outline-primary"><i class="fas fa-plus"></i></button></th>
                              </tr>
                            </thead>
                          </table>
                        </div>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="vert-tabs-unit" role="tabpanel" aria-labelledby="vert-tabs-unit-tab">
                      <div class="row">
                        <div class="table-responsive mt-3">
                          <table class="table table-sm table-bordered" id="table-operableunit">
                            <thead>
                              <tr>
                                <th class="align-middle">Unit Name</th>
                                <th class="align-middle">Unit Type</th>
                                <th class="align-middle">Remarks</th>
                                <th style="width: 40px"><button type="button" id="add-operableunit" class="btn btn-outline-primary"><i class="fas fa-plus"></i></button></th>
                              </tr>
                            </thead>
                          </table>
                        </div>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="vert-tabs-licence" role="tabpanel" aria-labelledby="vert-tabs-licence-tab">
                      <div class="row">
                        <div class="table-responsive mt-3">
                          <table class="table table-sm table-bordered" id="table-license">
                            <thead>
                              <tr>
                                <th class="align-middle">License Type</th>
                                <th class="align-middle">License No</th>
                                <th class="align-middle">Expire Date</th>
                                <th style="width: 40px"><button type="button" id="add-license" class="btn btn-outline-primary"><i class="fas fa-plus"></i></button></th>
                              </tr>
                            </thead>
                          </table>
                        </div>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="vert-tabs-emergency" role="tabpanel" aria-labelledby="vert-tabs-emergency-tab">
                      <div class="row">
                        <div class="table-responsive mt-3">
                          <table class="table table-sm table-bordered" id="table-emergency">
                            <thead>
                              <tr>
                                <th class="align-middle">Status</th>
                                <th class="align-middle">Name</th>
                                <th class="align-middle">Address</th>
                                <th class="align-middle">Phone</th>
                                <th style="width: 40px"><button type="button" id="add-emergency" class="btn btn-outline-primary"><i class="fas fa-plus"></i></button></th>
                              </tr>
                            </thead>
                          </table>
                        </div>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="vert-tabs-additional" role="tabpanel" aria-labelledby="vert-tabs-additional-tab">
                      <div class="row">
                        <div class="col-md-8">
                          <div class="form-group">
                            <label for="cloth_size" class="form-label">Clothes Size</label>
                            <input type="text" value="{{ old('cloth_size') }}" class="form-control @error('cloth_size') is-invalid @enderror" id="cloth_size" name="cloth_size">
                            @if ($errors->any('cloth_size'))
                            <span class="text-danger">{{ ($errors->first('cloth_size')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-8">
                          <div class="form-group">
                            <label for="pants_size" class="form-label">Pants Size</label>
                            <input type="text" value="{{ old('pants_size') }}" class="form-control @error('pants_size') is-invalid @enderror" id="pants_size" name="pants_size">
                            @if ($errors->any('pants_size'))
                            <span class="text-danger">{{ ($errors->first('pants_size')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-8">
                          <div class="form-group">
                            <label for="shoes_size" class="form-label">Shoes Size</label>
                            <input type="text" value="{{ old('shoes_size') }}" class="form-control @error('shoes_size') is-invalid @enderror" id="shoes_size" name="shoes_size">
                            @if ($errors->any('shoes_size'))
                            <span class="text-danger">{{ ($errors->first('shoes_size')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-8">
                          <div class="form-group">
                            <label for="height" class="form-label">Height</label>
                            <input type="text" value="{{ old('height') }}" class="form-control @error('height') is-invalid @enderror" id="height" name="height">
                            @if ($errors->any('height'))
                            <span class="text-danger">{{ ($errors->first('height')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-8">
                          <div class="form-group">
                            <label for="weight" class="form-label">Weight</label>
                            <input type="text" value="{{ old('weight') }}" class="form-control @error('weight') is-invalid @enderror" id="weight" name="weight">
                            @if ($errors->any('weight'))
                            <span class="text-danger">{{ ($errors->first('weight')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-8">
                          <div class="form-group">
                            <label for="glasses" class="form-label">Glasses</label>
                            <input type="text" value="{{ old('glasses') }}" class="form-control @error('glasses') is-invalid @enderror" id="glasses" name="glasses">
                            @if ($errors->any('glasses'))
                            <span class="text-danger">{{ ($errors->first('glasses')) }}</span>
                            @endif
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="vert-tabs-administration" role="tabpanel" aria-labelledby="vert-tabs-administration-tab">
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="nik" class="form-label">NIK</label>
                            <input type="text" value="{{ old('nik') }}" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik">
                            @if ($errors->any('nik'))
                            <span class="text-danger">{{ ($errors->first('nik')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="pants_size" class="form-label">Class</label>
                            <div class="row mt-2">
                              <div class="col-6">
                                <div class="custom-control custom-radio">
                                  <input class="custom-control-input" type="radio" id="class1" name="class" value="Staff" {{ old('class') == "Staff" ? 'checked' : '' }}>
                                  <label for="class1" class="custom-control-label">Staff</label>
                                </div>
                              </div>
                              <div class="col-6">
                                <div class="custom-control custom-radio">
                                  <input class="custom-control-input" type="radio" id="class2" name="class" value="Non Staff" {{ old('class') == "Non Staff" ? 'checked' : '' }}>
                                  <label for="class2" class="custom-control-label">Non Staff</label>
                                </div>
                              </div>
                              @if ($errors->any('class'))
                              <span class="text-danger">{{ ($errors->first('class')) }}</span>
                              @endif
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="doh" class="form-label">DOH</label>
                            <input type="date" value="{{ old('doh') }}" class="form-control @error('doh') is-invalid @enderror" id="doh" name="doh">
                            @if ($errors->any('doh'))
                            <span class="text-danger">{{ ($errors->first('doh')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="foc" class="form-label">FOC</label>
                            <input type="date" value="{{ old('foc') }}" class="form-control @error('foc') is-invalid @enderror" id="foc" name="foc">
                            @if ($errors->any('foc'))
                            <span class="text-danger">{{ ($errors->first('foc')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="poh" class="form-label">POH</label>
                            <input type="text" value="{{ old('poh') }}" class="form-control @error('poh') is-invalid @enderror" id="poh" name="poh">
                            @if ($errors->any('poh'))
                            <span class="text-danger">{{ ($errors->first('poh')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="basic_salary" class="form-label">Basic Salary</label>
                            <input type="number" value="{{ old('basic_salary') }}" class="form-control @error('basic_salary') is-invalid @enderror" id="basic_salary" name="basic_salary">
                            @if ($errors->any('basic_salary'))
                            <span class="text-danger">{{ ($errors->first('basic_salary')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="position_id" class="form-label">Position</label>
                            <select id="position_id" name="position_id" class="form-control @error('position_id') is-invalid @enderror select2bs4">
                              <option value="">-Select Position-</option>
                              @foreach ($positions as $position)
                              <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>
                                {{ $position->position_name }}
                              </option>
                              @endforeach
                            </select>
                            @if ($errors->any('position_id'))
                            <span class="text-danger">{{ ($errors->first('position_id')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="site_allowance" class="form-label">Site Allowance</label>
                            <input type="number" value="{{ old('site_allowance') }}" class="form-control @error('site_allowance') is-invalid @enderror" id="site_allowance" name="site_allowance">
                            @if ($errors->any('site_allowance'))
                            <span class="text-danger">{{ ($errors->first('site_allowance')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" value="{{ old('department') }}" class="form-control @error('department') is-invalid @enderror" id="department" name="department" readonly>
                            @if ($errors->any('department'))
                            <span class="text-danger">{{ ($errors->first('department')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="other_allowance" class="form-label">Other Allowance</label>
                            <input type="number" value="{{ old('other_allowance') }}" class="form-control @error('other_allowance') is-invalid @enderror" id="other_allowance" name="other_allowance">
                            @if ($errors->any('other_allowance'))
                            <span class="text-danger">{{ ($errors->first('other_allowance')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="project_id" class="form-label">Project</label>
                            <select name="project_id" class="form-control @error('project_id') is-invalid @enderror select2bs4">
                              <option value="">-Select Project-</option>
                              @foreach ($projects as $project)
                              <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->project_code }} - {{ $project->project_name }}
                              </option>
                              @endforeach
                            </select>
                            @if ($errors->any('project_id'))
                            <span class="text-danger">{{ ($errors->first('project_id')) }}</span>
                            @endif
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="vert-tabs-taxidentification" role="tabpanel" aria-labelledby="vert-tabs-taxidentification-tab">
                      <div class="row">
                        <div class="col-md-8">
                          <div class="form-group">
                            <label for="tax_no" class="form-label">Tax No</label>
                            <input type="number" value="{{ old('tax_no') }}" class="form-control @error('tax_no') is-invalid @enderror" id="tax_no" name="tax_no">
                            @if ($errors->any('tax_no'))
                            <span class="text-danger">{{ ($errors->first('tax_no')) }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="col-md-8">
                          <div class="form-group">
                            <label for="tax_valid_date" class="form-label">Tax Valid Date</label>
                            <input type="date" value="{{ old('tax_valid_date') }}" class="form-control @error('tax_valid_date') is-invalid @enderror" id="tax_valid_date" name="tax_valid_date">
                            @if ($errors->any('tax_valid_date'))
                            <span class="text-danger">{{ ($errors->first('tax_valid_date')) }}</span>
                            @endif
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="vert-tabs-image" role="tabpanel" aria-labelledby="vert-tabs-image-tab">
                      <div class="form-group">
                        <div class="input-group">
                          <div>
                            <input type="file" name="filename[]" multiple>
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
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </div>
  <!-- /.container-fluid -->

  <a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top">
    <i class="fas fa-chevron-up"></i>
  </a>
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
    //  insurances
    $("#add-insurance").on('click', function() {
      addInsuranceDetail();
    });

    function addInsuranceDetail() {
      var tr =
        `<tr>
              <td>
                <select name="health_insurance_type[]" class="form-control" style="width: 100%;" required>
                    <option value="">-Select Insurance-</option>
                    <option value="bpjsks">BPJS Kesehatan</option>
                    <option value="bpjskt">BPJS Ketenagakerjaan</option>
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

    $(document).on('click', '.remove-input-field', function() {
      $(this).parents('tr').remove();
    });
    //  families
    $("#add-family").on('click', function() {
      addFamilyDetail();
    });

    function addFamilyDetail() {
      var tr =
        `<tr>
              <td>
                <select name="family_relationship[]" class="form-control" style="width: 100%;">
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
                <button type="button" class="btn btn-outline-danger remove-input-field"><i class="fas fa-trash-alt"></i></button>
              </td>
            </tr>`;
      $("#table-family").append(tr);
    };

    $(document).on('click', '.remove-input-field', function() {
      $(this).parents('tr').remove();
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

    $(document).on('click', '.remove-input-field', function() {
      $(this).parents('tr').remove();
    });

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

    $(document).on('click', '.remove-input-field', function() {
      $(this).parents('tr').remove();
    });

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

    $(document).on('click', '.remove-input-field', function() {
      $(this).parents('tr').remove();
    });

    // units
    $("#add-operableunit").on('click', function() {
      addOperableunitDetail();
    });

    function addOperableunitDetail() {
      var tr =
        `<tr>
              <td>
                <select name="unit_name[]" class="form-control" style="width: 100%;">
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

    $(document).on('click', '.remove-input-field', function() {
      $(this).parents('tr').remove();
    });

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

    $(document).on('click', '.remove-input-field', function() {
      $(this).parents('tr').remove();
    });

    // select2
    $('.select2').select2()
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })
    $(document).on('select2:open', () => {
      document.querySelector('.select2-search__field').focus();
    })

    // autofill department placeholder based on position_id
    $('#position_id').on('change', function() {
      var position_id = $(this).val();
      $.ajax({
        url: "{{ route('employees.getDepartment') }}"
        , type: "GET"
        , data: {
          position_id: position_id
        }
        , success: function(data) {
          console.log(data.department_name);
          // set value to department the data
          $('#department').val(data.department_name);
        }
      });
    });

  });

</script>
@endsection
