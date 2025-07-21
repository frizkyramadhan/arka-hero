@extends('layouts.main')

@section('title', 'Flow Designer - ' . $flow->name)

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Flow Designer</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('approval.flows.index') }}">Approval Flows</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('approval.flows.show', $flow) }}">{{ $flow->name }}</a></li>
                        <li class="breadcrumb-item active">Designer</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Flow Information -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-project-diagram"></i>
                            Flow Information
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-success btn-sm" id="saveFlow">
                                <i class="fas fa-save"></i> Save Flow
                            </button>
                            <button type="button" class="btn btn-info btn-sm" id="testFlow">
                                <i class="fas fa-play"></i> Test Flow
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="flow_name">Flow Name</label>
                                    <input type="text" class="form-control" id="flow_name" value="{{ $flow->name }}"
                                        placeholder="Enter flow name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="document_type">Document Type</label>
                                    <input type="text" class="form-control" id="document_type"
                                        value="{{ $flow->document_type }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="flow_description">Description</label>
                            <textarea class="form-control" id="flow_description" rows="2" placeholder="Enter flow description">{{ $flow->description }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flow Designer Canvas -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-palette"></i>
                            Visual Flow Designer
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary btn-sm" id="addStage">
                                <i class="fas fa-plus"></i> Add Stage
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" id="clearCanvas">
                                <i class="fas fa-trash"></i> Clear
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="flow-canvas-container">
                            <div id="flowCanvas" class="flow-canvas">
                                <!-- Flow stages will be rendered here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stage Properties Panel -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-cog"></i>
                            Stage Properties
                        </h3>
                    </div>
                    <div class="card-body">
                        <div id="stageProperties" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="stage_name">Stage Name</label>
                                        <input type="text" class="form-control" id="stage_name"
                                            placeholder="Enter stage name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="stage_type">Stage Type</label>
                                        <select class="form-control" id="stage_type">
                                            <option value="sequential">Sequential</option>
                                            <option value="parallel">Parallel</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="escalation_hours">Escalation Hours</label>
                                        <input type="number" class="form-control" id="escalation_hours" value="72"
                                            min="1">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="is_mandatory"
                                                checked>
                                            <label class="custom-control-label" for="is_mandatory">
                                                Mandatory Stage
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Approvers</label>
                                <div id="approversList">
                                    <!-- Approvers will be listed here -->
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addApprover">
                                    <i class="fas fa-plus"></i> Add Approver
                                </button>
                            </div>
                        </div>
                        <div id="noStageSelected" class="text-center text-muted">
                            <i class="fas fa-mouse-pointer fa-2x mb-2"></i>
                            <p>Select a stage to edit its properties</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Stage Modal -->
    <div class="modal fade" id="addStageModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i>
                        Add New Stage
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="addStageForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="new_stage_name">Stage Name</label>
                            <input type="text" class="form-control" id="new_stage_name" required
                                placeholder="Enter stage name">
                        </div>
                        <div class="form-group">
                            <label for="new_stage_type">Stage Type</label>
                            <select class="form-control" id="new_stage_type" required>
                                <option value="sequential">Sequential</option>
                                <option value="parallel">Parallel</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="new_stage_position">Position</label>
                            <select class="form-control" id="new_stage_position" required>
                                <option value="start">At Start</option>
                                <option value="end">At End</option>
                                <option value="after">After Stage</option>
                            </select>
                        </div>
                        <div class="form-group" id="afterStageGroup" style="display: none;">
                            <label for="after_stage">After Which Stage</label>
                            <select class="form-control" id="after_stage">
                                <!-- Stages will be populated here -->
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Stage
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Approver Modal -->
    <div class="modal fade" id="addApproverModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus"></i>
                        Add Approver
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="addApproverForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="approver_type">Approver Type</label>
                            <select class="form-control" id="approver_type" required>
                                <option value="">Select Type</option>
                                <option value="user">Individual User</option>
                                <option value="role">Role-based</option>
                                <option value="department">Department-based</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="approver_id">Approver</label>
                            <select class="form-control" id="approver_id" required disabled>
                                <option value="">Select Type First</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_backup">
                                <label class="custom-control-label" for="is_backup">
                                    Backup Approver
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Approver
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Test Flow Modal -->
    <div class="modal fade" id="testFlowModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-play"></i>
                        Test Approval Flow
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Test Parameters</h6>
                            <div class="form-group">
                                <label>Document Type</label>
                                <input type="text" class="form-control" id="test_document_type"
                                    value="{{ $flow->document_type }}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Document ID</label>
                                <input type="text" class="form-control" id="test_document_id" value="TEST-001">
                            </div>
                            <div class="form-group">
                                <label>Submitted By</label>
                                <select class="form-control" id="test_submitted_by">
                                    <!-- Users will be populated here -->
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary" id="runTest">
                                <i class="fas fa-play"></i> Run Test
                            </button>
                        </div>
                        <div class="col-md-6">
                            <h6>Test Results</h6>
                            <div id="testResults">
                                <div class="text-center text-muted">
                                    <i class="fas fa-play-circle fa-2x mb-2"></i>
                                    <p>Click "Run Test" to see results</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        $(document).ready(function() {
            const flowId = {{ $flow->id }};
            let currentStages = [];
            let selectedStage = null;
            let stageCounter = 0;

            // Initialize flow designer
            initializeFlowDesigner();

            // Load flow data
            loadFlowData();

            // Handle save flow
            $('#saveFlow').click(function() {
                saveFlow();
            });

            // Handle test flow
            $('#testFlow').click(function() {
                $('#testFlowModal').modal('show');
                loadTestUsers();
            });

            // Handle add stage
            $('#addStage').click(function() {
                $('#addStageModal').modal('show');
                populateStagePositionOptions();
            });

            // Handle add stage form submission
            $('#addStageForm').submit(function(e) {
                e.preventDefault();
                addNewStage();
            });

            // Handle stage type change in add modal
            $('#new_stage_type').change(function() {
                updateStagePositionOptions();
            });

            // Handle position change in add modal
            $('#new_stage_position').change(function() {
                const position = $(this).val();
                if (position === 'after') {
                    $('#afterStageGroup').show();
                } else {
                    $('#afterStageGroup').hide();
                }
            });

            // Handle add approver
            $('#addApprover').click(function() {
                if (selectedStage) {
                    $('#addApproverModal').modal('show');
                } else {
                    toastr.warning('Please select a stage first');
                }
            });

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

            // Handle add approver form submission
            $('#addApproverForm').submit(function(e) {
                e.preventDefault();
                addApproverToStage();
            });

            // Handle run test
            $('#runTest').click(function() {
                runFlowTest();
            });

            // Initialize flow designer
            function initializeFlowDesigner() {
                // Initialize Sortable for drag & drop
                const canvas = document.getElementById('flowCanvas');
                if (canvas) {
                    new Sortable(canvas, {
                        group: 'stages',
                        animation: 150,
                        onEnd: function(evt) {
                            updateStageOrder();
                        }
                    });
                }
            }

            // Load flow data
            function loadFlowData() {
                $.ajax({
                    url: `/approval/designer/${flowId}/data`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            currentStages = response.flow.stages;
                            renderFlowStages();
                        }
                    },
                    error: function() {
                        toastr.error('Failed to load flow data');
                    }
                });
            }

            // Render flow stages
            function renderFlowStages() {
                const canvas = $('#flowCanvas');
                canvas.empty();

                if (currentStages.length === 0) {
                    canvas.append(`
                <div class="empty-canvas text-center text-muted">
                    <i class="fas fa-plus-circle fa-3x mb-3"></i>
                    <h5>No Stages Yet</h5>
                    <p>Click "Add Stage" to start building your approval flow</p>
                </div>
            `);
                    return;
                }

                currentStages.forEach((stage, index) => {
                    const stageElement = createStageElement(stage, index);
                    canvas.append(stageElement);
                });
            }

            // Create stage element
            function createStageElement(stage, index) {
                const stageId = stage.id || `temp_${++stageCounter}`;
                const stageType = stage.stage_type || 'sequential';
                const approversCount = stage.approvers ? stage.approvers.length : 0;

                return `
            <div class="stage-item" data-stage-id="${stageId}" data-stage-index="${index}">
                <div class="stage-header">
                    <span class="stage-number">${index + 1}</span>
                    <span class="stage-name">${stage.stage_name}</span>
                    <span class="stage-type-badge badge badge-${stageType === 'sequential' ? 'primary' : 'info'}">
                        ${stageType.charAt(0).toUpperCase() + stageType.slice(1)}
                    </span>
                </div>
                <div class="stage-body">
                    <div class="stage-info">
                        <small class="text-muted">
                            <i class="fas fa-users"></i> ${approversCount} approvers
                        </small>
                        ${stage.is_mandatory ? '<span class="badge badge-success badge-sm">Mandatory</span>' : ''}
                    </div>
                    <div class="stage-actions">
                        <button class="btn btn-sm btn-outline-primary edit-stage" title="Edit Stage">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-stage" title="Delete Stage">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                ${index < currentStages.length - 1 ? '<div class="stage-connector"><i class="fas fa-arrow-down"></i></div>' : ''}
            </div>
        `;
            }

            // Handle stage selection
            $(document).on('click', '.stage-item', function() {
                const stageId = $(this).data('stage-id');
                const stageIndex = $(this).data('stage-index');

                // Remove previous selection
                $('.stage-item').removeClass('selected');
                $(this).addClass('selected');

                // Load stage properties
                selectedStage = currentStages[stageIndex];
                loadStageProperties(selectedStage);
            });

            // Load stage properties
            function loadStageProperties(stage) {
                $('#stage_name').val(stage.stage_name);
                $('#stage_type').val(stage.stage_type);
                $('#escalation_hours').val(stage.escalation_hours || 72);
                $('#is_mandatory').prop('checked', stage.is_mandatory !== false);

                // Load approvers
                loadStageApprovers(stage);

                // Show properties panel
                $('#stageProperties').show();
                $('#noStageSelected').hide();
            }

            // Load stage approvers
            function loadStageApprovers(stage) {
                const approversList = $('#approversList');
                approversList.empty();

                if (stage.approvers && stage.approvers.length > 0) {
                    stage.approvers.forEach(approver => {
                        const approverElement = createApproverElement(approver);
                        approversList.append(approverElement);
                    });
                } else {
                    approversList.append('<p class="text-muted">No approvers assigned</p>');
                }
            }

            // Create approver element
            function createApproverElement(approver) {
                const typeClass = approver.approver_type === 'user' ? 'primary' :
                    approver.approver_type === 'role' ? 'success' : 'info';

                return `
            <div class="approver-item d-flex justify-content-between align-items-center p-2 border rounded mb-2">
                <div>
                    <span class="badge badge-${typeClass} mr-2">${approver.approver_type}</span>
                    <span>${approver.approver_name || 'Unknown'}</span>
                    ${approver.is_backup ? '<span class="badge badge-warning ml-1">Backup</span>' : ''}
                </div>
                <button class="btn btn-sm btn-outline-danger remove-approver" data-approver-id="${approver.id}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
            }

            // Save flow
            function saveFlow() {
                const flowData = {
                    name: $('#flow_name').val(),
                    description: $('#flow_description').val(),
                    stages: currentStages.map((stage, index) => ({
                        id: stage.id,
                        stage_name: stage.stage_name,
                        stage_order: index + 1,
                        stage_type: stage.stage_type,
                        is_mandatory: stage.is_mandatory,
                        escalation_hours: stage.escalation_hours,
                        approvers: stage.approvers || []
                    }))
                };

                $.ajax({
                    url: `/approval/designer/${flowId}/update`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ...flowData
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            loadFlowData(); // Reload to get updated IDs
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
                            toastr.error('Failed to save flow');
                        }
                    }
                });
            }

            // Add new stage
            function addNewStage() {
                const stageName = $('#new_stage_name').val();
                const stageType = $('#new_stage_type').val();
                const position = $('#new_stage_position').val();
                const afterStage = $('#after_stage').val();

                if (!stageName) {
                    toastr.error('Please enter a stage name');
                    return;
                }

                const newStage = {
                    stage_name: stageName,
                    stage_type: stageType,
                    stage_order: 0,
                    is_mandatory: true,
                    escalation_hours: 72,
                    approvers: []
                };

                // Insert stage at appropriate position
                if (position === 'start') {
                    currentStages.unshift(newStage);
                } else if (position === 'end') {
                    currentStages.push(newStage);
                } else if (position === 'after' && afterStage) {
                    const afterIndex = currentStages.findIndex(s => s.id == afterStage);
                    if (afterIndex !== -1) {
                        currentStages.splice(afterIndex + 1, 0, newStage);
                    } else {
                        currentStages.push(newStage);
                    }
                } else {
                    currentStages.push(newStage);
                }

                // Update stage orders
                currentStages.forEach((stage, index) => {
                    stage.stage_order = index + 1;
                });

                renderFlowStages();
                $('#addStageModal').modal('hide');
                $('#addStageForm')[0].reset();

                toastr.success('Stage added successfully');
            }

            // Update stage order after drag & drop
            function updateStageOrder() {
                const stageElements = $('.stage-item');
                const newOrder = [];

                stageElements.each(function(index) {
                    const stageId = $(this).data('stage-id');
                    const stage = currentStages.find(s => s.id == stageId || s.temp_id == stageId);
                    if (stage) {
                        stage.stage_order = index + 1;
                        newOrder.push(stage);
                    }
                });

                currentStages = newOrder;
            }

            // Load users for approver selection
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

            // Load roles for approver selection
            function loadRoles() {
                const roles = @json(\Spatie\Permission\Models\Role::all());
                const select = $('#approver_id');
                select.empty().append('<option value="">Select Role</option>');

                roles.forEach(role => {
                    select.append(`<option value="${role.id}">${role.name}</option>`);
                });
            }

            // Load departments for approver selection
            function loadDepartments() {
                const departments = @json(\App\Models\Department::all());
                const select = $('#approver_id');
                select.empty().append('<option value="">Select Department</option>');

                departments.forEach(dept => {
                    select.append(`<option value="${dept.id}">${dept.name}</option>`);
                });
            }

            // Add approver to stage
            function addApproverToStage() {
                if (!selectedStage) {
                    toastr.error('No stage selected');
                    return;
                }

                const approverType = $('#approver_type').val();
                const approverId = $('#approver_id').val();
                const isBackup = $('#is_backup').is(':checked');

                if (!approverType || !approverId) {
                    toastr.error('Please select approver type and approver');
                    return;
                }

                const newApprover = {
                    approver_type: approverType,
                    approver_id: approverId,
                    is_backup: isBackup,
                    approver_name: $('#approver_id option:selected').text()
                };

                if (!selectedStage.approvers) {
                    selectedStage.approvers = [];
                }
                selectedStage.approvers.push(newApprover);

                // Update the stage in currentStages
                const stageIndex = currentStages.findIndex(s => s.id === selectedStage.id);
                if (stageIndex !== -1) {
                    currentStages[stageIndex] = selectedStage;
                }

                // Update UI
                loadStageApprovers(selectedStage);
                renderFlowStages();

                $('#addApproverModal').modal('hide');
                $('#addApproverForm')[0].reset();

                toastr.success('Approver added successfully');
            }

            // Populate stage position options
            function populateStagePositionOptions() {
                const select = $('#after_stage');
                select.empty().append('<option value="">Select Stage</option>');

                currentStages.forEach(stage => {
                    select.append(`<option value="${stage.id}">${stage.stage_name}</option>`);
                });
            }

            // Load test users
            function loadTestUsers() {
                $.ajax({
                    url: '/approval/approvers/search-users',
                    type: 'GET',
                    data: {
                        q: ''
                    },
                    success: function(response) {
                        if (response.success) {
                            const select = $('#test_submitted_by');
                            select.empty().append('<option value="">Select User</option>');

                            response.users.forEach(user => {
                                select.append(
                                    `<option value="${user.id}">${user.name}</option>`);
                            });
                        }
                    }
                });
            }

            // Run flow test
            function runFlowTest() {
                const testData = {
                    document_type: $('#test_document_type').val(),
                    document_id: $('#test_document_id').val(),
                    submitted_by: $('#test_submitted_by').val()
                };

                if (!testData.submitted_by) {
                    toastr.error('Please select a user for testing');
                    return;
                }

                $.ajax({
                    url: `/approval/designer/${flowId}/test`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        test_data: testData
                    },
                    success: function(response) {
                        if (response.success) {
                            displayTestResults(response.test_result);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to run test');
                    }
                });
            }

            // Display test results
            function displayTestResults(result) {
                const resultsDiv = $('#testResults');

                let html = `
            <div class="test-result">
                <h6>Flow: ${result.flow_name}</h6>
                <p><strong>Total Stages:</strong> ${result.total_stages}</p>
                <p><strong>Estimated Time:</strong> ${result.estimated_time} hours</p>

                <h6 class="mt-3">Stage Details:</h6>
                <div class="stage-details">
        `;

                result.stages.forEach((stage, index) => {
                    html += `
                <div class="stage-detail p-2 border rounded mb-2">
                    <strong>${index + 1}. ${stage.stage_name}</strong><br>
                    <small class="text-muted">
                        Type: ${stage.stage_type} |
                        Approvers: ${stage.approvers_count} |
                        Est. Time: ${stage.estimated_hours}h
                    </small>
                </div>
            `;
                });

                html += `
                </div>
            </div>
        `;

                resultsDiv.html(html);
            }
        });
    </script>
@endsection

@section('styles')
    <style>
        .flow-canvas-container {
            min-height: 400px;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .flow-canvas {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            min-height: 300px;
        }

        .empty-canvas {
            padding: 60px 20px;
            color: #6c757d;
        }

        .stage-item {
            background: white;
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 15px;
            min-width: 250px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stage-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .stage-item.selected {
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.25);
        }

        .stage-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .stage-number {
            background: #007bff;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .stage-name {
            font-weight: bold;
            flex: 1;
        }

        .stage-body {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stage-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stage-actions {
            display: flex;
            gap: 5px;
        }

        .stage-connector {
            text-align: center;
            color: #6c757d;
            font-size: 20px;
        }

        .approver-item {
            background-color: #f8f9fa;
        }

        .badge-sm {
            font-size: 0.75em;
        }

        .test-result {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }

        .stage-detail {
            background-color: white;
        }

        /* Drag & Drop Styles */
        .sortable-ghost {
            opacity: 0.5;
            background: #e9ecef;
        }

        .sortable-chosen {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
    </style>
@endsection
