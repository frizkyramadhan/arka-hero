# Request Reason Duplicate Fix

## Overview

Fixed the duplicate validation logic for `request_reason` in approval stage creation to allow users to select multiple reasons without false positive duplicate errors.

## Problem

When creating an approval stage for `recruitment_request` with multiple selected reasons (e.g., "replacement" and "additional"), the system showed a duplicate error even when the user should be able to select multiple reasons for the same approver, project, and department combination.

## Root Cause

The previous logic checked each `request_reason` individually against existing data. When a user wanted to create an approval stage with multiple reasons, the system treated each reason as a separate validation case, causing false positives when one of the reasons already existed for the same approver-project-department combination.

## Solution

Modified the duplicate validation logic to:

1. **Group by Stage**: Check for existing approval stages with the same approver + project + department combination
2. **Compare All Reasons**: Only show duplicate error if ALL selected reasons already exist for the same combination
3. **Allow Partial Overlaps**: Allow users to add new reasons to existing combinations

## Changes Made

### 1. Modified Duplicate Validation Logic

-   **File**: `app/Http/Controllers/ApprovalStageController.php`
-   **Method**: `store()`
-   **Changes**:
    -   Changed from individual `request_reason` validation to stage-level validation
    -   Check if approval stage exists for same approver + project + department
    -   Compare all selected reasons against existing reasons
    -   Only show duplicate error if ALL reasons already exist

### 2. Code Before (Problematic)

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

### 3. Code After (Fixed)

```php
// Check for duplicate combinations at the stage level
$duplicateStages = [];
foreach ($request->projects as $projectId) {
    foreach ($request->departments as $departmentId) {
        // Check if an approval stage already exists for this combination
        $existingStage = ApprovalStage::where('approver_id', $request->approver_id)
            ->where('document_type', $request->document_type)
            ->where('approval_order', $request->approval_order)
            ->whereHas('details', function ($query) use ($projectId, $departmentId) {
                $query->where('project_id', $projectId)
                    ->where('department_id', $departmentId);
            })
            ->with(['approver', 'details.project', 'details.department'])
            ->first();

        if ($existingStage) {
            // Get existing request reasons for this stage
            $existingReasons = $existingStage->details
                ->where('project_id', $projectId)
                ->where('department_id', $departmentId)
                ->pluck('request_reason')
                ->filter()
                ->unique()
                ->sort()
                ->toArray();

            // Check if all selected reasons already exist
            $selectedReasons = collect($requestReasons)->sort()->toArray();
            $allReasonsExist = empty(array_diff($selectedReasons, $existingReasons));

            if ($allReasonsExist) {
                $duplicateStages[] = [
                    'project' => $existingStage->details->where('project_id', $projectId)->first()->project,
                    'department' => $existingStage->details->where('department_id', $departmentId)->first()->department,
                    'approver' => $existingStage->approver,
                    'existing_reasons' => $existingReasons,
                    'selected_reasons' => $selectedReasons
                ];
            }
        }
    }
}
```

## Logic Flow

### Before Fix

1. Loop through each project
2. Loop through each department
3. Loop through each request_reason
4. For each combination, check if it already exists
5. If ANY combination exists, mark as duplicate ❌

### After Fix

1. Loop through each project
2. Loop through each department
3. Check if approval stage exists for this combination
4. If exists, get all existing reasons
5. Compare selected reasons with existing reasons
6. Only mark as duplicate if ALL selected reasons already exist ✅

## Example Scenarios

### Scenario 1: New Combination

-   **Existing**: None
-   **Selected**: ["replacement", "additional"]
-   **Result**: ✅ Allowed (no existing stage)

### Scenario 2: Partial Overlap

-   **Existing**: ["additional"]
-   **Selected**: ["replacement", "additional"]
-   **Result**: ✅ Allowed (only "replacement" is new)

### Scenario 3: Complete Overlap

-   **Existing**: ["replacement", "additional"]
-   **Selected**: ["replacement", "additional"]
-   **Result**: ❌ Duplicate error (all reasons already exist)

### Scenario 4: Different Approver

-   **Existing**: User A with ["additional"]
-   **Selected**: User B with ["replacement", "additional"]
-   **Result**: ✅ Allowed (different approver)

## Benefits

1. **Better UX**: Users can select multiple reasons without false duplicate errors
2. **Logical Validation**: Only prevents true duplicates (all reasons already exist)
3. **Flexible Configuration**: Allows adding new reasons to existing combinations
4. **Clear Error Messages**: Shows which combinations are truly duplicated
5. **Maintains Data Integrity**: Still prevents actual duplicate configurations

## Testing Scenarios

### 1. Multiple Reasons - New Combination

-   [ ] Select 2 reasons for new approver-project-department
-   [ ] Should create successfully
-   [ ] Should create 2 detail records

### 2. Multiple Reasons - Partial Overlap

-   [ ] Select 2 reasons where 1 already exists
-   [ ] Should create successfully
-   [ ] Should create 2 detail records (1 new, 1 existing)

### 3. Multiple Reasons - Complete Overlap

-   [ ] Select 2 reasons where both already exist
-   [ ] Should show duplicate error
-   [ ] Should not create new records

### 4. Single Reason - Existing

-   [ ] Select 1 reason that already exists
-   [ ] Should show duplicate error
-   [ ] Should not create new records

## Files Modified

1. `app/Http/Controllers/ApprovalStageController.php`
    - `store()` method

## Related Features

-   Approval Stage Management
-   Request Reason Selection
-   Duplicate Validation
-   User Experience
-   Data Integrity
