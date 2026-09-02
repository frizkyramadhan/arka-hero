# Stock In and Stock Out are multi-line documents

- Status: accepted
- Date: 2026-08-31

## Context

Phase 1 initially modeled Stock In / Stock Out as one item per row (mirroring a single Excel line). Supply Order already supports multiple lines. Operators record several items on one receipt or issue slip.

## Decision

- **Stock In** header: SI No, Project, Date, Notes. Lines: Item (code, name, description), qty in. Optional link to Supply Order on the header; lines may reference order lines.
- **Stock Out** header: SO No, Project, Date, Notes. Lines: Item, qty out, Location (free text), PIC (free text).
- Ending balance sums line quantities via `supply_stock_in_items` / `supply_stock_out_items`.
- Delete Stock In only if no line would drive ending balance negative.

## Considered options

- One item per Stock In / Stock Out record: rejected; does not match how GA fills the workbook or how orders work.
