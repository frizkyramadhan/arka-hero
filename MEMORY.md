**Purpose**: AI's persistent knowledge base for project context and learnings - ARKA HERO HRMS
**Last Updated**: 2026-01-14

## Memory Maintenance Guidelines

### Structure Standards

-   Entry Format: ### [ID] [Title (YYYY-MM-DD)] ✅ STATUS
-   Required Fields: Date, Challenge/Decision, Solution, Key Learning
-   Length Limit: 3-6 lines per entry (excluding sub-bullets)
-   Status Indicators: ✅ COMPLETE, ⚠️ PARTIAL, ❌ BLOCKED

### Content Guidelines

-   Focus: Architecture decisions, critical bugs, security fixes, major technical challenges
-   Exclude: Routine features, minor bug fixes, documentation updates
-   Learning: Each entry must include actionable learning or decision rationale
-   Redundancy: Remove duplicate information, consolidate similar issues

### File Management

-   Archive Trigger: When file exceeds 500 lines or 6 months old
-   Archive Format: `memory-YYYY-MM.md` (e.g., `memory-2025-01.md`)
-   New File: Start fresh with current date and carry forward only active decisions

---

## Project Memory Entries - ARKA HERO HRMS

### [001] Leave Entitlement System Dual-Group Architecture (2025-09-XX) ✅ COMPLETE

**Challenge**: Two distinct project types (standard office vs shift-based operational) require different leave management approaches within same system.

**Solution**: Implemented hybrid leave entitlement system with project classification (`projects.leave_type` field). Group 1 (standard): DOH-based calculations only. Group 2 (periodic): Roster-based periodic leave + DOH-based standard types. Special LSL rule for Group 2: requires 10 days periodic leave taken before eligibility.

**Key Learning**: Database-driven configuration (enum field in projects table) is superior to hardcoded project lists. Enables business users to classify new projects without code changes. Document complex business rules in dedicated technical flow document (`docs/LEAVE_ENTITLEMENT_TECHNICAL_FLOW.md`).

**Files**: `LeaveEntitlementController`, `projects` table migration, `docs/LEAVE_ENTITLEMENT_TECHNICAL_FLOW.md`

---

### [002] Days to Approve Calculation Bug in Official Travel Export (2025-12-XX) ✅ COMPLETE

**Challenge**: Excel export for official travel showed incorrect "Days to Approve" calculation. Anonymous export class couldn't access controller helper methods. Date parsing errors caused null values.

**Solution**: Added `calculateDaysToApproveHelper()` method within anonymous export class. Implemented proper error handling with try-catch blocks for date validation. Used `Carbon::diffInDays()` for accurate calculation between `requested_at` and `approved_at` dates. Updated anonymous class constructor to accept controller instance.

**Key Learning**: Anonymous classes in Laravel Excel exports are isolated scope. Helper methods must be duplicated or passed via constructor. Always add date validation and error handling for null date scenarios. Test export functionality with edge cases (null dates, same-day approvals).

**Files**: `OfficialtravelController::exportExcel()`, Excel export anonymous class

---

### [003] Recruitment Session Stage Validation System (2025-11-XX) ✅ COMPLETE

**Challenge**: Users could edit subsequent recruitment stages even after a stage failed, causing data integrity issues. No visual indicators for stage status.

**Solution**: Implemented comprehensive stage validation in `show-session.blade.php`. PHP logic detects failed stages and locks all subsequent stages. Conditional modal triggers prevent editing locked stages. Added visual lock indicators with tooltips. JavaScript validation with SweetAlert messages for better UX. Yellow clock icons for waiting/in-progress states (AdminLTE classes).

**Key Learning**: Multi-stage workflows require explicit validation at UI level to prevent out-of-sequence data entry. Combine server-side validation (controller) with client-side UX (disabled buttons, visual indicators). Use AdminLTE badge system consistently: success (green), danger (red), warning (yellow), secondary (gray). Always provide user feedback (tooltips, SweetAlert) explaining why actions are disabled.

**Files**: `recruitment/sessions/show-session.blade.php`, `RecruitmentSessionController`

---

### [004] Recruitment System Multi-Stage Refactoring (2025-08-XX) ✅ COMPLETE

**Challenge**: Original single-table approach for recruitment assessments (`recruitment_assessments`, `recruitment_offers`) became unwieldy as requirements grew. Different stages had vastly different data structures. Queries were slow, validation was complex.

