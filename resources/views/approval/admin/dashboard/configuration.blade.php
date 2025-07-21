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
                        <li class="breadcrumb-item"><a href="{{ route('approval.admin.dashboard.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Configuration</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- System Settings -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-cog"></i>
                            System Settings
                        </h3>
                    </div>
                    <div class="card-body">
                        <form id="systemSettingsForm">
                            <div class="form-group">
                                <label for="escalation_default_hours">Default Escalation Hours</label>
                                <input type="number" class="form-control" id="escalation_default_hours"
                                    name="escalation_default_hours" value="{{ $config['escalation_default_hours'] }}"
                                    min="1" max="720">
                                <small class="form-text text-muted">Hours before automatic escalation (1-720 hours)</small>
                            </div>

                            <div class="form-group">
                                <label for="max_approvers_per_stage">Max Approvers per Stage</label>
                                <input type="number" class="form-control" id="max_approvers_per_stage"
                                    name="max_approvers_per_stage" value="{{ $config['max_approvers_per_stage'] }}"
                                    min="1" max="50">
                                <small class="form-text text-muted">Maximum number of approvers per approval stage</small>
                            </div>

                            <div class="form-group">
                                <label for="max_stages_per_flow">Max Stages per Flow</label>
                                <input type="number" class="form-control" id="max_stages_per_flow"
                                    name="max_stages_per_flow" value="{{ $config['max_stages_per_flow'] }}" min="1"
                                    max="100">
                                <small class="form-text text-muted">Maximum number of stages per approval flow</small>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="notification_enabled"
                                        name="notification_enabled" {{ $config['notification_enabled'] ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="notification_enabled">Enable
                                        Notifications</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="audit_logging_enabled"
                                        name="audit_logging_enabled"
                                        {{ $config['audit_logging_enabled'] ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="audit_logging_enabled">Enable Audit
                                        Logging</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="auto_approval_enabled"
                                        name="auto_approval_enabled"
                                        {{ $config['auto_approval_enabled'] ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="auto_approval_enabled">Enable
                                        Auto-Approval</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save System Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bell"></i>
                            Notification Configuration
                        </h3>
                    </div>
                    <div class="card-body">
                        <form id="notificationConfigForm">
                            <div class="form-group">
                                <label for="email_notifications">Email Notifications</label>
                                <select class="form-control" id="email_notifications" name="email_notifications">
                                    <option value="all">All Notifications</option>
                                    <option value="important">Important Only</option>
                                    <option value="none">Disabled</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="escalation_notifications">Escalation Notifications</label>
                                <select class="form-control" id="escalation_notifications" name="escalation_notifications">
                                    <option value="immediate">Immediate</option>
                                    <option value="hourly">Hourly</option>
                                    <option value="daily">Daily</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="notification_channels">Notification Channels</label>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="email_channel"
                                        name="channels[]" value="email" checked>
                                    <label class="custom-control-label" for="email_channel">Email</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="sms_channel"
                                        name="channels[]" value="sms">
                                    <label class="custom-control-label" for="sms_channel">SMS</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="push_channel"
                                        name="channels[]" value="push">
                                    <label class="custom-control-label" for="push_channel">Push Notifications</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="notification_template">Default Notification Template</label>
                                <textarea class="form-control" id="notification_template" name="notification_template" rows="4"
                                    placeholder="Enter default notification template..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Notification Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Document Type Registration -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-alt"></i>
                            Document Type Registration
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
                                data-target="#addDocumentTypeModal">
                                <i class="fas fa-plus"></i> Add Document Type
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="documentTypesTable">
                                <thead>
                                    <tr>
                                        <th>Document Type</th>
                                        <th>Model Class</th>
                                        <th>Table Name</th>
                                        <th>Approval Flow</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Official Travel</td>
                                        <td>Officialtravel</td>
                                        <td>officialtravels</td>
                                        <td>
                                            <select class="form-control form-control-sm"
                                                data-document-type="officialtravel">
                                                <option value="">Select Flow</option>
                                                <option value="1">Linear Approval</option>
                                                <option value="2">Parallel Approval</option>
                                            </select>
                                        </td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info">Configure</button>
                                            <button type="button" class="btn btn-sm btn-warning">Test</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Recruitment Request</td>
                                        <td>RecruitmentRequest</td>
                                        <td>recruitment_requests</td>
                                        <td>
                                            <select class="form-control form-control-sm"
                                                data-document-type="recruitment_request">
                                                <option value="">Select Flow</option>
                                                <option value="3">3-Stage Approval</option>
                                                <option value="4">HR Approval</option>
                                            </select>
                                        </td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info">Configure</button>
                                            <button type="button" class="btn btn-sm btn-warning">Test</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Employee Registration</td>
                                        <td>EmployeeRegistration</td>
                                        <td>employee_registrations</td>
                                        <td>
                                            <select class="form-control form-control-sm"
                                                data-document-type="employee_registration">
                                                <option value="">Select Flow</option>
                                                <option value="5">Simple Approval</option>
                                                <option value="6">Admin Review</option>
                                            </select>
                                        </td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info">Configure</button>
                                            <button type="button" class="btn btn-sm btn-warning">Test</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Escalation Rules -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Escalation Rules
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
                                data-target="#addEscalationRuleModal">
                                <i class="fas fa-plus"></i> Add Rule
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="escalationRulesTable">
                                <thead>
                                    <tr>
                                        <th>Rule Name</th>
                                        <th>Document Type</th>
                                        <th>Stage</th>
                                        <th>Trigger Hours</th>
                                        <th>Action</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>HR Escalation</td>
                                        <td>Recruitment Request</td>
                                        <td>HR Review</td>
                                        <td>48</td>
                                        <td>Notify Manager</td>
                                        <td><span class="badge badge-warning">Medium</span></td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info">Edit</button>
                                            <button type="button" class="btn btn-sm btn-danger">Delete</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Travel Approval Escalation</td>
                                        <td>Official Travel</td>
                                        <td>Manager Approval</td>
                                        <td>24</td>
                                        <td>Auto-Forward</td>
                                        <td><span class="badge badge-danger">High</span></td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info">Edit</button>
                                            <button type="button" class="btn btn-sm btn-danger">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Flow Templates -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-copy"></i>
                            Approval Flow Templates
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
                                data-target="#addTemplateModal">
                                <i class="fas fa-plus"></i> Add Template
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Linear Approval</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">Sequential approval flow with one approver per stage.</p>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check text-success"></i> Simple to configure</li>
                                            <li><i class="fas fa-check text-success"></i> Clear approval chain</li>
                                            <li><i class="fas fa-check text-success"></i> Easy to track</li>
                                        </ul>
                                        <button type="button" class="btn btn-sm btn-primary">Use Template</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Parallel Approval</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">Multiple approvers can approve simultaneously.</p>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check text-success"></i> Faster processing</li>
                                            <li><i class="fas fa-check text-success"></i> Flexible approval</li>
                                            <li><i class="fas fa-check text-success"></i> Reduced bottlenecks</li>
                                        </ul>
                                        <button type="button" class="btn btn-sm btn-primary">Use Template</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Conditional Approval</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">Approval flow changes based on conditions.</p>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check text-success"></i> Dynamic routing</li>
                                            <li><i class="fas fa-check text-success"></i> Smart automation</li>
                                            <li><i class="fas fa-check text-success"></i> Complex workflows</li>
                                        </ul>
                                        <button type="button" class="btn btn-sm btn-primary">Use Template</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Document Type Modal -->
    <div class="modal fade" id="addDocumentTypeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i>
                        Add Document Type
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="addDocumentTypeForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="document_type_name">Document Type Name</label>
                                    <input type="text" class="form-control" id="document_type_name" name="name"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="document_type_code">Document Type Code</label>
                                    <input type="text" class="form-control" id="document_type_code" name="code"
                                        required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="model_class">Model Class</label>
                                    <input type="text" class="form-control" id="model_class" name="model_class"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="table_name">Table Name</label>
                                    <input type="text" class="form-control" id="table_name" name="table_name"
                                        required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="document_description">Description</label>
                            <textarea class="form-control" id="document_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="default_approval_flow">Default Approval Flow</label>
                            <select class="form-control" id="default_approval_flow" name="default_approval_flow">
                                <option value="">Select Flow</option>
                                <option value="linear">Linear Approval</option>
                                <option value="parallel">Parallel Approval</option>
                                <option value="conditional">Conditional Approval</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Document Type
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Escalation Rule Modal -->
    <div class="modal fade" id="addEscalationRuleModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i>
                        Add Escalation Rule
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="addEscalationRuleForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="rule_name">Rule Name</label>
                            <input type="text" class="form-control" id="rule_name" name="name" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rule_document_type">Document Type</label>
                                    <select class="form-control" id="rule_document_type" name="document_type" required>
                                        <option value="">Select Document Type</option>
                                        <option value="officialtravel">Official Travel</option>
                                        <option value="recruitment_request">Recruitment Request</option>
                                        <option value="employee_registration">Employee Registration</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rule_stage">Stage</label>
                                    <select class="form-control" id="rule_stage" name="stage" required>
                                        <option value="">Select Stage</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="trigger_hours">Trigger Hours</label>
                                    <input type="number" class="form-control" id="trigger_hours" name="trigger_hours"
                                        min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="escalation_action">Action</label>
                                    <select class="form-control" id="escalation_action" name="action" required>
                                        <option value="notify">Notify Manager</option>
                                        <option value="auto_forward">Auto-Forward</option>
                                        <option value="escalate">Escalate to Next Level</option>
                                        <option value="auto_approve">Auto-Approve</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="rule_priority">Priority</label>
                            <select class="form-control" id="rule_priority" name="priority" required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Rule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Template Modal -->
    <div class="modal fade" id="addTemplateModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i>
                        Add Approval Flow Template
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="addTemplateForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="template_name">Template Name</label>
                            <input type="text" class="form-control" id="template_name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="template_description">Description</label>
                            <textarea class="form-control" id="template_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="template_type">Template Type</label>
                            <select class="form-control" id="template_type" name="type" required>
                                <option value="linear">Linear Approval</option>
                                <option value="parallel">Parallel Approval</option>
                                <option value="conditional">Conditional Approval</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="template_stages">Stages Configuration</label>
                            <textarea class="form-control" id="template_stages" name="stages" rows="6"
                                placeholder="Enter JSON configuration for stages..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Template
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
            // System Settings Form
            $('#systemSettingsForm').submit(function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route('approval.admin.dashboard.save-system-settings') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('System settings saved successfully');
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to save system settings');
                    }
                });
            });

            // Notification Config Form
            $('#notificationConfigForm').submit(function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route('approval.admin.dashboard.save-notification-config') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Notification settings saved successfully');
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to save notification settings');
                    }
                });
            });

            // Document Type Flow Assignment
            $('[data-document-type]').change(function() {
                const documentType = $(this).data('document-type');
                const flowId = $(this).val();

                if (flowId) {
                    $.ajax({
                        url: '{{ route('approval.admin.dashboard.assign-flow') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            document_type: documentType,
                            flow_id: flowId
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success('Flow assigned successfully');
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function() {
                            toastr.error('Failed to assign flow');
                        }
                    });
                }
            });

            // Add Document Type Form
            $('#addDocumentTypeForm').submit(function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route('approval.admin.dashboard.add-document-type') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Document type added successfully');
                            $('#addDocumentTypeModal').modal('hide');
                            $('#addDocumentTypeForm')[0].reset();
                            // Refresh document types table
                            location.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to add document type');
                    }
                });
            });

            // Add Escalation Rule Form
            $('#addEscalationRuleForm').submit(function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route('approval.admin.dashboard.add-escalation-rule') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Escalation rule added successfully');
                            $('#addEscalationRuleModal').modal('hide');
                            $('#addEscalationRuleForm')[0].reset();
                            // Refresh escalation rules table
                            location.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to add escalation rule');
                    }
                });
            });

            // Add Template Form
            $('#addTemplateForm').submit(function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route('approval.admin.dashboard.add-template') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Template added successfully');
                            $('#addTemplateModal').modal('hide');
                            $('#addTemplateForm')[0].reset();
                            // Refresh templates section
                            location.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to add template');
                    }
                });
            });

            // Document type change handler for escalation rules
            $('#rule_document_type').change(function() {
                const documentType = $(this).val();
                const stageSelect = $('#rule_stage');

                if (documentType) {
                    $.ajax({
                        url: '{{ route('approval.admin.dashboard.get-stages') }}',
                        type: 'GET',
                        data: {
                            document_type: documentType
                        },
                        success: function(response) {
                            if (response.success) {
                                stageSelect.empty();
                                stageSelect.append('<option value="">Select Stage</option>');

                                response.stages.forEach(stage => {
                                    stageSelect.append(
                                        `<option value="${stage.id}">${stage.name}</option>`
                                        );
                                });
                            }
                        },
                        error: function() {
                            toastr.error('Failed to load stages');
                        }
                    });
                }
            });

            // Template type change handler
            $('#template_type').change(function() {
                const templateType = $(this).val();
                const stagesTextarea = $('#template_stages');

                if (templateType !== 'custom') {
                    const templates = {
                        linear: JSON.stringify([{
                                name: 'Stage 1',
                                order: 1,
                                type: 'sequential'
                            },
                            {
                                name: 'Stage 2',
                                order: 2,
                                type: 'sequential'
                            },
                            {
                                name: 'Stage 3',
                                order: 3,
                                type: 'sequential'
                            }
                        ], null, 2),
                        parallel: JSON.stringify([{
                                name: 'Parallel Stage 1',
                                order: 1,
                                type: 'parallel'
                            },
                            {
                                name: 'Parallel Stage 2',
                                order: 2,
                                type: 'parallel'
                            }
                        ], null, 2),
                        conditional: JSON.stringify([{
                                name: 'Initial Review',
                                order: 1,
                                type: 'sequential'
                            },
                            {
                                name: 'Conditional Stage',
                                order: 2,
                                type: 'conditional'
                            },
                            {
                                name: 'Final Approval',
                                order: 3,
                                type: 'sequential'
                            }
                        ], null, 2)
                    };

                    stagesTextarea.val(templates[templateType] || '');
                }
            });
        });
    </script>
@endsection

@section('styles')
    <style>
        .custom-control {
            margin-bottom: 10px;
        }

        .card {
            margin-bottom: 20px;
        }

        .badge {
            font-size: 0.8em;
        }

        .table th {
            background-color: #f8f9fa;
        }
    </style>
@endsection
