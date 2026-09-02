# Domain

Glossary only. No implementation details.

## Leave Type Category

A classification of a leave type. The four categories in use are **Cuti Tahunan**, **Cuti Panjang**, **Cuti Dibayar**, and **Izin Tanpa Upah**. Rules about whether a Leave Period fences dates follow the category, not the display name of an individual type.

## Cuti Tahunan

Annual leave. A Leave Request of this category is date-fenced: its date range must lie inside the Leave Period that funds it.

## Cuti Panjang

Long-service leave. Date-fenced the same way as Cuti Tahunan.

## Cuti Dibayar

Paid leave as a category — many named types (marriage, sick, childbirth, pilgrimage, and so on), not a single type. A Leave Request of this category is not date-fenced by Leave Period. Leave Period is only the accounting window whose remaining days are charged.

## Izin Tanpa Upah

Unpaid leave. Not date-fenced by Leave Period. Leave Period is only the accounting window whose remaining days are charged.

## Leave Period

The entitlement window for one employee, one leave type, and one cycle (typically a year; longer for Cuti Panjang). It is the quota that remaining days are taken from.

**Not:** the start and end dates of a Leave Request.

## Date fence

Using a Leave Period as the allowed min and max of a Leave Request’s date range. Applies only to Cuti Tahunan and Cuti Panjang.

## Accounting window

Using a Leave Period only to choose which quota is charged, without restricting the request’s dates. Applies to Cuti Dibayar and Izin Tanpa Upah. The type still appears only when the current Leave Period has remaining days. The request’s dates may be in the past or the future; Leave Period does not bound them.

## Leave Period Snapshot

The Leave Period copied onto a Leave Request when it is created. For Cuti Dibayar and Izin Tanpa Upah it is not shown on the create/edit form, but it is still recorded and is what approval and cancellation charge. It remains visible on the request’s detail, print, email, and approval views so the charged quota is obvious. Editing a draft or pending request keeps that snapshot unless the employee or leave type changes, in which case it is taken again from the current Leave Period of the new type.

**Not:** re-resolving “today’s” Leave Period on the day of approval.

## Leave Request

A request to take days of a leave type. Its date range is independent of Leave Period for Cuti Dibayar and Izin Tanpa Upah, and must sit inside Leave Period for Cuti Tahunan and Cuti Panjang.

## Supplies

GA stock of stationery and other consumable goods as **one catalog**. This is not meeting-room food or drink (that is Room & Consumption Request). There is no inter-project transfer document; site quantity grows only via Stock In on that Project. Taking goods from stock is recorded only as Stock Out (walk-in card), not as an in-app request. **All UI labels for this module are English.**

**Supply Item**:
A catalogued good that GA keeps on hand. One global list; each Project has its own quantity. Items belong to an **Item Category** master record that defines the code prefix (default categories: Office Supply **GAA**, Consumable **GAC**). Screen label: Catalog (not Katalog).
_Avoid_: Office Supply module, Consumable module, Product, SKU (as the entity name), per-project catalog, one shared GAA series for both categories, ATK as a UI label, hard-coded `kind` enum

**Item Category**:
Master data for supply item types: name (Office Supply, Consumable, …), unique item code prefix, description, status. Catalog items pick a category; codes are generated from that prefix.
_Avoid_: Kind (as a column on Supply Item), mixing prefixes under one counter

**Stock unit**:
The unit in which a Supply Item’s quantity is counted (pcs, box, rim, pack). Text on the item; quantities are integers; packs are not converted to pieces.
_Avoid_: unit conversion, dual UoM, decimal litres as the Phase 1 default

**Stock In**:
A dated receipt document onto one Project’s stock. Header: SI No, Project, Date, Notes. Lines: Item (code, name, description), qty in. May link to a Supply Order when ordered goods arrive. Screen label: Stock In (not Masuk).
_Avoid_: Purchase order (as the name of this movement), inbound shipment, single-item-only receipt, Masuk as a UI label

**Stock Out**:
A dated issue document from one Project’s stock. Header: SO No, Project, Date, Notes. Lines: Item, qty out, Location (free text), PIC (free text). People asking to take goods do not use the system; GA only records the card. Combined qty per item must not exceed that Project’s ending balance. Screen label: Stock Out (not Keluar).
_Avoid_: Distribution, fulfillment, Department (as Location), negative stock, linking Stock Out to a Supply Order, Keluar as a UI label, Need location (old label — use Location)

**Location** (Stock Out line):
Where the issued goods will be used. Free text on each Stock Out line. Not the Project that owns the stock, and not a Department record.
_Avoid_: Lokasi as Project, warehouse, department_id, need_location (legacy column name)

**PIC** (Stock Out line):
Person in charge for the line — free text, not an Employee record.
_Avoid_: employee_id (as required on walk-in issue)

**Opening balance**:
The quantity on a Supply Item for a Project at cut-over (Excel column Awal). Later loaded from the latest workbook the operator provides. Not a yearly reset.

**Ending balance**:
That Project’s opening plus Stock In minus Stock Out for the item (Excel column Akhir). Not a separately entered number. Perpetual, per Project.

**Supply Order**:
A request to **buy or bring in** Supply Items for the requestor’s project — including restock while quantity remains. Not a gate on zero stock. Lines are existing catalog items (new goods are added on Catalog first). Header: Order No, Project, Date, Department. Lines: Item (code, name, description), qty, remarks. Belongs to the project on the requestor’s **active administration** (`is_active = 1`); no project picker. Created by the employee or by HCS/GA. The requestor picks manual approvers (same pattern as Room & Consumption Request). Phase 1 numbering is internal, **per Project**, not a Letter Number: `ORD-{projectCode}-{seq}` (e.g. `ORD-000H-0001`). Stock Out documents use **SO-**; Stock In uses **SI-**. Sequence restarts per project per document type. Approval does not change stock; a later Stock In may point at the order when goods arrive. Taking goods from the cupboard is not this document.
_Avoid_: Supply Request (as taking from stock), item request, permintaan keluar, auto Stock In on approve, Letter Number in Phase 1, a single global ORD-0001 series, project picker on the order, blocking Order because ending balance > 0, Katalog as a UI label, SO- prefix on Supply Order
