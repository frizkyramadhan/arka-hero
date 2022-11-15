@extends('admin.main-layout')
@section('content-header')
                <div class="content-header">
                    <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        <h1 class="m-0">Insurance<h1>
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
              <h3 class="card-title">Insurance Employee Data Table</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                <div class="row">
                <div class="col-sm-12 col-md-6">
                  </div>
                  <div class="col-sm-12 col-md-6">
                    <div class="mt-5 d-flex justify-content-end">
                      <a href="{{route('addInsurance')}}" class="btn btn-primary btn-sm">Add Data</a>
                  </div>
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
                <div class="mt-5">
                  @if (session('status'))
                      <div class="alert alert-success">
                          {{ session('status') }}
                      </div>
                  @endif
              </div>
              
                <div class="row">
                  <div class="col-sm-12"><table id="example2" class="table table-bordered table-hover dataTable dtr-inline" role="grid" aria-describedby="example2_info">
                <thead>
                <tr role="row">
                  <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="Rendering engine: activate to sort column ascending">No</th>
                  <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="Rendering engine: activate to sort column ascending">Employee Name</th>
                  <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="Browser: activate to sort column ascending">Health Insurance Type</th>              
                  <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="Browser: activate to sort column ascending">Health Insurance No</th>              
                  <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="Browser: activate to sort column ascending">Health Facility</th>              
                  <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="Browser: activate to sort column ascending">Insurance Remarks</th>              
                  <th class="sorting text-center" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="CSS grade: activate to sort column ascending">Action</th></tr>
                </thead>
                <tbody>
                  @foreach ($insurances as $item)
                    <tr class="odd">
                      <td class="dtr-control">{{ $loop->iteration }}</td>
                      <td>{{ $item->employees->fullname }}</td>
                      <td class="sorting_1">{{ $item->health_insurance_type }}</td>
                      <td class="sorting_1">{{ $item->health_insurance_no }}</td>
                      <td class="sorting_1">{{ $item->health_facility }}</td>
                      <td class="sorting_1">{{ $item->health_insurance_remarks }}</td>
                      <td class="text-center">
                        {{-- <a href="editLicense/{{$item->slug}}" class="btn btn-primary btn-sm">Update</a>
                        <a href="deleteLicense/{{$item->slug}}" class="btn btn-danger btn-sm">Delete</a> --}}
                        <form action="deleteInsurance/{{$item->slug}}" method="post"
                          onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
                          @method('delete')
                          @csrf
                          <button class="btn btn-danger btn-sm">
                            <i class="fa fa-trash">Delete</i>
                          </button>
                          <a href="editInsurance/{{$item->slug}}" class="btn btn-primary btn-sm">
                            <i class="fa fa-pencil">Update</i>
                          </a>
                        </form>
                    </td>
                    </tr>

                @endforeach
                </tbody>
                
              </table>
              <div class="my-5">
                {{$insurances->withQueryString()->links()}}
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





