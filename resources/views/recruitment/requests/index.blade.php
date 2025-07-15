@extends('layouts.main')

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
                        <li class="breadcrumb-item active">FPTK</li>
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
                                        FPTK (Formulir Permintaan Tenaga Kerja)
                                    </h3>
                                    <div class="d-flex flex-column flex-md-row ms-auto gap-2">
                                        @can('recruitment-requests.export')
                                            <a href="{{ route('recruitment.requests.export') }}"
                                                class="btn btn-primary mb-md-0 ml-1 mb-1">
                                                <i class="fas fa-download"></i> Export
                                            </a>
                                        @endcan
                                        @can('recruitment-requests.create')
                                            <a href="{{ route('recruitment.requests.create') }}"
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
                                                        <label class="form-control-label">Request Number</label>
                                                        <input type="text" class="form-control" name="request_number"
                                                            id="request_number" value="{{ request('request_number') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Department</label>
                                                        <select name="department_id" class="form-control select2bs4"
                                                            id="department_id" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            @foreach ($departments as $department)
                                                                <option value="{{ $department->id }}"
                                                                    {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                                                    {{ $department->department_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Position</label>
                                                        <select name="position_id" class="form-control select2bs4"
                                                            id="position_id" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            @foreach ($positions as $position)
                                                                <option value="{{ $position->id }}"
                                                                    {{ request('position_id') == $position->id ? 'selected' : '' }}>
                                                                    {{ $position->position_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Level</label>
                                                        <select name="level_id" class="form-control select2bs4"
                                                            id="level_id" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            @foreach ($levels as $level)
                                                                <option value="{{ $level->id }}"
                                                                    {{ request('level_id') == $level->id ? 'selected' : '' }}>
                                                                    {{ $level->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Date From</label>
                                                        <input type="date" class="form-control" name="date_from"
                                                            id="date_from" value="{{ request('date_from') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Date To</label>
                                                        <input type="date" class="form-control" name="date_to"
                                                            id="date_to" value="{{ request('date_to') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Status</label>
                                                        <select name="status" class="form-control select2bs4"
                                                            id="status" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            <option value="draft"
                                                                {{ request('status') == 'draft' ? 'selected' : '' }}>Draft
                                                            </option>
                                                            <option value="submitted"
                                                                {{ request('status') == 'submitted' ? 'selected' : '' }}>
                                                                Submitted</option>
                                                            <option value="approved"
                                                                {{ request('status') == 'approved' ? 'selected' : '' }}>
                                                                Approved</option>
                                                            <option value="rejected"
                                                                {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                                                Rejected</option>
                                                            <option value="closed"
                                                                {{ request('status') == 'closed' ? 'selected' : '' }}>
                                                                Closed</option>
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
                                                <th class="align-middle">Request Number</th>
                                                <th class="align-middle">Department</th>
                                                <th class="align-middle">Position</th>
                                                <th class="align-middle">Level</th>
                                                <th class="align-middle">Employment Type</th>
                                                <th class="align-middle text-center">Status</th>
                                                <th class="align-middle">Requested By</th>
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

        <!-- Approval Modal -->
        <div class="modal fade" id="approvalModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Approve FPTK Request</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="approvalForm">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" id="approval_fptk_id" name="fptk_id">
                            <div class="form-group">
                                <label for="approval_notes">Approval Notes</label>
                                <textarea class="form-control" id="approval_notes" name="notes" rows="3"
                                    placeholder="Enter approval notes (optional)"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Approve</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Rejection Modal -->
        <div class="modal fade" id="rejectionModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Reject FPTK Request</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="rejectionForm">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" id="rejection_fptk_id" name="fptk_id">
                            <div class="form-group">
                                <label for="rejection_reason">Rejection Reason *</label>
                                <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3"
                                    placeholder="Enter rejection reason" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Reject</button>
                        </div>
                    </form>
                </div>
            </div>
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
                    url: "{{ route('recruitment.requests.data') }}",
                    data: function(d) {
                        d.request_number = $('#request_number').val();
                        d.department_id = $('#department_id').val();
                        d.position_id = $('#position_id').val();
                        d.level_id = $('#level_id').val();
                        d.status = $('#status').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                        d.search = $("input[type=search][aria-controls=example1]").val();
                    }
                },
                columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }, {
                    data: "request_number",
                    name: "request_number",
                    orderable: false,
                    className: 'text-center'
                }, {
                    data: "department",
                    name: "department",
                    orderable: false,
                }, {
                    data: "position",
                    name: "position",
                    orderable: false,
                }, {
                    data: "level",
                    name: "level",
                    orderable: false,
                }, {
                    data: "employment_type",
                    name: "employment_type",
                    orderable: false,
                    className: 'text-center'
                }, {
                    data: "status",
                    name: "status",
                    orderable: false,
                    className: 'text-center'
                }, {
                    data: "requested_by",
                    name: "requested_by",
                    orderable: false,
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
            $('#request_number, #department_id, #position_id, #level_id, #status, #date_from, #date_to')
                .keyup(function() {
                    table.draw();
                });
            $('#department_id, #position_id, #level_id, #status, #date_from, #date_to')
                .change(function() {
                    table.draw();
                });

            // Reset functionality
            $('#btn-reset').click(function() {
                $('#request_number, #department_id, #position_id, #level_id, #status, #date_from, #date_to')
                    .val('');
                $('#department_id, #position_id, #level_id, #status').change();
                table.draw();
            });

            // Initialize tooltips for action buttons
            $(document).tooltip({
                selector: '[title]'
            });
        });

        // Modal functionality
        function showApprovalModal(id) {
            $('#approval_fptk_id').val(id);
            $('#approvalModal').modal('show');
        }

        function showRejectionModal(id) {
            $('#rejection_fptk_id').val(id);
            $('#rejectionModal').modal('show');
        }

        // Form submissions
        $(document).ready(function() {
            $('#approvalForm').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                var id = $('#approval_fptk_id').val();

                $.ajax({
                    url: "{{ route('recruitment.requests.approve', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#approvalModal').modal('hide');
                        $('#example1').DataTable().ajax.reload();
                        toastr.success('FPTK request approved successfully');
                    },
                    error: function(xhr) {
                        toastr.error('Error approving FPTK request');
                    }
                });
            });

            $('#rejectionForm').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                var id = $('#rejection_fptk_id').val();

                $.ajax({
                    url: "{{ route('recruitment.requests.reject', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#rejectionModal').modal('hide');
                        $('#example1').DataTable().ajax.reload();
                        toastr.success('FPTK request rejected');
                    },
                    error: function(xhr) {
                        toastr.error('Error rejecting FPTK request');
                    }
                });
            });
        });
    </script>

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
