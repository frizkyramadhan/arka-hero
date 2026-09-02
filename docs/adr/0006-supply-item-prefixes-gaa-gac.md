# Item code prefixes live on Item Category master data

- Status: accepted
- Date: 2026-08-31 (supersedes 2026-08-28 decision)

## Context

The HO workbook numbers ATK as GAA001–GAA146. Consumable joins the same module but must stay distinguishable in the catalog and on reports. Hard-coded `kind` on Supply Item blocked adding categories and changing prefixes without code changes.

## Decision

- **Item Category** master data (`supply_item_categories`): name (e.g. Office Supply, Consumable), unique **prefix** (GAA, GAC), description, status.
- Each Supply Item belongs to one category; codes are `{prefix}{seq}` with a per-category sequence (e.g. GAA001).
- Default seed: Office Supply / GAA, Consumable / GAC.
- Prefix is locked after catalog items exist for that category.

## Considered options

- Hard-coded `kind` enum on Supply Item: rejected; operator wants configurable categories and prefixes.
- One GAA series plus a category label only: rejected; the code itself must show the category.
