# MEMORY.md

## Recent Implementations & Learnings

### 2025-01-27: Position Export/Import Feature Implementation

**Context**: User requested to implement export and import features for positions similar to EmployeeController, with proper failure handling and updateOrCreate functionality using ID as key.

**Changes Made**:

1. **Created PositionExport Class**:

    - Exports position data with columns: ID, Position Name, Department Name, Position Status
    - Uses proper Excel formatting and styling
    - Includes department relationship data via leftJoin

2. **Enhanced PositionImport Class**:

    - Updated to use `updateOrCreate` with ID as primary key
    - Added comprehensive failure handling with proper error messages
    - Improved validation rules and custom validation messages
    - Added support for status text conversion (Active/Inactive)
    - Implemented proper error handling for database operations

3. **Updated PositionController**:

    - Added export method for downloading position data
    - Enhanced import method with comprehensive failure handling
    - Added proper validation and error handling similar to EmployeeController

4. **Updated Routes and Views**:
    - Added export route: `GET /positions/export`
    - Added export button to position index view
    - Implemented failure display modal for import validation errors

**Technical Implementation**:

-   **Export**: `app/Exports/PositionExport.php` - Handles data export with proper formatting
-   **Import**: `app/Imports/PositionImport.php` - Enhanced with failure handling and updateOrCreate
-   **Controller**: `app/Http/Controllers/PositionController.php` - Added export method and improved import
-   **Routes**: `routes/web.php` - Added export route
-   **View**: `resources/views/position/index.blade.php` - Added export button and failure modal

**Key Features**:

-   **Export**: Downloads Excel file with ID, Position Name, Department Name, Position Status
-   **Import**: Supports both creating new positions and updating existing ones via ID
-   **Validation**: Comprehensive validation with custom error messages
-   **Failure Handling**: Detailed error display modal showing validation failures
-   **Status Handling**: Converts text status (Active/Inactive) to boolean values
-   **Department Lookup**: Validates department names against existing departments

**Benefits**:

-   Complete data portability for positions
-   Efficient bulk updates using existing IDs
-   Comprehensive error reporting for import issues
-   Consistent with existing employee import/export patterns
-   Better data integrity through validation

**Files Modified**:

-   `app/Exports/PositionExport.php` (new)
-   `app/Imports/PositionImport.php` (enhanced)
-   `app/Http/Controllers/PositionController.php` (updated)
-   `routes/web.php` (added export route)
-   `resources/views/position/index.blade.php` (added export button and failure modal)

---

### 2025-01-27: PersonalImport Validation and Date Parsing Improvements

**Context**: User reported import errors for employee personal data, specifically issues with place of birth and date of birth validation, and date parsing errors for Indonesian date formats like '28 Oktober 1972'.

**Changes Made**:

1. **Removed Validation Rules**:

    - Removed `place_of_birth` required validation
    - Removed `date_of_birth` required validation
    - Updated validation messages accordingly

2. **Enhanced Date Parsing**:
    - Added support for Indonesian month names (Januari, Februari, Maret, etc.)
    - Implemented fallback date parsing with multiple format attempts
    - Added error handling to prevent import failures due to date parsing issues
    - Added logging for unparseable dates to help with debugging

**Technical Implementation**:

-   Modified `app/Imports/PersonalImport.php` validation rules
-   Enhanced date processing logic in the `model()` method
-   Added Indonesian month name mapping to English month numbers
-   Implemented multiple date format fallback attempts
-   Added proper error handling and logging

**Benefits**:

-   More flexible import process that doesn't fail on missing birth data
-   Better handling of Indonesian date formats commonly used in local data
-   Improved error resilience and debugging capabilities
-   Maintains data integrity while allowing for incomplete records

**Files Modified**:

-   `app/Imports/PersonalImport.php`

---

### 2025-08-19: Conditional Theory Test Implementation

**Business Rule Implemented**: Tes Teori hanya dilakukan untuk posisi mekanik yang memerlukan kompetensi teknis.

**Technical Implementation**:

-   Added `requires_theory_test` BOOLEAN column to `recruitment_requests` table
-   Updated `RecruitmentRequest` model with helper method `requiresTheoryTest()`
-   Modified `RecruitmentSession` model to conditionally skip tes_teori stage
-   Updated timeline view to conditionally show/hide tes_teori stage
-   Added checkbox in FPTK create/edit forms for HR to set requirement

**Key Benefits of This Approach**:

