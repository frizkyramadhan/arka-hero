# Roster Management Implementation - FB Cycle System

## Overview

Implementasi baru sistem Roster Management menggunakan konsep **FB Cycle (Fly Back Cycle)** untuk mengelola rotasi kerja dan cuti periodik karyawan di project dengan tipe roster.

## Architecture

### Database Structure

#### Table: `rosters`

```sql
- id (bigint, PK)
- employee_id (char 36, FK to employees)
- administration_id (bigint, FK to administrations)
- created_at, updated_at
- UNIQUE(employee_id, administration_id)
```

#### Table: `roster_details`

```sql
- id (bigint, PK)
- roster_id (bigint, FK to rosters)
- cycle_no (int) - Nomor cycle rotasi
- work_start (date) - Tanggal mulai kerja
- work_end (date) - Tanggal selesai kerja
- adjusted_days (int) - Penyesuaian hari kerja (balancing)
- leave_start (date) - Tanggal mulai cuti
- leave_end (date) - Tanggal selesai cuti
- status (enum) - Status cycle: scheduled, active, on_leave, completed
- remarks (text) - Catatan
- created_at, updated_at
```

### FB Cycle Ratio Formula

```php
$baseLeaveRatio = 15 / 7; // 2.142857143
$offWeeks = $level->off_days / 7;
$workWeeks = $level->work_days / 7;
$totalWeeks = $offWeeks + $workWeeks;

$fbCycleRatio = ($baseLeaveRatio * $totalWeeks) / ($workWeeks * $totalWeeks);
```

**Contoh Perhitungan:**

-   Pattern 2w/9w (2 weeks cuti / 9 weeks kerja)
-   Work days: 63 days (9 weeks)
-   Off days: 14 days (2 weeks)
-   Total weeks: 11 weeks
-   FB Cycle Ratio: 0.24
-   Leave Entitlement: 63 × 0.24 = 15.12 days

## Features

### 1. Roster Management (Index)

**URL:** `/rosters`

**Features:**

-   Filter by project (roster-type projects only)
-   Search by NIK or employee name
-   View employees with their roster status
-   Create roster for employees with roster-configured levels
-   Delete roster (cascade delete all cycles)
-   Pagination support

**Display Information:**

-   NIK, Full Name, Position, Level
-   Roster Cycle (e.g., 8w/2w, 6w/2w)
-   Total cycles
-   Current status (Active, On Leave, No Active Cycle)

### 2. Roster Details (Show)

**URL:** `/rosters/{roster_id}`

**Employee Information:**

-   NIK, Name, Position, Department, Project
-   Level, Roster Cycle, FB Cycle Ratio

**Statistics:**

-   Total Cycles
-   Total Accumulated Leave (calculated from all cycles)
-   Total Leave Taken
-   Leave Balance

**Cycle Management:**

-   View all cycles with details
-   Add cycle manually
-   Edit cycle
-   Apply balancing (adjust work days)
-   Delete cycle

### 3. Cycle Operations

#### Add Cycle Manually

Automatically calculates:

-   Work period (start & end date)
-   Leave period (start & end date)
-   Leave entitlement based on FB Cycle ratio
-   Next cycle number

#### Add Cycle - Manual

User can manually input:

-   Work start & end dates
-   Adjusted days (balancing)
-   Leave start & end dates
-   Remarks

#### Apply Balancing

**Endpoint:** `POST /rosters/cycles/{cycle_id}/balancing`

Adjust work days for specific cycle:

-   Positive value: add days (e.g., +5 for extension)
-   Negative value: reduce days (e.g., -3 for reduction)
-   Reason required
-   Automatically recalculates leave entitlement

### 4. Business Logic

#### Leave Entitlement Calculation

```php
$actualWorkDays = $detail->getActualWorkDays(); // Base + adjusted
$fbCycleRatio = $roster->getFbCycleRatio();
$leaveEntitlement = $actualWorkDays * $fbCycleRatio;
```

#### Leave Balance

```php
$totalAccumulated = sum(all cycles' leave entitlement)
$totalTaken = sum(all cycles' leave days)
$balance = $totalAccumulated - $totalTaken
```

#### Cycle Status (Auto-Updated)

Status field di `roster_details` akan **otomatis ter-update** berdasarkan tanggal:

-   **scheduled:** Future cycle (work_start > now)
-   **active:** Currently in work period (now between work_start and work_end)
-   **on_leave:** Currently in leave period (now between leave_start and leave_end)
-   **completed:** Leave ended (now > leave_end)

**Auto-Update Mechanism:**

```php
public function updateStatus()
{
    $now = now();

    if ($this->leave_end && $now->gt($this->leave_end)) {
        $this->status = 'completed';
    } elseif ($this->leave_start && $this->leave_end && $now->between($this->leave_start, $this->leave_end)) {
        $this->status = 'on_leave';
    } elseif ($now->between($this->work_start, $this->work_end)) {
        $this->status = 'active';
    } else {
        $this->status = 'scheduled';
    }

    return $this->save();
}
```

**Trigger Points:**

-   Status automatically updated when cycle is created
-   Status automatically updated when cycle is modified
-   Can be manually updated by calling `$rosterDetail->updateStatus()`

## Routes

