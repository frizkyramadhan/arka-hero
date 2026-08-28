# Supply stock is scoped by Project; need location is free text

- Status: accepted
- Date: 2026-08-28

## Context

The ATK workbook has one header location (“HO Balikpapan”) and a Keluar column “Lokasi Kebutuhan” filled with department nicknames (HCS, IT, PNB). Consumable issue often goes to a physical place (office, mess, office lt. 1), not a Department. A separate warehouse master would duplicate Project.

## Decision

- The stock that Masuk/Keluar changes belongs to a **Project** (`project_code`). There is no Warehouse entity.
- **Need location** on Stock Out is free text (office, mess, floor, or a nickname). It is not `department_id` and it is not the Project that owns the stock.
- Ending balance is per Supply Item per Project.
- The **catalog is global**: one Supply Item (e.g. GAA001) is shared; only the quantity differs by Project.

## Considered options

- Warehouse / location master: rejected for Phase 1; Project already is the company’s place code.
- Need location = Department: rejected; consumable use-places are rooms and messes, not org units.
