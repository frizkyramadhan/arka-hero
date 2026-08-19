**Purpose**: AI's persistent knowledge base for project context and learnings - ARKA HERO HRMS
**Last Updated**: 2026-08-19

### [047] Paid leave store/update silent fail (2026-08-19) ✅ FIXED

**Symptom**: Submit *Karyawan sendiri kawin* (`paid` `2.01`) returned to the create form with no validation message; request was not saved.

**Cause**: `findLeaveEntitlementForRequest()` was typed `int $employeeId`. `employee_id` is a UUID, so `(int)` became `0` and/or a later string pass threw `TypeError` (not caught by `catch (\Exception)` — user hits Back to a blank form). Failures also used `with(['total_days' => ...])` instead of `withErrors()`, and the forms had no `$errors` banner / `@error('total_days')`.

**Fix**: Keep UUID as `string`; reject missing entitlement; `rejectWithTotalDaysError()` uses `withErrors` + toast; show errors on HR/My Request create+edit; catch `\Throwable` with input flashed.

**Files**: `LeaveRequestController` (`findLeaveEntitlementForRequest`, `rejectWithTotalDaysError`, store/update), `leave-requests/{create,edit,my-create,my-edit}.blade.php`.

### [046] Leave Period date fence vs accounting window (2026-08-19) ✅ COMPLETE

**Rule**: Leave Period fences **Leave Date** only for Cuti Tahunan (`annual`) and Cuti Panjang (`lsl`). Cuti Dibayar (`paid`) and Izin Tanpa Upah (`unpaid`) are not date-fenced; remaining days of the **current** period still gate the dropdown and balance check. Server snapshots `leave_requests.leave_period` at create; approval/cancel charge that snapshot (`matchingEntitlement()`), not date containment. Field hidden on create/edit; still shown on detail/print/email/approval. Edit keeps snapshot unless employee or type changes.

**Files**: `LeaveType::usesLeavePeriodAsDateFence()`, `LeaveRequest::matchingEntitlement()`, `LeaveRequestController` (`findLeaveEntitlementForRequest`, `snapshotLeavePeriod`), `ApprovalRequestController`, `LeaveRequestCancellation`, leave-request create/edit (HR + My Request) + `partials/leave-period-date-fence-scripts`. Docs: `CONTEXT.md`, `docs/adr/0001-leave-period-date-fence.md`.

### [045] Pembinaan & Surat Peringatan (SP) (2026-08-07) ✅ COMPLETE

**Flow**: HCS creates Coaching/Counseling (3 mo, independent) or SP1–SP3 (6 mo) on `employee_disciplinaries`. Active SP sets floor — **new violation auto-escalates** (same/lower SP blocked; no return to pembinaan; jump-up allowed). UI pre-selects `suggest_next`. Expired by `disciplinary:expire` (+ lazy) clears floor. Active SP3 + next violation → terminate active `administrations` (create blocked).

**Escalation matrix (create while still valid)**: no SP → all types; SP1 → SP2|SP3; SP2 → SP3; SP3 → termination only. Coaching/Counseling do not set SP floor and are not sequential with each other.

**Permissions**: `php artisan db:seed --class=DisciplinaryPermissionSeeder` — HR write; PM/DM view-only show; `user` role gets `personal.disciplinary.view-own`.

**PP criteria seed**: `php artisan db:seed --class=DisciplinaryCriteriaSeeder` — Pasal 22 ayat (5) Counseling 6.a–p (16), ayat (6) SP1 7.a–r (18), ayat (8) SP Pertama & Terakhir 10.a–k (11). `sanction_type` includes `counseling`.

**UI**: Employee Management → **Disciplinary** (combined Coaching/Counseling + SP CRUD, English UI). Master Data → **PP Criteria**. Employee pick by Name + NIK KTP. Personal → **My Disciplinary Record** (list+detail only) + My Dashboard widget. Index/my-records use custom filters only (`searching: false`, `dom: 'rtip'`) including **PP Criteria** (`criterion_id`); Export passes the same filter query string (status/type/criterion/employee/dates) into `filteredQuery` — no filters = all rows. Index has **Export / Import** (Excel); export column `Imported (doc later)` (Yes/No) reflects `imported_at` (Excel-imported rows may upload supporting document later; normal create requires document at create time). Export also includes `remaining_days` after `end_date` (blank when not active, same idea as list `-`).

