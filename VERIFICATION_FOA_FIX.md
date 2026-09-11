# FOA Letter Number Fix - Manual Verification Guide

## Issue Fixed
Bug where reserved FOA letter number for one project was blocked by a used FOA with the same number string in a different project.

**Example**: FOA4964 Reserved for HO could not be used because FOA4964 Used for APS already existed.

## Quick Database Check

```sql
-- Check if duplicate FOA letter numbers exist across projects
SELECT 
    id,
    letter_number,
    project_id,
    status,
    related_document_id,
    related_document_type
FROM letter_numbers 
WHERE letter_number LIKE 'FOA%'
    AND letter_number IN (
        SELECT letter_number 
        FROM letter_numbers 
        WHERE letter_category_id = (SELECT id FROM letter_categories WHERE category_code = 'FOA')
        GROUP BY letter_number, year
        HAVING COUNT(*) > 1
    )
ORDER BY letter_number, project_id;
```

## Test Scenario 1: Cross-Project FOA Usage

### Prerequisites
1. Two projects exist (e.g., 000H/HO and APS)
2. Two letter_number records with same FOA string:
   - FOA4964 for HO, status='reserved'
   - FOA4964 for APS, status='used' (linked to an existing assignment)

### Setup (if needed)
```sql
-- Insert test letter numbers (adjust IDs and dates as needed)
INSERT INTO letter_numbers (letter_number, letter_category_id, project_id, year, status, letter_date, reserved_by)
VALUES 
    ('FOA4964', (SELECT id FROM letter_categories WHERE category_code='FOA'), 
     (SELECT id FROM projects WHERE project_code='000H'), YEAR(CURDATE()), 'reserved', CURDATE(), 1),
    ('FOA4964', (SELECT id FROM letter_categories WHERE category_code='FOA'), 
     (SELECT id FROM projects WHERE project_code='APS'), YEAR(CURDATE()), 'used', CURDATE(), 1);
```

### Test Steps
1. Login to ARKA HERO
2. Navigate to **Vehicle Assignments** → **Create FOA**
3. Click **Letter Number** field
4. Select **FOA4964** for project **000H** (should show as available/reserved)
5. Fill required fields:
   - **Date**: any valid date
   - **Driver**: any active employee
   - **Vehicle**: any active vehicle with project_code 000H
   - **Origin (lokasi awal)**: Select `000H - HO - Balikpapan`
   - **Destination**: Add at least one destination (e.g., `022C - GPK - Melak`)
6. Click **Save Draft**

### Expected Results
✅ **Success**: "FOA4964 created as draft" toast message  
✅ **No Error**: No "sudah digunakan" error  
✅ **Database**: New record in `vehicle_assignments` with `letter_number_id` pointing to HO's FOA4964  
✅ **Letter Status**: HO's FOA4964 still 'reserved' (until Issue)  

### Previous Behavior (Bug)
❌ Error modal: "Save failed: FOA No dari surat ini sudah digunakan: FOA4964"  
❌ Cannot save even though HO's FOA4964 was reserved  

## Test Scenario 2: Same Letter Number Record Reuse

### Test Steps
1. Create a draft FOA using a reserved letter number (e.g., FOA5000)
2. Save draft (letter stays reserved)
3. Try to create **another** FOA using the **same letter_number record**

### Expected Results
❌ **Error**: "FOA No dari surat ini sudah digunakan: FOA5000"  
✅ **Validation**: Same letter_number record cannot be used by two drafts  

This confirms the duplicate check still works correctly for the same record.

## Test Scenario 3: Edit Existing FOA

### Test Steps
1. Open an existing draft FOA that has FOA4965
2. Click **Edit**
3. Change the letter number to a different reserved FOA (e.g., FOA4966)
4. Save

### Expected Results
✅ **Success**: FOA updated with new letter number  
✅ **Old Letter**: FOA4965 released back to 'reserved' status  
✅ **New Letter**: FOA4966 stays 'reserved' (draft mode)  

## Database Verification Queries

```sql
-- Check vehicle assignment was created correctly
SELECT 
    id,
    form_number,
    letter_number_id,
    letter_number,
    project_id,
    status,
    origin_destination
FROM vehicle_assignments 
WHERE form_number = 'FOA4964'
ORDER BY created_at DESC;

-- Check letter number status after save
SELECT 
    id,
    letter_number,
    project_id,
    status,
    related_document_type,
    related_document_id
FROM letter_numbers 
WHERE letter_number = 'FOA4964';

-- Verify project_id relationship
SELECT 
    va.id,
    va.form_number,
    va.letter_number,
    va.project_id as va_project_id,
    ln.project_id as ln_project_id,
    p.project_code,
    p.project_name
FROM vehicle_assignments va
JOIN letter_numbers ln ON va.letter_number_id = ln.id
LEFT JOIN projects p ON ln.project_id = p.id
WHERE va.form_number = 'FOA4964';
```

## Rollback Plan

If the fix causes issues:

```bash
git revert <commit-hash>
git push origin main
```

Or revert the specific change:
```php
// Change back to (in VehicleAssignmentController.php line 858):
->where('form_number', $formNumber)  // Global check (old behavior)
```

## Notes

- The fix changes validation scope from **global form_number** to **specific letter_number_id**
- Letter numbers table design: unique constraint `(letter_number, year, project_id)` allows cross-project duplicates
- Each letter_number record is independent with its own status and linked document
- Draft FOAs keep letters 'reserved'; Issue action marks them 'used'
