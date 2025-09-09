# Request Reason Duplicate Issue Analysis

## Problem Description

When creating an approval stage for recruitment_request with 2 selected reasons (e.g., "replacement" and "additional"), the system shows a duplicate error even though the user should be able to select multiple reasons for the same approver, project, and department combination.

## Current Behavior

1. User selects approver: Rachman Yulikiswanto
2. User selects document_type: recruitment_request
3. User selects approval_order: 1
4. User selects project: 000H
5. User selects department: Accounting
6. User selects request_reasons: ["replacement", "additional"]
7. System checks for duplicates:
    - For "replacement": No existing combination found ✓
    - For "additional": Found existing combination (stage_id 607) ❌
8. System shows duplicate error for "additional"

## Existing Data

```
Stage ID: 607
Approver: Rachman Yulikiswanto (ID: 4)
Document Type: recruitment_request
Approval Order: 1
Project: 000H
Department: Accounting
Request Reason: additional
```

## Root Cause

The current duplicate validation logic checks each request_reason individually against existing data. When a user wants to create an approval stage with multiple reasons, the system treats each reason as a separate validation case, causing false positives when one of the reasons already exists for the same approver-project-department combination.

## Current Logic Flow

1. Loop through each project
2. Loop through each department
3. Loop through each request_reason
4. For each combination, check if it already exists
5. If any combination exists, mark as duplicate

## Desired Behavior

The system should allow users to:

1. Create approval stages with multiple request_reasons for the same approver-project-department combination
2. Only show duplicate errors when the EXACT same combination (approver + project + department + reason) already exists
3. Allow adding new reasons to existing approval stages

## Solution Options

### Option 1: Remove Duplicate Check for Request Reasons

-   Simply remove the request_reason from duplicate validation
-   Allow multiple reasons for the same combination
-   Risk: May create too many duplicate stages

### Option 2: Group by Stage Instead of Individual Details

-   Check for existing approval stages with the same approver + project + department
-   If found, allow adding new reasons to existing stage
-   If not found, create new stage

### Option 3: Modify Duplicate Logic

-   Only show duplicate error if ALL selected reasons already exist for the same combination
-   Allow partial overlaps (some reasons exist, some don't)

### Option 4: Update Existing Stage

-   If partial overlap exists, update the existing stage to include new reasons
-   Only create new stage if no existing stage matches

## Recommended Solution

**Option 2: Group by Stage Instead of Individual Details**

This approach would:

1. Check if an approval stage already exists for the same approver + project + department combination
2. If exists, update the stage to include new request_reasons
3. If not exists, create new stage with all selected request_reasons
4. Only show duplicate error if ALL selected reasons already exist in the same stage

## Implementation Considerations

-   Need to modify the store() method logic
-   Need to handle update vs create scenarios
-   Need to ensure data integrity
-   Need to provide clear user feedback
