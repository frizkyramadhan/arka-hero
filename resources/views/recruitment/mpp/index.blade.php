@extends('layouts.main')

@section('title', $title)

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Recruitment</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active">MPP</li>
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
                                <div class="d-flex flex-row justify-content-between align-items-start mb-1">
                                    <h3 class="card-title mb-0">
                                        <b>Man Power Plan (MPP)</b>
                                    </h3>
                                    <div class="d-flex flex-column flex-md-row ms-auto gap-2">
                                        @can('mpp.create')
                                            <a href="{{ route('recruitment.mpp.create') }}"
                                                class="btn btn-warning mb-md-0 ml-1 mb-1">
                                                <i class="fas fa-plus"></i> Add
                                            </a>
                                        @endcan
                                    </div>
                                </div>
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
                                                        <label class="form-control-label">MPP Number</label>
                                                        <input type="text" class="form-control" name="mpp_number"
                                                            id="mpp_number" value="{{ request('mpp_number') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Project</label>
                                                        <select name="project_id" class="form-control select2bs4"
                                                            id="project_id" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            @foreach ($projects as $project)
                                                                <option value="{{ $project->id }}"
                                                                    {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                                                    {{ $project->project_code }} -
                                                                    {{ $project->project_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Status</label>
                                                        <select name="status" class="form-control select2bs4"
                                                            id="status" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            <option value="active"
                                                                {{ request('status') == 'active' ? 'selected' : '' }}>
                                                                Active
                                                            </option>
                                                            <option value="closed"
                                                                {{ request('status') == 'closed' ? 'selected' : '' }}>
                                                                Closed</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Year</label>
                                                        <select name="year" class="form-control select2bs4"
                                                            id="year" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            @foreach ($years as $year)
                                                                <option value="{{ $year }}"
                                                                    {{ request('year') == $year ? 'selected' : '' }}>
                                                                    {{ $year }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">&nbsp;</label>
                                                        <button id="btn-reset" type="button"
                                                            class="btn btn-danger btn-block">Reset</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="example1" width="100%" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="align-middle text-center">No</th>
                                                <th class="align-middle">MPP Number</th>
                                                <th class="align-middle">Project</th>
                                                <th class="align-middle">Title</th>
                                                <th class="align-middle text-center">Plan</th>
                                                <th class="align-middle text-center">Existing</th>
                                                <th class="align-middle text-center">Diff</th>
                                                <th class="align-middle text-center">Completion</th>
                                                <th class="align-middle text-center">Status</th>
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
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('scripts')
    <!-- Bootstrap 4 -->
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
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
    <!-- Page specific script -->
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

            var table = $("#example1").DataTable({
                responsive: true,
                autoWidth: true,
                lengthChange: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    ['10', '25', '50', '100', 'Show all']
                ],
                dom: 'rtpi',
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('recruitment.mpp.data') }}",
                    data: function(d) {
                        d.mpp_number = $('#mpp_number').val();
                        d.project_id = $('#project_id').val();
                        d.status = $('#status').val();
                        d.year = $('#year').val();
                        d.search = $("input[type=search][aria-controls=example1]").val();
                    }
                },
                columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }, {
                    data: "mpp_number",
                    name: "mpp_number",
                    orderable: false
                }, {
                    data: "project_name",
                    name: "project.project_code",
                    orderable: false,
                }, {
                    data: "title",
                    name: "title",
                    orderable: false,
                }, {
                    data: "total_positions_needed",
                    name: "total_positions_needed",
                    orderable: false,
                    className: 'text-center'
                }, {
                    data: "total_existing",
                    name: "total_existing",
                    orderable: false,
                    className: 'text-center'
                }, {
                    data: "total_diff",
                    name: "total_diff",
                    orderable: false,
                    className: 'text-center'
                }, {
                    data: "completion",
                    name: "completion",
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }, {
                    data: "status",
                    name: "status",
                    orderable: false,
                    className: 'text-center'
                }, {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false,
                    className: "text-center"
                }],
                fixedColumns: true,
            });

            // Filter functionality
            $('#mpp_number, #project_id, #status, #year')
                .keyup(function() {
                    table.draw();
                });
            $('#project_id, #status, #year')
                .change(function() {
                    table.draw();
                });

            // Reset functionality
            $('#btn-reset').click(function() {
                $('#mpp_number, #project_id, #status, #year')
                    .val('');
                $('#project_id, #status, #year').change();
                table.draw();
            });

            // Delete button handler
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                const url = $(this).data('url');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Deleted!', response.message, 'success');
                                    table.ajax.reload();
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', 'Failed to delete MPP', 'error');
                            }
                        });
                    }
                });
            });

            // Initialize tooltips for action buttons
            $(document).tooltip({
                selector: '[title]'
            });
        });
    </script>
@endsection
