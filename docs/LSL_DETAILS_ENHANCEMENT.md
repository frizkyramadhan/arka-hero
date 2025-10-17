# Enhanced LSL Details Display

## Overview

Improved Long Service Leave (LSL) Details display with better visual design and comprehensive information for leave request show page.

## Visual Structure

```
┌─────────────────────────────────────────────────────────────────┐
│                    Long Service Leave Details                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐            │
│  │ Leave Days  │  │ Cash Out    │  │ Total Used  │            │
│  │     📅      │  │     💰      │  │     🧮      │            │
│  │     5       │  │     2       │  │     7       │            │
│  │    days     │  │    days     │  │    days     │            │
│  └─────────────┘  └─────────────┘  └─────────────┘            │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │                    LSL Breakdown                            │ │
│  ├─────────────────────────────────────────────────────────────┤ │
│  │ 📅 Leave Taken        Days used as actual leave        5 days │ │
│  │ 💰 Cash Out          Days converted to cash payment    2 days │ │
│  │ 🧮 Total LSL Used     Combined leave and cash out       7 days │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ ℹ️  Cash Out Information                                   │ │
│  │ This request includes 2 day(s) of Long Service Leave       │ │
│  │ cash out. The cash equivalent will be processed according  │ │
│  │ to your employment agreement.                              │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ 💡 LSL Flexibility                                         │ │
│  │ Long Service Leave can be taken as actual leave days or    │ │
│  │ converted to cash payment, providing flexibility in how    │ │
│  │ you utilize your accumulated service benefits.             │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

## Key Features

### 1. LSL Summary Cards

-   **Visual Design**: Three distinct cards with gradient backgrounds and hover effects
-   **Information**: Leave Days, Cash Out Days, and Total Used
-   **Icons**: Calendar check, money bill wave, and calculator icons
-   **Responsive**: Stack vertically on mobile devices

### 2. LSL Breakdown Table

-   **Header**: Dark gradient background with list icon
-   **Rows**: Each LSL component with icon, label, description, and value
-   **Styling**: Hover effects and color-coded icons
-   **Total Row**: Special highlighting with green background

### 3. Information Notes

-   **Cash Out Note**: Yellow background with warning icon (conditional)
-   **General Note**: Blue background with lightbulb icon
-   **Content**: Contextual information about LSL flexibility

## Technical Implementation

### HTML Structure

```html
<div class="lsl-flexible-details">
    <!-- LSL Summary Cards -->
    <div class="lsl-summary-cards">
        <div class="lsl-summary-card leave-card">...</div>
        <div class="lsl-summary-card cashout-card">...</div>
        <div class="lsl-summary-card total-card">...</div>
    </div>

    <!-- LSL Breakdown Table -->
    <div class="lsl-breakdown-table">...</div>

    <!-- LSL Information Notes -->
    <div class="lsl-information-notes">...</div>
</div>
```

### CSS Features

-   **Grid Layout**: 3-column responsive grid for summary cards
-   **Gradients**: Linear gradients for backgrounds and icons
-   **Transitions**: Smooth hover effects and animations
-   **Typography**: Proper hierarchy with different font weights and sizes
-   **Colors**: Consistent color scheme with semantic meaning

## Responsive Design

### Desktop (≥768px)

-   3-column grid layout for summary cards
-   Full-width breakdown table
-   Side-by-side information notes

### Mobile (<768px)

-   Single column layout for summary cards
-   Stacked breakdown table rows
-   Full-width information notes
-   Adjusted padding and font sizes

## Benefits

1. **Better Visual Hierarchy**: Clear separation of information types
2. **Enhanced User Experience**: Interactive elements with hover effects
3. **Comprehensive Information**: Detailed breakdown with descriptions
4. **Mobile Friendly**: Responsive design for all devices
5. **Consistent Design**: Matches overall application design language
6. **Accessibility**: Proper contrast ratios and semantic HTML

## Files Modified

-   `resources/views/leave-requests/show.blade.php`
    -   Updated HTML structure for LSL details section
    -   Added comprehensive CSS styles for enhanced display
    -   Implemented responsive design patterns

## Future Enhancements

-   Add progress bars for LSL usage visualization
-   Include remaining LSL balance information
-   Add tooltips for additional context
-   Implement print-friendly styles