1. **Per-FPTK Control**: HR can set theory test requirement per recruitment request
2. **Flexible**: Same position can have different requirements per project/department
3. **Simple Implementation**: Single boolean field vs complex position classification
4. **Backward Compatible**: Existing FPTKs default to FALSE (no breaking changes)

**Files Modified**:

-   `database/migrations/2025_08_19_110333_add_requires_theory_test_to_recruitment_requests.php`
-   `app/Models/RecruitmentRequest.php` - Added field and helper method
-   `app/Models/RecruitmentSession.php` - Updated stage progression logic
-   `resources/views/recruitment/sessions/show-session.blade.php` - Conditional timeline rendering
-   `resources/views/recruitment/requests/create.blade.php` - Added checkbox
-   `resources/views/recruitment/requests/edit.blade.php` - Added checkbox
-   `app/Http/Controllers/RecruitmentRequestController.php` - Handle new field

**Stage Flow Changes**:

-   **Mechanic Positions** (`requires_theory_test = TRUE`): Full flow including tes_teori
-   **Non-Mechanic Positions** (`requires_theory_test = FALSE`): Skip tes_teori, direct from psikotes to interview

**Progress Calculation**:

-   Adjusted progress percentages for non-mechanic positions to maintain smooth progression
-   Interview stage gets higher weight when tes_teori is skipped

**User Experience**:

-   HR can easily identify which FPTKs require theory tests
-   Timeline automatically adjusts based on position requirements
-   No confusion about missing tes_teori stage for non-mechanic positions

---

## Project Memory and Learning

### 2025-08-18: Server-Side DataTable Implementation for Recruitment Reports

**Context**: User requested conversion of all recruitment report tables to server-side DataTables (except "Recruitment Funnel by Stage"), including funnel stage details and all other reports.

**Implementation**:

-   Added new server-side data methods in `RecruitmentReportController`:
    -   `agingData()` - for Request Aging & SLA report
    -   `timeToHireData()` - for Time-to-Hire Analysis report
    -   `offerAcceptanceRateData()` - for Offer Acceptance Rate report
    -   `interviewAssessmentAnalyticsData()` - for Interview & Assessment Analytics report
    -   `staleCandidatesData()` - for Stale Candidates report
    -   `stageDetailData()` - for funnel stage detail reports
-   Updated routes in `web.php` to add new data endpoints
-   Modified view files to use DataTables with `processing: true`, `serverSide: true`, and AJAX configuration
-   Each method handles filtering, searching, ordering, and pagination server-side

**Benefits**:

-   Improved performance for large datasets
-   Reduced client-side memory usage
-   Better scalability for reports with many records
-   Consistent server-side processing across all reports

**Files Modified**:

-   `app/Http/Controllers/RecruitmentReportController.php`
-   `routes/web.php`
-   All report view files in `resources/views/recruitment/reports/`

### 2025-08-18: Bug Fixes and Column Optimization for Recruitment Reports

**Context**: User reported errors in "Aging" and "Stale" reports and requested column merging for "Interview Assessment Analytics" report.

**Fixes Applied**:

1. **Aging Report Fixes**:

    - Fixed `approved_at` ordering by adding proper `leftJoin` with `approval_plans` table
    - Corrected `days_to_approve` calculation to use proper date difference method
    - Fixed column name reference from `request_no` to `request_number` in ordering

2. **Stale Report Fixes**:

    - Added `candidate` to eager loading relationships
    - Implemented comprehensive null checks for all relationship fields
    - Added robust null checks for `count()` method calls on collections

3. **Interview Assessment Analytics Column Merging**:
    - Combined "Psikotes Result" and "Psikotes Score" into single "Psikotes Result" column
    - Combined "Tes Teori Result" and "Tes Teori Score" into single "Tes Teori Result" column
    - Combined "Interview Type" and "Interview Result" into single "Interview Result" column
    - Updated `calculateOverallAssessment` method to handle combined data format

**Technical Details**:

-   Added `is_object()`, `method_exists()`, and `count()` checks before calling collection methods
-   Fixed variable naming conflicts in loops (changed `$request` to `$recruitmentRequest` in aging report)
-   Updated column definitions and ordering logic for proper database field references

**Benefits**:

-   Eliminated "Column not found" SQL errors
-   Prevented "count() on null" PHP errors
-   Improved data presentation with combined columns
-   Enhanced error handling and null safety

**Files Modified**:

