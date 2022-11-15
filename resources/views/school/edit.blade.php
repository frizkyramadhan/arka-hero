@extends('admin.main-layout')
@section('content-header')
                <div class="content-header">
                    <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        <h1 class="m-0">Education</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Education Employee</li>
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
              <h3 class="card-title">Add Education Data Employee</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                <div class="row">
                <div class="col-sm-12 col-md-6">
                  </div>
                  <div class="col-sm-12 col-md-6">
                    <div class="mt-5 d-flex justify-content-end">
                      <a href="{{route('schools') }}" class="btn btn-primary">Back</a>
                  </div>
                  </div>
                </div>
                    {{-- @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif --}}
                <form class="form-horizontal" action="/UpdateSchool/{{$schools->slug}}" method="post">
                    @method('put')
                    @csrf
                    <div class="card-body">
                        <div class="form-group row">
                            <label for="employee_id" class="col-sm-2 col-form-label">Employee Name</label> :
                            <div class="col-sm-3">
                                <select name="employee_id" class="form-control select2 select2-hidden-accessible">
                                  @foreach ($employee as $employee)
                                  <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->fullname }}
                                  </option>
                                  @endforeach
                                  </select>
                            </div>
                          </div> 
                        <div class="form-group row">
                          <label for="education_level" class="col-sm-2 col-form-label">Education Level</label> :
                          <div class="col-sm-3">
                            <input type="text" value="{{$schools->education_level}}" name="education_level" class="form-control" id="education_level" placeholder="">
                            @if ($errors->any('education_level'))
                            <span class="text-danger">{{ ($errors->first('education_level')) }}</span>
                          @endif
                        </div>
                          
                        </div>
                        <div class="form-group row">
                            <label for="education_name" class="col-sm-2 col-form-label">Education Name</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{$schools->education_name}}" name="education_name" class="form-control" id="education_name" placeholder="">
                              @if ($errors->any('education_name'))
                                <span class="text-danger">{{ ($errors->first('education_name')) }}</span>
                              @endif
                            </div>
                           
                        </div>

                        <div class="form-group row">
                            <label for="education_year" class="col-sm-2 col-form-label">Education Years</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{$schools->education_year}}" name="education_year" class="form-control" id="education_year" placeholder="">
                              @if ($errors->any('education_year'))
                                <span class="text-danger">{{ ($errors->first('education_year')) }}</span>
                              @endif
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="education_remarks" class="col-sm-2 col-form-label">Education Remarks</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{$schools->education_remarks}}" name="education_remarks" class="form-control" id="education_remarks" placeholder="">
                              @if ($errors->any('education_remarks'))
                                <span class="text-danger">{{ ($errors->first('education_remarks')) }}</span>
                              @endif
                            </div>
                           
                        </div>
                        
                        
                      </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                      <button type="submit" class="btn btn-info">Save</button>
                      <button type="reset" class="btn btn-default float-right">Cancel</button>
                    </div>
                    <!-- /.card-footer -->
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