**Files**: `DisciplinaryService`, `EmployeeDisciplinaryController`, `DisciplinaryCriterionController`, `EmployeeDisciplinaryExport`/`EmployeeDisciplinaryImport`, migrations `2026_08_07_0818*`, `2026_08_10_145500_add_imported_at_to_employee_disciplinaries_table`, `DisciplinaryCriteriaSeeder`.

### [044] Telegram Fuel Bot (off-network) (2026-08-05) ✅ COMPLETE

**Flow**: Whitelist in HERO (`fuel_bot_subscribers`) → driver sends SPBU photo to Telegram bot → OpenRouter AI extract → YA/TIDAK confirm → `FuelBotIngestService` creates `fuel_records` (`submitted`) → same office verify/claim path.

**Config**: `TELEGRAM_FUEL_BOT_TOKEN`, `TELEGRAM_FUEL_BOT_WEBHOOK_SECRET`, plus existing `OPENROUTER_*`. Webhook: `POST /api/v1/telegram/fuel-bot/webhook` (skips `X-API-Key`; uses Telegram secret). Set: `php artisan telegram:fuel-bot-webhook set {publicHttpsUrl}`. Seed: `FuelBotPermissionSeeder`.

**Files**: `TelegramFuelBotHandler`, `FuelBotIngestService`, `FuelBotSubscriberController`, `Api\V1\FuelBotApiController`, migration `2026_08_05_090000_create_fuel_bot_tables`.

**Activity log UI**: `/fuel-bot-logs` (permission `fuel-bot-logs.show`) lists `fuel_bot_submissions` with status filter/date/search, stats cards, and a detail page showing parsed JSON + raw inbox photo (`fuel_bot_inbox/...` on the `private` disk) + link to the created fuel record. Sidebar SYSTEMS now groups **Fuel Bot → Whitelist / Activity Log**. Status labels/colors live on `FuelBotSubmission::statusLabels()`/`statusColors()` so table, badge, and filter stay in sync.

### [043] Driver Fuel: Photo + OpenRouter → Verify → Claim (2026-08-04) ✅ COMPLETE

**Flow**: My Features Fuel Log → scan SPBU nota (`OpenRouterReceiptParser`) or manual → `submitted` → GAMMA Pending Verification → `verified` → bundle `fuel_claims` (`draft`→`ready`) → external app `GET/PUT /api/v1/fuel-claims*`. While a claim is `draft`, users with `fuel-claims.edit` can add verified/unclaimed receipts, edit receipt fields (modal), or remove receipts; totals are recalculated transactionally and removed receipts return to `verified`. Claim print (`fuel-claims.print`) renders A4 portrait sheets with receipt photos in a balanced 3×3 grid (9/page).

**Config**: `OPENROUTER_API_KEY` (+ optional `OPENROUTER_MODEL`). Empty key disables AI; manual still works. Seed: `php artisan db:seed --class=FuelWorkflowPermissionSeeder`.

**PWA**: `public/manifest.webmanifest` + `public/sw.js` (online-first). Postman: `docs/postman/fuel-claims-api.json` (live MCP sync needs valid Postman API key).

**Files**: `FuelRecordController`, `FuelClaimController`, `Api\V1\FuelClaimApiController`, `config/openrouter.php`

### [042] Leave Requests DataTables 403 (2026-08-03) ✅ FIXED

**Challenge**: Some users saw DataTables Ajax error on Leave Requests; console showed **403** on server-side JSON (`leave/requests/data`) while the HTML page still loaded.

**Cause**: `AppServiceProvider` calls `URL::forceRootUrl(APP_URL)`. Absolute `route()` URLs in DataTables AJAX can point at the other entry (`:8080` vs `:80/arka-hero/`). Different origin → session cookie not sent → Spatie `PermissionMiddleware` throws `UnauthorizedException::notLoggedIn()` (**403**).

**Fix**: Leave DataTables AJAX uses (1) URL derived from `window.location.pathname` + `/data`, (2) **POST** + CSRF so `columns[n][...]` is not in the query string (WAF/ModSecurity often returns HTTP 403 on those GET params). Routes: `match(['get','post'], …/data)`. Handler returns real HTTP 403 JSON for AJAX unauthorized.

**Files**: `leave-requests/index.blade.php`, `my-requests.blade.php`, `routes/web.php`, `app/Exceptions/Handler.php`

