# Approval Preview Update Documentation

## Overview

This document tracks the implementation of department-based approval system, sequential approval workflow, and approval stage management improvements.

## Recent Updates

### Sequential Approval Implementation in ApprovalRequestController

**Date**: 2025-08-25  
**Status**: ✅ **COMPLETED**

**Objective**: Implement sequential approval logic in ApprovalRequestController to ensure approvals follow the correct order and handle rejections properly.

**Changes Made**:

1. **Sequential Approval Validation**:

    - Added `canProcessApproval()` method to check if current approval can be processed
    - Validates that previous approvals in sequence are completed before allowing current approval
    - Respects `is_sequential` flag from ApprovalStage configuration

2. **Enhanced Rejection Handling**:

    - Immediate document rejection when any approver rejects
    - Closes all remaining approval plans upon rejection
    - Adds `rejected_at` timestamp to document
    - Comprehensive logging for rejection events

3. **Sequential Completion Logic**:

    - Added `areAllSequentialApprovalsCompleted()` method
    - Checks if all approvals in sequence are completed
    - Handles both sequential and parallel approval workflows
    - Only marks document as approved when all sequential steps complete

4. **Bulk Approval Enhancement**:
    - Added sequential validation to bulk approval operations
    - Prevents bulk approval of items that violate sequential order
    - Maintains data integrity across multiple approvals

**Key Methods Added**:

```php
private function canProcessApproval($approvalPlan)
// Validates sequential approval order

private function areAllSequentialApprovalsCompleted($approvalPlan, $allApprovalPlans)
// Checks if all sequential approvals are completed

private function getDocumentProjectId($approvalPlan)
// Extracts project ID from document

private function getDocumentDepartmentId($approvalPlan)
// Extracts department ID from document
```

**Benefits**:

-   **Sequential Integrity**: Approvals must follow the configured order
-   **Immediate Rejection**: Documents are rejected immediately upon any rejection
-   **Better User Experience**: Clear error messages for out-of-order approvals
-   **Data Consistency**: Prevents approval state inconsistencies
-   **Audit Trail**: Comprehensive logging for all approval decisions

**Workflow Examples**:

1. **Sequential Approval (3 steps)**:

    - Step 1: Gusti approves → Document moves to Step 2
    - Step 2: Rachman can now approve → Document moves to Step 3
    - Step 3: Eddy can now approve → Document fully approved

2. **Rejection Handling**:

    - Any step rejects → Document immediately rejected
    - All remaining approval plans closed
    - No further approvals possible

3. **Parallel Approval**:
    - When `is_sequential = false`
    - All approvers can approve simultaneously
    - Document approved when all complete

## Overview

Updated the approval preview functionality to support both document types:

-   **Official Travel**: Uses logged-in user's department automatically
-   **Recruitment Request**: Requires department_id parameter from the form

## Changes Made

### 1. Frontend Updates

#### Recruitment Request Create Form:

-   **Before**: Only required project selection for approval preview
-   **After**: Requires both project and department selection for approval preview

#### Approval Preview Function:

```javascript
function loadApprovalPreview() {
    const projectId = $("#project_id").val();
    const departmentId = $("#department_id").val();

    if (!projectId || !departmentId) {
        $("#approvalPreview").html(`
            <div class="text-center py-3">
                <i class="fas fa-info-circle text-info"></i>
                <div class="mt-2">Select both project and department to see approval flow</div>
            </div>
        `);
        return;
    }
    // ... rest of function
}
```

#### Event Listeners:

-   **Before**: Only `#project_id` change triggered approval preview
-   **After**: Both `#project_id` and `#department_id` changes trigger approval preview

### 2. Backend Updates

#### ApprovalPlanController.php:

-   **Function `create_approval_plan`**: Now uses `department_id` from document recruitment request
-   **Logic**:
    -   `recruitment_request` → `$document->department_id`
    -   `officialtravel` → `Auth::user()->departments->first()->id`

#### ApprovalStageController.php:

-   **Method `preview`**: Can receive `department_id` parameter for recruitment request
-   **Conditional Logic**:
    -   Recruitment request requires `department_id` from request
    -   Official travel uses user's department automatically

### 3. Sequential Approval System Implementation

#### Database Changes:

-   Added `approval_order` field to `approval_stages` table
-   Added `is_sequential` field to `approval_stages` table
-   Added `approval_order` field to `approval_plans` table

#### Model Updates:

