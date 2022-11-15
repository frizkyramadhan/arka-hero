@extends('admin.main-layout')
@section('content-header')
                <div class="content-header">
                    <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        <h1 class="m-0">Course</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Course</li>
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
              <h3 class="card-title">Add Course Data Employee</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                <div class="row">
                <div class="col-sm-12 col-md-6">
                  </div>
                  <div class="col-sm-12 col-md-6">
                    <div class="mt-5 d-flex justify-content-end">
                      <a href="{{route('courses') }}" class="btn btn-primary">Back</a>
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
                <form class="form-horizontal" action="{{route('addCourse') }}" method="post">
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
                          <label for="course_name" class="col-sm-2 col-form-label">Courses Name</label> :
                          <div class="col-sm-3">
                            <input type="text" value="{{ old('course_name') }}" name="course_name" class="form-control" id="course_name" placeholder="">
                            @if ($errors->any('course_name'))
                            <span class="text-danger">{{ ($errors->first('course_name')) }}</span>
                          @endif
                        </div>
                          
                        </div>
                        <div class="form-group row">
                            <label for="course_year" class="col-sm-2 col-form-label">Courses Years</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{ old('course_year') }}" name="course_year" class="form-control" id="course_year" placeholder="">
                              @if ($errors->any('course_year'))
                                <span class="text-danger">{{ ($errors->first('course_year')) }}</span>
                              @endif
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="course_remarks" class="col-sm-2 col-form-label">Courses Remarks</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{ old('course_remarks') }}" name="course_remarks" class="form-control" id="course_remarks" placeholder="">
                              @if ($errors->any('course_remarks'))
                                <span class="text-danger">{{ ($errors->first('course_remarks')) }}</span>
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









