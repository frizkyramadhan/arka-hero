@extends('layouts.main')

@section('title', 'Create Approval Stage')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create Approval Stage</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('approval.flows.index') }}">Approval Flows</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('approval.flows.show', $flow) }}">{{ $flow->name }}</a></li>
                        <li class="breadcrumb-item active">Create Stage</li>
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
                            <h3 class="card-title">Create New Stage for: {{ $flow->name }}</h3>
                            <div class="card-tools">
                                <a href="{{ route('approval.stages.index', $flow) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Stages
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="stageForm">
                                @csrf

                                <!-- Stage Details -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="stage_name">Stage Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="stage_name" name="stage_name"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="stage_order">Stage Order <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="stage_order" name="stage_order"
                                                value="{{ $nextOrder }}" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="stage_type">Stage Type <span class="text-danger">*</span></label>
                                            <select class="form-control" id="stage_type" name="stage_type" required>
                                                <option value="sequential">Sequential</option>
                                                <option value="parallel">Parallel</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="escalation_hours">Escalation (hours)</label>
                                            <input type="number" class="form-control" id="escalation_hours"
                                                name="escalation_hours" value="72" min="1">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="custom-control custom-switch mt-4">
                                                <input type="checkbox" class="custom-control-input" id="is_mandatory"
                                                    name="is_mandatory" value="1" checked>
                                                <label class="custom-control-label" for="is_mandatory">Mandatory
                                                    Stage</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <!-- Approvers Section -->
                                <div class="form-group">
                                    <label>Approvers</label>
                                    <div id="approvers-container">
                                        <!-- Approvers will be added here dynamically -->
                                    </div>
                                    <button type="button" class="btn btn-info" onclick="addApprover()">
                                        <i class="fas fa-plus"></i> Add Approver
                                    </button>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Create Stage
                                    </button>
                                    <a href="{{ route('approval.stages.index', $flow) }}"
                                        class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Approver Template -->
    <template id="approver-template">
        <div class="approver-item card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Approver <span class="approver-number"></span></h6>
                <button type="button" class="btn btn-sm btn-danger float-right" onclick="removeApprover(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Approver Type <span class="text-danger">*</span></label>
                            <select class="form-control approver-type" onchange="updateApproverOptions(this)" required>
                                <option value="">Select Type</option>
                                <option value="user">User</option>
                                <option value="role">Role</option>
                                <option value="department">Department</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Approver <span class="text-danger">*</span></label>
                            <select class="form-control approver-id" name="approvers[APPROVER_INDEX][approver_id]"
                                required>
                                <option value="">Select Approver</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="custom-control custom-switch mt-4">
                                <input type="checkbox" class="custom-control-input"
                                    name="approvers[APPROVER_INDEX][is_backup]" value="1">
                                <label class="custom-control-label">Backup Approver</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@section('scripts')
    <script>
        let approverIndex = 0;

        // Available data for approvers
        const users = @json($users);
        const roles = @json($roles);
        const departments = @json($departments);

        function addApprover() {
            const container = document.getElementById('approvers-container');
            const template = document.getElementById('approver-template');
            const approverItem = template.content.cloneNode(true);

            // Update approver index
            approverItem.querySelector('.approver-item').dataset.approverIndex = approverIndex;
            approverItem.querySelector('.approver-number').textContent = approverIndex + 1;

            // Update form field names
            approverItem.querySelectorAll('[name*="APPROVER_INDEX"]').forEach(input => {
                input.name = input.name.replace('APPROVER_INDEX', approverIndex);
            });

            container.appendChild(approverItem);
            approverIndex++;
        }

        function removeApprover(button) {
            if (confirm('Are you sure you want to remove this approver?')) {
                button.closest('.approver-item').remove();
                updateApproverNumbers();
            }
        }

        function updateApproverNumbers() {
            const approvers = document.querySelectorAll('.approver-item');
            approvers.forEach((approver, index) => {
                approver.querySelector('.approver-number').textContent = index + 1;
                approver.dataset.approverIndex = index;

                // Update form field names
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

            approverIdSelect.innerHTML = '<option value="">Select Approver</option>';

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

        // Form submission
        document.getElementById('stageForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            // Convert approvers to array format
            const approvers = [];
            const approverElements = document.querySelectorAll('.approver-item');

            approverElements.forEach((element, index) => {
                const type = element.querySelector('.approver-type').value;
                const id = element.querySelector('.approver-id').value;
                const isBackup = element.querySelector('[name*="is_backup"]').checked;

                if (type && id) {
                    approvers.push({
                        approver_type: type,
                        approver_id: parseInt(id),
                        is_backup: isBackup
                    });
                }
            });

            data.approvers = approvers;

            // Submit via AJAX
            fetch('{{ route('approval.stages.store', $flow) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', 'Stage created successfully!');
                        setTimeout(() => {
                            window.location.href = '{{ route('approval.stages.index', $flow) }}';
                        }, 1500);
                    } else {
                        showAlert('error', data.message || 'Failed to create stage');
                        if (data.errors) {
                            displayValidationErrors(data.errors);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Failed to create stage');
                });
        });

        function displayValidationErrors(errors) {
            // Clear previous errors
            document.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
            document.querySelectorAll('.invalid-feedback').forEach(el => {
                el.remove();
            });

            // Display new errors
            Object.keys(errors).forEach(field => {
                const element = document.querySelector(`[name="${field}"]`);
                if (element) {
                    element.classList.add('is-invalid');
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    feedback.textContent = errors[field][0];
                    element.parentNode.appendChild(feedback);
                }
            });
        }

        function showAlert(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';

            const alertDiv = document.createElement('div');
            alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="${icon}"></i> ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            `;

            const container = document.querySelector('.card-body');
            container.insertBefore(alertDiv, container.firstChild);

            // Auto dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Initialize with one approver
        document.addEventListener('DOMContentLoaded', function() {
            addApprover();
        });
    </script>
@endsection
