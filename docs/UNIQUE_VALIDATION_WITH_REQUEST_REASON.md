# Unique Validation with Request Reason Enhancement

## Overview

Mengupdate validasi unique untuk menambahkan `request_reason` dalam kombinasi unique constraint untuk dokumen `recruitment_request`.

## Problem

Sebelumnya, validasi unique hanya mengecek kombinasi `approver_id`, `document_type`, dan `approval_order`. Untuk dokumen `recruitment_request`, validasi ini tidak mempertimbangkan `request_reason`, sehingga bisa terjadi konflik ketika user mencoba membuat approval stage dengan request reason yang sama.

## Solution

Menambahkan validasi `request_reason` dalam kombinasi unique untuk dokumen `recruitment_request`, sambil tetap mempertahankan backward compatibility.

## Changes Made

### 1. Store Method Enhancement

-   **File**: `app/Http/Controllers/ApprovalStageController.php`
-   **Method**: `store()`
-   **Changes**:
    -   Added logic untuk mengecek overlap `request_reason` untuk `recruitment_request`
    -   Added detailed error message dengan informasi request reason yang overlap
    -   Maintained backward compatibility untuk existing data

### 2. Update Method Enhancement

-   **File**: `app/Http/Controllers/ApprovalStageController.php`
-   **Method**: `update()`
-   **Changes**:
    -   Added same validation logic untuk update method
    -   Consistent error handling dengan store method

## Code Implementation

### Validation Logic

```php
if ($existingStage) {
    // For recruitment_request, also check if request_reasons combination exists
    if ($request->document_type === 'recruitment_request') {
        $requestReasons = $request->request_reasons ?? [];
        if (empty($requestReasons)) {
            $requestReasons = [null]; // For backward compatibility
        }

        // Check if any of the request_reasons already exist for this stage
        $existingReasons = $existingStage->details->pluck('request_reason')->filter()->unique()->sort()->toArray();
        $newReasons = collect($requestReasons)->filter()->unique()->sort()->toArray();

        $hasOverlap = !empty(array_intersect($existingReasons, $newReasons));

        if ($hasOverlap) {
            $overlappingReasons = array_intersect($existingReasons, $newReasons);
            $reasonLabels = collect($overlappingReasons)->map(function($reason) {
                return $reason === 'replacement' ? 'Replacement' : 'Additional';
            })->implode(', ');

            return redirect()->back()
                ->withInput()
                ->withErrors(['duplicate' => "Approval stage with this approver, document type, and order already exists with request reason(s): <strong>{$reasonLabels}</strong>. Please choose different request reasons or modify the existing stage."]);
        }
    } else {
        return redirect()->back()
            ->withInput()
            ->withErrors(['duplicate' => 'Approval stage with this approver, document type, and order already exists.']);
    }
}
```

## Validation Rules

### For Official Travel

-   **Unique Combination**: `approver_id` + `document_type` + `approval_order`
-   **No Request Reason**: Tidak ada validasi request reason

### For Recruitment Request

-   **Unique Combination**: `approver_id` + `document_type` + `approval_order` + `request_reason`
-   **Request Reason Validation**: Mengecek overlap antara existing dan new request reasons
-   **Backward Compatibility**: Jika tidak ada request reason, menggunakan `null`

## Error Messages

### Before (Generic)

```
Approval stage with this approver, document type, and order already exists.
```

### After (Detailed)

```
Approval stage with this approver, document type, and order already exists with request reason(s): Replacement. Please choose different request reasons or modify the existing stage.
```

## Scenarios

### 1. Valid Cases

-   **Different Request Reasons**: User A dengan Replacement, User B dengan Additional
-   **Different Approvers**: Same request reason, different approvers
-   **Different Orders**: Same approver, different approval orders
-   **Official Travel**: No request reason validation

### 2. Invalid Cases

-   **Same Request Reason**: User A dengan Replacement, User A dengan Replacement (same approver, order)
-   **Overlapping Reasons**: Existing stage dengan [Replacement, Additional], new stage dengan [Replacement]

### 3. Backward Compatibility

-   **Existing Data**: Stages tanpa request reason tetap valid
-   **Mixed Scenarios**: Existing stage tanpa request reason, new stage dengan request reason

## Benefits

1. **Prevents Conflicts**: Mencegah duplicate request reason dalam same approval stage
2. **Better User Experience**: Clear error messages dengan specific information
3. **Data Integrity**: Ensures unique combinations untuk recruitment request
4. **Backward Compatibility**: Existing data tetap berfungsi
5. **Flexible Configuration**: Allows different request reasons untuk same approver

## Database Constraint

The validation works with the existing unique constraint:

```sql
UNIQUE KEY `unique_stage_detail` (`approval_stage_id`, `project_id`, `department_id`, `request_reason`)
```

## Testing Scenarios

### 1. Create Validation

-   [ ] Create approval stage dengan Replacement
-   [ ] Try to create same stage dengan Replacement (should fail)
-   [ ] Create same stage dengan Additional (should succeed)
-   [ ] Create same stage dengan [Replacement, Additional] (should fail)

### 2. Update Validation

-   [ ] Update existing stage dengan different request reasons
-   [ ] Try to update dengan overlapping request reasons (should fail)
-   [ ] Update dengan non-overlapping request reasons (should succeed)

### 3. Backward Compatibility

-   [ ] Existing stages tanpa request reason tetap berfungsi
-   [ ] New stages bisa dibuat dengan request reason
-   [ ] Mixed scenarios handled correctly

### 4. Error Messages

-   [ ] Error messages show correct overlapping request reasons
-   [ ] HTML formatting works correctly
-   [ ] Form data preserved after error

## Files Modified

1. `app/Http/Controllers/ApprovalStageController.php`
    - `store()` method
    - `update()` method

## Related Features

-   Request Reason Simplification
-   Duplicate Entry Error Handling
-   HTML Rendering Fix
-   Approval Stage Index Enhancement
