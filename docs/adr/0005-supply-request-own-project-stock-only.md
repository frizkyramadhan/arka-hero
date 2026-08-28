# A Supply Order replenishes the requestor’s project stock

- Status: accepted
- Date: 2026-08-28

## Context

Stock quantity is per Project. The in-app document is a **Supply Order** (obtain goods not in HCS stock), not a slip to take from the cupboard. A site employee must not order into another Project’s warehouse (e.g. HO).

## Decision

- A Supply Order belongs to the project on the requestor’s **active administration** (`administrations.is_active = 1`). No project picker on the form.
- When goods arrive, **Stock In** on that same Project may point at the order.
- Walk-in Stock In / Stock Out still name a Project explicitly (GA recording the card).
- There is no transfer document. Another Project’s quantity increases only with Stock In recorded on that Project.

## Considered options

- Requestor selects any Project as stock destination: rejected for Phase 1.
- Mixed-project lines on one order: rejected.
- Picker limited to the requestor’s own assignments: rejected; active administration only.
