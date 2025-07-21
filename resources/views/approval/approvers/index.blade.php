@extends('layouts.main')

@section('title', 'Approver Assignment - ' . $stage->stage_name)

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Approver Assignment</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('approval.flows.index') }}">Approval Flows</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('approval.flows.show', $stage->approvalFlow) }}">{{ $stage->approvalFlow->name }}</a>
                        </li>
                        <li class="breadcrumb-item active">Stage: {{ $stage->stage_name }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Stage Information -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-layer-group"></i>
                            Stage Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="150"><strong>Stage Name:</strong></td>
                                        <td>{{ $stage->stage_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Stage Order:</strong></td>
                                        <td>{{ $stage->stage_order }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Stage Type:</strong></td>
                                        <td>
                                            <span
                                                class="badge badge-{{ $stage->stage_type === 'sequential' ? 'primary' : 'info' }}">
                                                {{ ucfirst($stage->stage_type) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="150"><strong>Mandatory:</strong></td>
                                        <td>
                                            <span class="badge badge-{{ $stage->is_mandatory ? 'success' : 'warning' }}">
                                                {{ $stage->is_mandatory ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Escalation:</strong></td>
                                        <td>{{ $stage->escalation_hours ?? 'Not set' }} hours</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Approvers:</strong></td>
                                        <td>
                                            <span class="badge badge-info" id="total-approvers">
                                                {{ $stage->approvers->count() }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approver Assignment -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-check"></i>
                            Approver Assignment
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                data-target="#assignApproverModal">
                                <i class="fas fa-plus"></i> Assign Approver
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Tabs for different approver types -->
                        <ul class="nav nav-tabs" id="approverTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="primary-tab" data-toggle="tab" href="#primary"
                                    role="tab">
                                    Primary Approvers
                                    <span class="badge badge-primary ml-1" id="primary-count">0</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="backup-tab" data-toggle="tab" href="#backup" role="tab">
                                    Backup Approvers
                                    <span class="badge badge-warning ml-1" id="backup-count">0</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mt-3" id="approverTabContent">
                            <!-- Primary Approvers -->
                            <div class="tab-pane fade show active" id="primary" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="primary-approvers-table">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Approver</th>
                                                <th>Details</th>
                                                <th>Conditions</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Primary approvers will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Backup Approvers -->
                            <div class="tab-pane fade" id="backup" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="backup-approvers-table">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Approver</th>
                                                <th>Details</th>
                                                <th>Conditions</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Backup approvers will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Approver Modal -->
    <div class="modal fade" id="assignApproverModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus"></i>
                        Assign Approver
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="assignApproverForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="approver_type">Approver Type <span class="text-danger">*</span></label>
                                    <select class="form-control" id="approver_type" name="approver_type" required>
                                        <option value="">Select Type</option>
                                        <option value="user">Individual User</option>
                                        <option value="role">Role-based</option>
                                        <option value="department">Department-based</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="approver_id">Approver <span class="text-danger">*</span></label>
                                    <select class="form-control" id="approver_id" name="approver_id" required disabled>
                                        <option value="">Select Type First</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_backup"
                                            name="is_backup">
                                        <label class="custom-control-label" for="is_backup">
                                            Backup Approver
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="approval_condition">Approval Conditions (Optional)</label>
                            <textarea class="form-control" id="approval_condition" name="approval_condition" rows="3"
                                placeholder="Enter JSON conditions for when this approver is required..."></textarea>
                            <small class="form-text text-muted">
                                Example: {"amount_limit": 1000000, "department": "IT"}
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Assign Approver
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Approver Modal -->
    <div class="modal fade" id="editApproverModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i>
                        Edit Approver Assignment
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="editApproverForm">
                    <div class="modal-body">
                        <input type="hidden" id="edit_approver_id" name="approver_id">

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="edit_is_backup"
                                    name="is_backup">
                                <label class="custom-control-label" for="edit_is_backup">
                                    Backup Approver
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="edit_approval_condition">Approval Conditions</label>
                            <textarea class="form-control" id="edit_approval_condition" name="approval_condition" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const stageId = {{ $stage->id }};
            let currentApprovers = [];

            // Load approvers on page load
            loadApprovers();

            // Handle approver type change
            $('#approver_type').change(function() {
                const type = $(this).val();
                const approverSelect = $('#approver_id');

                approverSelect.prop('disabled', !type);
                approverSelect.empty().append('<option value="">Loading...</option>');

                if (!type) {
                    approverSelect.empty().append('<option value="">Select Type First</option>');
                    return;
                }

                // Load approvers based on type
                if (type === 'user') {
                    loadUsers();
                } else if (type === 'role') {
                    loadRoles();
                } else if (type === 'department') {
                    loadDepartments();
                }
            });

            // Handle form submission
            $('#assignApproverForm').submit(function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: `/approval/stages/${stageId}/approvers/assign`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#assignApproverModal').modal('hide');
                            $('#assignApproverForm')[0].reset();
                            loadApprovers();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        if (response && response.errors) {
                            Object.keys(response.errors).forEach(key => {
                                toastr.error(response.errors[key][0]);
                            });
                        } else {
                            toastr.error('Failed to assign approver');
                        }
                    }
                });
            });

            // Handle edit form submission
            $('#editApproverForm').submit(function(e) {
                e.preventDefault();

                const approverId = $('#edit_approver_id').val();
                const formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: `/approval/approvers/${approverId}/update`,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#editApproverModal').modal('hide');
                            loadApprovers();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        if (response && response.errors) {
                            Object.keys(response.errors).forEach(key => {
                                toastr.error(response.errors[key][0]);
                            });
                        } else {
                            toastr.error('Failed to update approver');
                        }
                    }
                });
            });

            // Load approvers
            function loadApprovers() {
                $.ajax({
                    url: `/approval/stages/${stageId}/approvers`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            currentApprovers = response.approvers;
                            renderApprovers();
                            updateCounts();
                        }
                    },
                    error: function() {
                        toastr.error('Failed to load approvers');
                    }
                });
            }

            // Render approvers in tables
            function renderApprovers() {
                const primaryApprovers = currentApprovers.filter(a => !a.is_backup);
                const backupApprovers = currentApprovers.filter(a => a.is_backup);

                renderApproverTable('primary-approvers-table', primaryApprovers);
                renderApproverTable('backup-approvers-table', backupApprovers);
            }

            function renderApproverTable(tableId, approvers) {
                const tbody = $(`#${tableId} tbody`);
                tbody.empty();

                if (approvers.length === 0) {
                    tbody.append(
                        '<tr><td colspan="5" class="text-center text-muted">No approvers assigned</td></tr>');
                    return;
                }

                approvers.forEach(approver => {
                    const row = createApproverRow(approver);
                    tbody.append(row);
                });
            }

            function createApproverRow(approver) {
                const type = approver.approver_type;
                const approverName = getApproverName(approver);
                const details = getApproverDetails(approver);
                const conditions = approver.approval_condition ? JSON.stringify(approver.approval_condition, null,
                    2) : 'None';

                return `
            <tr data-approver-id="${approver.id}">
                <td>
                    <span class="badge badge-${getTypeBadgeClass(type)}">
                        ${type.charAt(0).toUpperCase() + type.slice(1)}
                    </span>
                </td>
                <td>${approverName}</td>
                <td>${details}</td>
                <td>
                    <small class="text-muted">${conditions}</small>
                </td>
                <td>
                    <button class="btn btn-sm btn-info edit-approver" data-approver='${JSON.stringify(approver)}'>
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger remove-approver" data-approver-id="${approver.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
            }

            function getApproverName(approver) {
                if (approver.user) return approver.user.name;
                if (approver.role) return approver.role.name;
                if (approver.department) return approver.department.name;
                return 'Unknown';
            }

            function getApproverDetails(approver) {
                if (approver.user) return approver.user.email;
                if (approver.role) return `${approver.role.users_count || 0} users`;
                if (approver.department) return `${approver.department.employees_count || 0} employees`;
                return '';
            }

            function getTypeBadgeClass(type) {
                switch (type) {
                    case 'user':
                        return 'primary';
                    case 'role':
                        return 'success';
                    case 'department':
                        return 'info';
                    default:
                        return 'secondary';
                }
            }

            function updateCounts() {
                const primaryCount = currentApprovers.filter(a => !a.is_backup).length;
                const backupCount = currentApprovers.filter(a => a.is_backup).length;

                $('#primary-count').text(primaryCount);
                $('#backup-count').text(backupCount);
                $('#total-approvers').text(currentApprovers.length);
            }

            // Load users for selection
            function loadUsers() {
                $.ajax({
                    url: '/approval/approvers/search-users',
                    type: 'GET',
                    data: {
                        q: ''
                    },
                    success: function(response) {
                        if (response.success) {
                            const select = $('#approver_id');
                            select.empty().append('<option value="">Select User</option>');

                            response.users.forEach(user => {
                                select.append(
                                    `<option value="${user.id}">${user.name} (${user.email})</option>`
                                );
                            });
                        }
                    }
                });
            }

            // Load roles for selection
            function loadRoles() {
                const roles = @json($roles);
                const select = $('#approver_id');
                select.empty().append('<option value="">Select Role</option>');

                roles.forEach(role => {
                    select.append(`<option value="${role.id}">${role.name}</option>`);
                });
            }

            // Load departments for selection
            function loadDepartments() {
                const departments = @json($departments);
                const select = $('#approver_id');
                select.empty().append('<option value="">Select Department</option>');

                departments.forEach(dept => {
                    select.append(`<option value="${dept.id}">${dept.name}</option>`);
                });
            }

            // Handle edit approver
            $(document).on('click', '.edit-approver', function() {
                const approver = $(this).data('approver');

                $('#edit_approver_id').val(approver.id);
                $('#edit_is_backup').prop('checked', approver.is_backup);
                $('#edit_approval_condition').val(approver.approval_condition);

                $('#editApproverModal').modal('show');
            });

            // Handle remove approver
            $(document).on('click', '.remove-approver', function() {
                const approverId = $(this).data('approver-id');

                if (confirm('Are you sure you want to remove this approver?')) {
                    $.ajax({
                        url: `/approval/approvers/${approverId}/remove`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                loadApprovers();
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function() {
                            toastr.error('Failed to remove approver');
                        }
                    });
                }
            });
        });
    </script>
@endsection

@section('styles')
    <style>
        .approver-type-badge {
            font-size: 0.8em;
        }

        .approver-details {
            font-size: 0.9em;
            color: #6c757d;
        }

        .conditions-text {
            font-family: monospace;
            font-size: 0.8em;
            background-color: #f8f9fa;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
@endsection
