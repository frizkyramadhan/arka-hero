# Item codes are GAA for ATK and GAC for Consumable

- Status: accepted
- Date: 2026-08-28

## Context

The HO workbook numbers ATK as GAA001–GAA146. Consumable joins the same module but must stay distinguishable in the catalog and on reports. One shared counter would mix the two kinds; two modules would duplicate in/out.

## Decision

- **GAA** + sequence = ATK. **GAC** + sequence = Consumable. Two counters, one Supply Item table.
- Prefix is the kind. No third prefix in Phase 1.

## Considered options

- One GAA series plus a category field: rejected; operator wants the code itself to show the kind.
- Separate modules per prefix: rejected earlier.
