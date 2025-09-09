# Duplicate Entry Error Handling Enhancement

## Overview

Menambahkan handling untuk error duplicate entry yang lebih friendly dan informatif, memberikan informasi detail tentang data yang duplicate.

## Problem

Sebelumnya, ketika terjadi error `SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '603-1-1' for key 'approval_stage_details.unique_stage_detail'`, pesan error yang ditampilkan tidak user-friendly dan sulit dipahami.

## Solution

Menambahkan try-catch block dengan error handling yang lebih baik untuk memberikan informasi yang jelas tentang data yang duplicate.

## Changes Made

### 1. Controller Updates

-   **File**: `app/Http/Controllers/ApprovalStageController.php`
-   **Methods**: `store()` dan `update()`
-   **Changes**:
    -   Added try-catch block untuk menangani `QueryException`
    -   Added logic untuk parse duplicate key information
    -   Added database query untuk mendapatkan detail data yang duplicate
    -   Added friendly error message dengan informasi lengkap

### 2. Error Message Enhancement

-   **Before**: Generic database error message
-   **After**: Detailed, user-friendly message dengan informasi:
    -   Project name dan code
    -   Department name
    -   Approver name
    -   Request reason (jika applicable)

## Code Implementation

### Error Handling Logic

```php
try {
    // Database operations
} catch (\Illuminate\Database\QueryException $e) {
    // Handle duplicate entry error
    if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'unique_stage_detail')) {
        // Extract the duplicate key information
        preg_match("/Duplicate entry '([^']+)' for key/", $e->getMessage(), $matches);
        $duplicateKey = $matches[1] ?? 'unknown';

        // Parse the duplicate key to get meaningful information
        $keyParts = explode('-', $duplicateKey);
        $duplicateStageId = $keyParts[0] ?? 'unknown';
        $duplicateProjectId = $keyParts[1] ?? 'unknown';
        $duplicateDepartmentId = $keyParts[2] ?? 'unknown';

        // Get the existing stage details for better error message
        $existingDetail = ApprovalStageDetail::with(['approvalStage.approver', 'project', 'department'])
            ->where('approval_stage_id', $duplicateStageId)
            ->where('project_id', $duplicateProjectId)
            ->where('department_id', $duplicateDepartmentId)
            ->first();

        if ($existingDetail) {
            $errorMessage = "Duplicate configuration detected! ";
            $errorMessage .= "The combination of ";
            $errorMessage .= "<strong>Project: {$existingDetail->project->project_code}</strong>, ";
            $errorMessage .= "<strong>Department: {$existingDetail->department->department_name}</strong> ";
            $errorMessage .= "already exists for ";
            $errorMessage .= "<strong>{$existingDetail->approvalStage->approver->name}</strong> ";
            $errorMessage .= "in the approval stage configuration.";

            if ($request->document_type === 'recruitment_request' && $existingDetail->request_reason) {
                $reasonLabel = $existingDetail->request_reason === 'replacement' ? 'Replacement' : 'Additional';
                $errorMessage .= " (Request Reason: <strong>{$reasonLabel}</strong>)";
            }
        } else {
            $errorMessage = "Duplicate configuration detected! This combination of project, department, and approver already exists in the approval stage configuration.";
        }

        return redirect()->back()
            ->withInput()
            ->withErrors(['duplicate' => $errorMessage]);
    }

    // Re-throw other database errors
    throw $e;
}
```

## Error Message Examples

### Before (Generic Error)

```
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '603-1-1' for key 'approval_stage_details.unique_stage_detail'
```

### After (Friendly Error)

```
Duplicate configuration detected! The combination of Project: PRJ001, Department: Human Resources already exists for John Doe in the approval stage configuration. (Request Reason: Replacement)
```

## Features

### 1. Detailed Information

-   **Project**: Shows project code instead of ID
-   **Department**: Shows department name instead of ID
-   **Approver**: Shows approver name instead of ID
-   **Request Reason**: Shows human-readable reason (Replacement/Additional)

### 2. HTML Formatting

-   Uses `<strong>` tags untuk emphasis
-   Proper formatting untuk readability
-   Consistent styling dengan AdminLTE theme

### 3. Fallback Handling

-   Jika data tidak ditemukan, shows generic message
-   Handles parsing errors gracefully
-   Re-throws non-duplicate database errors

### 4. Input Preservation

-   Uses `withInput()` untuk preserve form data
-   User tidak perlu mengisi ulang form

## Error Display in UI

### Create Form

-   Error ditampilkan di atas form dengan alert-danger styling
-   Icon exclamation triangle untuk visual attention
-   HTML content di-render dengan `{!! nl2br(e($message)) !!}`

### Edit Form

-   Same error display mechanism
-   Preserves existing form data

## Benefits

1. **User-Friendly**: Clear, understandable error messages
2. **Informative**: Shows exactly which data is duplicate
3. **Actionable**: User knows what to change to fix the issue
4. **Consistent**: Same error handling for both create and update
5. **Robust**: Handles edge cases and parsing errors
6. **Preserves Data**: Form input is preserved when error occurs

## Testing Scenarios

### 1. Duplicate Detection

-   [ ] Create approval stage with existing project-department combination
-   [ ] Update approval stage with existing project-department combination
-   [ ] Test with different request reasons

### 2. Error Message Quality

-   [ ] Error message shows correct project name
-   [ ] Error message shows correct department name
-   [ ] Error message shows correct approver name
-   [ ] Error message shows request reason (if applicable)

### 3. Form Behavior

-   [ ] Form data is preserved after error
-   [ ] User can correct and resubmit
-   [ ] Error styling is consistent

### 4. Edge Cases

-   [ ] Handles missing data gracefully
-   [ ] Handles parsing errors
-   [ ] Re-throws non-duplicate database errors

## Files Modified

1. `app/Http/Controllers/ApprovalStageController.php`
    - `store()` method
    - `update()` method

## Database Constraint

The error occurs due to unique constraint on `approval_stage_details` table:

```sql
UNIQUE KEY `unique_stage_detail` (`approval_stage_id`, `project_id`, `department_id`, `request_reason`)
```

This constraint ensures that the same combination of approval stage, project, department, and request reason cannot be duplicated.
