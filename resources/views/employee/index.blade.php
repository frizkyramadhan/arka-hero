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
              <h3 class="card-title">Employee Data Table</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                <div class="row">
                <div class="col-sm-12 col-md-6">
                  {{-- <form action="{{route('ImportSchool') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file" class="form-control">
                    <button class="btn btn-info btn-sm" class="form-control"> Upload

                    </button>
                 </form> --}}
                  </div>
                  <div class="col-sm-12 col-md-6">
                    <div class="mt-5 d-flex justify-content-end">
                      <a href="{{route('addEmployee')}}" class="btn btn-primary btn-sm">Add Data</a>
                      {{-- <a href="{{route('ExportSchool')}}" class="btn btn-success btn-sm">Exp Excel</a> --}}
                  </div>
                  </div>
                  <div class="my-3">
                    <form action="" method="get">
                      <div class="input-group mb-3">              
                          <input type="text" class="form-control" name="keyword" id="floatingInputGroup1" placeholder="Keyword">
                          <button class="input-group-text btn btn-primary">Search</button>                                       
                      </div>
  
                    </form>
                </div>
                </div>
                <div class="mt-5">
                  @if (session('status'))
                      <div class="alert alert-success">
                          {{ session('status') }}
                      </div>
                  @endif
              </div>
              
                <div class="row">
                  <div class="col-sm-12">
                    <table id="example2" class="table table-bordered table-hover dataTable dtr-inline" role="grid" aria-describedby="example2_info">
                <thead>
                <tr role="row">
                  <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="Rendering engine: activate to sort column ascending">No</th>
                  <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="Rendering engine: activate to sort column ascending">Name</th>
                  <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="Browser: activate to sort column ascending">POB</th>
                  <th class="sorting sorting_desc" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="Platform(s): activate to sort column ascending" aria-sort="descending">DOB</th>
                  <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="Engine version: activate to sort column ascending">Religion</th>
                  <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="Engine version: activate to sort column ascending">Nationality</th>
                  <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="Engine version: activate to sort column ascending">Address</th>
                  <th class="sorting text-center" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="CSS grade: activate to sort column ascending">Action</th></tr>
                </thead>
                <tbody>
                  @foreach ($employees as $item)
                    <tr class="odd">
                      <td class="dtr-control">{{ $loop->iteration }}</td>
                      <td>{{ $item->fullname }}</td>
                      <td class="sorting_1">{{ $item->emp_pob }}</td>
                      <td>{{ $item->emp_dob }}</td>
                      <td>{{ $item->religion->religion_name }}</td>
                      <td>{{ $item->nationality }}</td>
                      <td>{{ $item->address }}</td>
                      <td class="text-center">
                        
                        <a href="deleteEmployee/{{$item->slug}}" class="btn btn-danger btn-sm">
                          <i class="fa fa-pencil">Delete</i>
                        </a>
                          <a href="editEmployee/{{$item->slug}}" class="btn btn-primary btn-sm">
                            <i class="fa fa-pencil">Update</i>
                          </a>
                          <a href="detailEmployee/{{$item->slug}}" class="btn btn-success btn-sm">
                            <i class="fa fa-view">Detail</i>
                          </a>
                       
                    </td>
                    </tr>

                @endforeach
                </tbody>
                
              </table>
              <div class="my-5">
                {{$employees->withQueryString()->links()}}
            </div>
            </div>
          </div>
          
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

{{-- <script type="text/javascript">
  $(function () {
    $('#datatables').DataTable({
      processing:true,
      serverSide: true,
      ajax: 'admin/EmployeeJson',
      columns:[
        {data: 'id', name: 'id',}
        {data: 'fullname', name: 'id',}
        {data: 'emp_pob', name: 'id',}
        {data: 'emp_dob', name: 'id',}
        {data: 'name_religion', name: 'id',}
        {data: 'nationality', name: 'id',}
        {data: 'name_gender', name: 'id',}
      ]
    })
  })

</script> --}}





