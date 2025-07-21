# Approval System User Manual

## Table of Contents

1. [System Overview](#system-overview)
2. [Getting Started](#getting-started)
3. [User Roles and Permissions](#user-roles-and-permissions)
4. [Approval Flows](#approval-flows)
5. [Document Integration](#document-integration)
6. [Approval Actions](#approval-actions)
7. [Delegation and Escalation](#delegation-and-escalation)
8. [Analytics and Reporting](#analytics-and-reporting)
9. [Troubleshooting](#troubleshooting)
10. [Best Practices](#best-practices)

## System Overview

The Approval System is a comprehensive workflow management solution that enables organizations to create, manage, and track approval processes for various document types. The system supports dynamic approval flows, delegation, escalation, and provides detailed analytics.

### Key Features

-   **Dynamic Approval Flows**: Create custom approval workflows with multiple stages
-   **Document Integration**: Seamlessly integrate with existing document types
-   **Delegation System**: Allow approvers to delegate their responsibilities
-   **Escalation Management**: Automatic escalation for overdue approvals
-   **Analytics Dashboard**: Track approval metrics and performance
-   **Notification System**: Real-time notifications for all stakeholders

### Supported Document Types

-   Official Travel Requests
-   Recruitment Requests
-   Employee Registration
-   And more (extensible)

## Getting Started

### Accessing the System

1. Navigate to the approval system dashboard
2. Log in with your credentials
3. Verify your role and permissions

### Dashboard Overview

The main dashboard displays:

-   Pending approvals assigned to you
-   Recent activity
-   Quick statistics
-   Navigation to different modules

## User Roles and Permissions

### Role Hierarchy

1. **System Administrator**

    - Full system access
    - Can create/modify approval flows
    - Manage users and roles
    - Access all analytics

2. **Department Head**

    - Approve documents within their department
    - Delegate approvals
    - View department-specific analytics

3. **Approver**

    - Review and approve/reject documents
    - Add comments and feedback
    - Delegate when necessary

4. **Document Creator**
    - Submit documents for approval
    - Track approval status
    - View approval history

### Permission Matrix

| Action               | Admin | Dept Head | Approver | Creator |
| -------------------- | ----- | --------- | -------- | ------- |
| Create Approval Flow | ✓     | ✗         | ✗        | ✗       |
| Modify Approval Flow | ✓     | ✗         | ✗        | ✗       |
| Submit Document      | ✓     | ✓         | ✓        | ✓       |
| Approve/Reject       | ✓     | ✓         | ✓        | ✗       |
| Delegate             | ✓     | ✓         | ✓        | ✗       |
| View Analytics       | ✓     | ✓         | ✗        | ✗       |
| Manage Users         | ✓     | ✗         | ✗        | ✗       |

## Approval Flows

### Creating an Approval Flow

1. **Navigate to Approval Flows**

    ```
    Dashboard → Approval Flows → Create New Flow
    ```

2. **Define Basic Information**

    - Flow Name: Descriptive name for the workflow
    - Document Type: Select the document type this flow applies to
    - Description: Optional detailed description

3. **Configure Stages**

    - Add stages in the desired order
    - Set approvers for each stage
    - Configure time limits and escalation rules

4. **Set Advanced Options**
    - Parallel vs Sequential processing
    - Required approval percentage
    - Auto-escalation settings

### Example Approval Flow Configuration

```php
// Example: Employee Registration Approval Flow
$flow = ApprovalFlow::create([
    'name' => 'Employee Registration Approval',
    'document_type' => 'employee_registration',
    'description' => 'Standard approval process for new employee registrations'
]);

// Stage 1: HR Review
$stage1 = $flow->stages()->create([
    'name' => 'HR Review',
    'order' => 1,
    'approvers' => ['hr_manager', 'hr_supervisor'],
    'time_limit' => 48, // hours
    'escalation_enabled' => true
]);

// Stage 2: Department Head Approval
$stage2 = $flow->stages()->create([
    'name' => 'Department Head Approval',
    'order' => 2,
    'approvers' => ['department_head'],
    'time_limit' => 72,
    'escalation_enabled' => true
]);

// Stage 3: Final Approval
$stage3 = $flow->stages()->create([
    'name' => 'Final Approval',
    'order' => 3,
    'approvers' => ['general_manager'],
    'time_limit' => 24,
    'escalation_enabled' => false
]);
```

### Managing Approval Flows

#### Editing Existing Flows

1. Navigate to the flow details
2. Click "Edit" button
3. Modify stages, approvers, or settings
4. Save changes

#### Deactivating Flows

-   Set status to "Inactive" to prevent new documents from using this flow
-   Existing approvals continue using the original flow

## Document Integration

### Adding Approval to Existing Documents

Documents can be integrated with the approval system using the `ApprovalTrait`:

```php
use App\Traits\ApprovalTrait;

class EmployeeRegistration extends Model
{
    use ApprovalTrait;

    // Your existing model code...
}
```

### Required Methods

Implement these methods in your document model:

```php
public function getApprovalFlow()
{
    return ApprovalFlow::where('document_type', 'employee_registration')
                      ->where('status', 'active')
                      ->first();
}

public function getApprovers()
{
    // Return users who can approve this document
    return User::whereHas('roles', function($query) {
        $query->whereIn('name', ['hr_manager', 'department_head']);
    })->get();
}

public function getCurrentStage()
{
    return $this->approvalStages()->where('status', 'pending')->first();
}
```

### Document Status Tracking

The system automatically tracks document status:

-   **Draft**: Document created but not submitted
-   **Pending**: Submitted and waiting for approval
-   **In Progress**: Being reviewed by approvers
-   **Approved**: All stages completed successfully
-   **Rejected**: Rejected at any stage
-   **Cancelled**: Withdrawn by creator

## Approval Actions

### Submitting for Approval

```php
// Submit a document for approval
$document = EmployeeRegistration::find($id);
$document->submitForApproval();
```

### Approving/Rejecting Documents

#### Via Web Interface

1. Navigate to "My Approvals"
2. Click on the document to review
3. Add comments if needed
4. Click "Approve" or "Reject"

#### Via API

```php
// Approve a document
$approval = ApprovalStage::find($stage_id);
$approval->approve($user_id, 'Approved with minor changes');

// Reject a document
$approval->reject($user_id, 'Incomplete information provided');
```

### Adding Comments

Comments provide context for decisions:

```php
$approval->addComment([
    'user_id' => auth()->id(),
    'comment' => 'Please provide additional documentation',
    'type' => 'request_info'
]);
```

### Bulk Actions

For multiple approvals:

```php
// Bulk approve multiple documents
ApprovalStage::whereIn('id', $stage_ids)
    ->update(['status' => 'approved', 'approved_by' => auth()->id()]);
```

## Delegation and Escalation

### Delegating Approvals

#### Setting Up Delegation

1. Navigate to "Delegation Settings"
2. Select the approver to delegate to
3. Set delegation period
4. Configure notification preferences

```php
// Delegate approval to another user
$delegation = ApprovalDelegation::create([
    'original_approver_id' => auth()->id(),
    'delegated_to_id' => $delegate_id,
    'start_date' => now(),
    'end_date' => now()->addDays(7),
    'reason' => 'Out of office'
]);
```

#### Accepting Delegation

1. Check "Delegated Approvals" section
2. Review delegated documents
3. Process as normal approvals

### Escalation Management

#### Automatic Escalation

The system automatically escalates overdue approvals:

```php
// Escalation configuration
$stage = ApprovalStage::find($stage_id);
$stage->update([
    'time_limit' => 48, // hours
    'escalation_enabled' => true,
    'escalation_to' => 'supervisor'
]);
```

#### Manual Escalation

```php
// Manually escalate an approval
$approval->escalate([
    'reason' => 'Urgent approval required',
    'escalated_to' => $supervisor_id
]);
```

### Escalation Notifications

Escalation triggers notifications:

-   Email to escalated approver
-   Dashboard notification
-   SMS (if configured)

## Analytics and Reporting

### Dashboard Analytics

#### Key Metrics

-   **Approval Rate**: Percentage of approved vs rejected documents
-   **Average Processing Time**: Time from submission to completion
-   **Bottleneck Analysis**: Stages with longest processing times
-   **Delegation Usage**: Frequency of delegation usage

#### Real-time Monitoring

```php
// Get approval statistics
$stats = ApprovalAnalytics::getStats([
    'date_range' => 'last_30_days',
    'document_type' => 'employee_registration'
]);

// Bottleneck analysis
$bottlenecks = ApprovalAnalytics::getBottlenecks();
```

### Custom Reports

#### Generating Reports

1. Navigate to "Reports" section
2. Select report type
3. Set date range and filters
4. Generate and export

#### Available Report Types

-   **Approval Performance Report**: Individual approver performance
-   **Department Summary**: Department-wise approval statistics
-   **Time Analysis**: Processing time trends
-   **Delegation Report**: Delegation patterns and usage

### Export Options

-   PDF format for formal reports
-   Excel/CSV for data analysis
-   API endpoints for integration

## Troubleshooting

### Common Issues

#### Document Stuck in Approval

**Problem**: Document remains in "Pending" status
**Solution**:

1. Check if approvers are available
2. Verify delegation settings
3. Review escalation rules
4. Contact system administrator

#### Missing Notifications

**Problem**: Users not receiving approval notifications
**Solution**:

1. Check notification settings
2. Verify email configuration
3. Review user preferences
4. Check spam filters

#### Delegation Not Working

**Problem**: Delegation not taking effect
**Solution**:

1. Verify delegation period
2. Check user permissions
3. Ensure delegate is active
4. Review delegation rules

### Error Messages

#### "No Active Approval Flow"

-   Ensure approval flow exists for document type
-   Check flow status (should be "Active")
-   Verify document type mapping

#### "Insufficient Permissions"

-   Check user role assignments
-   Verify approval permissions
-   Contact administrator for role updates

#### "Approval Stage Not Found"

-   Document may have been modified
-   Check approval flow changes
-   Review document status

### Debug Information

#### Enabling Debug Mode

```php
// Enable debug logging
config(['approval.debug' => true]);
```

#### Viewing Approval History

```php
// Get complete approval history
$history = $document->approvalHistory();
```

## Best Practices

### Approval Flow Design

#### Best Practices

1. **Keep Flows Simple**: Avoid overly complex approval chains
2. **Set Realistic Time Limits**: Consider approver availability
3. **Use Parallel Processing**: When possible, for efficiency
4. **Plan for Delegation**: Always have backup approvers

#### Anti-patterns to Avoid

-   Too many approval stages
-   Unrealistic time limits
-   No escalation rules
-   Missing delegation options

### Performance Optimization

#### Database Optimization

```php
// Use eager loading for approval relationships
$documents = EmployeeRegistration::with(['approvalStages.approver'])
    ->whereHas('approvalStages', function($query) {
        $query->where('status', 'pending');
    })->get();
```

#### Caching Strategies

```php
// Cache approval flow configurations
$flow = Cache::remember('approval_flow_' . $document_type, 3600, function() {
    return ApprovalFlow::where('document_type', $document_type)->first();
});
```

### Security Considerations

#### Access Control

-   Implement role-based access control
-   Regular permission audits
-   Monitor approval activities

#### Data Protection

-   Encrypt sensitive approval data
-   Implement audit logging
-   Regular backup procedures

### Maintenance

#### Regular Tasks

1. **Clean Old Data**: Archive completed approvals
2. **Update Flows**: Review and update approval flows
3. **Monitor Performance**: Track system performance
4. **User Training**: Regular user training sessions

#### Backup Procedures

```bash
# Backup approval data
php artisan backup:run --only-approval-data

# Restore approval data
php artisan backup:restore --approval-data
```

## Support and Contact

### Getting Help

-   **User Guide**: This manual
-   **System Documentation**: Technical documentation
-   **Support Team**: Contact IT support
-   **Training Sessions**: Regular training programs

### Feedback and Improvements

-   Submit feature requests
-   Report bugs and issues
-   Provide user feedback
-   Participate in user surveys

---

_This manual is regularly updated. Check for the latest version online._
