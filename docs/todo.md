**Purpose**: Track current work and immediate priorities for ARKA HERO HRMS
**Last Updated**: 2026-08-19

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

-   `[done] P2: Move leave-request Leave Balance from sidebar card into a modal linked under Leave Type [create/edit/my-create/my-edit] (completed: 2026-08-19)`
-   `[done] P1: Leave Period date-fences only annual/LSL; paid/unpaid unconstrained dates + create-time snapshot [LeaveType::usesLeavePeriodAsDateFence, LeaveRequest::matchingEntitlement, leave-requests create/edit] (completed: 2026-08-19)`

-   `[done] P2: FPTK list — add Project column and Project filter [recruitment/requests/index, RecruitmentRequestController::index/getRecruitmentRequests] (completed: 2026-08-19)`
-   `[done] P2: Align my-trips list styling with personal DataTables pages [my-trips.blade.php; pattern from overtime/leave my-requests] (completed: 2026-08-11)`
-   `[done] P2: Align my-trips detail Assignment Information card with FOA admin show [partials/assignment-info-card] (completed: 2026-08-11)`
-   `[done] P1: FOA Close at Origin on admin detail + shared partial with my-trips [close-at-origin-form, vehicle-assignments.close] (completed: 2026-08-11)`
-   `[done] P1: FOA letter number (category FOA) + Status/Trip Log UI polish [smart-letter-number-selector, FoaLetterCategorySeeder, formatFormNumber] (completed: 2026-08-11)`
-   `[done] P1: Form of Assignment (FOA) — requestor issue/print → driver trip log (project/manual stops) → close at origin + odometer [VehicleAssignmentController, VEHICLE_ASSIGNMENT_FOA_DESIGN.md, VehicleAssignmentPermissionSeeder] (completed: 2026-08-11)`
-   `[done] P2: Disciplinary export — clarify Imported (doc later) Yes/No, add remaining_days after end_date, export honors list filters [EmployeeDisciplinaryExport, index Export JS, filteredQuery dates] (completed: 2026-08-10)`
-   `[done] P2: Disciplinary list — hide DataTables search; add PP Criteria Select2 filter (criterion_id) on admin + my-records [index/my-index, EmployeeDisciplinaryController::filteredQuery/myRecordsData] (completed: 2026-08-10)`
-   `[done] P1: Disciplinary Export/Import + deferred document upload for imported rows [EmployeeDisciplinaryExport/Import, imported_at, upload-document; reuse show/create/edit] (completed: 2026-08-10)`
-   `[done] P1: Disciplinary auto-escalation — strict SP floor (same/lower blocked on create; UI pre-selects next; clearer messages) [DisciplinaryService::allowedTypes/allowedTypesForEdit, _form-scripts] (completed: 2026-08-10)`
-   `[done] P1: My Disciplinary Record — personal view-only list/detail + My Dashboard widget [personal.disciplinary.view-own, employee-disciplinaries/my-records*, DashboardController::personal] (completed: 2026-08-10)`
-   `[done] P1: Seed kriteria PP Pasal 22 — Counseling 6.a–p, SP1 7.a–r, SP Pertama & Terakhir 10.a–k [DisciplinaryCriteriaSeeder; sanction_type +counseling] (completed: 2026-08-07)`
-   `[done] P1: Pembinaan & SP — Coaching/Counseling + SP1–SP3 with PP criteria M2M, validity/floor/expire, post-SP3 termination [EmployeeDisciplinaryController, DisciplinaryCriterionController, DisciplinaryService, disciplinary:expire, DisciplinaryPermissionSeeder] (completed: 2026-08-07)`
-   `[done] P1: Fuel Claim print — A4 portrait 3×3 receipt photo sheet (chunk 9/page, private images as data-URI) [FuelClaimController::print(), fuel-claims/print.blade.php] (completed: 2026-08-10)`
-   `[done] P1: Draft Fuel Claim receipt management — add/edit/remove receipts (edit via modal) with transactional total recalculation [FuelClaimController::addReceipts(), updateReceipt(), removeReceipt(), fuel-claims/show.blade.php] (completed: 2026-08-05)`
-   `[done] P1: Fuel Bot Activity Log UI — /fuel-bot-logs list+detail (status pipeline, parsed JSON, inbox photo, fuel record link); sidebar grouped SYSTEMS → Fuel Bot [FuelBotLogController, fuel-bot-logs views, fuel-bot-logs.show permission] (completed: 2026-08-05)`
-   `[done] P1: Telegram Fuel Bot — whitelist CRUD + webhook Level-2 AI confirm → ingest fuel_records [FuelBotSubscriber*, TelegramFuelBotHandler, FuelBotIngestService, FuelBotApiController, telegram:fuel-bot-webhook] (completed: 2026-08-05)`
-   `[done] P1: Driver Fuel Log — photo+OpenRouter AI → office verify → fuel_claims bundle + API v1 realization [FuelRecordController my-requests/pending, FuelClaimController, OpenRouterReceiptParser, FuelWorkflowPermissionSeeder, PWA manifest/sw] (completed: 2026-08-04)`
-   `[done] P0: Leave Requests DataTables Ajax 403 — same-origin AJAX paths via request()->getBasePath() (avoid APP_URL :8080 vs /arka-hero cookie drop) [leave-requests/index, my-requests] (completed: 2026-08-03)`
-   `[done] P2: Vehicle list filters + Excel export/import — validity filter; VehicleExport/Import upsert by kode [index, VehicleController] (completed: 2026-08-03)`
-   `[done] P2: Vehicle document validity API — GET /api/v1/vehicles, /vehicles/{id}, /vehicle-documents/expiring [VehicleApiController, api.php; X-API-Key] (completed: 2026-08-03)`
-   `[done] P2: Vehicle documents single-table schema — file_* on create_vehicle_documents; dropped revisions migration/model; rollback batch 78+79 then remigrate [VehicleDocumentController, show.blade] (completed: 2026-08-03)`
-   `[done] P1: Vehicle Administration (GAMMA) — master Light Vehicle + STNK/PKB/KIR monitoring + fuel; Kode from ArkFleet plant_group_id=3; no maintenance [VehicleController, ArkFleetClient, VehiclePermissionSeeder, sidebar GAMMA Kendaraan] (completed: 2026-08-03)`
-   `[done] P1: SPM letter number fields — periode magang, departemen ditempatkan, lembaga pendidikan [LetterNumberController case SPM, #spm-template create/edit/show, department_id + educational_institution migration, export/import] (completed: 2026-07-31)`
-   `[done] P2: Clarify Activity Log email statistics [live Pending Queue vs 7-day Queued Events/Delivered/Failed/Skipped] (completed: 2026-07-31)`
-   `[done] P1: Email notification Docker-ready improvements [database queue+ShouldQueue, CTA per event, 3-day reminder, config CC, idempotency, logo+plaintext, litmus, delivery metrics] (completed: 2026-07-30)`
-   `[done] P1: Email recipient full name + safe inbox CTA [users.name in Dear/To/Debug; every CTA only opens /approval/requests] (completed: 2026-07-30)`
-   `[done] P1: Production email CTA base URL [DOCUMENT_NOTIFICATIONS_BASE_URL; CTA and audit links target http://192.168.32.146:8080] (completed: 2026-07-30)`
-   `[done] P2: Clarify multi-day RCR ranges in dashboard calendar [range-prefixed event title, full-period tooltip, striped spanning bar] (completed: 2026-07-30)`
-   `[done] P2: Compact cozy redesign for all approval email notifications [shared _ui tokens, tighter padding, warm palette across 7 document partials] (completed: 2026-07-30)`
-   `[done] P1: Refactor RCR approval email content to match approval-request show [Room & Consumption Information + facilities/notes/consumption partial; room_name] (completed: 2026-07-30)`
-   `[done] P1: Refactor FPTK approval email content to match approval-request show [FPTK Information + Job Description & Requirements partial; position_name] (completed: 2026-07-30)`
-   `[done] P1: Refactor Leave Request approval email content to match approval-request show [Leave Request Information + Employee Information partial; fullname + sisa cuti] (completed: 2026-07-30)`
-   `[done] P1: Refactor Overtime Request approval email content to match approval-request show [Overtime Information + Employee Details partial; employee fullname] (completed: 2026-07-30)`
-   `[done] P1: Refactor Flight Ticket Issuance approval email content to match approval-request show [LG Information + Ticket Details partial; issued_number reference; passenger fullname] (completed: 2026-07-30)`
-   `[done] P1: Refactor Flight Request approval email content to match approval-request show [FR partial: employee info + LOT/standalone followers + flight segments + notes] (completed: 2026-07-30)`
-   `[done] P1: Refactor Official Travel approval email content to match approval-request show [OT partial: travel details + traveler fullname + conditional followers; reference=official_travel_number] (completed: 2026-07-30)`
-   `[done] P1: Outlook/Thunderbird-safe approval email + browser no-send preview + DOCUMENT_NOTIFICATIONS_ENABLED .env toggle [table/inline layout, Outlook VML CTA, shared mailViewData, debug preview route] (completed: 2026-07-30)`
-   `[done] P1: Fix edit leave JS — entitlementData null crash + Invalid date picker [lsl-flexible-scripts null-safe; edit destroy→remove + restore display] (completed: 2026-07-29)`
-   `[done] P0: Leave approver change = FPTK pattern (pending-only) [LeaveRequest canChangeApprovers/getLockedApproverIds, updateApprovers + show form, syncPendingLeaveApprovers on edit] (completed: 2026-07-29)`
-   `[done] P0: Fix leave update — recreate approval_plans when manual_approvers change [LeaveRequestController::update; was delete-only; restored b58a14ce] (completed: 2026-07-29)`
-   `[done] P1: Document email notification foundation + Spatie activity audit UI [NotifiableDocument, DocumentNotificationService, hooks on ApprovalPlan/ApprovalRequest controllers, Activity Logs under SYSTEMS, activity-logs.show] (completed: 2026-07-29)`
-   `[done] P1: FPTK & MPP HOLD/UNHOLD [status on_hold, recruitment_request_holds / man_power_plan_holds, freeze approval+recruitment, TTH/TTF/aging/stale clock subtract hold days, dashboard On Hold card; permissions recruitment-requests.hold & mpp.hold created manually] (completed: 2026-07-28)`
-   `[done] P1: RCR meeting_date → start_date + end_date [migration, conflict range, form/list/show/dashboard/report, IT WO end_date] (completed: 2026-07-29)`
-   `[done] P2: Align RCR user manual with My Request REQxxxxx + HR confirm flow [docs/user-manual/19-room-consumption-management.md §3.4/§5, PDF regen] (completed: 2026-07-28)`
-   `[done] P1: Fix approval_plans datetime — all steps showed final close time [acted_at column, close is_open without touching timestamps, decisionAt()] (completed: 2026-07-24)`
-   `[done] P2: RCR form Zoom Meeting ID Availability [Need Zoom checkbox → panel accounts 131/132/134, GET zoom-meeting-availability via rest-server + ItWoZoomClient] (completed: 2026-07-24)`
-   `[done] P2: RCR management dashboard [DashboardController@roomConsumptionManagement, calendar-events API, FullCalendar + bulan/tahun/ruangan filter, statistik zoom/konsumsi/approval] (completed: 2026-07-22)`
-   `[done] P1: IT WO karyawan auto-provision for RCR Zoom [resolveKaryawan NIK→email rehire→INSERT, resolveJabatanId auto-create jabatan/departemen, acknowledge first approver, HERO position/department payload] (completed: 2026-07-22)`
-   `[done] P1: IT WO Zoom Phase 2 — CI3 API + HERO client [arka-rest-server Zoom_meeting_requests, cat 8/subcat 35, issue packing, ItWoZoomClient, approval dispatch, zoom-callback] (completed: 2026-07-20)`
-   `[done] P1: Room & Consumption Request (RCR) Phase 1 [docs/ROOM_CONSUMPTION_REQUEST_DESIGN.md, meeting_rooms, room_consumption_requests, manual approval, My Features, IT WO Zoom stub] (completed: 2026-07-20)`
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
