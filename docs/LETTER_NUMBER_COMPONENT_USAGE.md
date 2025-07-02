# Smart Letter Number Selector Component Usage Guide

## Overview

The `smart-letter-number-selector` component is a reusable Blade component that allows easy selection of existing reserved letter numbers across different features in the HCSSIS application.

## Features

-   **Self-contained**: JavaScript and CSS included, no external dependencies
-   **Flexible**: Works with any letter category (A, B, PKWT, PAR, etc.)
-   **Real-time loading**: Fetches available letter numbers via API
-   **User-friendly**: Select2 dropdown with search functionality
-   **Status feedback**: Shows loading, success, and error states
-   **Auto-refresh**: Manual refresh button to reload available numbers

## Basic Usage

### Include the Component

```blade
@include('components.smart-letter-number-selector', [
    'categoryCode' => 'B',           // Required: Letter category
    'fieldName' => 'letter_number_id', // Optional: Field name (default: letter_number_id)
    'required' => true,              // Optional: Required field (default: false)
    'placeholder' => 'Select Number' // Optional: Placeholder text
])
```

## Examples for Different Features

### 1. Official Travel (Category B)

```blade
{{-- Official Travel Form --}}
<form action="{{ route('officialtravels.store') }}" method="POST">
    @csrf

    <!-- Letter Number Selection -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Letter Number</h3>
        </div>
        <div class="card-body">
            @include('components.smart-letter-number-selector', [
                'categoryCode' => 'B',
                'required' => true,
                'placeholder' => 'Select Letter Number for Travel'
            ])
        </div>
    </div>

    <!-- Other form fields... -->

    <button type="submit" class="btn btn-primary">Create Travel</button>
</form>
```

### 2. Internal Letter (Category A)

```blade
{{-- Internal Letter Form --}}
<form action="{{ route('internal-letters.store') }}" method="POST">
    @csrf

    @include('components.smart-letter-number-selector', [
        'categoryCode' => 'A',
        'fieldName' => 'internal_letter_number_id',
        'required' => true,
        'placeholder' => 'Select Internal Letter Number'
    ])

    <!-- Other form fields... -->

    <button type="submit" class="btn btn-primary">Create Letter</button>
</form>
```

### 3. PKWT Contract (Category PKWT)

```blade
{{-- PKWT Contract Form --}}
<form action="{{ route('contracts.store') }}" method="POST">
    @csrf

    <div class="row">
        <div class="col-md-6">
            @include('components.smart-letter-number-selector', [
                'categoryCode' => 'PKWT',
                'fieldName' => 'contract_number_id',
                'required' => true,
                'placeholder' => 'Select PKWT Number'
            ])
        </div>
        <div class="col-md-6">
            <!-- Other fields... -->
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Create Contract</button>
</form>
```

### 4. PAR Document (Category PAR)

```blade
{{-- PAR Form --}}
<form action="{{ route('pars.store') }}" method="POST">
    @csrf

    @include('components.smart-letter-number-selector', [
        'categoryCode' => 'PAR',
        'fieldName' => 'par_letter_id',
        'required' => false,  // Optional for PAR
        'placeholder' => 'Select PAR Letter Number (Optional)'
    ])

    <!-- Other PAR fields... -->

    <button type="submit" class="btn btn-primary">Create PAR</button>
</form>
```

### 5. Multiple Letter Numbers in One Form

```blade
{{-- Form with multiple letter number selectors --}}
<form action="{{ route('complex-document.store') }}" method="POST">
    @csrf

    <div class="row">
        <div class="col-md-6">
            <h5>Main Letter</h5>
            @include('components.smart-letter-number-selector', [
                'categoryCode' => 'B',
                'fieldName' => 'main_letter_id',
                'required' => true,
                'placeholder' => 'Select Main Letter'
            ])
        </div>
        <div class="col-md-6">
            <h5>Reference Letter</h5>
            @include('components.smart-letter-number-selector', [
                'categoryCode' => 'A',
                'fieldName' => 'reference_letter_id',
                'required' => false,
                'placeholder' => 'Select Reference Letter'
            ])
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Create Document</button>
</form>
```

## Controller Integration

### Store Method Example

