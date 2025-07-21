@extends('layouts.main')

@section('title', $title)

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('approval.dashboard.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('approval.actions.show', $approval) }}">Approval
                                Details</a></li>
                        <li class="breadcrumb-item active">Advanced Actions</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Conditional Approval -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-code-branch"></i>
                            Conditional Approval
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="condition_type">Condition Type</label>
                                    <select class="form-control" id="condition_type">
                                        <option value="">Select Condition</option>
                                        <option value="amount">Amount Threshold</option>
                                        <option value="department">Department</option>
                                        <option value="urgency">Urgency Level</option>
                                        <option value="custom">Custom Condition</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="condition_value">Condition Value</label>
                                    <input type="text" class="form-control" id="condition_value"
                                        placeholder="Enter condition value">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="approval_action">Approval Action</label>
                                    <select class="form-control" id="approval_action">
                                        <option value="approve">Approve</option>
                                        <option value="reject">Reject</option>
                                        <option value="forward">Forward</option>
                                        <option value="request_info">Request Info</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="condition_comments">Comments</label>
                                    <textarea class="form-control" id="condition_comments" rows="2" placeholder="Condition-specific comments"></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="applyConditionalApproval()">
                            <i class="fas fa-check"></i> Apply Conditional Approval
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batch Processing -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-layer-group"></i>
                            Batch Processing
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="batch_criteria">Batch Criteria</label>
                                    <select class="form-control" id="batch_criteria">
                                        <option value="">Select Criteria</option>
                                        <option value="same_department">Same Department</option>
                                        <option value="same_approver">Same Approver</option>
                                        <option value="same_document_type">Same Document Type</option>
                                        <option value="date_range">Date Range</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="batch_action">Batch Action</label>
                                    <select class="form-control" id="batch_action">
                                        <option value="approve">Approve All</option>
                                        <option value="reject">Reject All</option>
                                        <option value="forward">Forward All</option>
                                        <option value="delegate">Delegate All</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="batch_comments">Batch Comments</label>
                                    <textarea class="form-control" id="batch_comments" rows="2" placeholder="Comments for batch processing"></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success" onclick="processBatch()">
                            <i class="fas fa-play"></i> Process Batch
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Templates -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-alt"></i>
                            Approval Templates
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="template_category">Template Category</label>
                                    <select class="form-control" id="template_category">
                                        <option value="">Select Category</option>
                                        <option value="standard">Standard Approvals</option>
                                        <option value="urgent">Urgent Approvals</option>
                                        <option value="complex">Complex Approvals</option>
                                        <option value="budget">Budget Approvals</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="template_name">Template Name</label>
                                    <select class="form-control" id="template_name">
                                        <option value="">Select Template</option>
                                        <!-- Templates will be loaded via AJAX -->
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="template_preview">Template Preview</label>
                                    <textarea class="form-control" id="template_preview" rows="4" readonly
                                        placeholder="Template content will appear here"></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-info" onclick="applyTemplate()">
                            <i class="fas fa-magic"></i> Apply Template
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="saveAsTemplate()">
                            <i class="fas fa-save"></i> Save as Template
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Workflow Designer -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-project-diagram"></i>
                            Workflow Designer
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Workflow Stages</label>
                                    <div id="workflowStages" class="border p-3" style="min-height: 200px;">
                                        <div class="workflow-stage" data-stage="1">
                                            <div class="stage-header">
                                                <i class="fas fa-circle text-primary"></i>
                                                <span>Stage 1</span>
                                            </div>
                                            <div class="stage-content">
                                                <small>Initial Review</small>
                                            </div>
                                        </div>
                                        <div class="workflow-stage" data-stage="2">
                                            <div class="stage-header">
                                                <i class="fas fa-circle text-warning"></i>
                                                <span>Stage 2</span>
                                            </div>
                                            <div class="stage-content">
                                                <small>Manager Approval</small>
                                            </div>
                                        </div>
                                        <div class="workflow-stage" data-stage="3">
                                            <div class="stage-header">
                                                <i class="fas fa-circle text-success"></i>
                                                <span>Stage 3</span>
                                            </div>
                                            <div class="stage-content">
                                                <small>Final Approval</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label>Stage Configuration</label>
                                    <div id="stageConfig" class="border p-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="stage_name">Stage Name</label>
                                                    <input type="text" class="form-control" id="stage_name"
                                                        value="Initial Review">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="stage_type">Stage Type</label>
                                                    <select class="form-control" id="stage_type">
                                                        <option value="sequential">Sequential</option>
                                                        <option value="parallel">Parallel</option>
                                                        <option value="conditional">Conditional</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="stage_approvers">Approvers</label>
                                                    <select class="form-control" id="stage_approvers" multiple>
                                                        <option value="1">John Doe</option>
                                                        <option value="2">Jane Smith</option>
                                                        <option value="3">Manager Role</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="stage_escalation">Escalation (hours)</label>
                                                    <input type="number" class="form-control" id="stage_escalation"
                                                        value="72" min="1" max="168">
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary" onclick="saveStageConfig()">
                                            <i class="fas fa-save"></i> Save Stage Config
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Analytics -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line"></i>
                            Approval Analytics
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <canvas id="approvalTrendChart" style="height: 300px;"></canvas>
                            </div>
                            <div class="col-md-6">
                                <canvas id="approvalDistributionChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Metric</th>
                                                <th>Value</th>
                                                <th>Trend</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Average Approval Time</td>
                                                <td>2.5 days</td>
                                                <td><span class="text-success"><i class="fas fa-arrow-down"></i>
                                                        -15%</span></td>
                                                <td><span class="badge badge-success">Good</span></td>
                                            </tr>
                                            <tr>
                                                <td>Approval Rate</td>
                                                <td>85%</td>
                                                <td><span class="text-success"><i class="fas fa-arrow-up"></i> +5%</span>
                                                </td>
                                                <td><span class="badge badge-success">Good</span></td>
                                            </tr>
                                            <tr>
                                                <td>Escalation Rate</td>
                                                <td>12%</td>
                                                <td><span class="text-warning"><i class="fas fa-arrow-up"></i> +2%</span>
                                                </td>
                                                <td><span class="badge badge-warning">Monitor</span></td>
                                            </tr>
                                            <tr>
                                                <td>Rejection Rate</td>
                                                <td>8%</td>
                                                <td><span class="text-success"><i class="fas fa-arrow-down"></i>
                                                        -3%</span></td>
                                                <td><span class="badge badge-success">Good</span></td>
                                            </tr>
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
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize charts
            initializeCharts();

            // Initialize workflow designer
            initializeWorkflowDesigner();

            // Load templates when category changes
            $('#template_category').change(function() {
                loadTemplates($(this).val());
            });

            // Load template preview when template changes
            $('#template_name').change(function() {
                loadTemplatePreview($(this).val());
            });
        });

        function applyConditionalApproval() {
            const conditionType = $('#condition_type').val();
            const conditionValue = $('#condition_value').val();
            const approvalAction = $('#approval_action').val();
            const comments = $('#condition_comments').val();

            if (!conditionType || !conditionValue || !approvalAction) {
                toastr.error('Please fill in all required fields');
                return;
            }

            $.ajax({
                url: '{{ route('approval.actions.conditional-approve', $approval) }}',
                type: 'POST',
                data: {
                    condition_type: conditionType,
                    condition_value: conditionValue,
                    approval_action: approvalAction,
                    comments: comments,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Failed to apply conditional approval');
                }
            });
        }

        function processBatch() {
            const criteria = $('#batch_criteria').val();
            const action = $('#batch_action').val();
            const comments = $('#batch_comments').val();

            if (!criteria || !action) {
                toastr.error('Please select batch criteria and action');
                return;
            }

            $.ajax({
                url: '{{ route('approval.actions.batch-process') }}',
                type: 'POST',
                data: {
                    criteria: criteria,
                    action: action,
                    comments: comments,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Failed to process batch');
                }
            });
        }

        function loadTemplates(category) {
            $.ajax({
                url: '{{ route('approval.templates.list') }}',
                type: 'GET',
                data: {
                    category: category
                },
                success: function(response) {
                    if (response.success) {
                        $('#template_name').empty();
                        $('#template_name').append('<option value="">Select Template</option>');

                        response.templates.forEach(template => {
                            $('#template_name').append(
                                `<option value="${template.id}">${template.name}</option>`);
                        });
                    }
                },
                error: function() {
                    toastr.error('Failed to load templates');
                }
            });
        }

        function loadTemplatePreview(templateId) {
            if (!templateId) {
                $('#template_preview').val('');
                return;
            }

            $.ajax({
                url: `{{ route('approval.templates.preview', '') }}/${templateId}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#template_preview').val(response.template.content);
                    }
                },
                error: function() {
                    toastr.error('Failed to load template preview');
                }
            });
        }

        function applyTemplate() {
            const templateId = $('#template_name').val();

            if (!templateId) {
                toastr.error('Please select a template');
                return;
            }

            $.ajax({
                url: `{{ route('approval.templates.apply', '') }}/${templateId}`,
                type: 'POST',
                data: {
                    approval_id: '{{ $approval->id }}',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Failed to apply template');
                }
            });
        }

        function saveAsTemplate() {
            const name = prompt('Enter template name:');
            if (!name) return;

            $.ajax({
                url: '{{ route('approval.templates.save') }}',
                type: 'POST',
                data: {
                    name: name,
                    approval_id: '{{ $approval->id }}',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Template saved successfully');
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Failed to save template');
                }
            });
        }

        function saveStageConfig() {
            const stageName = $('#stage_name').val();
            const stageType = $('#stage_type').val();
            const approvers = $('#stage_approvers').val();
            const escalation = $('#stage_escalation').val();

            $.ajax({
                url: '{{ route('approval.workflow.save-stage') }}',
                type: 'POST',
                data: {
                    stage_name: stageName,
                    stage_type: stageType,
                    approvers: approvers,
                    escalation: escalation,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Stage configuration saved');
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Failed to save stage configuration');
                }
            });
        }

        function initializeCharts() {
            // Approval Trend Chart
            const trendCtx = document.getElementById('approvalTrendChart').getContext('2d');
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Approval Time (days)',
                        data: [3.2, 2.8, 2.5, 2.3, 2.1, 2.0],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Approval Time Trend'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Approval Distribution Chart
            const distributionCtx = document.getElementById('approvalDistributionChart').getContext('2d');
            new Chart(distributionCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Approved', 'Rejected', 'Pending', 'Escalated'],
                    datasets: [{
                        data: [65, 8, 20, 7],
                        backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Approval Distribution'
                        }
                    }
                }
            });
        }

        function initializeWorkflowDesigner() {
            // Make workflow stages draggable
            $('.workflow-stage').draggable({
                containment: '#workflowStages',
                cursor: 'move'
            });

            // Handle stage selection
            $('.workflow-stage').click(function() {
                $('.workflow-stage').removeClass('selected');
                $(this).addClass('selected');

                // Load stage configuration
                const stageId = $(this).data('stage');
                loadStageConfig(stageId);
            });
        }

        function loadStageConfig(stageId) {
            $.ajax({
                url: `{{ route('approval.workflow.get-stage', '') }}/${stageId}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#stage_name').val(response.stage.name);
                        $('#stage_type').val(response.stage.type);
                        $('#stage_approvers').val(response.stage.approvers);
                        $('#stage_escalation').val(response.stage.escalation);
                    }
                },
                error: function() {
                    toastr.error('Failed to load stage configuration');
                }
            });
        }
    </script>
@endsection

@section('styles')
    <style>
        .workflow-stage {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .workflow-stage:hover {
            background-color: #f8f9fa;
            border-color: #007bff;
        }

        .workflow-stage.selected {
            background-color: #e3f2fd;
            border-color: #2196f3;
        }

        .stage-header {
            display: flex;
            align-items: center;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stage-header i {
            margin-right: 8px;
        }

        .stage-content {
            color: #6c757d;
            font-size: 0.9em;
        }

        #workflowStages {
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        #stageConfig {
            background-color: #fff;
            border-radius: 5px;
        }
    </style>
@endsection
