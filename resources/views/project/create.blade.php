@extends('admin.main-layout')
@section('content-header')
                <div class="content-header">
                    <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        <h1 class="m-0">Project</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Project</li>
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
              <h3 class="card-title">Add Project Data</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                <div class="row">
                <div class="col-sm-12 col-md-6">
                  </div>
                  <div class="col-sm-12 col-md-6">
                    <div class="mt-5 d-flex justify-content-end">
                      <a href="{{route('projects') }}" class="btn btn-primary">Back</a>
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
                <form class="form-horizontal" action="{{route('addProjects') }}" method="post">
                    @csrf
                    <div class="card-body">
                      <div class="form-group row">
                        <label for="project_code" class="col-sm-2 col-form-label">Project Code</label> :
                        <div class="col-sm-3">
                          <input type="text" value="{{ old('project_code') }}" name="project_code" class="form-control" id="project_code" placeholder="Project Code">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="project_name" class="col-sm-2 col-form-label">Project Name</label> :
                        <div class="col-sm-3">
                          <input type="text" value="{{ old('project_name') }}" name="project_name" class="form-control" id="project_name" placeholder="Project Name">
                        </div>
                      </div>

                      <div class="form-group row">
                        <label for="project_location" class="col-sm-2 col-form-label">Project Location</label> :
                        <div class="col-sm-3">
                          <input type="text" value="{{ old('project_location') }}" name="project_location" class="form-control" id="project_location" placeholder="Project Location">
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="bowheer" class="col-sm-2 col-form-label">Bowheer</label> :
                        <div class="col-sm-3">
                          <input type="text" value="{{ old('bowheer') }}" name="bowheer" class="form-control" id="bowheer" placeholder="Bowheer">
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





