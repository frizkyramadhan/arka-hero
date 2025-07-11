@extends('layouts.main')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">{{ $title }}</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">Users</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <!-- Left col -->
      <div class="col-lg-12">
        <!-- Custom tabs (Charts with tabs)-->
        <div id="accordion">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <strong>{{ $subtitle }}</strong>
              </h3>
              <div class="card-tools">
                <ul class="nav nav-pills ml-auto">
                  <li class="nav-item mr-2">
                    <a class="btn btn-warning" data-toggle="modal" data-target="#modal-lg"><i class="fas fa-plus"></i>
                      Add</a>
                  </li>
                </ul>
              </div>
            </div><!-- /.card-header -->
            <div class="card-body">
              <div class="table-responsive">
                <table id="example1" width="100%" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th style="width: 5%" class="text-center">No</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Roles</th>
                      <th style="width: 10%" class="text-center">Status</th>
                      <th style="width: 10%" class="text-center">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div><!-- /.card-body -->
          </div>
        </div>
        <!-- /.card -->
      </div>
      <!-- right col -->
    </div>
    <!-- /.row (main row) -->
  </div>

  <div class="modal fade" id="modal-lg">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Add User</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form class="form-horizontal" action="{{ url('users') }}" method="POST">
          <div class="modal-body">
            @csrf
            <div class="card-body">
              <div class="tab-content p-0">
                <div class="form-group row">
                  <label class="col-sm-2 col-form-label">Full Name</label>
                  <div class="col-sm-10">
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Full Name" required>
                    @error('name')
                    <div class="error invalid-feedback">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-sm-2 col-form-label">Email</label>
                  <div class="col-sm-10">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="@arka.co.id" required>
                    @error('email')
                    <div class="error invalid-feedback">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-sm-2 col-form-label">Password</label>
                  <div class="col-sm-10">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" value="{{ old('password') }}" placeholder="Password" required>
                    @error('password')
                    <div class="error invalid-feedback">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-sm-2 col-form-label">Roles</label>
                    <div class="col-sm-10">
                      <select name="roles[]" class="form-control select2 @error('roles') is-invalid @enderror" multiple="multiple" data-placeholder="Select roles">
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ (collect(old('roles'))->contains($role->name)) ? 'selected':'' }}>{{ $role->name }}</option>
                        @endforeach
                      </select>
                      @error('roles')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                </div>
                <div class="form-group row">
                  <label class="col-sm-2 col-form-label">Status</label>
                  <div class="col-sm-10">
                    <select name="user_status" class="form-control @error('user_status') is-invalid @enderror">
                      <option value="1" {{ old('user_status') == '1' ? 'selected' : '' }}>
                        Active</option>
                      <option value="0" {{ old('user_status') == '0' ? 'selected' : '' }}>Inactive
                      </option>
                    </select>
                    @error('user_status')
                    <div class="invalid-feedback">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>
              </div>
            </div><!-- /.card-body -->
          </div>
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->
</section>
@endsection

@section('styles')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('scripts')
<!-- DataTables  & Plugins -->
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
<!-- Page specific script -->
<script>
  $(function() {
    //Initialize Select2 Elements
    $('.select2').select2({
      theme: 'bootstrap4',
      dropdownParent: $('#modal-lg')
    });

    var table = $("#example1").DataTable({
      responsive: true,
      autoWidth: true,
      lengthChange: true,
      lengthMenu: [
        [10, 25, 50, 100, -1],
        ['10', '25', '50', '100', 'Show all']
      ],
      dom: 'frtpi',
      buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('users.data') }}",
        data: function(d) {
          d.search = $("input[type=search][aria-controls=example1]").val();
        }
      },
      columns: [{
          data: 'DT_RowIndex',
          orderable: false,
          searchable: false,
          className: 'text-center'
        },
        {
          data: "name",
          name: "name",
          orderable: false,
        },
        {
          data: "email",
          name: "email",
          orderable: false,
        },
        {
          data: "roles",
          name: "roles",
          orderable: false,
        },
        {
          data: "user_status",
          name: "user_status",
          orderable: false,
          className: "text-center",
        },
        {
          data: "action",
          name: "action",
          orderable: false,
          searchable: false,
          className: "text-center"
        }
      ],
      fixedColumns: true,
    });

    // Enable tooltips
    $('[data-toggle="tooltip"]').tooltip();
  });

  // Function to handle edit user
  function editUser(id) {
    $('#modal-edit-' + id).modal('show');
    setTimeout(function() {
      $('.select2-edit-' + id).select2({
        theme: 'bootstrap4',
        width: '100%',
        dropdownParent: $('#modal-edit-' + id)
      });
    }, 100);
  }
</script>
@endsection
