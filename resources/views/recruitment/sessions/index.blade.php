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
                                        <b>Recruitment Sessions</b>
                                    </h3>
                                    <div class="d-flex flex-column flex-md-row ms-auto gap-2">
                                        <a href="{{ route('dashboard.recruitment') }}"
                                            class="btn btn-info mb-md-0 ml-1 mb-1">
                                            <i class="fas fa-chart-bar"></i> Dashboard
                                        </a>
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
                                                        <label class="form-control-label">FPTK Number</label>
                                                        <input type="text" class="form-control" name="fptk_number"
                                                            id="fptk_number" value="{{ request('fptk_number') }}">
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
                                                        <label class="form-control-label">Required Date From</label>
                                                        <input type="date" class="form-control" name="required_date_from"
                                                            id="required_date_from"
                                                            value="{{ request('required_date_from') }}">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="form-group">
                                                        <label class="form-control-label">Required Date To</label>
                                                        <input type="date" class="form-control" name="required_date_to"
                                                            id="required_date_to"
                                                            value="{{ request('required_date_to') }}">
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
                                                <th class="align-middle">FPTK No.</th>
                                                <th class="align-middle">Position</th>
                                                <th class="align-middle text-center">Candidate Count</th>
                                                <th class="align-middle text-center">Overall Progress</th>
                                                <th class="align-middle text-center">Final Status</th>
                                                <th class="align-middle text-center">Required Date</th>
                                                <th class="align-middle text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /.card-body -->
            </div>
        </div>
        <!-- /.card -->
    </section>

    <!-- Add Candidate Modal -->
    <div class="modal fade" id="addCandidateModal" tabindex="-1" role="dialog" aria-labelledby="addCandidateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCandidateModalLabel">
                        <i class="fas fa-plus"></i> Add Candidate to FPTK
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="candidate_search">Search Candidate/CV</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="candidate_search"
                                placeholder="Enter candidate name, email, or position applied...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="search_candidate">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="search_results" class="mt-3" style="display: none;">
                        <h6>Search Results</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th class="align-middle">Name</th>
                                        <th class="align-middle">Email</th>
                                        <th class="align-middle">Phone</th>
                                        <th class="align-middle">Position Applied</th>
                                        <th class="align-middle text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="candidate_results">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
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
                    url: "{{ route('recruitment.sessions.data') }}",
                    data: function(d) {
                        d.fptk_number = $('#fptk_number').val();
                        d.department_id = $('#department_id').val();
                        d.position_id = $('#position_id').val();
                        d.required_date_from = $('#required_date_from').val();
                        d.required_date_to = $('#required_date_to').val();
                        d.search = $("input[type=search][aria-controls=example1]").val();
                    }
                },
                columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }, {
                    data: "fptk_number",
                    name: "fptk_number",
                    orderable: false,
                }, {
                    data: "position_name",
                    name: "position_name",
                    orderable: false,
                }, {
                    data: "candidate_count",
                    name: "candidate_count",
                    orderable: false,
                    className: 'text-center'
                }, {
                    data: "overall_progress",
                    name: "overall_progress",
                    orderable: false,
                    className: 'text-center',
                }, {
                    data: "final_status",
                    name: "final_status",
                    orderable: false,
                    className: 'text-center'
                }, {
                    data: "required_date",
                    name: "required_date",
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
            $('#fptk_number, #department_id, #position_id, #required_date_from, #required_date_to')
                .keyup(function() {
                    table.draw();
                });
            $('#department_id, #position_id, #required_date_from, #required_date_to')
                .change(function() {
                    table.draw();
                });

            // Reset functionality
            $('#btn-reset').click(function() {
                $('#fptk_number, #department_id, #position_id, #required_date_from, #required_date_to')
                    .val('');
                $('#department_id, #position_id').change();
                table.draw();
            });

            // Initialize tooltips for action buttons
            $(document).tooltip({
                selector: '[title]'
            });

            // Search candidate functionality
            $('#search_candidate').click(function() {
                searchCandidates();
            });

            $('#candidate_search').keypress(function(e) {
                if (e.which == 13) {
                    searchCandidates();
                }
            });

            function searchCandidates() {
                var query = $('#candidate_search').val();
                if (query.length < 3) {
                    alert('Please enter at least 3 characters to search');
                    return;
                }

                $.ajax({
                    url: '{{ route('recruitment.candidates.search') }}',
                    type: 'GET',
                    data: {
                        query: query
                    },
                    success: function(response) {
                        displaySearchResults(response.candidates);
                    },
                    error: function() {
                        alert('Error searching candidates');
                    }
                });
            }

            function displaySearchResults(candidates) {
                var tbody = $('#candidate_results');
                tbody.empty();

                if (candidates.length === 0) {
                    tbody.append(
                        '<tr><td colspan="5" class="text-center text-muted">No candidates found</td></tr>');
                } else {
                    candidates.forEach(function(candidate) {
                        var row = '<tr>' +
                            '<td>' + candidate.name + '</td>' +
                            '<td>' + candidate.email + '</td>' +
                            '<td>' + candidate.phone + '</td>' +
                            '<td>' + (candidate.position_applied || '-') + '</td>' +
                            '<td class="text-center">' +
                            '<button class="btn btn-sm btn-primary add-candidate-btn" data-candidate-id="' +
                            candidate.id + '">' +
                            '<i class="fas fa-plus"></i> Add</button>' +
                            '</td>' +
                            '</tr>';
                        tbody.append(row);
                    });
                }

                $('#search_results').show();
            }

            // Add candidate to FPTK from table row
            $(document).on('click', '.add-candidate-btn[data-fptk-id]', function() {
                var fptkId = $(this).data('fptk-id');
                var fptkNumber = $(this).data('fptk-number');
                var position = $(this).data('position');

                // Set FPTK info in modal
                $('#addCandidateModal .modal-title').html(
                    '<i class="fas fa-plus"></i> Add Candidate to FPTK: ' + fptkNumber);
                $('#addCandidateModal .modal-body').prepend(
                    '<div class="alert alert-info"><strong>FPTK:</strong> ' + fptkNumber +
                    ' | <strong>Position:</strong> ' + position + '</div>');
                $('#addCandidateModal').data('fptk-id', fptkId);

                // Clear previous search
                $('#candidate_search').val('');
                $('#search_results').hide();

                // Show modal
                $('#addCandidateModal').modal('show');
            });

            // Add candidate to FPTK from search results
            $(document).on('click', '.add-candidate-btn[data-candidate-id]', function() {
                var candidateId = $(this).data('candidate-id');
                var fptkId = $('#addCandidateModal').data('fptk-id');

                if (!fptkId) {
                    alert('Please select a FPTK first');
                    return;
                }

                // Add candidate to FPTK
                $.ajax({
                    url: '{{ route('recruitment.sessions.store') }}',
                    type: 'POST',
                    data: {
                        candidate_id: candidateId,
                        fptk_id: fptkId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#addCandidateModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            });
                            // Reload the table
                            table.draw();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        var message = 'Error adding candidate to FPTK';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });

            // Clear modal when closed
            $('#addCandidateModal').on('hidden.bs.modal', function() {
                $(this).find('.alert').remove();
                $(this).find('.modal-title').html('<i class="fas fa-plus"></i> Add Candidate to FPTK');
                $(this).removeData('fptk-id');
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