```php
public function store(Request $request)
{
    $request->validate([
        'letter_number_id' => 'nullable|exists:letter_numbers,id',
        // other validation rules...
    ]);

    DB::beginTransaction();

    try {
        // Create main document
        $document = YourModel::create([
            'letter_number_id' => $request->letter_number_id,
            // other fields...
        ]);

        // Mark letter number as used if selected
        if ($request->letter_number_id) {
            $letterNumber = LetterNumber::find($request->letter_number_id);
            $letterNumber->markAsUsed('your_model_type', $document->id);
        }

        DB::commit();

        return redirect()->route('your-route.index')
            ->with('toast_success', 'Document created successfully!');

    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()
            ->with('toast_error', 'Failed to create document: ' . $e->getMessage())
            ->withInput();
    }
}
```

## Available Letter Categories

| Category | Description               | Example Use Case                |
| -------- | ------------------------- | ------------------------------- |
| `A`      | Internal Letters          | Memo, Announcements             |
| `B`      | External Letters          | Official Travel, Client Letters |
| `PKWT`   | Work Contracts            | Employee Contracts              |
| `PAR`    | Personnel Action Requests | Promotions, Transfers           |
| `CRTE`   | Certificates              | Training Certificates           |
| `SKPK`   | Work Permits              | Special Permissions             |
| `FR`     | Travel Requests           | Flight/Transport Tickets        |

## Technical Details

### API Endpoint

The component uses the following API endpoint:

```
GET /api/letter-numbers/available/{categoryCode}
```

This endpoint returns only letter numbers with `status = 'reserved'`.

### Response Format

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "letter_number": "B/001/HR/XII/2024",
            "subject_name": "Surat Perjalanan Dinas",
            "letter_date": "25/12/2024",
            "employee_name": "John Doe",
            "remarks": "Important travel for client meeting"
        }
    ],
    "count": 1,
    "category_code": "B"
}
```

### Display Format

The component displays letter numbers in the following format:

```
B/001/HR/XII/2024 - Surat Perjalanan Dinas (25/12/2024) - Important travel for client...
```

**Format Structure:**

-   **Letter Number**: The actual letter number
-   **Subject**: Letter subject name (or "No Subject")
-   **Date**: Letter date (or "No Date")
-   **Remarks**: Letter remarks (limited to 40 characters, truncated with "..." if longer)

**Tooltip**: Hover over any option to see the full remarks text.

### JavaScript Events

The component is fully self-contained and handles:

-   Select2 initialization
-   AJAX loading of available numbers
-   Error handling and user feedback
-   Refresh functionality

### CSS Classes

-   `.letter-number-selector` - Main container
-   `.letter-number-select` - Select element
-   `.refresh-btn` - Refresh button
-   `.status-alert` - Status message container

## Troubleshooting

### Common Issues

1. **No letter numbers showing**

    - Check if there are reserved letter numbers for the category
    - Verify API endpoint is accessible
    - Check browser console for JavaScript errors

2. **Select2 not working**

    - Ensure Select2 CSS/JS is loaded in the parent view
    - Check for jQuery conflicts

3. **AJAX errors**
    - Verify the API route exists in `routes/api.php`
    - Check application URL configuration
    - Ensure proper authentication if required

### Debug Mode

Add this to check component status:

```blade
@include('components.smart-letter-number-selector', [
    'categoryCode' => 'B',
    'required' => true
])

<script>
console.log('Letter Number Component loaded for category: B');
</script>
```

## Best Practices

1. **Always specify categoryCode**: Required parameter for proper functionality
2. **Use descriptive fieldNames**: When using multiple selectors in one form
3. **Handle validation**: Include proper backend validation
4. **Mark as used**: Always mark letter numbers as used after document creation
5. **Error handling**: Implement proper error handling in controllers
6. **User feedback**: Use SweetAlert for consistent user notifications

## Integration with SweetAlert

Remember to use SweetAlert for user notifications as per project standards:

```php
// Success message
return redirect()->back()
    ->with('toast_success', 'Document created with Letter Number: ' . $letterNumber);

// Error message
return redirect()->back()
    ->with('toast_error', 'Failed to create document');
```