-   `app/Http/Controllers/RecruitmentReportController.php` - All fixes and optimizations
-   `docs/MEMORY.md` - Documentation updates

### 2025-08-18: Additional Fix for Stale Report Method Error

**Context**: User reported "Method App\Http\Controllers\RecruitmentReportController::buildStaleCandidatesData does not exist" error.

**Root Cause**: During previous refactoring, the `staleCandidatesData` method was accidentally removed while converting to server-side DataTables.

**Solution Applied**:

-   Restructured `staleCandidates()` method to only return the view with filter options
-   Re-added `staleCandidatesData()` method to handle all data processing and AJAX requests
-   Maintained separation of concerns: view rendering vs. data processing

**Result**: Stale Candidates report now functions correctly with server-side DataTable processing.

### 2025-08-18: Final Fixes for Aging Report DataTable

**Context**: User reported SQL error "Column not found: request_no in order clause" and "Call to undefined method App\Models\RecruitmentRequest::input()".

**Root Causes**:

1. Column name mismatch: `request_no` vs `request_number` in database
2. Variable naming conflict: HTTP request parameter `$request` vs. loop variable `$request` for RecruitmentRequest model

**Fixes Applied**:

1. **Column Name Fix**: Changed `request_no` to `request_number` in columns array and ordering logic
2. **Variable Naming Fix**: Renamed loop variable from `$request` to `$recruitmentRequest` to avoid conflicts
3. **Ordering Logic**: Added specific handling for `request_number` column in ordering

**Technical Details**:

-   Updated `$columns` array to use correct database field names
-   Modified ordering logic to handle `request_number` column properly
-   Renamed all loop variables to prevent method call confusion
-   Maintained consistent data structure for DataTable response

**Result**: Aging report DataTable now functions correctly without SQL or method call errors.

**Files Modified**:

-   `app/Http/Controllers/RecruitmentReportController.php` - agingData method fixes

### 2025-08-18: Standardized Filter Layouts for Recruitment Reports

**Task**: Standardized the filter section layout across all recruitment report views for consistency and better UX.

**Changes Made**:

-   **Layout Standardization**: Converted all filter forms from `form-inline` to `row` grid layout using Bootstrap columns
-   **Consistent Structure**: All reports now use the same filter structure:
    -   `card-header` with "Filter Options" title and filter icon
    -   `card-body` containing the filter form
    -   6 columns layout: 5 filter fields + 1 button group column
    -   Each filter field uses `col-md-2` for consistent spacing
-   **Button Grouping**: All action buttons (Filter, Reset, Export Excel) are now vertically stacked in the last column
-   **Visual Consistency**: Added proper labels, IDs, and consistent spacing across all reports

**Files Updated**:

1. `funnel.blade.php` - Recruitment Funnel by Stage
2. `aging.blade.php` - Request Aging & SLA
3. `time-to-hire.blade.php` - Time-to-Hire Analysis
4. `offer-acceptance-rate.blade.php` - Offer Acceptance Rate
5. `interview-assessment-analytics.blade.php` - Interview & Assessment Analytics (includes Scoring Index button)
6. `stale-candidates.blade.php` - Stale Candidates Report

**Benefits**:

-   **Better UX**: Cleaner, more organized filter interface
-   **Responsive Design**: Better mobile/tablet experience with grid layout
-   **Consistency**: All reports now look and behave the same way
-   **Maintainability**: Easier to update filter layouts across all reports
-   **Professional Appearance**: More polished and professional-looking interface

**Technical Details**:

-   Used Bootstrap 4 grid system with `col-md-2` for consistent column widths
-   Implemented `btn-group-vertical d-block` for button stacking
-   Added proper `for` attributes and `id` attributes for accessibility
-   Maintained all existing functionality while improving layout

### 2025-08-18: Bug Fixes and Column Optimization for Recruitment Reports

**Task**: Fixed errors in aging and stale reports, and optimized interview assessment analytics by combining related columns.

**Issues Fixed**:

#### **1. Aging Report (Request Aging & SLA)**

-   **Problem**: Error in ordering logic for `approved_at` column and incorrect days calculation
-   **Solution**:
    -   Fixed ordering for `approved_at` by adding proper join with `approval_plans` table
    -   Corrected `days_to_approve` calculation using proper date arithmetic
    -   Added proper error handling for missing relationships
