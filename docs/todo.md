**Purpose**: Track current work and immediate priorities for ARKA HERO HRMS
**Last Updated**: 2026-01-09

## Task Management Guidelines

### Entry Format

Each task entry must follow this format:
`[status] priority: task description [context] (completed: YYYY-MM-DD)`

### Context Information

Include relevant context in brackets to help with future AI-assisted coding:

-   **Files**: `[app/Http/Controllers/LeaveRequestController.php:145]` - specific file and line numbers
-   **Functions**: `[calculateLeaveBalance(), generateEntitlements()]` - relevant function names
-   **APIs**: `[POST /api/v1/leave/requests, GET /api/employees/list]` - API endpoints
-   **Database**: `[leave_requests table, leave_entitlements.balance column]` - tables/columns
-   **Error Messages**: `["Days to Approve calculation error", "Token mismatch"]` - exact errors
-   **Dependencies**: `[blocked by roster system, needs project classification]` - blockers

### Status Options

-   `[ ]` - pending/not started
-   `[WIP]` - work in progress
-   `[blocked]` - blocked by dependency
-   `[testing]` - testing in progress
-   `[done]` - completed (add completion date)

### Priority Levels

-   `P0` - Critical (app won't work without this)
-   `P1` - Important (significantly impacts user experience)
-   `P2` - Nice to have (improvements and polish)
-   `P3` - Future (ideas for later)

---

# Current Tasks

## Working On Now

-   `[done] P0: Complete comprehensive documentation update [docs/*, MEMORY.md, AGENTS.md]` (completed: 2026-01-09)

## Up Next (This Week)

-   `[ ] P1: Sync all API routes to Postman collection "ARKA HERO - API" [routes/api.php, Postman MCP]`
-   `[✓] P2: Review leave_calculations table usage and purpose [COMPLETED - See docs/LEAVE_CALCULATIONS_ANALYSIS.md]`
-   `[ ] P2: Add automated tests for critical workflows [tests/Feature/, recruitment sessions, leave requests]`

## Blocked/Waiting

-   None currently

## Recently Completed

-   `[done] P0: Enhanced project documentation automation system [AGENTS.md, docs/* templates] (completed: 2026-01-09)`
-   `[done] P0: Updated architecture documentation with current system state [docs/architecture.md, 140 migrations, 62 controllers, 62 models] (completed: 2026-01-09)`
-   `[done] P1: Fixed Days to Approve calculation in Excel export [OfficialtravelController export, calculateDaysToApproveHelper() method, Carbon::diffInDays()] (completed: 2025-12-XX)`
-   `[done] P1: Implemented comprehensive recruitment stage validation [recruitment/sessions/show-session.blade.php, failed stage detection, modal triggers, SweetAlert validation] (completed: 2025-11-XX)`
-   `[done] P1: Modified recruitment session UI with yellow clock icons for waiting states [recruitment/sessions/show.blade.php, AdminLTE CSS classes] (completed: 2025-11-XX)`
-   `[done] P0: Created leave entitlement technical flow documentation [docs/LEAVE_ENTITLEMENT_TECHNICAL_FLOW.md, Group 1/Group 2 project classification, DOH-based + Roster-based calculations, LSL special rules] (completed: 2025-10-XX)`
-   `[done] P1: Restructured roster system with cycle-based approach [database/migrations, RosterController, rosters table, roster_details table, roster_daily_status table] (completed: 2025-12-XX)`
-   `[done] P1: Implemented leave request cancellation workflow [leave_request_cancellations table, LeaveRequestController cancellation methods] (completed: 2025-10-XX)`
-   `[done] P1: Added bulk periodic leave request functionality [BulkLeaveRequestController, batch_id tracking, bulk approval preview] (completed: 2025-11-XX)`
-   `[done] P0: Integrated letter numbering system with Official Travel and Recruitment [LetterNumberController, letter_numbers table, API integration endpoints] (completed: 2025-07-XX)`
-   `[done] P1: Implemented employee self-service registration system [EmployeeRegistrationController, token-based invitation, document upload] (completed: 2025-06-XX)`
-   `[done] P1: Created employee bond tracking and violation management [EmployeeBondController, BondViolationController, penalty calculation] (completed: 2025-09-XX)`
-   `[done] P1: Implemented Man Power Plan (MPP) module [ManPowerPlanController, man_power_plans table, MPP-FPTK integration] (completed: 2025-11-XX)`
-   `[done] P0: Refactored recruitment system with multi-stage approach [7 stage tables: cv_reviews, psikotes, tes_teori, interviews, offerings, mcu, hiring, session-based tracking] (completed: 2025-08-XX)`
-   `[done] P1: Added 3-level FPTK approval workflow [acknowledge → PM approval → Director approval, approval_plans table] (completed: 2025-08-XX)`

## Quick Notes

### Leave Entitlement System

-   **Group 1 Projects** (000H, 001H, APS, 021C, 025C): Standard leave types based on DOH eligibility
-   **Group 2 Projects** (017C, 022C): Roster-based periodic leave + standard types
-   **DOH Requirements**:
    -   Annual Leave: 12 months
    -   LSL Staff: 60 months
    -   LSL Non-staff: 72 months
-   **Special LSL Rule for Group 2**: Must take 10 days periodic leave before eligible for LSL

### Roster Patterns by Level

-   PM/SPT: 6 working days / 2 off days
-   SPV: 8 working days / 2 off days
-   FM: 9 working days / 2 off days
-   NS: 10 working days / 2 off days

### Recruitment Stage Validation

-   Failed stage locks all subsequent stages
-   Visual lock indicators with tooltips
-   JavaScript validation with SweetAlert messages
-   Yellow clock icons for waiting/in-progress states

### Official Travel Claim System

-   **search**: Returns all travel records
-   **search-claimed**: Returns already claimed records
-   **search-claimable**: Returns finished trips not yet claimed (departure_from_destination not null and claim status not set)

### API Authentication

-   Sanctum token-based for all `/api/v1/*` routes
-   Legacy `/api/*` routes remain unprotected for backward compatibility
-   Token obtained via `POST /api/v1/auth/login`

### Badge Color System (AdminLTE)

-   **Success (Green)**: Pass, Passed, Recommended, Approved, Accepted, Hired, Fit
-   **Danger (Red)**: Fail, Failed, Rejected, Declined, Not_recommended, Unfit
-   **Warning (Yellow)**: Pending, In_progress, Negotiating, Follow_up
-   **Secondary (Gray)**: Default/unknown status

### Notification System

-   Use `toast_success()`, `toast_error()`, `toast_warning()`, `toast_info()` helpers
-   Controller methods return messages in English
-   Avoid using toastr library directly

### Testing Strategy

-   Do NOT use `migrate:fresh` to reset database during testing
-   Continue using existing migration state
-   Focus tests on critical workflows: recruitment sessions, leave calculations, approval flows

### Documentation Maintenance

After every significant code change:

1. Update `docs/architecture.md` with current state
2. Update progress in `docs/todo.md`
3. Log decisions in `docs/decisions.md`
4. Note important discoveries in `MEMORY.md`
5. Move future ideas to `docs/backlog.md`

---

**Active Priorities for Next Development Session**:

1. Sync API routes to Postman collection
2. Optimize leave entitlement calculation queries
3. Add automated tests for recruitment and leave workflows
4. Review and improve error handling across modules
