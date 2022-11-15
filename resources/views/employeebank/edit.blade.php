@extends('admin.main-layout')
@section('content-header')
                <div class="content-header">
                    <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        <h1 class="m-0">Employee Bank</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Employee Bank</li>
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
              <h3 class="card-title">Edit Employee Bank Data</h3>
            </div>
            <div class="card-body">
              <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                <div class="row">
                <div class="col-sm-12 col-md-6">
                  </div>
                  <div class="col-sm-12 col-md-6">
                    <div class="mt-5 d-flex justify-content-end">
                      <a href="{{route('employeebanks') }}" class="btn btn-primary">Back</a>
                  </div>
                  </div>
                </div>
                <form class="form-horizontal" action="/updateEmployeebank/{{$employeebanks->slug}}" method="post">
                    @method('put')
                    @csrf
                    <div class="card-body">
                        <div class="form-group row">
                            <label for="employee_id" class="col-sm-2 col-form-label">Employee Name</label> :
                            <div class="col-sm-3">
                                <select name="employee_id" class="form-control select2 select2-hidden-accessible">
                                    @foreach ($employee as $employee)
                                    <option value="{{ $employee->id }}"
                                      {{ old('employee_id', $employeebanks->employee_id) == $employee->id ? 'selected' : '' }}>
                                      {{ $employee->fullname }}
                                    </option>
                                    @endforeach
                                  </select>
                            </div>
                          </div> 
                        <div class="form-group row">
                          <label for="bank_id" class="col-sm-2 col-form-label">Bank Name</label> :
                          <div class="col-sm-3">
                            <select name="bank_id" class="form-control select2 select2-hidden-accessible">
                                @foreach ($banks as $banks)
                                <option value="{{ $banks->id }}"
                                  {{ old('bank_id', $employeebanks->bank_id) == $banks->id ? 'selected' : '' }}>
                                  {{ $banks->bank_name }}
                                </option>
                              @endforeach
                              </select>
                        </div>
                          
                        </div>
                        <div class="form-group row">
                            <label for="bank_account_no" class="col-sm-2 col-form-label">Bank Account No</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{$employeebanks->bank_account_no}}" name="bank_account_no" class="form-control" id="bank_account_no" placeholder="">
                              @if ($errors->any('bank_account_no'))
                                <span class="text-danger">{{ ($errors->first('bank_account_no')) }}</span>
                              @endif
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="bank_account_name" class="col-sm-2 col-form-label">Bank Account Name</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{$employeebanks->bank_account_name}}" name="bank_account_name" class="form-control" id="bank_account_name" placeholder="">
                              @if ($errors->any('bank_account_name'))
                                <span class="text-danger">{{ ($errors->first('bank_account_name')) }}</span>
                              @endif
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="bank_account_branch" class="col-sm-2 col-form-label">Bank Account Branch</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{$employeebanks->bank_account_branch}}" name="bank_account_branch" class="form-control" id="bank_account_branch" placeholder="">
                              @if ($errors->any('bank_account_branch'))
                                <span class="text-danger">{{ ($errors->first('bank_account_branch')) }}</span>
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









