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
                        <li class="breadcrumb-item active">Personal Detail</li>
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
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h4 class="card-title w-100">
                                            <a class="d-block w-100" data-toggle="collapse" href="#collapseOne">
                                                <i class="fas fa-filter"></i> Filter
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapseOne" class="collapse" data-parent="#accordion">
                                        <div class="card-body">
                                            <div class="row form-group">
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class=" form-control-label">DOB From</label>
                                                        <input type="date" class="form-control" name="date1"
                                                            id="date1" value="{{ request('date1') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class=" form-control-label">DOB To</label>
                                                        <input type="date" class="form-control" name="date2"
                                                            id="date2" value="{{ request('date2') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Full Name</label>
                                                        <input type="text" class="form-control" name="fullname"
                                                            id="fullname" value="{{ request('fullname') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">POB</label>
                                                        <input type="text" class="form-control" name="emp_pob"
                                                            id="emp_pob" value="{{ request('emp_pob') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Religion</label>
                                                        <select name="religion_name" class="form-control select2bs4"
                                                            id="religion_name" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            @foreach ($religions as $religion => $data)
                                                                <option value="{{ $data->religion_name }}"
                                                                    {{ request('religion_name') == $data->religion_name ? 'selected' : '' }}>
                                                                    {{ $data->religion_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Gender</label>
                                                        <select name="gender" class="form-control select2bs4"
                                                            id="gender" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            <option value="male">Male</option>
                                                            <option value="female">Female</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Marital</label>
                                                        <input type="text" class="form-control" name="marital"
                                                            id="marital" value="{{ request('marital') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Address</label>
                                                        <input type="text" class="form-control" name="address"
                                                            id="address" value="{{ request('address') }}"
                                                            placeholder="Jalan">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Village</label>
                                                        <input type="text" class="form-control" name="village"
                                                            id="village" value="{{ request('village') }}"
                                                            placeholder="Desa/Dusun">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Ward</label>
                                                        <input type="text" class="form-control" name="ward"
                                                            id="ward" value="{{ request('ward') }}"
                                                            placeholder="Kelurahan">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">District</label>
                                                        <input type="text" class="form-control" name="district"
                                                            id="district" value="{{ request('district') }}"
                                                            placeholder="Kecamatan">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">City</label>
                                                        <input type="text" class="form-control" name="city"
                                                            id="city" value="{{ request('city') }}"
                                                            placeholder="Kota/Kabupaten">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Phone</label>
                                                        <input type="text" class="form-control" name="phone"
                                                            id="phone" value="{{ request('phone') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class=" form-control-label">&nbsp;</label>
                                                        <button id="btn-reset" type="button"
                                                            class="btn btn-danger btn-block">Reset</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="example1" width="100%"
                                        class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="align-middle text-center">No</th>
                                                <th class="align-middle">Full Name</th>
                                                <th class="align-middle">POB</th>
                                                <th class="align-middle">DOB</th>
                                                <th class="align-middle">Religion</th>
                                                <th class="align-middle">Gender</th>
                                                <th class="align-middle">Marital</th>
                                                <th class="align-middle">Address</th>
                                                <th class="align-middle">Village</th>
                                                <th class="align-middle">Ward</th>
                                                <th class="align-middle">District</th>
                                                <th class="align-middle">City</th>
                                                <th class="align-middle">Phone</th>
                                                <th class="align-middle text-center">Action</th>
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
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
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
                responsive: true,
                autoWidth: true,
                lengthChange: true,
                lengthMenu: [
                        [10, 25, 50, 100, -1],
                        ['10', '25', '50', '100', 'Show all']
                    ]
                    //, dom: 'lBfrtpi'
                    ,
                dom: 'rtpi',
                buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('employees.getPersonals') }}",
                    data: function(d) {
                        d.date1 = $('#date1').val(), d.date2 = $('#date2').val(), d.fullname = $(
                                '#fullname').val(), d.emp_pob = $('#emp_pob').val(), d.religion_name =
                            $('#religion_name').val(), d.gender = $('#gender').val(), d.marital = $(
                                '#marital').val(), d.address = $('#address').val(), d.ward = $('#ward')
                            .val(), d.district = $('#district').val(), d.city = $('#city').val(), d
                            .phone = $('#phone').val(), d.search = $(
                                "input[type=search][aria-controls=example1]").val()
                        console.log(d);
                    }
                },
                columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }, {
                    data: "fullname",
                    name: "fullname",
                    orderable: false,
                }, {
                    data: "emp_pob",
                    name: "emp_pob",
                    orderable: false,
                }, {
                    data: "emp_dob",
                    name: "emp_dob",
                    orderable: false,
                }, {
                    data: "religion_name",
                    name: "religion_name",
                    orderable: false,
                }, {
                    data: "gender",
                    name: "gender",
                    orderable: false,
                }, {
                    data: "marital",
                    name: "marital",
                    orderable: false,
                }, {
                    data: "address",
                    name: "address",
                    orderable: false,
                }, {
                    data: "village",
                    name: "village",
                    orderable: false,
                }, {
                    data: "ward",
                    name: "ward",
                    orderable: false,
                }, {
                    data: "district",
                    name: "district",
                    orderable: false,
                }, {
                    data: "city",
                    name: "city",
                    orderable: false,
                }, {
                    data: "phone",
                    name: "phone",
                    orderable: false,
                }, {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false,
                    className: "text-center"
                }],
                fixedColumns: true,
            })
            $('#date1, #date2, #fullname, #emp_pob, #emp_dob, #religion_name, #gender, #marital, #address, #village, #ward, #district, #city, #phone')
                .keyup(function() {
                    table.draw();
                });
            $('#date1, #date2, #religion_name, #gender').change(function() {
                table.draw();
            });
            $('#btn-reset').click(function() {
                $('#date1, #date2, #fullname, #emp_pob, #emp_dob, #religion_name, #gender, #marital, #address, #village, #ward, #district, #city, #phone')
                    .val('');
                $('#date1, #date2, #religion_name, #gender').change();
            });
        });
    </script>

    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(function() {
            //Initialize Select2 Elements
            $('.select2').select2()

            //Initialize Select2 Elements
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            })

            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            })
        })
    </script>
@endsection
