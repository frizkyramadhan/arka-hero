# Room & Consumption Request (RCR) — Design

**Status**: Implemented (Phase 1 + Phase 2 Zoom/IT WO API)
**Last Updated**: 2026-07-22
**Replaces**: Module 5 direction in `GA_MODULES_ANALYSIS.md` for approval & consumption model

## Decisions

| Aspect | Decision |
|--------|----------|
| Consumption | 4 fixed types (Coffee Break Pagi/Sore, Lunch, Dinner) + free-text description; no Office Supplies dependency |
| Portal | GA/Admin + employee My Features |
| Approval | `manual_approvers` + `create_manual_approval_plan('room_consumption_request', $id)` |
| Letter category | **RCR** — created manually in Letter Administration (not seeded) |
| Reg. No | `{seq}/HCS-{projectCode}/RCR/{romanMonth}/{year}` e.g. `0001/HCS-000H/RCR/I/2026` |
| Letter on save | Draft keeps letter **reserved**; mark **used** only on submit (`related_document_type=room_consumption_request`). Edit selector includes current letter via `include_id`. |
| Room location | `meeting_rooms.project_id` → `project_code` |
| Zoom / IT WO | Phase 2 — CI3 API at `http://192.168.32.37/arka-rest-server`; cat **8** / subcat **35**; details in `wo.issue`; no ALTER `wo` |

## Schema

### `meeting_rooms`

- `id` (UUID), `project_id`, `room_name`, `capacity`, `facilities` (text), `status` (`active`/`inactive`/`maintenance`), `notes`
- No `room_code`, no `has_zoom_support`

### `room_consumption_requests`

- Letter: `letter_number_id`, `letter_number`, `request_number` (formatted Reg. No)
- Room/org: `meeting_room_id`, `project_id`, `department_id`, `requested_by`
- Meeting: `meeting_title`, `start_date`, `end_date`, `start_time`, `end_time`, `attendees_count`, `facilities`
- `need_zoom`, `manual_approvers` (JSON), `status`, timestamps, `rejection_reason`, `notes`
- Zoom stubs: `it_wo_id`, `it_wo_number`, `zoom_meeting_id`, `zoom_topic`, `zoom_join_url`, `zoom_passcode`, `zoom_sync_status`

Statuses: `draft` → `submitted` → `approved` | `rejected` → `cancelled` | `completed`

### `room_consumption_items`

- `request_id`, `consumption_type` (`coffee_break_morning`, `coffee_break_afternoon`, `lunch`, `dinner`), `is_selected`, `description`
- Always 4 rows per request

## Reg. No formatting

Mirror FPTK (`RecruitmentRequestController`):

```
{numericPart}/HCS-{projectCode}/RCR/{romanMonth}/{year}
```

- Numeric part: strip `RCR` prefix from letter number, pad 4 digits
- Project code from request/room project
- Roman month + year from `start_date`

Helper: `RoomConsumptionRequest::formatRequestNumber()`

## Approval

Document type: `room_consumption_request`  
Suggested order (UX help text):
- HO / BO / APS: 1) DIV/DEPT HEAD → 2) HCS DIVISION MANAGER
- Site: 1) SPV/DEPT HEAD department masing-masing → 2) Project Manager  
Processed via My Approvals (`ApprovalRequestController`).

## Permissions

- `meeting-rooms.{show,create,edit,delete}`
- `room-consumption-requests.{show,create,edit,delete}`
- `personal.room-consumption.{view-own,create-own,edit-own,cancel-own}`

Seeder: `RoomConsumptionPermissionSeeder` (run manually).

## Routes (web)

- `/meeting-rooms` — master CRUD
- `/room-consumption-requests` — admin list/CRUD/print/submit/cancel
- `/room-consumption-requests/my-requests` — employee portal

## Phase 2 — IT WO Zoom Meeting ID (CodeIgniter 3)

**Status**: Implemented (CI3 API + HERO client)

### Trigger

1. **Create WO** — After RCR **submitted** (not draft) and `need_zoom = true`: `ItWoZoomClient::dispatchOnSubmit()` (`acknowledge` = first RCR approver). Manual Request Zoom / Sync Zoom remain on RCR show.
2. **Approve L1** — When **first** RCR approval step is approved (`approval_order` minimum): `ItWoZoomClient::syncApproveL1()` → IT WO `app_status_l1 = Approved`, `date_app_l1 = now()`. Does **not** wait for full RCR approval.
3. **Cancel WO** — When RCR is **rejected**: `ItWoZoomClient::syncCancelOnReject()` → IT WO `status = Canceled`, `app_status_l1 = Disapproved`, `date_app_l1 = now()`.

### Config (HERO `.env`)

```
IT_WO_BASE_URL=http://192.168.32.37/arka-rest-server
IT_WO_API_KEY={same key as rest-server keys table}
```

Default `IT_WO_BASE_URL` in `config/it_wo.php` is `http://192.168.32.37/arka-rest-server`. Empty base URL enables trial/mock mode.

### IT WO mapping (no schema change on `it_wo.wo`)

| Fixed | Value |
|-------|-------|
| `id_kategori` | **8** (`ZOOM MEETING ID`) |
| `id_subkat` | **35** (`ROOM MEETING ID`) |
| `asset` | `Company` |
| HERO details | Packed into **`wo.issue`** (varchar 1000) |

Issue format (readable):

```
[ARKA HERO - RCR]
Reg No: {request_number}
Topic: ...
Room: ...
Date: {Indonesian weekday date}
Time: {start}-{end}
Project: {project_code}
```

Idempotency: `LIKE '%Reg No: {request_number}%'` (or legacy `id={uuid}`) on `issue` (no new columns).

