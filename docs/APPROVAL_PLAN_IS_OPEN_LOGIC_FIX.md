# Approval Plan is_open Logic Fix

## Overview

Memperbaiki logic untuk menentukan approval plan yang "aktif" dalam validasi penghapusan approval stage dengan menggunakan kolom `is_open` sebagai kriteria utama.

## Problem

Logic sebelumnya menggunakan `status = 0` (pending) sebagai kriteria untuk memblokir penghapusan approval stage, padahal seharusnya menggunakan `is_open = 1` (open) sesuai dengan logic yang digunakan di `ApprovalPlanController`.

## Root Cause

Inkonsistensi antara `ApprovalStageController` dan `ApprovalPlanController` dalam menentukan approval plan yang "aktif":

-   **ApprovalPlanController**: Menggunakan `is_open = 1` untuk approval plan yang aktif
-   **ApprovalStageController**: Menggunakan `status = 0` untuk approval plan yang aktif

## Solution

Menyelaraskan logic di `ApprovalStageController` dengan `ApprovalPlanController` menggunakan `is_open = 1` sebagai kriteria untuk approval plan yang aktif.

## Status Definitions

### Approval Plan Status

-   **status = 0**: Pending (menunggu approval)
-   **status = 1**: Approved (disetujui)
-   **status = 2**: Rejected (ditolak)

### Approval Plan Open Status

-   **is_open = 1**: Open/Active (aktif - masih dalam proses approval)
-   **is_open = 0**: Closed/Inactive (tutup - sudah selesai atau dibatalkan)

### Logic Rules

-   **Active Plans**: Hanya `is_open = 1` yang dianggap aktif
-   **Inactive Plans**: `is_open = 0` dianggap selesai/tutup
-   **Deletion Block**: Hanya diblokir jika ada approval plan dengan `is_open = 1`

## Changes Made

### 1. Controller Logic Fix

-   **File**: `app/Http/Controllers/ApprovalStageController.php`
-   **Method**: `destroy()`
-   **Changes**:
    -   Changed from `status = 0` to `is_open = 1`
    -   Updated comments to reflect correct logic
    -   Updated variable names and error messages

### 2. Code Before (Incorrect)

```php
// Check if there are any pending approval plans for this approver
// Active approval plans are those with status = 0 (pending)
// Status 1 (approved) and 2 (rejected) are considered completed/inactive
$pendingApprovalPlans = ApprovalPlan::where('approver_id', $approverId)
    ->where('status', 0) // pending status
    ->count();
```

### 3. Code After (Correct)

```php
// Check if there are any active approval plans for this approver
// Active approval plans are those with is_open = 1 (open)
// is_open = 0 means the approval plan is closed/completed
$activeApprovalPlans = ApprovalPlan::where('approver_id', $approverId)
    ->where('is_open', 1) // open status
    ->count();
```

## Verification

### Gusti Permana Case

-   **Total Approval Plans**: 4
-   **is_open = 1 (Open)**: 0 plans
-   **is_open = 0 (Closed)**: 4 plans
-   **Result**: Can be deleted (no open plans)

### Query Results

```sql
SELECT
    ap.is_open,
    COUNT(*) as count,
    CASE
        WHEN ap.is_open = 1 THEN 'Open (Active)'
        WHEN ap.is_open = 0 THEN 'Closed (Inactive)'
        ELSE 'Unknown'
    END as status_label
FROM approval_plans ap
JOIN users u ON ap.approver_id = u.id
WHERE u.name = 'Gusti Permana'
AND ap.document_type = 'recruitment_request'
GROUP BY ap.is_open
```

**Result:**

-   is_open = 0 (Closed): 4 plans
-   is_open = 1 (Open): 0 plans

## Consistency with ApprovalPlanController

### ApprovalPlanController Logic

-   Creates approval plans with `is_open = true` (1)
-   Updates `is_open = 0` when document is approved/rejected
-   Uses `is_open = 1` to find active approval plans

### ApprovalStageController Logic (Fixed)

-   Now uses `is_open = 1` to find active approval plans
-   Consistent with ApprovalPlanController logic
-   Prevents deletion only when necessary

## Benefits

1. **Consistent Logic**: Logic konsisten antara controller
2. **Correct Behavior**: Hanya approval plan yang benar-benar aktif yang memblokir penghapusan
3. **Better UX**: User bisa menghapus approval stage ketika tidak ada approval plan yang aktif
4. **Data Integrity**: Mencegah penghapusan hanya ketika diperlukan
5. **Clear Status**: Pemahaman yang jelas tentang status approval plan

## Testing Scenarios

### 1. Open Approval Plans

-   [ ] Create approval plan with is_open = 1
-   [ ] Try to delete approval stage (should be blocked)
-   [ ] Error message shows correct count

### 2. Closed Approval Plans

-   [ ] Create approval plan with is_open = 0
-   [ ] Try to delete approval stage (should succeed)
-   [ ] No error message shown

### 3. Mixed Status Plans

-   [ ] Create mix of open and closed plans
-   [ ] Try to delete approval stage
-   [ ] Only open plans block deletion

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
-   ApprovalPlanController Integration
