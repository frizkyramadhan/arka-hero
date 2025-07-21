@extends('layouts.main')

@section('title', 'Edit Approval Flow')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Approval Flow</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('approval.flows.index') }}">Approval Flows</a></li>
                        <li class="breadcrumb-item active">Edit {{ $flow->name }}</li>
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
                            <h3 class="card-title">Edit Approval Flow: {{ $flow->name }}</h3>
                            <div class="card-tools">
                                <a href="{{ route('approval.flows.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Flows
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('approval.flows.update', $flow) }}" id="flowForm">
                                @csrf
                                @method('PUT')

                                <!-- Flow Details -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Flow Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                id="name" name="name" value="{{ old('name', $flow->name) }}"
                                                required>
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
                                                        {{ old('document_type', $flow->document_type) == $key ? 'selected' : '' }}>
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
                                        rows="3">{{ old('description', $flow->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                            value="1" {{ old('is_active', $flow->is_active) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Active</label>
                                    </div>
                                </div>

                                <hr>

                                <!-- Stages Configuration -->
                                <div class="form-group">
                                    <label>Approval Stages</label>
                                    <div class="alert alert-info" id="stages-info" style="display: none;">
                                        <i class="fas fa-info-circle"></i>
                                        <span id="stages-info-text"></span>
                                    </div>
                                    <div id="stages-container">
                                        <!-- Existing stages will be loaded here -->
                                    </div>
                                    <button type="button" class="btn btn-success" onclick="addStage()">
                                        <i class="fas fa-plus"></i> Add Stage
                                    </button>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary" onclick="validateForm()">
                                        <i class="fas fa-save"></i> Update Flow
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
            <input type="hidden" class="stage-order-input" name="stages[STAGE_INDEX][stage_order]">
            <div class="card-header">
                <div class="card-title">
                    <h6 class="mb-0">Stage <span class="stage-number"></span></h6>
                </div>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-danger float-right" onclick="removeStage(this)">
                        <i class="fas fa-trash"></i> Remove Stage
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
                <select class="form-control approver-type" onchange="updateApproverOptions(this)"
                    name="stages[STAGE_INDEX][approvers][APPROVER_INDEX][approver_type]" required>
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
        const existingFlow = @json($flow);

        console.log('Existing flow data:', existingFlow);
        console.log('Flow stages:', existingFlow.stages);
        console.log('Users:', users);
        console.log('Roles:', roles);
        console.log('Departments:', departments);

        // Predefined stages for different document types
        const documentTypeStages = {
            'officialtravel': [{
                    stage_name: 'Recommendation',
                    stage_type: 'sequential',
                    escalation_hours: 24,
                    is_mandatory: true,
                    stage_order: 1
                },
                {
                    stage_name: 'Approval',
                    stage_type: 'sequential',
                    escalation_hours: 48,
                    is_mandatory: true,
                    stage_order: 2
                }
            ],
            'recruitment_request': [{
                    stage_name: 'HR Acknowledgment',
                    stage_type: 'sequential',
                    escalation_hours: 24,
                    is_mandatory: true,
                    stage_order: 1
                },
                {
                    stage_name: 'PM Approval',
                    stage_type: 'sequential',
                    escalation_hours: 48,
                    is_mandatory: true,
                    stage_order: 2
                },
                {
                    stage_name: 'Director Approval',
                    stage_type: 'sequential',
                    escalation_hours: 72,
                    is_mandatory: true,
                    stage_order: 3
                }
            ],
            'employee_registration': [{
                stage_name: 'Admin Review',
                stage_type: 'sequential',
                escalation_hours: 24,
                is_mandatory: true,
                stage_order: 1
            }]
        };

        function addStage() {
            const container = document.getElementById('stages-container');
            const template = document.getElementById('stage-template');

            if (!container || !template) {
                console.error('Required elements not found');
                return;
            }

            const stageItem = template.content.cloneNode(true);

            // Update stage index
            const stageItemElement = stageItem.querySelector('.stage-item');
            const stageNumberElement = stageItem.querySelector('.stage-number');
            const stageOrderInput = stageItem.querySelector('.stage-order-input');

            if (stageItemElement) {
                stageItemElement.dataset.stageIndex = stageIndex;
            }

            if (stageNumberElement) {
                stageNumberElement.textContent = stageIndex + 1;
            }

            // Set stage order value
            if (stageOrderInput) {
                stageOrderInput.value = stageIndex + 1;
            }

            // Update form field names
            stageItem.querySelectorAll('[name*="STAGE_INDEX"]').forEach(input => {
                input.name = input.name.replace('STAGE_INDEX', stageIndex);
            });

            container.appendChild(stageItem);
            stageIndex++;
        }

        function removeStage(button) {
            if (!button) return;

            if (confirm('Are you sure you want to remove this stage?')) {
                const stageItem = button.closest('.stage-item');
                if (stageItem) {
                    stageItem.remove();
                    updateStageNumbers();
                }
            }
        }

        function updateStageNumbers() {
            const stages = document.querySelectorAll('.stage-item');
            stages.forEach((stage, index) => {
                const stageNumberElement = stage.querySelector('.stage-number');
                if (stageNumberElement) {
                    stageNumberElement.textContent = index + 1;
                }

                const stageOrderInput = stage.querySelector('.stage-order-input');
                if (stageOrderInput) {
                    stageOrderInput.value = index + 1;
                }

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
            const approverItemElement = approverItem.querySelector('.approver-item');
            if (approverItemElement) {
                approverItemElement.dataset.approverIndex = currentApproverIndex;
            }

            // Update form field names
            approverItem.querySelectorAll('[name*="APPROVER_INDEX"]').forEach(input => {
                input.name = input.name.replace('STAGE_INDEX', stageIndex).replace('APPROVER_INDEX',
                    currentApproverIndex);
            });

            approversContainer.appendChild(approverItem);

            // Update approver options after adding to DOM
            const approverTypeSelect = approversContainer.querySelector('.approver-item:last-child .approver-type');
            if (approverTypeSelect) {
                updateApproverOptions(approverTypeSelect);
            }
        }

        function removeApprover(button) {
            if (!button) return;

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
            if (!select) return;

            const approverItem = select.closest('.approver-item');
            if (!approverItem) return;

            const approverIdSelect = approverItem.querySelector('.approver-id');
            if (!approverIdSelect) return;

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
                        name: dept.name
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

        function loadExistingStages() {
            // Clear existing stages first
            const container = document.getElementById('stages-container');
            container.innerHTML = '';

            if (!existingFlow.stages || existingFlow.stages.length === 0) {
                addStage();
                return;
            }

            // Sort stages by stage_order to ensure correct order
            const sortedStages = existingFlow.stages.sort((a, b) => a.stage_order - b.stage_order);

            console.log('Loading existing stages:', sortedStages);

            sortedStages.forEach((stage, index) => {
                addStage();
                const lastStage = document.querySelector('.stage-item:last-child');

                // Reset approver index for this stage
                const approversContainer = lastStage.querySelector('.approvers-container');
                if (approversContainer) {
                    approversContainer.innerHTML = '';
                }

                // Set stage values
                lastStage.querySelector('.stage-name').value = stage.stage_name;
                lastStage.querySelector('.stage-type').value = stage.stage_type;
                lastStage.querySelector('[name*="escalation_hours"]').value = stage.escalation_hours;
                lastStage.querySelector('[name*="is_mandatory"]').checked = stage.is_mandatory;

                console.log(`Loading stage ${index + 1}:`, stage.stage_name, 'with approvers:', stage.approvers);

                // Load approvers for this stage
                if (stage.approvers && stage.approvers.length > 0) {
                    stage.approvers.forEach((approver, approverIndex) => {
                        console.log(`Loading approver ${approverIndex + 1}:`, approver);

                        const addApproverBtn = lastStage.querySelector('.approvers-section button');
                        if (addApproverBtn) {
                            addApprover(addApproverBtn);

                            // Get the approver that was just added (last one in this stage)
                            const approversInStage = lastStage.querySelectorAll('.approver-item');
                            const currentApprover = approversInStage[approversInStage.length - 1];

                            // Alternative: Get the last added approver more reliably
                            const allApprovers = document.querySelectorAll('.approver-item');
                            const currentApproverAlt = allApprovers[allApprovers.length - 1];

                            console.log('Current approver element:', currentApprover);

                            if (currentApprover) {
                                const approverTypeSelect = currentApprover.querySelector('.approver-type');
                                const approverIdSelect = currentApprover.querySelector('.approver-id');
                                const backupCheckbox = currentApprover.querySelector('[name*="is_backup"]');

                                if (approverTypeSelect) {
                                    approverTypeSelect.value = approver.approver_type;
                                    updateApproverOptions(approverTypeSelect);

                                    // Set approver ID after options are loaded
                                    setTimeout(() => {
                                        if (approverIdSelect) {
                                            approverIdSelect.value = approver.approver_id;
                                            console.log(`Set approver ID: ${approver.approver_id}`);
                                        }
                                        if (backupCheckbox) {
                                            backupCheckbox.checked = approver.is_backup;
                                            console.log(`Set backup: ${approver.is_backup}`);
                                        }
                                    }, 200);
                                }
                            }
                        }
                    });
                }
            });

            // Update stage numbers after loading
            updateStageNumbers();
        }

        function loadStagesByDocumentType(documentType) {
            // Clear existing stages
            const container = document.getElementById('stages-container');
            container.innerHTML = '';

            if (documentTypeStages[documentType]) {
                // Show info about predefined stages
                const infoDiv = document.getElementById('stages-info');
                const infoText = document.getElementById('stages-info-text');
                infoText.textContent =
                    `Predefined stages for ${documentType} have been loaded. You can modify them as needed.`;
                infoDiv.style.display = 'block';

                documentTypeStages[documentType].forEach((stageConfig, index) => {
                    addStage();
                    const lastStage = document.querySelector('.stage-item:last-child');

                    // Set stage values
                    lastStage.querySelector('.stage-name').value = stageConfig.stage_name;
                    lastStage.querySelector('.stage-type').value = stageConfig.stage_type;
                    lastStage.querySelector('[name*="escalation_hours"]').value = stageConfig.escalation_hours;
                    lastStage.querySelector('[name*="is_mandatory"]').checked = stageConfig.is_mandatory;

                    // Ensure stage order is set correctly
                    const stageOrderInput = lastStage.querySelector('.stage-order-input');
                    if (stageOrderInput) {
                        stageOrderInput.value = stageConfig.stage_order;
                    }
                });

                updateStageNumbers();
            } else {
                // Hide info if no predefined stages
                document.getElementById('stages-info').style.display = 'none';
            }
        }

        function validateForm() {
            const stages = document.querySelectorAll('.stage-item');
            let isValid = true;

            stages.forEach((stage, index) => {
                // Check if stage order is set
                const stageOrderInput = stage.querySelector('.stage-order-input');
                if (!stageOrderInput || !stageOrderInput.value) {
                    console.error(`Stage ${index + 1} missing stage_order`);
                    isValid = false;
                }

                // Check if stage name is set
                const stageNameInput = stage.querySelector('.stage-name');
                if (!stageNameInput || !stageNameInput.value.trim()) {
                    console.error(`Stage ${index + 1} missing stage_name`);
                    isValid = false;
                }

                // Check approvers
                const approvers = stage.querySelectorAll('.approver-item');
                approvers.forEach((approver, approverIndex) => {
                    const approverTypeSelect = approver.querySelector('.approver-type');
                    const approverIdSelect = approver.querySelector('.approver-id');

                    if (!approverTypeSelect || !approverTypeSelect.value) {
                        console.error(
                            `Stage ${index + 1}, Approver ${approverIndex + 1} missing approver_type`);
                        isValid = false;
                    }

                    if (!approverIdSelect || !approverIdSelect.value) {
                        console.error(
                            `Stage ${index + 1}, Approver ${approverIndex + 1} missing approver_id`);
                        isValid = false;
                    }
                });
            });

            if (!isValid) {
                alert('Please fill in all required fields for stages and approvers.');
                return false;
            }

            return true;
        }

        // Initialize with existing stages
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing form...');
            setTimeout(() => {
                loadExistingStages();
            }, 100);

            // Show info about existing stages if any
            if (existingFlow.stages && existingFlow.stages.length > 0) {
                const infoDiv = document.getElementById('stages-info');
                const infoText = document.getElementById('stages-info-text');
                infoText.textContent =
                    `Existing stages for this flow (${existingFlow.stages.length} stages). You can modify them as needed.`;
                infoDiv.style.display = 'block';
            }

            // Add event listener for document type change
            document.getElementById('document_type').addEventListener('change', function() {
                const selectedType = this.value;
                if (selectedType) {
                    if (!existingFlow.stages || existingFlow.stages.length === 0) {
                        loadStagesByDocumentType(selectedType);
                    } else {
                        // Show info about existing stages
                        const infoDiv = document.getElementById('stages-info');
                        const infoText = document.getElementById('stages-info-text');
                        infoText.textContent =
                            `Existing stages for this flow will be preserved. You can modify them as needed.`;
                        infoDiv.style.display = 'block';
                    }
                } else {
                    document.getElementById('stages-info').style.display = 'none';
                }
            });
        });
    </script>
@endsection