**Solution**: Refactored to separate table per stage architecture: 7 stage-specific tables (`recruitment_cv_reviews`, `recruitment_psikotes`, `recruitment_tes_teori`, `recruitment_interviews`, `recruitment_offerings`, `recruitment_mcu`, `recruitment_hiring`). Each table optimized for its specific data requirements. `recruitment_sessions` table tracks current stage and overall status. Dedicated controller methods per stage.

**Key Learning**: Don't force different data structures into single table. Separate tables enable better indexing, clearer validation, easier reporting. Multi-stage workflows benefit from explicit stage transition methods. Migration 2025_08_07_150012 provides clean break from old structure. Document stage flow in comments and diagrams.

**Files**: All `recruitment_*` tables, `RecruitmentSessionController`, `RecruitmentSession` model with relationships

---

### [005] Letter Numbering System Integration (2025-06-XX) ✅ COMPLETE

**Challenge**: Multiple document types needed sequential letter numbers. Manual assignment was error-prone, caused duplicates, no audit trail.

**Solution**: Built centralized letter numbering system with lifecycle tracking (available → reserved → used → cancelled). API integration layer (`LetterNumberApiController`) for documents to auto-request numbers on approval. Format: `{sequential}/{category_code}/{subject_code}/{project_code}/{month_roman}/{year}`. Integration points in `OfficialtravelController` and `RecruitmentRequestController`.

**Key Learning**: Centralized number generation systems require thread-safe sequential generation and clear lifecycle management. API-first approach enables future document types to integrate easily. Letter number should be reserved on approval initiation, marked as used only when document is finalized. Provide manual mark-as-used functionality for non-integrated documents.

**Files**: `LetterNumberController`, `LetterNumberApiController`, `letter_numbers` table, API integration in document controllers

---

### [006] Roster System Cycle-Based Restructure (2025-12-XX) ✅ COMPLETE

**Challenge**: Original roster system was too rigid, couldn't handle varying work patterns per level (PM/SPT: 6/2, SPV: 8/2, FM: 9/2, NS: 10/2). Single roster template didn't support employee-specific cycles.

**Solution**: Restructured to roster with multiple cycles per employee. `rosters` table holds employee assignment. `roster_details` table stores individual cycles with start dates and patterns. `roster_daily_status` table tracks daily work/off status. Removed rigid template system. `levels` table includes default roster pattern configuration.

**Key Learning**: Flexible roster systems need cycle-based architecture rather than fixed templates. Employee-level roster assignment with multiple cycles supports real-world complexity. Daily status tracking table enables historical queries and reporting. Level-based default patterns reduce data entry but allow per-employee customization.

**Files**: `RosterController`, `rosters` table, `roster_details` table, `roster_daily_status` table, `levels.roster_pattern`

---

### [007] Leave Request Cancellation Workflow (2025-10-XX) ✅ COMPLETE

**Challenge**: Approved leave requests sometimes need to be cancelled due to operational changes. Direct deletion would lose audit trail. No approval workflow for cancellations.

**Solution**: Implemented `leave_request_cancellations` table for cancellation requests. Workflow: employee submits cancellation request → approver reviews → approve/reject. Original leave request remains in database with status updated to 'cancelled' only after approval. Audit trail maintained for both request and cancellation.

**Key Learning**: Critical transactions (approved leaves) should never be directly deletable. Implement separate cancellation workflow with approval. Maintain complete audit trail of original request, cancellation request, and approval decision. Status field on original record should reflect cancellation only after approval.

**Files**: `LeaveRequestController` cancellation methods, `leave_request_cancellations` table, `leave_requests.status` enum

---

### [008] Bulk Periodic Leave Request System (2025-11-XX) ✅ COMPLETE

**Challenge**: Group 2 projects require periodic leave requests for multiple employees simultaneously. Creating individual requests was time-consuming.

**Solution**: Implemented bulk leave request creation with `batch_id` tracking. System identifies employees due for periodic leave based on roster cycle end dates. Single form creates multiple leave requests with same parameters. Bulk approval preview before submission. All requests in batch share same `batch_id` for easy tracking.

**Key Learning**: Bulk operations require batch tracking for auditability and batch cancellation. Preview functionality critical for user confidence before creating many records. Filter employees by eligibility criteria before showing in bulk form (e.g., due for periodic leave). Provide batch summary after creation (X created successfully, Y failed with reasons).

**Files**: `BulkLeaveRequestController`, `leave_requests.batch_id`, batch cancel methods

---

### [009] Employee Self-Service Registration System (2025-06-XX) ✅ COMPLETE

**Challenge**: HR spends significant time manually entering new employee data. Employees have accurate personal information but no system access during onboarding.

