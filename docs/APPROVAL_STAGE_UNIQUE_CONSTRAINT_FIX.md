# Approval Stage Unique Constraint Fix

## Overview

Fixed the duplicate validation logic to properly handle the unique constraint on `approval_stages` table that prevents duplicate combinations of `document_type`, `approver_id`, and `approval_order`.

## Problem

When creating an approval stage, the system was throwing a `SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'recruitment_request-4-1' for key 'approval_stages.unique_approval_stage'` error because there was already an approval stage with the same combination of:

-   `document_type` = 'recruitment_request'
-   `approver_id` = '4'
-   `approval_order` = '1'

## Root Cause

The previous duplicate validation logic only checked at the `approval_stage_details` level, but didn't account for the unique constraint at the `approval_stages` level. The `approval_stages` table has a unique constraint `unique_approval_stage` on the combination of:

-   `document_type`
-   `approver_id`
-   `approval_order`

## Database Constraint

```sql
UNIQUE KEY `unique_approval_stage` (`document_type`, `approver_id`, `approval_order`)
```

This means:

-   One approver can only have ONE approval stage per document type per approval order
-   Multiple project-department combinations can be handled through `approval_stage_details`
-   Multiple request reasons can be handled through `approval_stage_details`

## Solution

Modified the duplicate validation logic to:

1. **Check Stage Level First**: Check if an approval stage already exists for the same `approver_id`, `document_type`, and `approval_order`
2. **Compare Combinations**: If stage exists, compare all selected project-department combinations with existing ones
3. **Compare Reasons**: For each combination, check if all selected request reasons already exist
4. **Show Detailed Error**: Display which combinations and reasons already exist

## Changes Made

### 1. Modified Duplicate Validation Logic

-   **File**: `app/Http/Controllers/ApprovalStageController.php`
-   **Method**: `store()`
-   **Changes**:
    -   Check for existing approval stage first (unique constraint level)
    -   Compare all selected combinations with existing ones
    -   Only show duplicate error if ALL combinations and reasons already exist
    -   Provide detailed error message showing what already exists

### 2. Code Before (Problematic)

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
        // ... rest of logic
    }
}
```

### 3. Code After (Fixed)

```php
// Check for duplicate approval stage (unique constraint: document_type + approver_id + approval_order)
$existingStage = ApprovalStage::where('approver_id', $request->approver_id)
    ->where('document_type', $request->document_type)
    ->where('approval_order', $request->approval_order)
    ->with(['approver', 'details.project', 'details.department'])
    ->first();

if ($existingStage) {
    // Get all existing project-department combinations for this stage
    $existingCombinations = $existingStage->details->map(function ($detail) {
        return [
            'project_id' => $detail->project_id,
            'department_id' => $detail->department_id,
            'project_code' => $detail->project->project_code,
            'department_name' => $detail->department->department_name,
            'request_reason' => $detail->request_reason
        ];
    })->groupBy(function ($item) {
        return $item['project_id'] . '-' . $item['department_id'];
    });

    // Check if all selected combinations already exist with all selected reasons
    $allCombinationsExist = true;
    $duplicateDetails = [];

    foreach ($selectedCombinations as $key => $selected) {
        if (!isset($existingCombinations[$key])) {
            $allCombinationsExist = false;
            break;
        }

        $existing = $existingCombinations[$key];
        $existingReasons = $existing->pluck('request_reason')->filter()->unique()->sort()->toArray();
        $selectedReasons = collect($selected['request_reasons'])->sort()->toArray();

        if (empty(array_diff($selectedReasons, $existingReasons))) {
            $duplicateDetails[] = [
                'project_code' => $existing->first()['project_code'],
                'department_name' => $existing->first()['department_name'],
                'existing_reasons' => $existingReasons,
                'selected_reasons' => $selectedReasons
            ];
        } else {
            $allCombinationsExist = false;
            break;
        }
    }

    if ($allCombinationsExist) {
        $duplicateStages = [[
            'approver' => $existingStage->approver,
            'details' => $duplicateDetails
        ]];
    } else {
        $duplicateStages = [];
    }
} else {
    $duplicateStages = [];
}
```

## Logic Flow

### Before Fix

1. Loop through each project-department combination
2. Check if approval stage exists for each combination
3. Check if reasons exist for each combination
4. Show duplicate error if any combination exists ❌

### After Fix

1. Check if approval stage exists for approver + document_type + approval_order
2. If exists, get all existing combinations and reasons
3. Compare ALL selected combinations with existing ones
4. Only show duplicate error if ALL combinations and reasons already exist ✅

## Example Scenarios

### Scenario 1: New Approver

-   **Existing**: None
-   **Selected**: User A, recruitment_request, order 1, project 000H, department Accounting, reasons ["replacement", "additional"]
-   **Result**: ✅ Allowed (no existing stage)

### Scenario 2: Different Approval Order

-   **Existing**: User A, recruitment_request, order 1
-   **Selected**: User A, recruitment_request, order 2
-   **Result**: ✅ Allowed (different approval order)

### Scenario 3: Different Document Type

-   **Existing**: User A, recruitment_request, order 1
-   **Selected**: User A, officialtravel, order 1
-   **Result**: ✅ Allowed (different document type)

### Scenario 4: Same Combination

-   **Existing**: User A, recruitment_request, order 1, project 000H, department Accounting, reasons ["additional"]
-   **Selected**: User A, recruitment_request, order 1, project 000H, department Accounting, reasons ["replacement", "additional"]
-   **Result**: ❌ Duplicate error (same approver + document_type + order, but different reasons)

### Scenario 5: Complete Duplicate

-   **Existing**: User A, recruitment_request, order 1, project 000H, department Accounting, reasons ["replacement", "additional"]
-   **Selected**: User A, recruitment_request, order 1, project 000H, department Accounting, reasons ["replacement", "additional"]
-   **Result**: ❌ Duplicate error (exact same combination)

## Benefits

1. **Prevents Database Errors**: Catches unique constraint violations before they occur
2. **Better User Experience**: Shows clear error messages instead of database errors
3. **Logical Validation**: Only prevents true duplicates at the stage level
4. **Detailed Feedback**: Shows exactly what combinations already exist
5. **Maintains Data Integrity**: Respects database constraints

## Error Message Example

```
Duplicate configuration detected! An approval stage already exists for this approver and approval order with the same project-department combinations:

• Approver: Rachman Yulikiswanto
  Existing combinations:
  • Project: 000H, Department: Accounting, Request Reasons: Additional

Please choose a different approver, approval order, or modify the existing approval stage.
```

## Files Modified

1. `app/Http/Controllers/ApprovalStageController.php`
    - `store()` method

## Related Features

-   Approval Stage Management
-   Unique Constraint Validation
-   Database Integrity
-   User Experience
-   Error Handling
