# Form of Assignment (FOA) — Design

**Status**: Implemented (Phase 1)
**Last Updated**: 2026-08-11
**Document control (print)**: ARKA/HCS/IV/04.02 Rev.2

## Decisions

| Aspect | Decision |
|--------|----------|
| Portal | GAMMA requestor/admin + driver My Features |
| Numbering | Letter Number category **FOA** via `smart-letter-number-selector`; FOA No = letter number itself (`FOA0001`). Draft keeps letter **reserved**; **Issue** calls `markAsUsed('vehicle_assignment', id)` |
| Approval | None (not ApprovalPlan). Workflow: requestor issue → driver trip log → close at origin |
| Destinations | Mirror Official Travel: `destination` string + `is_manual` (project Select2 vs free text) |
| Stops | Dynamic `origin` / `destination` / `return` with jam + KM |
| Odometer | On `closed`, bump `vehicles.odometer` if return arrive KM is greater |
| Vehicle PIC | Does **not** change `vehicles.assigned_to` / `pic` |

## Roles

- **Requestor**: create header (date, driver, vehicle, origin, planned stops, passengers) → issue → print → hand to driver
- **Driver**: start trip, update jam/KM, add stops (project or manual, including return), close at origin
- **Admin**: full list, limited corrections, cancel, reprint

## Status flow

`draft` → `issued` → `in_progress` → `closed`  
Also: `cancelled` from `draft`/`issued` (requestor) or `in_progress` (admin)

## Schema

### `vehicle_assignments`

- `form_number`, `letter_number_id`, `letter_number`, `assignment_date`, driver fields, origin snapshot (`origin_destination`, `origin_is_manual`)
- `vehicle_id` + plate/kode snapshot, `project_id` nullable, `requested_by`, `status`, timestamps for issue/start/close

Letter: seed category/subject via `FoaLetterCategorySeeder`. Required on create/edit draft.

### `vehicle_assignment_stops`

Trip legs only — **no origin row**. Origin lives on `vehicle_assignments.origin_*` (header).

| Kolom | Arti |
| --- | --- |
| `sequence` | 0-based order of legs |
| `stop_type` | `destination` \| `return` |
| `destination` | label tujuan (project `CODE - NAME` atau manual) |
| `is_manual` | external flag |
| `depart_time` / `depart_km` | **Berangkat** (dari lokasi sebelumnya; baris pertama = dari origin) |
| `arrive_time` / `arrive_km` | **Tiba** di tujuan baris ini |

Paper-form labels (`legLabel()`):

- sequence 0 → `Jam Berangkat/Tiba`
- sequence 1 → `Jam Berangkat/Tiba Tujuan I`
- sequence 2 → `Jam Berangkat/Tiba Tujuan II`
- `return` → `Jam Berangkat/Tiba Pulang`

**Create/issue**: ≥1 destination stop (+ origin header).  
**Issued / in progress**: requestor/admin may adjust destinations (LOT-style); legs with jam/KM stay locked. Driver may add destination/return from My FOA.  
**Start trip**: isi `depart_*` pada leg pertama.  
**Close**: isi `arrive_*` pada leg pulang (auto-create `return` ke origin bila belum ada).

### `vehicle_assignment_passengers`

- `employee_id` nullable, `passenger_name`, `sort_order`

## Destination parity with LOT

Reuse pattern from `officialtravel_stops` + `stop-destinations-fields` partial:

- Internal: `is_manual=false`, label `{project_code} - {project_name}`
- External: `is_manual=true`, free text (min 3 chars)
- Request arrays: `stop_destinations[]` + `stop_destinations_manual[]`

FOA Phase 1 does **not** use LOT destination stamp / `UserProject` checkpoint gates; the assigned driver may update all stops on their FOA.

## Permissions

- `vehicle-assignments.{show,create,edit,delete,issue,print,cancel}`
- `personal.vehicle-assignments.{view-own,update-trip,close-own}`

Seeder: `VehicleAssignmentPermissionSeeder` (manual).

## Routes (web)

- `/vehicle-assignments` — admin CRUD / issue / print / cancel / adjust destinations / close at origin
- `/vehicle-assignments/my-trips*` — driver portal (start, trip log, adjust destinations, close)
- Shared UI: `partials/assignment-info-card` (admin show + my-show left column), `partials/issued-destinations-adjust-form`, `partials/close-at-origin-form` (admin show + my-show right column)

## Out of scope (Phase 1)

- Letter category FOA, ApprovalPlan, GPS, REST API, ArkFleet sync, long-term vehicle assign
