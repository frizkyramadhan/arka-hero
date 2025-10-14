@extends('layouts.main')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Approval Stage</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('approval.stages.index') }}">Approval Stages</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Create New Approval Stage</h3>
                        </div>
                        <form action="{{ route('approval.stages.store') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <!-- Duplicate Error Display -->
                                @error('duplicate')
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Duplicate Combination Error:</strong>
                                        <div class="mt-2">
                                            {!! $message !!}
                                        </div>
                                    </div>
                                @enderror

                                <!-- Approver Selection -->
                                <div class="form-group">
                                    <label for="approver_id">Approver <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('approver_id') is-invalid @enderror"
                                        name="approver_id" id="approver_id" required>
                                        <option value="">Select Approver</option>
                                        @foreach ($approvers as $approver)
                                            <option value="{{ $approver->id }}"
                                                {{ old('approver_id') == $approver->id ? 'selected' : '' }}>
                                                {{ $approver->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('approver_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Document Type Selection -->
                                <div class="form-group">
                                    <label for="document_type">Document Type <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('document_type') is-invalid @enderror"
                                        name="document_type" id="document_type" required>
                                        <option value="">Select Document Type</option>
                                        <option value="officialtravel"
                                            {{ old('document_type') == 'officialtravel' ? 'selected' : '' }}>
                                            Official Travel
                                        </option>
                                        <option value="recruitment_request"
                                            {{ old('document_type') == 'recruitment_request' ? 'selected' : '' }}>
                                            Recruitment Request
                                        </option>
                                        <option value="leave_request"
                                            {{ old('document_type') == 'leave_request' ? 'selected' : '' }}>
                                            Leave Request
                                        </option>
                                    </select>
                                    <small class="form-text text-muted">
                                        Select the document type for this approval stage
                                    </small>
                                    @error('document_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Request Reason Selection (Only for Recruitment Request) -->
                                <div class="form-group" id="request_reason_section" style="display: none;">
                                    <div class="card card-info">
                                        <div class="card-header">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Request Reason <span class="text-danger">*</span></h6>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="select_all_reasons" onchange="toggleAllRequestReasons()">
                                                    <label class="custom-control-label" for="select_all_reasons"
                                                        style="font-size: 12px;">
                                                        Select All
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input request-reason-checkbox"
                                                    name="request_reasons[]" value="additional_workplan"
                                                    id="additional_workplan"
                                                    {{ in_array('additional_workplan', old('request_reasons', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="additional_workplan"
                                                    style="font-size: 13px;">
                                                    Additional - Workplan
                                                </label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input request-reason-checkbox"
                                                    name="request_reasons[]" value="replacement_resign"
                                                    id="replacement_resign"
                                                    {{ in_array('replacement_resign', old('request_reasons', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="replacement_resign"
                                                    style="font-size: 13px;">
                                                    Replacement - Resign, Termination, End of Contract
                                                </label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input request-reason-checkbox"
                                                    name="request_reasons[]" value="replacement_promotion"
                                                    id="replacement_promotion"
                                                    {{ in_array('replacement_promotion', old('request_reasons', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="replacement_promotion"
                                                    style="font-size: 13px;">
                                                    Replacement - Promotion, Mutation, Demotion
                                                </label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input request-reason-checkbox"
                                                    name="request_reasons[]" value="other" id="other"
                                                    {{ in_array('other', old('request_reasons', [])) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="other" style="font-size: 13px;">
                                                    Other
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        Select which request reasons this approval stage applies to. Leave empty for
                                        backward compatibility.
                                    </small>
                                    @error('request_reasons')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Approval Order Input -->
                                <div class="form-group">
                                    <label for="approval_order">Approval Order <span class="text-danger">*</span></label>
                                    <input type="number"
                                        class="form-control @error('approval_order') is-invalid @enderror"
                                        name="approval_order" id="approval_order" value="{{ old('approval_order', 1) }}"
                                        min="1" required placeholder="Enter approval order (e.g., 1, 2, 3)">
                                    <small class="form-text text-muted">
                                        Sequential order for approval process. Lower numbers are processed first.
                                        <br><strong>Note:</strong> Steps with the same order number can be processed in
                                        parallel after previous orders are completed.
                                    </small>
                                    @error('approval_order')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Selection Panels -->
                                <div class="row">
                                    <!-- Projects Selection -->
                                    <div class="col-md-6">
                                        <div class="card card-primary">
                                            <div class="card-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">Projects</h6>
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="select_all_projects">
                                                        <label class="custom-control-label" for="select_all_projects"
                                                            style="font-size: 12px;">
                                                            Select All
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                                @foreach ($projects as $project)
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox"
                                                            class="custom-control-input project-checkbox"
                                                            id="project_{{ $project->id }}" name="projects[]"
                                                            value="{{ $project->id }}"
                                                            {{ in_array($project->id, old('projects', [])) ? 'checked' : '' }}>
                                                        <label class="custom-control-label"
                                                            for="project_{{ $project->id }}" style="font-size: 13px;">
                                                            {{ $project->project_code }} - {{ $project->project_name }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @error('projects')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Departments Selection -->
                                    <div class="col-md-6">
                                        <div class="card card-success">
                                            <div class="card-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">Departments</h6>
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="select_all_departments">
                                                        <label class="custom-control-label" for="select_all_departments"
                                                            style="font-size: 12px;">
                                                            Select All
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                                @foreach ($departments as $department)
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox"
                                                            class="custom-control-input department-checkbox"
                                                            id="department_{{ $department->id }}" name="departments[]"
                                                            value="{{ $department->id }}"
                                                            {{ in_array($department->id, old('departments', [])) ? 'checked' : '' }}>
                                                        <label class="custom-control-label"
                                                            for="department_{{ $department->id }}"
                                                            style="font-size: 13px;">
                                                            {{ $department->department_name }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @error('departments')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Approval Stage
                                </button>
                                <a href="{{ route('approval.stages.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('scripts')
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 if available
            $('.select2').select2({
                theme: 'bootstrap4'
            });

            // Function to handle select all functionality
            function handleSelectAll(selectAllId, checkboxClass) {
                $(selectAllId).on('change', function() {
                    const isChecked = $(this).is(':checked');
                    $(checkboxClass).prop('checked', isChecked);
                });

                // Handle individual checkbox changes
                $(checkboxClass).on('change', function() {
                    updateSelectAllState(selectAllId, checkboxClass);
                });
            }

            // Function to update select all state
            function updateSelectAllState(selectAllId, checkboxClass) {
                const totalCheckboxes = $(checkboxClass).length;
                const checkedCheckboxes = $(checkboxClass + ':checked').length;

                if (checkedCheckboxes === 0) {
                    $(selectAllId).prop('indeterminate', false).prop('checked', false);
                } else if (checkedCheckboxes === totalCheckboxes) {
                    $(selectAllId).prop('indeterminate', false).prop('checked', true);
                } else {
                    $(selectAllId).prop('indeterminate', true).prop('checked', false);
                }
            }

            // Initialize select all functionality
            handleSelectAll('#select_all_projects', '.project-checkbox');
            handleSelectAll('#select_all_departments', '.department-checkbox');
            handleSelectAll('#select_all_reasons', '.request-reason-checkbox');

            // Handle document type change to show/hide request reason section
            $('#document_type').on('change', function() {
                const documentType = $(this).val();
                const requestReasonSection = $('#request_reason_section');

                if (documentType === 'recruitment_request') {
                    requestReasonSection.show();
                } else {
                    requestReasonSection.hide();
                    // Uncheck all request reason checkboxes when hidden
                    $('.request-reason-checkbox').prop('checked', false);
                    $('#select_all_reasons').prop('checked', false).prop('indeterminate', false);
                }
            });

            // Initialize states after DOM is ready
            setTimeout(function() {
                updateSelectAllState('#select_all_projects', '.project-checkbox');
                updateSelectAllState('#select_all_departments', '.department-checkbox');
                updateSelectAllState('#select_all_reasons', '.request-reason-checkbox');

                // Show request reason section if document type is already recruitment_request
                if ($('#document_type').val() === 'recruitment_request') {
                    $('#request_reason_section').show();
                }
            }, 200);
        });

        // Function to toggle all request reasons (called from HTML)
        function toggleAllRequestReasons() {
            const selectAll = $('#select_all_reasons');
            const isChecked = selectAll.is(':checked');
            $('.request-reason-checkbox').prop('checked', isChecked);
        }
    </script>
@endsection