-   **Technical Details**:

    ```php
    // Fixed ordering for approved_at column
    if ($column === 'approved_at') {
        $query->leftJoin('approval_plans', function($join) {
            $join->on('recruitment_requests.id', '=', 'approval_plans.document_id')
                 ->where('approval_plans.document_type', '=', 'recruitment_request')
                 ->where('approval_plans.status', '=', 1);
        });
        $query->orderBy('approval_plans.updated_at', $orderDir);
    }

    // Fixed days calculation
    $daysToApprove = $latestApproval->updated_at ?
        $latestApproval->updated_at->diffInDays($request->created_at) : null;
    ```

#### **2. Stale Candidates Report**

-   **Problem**: Missing `candidate` relationship in eager loading and potential null reference errors
-   **Solution**:
    -   Added `candidate` to eager loading relationships
    -   Added null checks for all relationship references
    -   Fixed data building with proper error handling
-   **Technical Details**:

    ```php
    // Added candidate to eager loading
    ->with([
        'fptk.department',
        'fptk.position',
        'fptk.project',
        'candidate'  // Added this
    ])

    // Added null checks
    'candidate_name' => $session->candidate ? $session->candidate->fullname : '-',
    'request_id' => $session->fptk ? $session->fptk->id : 0,
    ```

**Additional Fix**:

-   **Problem**: Method `buildStaleCandidatesData` was called but didn't exist, causing fatal error
-   **Solution**: Restructured the controller to use server-side DataTable properly:
    -   `staleCandidates()` method now only returns view with filter options
    -   `staleCandidatesData()` method handles all data processing and AJAX requests
    -   Removed unnecessary data building in the main method since DataTable fetches data via AJAX

#### **3. Interview Assessment Analytics Report - Column Optimization**

-   **Task**: Combined related columns to reduce table width and improve readability
-   **Changes Made**:
    -   **Psikotes**: Combined `psikotes_result` + `psikotes_score` → single `psikotes_result` column
    -   **Tes Teori**: Combined `tes_teori_result` + `tes_teori_score` → single `tes_teori_result` column
    -   **Interview**: Combined `interview_type` + `interview_result` → single `interview_result` column
-   **Format Examples**:
    -   **Psikotes**: "Pass (Online: 85.0, Offline: 82.5)" or "Fail"
    -   **Tes Teori**: "Pass (78.5)" or "Pending"
    -   **Interview**: "HR - Recommended" or "User - Pass" or "HR, User - Recommended"
-   **Technical Implementation**:

    ```php
    // Psikotes combined format
    $psikotesResult = $result;
    if (!empty($scoreDetails)) {
        $psikotesResult .= ' (' . implode(', ', $scoreDetails) . ')';
    }

    // Tes Teori combined format
    $tesTeoriResult = $result;
    if ($score) {
        $tesTeoriResult .= ' (' . $score . ')';
    }

    // Interview combined format
    if ($type && $result) {
        $interviewResult = $type . ' - ' . $result;
    }
    ```

#### **4. Overall Assessment Calculation Fix**

-   **Problem**: Method signature changed after column combination
-   **Solution**: Updated `calculateOverallAssessment()` method to:
    -   Extract result part from combined data using regex
    -   Parse interview type and result from combined string
    -   Maintain backward compatibility with scoring logic
-   **Technical Details**:

    ```php
    // Extract result from combined data
    $psikotesResult = preg_replace('/\s*\([^)]*\)/', '', $psikotes);

    // Parse interview type and result
    if (strpos($interviewResult, ' - ') !== false) {
        list($interviewType, $interviewResultOnly) = explode(' - ', $interviewResult, 2);
    }
    ```

**Benefits of Column Optimization**:

-   **Better UX**: Reduced table width, easier to read on all devices
-   **Data Consolidation**: Related information displayed together logically
-   **Maintained Functionality**: All data still accessible, just better organized
-   **Responsive Design**: Table works better on mobile and tablet devices
-   **Cleaner Interface**: Less cluttered appearance while maintaining all information

**Files Modified**:

1. `app/Http/Controllers/RecruitmentReportController.php` - Fixed aging and stale methods, optimized interview analytics
2. `resources/views/recruitment/reports/interview-assessment-analytics.blade.php` - Updated table structure and JavaScript rendering

**Testing Required**:

-   Verify aging report sorting works correctly for all columns
-   Confirm stale candidates report displays data without errors
-   Test interview analytics with various data combinations
-   Validate overall assessment calculation still works accurately

### 2025-08-18: Stale Candidates View - count() on null Fix

