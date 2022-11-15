@extends('admin.main-layout')
@section('content-header')
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
@endsection
@section('body')
<section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Add Employee Data</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                <div class="row">
                <div class="col-sm-12 col-md-6">
                  </div>
                  <div class="col-sm-12 col-md-6">
                    <div class="mt-5 d-flex justify-content-end">
                      <a href="{{route('employees') }}" class="btn btn-primary">Back</a>
                  </div>
                  </div>
                </div>
                    <form class="row g-3" method="post" action="{{route('addEmployee') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="col-md-3">
                          <label for="fullname" class="form-label">Full Name</label>
                          <input type="text" value="{{ old('fullname') }}" class="form-control" id="fullname" name="fullname">
                          @if ($errors->any('fullname'))
                            <span class="text-danger">{{ ($errors->first('fullname')) }}</span>
                              
                          @endif
                        </div>
                        <div class="col-md-3">
                          <label for="emp_pob" class="form-label">POB</label>
                          <input type="text" value="{{ old('emp_pob') }}" class="form-control" id="emp_pob"  name="emp_pob">
                          @if ($errors->any('emp_pob'))
                          <span class="text-danger">{{ ($errors->first('emp_pob')) }}</span>
                            
                            @endif
                        </div>
                        <div class="col-3">
                          <label for="emp_dob" class="form-label">DOB</label>
                          <input type="date"  value="{{ old('emp_dob') }}" class="form-control" id="emp_dob" placeholder="" name="emp_dob">
                          @if ($errors->any('emp_dob'))
                          <span class="text-danger">{{ ($errors->first('emp_dob')) }}</span>
                            
                        @endif
                        </div>
                        <div class="col-3">
                          <label for="blood_type" class="form-label">Blood Type</label>
                          <input type="text" value="{{ old('blood_type') }}" class="form-control" name="blood_type" id="blood_type" placeholder="O, A, or B">
                          @if ($errors->any('blood_type'))
                          <span class="text-danger">{{ ($errors->first('blood_type')) }}</span>
                            
                        @endif
                        </div>
                        <div class="col-3">
                            <label for="religion_id" class="form-label">Religion</label>
                            <select name="religion_id" class="form-control select2 select2-hidden-accessible" style="width: 100%;" data-select2-id="1" tabindex="-1" aria-hidden="true">
                                @foreach ($religions as $religions)
                                <option value="{{ $religions->id }}" {{ old('religion_id') == $religions->id ? 'selected' : '' }}>
                                  {{ $religions->religion_name }}
                                </option>
                              @endforeach
                            </select>
                          </div>               
                        <div class="col-md-3">
                          <label for="nationality" class="form-label">Nationality</label>
                          <input type="text" value="{{ old('nationality') }}" class="form-control" id="nationality" name="nationality">
                        </div>
                        <div class="col-3">
                            <label for="blood_type" class="form-label">Gender</label>
                            <select name="gender_id" class="form-control select2 select2-hidden-accessible" style="width: 100%;" data-select2-id="1" tabindex="-1" aria-hidden="true">
                                @foreach ($genders as $genders)
                                <option value="{{ $genders->id }}" {{ old('gender_id') == $genders->id ? 'selected' : '' }}>
                                  {{ $genders->name_gender }}
                                </option>
                              @endforeach
                            </select>
                          </div>  
                          <div class="col-md-3">
                            <label for="marital" class="form-label">Marital</label>
                            <input type="text" value="{{ old('marital') }}" class="form-control" id="marital" name="marital">
                            @if ($errors->any('marital'))
                            <span class="text-danger">{{ ($errors->first('marital')) }}</span>
                              
                          @endif
                          </div>
                          <div class="col-md-6">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" value="{{ old('address') }}" class="form-control" id="address" name="address">
                            @if ($errors->any('address'))
                            <span class="text-danger">{{ ($errors->first('address')) }}</span>
                              
                          @endif
                          </div>
                          <div class="col-md-3">
                            <label for="village" class="form-label">Village</label>
                            <input type="text" value="{{ old('village') }}" class="form-control" id="village" name="village">
                          </div>
                          <div class="col-md-3">
                            <label for="ward" class="form-label">Ward</label>
                            <input type="text" value="{{ old('ward') }}" class="form-control" id="ward" name="ward">
                          </div>
                          <div class="col-md-3">
                            <label for="district" class="form-label">District</label>
                            <input type="text" value="{{ old('district') }}" class="form-control" id="district" name="district">
                          </div>
                          <div class="col-md-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" value="{{ old('city') }}" class="form-control" id="city" name="city">
                          </div>
                          <div class="col-md-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" value="{{ old('phone') }}" class="form-control" id="phone" name="phone">
                          </div>
                          <div class="col-md-3">
                            <label for="identity_card" class="form-label">Identity Card</label>
                            <input type="text" value="{{ old('identity_card') }}" class="form-control" id="identity_card" name="identity_card">
                          </div>
                          <div class="col-md-3">
                            <label for="gambar" class="form-label">Image (Photo,KTP,KK,SIM..dll)</label>
                            <input type="file" value="{{ old('gambar') }}" class="form-control" id="gambar" name="gambar" placeholder="Choose an image">
                          </div>
                          {{-- <div class="col-md-3">
                            <label for="ktp" class="form-label">KTP</label>
                            <input type="file" value="{{ old('ktp') }}" class="form-control" id="ktp" name="ktp" placeholder="Choose an image">
                          </div>
                          <div class="col-md-3">
                            <label for="kk" class="form-label">KK</label>
                            <input type="file" value="{{ old('kk') }}" class="form-control" id="kk" name="kk" placeholder="Choose an image">
                          </div>
                          <div class="col-md-3">
                            <label for="sim" class="form-label">SIM</label>
                            <input type="file" value="{{ old('sim') }}" class="form-control" id="sim" name="sim" placeholder="Choose an image">
                          </div>
                          <div class="col-md-3">
                            <label for="bpjsks" class="form-label">BPJS KS</label>
                            <input type="file" value="{{ old('bpjsks') }}" class="form-control" id="bpjsks" name="bpjsks" placeholder="Choose an image">
                          </div>
                          <div class="col-md-3">
                            <label for="bpjskt" class="form-label">BPJS KT</label>
                            <input type="file" value="{{ old('bpjskt') }}" class="form-control" id="bpjskt" name="bpjskt" placeholder="Choose an image">
                          </div> --}}



                        <div class="col-12">
                          <div class="form-check">
                            
                            <label class="form-check-label" for="gridCheck">
                              -
                            </label>
                          </div>
                        </div>
                        <div class="col-12">
                          <button type="submit" class="btn btn-primary">Save</button>
                          <button type="reset" class="btn-default float-right">Cancel</button>
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
  </section>
@endsection





