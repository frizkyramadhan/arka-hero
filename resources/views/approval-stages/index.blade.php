@extends('layouts.main')

@section('title', 'Approval Stages Management')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Approval Stages Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Approval Stages</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Approval Stages Configuration</h3>
                                <div class="card-tools">
                                    <a href="{{ route('approval.stages.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Create New
                                    </a>

                                </div>
                            </div>
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
                                                        <label>Approver</label>
                                                        <select class="form-control select2bs4" id="approver_id"
                                                            name="approver_id">
                                                            <option value="">- All -</option>
                                                            @foreach ($approvers as $approver)
                                                                <option value="{{ $approver->id }}">
                                                                    {{ $approver->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Document Type</label>
                                                        <select class="form-control select2bs4" id="document_type"
                                                            name="document_type">
                                                            <option value="">- All -</option>
                                                            <option value="officialtravel">Official Travel</option>
                                                            <option value="recruitment_request">Recruitment Request</option>
                                                            <option value="leave_request">Leave Request</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Project</label>
                                                        <select class="form-control select2bs4" id="project_id"
                                                            name="project_id">
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
                                                        <label>Department</label>
                                                        <select class="form-control select2bs4" id="department_id"
                                                            name="department_id">
                                                            <option value="">- All -</option>
                                                            @foreach ($departments as $department)
                                                                <option value="{{ $department->id }}">
                                                                    {{ $department->department_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <button type="button" class="btn btn-secondary w-100"
                                                            id="btn-reset" style="margin-bottom: 6px;">
                                                            <i class="fas fa-times"></i> Reset
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="approval-stages-table" class="table table-bordered table-striped"
                                        style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th class="align-middle">No</th>
                                                <th class="align-middle">Approver</th>
                                                <th class="align-middle">Document Type</th>
                                                <th class="align-middle">Approval Order</th>
                                                <th class="align-middle">Projects</th>
                                                <th class="align-middle">Departments</th>
                                                <th class="text-center align-middle" style="width: 12%;">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/toastr/toastr.min.css') }}">
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize Toastr configuration
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            // Initialize Select2
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // Initialize DataTable
            var table = $('#approval-stages-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('approval.stages.data') }}",
                    data: function(d) {
                        d.approver_id = $('#approver_id').val();
                        d.document_type = $('#document_type').val();
                        d.project_id = $('#project_id').val();
                        d.department_id = $('#department_id').val();
                        d.search = $("input[type=search][aria-controls=approval-stages-table]").val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'approver',
                        name: 'approver'
                    },
                    {
                        data: 'document_type',
                        name: 'document_type'
                    },
                    {
                        data: 'approval_order',
                        name: 'approval_order',
                        className: 'text-center'
                    },
                    {
                        data: 'projects',
                        name: 'projects'
                    },
                    {
                        data: 'departments',
                        name: 'departments'
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
            $('#approver_id, #document_type, #project_id, #department_id').change(function() {
                table.draw();
            });

            // Handle reset button
            $('#btn-reset').click(function() {
                $('#approver_id, #document_type, #project_id, #department_id').val('').trigger('change');
                table.draw();
            });

            // Delete approval stage function
            window.deleteApprovalStage = function(id) {
                if (confirm(
                        'Are you sure you want to delete all approval stages for this approver? This action cannot be undone.'
                    )) {
                    $.ajax({
                        url: "{{ route('approval.stages.destroy', '') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Show success message
                                toastr.success(response.message);

                                // Reload DataTable instead of full page
                                $('#approval-stages-table').DataTable().ajax.reload();
                            } else {
                                // Show error message from response
                                toastr.error(response.message ||
                                    'Failed to delete approval stages');
                            }
                        },
                        error: function(xhr) {
                            let errorMessage =
                                'An error occurred while deleting the approval stages.';

                            // Try to get error message from response
                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                } else if (xhr.responseJSON.error) {
                                    errorMessage = xhr.responseJSON.error;
                                }
                            }

                            // Show error message
                            toastr.error(errorMessage);

                            // Log error for debugging
                            console.error('Delete approval stage error:', xhr);
                        }
                    });
                }
            };
        });
    </script>
@endsection
