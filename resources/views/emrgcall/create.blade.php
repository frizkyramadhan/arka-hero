@extends('admin.main-layout')
@section('content-header')
                <div class="content-header">
                    <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        <h1 class="m-0">Emergency Call</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Emergency Call</li>
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
              <h3 class="card-title">Add Emergency Call Data Employee</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                <div class="row">
                <div class="col-sm-12 col-md-6">
                  </div>
                  <div class="col-sm-12 col-md-6">
                    <div class="mt-5 d-flex justify-content-end">
                      <a href="{{route('emrgcalls') }}" class="btn btn-primary">Back</a>
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
                <form class="form-horizontal" action="{{route('addEmrgcall') }}" method="post">
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
                          <label for="emrg_call_name" class="col-sm-2 col-form-label">Emergency Call Name</label> :
                          <div class="col-sm-3">
                            <input type="text" value="{{ old('emrg_call_name') }}" name="emrg_call_name" class="form-control" id="emrg_call_name" placeholder="">
                            @if ($errors->any('emrg_call_name'))
                            <span class="text-danger">{{ ($errors->first('emrg_call_name')) }}</span>
                          @endif
                        </div>
                          
                        </div>
                        <div class="form-group row">
                            <label for="emrg_call_relation" class="col-sm-2 col-form-label">Emergency Call Relation</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{ old('emrg_call_relation') }}" name="emrg_call_relation" class="form-control" id="emrg_call_relation" placeholder="">
                              @if ($errors->any('emrg_call_relation'))
                                <span class="text-danger">{{ ($errors->first('emrg_call_relation')) }}</span>
                              @endif
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="emrg_call_phone" class="col-sm-2 col-form-label">Emergency Call Phone</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{ old('emrg_call_phone') }}" name="emrg_call_phone" class="form-control" id="emrg_call_phone" placeholder="">
                              @if ($errors->any('emrg_call_phone'))
                                <span class="text-danger">{{ ($errors->first('emrg_call_phone')) }}</span>
                              @endif
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="emrg_call_address" class="col-sm-2 col-form-label">Emergency Call Address</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{ old('emrg_call_address') }}" name="emrg_call_address" class="form-control" id="emrg_call_address" placeholder="">
                              @if ($errors->any('emrg_call_address'))
                                <span class="text-danger">{{ ($errors->first('emrg_call_address')) }}</span>
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









