# Estimated Next Letter Numbers Feature

## Overview

The Estimated Next Letter Numbers feature provides users with a comprehensive view of the next available letter numbers for each category when creating new letter numbers. This helps users understand the numbering sequence and plan accordingly.

## Features

### 1. Estimated Next Numbers Display
- Shows the next letter number for each active category
- Displays sequence number and year information
- Handles both annual reset and continuous numbering behaviors

### 2. Category Information
- Category code and name display
- Numbering behavior badges (Annual Reset/Continuous)
- Letter count for each category
- Recent letter numbers for context

### 3. Dynamic Preview
- Real-time next number preview when selecting a category
- Shows in both the main form area and sidebar
- Updates automatically based on category selection

### 4. Summary Statistics
- Total categories count
- Categories with existing numbers
- Total sequences across all categories
- Total letters across all categories
- Average sequence number
- Current year display

## Technical Implementation

### Model Methods

#### `getEstimatedNextNumber($categoryId, $year = null)`
- Calculates the next sequence number for a specific category
- Respects numbering behavior (annual_reset vs continuous)
- Returns structured data with next sequence, letter number, year, and category code

#### `getEstimatedNextNumbersForAllCategories($year = null)`
- Gets estimated next numbers for all active categories
- Useful for bulk display and statistics

#### `getLastNumbersForCategory($categoryId, $limit = 5, $year = null)`
- Retrieves the last few numbers for a category
- Provides context about recent numbering

#### `getLetterCountForCategory($categoryId, $year = null)`
- Counts total letters for a category
- Respects numbering behavior for year-based counting

### Controller Updates

The `LetterNumberController::create()` method now:
- Fetches estimated next numbers for all categories
- Retrieves last numbers for context
- Calculates letter counts per category
- Passes all data to the view

### View Enhancements

#### Estimated Next Numbers Section
- Responsive grid layout using AdminLTE info-box components
- Color-coded badges for different information types
- Tooltips for numbering behavior explanation
- Recent numbers display with badge styling

#### Form Integration
- Dynamic next number preview above form fields
- Real-time updates when category changes
- Sidebar information panel with detailed breakdown

## Numbering Behavior

### Annual Reset
- Numbers reset to 1 each year
- Year-based filtering for sequence calculation
- Examples: A0001-2024, A0001-2025

### Continuous
- Numbers continue from previous sequence
- No year-based filtering
- Examples: PKWT0001, PKWT0002, PKWT0003

## UI Components

### Info Boxes
- Category code and name header
- Next letter number (large, prominent display)
- Progress bar for visual appeal
- Sequence and year information
- Numbering behavior badge
- Letter count badge
- Recent numbers display

### Summary Panel
- 6-column responsive layout
- Color-coded statistics
- Icons for visual appeal
- Responsive design for mobile

### Form Preview
- Alert-style information box
- Next number prominently displayed
- Sequence information
- Updates dynamically

## CSS Styling

### Custom Classes
- `.info-box` - Main container styling
- `.info-box-icon` - Icon container with background colors
- `.info-box-content` - Content area with flexbox layout
- `.badge-*` - Badge styling for various information types

### Responsive Design
- Mobile-first approach
- Grid system adapts to screen size
- Info boxes stack on smaller screens
- Maintains readability across devices

## Error Handling

### Model Methods
- Try-catch blocks for database operations
- Null checks for missing data
- Logging for debugging purposes
- Graceful fallbacks for missing categories

### View Rendering
- Null coalescing operators for safe data access
- Conditional rendering for missing information
- Default values for statistics calculations

## Usage Examples

### Basic Display
```php
$estimatedNextNumbers = LetterNumber::getEstimatedNextNumbersForAllCategories();
```

### Category-Specific
```php
$nextNumber = LetterNumber::getEstimatedNextNumber($categoryId, 2025);
```

### Recent Numbers
```php
$lastNumbers = LetterNumber::getLastNumbersForCategory($categoryId, 5);
```

### Letter Counts
```php
$count = LetterNumber::getLetterCountForCategory($categoryId);
```

## Benefits

1. **User Experience**: Users can see what number they'll get before creating
2. **Planning**: Helps users understand the numbering sequence
3. **Transparency**: Clear visibility into the numbering system
4. **Efficiency**: Reduces confusion about next available numbers
5. **Context**: Shows recent numbers for verification

## Future Enhancements

1. **Real-time Updates**: AJAX updates when numbers are created
2. **Number Reservation**: Allow users to reserve specific numbers
3. **Bulk Operations**: Create multiple numbers at once
4. **Export Functionality**: Export numbering statistics
5. **Audit Trail**: Track number creation and usage history

## Dependencies

- AdminLTE CSS framework
- FontAwesome icons
- Bootstrap 4 grid system
- jQuery for dynamic updates
- Select2 for enhanced dropdowns