-   Fixed PHP error `count(): Argument #1 ($value) must be of type Countable|array, null given` in `resources/views/recruitment/reports/stale-candidates.blade.php` by replacing `count($rows)` and related usages with a safe `collect($rows ?? [])` pattern for summary calculations. This aligns with server-side DataTable usage where `$rows` is not provided by the controller.

### 2025-08-18: Aging Report Filter Fix

**Context**: User reported that filters in the aging report were not functioning properly.

**Root Cause**: The DataTable was using hardcoded Blade variables (`{{ $date1 }}`, `{{ $department }}`, etc.) instead of reading actual form input values, and the controller was still using old data building logic.

**Fixes Applied**:

1. **DataTable Filter Integration**: Updated the `ajax.data` function in `aging.blade.php` to read values from actual form inputs using jQuery selectors instead of hardcoded Blade variables
2. **Controller Cleanup**: Removed unnecessary `buildAgingData()` call and `rows` data from the `aging()` method since it now uses server-side DataTable
3. **Filter Synchronization**: Ensured that form filters properly trigger DataTable refresh via AJAX

**Technical Details**:

-   Changed `d.date1 = '{{ $date1 }}'` to `d.date1 = $('input[name="date1"]').val()`
-   Applied same pattern for all filter fields (date2, department, project, status)
-   Maintained existing filter logic in `agingData()` method for server-side processing
-   Form submission now properly triggers `table.ajax.reload()` to refresh data with new filters

**Result**: Aging report filters now work correctly, allowing users to filter by date range, department, project, and status with real-time DataTable updates.

**Files Modified**:

-   `resources/views/recruitment/reports/aging.blade.php` - Fixed DataTable filter integration
-   `app/Http/Controllers/RecruitmentReportController.php` - Cleaned up aging method

### 2025-08-18: Time-to-Hire Report Updates - Filter Integration & Export

**Context**: Applied the same improvements to the time-to-hire report that were made to the aging report: removed DataTables built-in buttons/search, added custom Export Excel with filter integration, and updated project ordering.

**Changes Applied**:

1. **DataTable Simplification**:

    - Removed built-in buttons (copy, csv, excel, print, pdf) and search functionality
    - Set `searching: false` to disable built-in search
    - Removed unnecessary DataTables button assets and scripts

2. **Filter Integration**:

    - Updated `ajax.data` function to read values from actual form inputs using jQuery selectors
    - Changed from hardcoded Blade variables to dynamic form values: `$('input[name="date1"]').val()`

3. **Custom Export Excel**:

    - Replaced static export link with dynamic button that captures current filter values
    - Export now includes all filter parameters (date1, date2, department, position, project)
    - Uses JavaScript to build query string and navigate to export route

4. **Controller Cleanup**:

    - Removed old data building logic from `timeToHire()` method since it now uses server-side DataTable
    - Export method already correctly applies filters, so no changes needed there

5. **Project Ordering**:
    - Updated project ordering to use `project_code` instead of `project_name` for consistency

**Technical Details**:

-   Filter values are now read dynamically: `d.date1 = $('input[name="date1"]').val()`
-   Export button builds query string: `$.param({ date1: ..., date2: ..., department: ..., position: ..., project: ... })`
-   Maintained existing filter logic in `timeToHireData()` method for server-side processing
-   Form submission properly triggers `table.ajax.reload()` to refresh data with new filters

**Result**: Time-to-hire report now has consistent behavior with aging report:

-   Clean interface with only custom filter controls
-   Export Excel respects current filter settings
-   No filter = export all data, with filters = export filtered data only
-   Consistent project ordering across reports

**Files Modified**:

-   `resources/views/recruitment/reports/time-to-hire.blade.php` - Removed built-in buttons/search, added custom export
-   `app/Http/Controllers/RecruitmentReportController.php` - Cleaned up timeToHire method

### 2025-08-18: Offer Acceptance Rate Report Updates - Filter Integration & Export

**Context**: Applied the same improvements to the offer acceptance rate report that were made to the aging and time-to-hire reports: removed DataTables built-in buttons/search, added custom Export Excel with filter integration, and updated project ordering.

**Changes Applied**:

1. **DataTable Simplification**:

    - Removed built-in buttons (copy, csv, excel, print, pdf) and search functionality
    - Set `searching: false` to disable built-in search
    - Removed unnecessary DataTables button assets and scripts

2. **Filter Integration**:

    - Updated `ajax.data` function to read values from actual form inputs using jQuery selectors
    - Changed from hardcoded Blade variables to dynamic form values: `$('input[name="date1"]').val()`