---

## Docker Server Inventory (192.168.32.146) — no secrets

**Host**: `saphire-one` · SSH user `skyone` · Local SSH alias **`arka-docker`** (key `~/.ssh/id_ed25519_arka_deploy`)  
**Stack**: `/home/skyone/stack` · Compose: `docker-compose.yml` · Network: `stack_appnet`  
**Bind**: `~/stack/apps` → `/var/www` in nginx + all PHP-FPM containers

| Container            | Role                   | Published ports       |
| -------------------- | ---------------------- | --------------------- |
| `stack-nginx-1`      | nginx 1.27             | `80`, `8080`, `8081`  |
| `stack-php74-1`      | PHP 7.4-FPM            | (internal 9000)       |
| `stack-php81-1`      | PHP 8.1-FPM            | (internal 9000)       |
| `stack-php82-1`      | PHP 8.2-FPM            | (internal 9000)       |
| `stack-mysql-1`      | MySQL 8.0              | `127.0.0.1:3306` only |
| `stack-phpmyadmin-1` | phpMyAdmin via `/pma/` | (via nginx)           |
| `stack-arka-fms`     | Node ARKA FMS          | `3000`                |

**Apps**

| App           | Host path                   | Container workdir                               | URL                                | DB dir name |
| ------------- | --------------------------- | ----------------------------------------------- | ---------------------------------- | ----------- |
| **arka-hero** | `apps/app82/arka-hero`      | `/var/www/app82/arka-hero` on `stack-php82-1`   | `:8080` (+ `/arka-hero/` on `:80`) | `arka_hero` |
| irr-support   | `apps/app81/irr-support`    | `/var/www/app81/irr-support` on `stack-php81-1` | `:8081`                            | `irr5`      |
| arka-fms      | `apps/app81/arka-fms`       | Node image `stack-arka-fms`                     | `:3000`                            | `arka_fms`  |
| stubs         | `apps/app{74,81,82}/public` | path prefixes `/app74/` `/app81/` `/app82/`     | `:80`                              | `appdb`     |

**Deploy HERO**: user says `deploy` → commit/push if needed → `ssh arka-docker 'cd .../arka-hero && git pull --ff-only'` → `docker exec -w /var/www/app82/arka-hero stack-php82-1 php artisan …`  
**Rule**: `.cursor/rules/deploy.mdc` · Never copy compose/DB/JWT passwords into this file.

---

## Memory Maintenance Guidelines

### Structure Standards

- Entry Format: ### [ID] [Title (YYYY-MM-DD)] ✅ STATUS
- Required Fields: Date, Challenge/Decision, Solution, Key Learning
- Length Limit: 3-6 lines per entry (excluding sub-bullets)
- Status Indicators: ✅ COMPLETE, ⚠️ PARTIAL, ❌ BLOCKED

### Content Guidelines

- Focus: Architecture decisions, critical bugs, security fixes, major technical challenges
- Exclude: Routine features, minor bug fixes, documentation updates
- Learning: Each entry must include actionable learning or decision rationale
- Redundancy: Remove duplicate information, consolidate similar issues

### File Management

- Archive Trigger: When file exceeds 500 lines or 6 months old
- Archive Format: `memory-YYYY-MM.md` (e.g., `memory-2025-01.md`)
- New File: Start fresh with current date and carry forward only active decisions

---

## Project Memory Entries - ARKA HERO HRMS

### [036] Form of Assignment (FOA) requestor → driver (2026-08-11) ✅ COMPLETE

**Challenge**: Digitalize Form of Assignment (ARKA/HCS/IV/04.02) using existing `vehicles` master: requestor prepares trip assignment, prints for driver; driver logs jam/KM, adds destinations (project or external), returns and closes at origin.

