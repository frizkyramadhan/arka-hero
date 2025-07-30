# Approval Preview Update

## Overview

Updated the approval preview functionality to use the logged-in user's department instead of the traveler's department, and display the approver's actual department from their user profile.

## Changes Made

### 1. Frontend Simplification

#### Before:

-   Required both project and traveler selection
-   Used traveler's department for approval flow lookup
-   Complex validation for department determination

#### After:

-   Only requires project selection
-   Uses logged-in user's department automatically
-   Simplified validation and user experience

### 2. Backend Logic Update

#### Controller Changes (`ApprovalStageController::preview`):

**Before:**

```php
// Required department_name parameter
$request->validate([
    'project_id' => 'required|integer',
    'department_name' => 'required|string', // Removed
    'document_type' => 'required|string|in:officialtravel,recruitment_request'
]);

// Used department from request
$department = Department::where('department_name', $request->department_name)->first();
```

**After:**

```php
// Only requires project_id
$request->validate([
    'project_id' => 'required|integer',
    'document_type' => 'required|string|in:officialtravel,recruitment_request'
]);

// Uses logged-in user's department
$user = auth()->user();
$userDepartment = $user->departments->first();
```

### 3. Approver Department Display

#### Before:

-   Displayed department from approval stage configuration
-   Used `$stage->department->department_name`

#### After:

-   Displays actual department from approver's user profile
-   Uses `$stage->approver->departments->first()->department_name`

**Code Change:**

```php
// Before
$approvalStages = ApprovalStage::with(['approver', 'department'])

$approvers = $approvalStages->map(function ($stage) {
    return [
        'id' => $stage->approver->id,
        'name' => $stage->approver->name,
        'department' => $stage->department->department_name // From approval stage
    ];
});

// After
$approvalStages = ApprovalStage::with(['approver.departments'])

$approvers = $approvalStages->map(function ($stage) {
    $approverDepartment = $stage->approver->departments->first();
    return [
        'id' => $stage->approver->id,
        'name' => $stage->approver->name,
        'department' => $approverDepartment ? $approverDepartment->department_name : 'No Department' // From user profile
    ];
});
```

## Benefits

### 1. Simplified User Experience

-   Users only need to select project to see approval flow
-   No dependency on traveler selection
-   Cleaner interface with less complexity

### 2. Consistent Logic

-   Matches the logic used in `ApprovalPlanController::create_approval_plan()`
-   Uses logged-in user's department for approval flow determination
-   Predictable behavior across the application

### 3. Accurate Information

-   Shows approver's actual department from their user profile
-   More accurate representation of organizational structure
-   Better reflects current user assignments

### 4. Reduced Complexity

-   Fewer parameters to validate and process
-   Less JavaScript code to maintain
-   Simpler error handling

## Technical Details

### Database Relationships Used

-   `User` model has `departments()` relationship via `user_department` pivot table
-   `ApprovalStage` model has `approver()` relationship to `User`
-   Eager loading: `approver.departments` for optimal performance

### Error Handling

-   Handles users without assigned departments
-   Graceful fallback to "No Department" display
-   Proper HTTP status codes for different error scenarios

### Performance

-   Uses eager loading to prevent N+1 queries
-   Minimal database queries for approval preview
-   Efficient data retrieval and processing

## Usage

### For Users:

1. Navigate to Official Travel create/edit form
2. Select a project from the dropdown
3. Approval preview will automatically load showing:
    - Approver names
    - Their actual departments
    - Sequential approval flow

### For Developers:

-   Endpoint: `GET /approval/stages/preview`
-   Parameters: `project_id`, `document_type`
-   Response: JSON with approver details including their departments
