@extends('admin.main-layout')
@section('content-header')
                <div class="content-header">
                    <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        <h1 class="m-0">Position</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Position</li>
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
              <h3 class="card-title">Edit Position Data Employee</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                <div class="row">
                <div class="col-sm-12 col-md-6">
                  </div>
                  <div class="col-sm-12 col-md-6">
                    <div class="mt-5 d-flex justify-content-end">
                      <a href="{{route('positions') }}" class="btn btn-primary">Back</a>
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
                <form class="form-horizontal" action="/updatePost/{{$position->slug}}" method="post">
                    @method('put')
                    @csrf
                    <div class="card-body">
                      <div class="form-group row">
                        <label for="position_name" class="col-sm-2 col-form-label">Position Name</label> :
                        <div class="col-sm-3">
                          <input type="text" name="position_name" class="form-control" id="position_name" value="{{$position->position_name}}">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="department_id" class="col-sm-2 col-form-label">Department</label> :
                        <div class="col-sm-3">
                            <select name="department_id" class="form-control select2 select2-hidden-accessible">
                                    @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ old('department_id', $position->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->department_name }}
                                    </option>
                                    @endforeach
                              </select>
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





