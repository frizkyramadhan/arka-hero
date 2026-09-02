# Cutover seed loads GA workbooks into catalog and stock documents

- Status: accepted
- Date: 2026-09-02

## Context

GA keeps two Excel stock cards: **Office Supply** (`01. ATK STOCK 2026.xlsx`, prefix **GAA**, 146 catalog rows, 64 Masuk lines, 202 Keluar lines) and **Consumable** (prefix **GAC**, 29 items, 127 Masuk, 633 Keluar). Each workbook has three sheets:

- **Katalog** — code, name, description, Awal / Masuk / Keluar / Akhir (summary columns)
- **Masuk** — date, item code, quantity
- **Keluar** — date, item code, quantity, location, PIC

The workbook header location **HO Balikpapan** is the single site in Phase 1 and maps to Project **000H**. Laravel already models catalog and movements in `supply_items`, `supply_stock_ins` / `supply_stock_in_items`, and `supply_stock_outs` / `supply_stock_out_items` (ADR-0011: multi-line documents). ADR-0002 committed to cut-over from the latest workbook; this ADR defines how that one-time load is shaped.

## Decision

- **One cutover seed per workbook**, matched to its Item Category prefix (GAA → Office Supply, GAC → Consumable). Import **Katalog** rows into `supply_items` (code, name, description, stock unit from workbook or default).
- **Awal → one opening-balance Stock In** on Project `000H`: a single multi-line Stock In document dated at cutover, one line per catalog item with Awal > 0. Awal is not stored as a column; it is represented only as that receipt (ADR-0002 opening balance).
- **Masuk sheet → Stock In documents** grouped by **date**: all lines on the same date become one Stock In header with multiple `supply_stock_in_items`. Notes may mark the document as workbook import.
- **Keluar sheet → Stock Out documents** grouped by **date**: all lines on the same date become one Stock Out header; each line keeps workbook **location** and **PIC** as free text on `supply_stock_out_items`.
- **Katalog Masuk / Keluar / Akhir** are control totals only during seed; movements come from the Masuk and Keluar sheets. After seed, ending balance is derived (opening Stock In + Stock Ins − Stock Outs); Akhir is a reconciliation check, not imported.
- **All seeded stock documents** use Project `000H` (workbook header location). Keluar line location remains the use-place text, not the Project.
- Cutover seed does **not** create Supply Orders, approvals, or Letter Numbers. Document sequences continue after the seeded SI/SO set.

## Consequences

- Operators can verify the seed by comparing derived ending balance to Katalog **Akhir** per item on Project 000H.
- Historical issue detail (location, PIC) is preserved on Stock Out lines; the workbook’s walk-in pattern matches ADR-0007.
- Two prefix-specific workbooks stay separate at import time; the app still exposes one global catalog (ADR-0003, ADR-0006).
- Re-running cutover on a non-empty database requires an explicit wipe or idempotent strategy; the seed is intended for initial go-live only.
- Future workbook re-import (post cutover) is out of scope here and must not double-count Awal or historical Masuk/Keluar without a defined replace strategy.