**Solution**: Tables `vehicle_assignments` / `vehicle_assignment_stops` / `vehicle_assignment_passengers`. Status `draft→issued→in_progress→closed`. Destinations mirror LOT (`destination` + `is_manual`). Driver portal under My Features (`my-trips`). On close, bump `vehicles.odometer` if return arrive KM is greater. FOA No = letter number (`FOA0001`); draft keeps letter reserved, Issue marks used. **Assignment Information** card shared via `partials/assignment-info-card` on admin show + driver my-show. Issued/in-progress **Edit Destinations** (locked jam/KM legs) shared via `partials/issued-destinations-adjust-form` on admin show + driver my-show right column (`PATCH …/destinations`). **Close at Origin** shared via `partials/close-at-origin-form` (admin `POST …/close` + `vehicle-assignments.edit`; driver `close-own`). **My Trips list** UI matches personal DataTables pages (breadcrumb, accordion Filter, `table-striped` + `dom: 'rtip'`), same pattern as overtime/leave my-requests. **Nav**: "My Form of Assignment" gated only with `@can('personal.vehicle-assignments.view-own')` (not `update-trip` alone); list/show middleware already `view-own`; update-trip/close-own stay on action routes. Admin FOA uses `vehicle-assignments.show`.

**Key Learning**: Reuse Official Travel stop UI pattern (Select2 project vs manual checkbox) rather than inventing a third destination model. Keep `vehicles.pic` / `assigned_to` untouched — FOA is trip log, not long-term assignment. Mirror RCR letter flow (`smart-letter-number-selector` + reserved until Issue).

**Files**: `VehicleAssignmentController`, models `VehicleAssignment*`, `VehicleAssignmentPermissionSeeder`, `FoaLetterCategorySeeder`, `docs/VEHICLE_ASSIGNMENT_FOA_DESIGN.md`, sidebar GAMMA + My Features

---

### [035] Vehicle Administration (GAMMA) without maintenance (2026-08-03) ✅ COMPLETE

**Challenge**: Need Light Vehicle monitoring (NoPol, Kode, PIC, STNK/PKB/KIR expiry, lokasi, keterangan) under GAMMA, with Kode from ArkFleet.

**Solution**: Local CRUD `vehicles` + `vehicle_documents` + `fuel_records`; Kode via `ArkFleetClient` `GET /api/equipments` filtered `plant_group_id=3` (`unit_no`→kode, `nomor_polisi`→plate). Nav under GAMMA. No maintenance module.

**Key Learning**: ArkFleet sample uses plant_group **"Light Vehicles"** (plural); map `unit_no` not a separate "kode" field. Seeder: `VehiclePermissionSeeder` (manual). Config: `config/ark_fleet.php`.

**Documents (2026-08-03)**: Single table `vehicle_documents` includes file columns (`file_path`, `file_name`, `file_size`, `file_uploaded_at`). No `vehicle_document_revisions` table. Edit modal updates metadata + optional file replace (deletes previous private-disk file). Download: `vehicles/{vehicle}/documents/{document}/download`. Status badges: active=success, expired=danger, pending_renewal=warning, archived=secondary. Actions: Edit + dropdown (Download/Delete).

**API validity (2026-08-03)**: External read API under `/api/v1/` with standard `X-API-Key` / Bearer (`ValidateApiKey`). Controller: `Api\V1\VehicleApiController`. Endpoints: `GET /api/v1/vehicles` (paginated + STNK/PKB/KIR summary), `GET /api/v1/vehicles/{id}`, `GET /api/v1/vehicle-documents/expiring?days=30`. Envelope: `{ success, data, meta }`.

**List filters / Excel (2026-08-03)**: Index filters = Search (plate/code/PIC/remarks), Status, Location, Validity (expired / expiring / valid / missing) + days. Export/import via Maatwebsite (`VehicleExport` / `VehicleImport`); upsert by `kode` then `license_plate`; syncs STNK/PKB/KIR expiry. Permissions: export/template=`vehicles.show`, import=`vehicles.create|edit`.

**Files**: `VehicleController`, `VehicleDocumentController`, `FuelRecordController`, `ArkFleetClient`, migrations `2026_08_03_090*`, sidebar GAMMA Kendaraan

---

### [034] SPM letter number category-specific fields (2026-07-31) ✅ COMPLETE

**Challenge**: Create/edit letter number for category SPM (Surat Perjanjian Magang) needed internship period, placement department, and educational institution — no SPM dynamic fields existed.

**Solution**: Follow PKWT/PAR pattern — `#spm-template` + `case 'SPM'` validation; reuse `start_date`/`end_date` for periode; new `department_id` FK + `educational_institution`; whitelist in `ManagesLetterNumberForm`; show/export/import updated.

**Key Learning**: Category-specific letter fields are driven by `category_code` string switches + JS templates into `#dynamic-fields`; new columns must be listed in `letterNumberFormAttributeNames()` or payload drops them.

