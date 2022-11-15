@extends('admin.main-layout')
@section('content-header')
                <div class="content-header">
                    <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        <h1 class="m-0">Administrations</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Administration</li>
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
              <h3 class="card-title">Edit Employee Administration</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                <div class="row">
                <div class="col-sm-12 col-md-6">
                  </div>
                  <div class="col-sm-12 col-md-6">
                    <div class="mt-5 d-flex justify-content-end">
                      <a href="{{route('administrations') }}" class="btn btn-primary">Back</a>
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
                <form class="form-horizontal" action="/updateAdministration/{{$administrations->slug}}" method="post">
                    @method('put')
                    @csrf
                    <div class="card-body">
                        <div class="form-group row">
                            <label for="employee_id" class="col-sm-2 col-form-label">Employee Name</label> :
                            <div class="col-sm-3">
                                <select name="employee_id" class="form-control select2 select2-hidden-accessible">
                                    @foreach ($employee as $employee)
                                    <option value="{{ $employee->id }}"
                                      {{ old('employee_id', $administrations->employee_id) == $employee->id ? 'selected' : '' }}>
                                      {{ $employee->fullname }}
                                    </option>
                                  @endforeach
                                  </select>
                            </div>
                          </div> 
                        <div class="form-group row">
                          <label for="project_id" class="col-sm-2 col-form-label">Project Name</label> :
                          <div class="col-sm-3">
                            <select name="project_id" class="form-control select2 select2-hidden-accessible">
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}"
                                    {{ old('project_id', $administrations->project_id) == $project->id ? 'selected' : '' }}>
                                    {{ $project->project_code }} - {{ $project->project_name }}
                                    </option>
                                @endforeach
                              </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="position_id" class="col-sm-2 col-form-label">Position Name</label> :
                        <div class="col-sm-3">
                          <select name="position_id" class="form-control select2 select2-hidden-accessible">
                            @foreach ($positions as $positions)
                                <option value="{{ $positions->id }}"
                                {{ old('position_id', $administrations->position_id) == $positions->id ? 'selected' : '' }}>
                                {{ $positions->position_name }}
                                </option>
                            @endforeach
                            </select>
                      </div>
                  </div>
                        <div class="form-group row">
                            <label for="nik" class="col-sm-2 col-form-label">NIK</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{$administrations->nik}}" name="nik" class="form-control" id="nik" placeholder="">
                              @if ($errors->any('nik'))
                                <span class="text-danger">{{ ($errors->first('nik')) }}</span>
                              @endif
                            </div>
                           
                        </div>
                        
                        <div class="form-group row">
                            <label for="class" class="col-sm-2 col-form-label">Class</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{$administrations->class}}" name="class" class="form-control" id="class" placeholder="">
                              @if ($errors->any('class'))
                                <span class="text-danger">{{ ($errors->first('class')) }}</span>
                              @endif
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="doh" class="col-sm-2 col-form-label">DOH</label> :
                            <div class="col-sm-3">
                              <input type="date" value="{{$administrations->doh}}" name="doh" class="form-control" id="doh" placeholder="">
                              @if ($errors->any('doh'))
                                <span class="text-danger">{{ ($errors->first('doh')) }}</span>
                              @endif
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="poh" class="col-sm-2 col-form-label">POH</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{$administrations->poh}}" name="poh" class="form-control" id="poh" placeholder="">
                              @if ($errors->any('poh'))
                                <span class="text-danger">{{ ($errors->first('poh')) }}</span>
                              @endif
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="basic_salary" class="col-sm-2 col-form-label">Basic Salary</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{$administrations->basic_salary}}" name="basic_salary" class="form-control" id="basic_salary" placeholder="">
                              @if ($errors->any('basic_salary'))
                                <span class="text-danger">{{ ($errors->first('basic_salary')) }}</span>
                              @endif
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="site_allowance" class="col-sm-2 col-form-label">Site Allowance</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{$administrations->site_allowance}}" name="site_allowance" class="form-control" id="site_allowance" placeholder="">
                              @if ($errors->any('site_allowance'))
                                <span class="text-danger">{{ ($errors->first('site_allowance')) }}</span>
                              @endif
                            </div>
                           
                        </div>
                        <div class="form-group row">
                            <label for="other_allowance" class="col-sm-2 col-form-label">Other Allowance</label> :
                            <div class="col-sm-3">
                              <input type="text" value="{{$administrations->other_allowance}}" name="other_allowance" class="form-control" id="other_allowance" placeholder="">
                              @if ($errors->any('other_allowance'))
                                <span class="text-danger">{{ ($errors->first('other_allowance')) }}</span>
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









