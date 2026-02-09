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
                        <li class="breadcrumb-item active">{{ $title }}</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">{{ $title }}</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-primary" id="exportExcel">
                                        <i class="fas fa-download"></i> Export
                                    </button>
                                    @can('official-travels.create')
                                        <a href="{{ route('officialtravels.create') }}" class="btn btn-warning">
                                            <i class="fas fa-plus"></i> Add
                                        </a>
                                    @endcan
                                </div>
                            </div>
                            <!-- /.card-header -->
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
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Date From</label>
                                                        <input type="date" class="form-control" id="date1"
                                                            name="date1">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Date To</label>
                                                        <input type="date" class="form-control" id="date2"
                                                            name="date2">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Travel Number</label>
                                                        <input type="text" class="form-control" id="travel_number"
                                                            name="travel_number">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Destination</label>
                                                        <input type="text" class="form-control" id="destination"
                                                            name="destination">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>NIK</label>
                                                        <input type="text" class="form-control" id="nik"
                                                            name="nik" placeholder="Search by NIK">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Traveler Name</label>
                                                        <input type="text" class="form-control" id="fullname"
                                                            name="fullname" placeholder="Search by name">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Project</label>
                                                        <select class="form-control select2bs4" id="project"
                                                            name="project">
                                                            <option value="">- All -</option>
                                                            @foreach ($projects as $project)
                                                                <option value="{{ $project->id }}">
                                                                    {{ $project->project_code }} -
                                                                    {{ $project->project_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select class="form-control select2bs4" id="status"
                                                            name="status">
                                                            <option value="">- All -</option>
                                                            <option value="draft">Draft</option>
                                                            <option value="pending_hr">Menunggu Konfirmasi HR</option>
                                                            <option value="submitted">Submitted</option>
                                                            <option value="approved">Approved</option>
                                                            <option value="rejected">Rejected</option>
                                                            <option value="closed">Closed</option>
                                                            <option value="canceled">Canceled</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <button type="button" id="btn-reset"
                                                            class="btn btn-danger btn-block">
                                                            <i class="fas fa-times"></i> Reset Filter
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="officialtravel-table" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center" width="5%">No</th>
                                                <th>Travel Number</th>
                                                <th>Date</th>
                                                <th>Traveler</th>
                                                <th>Project</th>
                                                <th>Destination</th>
                                                <th>Status</th>
                                                <th>Creator</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
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
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(function() {
            //Initialize Select2 Elements
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });

            var table = $("#officialtravel-table").DataTable({
                responsive: true,
                autoWidth: true,
                // lengthChange: true,
                // lengthMenu: [
                //     [10, 25, 50, 100, -1],
                //     ['10', '25', '50', '100', 'Show all']
                // ],
                dom: 'rtip',
                // buttons: ["copy", "csv", "excel", "pdf", "print"],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('officialtravels.data') }}",
                    data: function(d) {
                        d.date1 = $('#date1').val(),
                            d.date2 = $('#date2').val(),
                            d.travel_number = $('#travel_number').val(),
                            d.destination = $('#destination').val(),
                            d.nik = $('#nik').val(),
                            d.fullname = $('#fullname').val(),
                            d.project = $('#project').val(),
                            d.status = $('#status').val(),

                            d.search = $("input[type=search][aria-controls=officialtravel-table]").val()
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'official_travel_number',
                        name: 'official_travel_number'
                    },
                    {
                        data: 'official_travel_date',
                        name: 'official_travel_date'
                    },
                    {
                        data: 'traveler',
                        name: 'traveler',
                        orderable: false
                    },
                    {
                        data: 'project',
                        name: 'project',
                        orderable: false
                    },
                    {
                        data: 'destination',
                        name: 'destination'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'created_by',
                        name: 'created_by',
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ]
            });

            // Handle filter changes
            $('#date1, #date2, #travel_number, #destination, #project, #status, #recommendation').change(
                function() {
                    table.draw();
                });

            $('#travel_number, #destination, #nik, #fullname').keyup(function() {
                table.draw();
            });

            // Handle reset button
            $('#btn-reset').click(function() {
                $('#date1, #date2').val('');
                $('#travel_number, #destination, #nik, #fullname').val('');
                $('#project, #status, #recommendation').val('').trigger('change');
                table.draw();
            });

            // Export Excel button click handler
            $('#exportExcel').click(function() {
                // Get all filter values
                var filters = {
                    date1: $('#date1').val(),
                    date2: $('#date2').val(),
                    travel_number: $('#travel_number').val(),
                    destination: $('#destination').val(),
                    nik: $('#nik').val(),
                    fullname: $('#fullname').val(),
                    project: $('#project').val(),
                    status: $('#status').val(),
                    recommendation: $('#recommendation').val(),

                    search: $("input[type=search][aria-controls=officialtravel-table]").val(),
                    _token: '{{ csrf_token() }}'
                };

                // Create a form dynamically
                var form = $('<form>', {
                    method: 'POST',
                    action: '{{ route('officialtravels.export') }}'
                });

                // Add CSRF token
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_token',
                    value: '{{ csrf_token() }}'
                }));

                // Add all filter values as hidden inputs
                Object.keys(filters).forEach(function(key) {
                    if (filters[key]) {
                        form.append($('<input>', {
                            type: 'hidden',
                            name: key,
                            value: filters[key]
                        }));
                    }
                });

                // Append form to body and submit
                $('body').append(form);
                form.submit();
            });
        });
    </script>
@endsection
