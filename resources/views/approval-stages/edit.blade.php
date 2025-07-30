@extends('layouts.main')

@section('title', 'Edit Approval Stage')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Approval Stage</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('approval.stages.index') }}">Approval Stages</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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
                            <h3 class="card-title">Edit Approval Stage</h3>
                        </div>
                        <form action="{{ route('approval.stages.update', $approvalStage->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="approver_id">Approver <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('approver_id') is-invalid @enderror"
                                        name="approver_id" id="approver_id" required>
                                        <option value="">Select Approver</option>
                                        @foreach ($approvers as $approver)
                                            <option value="{{ $approver->id }}"
                                                {{ old('approver_id', $approvalStage->approver_id) == $approver->id ? 'selected' : '' }}>
                                                {{ $approver->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('approver_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Selection Panels -->
                                <div class="row">
                                    <!-- Projects Selection -->
                                    <div class="col-md-4">
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
                                                        <input type="checkbox" class="custom-control-input project-checkbox"
                                                            id="project_{{ $project->id }}" name="projects[]"
                                                            value="{{ $project->id }}"
                                                            {{ in_array($project->id, old('projects', $selectedProjects ?? [])) ? 'checked' : '' }}>
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
                                    <div class="col-md-4">
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
                                                            {{ in_array($department->id, old('departments', $selectedDepartments ?? [])) ? 'checked' : '' }}>
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

                                    <!-- Document Types Selection -->
                                    <div class="col-md-4">
                                        <div class="card card-warning">
                                            <div class="card-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">Document Types</h6>
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="select_all_documents">
                                                        <label class="custom-control-label" for="select_all_documents"
                                                            style="font-size: 12px;">
                                                            Select All
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input document-checkbox"
                                                        id="document_officialtravel" name="documents[]"
                                                        value="officialtravel"
                                                        {{ in_array('officialtravel', old('documents', $selectedDocuments ?? [])) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="document_officialtravel"
                                                        style="font-size: 13px;">
                                                        Official Travel
                                                    </label>
                                                </div>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input document-checkbox"
                                                        id="document_recruitment_request" name="documents[]"
                                                        value="recruitment_request"
                                                        {{ in_array('recruitment_request', old('documents', $selectedDocuments ?? [])) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="document_recruitment_request"
                                                        style="font-size: 13px;">
                                                        Recruitment Request
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        @error('documents')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Approval Stage
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
            handleSelectAll('#select_all_documents', '.document-checkbox');

            // Initialize states after DOM is ready
            setTimeout(function() {
                updateSelectAllState('#select_all_projects', '.project-checkbox');
                updateSelectAllState('#select_all_departments', '.department-checkbox');
                updateSelectAllState('#select_all_documents', '.document-checkbox');
            }, 200);
        });
    </script>
@endsection