```php
// Main routes
GET    /rosters                          - Index page
GET    /rosters/{roster}                 - Show details
POST   /rosters                          - Create roster
DELETE /rosters/{roster}                 - Delete roster

// Cycle management
POST   /rosters/{roster}/cycles          - Add cycle
PUT    /rosters/cycles/{cycle}           - Update cycle
DELETE /rosters/cycles/{cycle}           - Delete cycle

// Helper endpoints
GET    /rosters/{roster}/statistics          - Get statistics
POST   /rosters/cycles/{cycle}/balancing     - Apply balancing
```

## Testing Steps

### Manual Testing Checklist

1. **Navigate to Roster Management**

    - URL: `http://localhost/arka-hero/rosters`
    - Login as admin if needed

2. **Select Roster Project**

    - Choose project with leave_type='roster' (e.g., 017C, 022C)
    - Verify employees displayed

3. **Create Roster**

    - Click "Create" button for employee with roster-configured level
    - Verify roster created successfully

4. **View Roster Details**

    - Click "View" button for employee
    - Verify employee info and statistics displayed

5. **Add Cycle Manually**

    - Click "Add Cycle Manually"
    - Fill in work_start and adjusted_days (if needed)
    - Verify auto-calculated dates
    - Save cycle

6. **Add Cycle - Manual**

    - Click "Add Cycle Manually"
    - Input dates manually
    - Save cycle

7. **Apply Balancing**

    - Click "Balancing" button for specific cycle
    - Input adjusted days (e.g., +5 or -3)
    - Input reason
    - Verify adjusted days updated

8. **Delete Cycle**

    - Click "Delete" button for specific cycle
    - Confirm deletion
    - Verify cycle removed

9. **Delete Roster**
    - Go back to index
    - Click "Delete" button for roster
    - Confirm deletion
    - Verify roster and all cycles deleted

## Models & Relationships

### Roster Model

```php
// Relationships
- belongsTo: Employee, Administration
- hasMany: RosterDetails
- hasOne: LatestDetail, CurrentDetail

// Methods
- getFbCycleRatio()
- calculateLeaveEntitlement($workDays)
- getWorkDays(), getOffDays()
- getRosterPattern()
- getTotalAccumulatedLeave()
- getTotalLeaveTaken()
- getLeaveBalance()
```

### RosterDetail Model

```php
// Relationships
- belongsTo: Roster

// Methods
- getBaseWorkDays()
- getActualWorkDays() // base + adjusted
- getLeaveDays()
- getLeaveEntitlement()
- getLeaveBalance()
- isActive(), isOnLeave(), isCompleted()
- getStatusLabel(), getStatusBadgeClass()
```

## Key Benefits

### 1. Clean & Simple

-   Only 2 tables (rosters & roster_details)
-   No over-engineering
-   Easy to understand and maintain

### 2. Flexible

-   Support multiple FB Cycle patterns
-   Manual adjustments (balancing)
-   Customizable cycle dates

### 3. Accurate

-   Precise leave entitlement calculation
-   Decimal support for accurate tracking
-   Clear audit trail in remarks

### 4. User-Friendly

-   Modern UI with AdminLTE
-   Auto-suggestion for cycles
-   Visual indicators (badges, colors)
-   Responsive design

## FB Cycle Patterns

Based on `levels` table configuration:

| Level      | Work Days | Off Days | Pattern | FB Cycle Ratio |
| ---------- | --------- | -------- | ------- | -------------- |
| Non Staff  | 70        | 14       | 10w/2w  | 0.21           |
| Foreman    | 63        | 14       | 9w/2w   | 0.24           |
| Supervisor | 56        | 14       | 8w/2w   | 0.27           |
| Manager    | 42        | 14       | 6w/2w   | 0.36           |

## Example Scenarios

### Scenario 1: Normal Cycle

-   Level: Foreman (9w/2w)
-   Work: 63 days (7-Mar-25 to 9-May-25)
-   Adjusted: 0 days
-   Actual Work: 63 days
-   Leave Entitlement: 63 × 0.24 = 15.12 days
-   Leave Period: 10-May-25 to 24-May-25 (15 days)
-   Balance: 15.12 - 15 = 0.12 days

### Scenario 2: With Balancing

-   Level: Foreman (9w/2w)
-   Work: 63 days
-   Adjusted: +5 days (extended due to project needs)
-   Actual Work: 68 days
-   Leave Entitlement: 68 × 0.24 = 16.32 days
-   Leave Period: 16 days (can take more leave)
-   Balance: 16.32 - 16 = 0.32 days

### Scenario 3: Multiple Cycles

-   Cycle 1: 63 days → 15.12 entitlement, 15 taken
-   Cycle 2: 82 days → 19.68 entitlement, 20 taken
-   Cycle 3: 63 days → 15.12 entitlement, 15 taken
-   Total Accumulated: 49.92 days
-   Total Taken: 50 days
-   Balance: -0.08 days (slight over-utilization)

## Notes

-   Old roster system (with daily status, adjustments, histories) has been completely removed
-   Focus on cycle-based management instead of daily tracking
-   Balancing integrated into cycle details (adjusted_days field)
-   Simpler data structure for better performance
-   Compatible with existing leave management system

## Future Enhancements (Optional)

-   Export roster cycles to Excel
-   Import bulk cycles from Excel
-   Integration with leave request system
-   Notification for upcoming cycle end
-   Dashboard analytics for roster utilization
-   Mobile-friendly responsive view

---

**Implementation Date:** December 16, 2025  
**Status:** ✅ Completed  
**Migration Status:** ✅ Success  
**Testing Status:** ⏳ Ready for Manual Testing