3. **Custom Export Excel**:

    - Replaced static export link with dynamic button that captures current filter values
    - Export now includes all filter parameters (date1, date2, department, position, project)
    - Uses JavaScript to build query string and navigate to export route

4. **Controller Cleanup**:

    - Removed old data building logic from `offerAcceptanceRate()` method since it now uses server-side DataTable
    - Export method already correctly applies filters, so no changes needed there

5. **Project Ordering**:
    - Updated project ordering to use `project_code` instead of `project_name` for consistency

**Technical Details**:

-   Filter values are now read dynamically: `d.date1 = $('input[name="date1"]').val()`
-   Export button builds query string: `$.param({ date1: ..., date2: ..., department: ..., position: ..., project: ... })`
-   Maintained existing filter logic in `offerAcceptanceRateData()` method for server-side processing
-   Form submission properly triggers `table.ajax.reload()` to refresh data with new filters

**Result**: Offer acceptance rate report now has consistent behavior with other reports:

-   Clean interface with only custom filter controls
-   Export Excel respects current filter settings
-   No filter = export all data, with filters = export filtered data only
-   Consistent project ordering across all reports

**Files Modified**:

-   `resources/views/recruitment/reports/offer-acceptance-rate.blade.php` - Removed built-in buttons/search, added custom export
-   `app/Http/Controllers/RecruitmentReportController.php` - Cleaned up offerAcceptanceRate method

### 2025-08-18: Interview Assessment Analytics & Stale Candidates Reports - Filter Integration & Export

**Context**: Applied the same improvements to the interview assessment analytics and stale candidates reports that were made to the other reports: removed DataTables built-in buttons/search, added custom Export Excel with filter integration, and updated controller methods.

**Changes Applied**:

#### Interview Assessment Analytics Report:

1. **DataTable Simplification**:

    - Removed built-in buttons (copy, csv, excel, print, pdf) and search functionality
    - Set `searching: false` to disable built-in search
    - Removed unnecessary DataTables button assets and scripts

2. **Filter Integration**:

    - Updated `ajax.data` function to read values from actual form inputs using jQuery selectors
    - Changed from hardcoded Blade variables to dynamic form values: `$('input[name="date1"]').val()`

3. **Custom Export Excel**:

    - Replaced static export link with dynamic button that captures current filter values
    - Export now includes all filter parameters (date1, date2, department, position, project)
    - Uses JavaScript to build query string and navigate to export route

4. **Controller Cleanup**:
    - Removed old data building logic from `interviewAssessmentAnalytics()` method since it now uses server-side DataTable
    - Export method already correctly applies filters, so no changes needed there

#### Stale Candidates Report:

1. **DataTable Simplification**:

    - Removed built-in buttons (copy, csv, excel, print, pdf) and search functionality
    - Set `searching: false` to disable built-in search
    - Removed unnecessary DataTables button assets and scripts

2. **Filter Integration**:

    - Updated `ajax.data` function to read values from actual form inputs using jQuery selectors
    - Changed from hardcoded Blade variables to dynamic form values: `$('input[name="date1"]').val()`

3. **Custom Export Excel**:

    - Replaced static export link with dynamic button that captures current filter values
    - Export now includes all filter parameters (date1, date2, department, position, project)
    - Uses JavaScript to build query string and navigate to export route

4. **Controller Cleanup**:
    - Export method already correctly applies filters, so no changes needed there

**Technical Details**:

-   Filter values are now read dynamically: `d.date1 = $('input[name="date1"]').val()`
-   Export button builds query string: `$.param({ date1: ..., date2: ..., department: ..., position: ..., project: ... })`
-   Maintained existing filter logic in respective data methods for server-side processing
-   Form submission properly triggers `table.ajax.reload()` to refresh data with new filters

**Result**: Both reports now have consistent behavior with other reports:

-   Clean interface with only custom filter controls
-   Export Excel respects current filter settings
-   No filter = export all data, with filters = export filtered data only
-   Consistent project ordering using `project_code` across all reports

**Files Modified**:

-   `resources/views/recruitment/reports/interview-assessment-analytics.blade.php` - Removed built-in buttons/search, added custom export
-   `resources/views/recruitment/reports/stale-candidates.blade.php` - Removed built-in buttons/search, added custom export
-   `app/Http/Controllers/RecruitmentReportController.php` - Cleaned up interviewAssessmentAnalytics method
