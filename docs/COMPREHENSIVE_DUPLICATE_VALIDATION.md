# Comprehensive Duplicate Validation Enhancement

## Overview

Mengupdate validasi duplicate untuk memeriksa semua field yang diperlukan: `approver_id`, `document_type`, `approval_order`, `project_id`, `department_id`, dan `request_reason` di function store dan update.

## Problem

Validasi sebelumnya hanya mengecek kombinasi `approver_id`, `document_type`, dan `approval_order` di level approval stage, tetapi tidak memeriksa kombinasi spesifik di level detail yang meliputi `project_id`, `department_id`, dan `request_reason`.

## Solution

Mengubah validasi untuk mengecek kombinasi lengkap di level `ApprovalStageDetail` yang mencakup semua field yang diperlukan.

## Changes Made

### 1. Store Method Enhancement

-   **File**: `app/Http/Controllers/ApprovalStageController.php`
-   **Method**: `store()`
-   **Changes**:
    -   Changed validation to check at `ApprovalStageDetail` level
    -   Added comprehensive validation for all required fields
    -   Added detailed error message with specific duplicate combinations

### 2. Update Method Enhancement

-   **File**: `app/Http/Controllers/ApprovalStageController.php`
-   **Method**: `update()`
-   **Changes**:
    -   Same comprehensive validation as store method
    -   Excludes current stage from validation (using `where('id', '!=', $id)`)

## Code Implementation

### Store Method Validation

```php
// Check for duplicate combinations at the detail level
$duplicateDetails = [];
foreach ($request->projects as $projectId) {
    foreach ($request->departments as $departmentId) {
        foreach ($requestReasons as $requestReason) {
            // Check if this exact combination already exists
            $existingDetail = ApprovalStageDetail::whereHas('approvalStage', function ($query) use ($request) {
                $query->where('approver_id', $request->approver_id)
                      ->where('document_type', $request->document_type)
                      ->where('approval_order', $request->approval_order);
            })
            ->where('project_id', $projectId)
            ->where('department_id', $departmentId)
            ->where('request_reason', $requestReason)
            ->with(['approvalStage.approver', 'project', 'department'])
            ->first();

            if ($existingDetail) {
                $duplicateDetails[] = [
                    'project' => $existingDetail->project,
                    'department' => $existingDetail->department,
                    'approver' => $existingDetail->approvalStage->approver,
                    'request_reason' => $requestReason
                ];
            }
        }
    }
}
```

### Update Method Validation

```php
// Check if this exact combination already exists (excluding current stage)
$existingDetail = ApprovalStageDetail::whereHas('approvalStage', function ($query) use ($request, $id) {
    $query->where('approver_id', $request->approver_id)
          ->where('document_type', $request->document_type)
          ->where('approval_order', $request->approval_order)
          ->where('id', '!=', $id);
})
->where('project_id', $projectId)
->where('department_id', $departmentId)
->where('request_reason', $requestReason)
->with(['approvalStage.approver', 'project', 'department'])
->first();
```

## Validation Fields

### Complete Validation Set

1. **approver_id** - ID of the approver
2. **document_type** - Type of document (officialtravel/recruitment_request)
3. **approval_order** - Order of approval
4. **project_id** - ID of the project
5. **department_id** - ID of the department
6. **request_reason** - Reason for request (replacement/additional/null)

### Unique Constraint

The validation ensures that the combination of all these fields is unique in the `approval_stage_details` table.

## Error Message Enhancement

### Before (Generic)

```
Approval stage with this approver, document type, and order already exists.
```

### After (Detailed)

```
Duplicate configuration detected! The following combinations already exist:
• Project: PRJ001, Department: Human Resources, Approver: John Doe, Request Reason: Replacement
• Project: PRJ002, Department: Finance, Approver: John Doe, Request Reason: Additional
Please choose different combinations or modify the existing approval stage.
```

## Validation Logic

### 1. Field Combination Check

-   Checks all combinations of `project_id` × `department_id` × `request_reason`
-   For each combination, verifies if it already exists with same `approver_id`, `document_type`, and `approval_order`

### 2. Request Reason Handling

-   **Official Travel**: `request_reason` is always `null`
-   **Recruitment Request**: Uses provided `request_reasons` or `[null]` for backward compatibility

### 3. Update Exclusion

-   Update method excludes current stage from validation using `where('id', '!=', $id)`
-   Prevents false positive when updating existing stage

## Benefits

1. **Complete Validation**: Ensures all required fields are checked
2. **Detailed Error Messages**: Shows exactly which combinations are duplicate
3. **Data Integrity**: Prevents duplicate configurations at detail level
4. **User Experience**: Clear information about what needs to be changed
5. **Backward Compatibility**: Handles existing data without request reason
6. **Performance**: Efficient querying with proper relationships

## Scenarios

### 1. Valid Cases

-   Different projects with same approver
-   Different departments with same project
-   Different request reasons with same project/department
-   Different approvers with same project/department/reason

### 2. Invalid Cases

-   Same project + department + approver + order + reason
-   Overlapping combinations in single request

### 3. Update Scenarios

-   Updating existing stage with same data (allowed)
-   Updating existing stage with conflicting data (blocked)
-   Updating to match another existing stage (blocked)

## Database Impact

### Query Performance

-   Uses `whereHas` for efficient relationship queries
-   Loads related data with `with()` to avoid N+1 queries
-   Indexes on foreign keys improve performance

### Constraint Alignment

-   Validation aligns with database unique constraint
-   Prevents database-level constraint violations
-   Provides user-friendly error messages

## Testing Scenarios

### 1. Create Validation

-   [ ] Create approval stage with unique combinations
-   [ ] Try to create with duplicate combinations (should fail)
-   [ ] Test with different request reasons
-   [ ] Test with different projects/departments

### 2. Update Validation

-   [ ] Update existing stage with same data (should succeed)
-   [ ] Update with conflicting data (should fail)
-   [ ] Update with new unique combinations (should succeed)

### 3. Error Messages

-   [ ] Error messages show correct duplicate combinations
-   [ ] HTML formatting works correctly
-   [ ] Multiple duplicates listed properly

### 4. Edge Cases

-   [ ] Empty request reasons handled correctly
-   [ ] Null request reasons handled correctly
-   [ ] Mixed scenarios handled properly

## Files Modified

1. `app/Http/Controllers/ApprovalStageController.php`
    - `store()` method
    - `update()` method

## Related Features

-   Request Reason Simplification
-   Duplicate Entry Error Handling
-   HTML Rendering Fix
-   Approval Stage Index Enhancement
-   Unique Validation with Request Reason