Zoom results: parsed from `activity.detail` / `komentar.message` (Meeting ID, Passcode, zoom.us URL) — same free-text process as IT UI today.

### Karyawan / Acknowledge auto-provision (2026-07-22)

Only ~9% of active HERO NIKs exist in `it_wo.karyawan`. Rest-server therefore **auto-provisions** missing people instead of hard-failing:

1. Lookup `karyawan` by NIK.
2. If missing: lookup by **email** (rehire / NIK change). If found → **update** `nik` + `nama` only (`id_jabatan` / `id_project` / `email` untouched).
3. If still missing and nik+nama+email complete → **INSERT** new `karyawan` using HERO `position_name` / `department_name`:
   - Resolve `departemen` by `nama_dept` (create if missing).
   - Resolve `jabatan` by `nama_jabatan` + `id_dept` (create if missing).
   - Fallback names if HERO sends empty position/department: `"ARKA HERO User"` / `"ARKA HERO"`.
4. Same flow for **acknowledge** (first RCR approver). Soft-null only if approver data is incomplete (no email) — WO still created.

No ALTER on `wo` / `karyawan` / `jabatan` / `departemen` schemas — data rows only.

### IT WO API contract (arka-rest-server)

Base: `http://192.168.32.37/arka-rest-server`  
Auth: `X-API-Key` (alias) **or** legacy `arka-key` + optional `X-Source: arka-hero`

| Method | URL |
|--------|-----|
| POST | `/api/v1/zoom-meeting-requests` |
| GET | `/api/v1/zoom-meeting-requests/{it_wo_id}` |
| GET | `/api/v1/zoom-meeting-availability?date=YYYY-MM-DD` — accounts 131/132/134 (same logic as IT WO widget) |
| PUT | `/api/v1/zoom-meeting-requests/{it_wo_id}` — sync approval (`action`) |
| DELETE | `/api/v1/zoom-meeting-requests/{it_wo_id}` (debug reset) |

**POST** create:

```json
{
  "source_system": "arka-hero",
  "source_document_type": "room_consumption_request",
  "source_document_id": "uuid",
  "source_document_number": "0001/HCS-000H/RCR/I/2026",
  "requester_nik": "...",
  "requester_name": "...",
  "requester_email": "...",
  "requester_position_name": "...",
  "requester_department_name": "...",
  "meeting_title": "...",
  "meeting_date": "2026-07-20",
  "end_date": "2026-07-21",
  "start_time": "09:00",
  "end_time": "11:00",
  "attendees_count": 12,
  "room_name": "Meeting Room A",
  "project_code": "000H",
  "notes": "optional",
  "acknowledge_nik": "...",
  "acknowledge_name": "...",
  "acknowledge_email": "...",
  "acknowledge_position_name": "...",
  "acknowledge_department_name": "..."
}
```

**Response 201:** `{ "success": true, "data": { "it_wo_id", "it_wo_number", "status": "open", "id_kategori": 8, "id_subkat": 35 } }`  
**Idempotent retry:** HTTP 200 + same data + `"idempotent": true`

**GET** when done: `zoom_topic`, `zoom_meeting_id`, `zoom_join_url`, `zoom_passcode` (parsed from IT WO activity/komentar), `status` (`open`/`processing`/`done`/`cancelled`)

**GET availability** (`/api/v1/zoom-meeting-availability?date=YYYY-MM-DD` or `/api/v1/zoom-meeting-requests/availability?date=YYYY-MM-DD`): same logic as IT WO `Zoom_m::get_availability` + `zoom_build_availability` (accounts **131 / 132 / 134** → Available / Booked / Unavailable All Day + schedule). HERO form shows this panel when **Need Zoom Meeting ID** is checked (`GET room-consumption-requests/zoom-availability`). If the rest-server endpoint returns **404** (not deployed yet), HERO falls back to read-only query on local MySQL `it_wo` via `ZoomAvailabilityService`.

**PUT** sync HERO approval → IT WO L1 / cancel:

```json
{ "action": "approve_l1" }
```

or

```json
{ "action": "cancel" }
```

- `approve_l1` → `app_status_l1 = Approved`, `date_app_l1 = now()` (idempotent if already Approved)
- `cancel` → `status = Canceled`, `app_status_l1 = Disapproved`, `date_app_l1 = now()`
- Response includes `app_status_l1`, `date_app_l1` in `data`

**Webhook (optional):** IT WO → `POST /api/v1/integrations/it-wo/zoom-callback` on HERO (API key header). HERO primarily uses **poll Sync Zoom**.

### CI3 files

- `arka-rest-server/application/controllers/api/Zoom_meeting_requests.php`
- `arka-rest-server/application/controllers/api/Zoom_meeting_availability.php`
- `arka-rest-server/application/models/Zoom_meeting_request_model.php` (`resolveKaryawan`, `resolveJabatanId`, `getAvailability`)
- `arka-rest-server/application/helpers/zoom_parser_helper.php` (ported from IT WO)
- Routes in `application/config/routes.php`

### HERO files

- `app/Services/ItWoZoomClient.php` (`dispatchOnSubmit`, `syncApproveL1`, `syncCancelOnReject`, `getZoomAvailability`)
- `app/Http/Controllers/Api/V1/ItWoZoomCallbackController.php`
- Auto-dispatch on submit: `RoomConsumptionRequestController`
- First-step L1 + reject cancel: `ApprovalRequestController` / `ApprovalPlanController`
- Form availability UI: `resources/views/room-consumption-requests/form.blade.php`

## Out of scope (Phase 1 leftovers)

- Letter category RCR seeder
- Office Supplies stock
- Full calendar UI
- Server-side PDF library
- ALTER TABLE on `it_wo.wo`
