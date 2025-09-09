# Approval Plan Status Logic Fix

## Overview

Memperbaiki logic untuk menentukan approval plan yang "aktif" dalam validasi penghapusan approval stage.

## Problem

Logic sebelumnya menganggap approval plan dengan status = 1 (approved) sebagai "aktif", padahal seharusnya status = 0 (pending) yang dianggap aktif.

## Root Cause

Kesalahan dalam interpretasi status approval plan:

-   **Status 0**: Pending (aktif - masih menunggu approval)
-   **Status 1**: Approved (tidak aktif - sudah disetujui)
-   **Status 2**: Rejected (tidak aktif - sudah ditolak)

## Solution

Memperbaiki logic validasi untuk hanya memeriksa approval plan dengan status = 0 (pending) sebagai yang "aktif".

## Changes Made

### 1. Controller Logic Fix

-   **File**: `app/Http/Controllers/ApprovalStageController.php`
-   **Method**: `destroy()`
-   **Changes**:
    -   Removed duplicate logic for `is_open` check
    -   Updated logic to only check `status = 0` (pending)
    -   Added clear comments explaining status meanings

### 2. Code Before (Incorrect)

```php
// Check if there are any active approval plans for this approver
$activeApprovalPlans = ApprovalPlan::where('approver_id', $approverId)
    ->where('is_open', true)
    ->count();

// Check if there are any pending approval plans for this approver
$pendingApprovalPlans = ApprovalPlan::where('approver_id', $approverId)
    ->where('status', 0) // pending status
    ->count();
```

### 3. Code After (Correct)

```php
// Check if there are any pending approval plans for this approver
// Active approval plans are those with status = 0 (pending)
// Status 1 (approved) and 2 (rejected) are considered completed/inactive
$pendingApprovalPlans = ApprovalPlan::where('approver_id', $approverId)
    ->where('status', 0) // pending status
    ->count();
```

## Status Definitions

### Approval Plan Status

-   **0 (Pending)**: Approval plan is active and waiting for approval
-   **1 (Approved)**: Approval plan is completed and approved
-   **2 (Rejected)**: Approval plan is completed and rejected

### Logic Rules

-   **Active Plans**: Only status = 0 (pending) are considered active
-   **Inactive Plans**: Status = 1 (approved) and 2 (rejected) are considered completed
-   **Deletion Block**: Only blocked if there are pending (status = 0) approval plans

## Verification

### Gusti Permana Case

-   **Total Approval Plans**: 4
-   **Status 1 (Approved)**: 4 plans
-   **Status 0 (Pending)**: 0 plans
-   **Result**: Can be deleted (no pending plans)

### Query Results

```sql
SELECT
    ap.status,
    COUNT(*) as count,
    CASE
        WHEN ap.status = 0 THEN 'Pending'
        WHEN ap.status = 1 THEN 'Approved'
        WHEN ap.status = 2 THEN 'Rejected'
        ELSE 'Unknown'
    END as status_label
FROM approval_plans ap
JOIN users u ON ap.approver_id = u.id
WHERE u.name = 'Gusti Permana'
AND ap.document_type = 'recruitment_request'
GROUP BY ap.status
```

**Result:**

-   Status 1 (Approved): 4 plans
-   Status 0 (Pending): 0 plans

## Benefits

1. **Correct Logic**: Only pending approvals block deletion
2. **Better UX**: Users can delete approval stages when no pending approvals exist
3. **Data Integrity**: Prevents deletion only when necessary
4. **Clear Status**: Clear understanding of approval plan statuses
5. **Consistent Behavior**: Logic matches business requirements

## Testing Scenarios

### 1. Pending Approval Plans

-   [ ] Create approval plan with status = 0
-   [ ] Try to delete approval stage (should be blocked)
-   [ ] Error message shows correct count

### 2. Approved/Rejected Approval Plans

-   [ ] Create approval plan with status = 1 or 2
-   [ ] Try to delete approval stage (should succeed)
-   [ ] No error message shown

### 3. Mixed Status Plans

-   [ ] Create mix of pending and completed plans
-   [ ] Try to delete approval stage
-   [ ] Only pending plans block deletion

### 4. No Approval Plans

-   [ ] No approval plans exist
-   [ ] Try to delete approval stage (should succeed)
-   [ ] No error message shown

## Files Modified

1. `app/Http/Controllers/ApprovalStageController.php`
    - `destroy()` method

## Related Features

-   Approval Stage Management
-   Approval Plan Status Tracking
-   Document Approval Workflow
-   User Permission Management
