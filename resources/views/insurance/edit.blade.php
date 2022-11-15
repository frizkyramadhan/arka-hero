@extends('admin.main-layout')
@section('content-header')
                <div class="content-header">
                    <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        <h1 class="m-0">Insurance</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Insurance</li>
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
              <h3 class="card-title">Edit Insurance Data Employee</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                <div class="row">
                <div class="col-sm-12 col-md-6">
                  </div>
                  <div class="col-sm-12 col-md-6">
                    <div class="mt-5 d-flex justify-content-end">
                      <a href="{{route('insurances') }}" class="btn btn-primary">Back</a>
                  </div>
                  </div>
                </div>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                <form class="form-horizontal" action="/updateInsurance/{{$insurances->slug}}" method="post">
                    @method('put')
                    @csrf
                    <div class="card-body">
                      
                      <div class="form-group row">
                        <label for="employee_id" class="col-sm-2 col-form-label">Employee Name</label> :
                        <div class="col-sm-3">
                            <select name="employee_id" class="form-control select2 select2-hidden-accessible">
                                @foreach ($employee as $employee)
                                <option value="{{ $employee->id }}"
                                  {{ old('employee_id', $insurances->employee_id) == $employee->id ? 'selected' : '' }}>
                                  {{ $employee->fullname }}
                                </option>
                                @endforeach
                              </select>
                        </div>
                      </div> 
                      <div class="form-group row">
                        <label for="health_insurance_type" class="col-sm-2 col-form-label">Health Insurance Type</label> :
                        <div class="col-sm-3">
                          <input type="text" name="health_insurance_type" class="form-control" id="health_insurance_type" value="{{$insurances->health_insurance_type}}">
                          @if ($errors->any('health_insurance_type'))
                          <span class="text-danger">{{ ($errors->first('health_insurance_type')) }}</span>
                        @endif
                        </div>
                      </div> 

                      <div class="form-group row">
                        <label for="health_insurance_no" class="col-sm-2 col-form-label">Health Insurance No</label> :
                        <div class="col-sm-3">
                          <input type="text" value="{{$insurances->health_insurance_no}}" name="health_insurance_no" class="form-control" id="health_insurance_no" placeholder="">
                          @if ($errors->any('health_insurance_no'))
                            <span class="text-danger">{{ ($errors->first('health_insurance_no')) }}</span>
                          @endif
                        </div>
                       
                    </div>
                    <div class="form-group row">
                        <label for="health_facility" class="col-sm-2 col-form-label">Health Facility</label> :
                        <div class="col-sm-3">
                          <input type="text" value="{{$insurances->health_facility}}" name="health_facility" class="form-control" id="health_facility" placeholder="">
                          @if ($errors->any('health_facility'))
                            <span class="text-danger">{{ ($errors->first('health_facility')) }}</span>
                          @endif
                        </div>
                       
                    </div>
                    <div class="form-group row">
                        <label for="health_insurance_remarks" class="col-sm-2 col-form-label">Insurance Remarks</label> :
                        <div class="col-sm-3">
                          <input type="text" value="{{$insurances->health_insurance_remarks}}" name="health_insurance_remarks" class="form-control" id="health_insurance_remarks" placeholder="">
                          @if ($errors->any('health_insurance_remarks'))
                            <span class="text-danger">{{ ($errors->first('health_insurance_remarks')) }}</span>
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