**Solution**: Built token-based invitation system. HR generates invitation tokens for new employees via email. Employees access registration form using token link. Self-service form collects personal data, family members, education, documents. HR reviews and approves/rejects submissions. Upon approval, employee record created and user account activated.

**Key Learning**: Token-based invitations provide security without requiring pre-existing accounts. Token expiration (configurable, e.g., 7 days) adds urgency. Rate limiting on public registration routes prevents abuse. Separate `employee_registrations` table preserves original submission even if rejected. Document upload in registration phase reduces HR workload.

**Files**: `EmployeeRegistrationController`, `EmployeeRegistrationAdminController`, `employee_registration_tokens` table, `employee_registrations` table, `registration_documents` table

---

### [010] Employee Bond Tracking and Violation Management (2025-09-XX) ✅ COMPLETE

**Challenge**: Company invests in employee training and scholarships but lacks system to track bond agreements and violations. Manual spreadsheet tracking is error-prone.

**Solution**: Implemented `employee_bonds` table with bond terms (amount, start date, end date, requirements). Expiry monitoring functionality alerts HR of upcoming bond completions. `bond_violations` table tracks violations with penalty calculations. Document attachment for bond agreements. Mark-as-completed workflow when bond fulfilled.

**Key Learning**: Bond tracking requires clear end dates and completion criteria. Penalty calculation should be pro-rated based on time remaining. Notification system (not yet implemented) would greatly improve compliance monitoring. Link bonds to termination workflow to check for violations before processing termination.

**Files**: `EmployeeBondController`, `BondViolationController`, `employee_bonds` table, `bond_violations` table

---

### [011] Man Power Plan (MPP) Module (2025-11-XX) ✅ COMPLETE

**Challenge**: Recruitment requests (FPTK) created ad-hoc without connection to annual workforce planning. No visibility into planned vs actual hiring.

**Solution**: Implemented MPP system for annual workforce planning. `man_power_plans` table holds plan header (year, project, status). `man_power_plan_details` table stores position requirements with quantities and agreement types. FPTK can reference MPP plan to show planned hiring. Recruitment sessions can link to MPP for tracking progress against plan.

**Key Learning**: Strategic workforce planning requires separation from tactical recruitment. MPP provides annual planning while FPTK handles immediate needs. Link but don't enforce relationship (some FPTKs are unplanned, which is okay). Track MPP fulfillment percentage to measure hiring against plan.

**Files**: `ManPowerPlanController`, `man_power_plans` table, `man_power_plan_details` table, `recruitment_sessions.mpp_detail_id`

---

### [012] 3-Level FPTK Approval Workflow (2025-08-XX) ✅ COMPLETE

**Challenge**: Original recruitment request approval was single-level, insufficient for organizational hierarchy. Need acknowledgment (HR checks completeness), PM approval (hiring manager), Director approval (final decision).

**Solution**: Implemented sequential 3-level approval workflow. Stage 1: HR acknowledges request completeness. Stage 2: PM approves workforce need and budget. Stage 3: Director gives final approval. Each stage has dedicated approval form and action methods. `approval_plans` table tracks progress through stages. Letter number assigned only after director approval.

