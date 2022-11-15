@extends('admin.main-layout')
@section('content-header')
                <div class="content-header">
                    <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        <h1 class="m-0">Bank</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Bank</li>
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
              <h3 class="card-title">Bank Data Table</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                
                <div class="row">
                <div class="col-sm-12 col-md-6">
                  </div>
                  <div class="col-sm-12 col-md-6">
                    <div class="mt-5 d-flex justify-content-end">
                      <a href="{{route('addBanks')}}" class="btn btn-primary btn-sm">Add Data</a>
                  </div>
                  </div>
                  <div class="my-3">
                    <form action="" method="get">
                      <div class="input-group mb-3">              
                          <input type="text" class="form-control" name="keyword" id="floatingInputGroup1" placeholder="Bank Name">
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
                  <div class="col-sm-12"><table id="example2" class="table table-bordered table-hover dataTable dtr-inline" role="grid" aria-describedby="example2_info">
                <thead>
                <tr role="row">
                  <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="Rendering engine: activate to sort column ascending">No</th>
                  <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="Rendering engine: activate to sort column ascending">Bank Name</th>
                  <th class="sorting text-center" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" aria-label="CSS grade: activate to sort column ascending">Action</th></tr>
                </thead>
                <tbody>
                  @foreach ($banks as $item)
                    <tr class="odd">
                      <td class="dtr-control">{{ $loop->iteration }}</td>
                      <td>{{ $item->bank_name }}</td>
                      <td class="text-center">
                        <form action="deleteBanks/{{$item->slug}}" method="post"
                          onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
                          @method('delete')
                          @csrf
                          <button class="btn btn-danger btn-sm">
                            <i class="fa fa-trash">Delete</i>
                          </button>
                          <a href="editBanks/{{$item->slug}}" class="btn btn-primary btn-sm">
                            <i class="fa fa-pencil">Update</i>
                          </a>
                        </form>
                    </td>
                    </tr>

                @endforeach
                </tbody>
                
              </table>
              <div class="my-5">
                {{$banks->links()}}
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





