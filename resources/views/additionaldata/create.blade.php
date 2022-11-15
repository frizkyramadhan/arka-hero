@extends('admin.main-layout')
@section('content-header')
                <div class="content-header">
                    <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        <h1 class="m-0">Additional Data</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Additional Data</li>
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
              <h3 class="card-title">Add Additional Data Data Employee</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                <div class="row">
                <div class="col-sm-12 col-md-6">
                  </div>
                  <div class="col-sm-12 col-md-6">
                    <div class="mt-5 d-flex justify-content-end">
                      <a href="{{route('additionaldatas') }}" class="btn btn-primary">Back</a>
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
                <form class="form-horizontal" action="{{route('Addadditionaldata') }}" method="post">
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
                          <label for="cloth_size" class="col-sm-2 col-form-label">Cloth Size</label> :
                          <div class="col-sm-3">
                            <input type="text" value="{{ old('cloth_size') }}" name="cloth_size" class="form-control" id="cloth_size" placeholder="">
                            @if ($errors->any('cloth_size'))
                            <span class="text-danger">{{ ($errors->first('cloth_size')) }}</span>
                          @endif
                        </div>
                          
                        </div>
                        <div class="form-group row">
                            <label for="pants_size" class="col-sm-2 col-form-label">Pants Size</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{ old('pants_size') }}" name="pants_size" class="form-control" id="pants_size" placeholder="">
                              @if ($errors->any('pants_size'))
                                <span class="text-danger">{{ ($errors->first('pants_size')) }}</span>
                              @endif
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="shoes_size" class="col-sm-2 col-form-label">Shoes Size</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{ old('shoes_size') }}" name="shoes_size" class="form-control" id="shoes_size" placeholder="">
                              @if ($errors->any('shoes_size'))
                                <span class="text-danger">{{ ($errors->first('shoes_size')) }}</span>
                              @endif
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="height" class="col-sm-2 col-form-label">Height</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{ old('height') }}" name="height" class="form-control" id="height" placeholder="">
                              @if ($errors->any('height'))
                                <span class="text-danger">{{ ($errors->first('height')) }}</span>
                              @endif
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="weight" class="col-sm-2 col-form-label">Weight</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{ old('weight') }}" name="weight" class="form-control" id="weight" placeholder="">
                              @if ($errors->any('weight'))
                                <span class="text-danger">{{ ($errors->first('weight')) }}</span>
                              @endif
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="glasses" class="col-sm-2 col-form-label">Glasses</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{ old('glasses') }}" name="glasses" class="form-control" id="glasses" placeholder="">
                              @if ($errors->any('glasses'))
                                <span class="text-danger">{{ ($errors->first('glasses')) }}</span>
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









