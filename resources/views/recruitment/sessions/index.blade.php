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
                        <li class="breadcrumb-item active">Sessions</li>
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
                                        <strong>Recruitment Sessions</strong>
                                    </h3>
                                    <div class="d-flex flex-column flex-md-row ms-auto gap-2">
                                        @can('recruitment-session.export')
                                            <a href="{{ route('recruitment.sessions.export') }}"
                                                class="btn btn-primary mb-md-0 ml-1 mb-1">
                                                <i class="fas fa-download"></i> Export
                                            </a>
                                        @endcan
                                        @can('recruitment-session.dashboard')
                                            <a href="{{ route('recruitment.sessions.dashboard') }}"
                                                class="btn btn-info mb-md-0 ml-1 mb-1">
                                                <i class="fas fa-chart-bar"></i> Dashboard
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
                                                        <label class="form-control-label">Session Number</label>
                                                        <input type="text" class="form-control" name="session_number"
                                                            id="session_number" value="{{ request('session_number') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Candidate Name</label>
                                                        <input type="text" class="form-control" name="candidate_name"
                                                            id="candidate_name" value="{{ request('candidate_name') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">FPTK Number</label>
                                                        <input type="text" class="form-control" name="fptk_number"
                                                            id="fptk_number" value="{{ request('fptk_number') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Current Stage</label>
                                                        <select name="current_stage" class="form-control select2bs4"
                                                            id="current_stage" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            <option value="cv_review"
                                                                {{ request('current_stage') == 'cv_review' ? 'selected' : '' }}>
                                                                CV Review</option>
                                                            <option value="psikotes"
                                                                {{ request('current_stage') == 'psikotes' ? 'selected' : '' }}>
                                                                Psikotes</option>
                                                            <option value="tes_teori"
                                                                {{ request('current_stage') == 'tes_teori' ? 'selected' : '' }}>
                                                                Tes Teori</option>
                                                            <option value="interview_hr"
                                                                {{ request('current_stage') == 'interview_hr' ? 'selected' : '' }}>
                                                                Interview HR</option>
                                                            <option value="interview_user"
                                                                {{ request('current_stage') == 'interview_user' ? 'selected' : '' }}>
                                                                Interview User</option>
                                                            <option value="offering"
                                                                {{ request('current_stage') == 'offering' ? 'selected' : '' }}>
                                                                Offering</option>
                                                            <option value="mcu"
                                                                {{ request('current_stage') == 'mcu' ? 'selected' : '' }}>
                                                                MCU</option>
                                                            <option value="hire"
                                                                {{ request('current_stage') == 'hire' ? 'selected' : '' }}>
                                                                Hire</option>
                                                            <option value="onboarding"
                                                                {{ request('current_stage') == 'onboarding' ? 'selected' : '' }}>
                                                                Onboarding</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Stage Status</label>
                                                        <select name="stage_status" class="form-control select2bs4"
                                                            id="stage_status" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            <option value="pending"
                                                                {{ request('stage_status') == 'pending' ? 'selected' : '' }}>
                                                                Pending</option>
                                                            <option value="in_progress"
                                                                {{ request('stage_status') == 'in_progress' ? 'selected' : '' }}>
                                                                In Progress</option>
                                                            <option value="completed"
                                                                {{ request('stage_status') == 'completed' ? 'selected' : '' }}>
                                                                Completed</option>
                                                            <option value="failed"
                                                                {{ request('stage_status') == 'failed' ? 'selected' : '' }}>
                                                                Failed</option>
                                                            <option value="skipped"
                                                                {{ request('stage_status') == 'skipped' ? 'selected' : '' }}>
                                                                Skipped</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Final Status</label>
                                                        <select name="final_status" class="form-control select2bs4"
                                                            id="final_status" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            <option value="in_process"
                                                                {{ request('final_status') == 'in_process' ? 'selected' : '' }}>
                                                                In Process</option>
                                                            <option value="hired"
                                                                {{ request('final_status') == 'hired' ? 'selected' : '' }}>
                                                                Hired</option>
                                                            <option value="rejected"
                                                                {{ request('final_status') == 'rejected' ? 'selected' : '' }}>
                                                                Rejected</option>
                                                            <option value="withdrawn"
                                                                {{ request('final_status') == 'withdrawn' ? 'selected' : '' }}>
                                                                Withdrawn</option>
                                                            <option value="cancelled"
                                                                {{ request('final_status') == 'cancelled' ? 'selected' : '' }}>
                                                                Cancelled</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Applied Date From</label>
                                                        <input type="date" class="form-control"
                                                            name="applied_date_from" id="applied_date_from"
                                                            value="{{ request('applied_date_from') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Applied Date To</label>
                                                        <input type="date" class="form-control" name="applied_date_to"
                                                            id="applied_date_to"
                                                            value="{{ request('applied_date_to') }}">
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
                                                <th class="align-middle text-center">Session Number</th>
                                                <th class="align-middle">Candidate</th>
                                                <th class="align-middle">FPTK</th>
                                                <th class="align-middle">Position</th>
                                                <th class="align-middle">Current Stage</th>
                                                <th class="align-middle text-center">Stage Status</th>
                                                <th class="align-middle text-center">Progress</th>
                                                <th class="align-middle text-center">Final Status</th>
                                                <th class="align-middle text-center">Applied Date</th>
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

        <!-- Advance Stage Modal -->
        <div class="modal fade" id="advanceModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Advance to Next Stage</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="advanceForm">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" id="advance_session_id" name="session_id">
                            <div class="form-group">
                                <label for="advance_notes">Stage Notes</label>
                                <textarea class="form-control" id="advance_notes" name="notes" rows="3"
                                    placeholder="Enter notes for stage completion (optional)"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Advance</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Session Modal -->
        <div class="modal fade" id="rejectModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Reject Session</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="rejectForm">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" id="reject_session_id" name="session_id">
                            <div class="form-group">
                                <label for="reject_reason">Rejection Reason *</label>
                                <textarea class="form-control" id="reject_reason" name="reason" rows="3"
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

        <!-- Complete Session Modal -->
        <div class="modal fade" id="completeModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Complete Session</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="completeForm">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" id="complete_session_id" name="session_id">
                            <div class="form-group">
                                <label for="complete_notes">Completion Notes</label>
                                <textarea class="form-control" id="complete_notes" name="notes" rows="3"
                                    placeholder="Enter completion notes (optional)"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Complete</button>
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
                buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('recruitment.sessions.data') }}",
                    data: function(d) {
                        d.session_number = $('#session_number').val();
                        d.candidate_name = $('#candidate_name').val();
                        d.fptk_number = $('#fptk_number').val();
                        d.current_stage = $('#current_stage').val();
                        d.stage_status = $('#stage_status').val();
                        d.final_status = $('#final_status').val();
                        d.applied_date_from = $('#applied_date_from').val();
                        d.applied_date_to = $('#applied_date_to').val();
                        d.search = $("input[type=search][aria-controls=example1]").val();
                    }
                },
                columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }, {
                    data: "session_number",
                    name: "session_number",
                    orderable: false,
                    className: 'text-center'
                }, {
                    data: "candidate_name",
                    name: "candidate_name",
                    orderable: false,
                }, {
                    data: "fptk_number",
                    name: "fptk_number",
                    orderable: false,
                }, {
                    data: "position_name",
                    name: "position_name",
                    orderable: false,
                }, {
                    data: "current_stage",
                    name: "current_stage",
                    orderable: false,
                }, {
                    data: "stage_status",
                    name: "stage_status",
                    orderable: false,
                    className: 'text-center'
                }, {
                    data: "overall_progress",
                    name: "overall_progress",
                    orderable: false,
                    className: 'text-center'
                }, {
                    data: "final_status",
                    name: "final_status",
                    orderable: false,
                    className: 'text-center'
                }, {
                    data: "applied_date",
                    name: "applied_date",
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
            $('#session_number, #candidate_name, #fptk_number, #current_stage, #stage_status, #final_status, #applied_date_from, #applied_date_to')
                .keyup(function() {
                    table.draw();
                });
            $('#current_stage, #stage_status, #final_status, #applied_date_from, #applied_date_to')
                .change(function() {
                    table.draw();
                });

            // Reset functionality
            $('#btn-reset').click(function() {
                $('#session_number, #candidate_name, #fptk_number, #current_stage, #stage_status, #final_status, #applied_date_from, #applied_date_to')
                    .val('');
                $('#current_stage, #stage_status, #final_status').change();
            });

            // Modal functionality
            $(document).on('click', '.btn-advance', function() {
                var id = $(this).data('id');
                $('#advance_session_id').val(id);
                $('#advanceModal').modal('show');
            });

            $(document).on('click', '.btn-reject', function() {
                var id = $(this).data('id');
                $('#reject_session_id').val(id);
                $('#rejectModal').modal('show');
            });

            $(document).on('click', '.btn-complete', function() {
                var id = $(this).data('id');
                $('#complete_session_id').val(id);
                $('#completeModal').modal('show');
            });

            // Form submissions
            $('#advanceForm').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                var id = $('#advance_session_id').val();

                $.ajax({
                    url: "{{ route('recruitment.sessions.advance', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#advanceModal').modal('hide');
                        table.draw();
                        toastr.success('Session advanced to next stage successfully');
                    },
                    error: function(xhr) {
                        toastr.error('Error advancing session');
                    }
                });
            });

            $('#rejectForm').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                var id = $('#reject_session_id').val();

                $.ajax({
                    url: "{{ route('recruitment.sessions.reject', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#rejectModal').modal('hide');
                        table.draw();
                        toastr.success('Session rejected successfully');
                    },
                    error: function(xhr) {
                        toastr.error('Error rejecting session');
                    }
                });
            });

            $('#completeForm').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                var id = $('#complete_session_id').val();

                $.ajax({
                    url: "{{ route('recruitment.sessions.complete', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#completeModal').modal('hide');
                        table.draw();
                        toastr.success('Session completed successfully');
                    },
                    error: function(xhr) {
                        toastr.error('Error completing session');
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