**Key Learning**: Multi-level approval requires explicit stage tracking and sequential enforcement (can't skip stages). Each approval level should collect different information (HR: checklist, PM: justification, Director: budget approval). Separate controller methods per approval level improves clarity. Blade templates should show approval history and current stage clearly.

**Files**: `RecruitmentRequestController` approval methods, `approval_plans` table, approval stage views

---

### [013] Toast Helper Notification Standardization (2025-XX-XX) ✅ COMPLETE

**Challenge**: Inconsistent notification implementation across controllers. Some used toastr directly, some used SweetAlert, some had no notifications. Different styling and behavior.

**Solution**: Created global helper functions: `toast_success()`, `toast_error()`, `toast_warning()`, `toast_info()` in `app/Helpers/Common.php`. All controller methods return using toast helpers with English messages. Helpers flash messages to session, JavaScript displays them. Single configuration point for styling.

**Key Learning**: Global helpers enforce consistency and simplify maintenance. Session flash messages work with both redirects and AJAX responses. English messages provide consistency for multilingual future. Helper functions abstraction allows changing underlying library without modifying controllers. Document helper usage in architecture docs and memory for future developers.

**Files**: `app/Helpers/Common.php`, main layout JavaScript

---

### [014] Postman API MCP Integration (2025-XX-XX) ✅ COMPLETE

**Challenge**: Manual Postman collection updates time-consuming and frequently outdated. No automated sync between Laravel routes and API documentation.

**Solution**: Implemented Postman API MCP integration with workspace rules in `.cursor/rules/postman-api.mdc`. Standard workflow: get authenticated user → get workspace → get/create collection → sync routes. Collection "ARKA HERO - API" organized by modules with folder structure matching route groups. Variables for BASE_URL and TOKEN. MCP tools enable programmatic collection management.

**Key Learning**: API documentation as code is superior to manual updates. MCP integration enables automation triggers on route changes. Folder organization in Postman should match Laravel route groups for clarity. Environment variables (BASE_URL, TOKEN) enable easy environment switching. Document integration rules in workspace rules file for future AI assistance.

**Files**: `.cursor/rules/postman-api.mdc`, Postman collection "ARKA HERO - API"

---

### [015] Documentation Automation System (2026-01-09) ✅ COMPLETE

**Challenge**: Project documentation scattered, outdated, and inconsistent. No systematic approach to maintaining architecture docs, task tracking, and technical decisions.

**Solution**: Implemented comprehensive documentation automation system via `AGENTS.md`. Structure: `docs/architecture.md` (current system state), `docs/todo.md` (task tracking), `docs/decisions.md` (technical decisions), `docs/backlog.md` (future features), `MEMORY.md` (structured learnings). Documentation standards for each file type. Cross-referencing rules. AI agent protocol for automatic updates after code changes.

**Key Learning**: Living documentation requires systematic maintenance rules. Separate concerns: architecture (CURRENT state), decisions (WHY choices were made), todo (WHAT to work on), backlog (FUTURE ideas), memory (LEARNINGS). AI agents need explicit protocols to maintain documentation. Cross-reference between docs but avoid duplication. Update documentation immediately after significant changes, not as afterthought.

**Files**: `AGENTS.md`, `docs/architecture.md` (89 → 600+ lines), `docs/todo.md`, `docs/decisions.md`, `docs/backlog.md`, `MEMORY.md`

---

### [016] GA Modules Technical Analysis (2026-01-14) ✅ COMPLETE

**Challenge**: Need to expand ARKA HERO beyond HR modules to cover General Affair (GA) operations. Required comprehensive analysis of 5 major GA modules with complete database schema, API design, and integration patterns.

**Solution**: Created comprehensive technical analysis document (`docs/GA_MODULES_ANALYSIS.md`) covering 5 modules: Office Supplies (8 tables, dual approval workflow, stock opname), Vehicle Administration (5 tables, fuel tracking, maintenance, ArkFleet integration), Property Management System (4 tables, room reservations, check-in/out), Ticket Reservations (2 tables, travel booking), Meeting Room Reservations (3 tables, dual approval, supply consumption integration). Total 22 new database tables. All modules follow existing ARKA HERO patterns (UUID primary keys, Eloquent relationships, DataTables UI, RESTful API, approval workflow integration).

**Key Learning**: When expanding system into new domains (GA), critical to maintain architectural consistency with existing patterns while designing for domain-specific requirements. Comprehensive upfront analysis (155 pages) covering database schema, models, controllers, APIs, workflows, integration points, and implementation roadmap prevents architectural drift. Document common integration patterns (approval workflow, letter numbering, employee portals, notifications) as reusable components. GA modules have different workflow patterns than HR (e.g., dual approval for meeting rooms, stock opname variance calculation) but share core infrastructure.

**Files**: `docs/GA_MODULES_ANALYSIS.md` (83K tokens), `docs/backlog.md` updated with GA development priority

---

## Active Technical Debt

### TD-001: Limited Test Coverage ⚠️ PARTIAL

**Issue**: Current PHPUnit test coverage ~10%. Only 2 feature tests exist. Critical workflows (recruitment, leave calculations, approval flows) untested.

**Impact**: High risk of regressions during refactoring. Difficult to validate business logic. Slower development due to manual testing.

**Recommendation**: Prioritize test coverage for critical paths: recruitment session flow end-to-end, leave entitlement calculations, approval workflow state machine, letter number lifecycle. Target: 80% coverage on business logic. Effort: 3-4 weeks.

**Status**: Documented in backlog as high priority

---

### TD-002: Performance Optimization Needed ⚠️ PARTIAL

**Issue**: Some queries show N+1 patterns. Dashboard loads slowly with large datasets. No query result caching. Excel exports timeout with 10,000+ rows.

**Impact**: Degraded user experience with growing data. Potential timeout errors. Server resource inefficiency.

**Recommendation**: Add composite indexes on frequently queried columns. Implement eager loading for relationships. Add Redis caching for dashboard statistics. Queue large Excel exports. Effort: 1-2 weeks.

**Status**: Documented in backlog

---

### TD-003: Missing Production Infrastructure ❌ BLOCKED

**Issue**: Application runs only on local Laragon development environment. No production deployment plan, no CI/CD pipeline, no monitoring/alerting, no automated backups.

**Impact**: Cannot deploy to production safely. No disaster recovery plan. Manual deployment errors likely.

**Recommendation**: Set up production server with proper configuration. Implement automated daily database backups. Configure monitoring/alerting. Setup CI/CD pipeline with GitHub Actions. Effort: 2 weeks.

**Status**: Critical blocker for production launch

---

### TD-004: Security Hardening Needed ⚠️ PARTIAL

**Issue**: No security audit performed. No 2FA for admin accounts. No audit logging for sensitive data access. File uploads not scanned for viruses.

**Impact**: Potential security vulnerabilities. Difficult to investigate security incidents. Regulatory compliance concerns.

**Recommendation**: Conduct security audit (penetration testing). Implement 2FA for admin/HR roles. Add audit logging for sensitive operations. Integrate virus scanning for file uploads. Effort: 2-3 weeks.

**Status**: High priority security enhancement

---

## Lessons Learned

### Architecture Lessons

1. **Flexibility Through Configuration**: Database-driven configuration (enum fields, master tables) is superior to hardcoded values. Enables business users to adapt system without code changes.

2. **Separate Tables for Different Data Structures**: Don't force different stage data into single table. Separate tables enable optimization, clearer validation, better performance.

3. **API-First Design**: Building API endpoints alongside web features enables future mobile apps and integrations with minimal additional work.

4. **Audit Trail Everything**: Critical transactions (approvals, cancellations, deletions) require complete audit trail. Use status fields and separate transaction tables, not hard deletes.

5. **Lifecycle Management**: Resources with lifecycle (letter numbers: available → reserved → used) need explicit state management and status tracking.

### Development Lessons

1. **Helper Functions for Consistency**: Global helpers enforce patterns and simplify future changes. Examples: toast notifications, permission checks, date formatting.

2. **Documentation as Code**: Documentation automation systems work better than manual updates. AI agents need explicit protocols.

3. **Stage-Based Workflows**: Multi-stage processes (recruitment, approvals) benefit from explicit stage tracking, validation, and transition methods.

4. **Batch Operations Need Tracking**: Bulk actions require batch IDs for auditability and collective operations (batch cancel, batch status check).

5. **Test Before Scale**: Excel exports, queries, and batch operations should be tested with realistic data volumes. Many issues only appear at scale.

### Business Logic Lessons

1. **Complex Rules Need Documentation**: Document complex business rules (leave entitlement calculations, LSL eligibility) in dedicated technical flow documents, not just code comments.

2. **Validation at Multiple Levels**: Critical workflows need validation at database (constraints), application (Laravel validation), and UI (disabled states, visual indicators) levels.

3. **Approval Workflows Are Non-Linear**: Real-world approval workflows are complex. Design for parallel approvals, approval delegation, approval history, and stage rejection handling.

4. **Self-Service Reduces Workload**: Employee self-service features (registration, leave requests, travel tracking) significantly reduce HR administrative burden.

5. **Integration Points Need APIs**: Future integrations (payroll, biometric, third-party services) require clean API layer. Design API-first even if only web UI exists initially.

---

## Quick Reference Patterns

### Controller Patterns

-   `index()` - List with DataTables
-   `create()` - Show form
-   `store(Request $request)` - Save with validation
-   `show($id)` - Display single record
-   `edit($id)` - Show edit form
-   `update(Request $request, $id)` - Update with validation
-   `destroy($id)` - Soft delete or status change
-   `apiIndex()`, `apiStore()`, etc. - API versions with Sanctum auth
-   Return with `toast_success()` or `toast_error()`

### Approval Workflow Pattern

-   `submitForApproval()` - Create approval plan
-   `approve()` / `reject()` - Process approval decision
-   `approval_plans` table tracks progress
-   Post-approval actions: assign letter number, change status

### Export/Import Pattern

-   Export classes in `app/Exports/`
-   Import classes in `app/Imports/`
-   Queue large exports (>1000 rows)
-   Add proper error handling for date parsing

### Validation Pattern

-   Form Request classes for complex validation
-   Controller-level for simple cases
-   UI-level disabled states for workflow validation

---

**Last Memory Review**: 2026-01-14
**Next Memory Archive**: When file exceeds 500 lines (currently ~390 lines)
**Archive To**: `memory-2026-01.md`