**Files**: migration `add_spm_fields_to_letter_numbers_table`, `LetterNumberController`, create/edit/show blades, `LetterAdministrationExport`/`Import`

---

### [032] Docker deploy access + stack inventory (2026-07-29) ✅ COMPLETE

**Challenge**: Manual deploy (push → SSH password → git pull → docker exec artisan); multi-app stack needs documented layout for future deploys without storing credentials.

**Solution**: Passwordless SSH alias `arka-docker`; Cursor rule `.cursor/rules/deploy.mdc`; inventory section above (paths/ports/containers only). Password used once for key install — not stored in repo.

**Key Learning**: Host `~/stack/apps` mounts to `/var/www`; HERO artisan workdir is `/var/www/app82/arka-hero` in `stack-php82-1`. Never paste server secrets into MEMORY/rules.

**Files**: `.cursor/rules/deploy.mdc`, `MEMORY.md` inventory, `docs/architecture.md` Deployment

---

### [033] Leave detail blocked updateApprovers when plans empty (2026-07-29) ✅ COMPLETE

**Challenge**: `26LV-00324` pending with Herry/Rachman shown, but no Update Approvers form. Approver badges absent → `approval_plans` empty; `canChangeApprovers` required pending plans.

**Solution**: `hasPendingApprovers` also true when status pending and zero plans; `updateApprovers` syncs even if `manual_approvers` unchanged when plans missing.

**Key Learning**: View-mode without Pending/Approved badges = no plan rows. Gate must allow recreating plans, not only editing existing pending steps.

**Files**: `LeaveRequest::hasPendingApprovers`, `LeaveRequestController::updateApprovers`

---

### [032] Edit leave: entitlementData null + Invalid date (2026-07-29) ✅ COMPLETE

**Challenge**: Edit leave JS crashed on `window.entitlementData.remaining_days` when value was `null`; Leave Date showed "Invalid date".

**Solution**: Null-safe check in `lsl-flexible-scripts`; edit picker now uses `.remove()` (not invalid `daterangepicker('destroy')`), strict `YYYY-MM-DD` parse, restore display after recreate, expand min/max to include existing dates.

**Key Learning**: `typeof null === 'object'`; daterangepicker has no `'destroy'` API—use `.data('daterangepicker').remove()`.

**Files**: `lsl-flexible-scripts.blade.php`, `leave-requests/edit.blade.php`, `my-edit.blade.php`

---

### [032] Outlook/Thunderbird-safe email + browser preview (2026-07-30) ✅ COMPLETE

**Challenge**: Approval email layout and CTA needed reliable rendering in Outlook desktop and Thunderbird, plus a way to inspect real document emails without delivery.

**Solution**: Email Blade now uses presentation tables, inline styles, Outlook 96-DPI settings, and a VML CTA fallback. Administrator Debug Email Notify can render the exact template in a new browser tab through `debug.email-notifications.preview`; preview never invokes the mail transport. Recipient display names for the greeting, mail `To` header, debug send, and preview come strictly from `users.name` (email fallback only when no matching user exists). Approver/reminder CTAs open `/approval/requests`; approved/rejected CTAs open the document show URL (both rebased via `DOCUMENT_NOTIFICATIONS_BASE_URL`). Production mails implement `ShouldQueue` with `QUEUE_CONNECTION=database` (no Redis); host crontab runs `schedule:run` + short `queue:work --stop-when-empty` inside `stack-php82-1`. Audit: `email_queued` on enqueue, `email_sent`/`email_failed` via Notification listeners. Idempotency table `document_notification_sends`. Daily overdue reminder (`documents:remind-pending-approvals`, default 3 days). Optional CC from config on approved/rejected. HTML+text multipart. Brand logo is `public/images/logo_2.jpg`: SMTP embeds it as `cid:arka-logo` (avoids broken remote LAN image loads and reduces Thunderbird junk signals); browser preview still uses the absolute `DOCUMENT_NOTIFICATIONS_BASE_URL` URL. Hidden preheader removed; `List-Unsubscribe` + `Auto-Submitted` headers added. Debug litmus + Activity Log delivery metrics. Activity statistics distinguish live `Pending Queue` (current `jobs` rows) from 7-day audit events (`Queued Events`, `Delivered`, `Failed`, `Skipped`). Production send toggle: `DOCUMENT_NOTIFICATIONS_ENABLED`.

