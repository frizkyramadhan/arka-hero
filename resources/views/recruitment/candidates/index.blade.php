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
                        <li class="breadcrumb-item active">Candidates</li>
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
                                        <strong>Recruitment Candidates (CV)</strong>
                                    </h3>
                                    <div class="d-flex flex-column flex-md-row ms-auto gap-2">
                                        {{-- <a href="{{ route('recruitment.candidates.export') }}"
                                            class="btn btn-primary mb-md-0 ml-1 mb-1">
                                            <i class="fas fa-download"></i> Export
                                        </a> --}}
                                        @can('recruitment-candidates.create')
                                            <a href="{{ route('recruitment.candidates.create') }}"
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
                                                        <label class="form-control-label">Candidate Number</label>
                                                        <input type="text" class="form-control" name="candidate_number"
                                                            id="candidate_number" value="{{ request('candidate_number') }}">
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
                                                        <label class="form-control-label">Email</label>
                                                        <input type="text" class="form-control" name="email"
                                                            id="email" value="{{ request('email') }}">
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
                                                        <label class="form-control-label">Education Level</label>
                                                        <select name="education_level" class="form-control select2bs4"
                                                            id="education_level" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            <option value="SMA/SMK"
                                                                {{ request('education_level') == 'SMA/SMK' ? 'selected' : '' }}>
                                                                SMA/SMK</option>
                                                            <option value="D1"
                                                                {{ request('education_level') == 'D1' ? 'selected' : '' }}>
                                                                D1</option>
                                                            <option value="D2"
                                                                {{ request('education_level') == 'D2' ? 'selected' : '' }}>
                                                                D2</option>
                                                            <option value="D3"
                                                                {{ request('education_level') == 'D3' ? 'selected' : '' }}>
                                                                D3</option>
                                                            <option value="S1"
                                                                {{ request('education_level') == 'S1' ? 'selected' : '' }}>
                                                                S1</option>
                                                            <option value="S2"
                                                                {{ request('education_level') == 'S2' ? 'selected' : '' }}>
                                                                S2</option>
                                                            <option value="S3"
                                                                {{ request('education_level') == 'S3' ? 'selected' : '' }}>
                                                                S3</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Position Applied</label>
                                                        <input type="text" class="form-control" name="position_applied"
                                                            id="position_applied" placeholder="Search position applied"
                                                            value="{{ request('position_applied') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Global Status</label>
                                                        <select name="global_status" class="form-control select2bs4"
                                                            id="global_status" style="width: 100%;">
                                                            <option value="">- All -</option>
                                                            <option value="available"
                                                                {{ request('global_status') == 'available' ? 'selected' : '' }}>
                                                                Available</option>
                                                            <option value="in_process"
                                                                {{ request('global_status') == 'in_process' ? 'selected' : '' }}>
                                                                In Process</option>
                                                            <option value="hired"
                                                                {{ request('global_status') == 'hired' ? 'selected' : '' }}>
                                                                Hired</option>
                                                            <option value="blacklisted"
                                                                {{ request('global_status') == 'blacklisted' ? 'selected' : '' }}>
                                                                Blacklisted</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Registration Date From</label>
                                                        <input type="date" class="form-control"
                                                            name="registration_date_from" id="registration_date_from"
                                                            value="{{ request('registration_date_from') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Registration Date To</label>
                                                        <input type="date" class="form-control"
                                                            name="registration_date_to" id="registration_date_to"
                                                            value="{{ request('registration_date_to') }}">
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
                                                <th class="align-middle text-center">Candidate Number</th>
                                                <th class="align-middle">Full Name</th>
                                                <th class="align-middle">Email</th>
                                                <th class="align-middle">Phone</th>
                                                <th class="align-middle">Education</th>
                                                <th class="align-middle">Position Applied</th>
                                                <th class="align-middle text-center">Global Status</th>
                                                <th class="align-middle text-center">Registration Date</th>
                                                <th class="align-middle text-center" width="15%">Action</th>
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

        <!-- Apply to FPTK Modal -->
        <div class="modal fade" id="applyModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Apply to FPTK</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="applyForm" method="POST" action="">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" id="apply_candidate_id" name="candidate_id">
                            <div class="form-group">
                                <label for="fptk_id">Select FPTK *</label>
                                <select class="form-control select2bs4" id="fptk_id" name="fptk_id" required>
                                    <option value="">Select FPTK</option>
                                    @foreach ($availableFptks as $fptk)
                                        <option value="{{ $fptk->id }}">
                                            {{ $fptk->request_number }} - {{ $fptk->position->position_name }}
                                            ({{ $fptk->department->department_name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="apply_source">Application Source *</label>
                                <select class="form-control select2bs4" id="apply_source" name="source" required>
                                    <option value="">Select Source</option>
                                    <option value="website">Website</option>
                                    <option value="referral">Referral</option>
                                    <option value="job_portal">Job Portal</option>
                                    <option value="social_media">Social Media</option>
                                    <option value="walk_in">Walk In</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="apply_cover_letter">Cover Letter</label>
                                <textarea class="form-control" id="apply_cover_letter" name="cover_letter" rows="3"
                                    placeholder="Enter cover letter (optional)"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Apply</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Blacklist Modal -->
        <div class="modal fade" id="blacklistModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Blacklist Candidate</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="blacklistForm" method="POST" action="">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" id="blacklist_candidate_id" name="candidate_id">
                            <div class="form-group">
                                <label for="blacklist_reason">Blacklist Reason *</label>
                                <textarea class="form-control" id="blacklist_reason" name="blacklist_reason" rows="3"
                                    placeholder="Enter blacklist reason" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Blacklist</button>
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
                    url: "{{ route('recruitment.candidates.data') }}",
                    data: function(d) {
                        d.candidate_number = $('#candidate_number').val();
                        d.fullname = $('#fullname').val();
                        d.email = $('#email').val();
                        d.phone = $('#phone').val();
                        d.education_level = $('#education_level').val();
                        d.position_applied = $('#position_applied').val();
                        d.global_status = $('#global_status').val();
                        d.registration_date_from = $('#registration_date_from').val();
                        d.registration_date_to = $('#registration_date_to').val();
                        d.search = $("input[type=search][aria-controls=example1]").val();
                    }
                },
                columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }, {
                    data: "candidate_number",
                    name: "candidate_number",
                    orderable: false,
                }, {
                    data: "fullname",
                    name: "fullname",
                    orderable: false,
                }, {
                    data: "email",
                    name: "email",
                    orderable: false,
                }, {
                    data: "phone",
                    name: "phone",
                    orderable: false,
                }, {
                    data: "education_level",
                    name: "education_level",
                    orderable: false,
                    className: 'text-center'
                }, {
                    data: "position_applied",
                    name: "position_applied",
                    orderable: false,
                    className: 'text-center'
                }, {
                    data: "global_status",
                    name: "global_status",
                    orderable: false,
                    className: 'text-center'
                }, {
                    data: "created_at",
                    name: "created_at",
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
            $('#candidate_number, #fullname, #email, #phone, #position_applied')
                .keyup(function() {
                    table.draw();
                });
            $('#education_level, #global_status, #registration_date_from, #registration_date_to')
                .change(function() {
                    table.draw();
                });

            // Reset functionality
            $('#btn-reset').click(function() {
                $('#candidate_number, #fullname, #email, #phone, #education_level, #position_applied, #global_status, #registration_date_from, #registration_date_to')
                    .val('');
                $('#education_level, #global_status').change();
            });

            // Modal functionality
            $(document).on('click', '.btn-apply', function() {
                var id = $(this).data('id');
                $('#apply_candidate_id').val(id);
                $('#applyModal').modal('show');
            });

            $(document).on('click', '.btn-blacklist', function() {
                var id = $(this).data('id');
                $('#blacklist_candidate_id').val(id);
                $('#blacklistModal').modal('show');
            });

            // Refresh DataTables after page load (for toast messages)
            $(document).ready(function() {
                // Check if there are toast messages and refresh table
                if ($('.toast-success, .toast-error').length > 0) {
                    table.draw();
                }
            });

            // Form submissions
            $('#applyForm').on('submit', function(e) {
                e.preventDefault();
                var id = $('#apply_candidate_id').val();
                var actionUrl = "{{ route('recruitment.candidates.apply-to-fptk', ':id') }}".replace(':id',
                    id);

                // Set form action and submit
                $(this).attr('action', actionUrl);
                $(this).off('submit').submit();
            });

            $('#blacklistForm').on('submit', function(e) {
                e.preventDefault();
                var id = $('#blacklist_candidate_id').val();
                var actionUrl = "{{ route('recruitment.candidates.blacklist', ':id') }}".replace(':id',
                    id);

                // Set form action and submit
                $(this).attr('action', actionUrl);
                $(this).off('submit').submit();
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
