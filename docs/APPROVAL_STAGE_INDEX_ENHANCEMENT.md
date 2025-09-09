# Approval Stage Index Enhancement

## Overview

Menambahkan keterangan `request_reason` di kolom Document Type untuk Recruitment Request di halaman index approval stage.

## Changes Made

### 1. Controller Updates

-   **File**: `app/Http/Controllers/ApprovalStageController.php`
-   **Method**: `data()` - `document_type` column
-   **Changes**:
    -   Added logic to display request reason information for recruitment_request
    -   Shows specific request reasons (Replacement, Additional) or "All Types" if none selected
    -   Added icon and styling for better visual presentation

### 2. Model Updates

-   **File**: `app/Models/ApprovalStageDetail.php`
-   **Changes**:
    -   Added `request_reason` to `$fillable` array
    -   Enables mass assignment for `request_reason` field

## UI Enhancement Details

### Document Type Column Display

-   **Official Travel**: Shows only "Official Travel" badge
-   **Recruitment Request**:
    -   Shows "Recruitment Request" badge
    -   Below the badge, displays request reason information:
        -   If specific reasons selected: Shows "Replacement", "Additional", or both
        -   If no reasons selected (backward compatibility): Shows "All Types"
    -   Uses small text with tag icon for better visual hierarchy

### Visual Design

-   **Badge**: `badge-warning` class for document type
-   **Request Reason**: Small text with `text-muted` class and tag icon
-   **Icon**: Font Awesome tag icon (`fas fa-tag`)
-   **Layout**: Line break between document type and request reason info

## Code Implementation

### Controller Logic

```php
->addColumn('document_type', function ($stage) {
    $documentName = $stage->document_type === 'officialtravel' ? 'Official Travel' : ucfirst(str_replace('_', ' ', $stage->document_type));
    $html = '<span class="badge badge-warning">' . $documentName . '</span>';

    // Add request reason information for recruitment_request
    if ($stage->document_type === 'recruitment_request') {
        $requestReasons = $stage->details->pluck('request_reason')->filter()->unique()->sort();
        if ($requestReasons->isNotEmpty()) {
            $html .= '<br><small class="text-muted">';
            $reasonLabels = $requestReasons->map(function($reason) {
                return $reason === 'replacement' ? 'Replacement' : 'Additional';
            })->implode(', ');
            $html .= '<i class="fas fa-tag"></i> ' . $reasonLabels;
            $html .= '</small>';
        } else {
            $html .= '<br><small class="text-muted"><i class="fas fa-tag"></i> All Types</small>';
        }
    }

    return $html;
})
```

### Model Update

```php
protected $fillable = [
    'approval_stage_id',
    'project_id',
    'department_id',
    'request_reason'
];
```

## Display Examples

### Official Travel

```
[Official Travel]
```

### Recruitment Request with Specific Reasons

```
[Recruitment Request]
🏷️ Replacement, Additional
```

### Recruitment Request with All Types (Backward Compatibility)

```
[Recruitment Request]
🏷️ All Types
```

## Benefits

1. **Better Information Display**: Users can see which request reasons are configured for each approval stage
2. **Visual Clarity**: Clear distinction between different document types and their configurations
3. **Backward Compatibility**: Shows "All Types" for existing data without request reason configuration
4. **Consistent Styling**: Uses AdminLTE classes for consistent appearance
5. **Icon Usage**: Tag icon provides visual context for request reason information

## Files Modified

1. `app/Http/Controllers/ApprovalStageController.php`
2. `app/Models/ApprovalStageDetail.php`

## Testing Scenarios

### 1. Display Testing

-   [ ] Official Travel approval stages show only document type badge
-   [ ] Recruitment Request with Replacement only shows "Replacement"
-   [ ] Recruitment Request with Additional only shows "Additional"
-   [ ] Recruitment Request with both shows "Replacement, Additional"
-   [ ] Recruitment Request without request reason shows "All Types"

### 2. Data Loading

-   [ ] Request reason data loads correctly from database
-   [ ] Multiple request reasons display properly
-   [ ] Empty request reasons handled gracefully

### 3. Visual Testing

-   [ ] Badge styling consistent with AdminLTE theme
-   [ ] Text hierarchy clear and readable
-   [ ] Icon displays correctly
-   [ ] Responsive design works on different screen sizes