**Key Learning**: Keep browser preview and delivered email on one `mailViewData()` source so debug output cannot drift from production markup. Env boolean must use `filter_var(..., FILTER_VALIDATE_BOOLEAN)` so `false` actually disables. Document-type email partials should mirror approval-request fields, not a generic Date/Purpose stub. Shared `ui_tokens.php` keeps compact/cozy density consistent; do not rely on Blade `@include` to set style variables for the parent. On Docker php-FPM without Redis, database queues + minute crontab workers are enough; log `email_queued` separately from `email_sent`.

## **Files**: `DocumentApprovalNotification`, `DocumentNotificationService`, `DocumentNotificationSend`, `RemindPendingApprovalsCommand`, `LogDocumentNotificationSent`/`Failed`, `DebugEmailNotificationController`, `ActivityLogController`, `config/document_notifications.php`, `emails/documents/approval.blade.php`, `emails/documents/approval-text.blade.php`, `public/images/logo_2.jpg`, migrations `jobs` + `document_notification_sends`, `docs/docker-reference.md`

### [031] Leave update wiped approval_plans without recreate (2026-07-29) ✅ COMPLETE

**Challenge**: Editing leave `b58a14ce-…` to add approver saved `manual_approvers` but approvals vanished from inbox.

**Solution**: Leave now mirrors FPTK: `canChangeApprovers` / `getLockedApproverIds`, show-page `updateApprovers` (pending steps only), edit forms lock decided approvers, `syncPendingLeaveApprovers` keeps approved/rejected rows. Restored plans for that request.

**Key Learning**: Never full-recreate approval_plans on edit when any step is decided—only replace `status=0` rows (FPTK/LOT pattern).

**Files**: `LeaveRequest`, `LeaveRequestController::updateApprovers`, `leave.requests.update-approvers`, show/edit blades

---

### [030] Document email notifications + Spatie activity audit (2026-07-29) ✅ COMPLETE

**Challenge**: No email on document approval across 7 types; need reusable mail + audit trail with UI.

**Solution**: `NotifiableDocument` contract on all approval docs; `DocumentNotificationService` hooked from `create_manual_approval_plan` + `ApprovalRequestController`/`ApprovalPlanController` decisions; Spatie `activity_log` (`document_approval` / `document_email`); SYSTEMS → Activity Logs UI (`activity-logs.show`). `subject_id`/`causer_id` altered to string for UUID documents.

**Key Learning**: Centralize notify/audit in approval hub only—never per-module Mail::. UUID morphs need string IDs on `activity_log`. Email/audit failures must never break approve/reject.

**Files**: `DocumentNotificationService`, `DocumentAuditLogger`, `DocumentApprovalNotification`, `ActivityLogController`, `config/document_notifications.php`

---

### [029] FPTK & MPP HOLD for Time-to-Hire pause (2026-07-28) ✅ COMPLETE

**Challenge**: Management can slow recruitment; calendar Time to Hire inflated during intentional freeze. Need HR-only HOLD that freezes ops and excludes hold intervals from metrics.

**Solution**: Status `on_hold` + history tables (`recruitment_request_holds`, `man_power_plan_holds`). FPTK holdable when `submitted`/`approved`; MPP when `active`. Permissions `recruitment-requests.hold` / `mpp.hold` created manually (no seeder). Freeze: apply/advance/approve/close blocked. Reports/dashboard subtract hold overlap via `adjustedDaysBetween()`.

**Key Learning**: Persist hold intervals (not just flags) so overlapping SLA/TTH windows can subtract accurately across multiple hold/unhold cycles. Restore `status_before_hold` on Unhold.

**Files**: `RecruitmentRequest`, `ManPowerPlan`, `RecruitmentReportController`, `DashboardController@recruitment`, hold migrations 2026_07_28_140000\*

---

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

### [018] Approval Plan Decision Timestamp Overwrite (2026-07-24) ✅ COMPLETE

**Challenge**: FPTK (and other docs) with multiple approvers showed the same datetime on every approval step. Early approvers (e.g. early July) appeared as the final close date (e.g. 24 July).

