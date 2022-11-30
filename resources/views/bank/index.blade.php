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
          <li class="breadcrumb-item active">Banks</li>
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
                <table id="example1" width="100%" class="table table-sm table-bordered table-striped">
                  <thead>
                    <tr>
                      <th class="text-center">No</th>
                      <th>Bank Name</th>
                      <th class="text-center">Status</th>
                      <th class="text-center">Action</th>
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
          <h4 class="modal-title">Add Bank</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form class="form-horizontal" action="{{ url('banks') }}" method="POST">
          <div class="modal-body">
            @csrf
            <div class="card-body">
              <div class="tab-content p-0">
                <div class="form-group row">
                  <label class="col-sm-2 col-form-label">Bank Name</label>
                  <div class="col-sm-10">
                    <input type="text" class="form-control @error('bank_name') is-invalid @enderror" name="bank_name" value="{{ old('bank_name') }}" placeholder="Bank Name" required>
                    @error('bank_name')
                    <div class="error invalid-feedback">
                      {{ $message }}
                    </div>
                    @enderror
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-sm-2 col-form-label">Status</label>
                  <div class="col-sm-10">
                    <select name="bank_status" class="form-control @error('bank_status') is-invalid @enderror">
                      <option value="1" {{ old('bank_status') == '1' ? 'selected' : '' }}>
                        Active</option>
                      <option value="0" {{ old('bank_status') == '0' ? 'selected' : '' }}>Inactive
                      </option>
                    </select>
                    @error('bank_status')
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
{{-- <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}"> --}}
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('scripts')
<!-- DataTables  & Plugins -->
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
{{-- <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script> --}}
{{-- <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script> --}}
<script src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
<!-- Page specific script -->
<script>
  $(function() {
    var table = $("#example1").DataTable({
      responsive: true
      , autoWidth: true
      , lengthChange: true
      , lengthMenu: [
          [10, 25, 50, 100, -1]
          , ['10', '25', '50', '100', 'Show all']
        ]
        //, dom: 'lBfrtpi'
      , dom: 'frtpi'
      , buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"]
      , processing: true
      , serverSide: true
      , ajax: {
        url: "{{ route('banks.list') }}"
        , data: function(d) {
          d.search = $("input[type=search][aria-controls=example1]").val()
          console.log(d);
        }
      }
      , columns: [{
        data: 'DT_RowIndex'
        , orderable: false
        , searchable: false
        , className: 'text-center'
      }, {
        data: "bank_name"
        , name: "bank_name"
        , orderable: false
      , }, {
        data: "bank_status"
        , name: "bank_status"
        , orderable: false
        , className: "text-center"
      , }, {
        data: "action"
        , name: "action"
        , orderable: false
        , searchable: false
        , className: "text-center"
      }]
      , fixedColumns: true
    , })
  });

</script>
@endsection
