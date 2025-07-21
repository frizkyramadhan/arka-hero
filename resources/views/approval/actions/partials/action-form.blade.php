@php
    $actionTypes = [
        'approve' => [
            'title' => 'Approve Document',
            'icon' => 'check',
            'color' => 'success',
            'url' => route('approval.actions.approve', $approval),
        ],
        'reject' => [
            'title' => 'Reject Document',
            'icon' => 'times',
            'color' => 'danger',
            'url' => route('approval.actions.reject', $approval),
        ],
        'forward' => [
            'title' => 'Forward Approval',
            'icon' => 'share',
            'color' => 'info',
            'url' => route('approval.actions.forward', $approval),
        ],
        'delegate' => [
            'title' => 'Delegate Approval',
            'icon' => 'user-friends',
            'color' => 'warning',
            'url' => route('approval.actions.delegate', $approval),
        ],
        'request_info' => [
            'title' => 'Request Information',
            'icon' => 'question-circle',
            'color' => 'secondary',
            'url' => route('approval.actions.request-info', $approval),
        ],
        'escalate' => [
            'title' => 'Escalate Approval',
            'icon' => 'exclamation-triangle',
            'color' => 'dark',
            'url' => route('approval.actions.escalate', $approval),
        ],
        'cancel' => [
            'title' => 'Cancel Approval',
            'icon' => 'ban',
            'color' => 'light',
            'url' => route('approval.actions.cancel', $approval),
        ],
    ];

    $actionConfig = $actionTypes[$action] ?? $actionTypes['approve'];
@endphp

