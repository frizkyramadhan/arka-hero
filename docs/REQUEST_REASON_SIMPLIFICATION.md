# Request Reason Simplification Implementation

## Overview

Menyederhanakan sistem `request_reason` dalam approval system dari 4 opsi menjadi 2 opsi yang lebih sederhana.

## Changes Made

### 1. Database Schema

-   **File**: `database/migrations/2025_09_08_120614_add_request_reason_to_approval_stage_details.php`
-   **Change**: Menambahkan field `request_reason` ke tabel `approval_stage_details`
-   **Type**: `VARCHAR(50) NULL`

### 2. Model Updates

-   **File**: `app/Models/RecruitmentRequest.php`
-   **Change**:

    ```php
    // Before
    public const REQUEST_REASONS = ['replacement_resign', 'replacement_promotion', 'additional_workplan', 'other'];

    // After
    public const REQUEST_REASONS = ['replacement', 'additional'];
    ```

### 3. Controller Updates

#### ApprovalStageController

-   **File**: `app/Http/Controllers/ApprovalStageController.php`
-   **Changes**:
    -   Updated validation rules untuk `request_reasons.*` dari `in:replacement_resign,replacement_promotion,additional_workplan` menjadi `in:replacement,additional`
    -   Updated `store()` method untuk handle `request_reason` dalam `ApprovalStageDetail`
    -   Updated `edit()` method untuk pass `selectedRequestReasons` ke view
    -   Updated `update()` method untuk handle perubahan `request_reason`

#### ApprovalPlanController

-   **File**: `app/Http/Controllers/ApprovalPlanController.php`
-   **Changes**:
    -   Updated `getConditionalApprovers()` method untuk menggunakan `replacement` dan `additional` instead of specific subtypes
    -   Simplified conditional logic:
        -   `replacement`: HCS Division Manager only
        -   `additional`: Conditional based on project type (HO/BO/APS vs others)

### 4. View Updates

#### Approval Stages

-   **Files**:
    -   `resources/views/approval-stages/create.blade.php`
    -   `resources/views/approval-stages/edit.blade.php`
-   **Changes**:
    -   Added request reason section yang muncul hanya saat Document Type = "Recruitment Request"
    -   UI dengan checkbox untuk "Replacement" dan "Additional"
    -   Select All functionality
    -   JavaScript untuk show/hide section berdasarkan document type

#### Recruitment Requests

-   **Files**:
    -   `resources/views/recruitment/requests/create.blade.php`
    -   `resources/views/recruitment/requests/edit.blade.php`
    -   `resources/views/recruitment/requests/show.blade.php`
    -   `resources/views/recruitment/requests/print.blade.php`
-   **Changes**:
    -   Simplified dropdown options dari 4 opsi menjadi 2 opsi
    -   Updated display labels untuk konsistensi
    -   Removed "other" option dan `other_reason` field

## UI/UX Features

### Approval Stage Creation/Edit

1. **Document Type Selection**: Dropdown untuk pilih "Official Travel" atau "Recruitment Request"
2. **Request Reason Section**:
    - Muncul hanya saat Document Type = "Recruitment Request"
    - Card dengan checkbox untuk "Replacement" dan "Additional"
    - Select All checkbox untuk pilih semua
    - Backward compatibility: jika tidak ada yang dipilih, akan menggunakan `NULL`

### JavaScript Functionality

-   **Show/Hide Logic**: Request reason section muncul/hilang berdasarkan document type
-   **Select All**: Toggle semua checkbox request reason
-   **State Management**: Proper handling untuk indeterminate state

## Approval Flow Logic

### Replacement

-   **Approver**: HCS Division Manager only
-   **Flow**: Single approval stage

### Additional

-   **HO/BO/APS Projects**: HCS Division Manager → HCL Director
-   **Other Projects**: Operational General Manager → HCS Division Manager

## Backward Compatibility

1. **Existing Data**: Data lama dengan `request_reason` yang berbeda akan tetap berfungsi
2. **Official Travel**: Tidak terpengaruh karena tidak menggunakan `request_reason`
3. **Migration**: Field `request_reason` di `approval_stage_details` adalah nullable

## Testing Scenarios

### 1. Approval Stage Creation

-   [ ] Create approval stage untuk Official Travel (tidak ada request reason section)
-   [ ] Create approval stage untuk Recruitment Request tanpa request reason (backward compatibility)
-   [ ] Create approval stage untuk Recruitment Request dengan Replacement
-   [ ] Create approval stage untuk Recruitment Request dengan Additional
-   [ ] Create approval stage untuk Recruitment Request dengan keduanya

### 2. Approval Stage Editing

-   [ ] Edit existing approval stage
-   [ ] Change document type dari Official Travel ke Recruitment Request
-   [ ] Change document type dari Recruitment Request ke Official Travel

### 3. Recruitment Request Creation

-   [ ] Create FPTK dengan Replacement
-   [ ] Create FPTK dengan Additional
-   [ ] Verify approval flow sesuai dengan request reason

### 4. Approval Flow Testing

-   [ ] Test replacement approval (HCS Division Manager only)
-   [ ] Test additional approval untuk HO/BO/APS (HCS → HCL Director)
-   [ ] Test additional approval untuk other projects (Operational GM → HCS Division Manager)

## Files Modified

1. `app/Http/Controllers/ApprovalStageController.php`
2. `app/Http/Controllers/ApprovalPlanController.php`
3. `app/Models/RecruitmentRequest.php`
4. `resources/views/approval-stages/create.blade.php`
5. `resources/views/approval-stages/edit.blade.php`
6. `resources/views/recruitment/requests/create.blade.php`
7. `resources/views/recruitment/requests/edit.blade.php`
8. `resources/views/recruitment/requests/show.blade.php`
9. `resources/views/recruitment/requests/print.blade.php`
10. `database/migrations/2025_09_08_120614_add_request_reason_to_approval_stage_details.php`

## Benefits

1. **Simplified UI**: Hanya 2 opsi yang mudah dipahami
2. **Better UX**: Checkbox interface yang intuitif
3. **Maintainable Code**: Logic yang lebih sederhana
4. **Backward Compatible**: Tidak merusak data existing
5. **Flexible Approval**: Tetap mendukung conditional approval flow