**Solution**: Root cause was `closeAllApprovalPlans()` / `closeOpenApprovalPlans()` mass-updating `is_open`, which Eloquent also refreshed `updated_at` on every open plan — including already-approved ones. UI used `updated_at` as the decision time. Fix: add `approval_plans.acted_at` (set on status → approved/rejected via model boot), close `is_open` via query builder without touching timestamps, display via `ApprovalPlan::decisionAt()`.

**Key Learning**: Never use `updated_at` as a business event timestamp when other lifecycle updates (close, is_read) touch the same row. Store a dedicated `acted_at`. Historical rows already overwritten cannot be recovered without audit logs; backfill from `updated_at` is best-effort only.

**Files**: `ApprovalPlan`, migration `2026_07_24_170000_add_acted_at_to_approval_plans_table`, `ApprovalRequestController`, `ApprovalPlanController`, approval UI blades, `RecruitmentReportController`

---

### [017] Room & Consumption Request (RCR) Module (2026-07-20) ✅ COMPLETE

**Challenge**: Need meeting room booking + consumption form matching paper RCR, with letter numbers, manual approval, and future Zoom/IT WO — without waiting on Office Supplies or custom dual-status approval from GA analysis.

**Solution**: Implemented `meeting_rooms` + `room_consumption_requests` + `room_consumption_items`. Manual approval via `room_consumption_request` document type; Reg. No format mirrors FPTK (`0001/HCS-000H/RCR/I/2026`); letter category RCR created manually; Zoom stub columns + IT WO CI3 contract in design doc for Phase 2.

**Phase 2 (2026-07-20)**: CI3 API on `arka-rest-server` — `POST/GET /api/v1/zoom-meeting-requests` creates `wo` with `id_kategori=8` / `id_subkat=35`, all HERO fields packed into `wo.issue` (no ALTER). Idempotency via `id={uuid}` marker in issue. GET parses Meeting ID from `activity`/`komentar`. HERO: `ItWoZoomClient`, auto-dispatch on RCR approval, callback `POST /api/v1/integrations/it-wo/zoom-callback`. Base URL: `http://192.168.32.37/arka-rest-server`.

**Zoom availability on RCR form (2026-07-24)**: When **Need Zoom Meeting ID** is checked, form shows **Zoom Meeting ID Availability** (accounts 131/132/134) via `GET /api/v1/zoom-meeting-availability` (same logic as IT WO `Zoom_m` + `zoom_parser_helper`). HERO proxy: `room-consumption-requests/zoom-availability`.

**Zoom time-range parsing (2026-07-27)**: `zoom_parse_sessions_from_chunk` treats ranges (`08.30-12.00`, `s/d`/`sampai`/`hingga`/`to`, `jam 9 - jam 14`) as **one** session `HH:MM-HH:MM`; WIB/WITA dual = one session (prefer WITA); `Sesi N : HH:MM` stays multi-session. Synced helper: IT WO, `arka-rest-server`, HERO `app/Support/Zoom/zoom_parser_helper.php`.

**Zoom WIB/WITA paren + 1h (2026-07-27)**: Dual TZ also covers `14.30 WITA (13.30 WIB)`, `13.30 WIB (14.30 WITA)`, slash/dash pairs; processed **before** ranges. Heuristic: if both WIB and WITA appear and two nearby clocks differ by **60 minutes**, treat as one timezone dual (prefer WITA / later clock). Helpers: `zoom_extract_wib_wita_duals`, `zoom_pick_wita_from_pair`.

**RCR My Request REQ (2026-07-27)**: My Room & Consumption mirrors My Official Travel: temporary `REQxxxxx` (`submitted_by_user`), no letter on employee form; HR confirms via admin edit (letter RCR + approvers → formal Reg. No preview live on form). Column `submitted_by_user` on `room_consumption_requests`; filter status `pending_hr` on admin list. User manual `docs/user-manual/19-room-consumption-management.md` §3.4 / §5.2–5.3 updated (2026-07-28).

**RCR start_date / end_date (2026-07-29)**: Replaced single `meeting_date` with `start_date` + `end_date` (migration backfills both from old date). Conflict check uses datetime range overlap. Reg. No roman month from `start_date`. IT WO payload keeps `meeting_date` (= start) and adds `end_date`.

**RCR calendar range visibility (2026-07-30)**: Multi-day dashboard events include a compact date-range prefix in the event title, a full Indonesian period in the hover tooltip, and a striped/thicker event bar (`rcr-multi-day-event`) spanning `start_date` through `end_date`.

