@extends('layouts.main')

@section('title', 'Create Approval Flow')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create Approval Flow</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('approval.flows.index') }}">Approval Flows</a></li>
                        <li class="breadcrumb-item active">Create New</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Create Approval Flow</h3>
                            <div class="card-tools">
                                <a href="{{ route('approval.flows.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Flows
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('approval.flows.store') }}" id="flowForm">
                                @csrf

                                <!-- Flow Details -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Flow Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                id="name" name="name" value="{{ old('name') }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="document_type">Document Type <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control @error('document_type') is-invalid @enderror"
                                                id="document_type" name="document_type" required>
                                                <option value="">Select Document Type</option>
                                                @foreach ($documentTypes as $key => $label)
                                                    <option value="{{ $key }}"
                                                        {{ old('document_type') == $key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('document_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                        rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                            value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Active</label>
                                    </div>
                                </div>

                                <hr>

                                <!-- Flow Templates -->
                                <div class="form-group">
                                    <label>Quick Templates</label>
                                    <div class="btn-group" role="group">
                                        @foreach ($templates as $key => $template)
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="loadTemplate('{{ $key }}')">
                                                {{ $template['name'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Stages Configuration -->
                                <div class="form-group">
                                    <label>Approval Stages</label>
                                    <div id="stages-container">
                                        <!-- Stages will be added here dynamically -->
                                    </div>
                                    <button type="button" class="btn btn-success" onclick="addStage()">
                                        <i class="fas fa-plus"></i> Add Stage
                                    </button>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Create Flow
                                    </button>
                                    <a href="{{ route('approval.flows.index') }}" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stage Template -->
    <template id="stage-template">
        <div class="stage-item card mb-3" data-stage-index="">
            <div class="card-header">
                <div class="card-title">
                    <h6 class="mb-0">Stage <span class="stage-number"></span></h6>
                </div>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-danger float-right" onclick="removeStage(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Stage Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control stage-name" name="stages[STAGE_INDEX][stage_name]"
                                required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Stage Type</label>
                            <select class="form-control stage-type" name="stages[STAGE_INDEX][stage_type]">
                                <option value="sequential">Sequential</option>
                                <option value="parallel">Parallel</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Escalation (hours)</label>
                            <input type="number" class="form-control" name="stages[STAGE_INDEX][escalation_hours]"
                                value="72" min="1">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" name="stages[STAGE_INDEX][is_mandatory]"
                            value="1" checked>
                        <label class="custom-control-label">Mandatory Stage</label>
                    </div>
                </div>

                <!-- Approvers Section -->
                <div class="approvers-section">
                    <label>Approvers</label>
                    <div class="approvers-container">
                        <!-- Approvers will be added here -->
                    </div>
                    <button type="button" class="btn btn-sm btn-info" onclick="addApprover(this)">
                        <i class="fas fa-plus"></i> Add Approver
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- Approver Template -->
    <template id="approver-template">
        <div class="approver-item row mb-2">
            <div class="col-md-4">
                <select class="form-control approver-type" onchange="updateApproverOptions(this)">
                    <option value="user">User</option>
                    <option value="role">Role</option>
                    <option value="department">Department</option>
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-control approver-id"
                    name="stages[STAGE_INDEX][approvers][APPROVER_INDEX][approver_id]" required>
                    <!-- Options will be populated dynamically -->
                </select>
            </div>
            <div class="col-md-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input"
                        name="stages[STAGE_INDEX][approvers][APPROVER_INDEX][is_backup]" value="1">
                    <label class="custom-control-label">Backup</label>
                </div>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeApprover(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </template>
@endsection

@section('scripts')
    <script>
        let stageIndex = 0;
        let approverIndex = 0;

        // Available data for approvers
        const users = @json($users);
        const roles = @json($roles);
        const departments = @json($departments);
        const templates = @json($templates);

        function addStage() {
            const container = document.getElementById('stages-container');
            const template = document.getElementById('stage-template');
            const stageItem = template.content.cloneNode(true);

            // Update stage index
            stageItem.querySelector('.stage-item').dataset.stageIndex = stageIndex;
            stageItem.querySelector('.stage-number').textContent = stageIndex + 1;

            // Update form field names
            stageItem.querySelectorAll('[name*="STAGE_INDEX"]').forEach(input => {
                input.name = input.name.replace('STAGE_INDEX', stageIndex);
            });

            container.appendChild(stageItem);
            stageIndex++;
        }

        function removeStage(button) {
            if (confirm('Are you sure you want to remove this stage?')) {
                button.closest('.stage-item').remove();
                updateStageNumbers();
            }
        }

        function updateStageNumbers() {
            const stages = document.querySelectorAll('.stage-item');
            stages.forEach((stage, index) => {
                stage.querySelector('.stage-number').textContent = index + 1;
                stage.dataset.stageIndex = index;

                // Update form field names
                stage.querySelectorAll('[name*="stages["]').forEach(input => {
                    const name = input.name;
                    const newName = name.replace(/stages\[\d+\]/, `stages[${index}]`);
                    input.name = newName;
                });
            });
        }

        function addApprover(button) {
            const approversContainer = button.previousElementSibling;
            const template = document.getElementById('approver-template');
            const approverItem = template.content.cloneNode(true);

            // Get the current stage
            const stageItem = button.closest('.stage-item');
            const stageIndex = stageItem.dataset.stageIndex;

            // Count existing approvers in this stage to get the correct index
            const existingApprovers = approversContainer.querySelectorAll('.approver-item');
            const currentApproverIndex = existingApprovers.length;

            // Update approver index
            approverItem.querySelector('.approver-item').dataset.approverIndex = currentApproverIndex;

            // Update form field names
            approverItem.querySelectorAll('[name*="APPROVER_INDEX"]').forEach(input => {
                input.name = input.name.replace('STAGE_INDEX', stageIndex).replace('APPROVER_INDEX',
                    currentApproverIndex);
            });

            approversContainer.appendChild(approverItem);
            updateApproverOptions(approverItem.querySelector('.approver-type'));
        }

        function removeApprover(button) {
            const approverItem = button.closest('.approver-item');
            if (approverItem) {
                approverItem.remove();

                // Update approver indices after removal
                updateApproverIndices(approverItem.closest('.stage-item'));
            }
        }

        function updateApproverIndices(stageItem) {
            if (!stageItem) return;

            const approversContainer = stageItem.querySelector('.approvers-container');
            const approvers = approversContainer.querySelectorAll('.approver-item');

            approvers.forEach((approver, index) => {
                // Update dataset index
                approver.dataset.approverIndex = index;

                // Update form field names
                const stageIndex = stageItem.dataset.stageIndex;
                approver.querySelectorAll('[name*="approvers["]').forEach(input => {
                    const name = input.name;
                    const newName = name.replace(/approvers\[\d+\]/, `approvers[${index}]`);
                    input.name = newName;
                });
            });
        }

        function updateApproverOptions(select) {
            const approverIdSelect = select.parentElement.nextElementSibling.querySelector('.approver-id');
            const type = select.value;

            approverIdSelect.innerHTML = '<option value="">Select...</option>';

            let options = [];
            switch (type) {
                case 'user':
                    options = users.map(user => ({
                        id: user.id,
                        name: user.name
                    }));
                    break;
                case 'role':
                    options = roles.map(role => ({
                        id: role.id,
                        name: role.name
                    }));
                    break;
                case 'department':
                    options = departments.map(dept => ({
                        id: dept.id,
                        name: dept.department_name
                    }));
                    break;
            }

            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.id;
                optionElement.textContent = option.name;
                approverIdSelect.appendChild(optionElement);
            });
        }

        function loadTemplate(templateKey) {
            const template = templates[templateKey];
            if (!template) return;

            // Clear existing stages
            document.getElementById('stages-container').innerHTML = '';
            stageIndex = 0;

            // Load template stages
            template.stages.forEach(stage => {
                addStage();
                const lastStage = document.querySelector('.stage-item:last-child');

                // Set stage values
                lastStage.querySelector('.stage-name').value = stage.stage_name;
                lastStage.querySelector('.stage-type').value = stage.stage_type;
                lastStage.querySelector('[name*="escalation_hours"]').value = stage.escalation_hours;
                lastStage.querySelector('[name*="is_mandatory"]').checked = stage.is_mandatory;
            });
        }

        // Initialize with one stage
        document.addEventListener('DOMContentLoaded', function() {
            addStage();
        });
    </script>
@endsection