-   **ApprovalStage Model**: Added scopes for sequential/parallel approval
-   **ApprovalPlan Model**: Added methods to check if approval can be processed

#### Controller Updates:

-   **ApprovalPlanController**: Sequential validation in update method
-   **ApprovalStageController**: Support for approval order in CRUD operations

#### UI Updates:

-   **Approval Stages Index**: Added Approval Order column
-   **Create/Edit Forms**: Added approval order and sequential toggle fields
-   **Approval Preview**: Shows step numbers and sequential/parallel indicators

### 4. Document-Type Based Approval Order System

#### New Implementation:

-   **Approval Order**: Automatically assigned based on document type, not project-department combination
-   **Sequential Numbering**: Each document type has its own sequence (1, 2, 3, etc.)
-   **Auto-Increment**: Order is determined by when approval stage is created for each document type

#### Benefits:

-   **Cleaner Workflows**: No duplicate approval orders for same document type
-   **Easier Management**: Admin only needs to set sequential toggle, order is automatic
-   **Better Organization**: Approval stages grouped by document type with clear numbering

## Implementation Details

### Sequential Approval Logic:

1. **Approval Order**: Each approval stage has a numeric order (1, 2, 3, etc.) based on document type
2. **Sequential Validation**: Previous approvals must be completed before next step
3. **Parallel Approval**: Can be processed simultaneously if `is_sequential = false`

### Approval Flow Example:

```
Recruitment Request: Gusti (1) → Rachman (2) → Eddy (3)
Official Travel: Eddy (1) → Rachman (2)
```

### Database Structure:

```sql
-- approval_stages table
approval_order: 1, 2, 3 (order of approval per document type)
is_sequential: true/false (whether sequential approval required)

-- approval_plans table
approval_order: copied from approval_stages for tracking
```

### New Approval Order Display:

```
OT:1  - Official Travel, Step 1
RR:2  - Recruitment Request, Step 2
OT:3  - Official Travel, Step 3
```

## Usage

### Creating Approval Stages:

1. Go to Approval Stages Management
2. Select approver, projects, departments, and document types
3. **Approval order is automatically assigned** based on document type
4. Toggle sequential approval requirement

### Viewing Approval Flow:

1. In recruitment request form, select project and department
2. Approval preview will show ordered steps with sequential indicators
3. Each step shows approver name, department, and order number

### Processing Approvals:

1. Approvals are processed in order (if sequential)
2. System prevents processing later steps before earlier ones
3. Document is approved when all sequential approvals are completed

## Benefits

1. **Flexible Approval Workflows**: Different document types can have different approval flows
2. **Sequential Control**: Ensures proper approval order for critical documents
3. **Parallel Processing**: Allows simultaneous approvals when appropriate
4. **Clear Visualization**: Users can see exact approval flow before submission
5. **Department-Specific**: Approval flows can vary by department and project
6. **Cleaner Management**: No manual order input, automatic based on document type
7. **Better Organization**: Approval stages clearly grouped by document type

## Future Enhancements

1. **Conditional Approval Paths**: Different flows based on document content
2. **Approval Delegation**: Allow approvers to delegate to others
3. **Approval Timeouts**: Automatic escalation if approvals take too long
4. **Approval Templates**: Pre-configured approval flows for common scenarios
5. **Order Reordering**: Allow admin to reorder approval stages within document type

## Bug Fixes & Improvements

### Delete Approval Stage Error Handling

**Issue**: "An error occurred while deleting the approval stages" error appeared during AJAX delete operations, even though the operation was successful.

**Root Cause**:

-   Controller `destroy` method was returning `redirect()` responses for AJAX requests
-   AJAX requests expect JSON responses, not redirects
-   Frontend error handling was not properly parsing response types

**Solution Implemented**:

1. **Controller Updates**:

    - Added `request()->ajax()` checks in `ApprovalStageController::destroy()`
    - Return JSON responses for AJAX requests
    - Maintain redirect responses for regular form submissions

2. **Frontend Improvements**:

    - Enhanced JavaScript error handling
    - Better response parsing for success/error cases
    - DataTable reload instead of full page refresh
    - Improved toastr configuration and user feedback

3. **Response Format**:

    ```json
    // Success Response
    {
        "success": true,
        "message": "All approval stages for Gusti have been deleted successfully. (8 records deleted)",
        "deleted_count": 8
    }

    // Error Response
    {
        "success": false,
        "message": "Cannot delete approval stages. This approver has active approval plans."
    }
    ```

