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
          <li class="breadcrumb-item active">Operable Units</li>
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
            </div><!-- /.card-header -->
            <div class="card-body">
              <div class="table-responsive">
                <table id="example1" width="100%" class="table table-sm table-bordered table-striped">
                  <thead>
                    <tr>
                      <th class="text-center">No</th>
                      <th>Employee Name</th>
                      <th>Unit Name</th>
                      <th class="text-center">Unit Type</th>
                      <th class="text-center">Unit Remarks</th>                    
                      <th class="text-center">Detail</th>
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

  


  
      
 
</section>
@endsection

@section('styles')
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
{{-- <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}"> --}}
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('scripts')
<!-- Select2 -->
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
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
      drawCallback: function() {
        // script select2 harus dimasukkan ke dalam fungsi drawCallback datatable agar bisa berjalan, kalau diluar fungsi datatable, maka akan muncul select baru di model Add
        // $('.select2').select2()
        // $('.select2bs4').select2({
        //   theme: 'bootstrap4'
        // })
        $(document).on('select2:open', () => {
          document.querySelector('.select2-search__field').focus();
        })
      }
      , responsive: true
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
        url: "{{ route('operableunits.list') }}"
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
        data: "fullname"
        , name: "fullname"
        , orderable: false
      , }, {
        data: "unit_name"
        , name: "unit_name"
        , orderable: false
      , }, {
        data: "unit_type"
        , name: "unit_type"
        , orderable: false
        , className: "text-center"
      , }, {
        data: "unit_remarks"
        , name: "unit_remarks"
        , orderable: false
        , searchable: false
        , className: "text-center"
      ,}, {
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