<form id="approvalActionForm" action="{{ $actionConfig['url'] }}" method="POST">
    @csrf

    <div class="alert alert-{{ $actionConfig['color'] }}">
        <i class="fas fa-{{ $actionConfig['icon'] }}"></i>
        <strong>{{ $actionConfig['title'] }}</strong>
        <br>
        <small>Document: {{ ucfirst(str_replace('_', ' ', $approval->document_type)) }} (ID:
            {{ $approval->document_id }})</small>
    </div>

    @if ($action === 'approve')
        <div class="form-group">
            <label for="comments">Comments (Optional)</label>
            <textarea class="form-control" id="comments" name="comments" rows="3"
                placeholder="Enter your approval comments here..."></textarea>
        </div>

        <div class="form-group">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="auto_approve_next" name="auto_approve_next"
                    value="1">
                <label class="custom-control-label" for="auto_approve_next">
                    Auto-approve next stage if conditions are met
                </label>
            </div>
        </div>
    @elseif($action === 'reject')
        <div class="form-group">
            <label for="rejection_reason">Rejection Reason <span class="text-danger">*</span></label>
            <select class="form-control" id="rejection_reason" name="rejection_reason" required>
                <option value="">Select a reason</option>
                <option value="insufficient_information">Insufficient Information</option>
                <option value="policy_violation">Policy Violation</option>
                <option value="budget_exceeded">Budget Exceeded</option>
                <option value="timeline_issues">Timeline Issues</option>
                <option value="quality_concerns">Quality Concerns</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="comments">Detailed Comments <span class="text-danger">*</span></label>
            <textarea class="form-control" id="comments" name="comments" rows="4"
                placeholder="Please provide detailed explanation for rejection..." required></textarea>
        </div>
    @elseif($action === 'forward')
        <div class="form-group">
            <label for="forward_to">Forward To <span class="text-danger">*</span></label>
            <select class="form-control" id="forward_to" name="forward_to" required>
                <option value="">Select User</option>
                <!-- Users will be loaded via AJAX -->
            </select>
        </div>

        <div class="form-group">
            <label for="forward_reason">Forward Reason <span class="text-danger">*</span></label>
            <select class="form-control" id="forward_reason" name="forward_reason" required>
                <option value="">Select a reason</option>
                <option value="expertise_required">Expertise Required</option>
                <option value="workload_distribution">Workload Distribution</option>
                <option value="authority_level">Authority Level</option>
                <option value="conflict_of_interest">Conflict of Interest</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="comments">Additional Comments</label>
            <textarea class="form-control" id="comments" name="comments" rows="3"
                placeholder="Additional comments for forwarding..."></textarea>
        </div>
    @elseif($action === 'delegate')
        <div class="form-group">
            <label for="delegate_to">Delegate To <span class="text-danger">*</span></label>
            <select class="form-control" id="delegate_to" name="delegate_to" required>
                <option value="">Select User</option>
                <!-- Users will be loaded via AJAX -->
            </select>
        </div>

        <div class="form-group">
            <label for="delegation_reason">Delegation Reason <span class="text-danger">*</span></label>
            <select class="form-control" id="delegation_reason" name="delegation_reason" required>
                <option value="">Select a reason</option>
                <option value="out_of_office">Out of Office</option>
                <option value="workload_management">Workload Management</option>
                <option value="expertise_required">Expertise Required</option>
                <option value="authority_delegation">Authority Delegation</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="delegation_duration">Delegation Duration (Days) <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="delegation_duration" name="delegation_duration"
                min="1" max="30" value="7" required>
        </div>

        <div class="form-group">
            <label for="comments">Additional Comments</label>
            <textarea class="form-control" id="comments" name="comments" rows="3"
                placeholder="Additional comments for delegation..."></textarea>
        </div>
    @elseif($action === 'request_info')
        <div class="form-group">
            <label for="info_request">Information Request <span class="text-danger">*</span></label>
            <textarea class="form-control" id="info_request" name="info_request" rows="4"
                placeholder="Please specify what additional information is needed..." required></textarea>
        </div>

        <div class="form-group">
            <label for="deadline">Response Deadline <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="deadline" name="deadline"
                min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
        </div>

        <div class="form-group">
            <label for="priority">Priority <span class="text-danger">*</span></label>
            <select class="form-control" id="priority" name="priority" required>
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
            </select>
        </div>
    @elseif($action === 'escalate')
        <div class="form-group">
            <label for="escalate_to">Escalate To <span class="text-danger">*</span></label>
            <select class="form-control" id="escalate_to" name="escalate_to" required>
                <option value="">Select User</option>
                <!-- Users will be loaded via AJAX -->
            </select>
        </div>

        <div class="form-group">
            <label for="escalation_reason">Escalation Reason <span class="text-danger">*</span></label>
            <select class="form-control" id="escalation_reason" name="escalation_reason" required>
                <option value="">Select a reason</option>
                <option value="urgent_decision">Urgent Decision Required</option>
                <option value="complex_approval">Complex Approval</option>
                <option value="policy_decision">Policy Decision</option>
                <option value="budget_decision">Budget Decision</option>
                <option value="other">Other</option>
            </select>
        </div>
    @elseif($action === 'cancel')
        <div class="form-group">
            <label for="cancellation_reason">Cancellation Reason <span class="text-danger">*</span></label>
            <select class="form-control" id="cancellation_reason" name="cancellation_reason" required>
                <option value="">Select a reason</option>
                <option value="document_withdrawn">Document Withdrawn</option>
                <option value="duplicate_submission">Duplicate Submission</option>
                <option value="system_error">System Error</option>
                <option value="policy_change">Policy Change</option>
                <option value="other">Other</option>
            </select>
        </div>
    @endif

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-{{ $actionConfig['color'] }}">
            <i class="fas fa-{{ $actionConfig['icon'] }}"></i>
            {{ $actionConfig['title'] }}
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {
        // Load users for select dropdowns
        if (['forward', 'delegate', 'escalate'].includes('{{ $action }}')) {
            loadUsers();
        }

        // Handle form submission
        $('#approvalActionForm').submit(function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#actionModal').modal('hide');
                        setTimeout(function() {
                            window.location.href = response.redirect ||
                                '{{ route('approval.dashboard.index') }}';
                        }, 1500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Failed to process approval action');
                }
            });
        });

        function loadUsers() {
            $.ajax({
                url: '{{ route('users.list') }}',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const selectElement = $('#forward_to, #delegate_to, #escalate_to');
                        selectElement.empty();
                        selectElement.append('<option value="">Select User</option>');

                        response.users.forEach(user => {
                            selectElement.append(
                                `<option value="${user.id}">${user.name}</option>`);
                        });
                    }
                },
                error: function() {
                    toastr.error('Failed to load users');
                }
            });
        }
    });
</script>