**Benefits**:

-   No more false error messages during successful operations
-   Better user experience with proper success/error feedback
-   Faster UI updates (DataTable reload vs full page refresh)
-   Consistent error handling across the application

### Duplicate Approval Stage Prevention

**Issue**: Potential for creating duplicate approval stages with the same combination of project_id, department_id, document_type, approver_id, and approval_order.

**Root Cause**:

-   Previous validation only checked basic duplication without considering approval_order
-   No database-level constraints to prevent duplicates
-   Update operations could create conflicts with existing stages

**Solution Implemented**:

1. **Enhanced Validation Logic**:

    - **Store Method**: Check for exact duplicate combinations before creation
    - **Update Method**: Check for conflicts with other approvers (excluding current)
    - **Comprehensive Check**: Include approval_order in duplication validation

2. **Database Constraints**:

    - Added unique constraint on combination: `[project_id, department_id, document_type, approver_id, approval_order]`
    - Prevents duplicate entries at database level
    - Ensures data integrity across all operations

3. **User Feedback**:
    - Clear error messages showing which combinations already exist
    - Form validation errors displayed prominently
    - Prevents form submission when duplicates detected

**Validation Rules**:

```php
// Check for exact duplicate combination
$existingStage = ApprovalStage::where('department_id', $department->id)
    ->where('approver_id', $request->approver_id)
    ->where('project_id', $project)
    ->where('document_type', $document)
    ->where('approval_order', $request->approval_order)
    ->first();
```

**Database Constraint**:

```sql
-- Unique constraint to prevent duplicates
UNIQUE KEY `unique_approval_stage_combination`
(`project_id`, `department_id`, `document_type`, `approver_id`, `approval_order`)
```

**Benefits**:

-   **Data Integrity**: No duplicate approval stages can exist
-   **Clear Validation**: Users get immediate feedback on conflicts
-   **Database Security**: Constraint prevents bypassing application validation
-   **Better UX**: Clear error messages guide users to resolve conflicts

### Current Approval Display Feature

**Date**: 2025-08-25  
**Status**: ✅ **COMPLETED**

**Objective**: Display current approval information to help users understand whose turn it is to process approvals and the current status of the approval workflow.

**Changes Made**:

1. **Controller Enhancement**:

    - Added `getCurrentApprovalInfo()` method to retrieve current approval status
    - Enhanced `getApprovalRequests()` DataTable with current approval column
    - Updated `show()` method to pass current approval info to view

2. **Index View Updates**:

    - Added "Current Approval" column to approval requests table
    - Displays approval status, current approver, progress, and workflow type
    - Color-coded badges for different statuses (pending, completed, rejected)

3. **Show View Updates**:
    - Added current approval status card above approval form
    - Shows detailed approval workflow information
    - Displays step progress, approver details, and workflow type

**Key Features**:

-   **Status Display**: Shows current approval status (pending, completed, rejected)
-   **Progress Tracking**: Displays completion progress (X/Y steps completed)
-   **Current Approver**: Shows who is currently responsible for approval
-   **Workflow Type**: Indicates if approval is sequential or parallel
-   **Real-time Updates**: Information updates based on current approval state

**Technical Implementation**:

```php
private function getCurrentApprovalInfo($documentId, $documentType)
// Returns comprehensive approval status information including:
// - Current status (pending/completed/rejected)
// - Current approver name
// - Approval order and progress
// - Workflow type (sequential/parallel)
// - Status message for user guidance
```

**UI Components**:

1. **Index Table Column**:

    - Status badge with color coding
    - Progress indicator (X/Y completed)
    - Current approver information

2. **Show Page Status Card**:
    - Large status badge
    - Detailed progress information
    - Workflow type indicator
    - User-friendly status messages

**Benefits**:

-   **User Awareness**: Users can see whose turn it is to approve
-   **Workflow Transparency**: Clear understanding of approval progress
-   **Better Planning**: Users can anticipate when their approval will be needed
-   **Status Clarity**: Immediate understanding of document approval state
-   **Workflow Guidance**: Clear indication of sequential vs parallel processes

**Status Display Examples**:

1. **Pending Sequential**: "Waiting for Gusti (Step 1)" with progress 0/3
2. **In Progress**: "Waiting for Rachman (Step 2)" with progress 1/3
3. **Completed**: "All approvals completed" with progress 3/3
4. **Rejected**: "Document rejected" with rejection details
