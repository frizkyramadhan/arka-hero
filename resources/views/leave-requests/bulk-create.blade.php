@extends('layouts.main')

@section('title', 'Create Periodic Leave Request')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create Periodic Leave Request</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave.bulk-requests.index') }}">Periodic Leave
                                Requests</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form id="bulkLeaveForm" method="POST" action="{{ route('leave.bulk-requests.store') }}">
                @csrf

                <!-- Filter Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-filter mr-2"></i>
                                    <strong>Filter Periodic Leave Employees</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="project_id">
                                                <i class="fas fa-building mr-1"></i>
                                                Project <span class="text-danger">*</span>
                                            </label>
                                            <select name="project_id" id="project_id"
                                                class="select2bs4 form-control @error('project_id') is-invalid @enderror"
                                                required>
                                                <option value="">Select Project</option>
                                                @foreach ($projects as $project)
                                                    <option value="{{ $project->id }}"
                                                        {{ $selectedProjectId == $project->id ? 'selected' : '' }}>
                                                        {{ $project->project_name }} ({{ $project->project_code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('project_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="department_id">
                                                <i class="fas fa-sitemap mr-1"></i>
                                                Department
                                            </label>
                                            <select name="department_id" id="department_id" class="select2bs4 form-control">
                                                <option value="">All Departments</option>
                                            </select>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Select specific department or leave empty for all
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="days_ahead">
                                                <i class="fas fa-calendar-day mr-1"></i>
                                                Look Ahead Days
                                            </label>
                                            <input type="number" class="form-control" id="days_ahead" value="14"
                                                min="1" max="60">
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Days ahead
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-primary btn-block" id="btn-search">
                                                <i class="fas fa-search mr-1"></i> Search Employees
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div id="loading-employees" class="row" style="display: none;">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                                <p class="mt-3 text-muted">Loading employee data...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employee List Section -->
                <div class="row">
                    <div class="col-12">
                        <div id="employees-section">
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-users mr-2"></i>
                                        <strong>Employee List</strong>
                                    </h3>
                                    <div class="card-tools">
                                        <span class="badge badge-info" id="selected-count">0 selected</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="select-all">
                                            <i class="fas fa-check-square mr-1"></i> Select All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="deselect-all">
                                            <i class="fas fa-times mr-1"></i> Deselect All
                                        </button>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="employees-table">
                                            <thead>
                                                <tr>
                                                    <th width="50">
                                                        <div class="icheck-primary">
                                                            <input type="checkbox" id="checkbox-all">
                                                            <label for="checkbox-all"></label>
                                                        </div>
                                                    </th>
                                                    <th width="80">NIK</th>
                                                    <th>Employee Name</th>
                                                    <th>Position</th>
                                                    <th>Department</th>
                                                    <th width="100">Start Date</th>
                                                    <th width="100">End Date</th>
                                                    <th width="150">Roster Note</th>
                                                    <th width="80">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="employees-tbody">
                                                <tr id="empty-row">
                                                    <td colspan="9" class="text-center text-muted py-4">
                                                        <i class="fas fa-search fa-2x mb-2"></i>
                                                        <p>Select a project and click "Search Employees" to see available
                                                            employees</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approval Preview & Notes & Submit Section -->
                <div class="row">
                    <!-- Approval Preview Section -->
                    <div class="col-md-8">
                        <div class="card card-info card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-clipboard-check mr-2"></i>
                                    <strong>Approval Preview</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div id="approval-preview-content">
                                    <div class="text-center py-3 text-muted">
                                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                                        <p>Select employees to see approval flow</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes & Submit Section -->
                    <div class="col-md-4">
                        <div class="card card-success card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-sticky-note mr-2"></i>
                                    <strong>Notes & Submit</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Note:</strong> Each employee will have an individual leave request with
                                    their own approval process.
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Hierarchical Approval Logic:</strong> Actual approval flow will be adjusted
                                    based on each employee's level:
                                    <ul class="mb-0 mt-2" style="font-size: 0.85rem;">
                                        <li><strong>Non Staff/Foreman (Level 1-2):</strong> First from SPV(3) or SPT(4) →
                                            Manager(5) <span class="badge badge-secondary">2 approvers</span></li>
                                        <li><strong>Supervisor (Level 3):</strong> Superintendent(4) → Manager(5) <span
                                                class="badge badge-secondary">2 approvers</span></li>
                                        <li><strong>Superintendent (Level 4):</strong> Superintendent(4) → Manager(5) <span
                                                class="badge badge-secondary">2 approvers</span></li>
                                        <li><strong>Manager (Level 5):</strong> Director(6) <span
                                                class="badge badge-secondary">1 approver</span></li>
                                        <li><strong>Director (Level 6):</strong> Follow configured approval stages</li>
                                    </ul>
                                </div>

                                <div class="form-group">
                                    <label for="bulk_notes">
                                        <i class="fas fa-sticky-note mr-1"></i>
                                        Batch Notes (Optional)
                                    </label>
                                    <textarea name="bulk_notes" id="bulk_notes" class="form-control" rows="3"
                                        placeholder="Add notes for this batch submission..."></textarea>
                                </div>

                                <!-- Hidden inputs for selected employees -->
                                <div id="hidden-inputs-container"></div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-success btn-block" id="btn-submit">
                                    <i class="fas fa-paper-plane mr-1"></i> Submit Leave Request (<span
                                        id="submit-count">0</span> Employees)
                                </button>
                                <a href="{{ route('leave.bulk-requests.index') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times mr-1"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('styles')
    <style>
        /* Table Styles */
        .badge-due {
            background-color: #28a745;
        }

        .badge-upcoming {
            background-color: #ffc107;
        }

        .row-due {
            background-color: #d4edda !important;
        }

        .table th {
            white-space: nowrap;
            font-size: 0.875rem;
            padding: 0.5rem 0.4rem;
        }

        .table td {
            font-size: 0.875rem;
            padding: 0.5rem 0.4rem;
            vertical-align: middle;
        }

        #employees-table {
            font-size: 0.875rem;
        }

        #employees-table .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        #employees-table td {
            white-space: nowrap;
        }

        #employees-table td:nth-child(7) {
            white-space: normal;
            max-width: 150px;
            word-wrap: break-word;
            font-size: 0.8rem;
        }

        #btn-submit:disabled {
            cursor: not-allowed;
        }

        /* Approval Preview Styles */
        #approval-preview-content {
            max-height: 500px;
            overflow-y: auto;
        }

        .department-group {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            overflow: hidden;
            width: 100%;
            margin-bottom: 1rem;
        }

        .department-header {
            font-size: 0.9rem;
        }

        .approval-flow-container {
            max-height: 150px;
            overflow-y: auto;
        }

        .approval-step-mini {
            background: white;
            border-radius: 0.25rem;
            padding: 0.5rem;
            border-left: 3px solid #007bff;
        }

        .step-number-mini {
            width: 28px;
            height: 28px;
            background: #007bff;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .approver-name-mini {
            font-weight: 600;
            font-size: 0.85rem;
            line-height: 1.2;
        }

        .approver-dept-mini {
            font-size: 0.75rem;
            line-height: 1.2;
        }

        @media (max-width: 768px) {
            .department-group {
                width: 100%;
                margin-right: 0;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        let selectedEmployees = [];
        let employeesData = [];

        $(document).ready(function() {
            // Initialize select2
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // Load departments when project changes
            $('#project_id').on('change', function() {
                const projectId = $(this).val();
                loadDepartments(projectId);
            });

            // Auto-load departments and employees if project is pre-selected
            @if ($selectedProjectId)
                loadDepartments({{ $selectedProjectId }});
                loadEmployees({{ $selectedProjectId }}, $('#days_ahead').val(), null, false, false);
            @endif

            // Search button handler
            $('#btn-search').on('click', function() {
                const projectId = $('#project_id').val();
                const daysAhead = $('#days_ahead').val();
                const departmentId = $('#department_id').val() || null;

                if (!projectId) {
                    Swal.fire('Warning', 'Please select a project first', 'warning');
                    return;
                }

                loadEmployees(projectId, daysAhead, departmentId, true, true);
            });

            // Selection handlers
            $('#select-all').on('click', function() {
                $('#employees-tbody input[type="checkbox"]').prop('checked', true);
                updateSelectedEmployees();
            });

            $('#deselect-all').on('click', function() {
                $('#employees-tbody input[type="checkbox"]').prop('checked', false);
                updateSelectedEmployees();
            });

            $('#checkbox-all').on('change', function() {
                $('#employees-tbody input[type="checkbox"]').prop('checked', $(this).is(':checked'));
                updateSelectedEmployees();
            });

            // Employee checkbox change
            $(document).on('change', '#employees-tbody input[type="checkbox"]', function() {
                updateSelectedEmployees();
            });

            // Form submission
            $('#bulkLeaveForm').on('submit', function(e) {
                e.preventDefault();

                if (selectedEmployees.length == 0) {
                    Swal.fire('Error', 'No employees selected', 'error');
                    return false;
                }

                $('#btn-submit').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin mr-1"></i> Submitting...');

                // Submit the form
                this.submit();
            });
        });

        function loadDepartments(projectId) {
            if (!projectId) {
                $('#department_id').empty().append('<option value="">All Departments</option>');
                $('#department_id').trigger('change');
                return;
            }

            $.ajax({
                url: '{{ route('leave.bulk-requests.ajax.departments') }}',
                method: 'GET',
                data: {
                    project_id: projectId
                },
                success: function(response) {
                    const select = $('#department_id');
                    select.empty().append('<option value="">All Departments</option>');

                    if (response.departments && response.departments.length > 0) {
                        response.departments.forEach(function(dept) {
                            select.append(
                                `<option value="${dept.id}">${dept.department_name}</option>`);
                        });
                    }

                    select.trigger('change');
                },
                error: function(xhr) {
                    console.error('Failed to load departments:', xhr);
                    $('#department_id').empty().append('<option value="">All Departments</option>');
                }
            });
        }

        function loadEmployees(projectId, daysAhead, departmentId = null, showNotification = true, showLoading = true) {
            if (showLoading) {
                $('#loading-employees').show();
            }

            const requestData = {
                project_id: projectId,
                days_ahead: daysAhead
            };

            if (departmentId) {
                requestData.department_id = departmentId;
            }

            $.ajax({
                url: '{{ route('leave.bulk-requests.ajax.employees-due') }}',
                method: 'GET',
                data: requestData,
                success: function(response) {
                    employeesData = response.employees;
                    renderEmployeesTable();
                    if (showLoading) {
                        $('#loading-employees').hide();
                    }

                    if (employeesData.length === 0) {
                        // Only show notification if it's a manual search (not auto-load)
                        if (showNotification) {
                            Swal.fire('Info',
                                'No employees with periodic leave schedule within the selected timeframe',
                                'info');
                        }
                    }
                },
                error: function(xhr) {
                    if (showLoading) {
                        $('#loading-employees').hide();
                    }
                    Swal.fire('Error', 'Failed to load employee data: ' + (xhr.responseJSON?.message ||
                        'Unknown error'), 'error');
                }
            });
        }

        function renderEmployeesTable() {
            const tbody = $('#employees-tbody');
            tbody.empty();

            if (employeesData.length == 0) {
                tbody.html(
                    '<tr id="empty-row"><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-search fa-2x mb-2"></i><p>Select a project and click "Search Employees" to see available employees</p></td></tr>'
                );
                return;
            }

            employeesData.forEach((emp, index) => {
                const isDue = emp.is_due;
                const daysUntilBadge = emp.days_until_off <= 0 ?
                    '<span class="badge badge-warning">Today</span>' :
                    emp.days_until_off <= 7 ?
                    `<span class="badge badge-due">${emp.days_until_off} days</span>` :
                    `<span class="badge badge-upcoming">${emp.days_until_off} days</span>`;

                const rowClass = isDue ? 'row-due' : '';

                const rosterNote = emp.roster_note ?
                    `<small class="text-muted" title="${emp.roster_note}">
                        <i class="fas fa-sticky-note mr-1"></i>${emp.roster_note.length > 25 ? emp.roster_note.substring(0, 25) + '...' : emp.roster_note}
                    </small>` :
                    '<small class="text-muted">-</small>';

                const html = `
                    <tr class="${rowClass}">
                        <td>
                            <div class="icheck-primary">
                                <input type="checkbox" id="emp-${index}"
                                    data-index="${index}"
                                    data-is-due="${isDue}">
                                <label for="emp-${index}"></label>
                            </div>
                        </td>
                        <td>${emp.employee_nik}</td>
                        <td>${emp.employee_name}</td>
                        <td>${emp.position_name}</td>
                        <td>${emp.department_name}</td>
                        <td>${formatDate(emp.off_start_date)}</td>
                        <td>${formatDate(emp.off_end_date)}</td>
                        <td>${rosterNote}</td>
                        <td>${daysUntilBadge}</td>
                    </tr>
                `;
                tbody.append(html);
            });

            // Update selected employees based on checked boxes
            updateSelectedEmployees();
        }

        function updateSelectedEmployees() {
            selectedEmployees = [];
            const container = $('#hidden-inputs-container');
            container.empty();

            $('#employees-tbody input[type="checkbox"]:checked').each(function() {
                const index = $(this).data('index');
                const emp = employeesData[index];
                selectedEmployees.push({
                    employee_id: emp.employee_id,
                    administration_id: emp.administration_id,
                    employee_nik: emp.employee_nik,
                    employee_name: emp.employee_name,
                    position_name: emp.position_name,
                    start_date: emp.off_start_date,
                    end_date: emp.off_end_date,
                    total_days: emp.off_days
                });
            });

            // Update badges
            $('#selected-count').text(`${selectedEmployees.length} selected`);
            $('#submit-count').text(selectedEmployees.length);

            // Generate hidden inputs
            selectedEmployees.forEach((emp, index) => {
                container.append(`
                    <input type="hidden" name="selected_employees[${index}][employee_id]" value="${emp.employee_id}">
                    <input type="hidden" name="selected_employees[${index}][administration_id]" value="${emp.administration_id}">
                    <input type="hidden" name="selected_employees[${index}][start_date]" value="${emp.start_date}">
                    <input type="hidden" name="selected_employees[${index}][end_date]" value="${emp.end_date}">
                    <input type="hidden" name="selected_employees[${index}][total_days]" value="${emp.total_days}">
                `);
            });

            // Enable/disable submit button
            if (selectedEmployees.length > 0) {
                $('#btn-submit').prop('disabled', false);
            } else {
                $('#btn-submit').prop('disabled', true);
            }

            // Update approval preview
            const employeeIds = selectedEmployees.map(emp => emp.employee_id);
            loadApprovalPreview(employeeIds);
        }

        function loadApprovalPreview(employeeIds) {
            const projectId = $('#project_id').val();

            if (!projectId || employeeIds.length === 0) {
                $('#approval-preview-content').html(`
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p>Select employees to see approval flow</p>
                    </div>
                `);
                return;
            }

            // Show loading
            $('#approval-preview-content').html(`
                <div class="text-center py-3">
                    <i class="fas fa-spinner fa-spin fa-2x text-info mb-2"></i>
                    <p class="text-muted">Loading approval flow...</p>
                </div>
            `);

            $.ajax({
                url: '{{ route('leave.bulk-requests.ajax.approval-preview') }}',
                method: 'GET',
                data: {
                    employee_ids: employeeIds,
                    project_id: projectId
                },
                success: function(response) {
                    if (response.success && response.approval_groups.length > 0) {
                        let html = '';

                        // Summary
                        html += `
                            <div class="alert alert-info mb-3 p-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-users mr-1"></i>
                                        <strong>${response.total_employees}</strong> Employee${response.total_employees > 1 ? 's' : ''}
                                    </div>
                                    <div>
                                        <i class="fas fa-sitemap mr-1"></i>
                                        <strong>${response.total_departments}</strong> Department${response.total_departments > 1 ? 's' : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                        // Each department group (2 columns)
                        response.approval_groups.forEach(function(group) {
                            html += `
                                <div class="department-group">
                                    <div class="department-header bg-primary text-white p-2 rounded-top">
                                        <i class="fas fa-sitemap mr-1"></i>
                                        <strong>${group.department_name}</strong>
                                        <span class="badge badge-light float-right">${group.employee_count}</span>
                                    </div>
                            `;

                            // Show level summary if available
                            if (group.level_summary && group.level_summary.length > 0) {
                                html += `
                                    <div class="bg-light px-2 py-1" style="font-size: 0.8rem; border-bottom: 1px solid #dee2e6;">
                                        <i class="fas fa-layer-group mr-1 text-muted"></i>
                                        <span class="text-muted">Levels: ${group.level_summary.join(', ')}</span>
                                    </div>
                                `;
                            }

                            html += `
                                    <div class="approval-flow-container p-2 bg-light rounded-bottom">
                            `;

                            if (group.approvers.length > 0) {
                                group.approvers.forEach(function(approver) {
                                    const hasNote = approver.note && approver.note.length > 0;
                                    const levelBadge = approver.level ?
                                        `<span class="badge badge-info badge-sm ml-1">${approver.level}</span>` :
                                        '';

                                    html += `
                                        <div class="approval-step-mini mb-2">
                                            <div class="d-flex align-items-center">
                                                <div class="step-number-mini mr-2">${approver.order}</div>
                                                <div class="flex-grow-1">
                                                    <div class="approver-name-mini">
                                                        ${approver.name}
                                                        ${levelBadge}
                                                    </div>
                                                    ${hasNote ? `<div class="text-muted small"><i>${approver.note}</i></div>` : ''}
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                });
                            } else {
                                html += `
                                    <div class="text-center text-muted py-2">
                                        <small><i class="fas fa-exclamation-triangle"></i> No approval configured</small>
                                    </div>
                                `;
                            }

                            html += `
                                    </div>
                                </div>
                            `;
                        });

                        $('#approval-preview-content').html(html);
                    } else {
                        $('#approval-preview-content').html(`
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                <p>No approval flow configured</p>
                            </div>
                        `);
                    }
                },
                error: function(xhr) {
                    $('#approval-preview-content').html(`
                        <div class="text-center text-danger py-3">
                            <i class="fas fa-times-circle fa-2x mb-2"></i>
                            <p>Failed to load approval flow</p>
                            <small>${xhr.responseJSON?.message || 'Unknown error'}</small>
                        </div>
                    `);
                }
            });
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }
    </script>
@endsection