**RCR letter draft reserved (2026-07-29)**: Draft RCR keeps letter number **reserved** (not `used`); `used` only on submit. Edit form selector passes `include_id` so current letter populates. Saving draft releases letter if previously marked used by this RCR.

**IT WO live test (2026-07-20)**: GET by id works (e.g. 8183). POST create works when `requester_nik` exists in `it_wo.karyawan` and `project_code` exists in `it_wo.project`. Admin NIK `17806` not in IT WO master → API 400. Detail button **Request Zoom via IT WO** posts real JSON via `Http::asJson()`. Sample RCR `a24d3339-…` linked to WO `0008189/WO/ITY/VII/2026` (created with Eko NIK 10917 for smoke test).

**Auto-provision karyawan (2026-07-22)**: ~91% HERO NIKs missing from `it_wo.karyawan`. Rest-server `resolveKaryawan()`: NIK → email (rehire: update nik/nama) → INSERT new with HERO position/department (`resolveJabatanId` creates `departemen`/`jabatan` if missing; fallback `ARKA HERO User`/`ARKA HERO`). Same for acknowledge (first RCR approver). HERO payload adds position/department + acknowledge name/email. **Names use `employees.fullname`** (fallback `users.name`). Acknowledge also resolves by email when HERO NIK missing. Rehire nama update only when NIK actually changes.

**Key Files**: `docs/ROOM_CONSUMPTION_REQUEST_DESIGN.md`, `app/Services/ItWoZoomClient.php`, `arka-rest-server/.../Zoom_meeting_requests.php`

**Key Learning**: Prefer form-aligned fixed consumption enums over premature stock coupling. Reuse FPTK number formatting and overtime/LOT manual approval wiring rather than inventing GA-specific approval statuses. For cross-system identity, match by email for rehire NIK changes and auto-provision org masters by name rather than hard-blocking WO create.

**Files**: `docs/ROOM_CONSUMPTION_REQUEST_DESIGN.md`, `MeetingRoom*`, `RoomConsumptionRequest*`, `RoomConsumptionPermissionSeeder`, `ApprovalPlanController`, `ApprovalRequestController`

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

**Update (2026-02)**: Kebijakan perusahaan berubah — penalty tidak lagi dihitung proporsional (prorate), melainkan **jumlah tetap** sebesar total biaya pelatihan (investment value) sampai berakhirnya ikatan dinas. Perubahan hanya di logika `EmployeeBond::calculateProratePenalty()`; struktur tabel dan response API/UI tidak diubah. Data violation lama tetap mengikuti nilai yang sudah tersimpan.

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

### TD-003: Missing Production Infrastructure ⚠️ PARTIAL

**Issue**: Staging Docker at `192.168.32.146` is documented + deployable via Cursor (`deploy` / `.cursor/rules/deploy.mdc`). Still no formal production CI/CD, monitoring/alerting, or automated backup runbook beyond `stack/backup/`.

**Impact**: Staging deploys are safer; true production hardening and disaster recovery remain open.

**Recommendation**: Formalize backups/monitoring; consider GitHub Actions later. Do not store server secrets in repo.

**Status**: Staging deploy OK; production CI/CD still open

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

- `index()` - List with DataTables
- `create()` - Show form
- `store(Request $request)` - Save with validation
- `show($id)` - Display single record
- `edit($id)` - Show edit form
- `update(Request $request, $id)` - Update with validation
- `destroy($id)` - Soft delete or status change
- `apiIndex()`, `apiStore()`, etc. - API versions with Sanctum auth
- Return with `toast_success()` or `toast_error()`

### Approval Workflow Pattern

- `submitForApproval()` - Create approval plan
- `approve()` / `reject()` - Process approval decision
- `approval_plans` table tracks progress
- Post-approval actions: assign letter number, change status

### Export/Import Pattern

- Export classes in `app/Exports/`
- Import classes in `app/Imports/`
- Queue large exports (>1000 rows)
- Add proper error handling for date parsing

### Validation Pattern

- Form Request classes for complex validation
- Controller-level for simple cases
- UI-level disabled states for workflow validation

---

**Last Memory Review**: 2026-01-14
**Next Memory Archive**: When file exceeds 500 lines (currently ~390 lines)
**Archive To**: `memory-2026-01.md`
