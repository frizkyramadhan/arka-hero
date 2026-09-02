# A Supply Order is not gated on zero stock

- Status: accepted
- Date: 2026-08-28

## Context

The first gloss of Order was “barang yang belum tersedia di stok HCS”, which sounds like a zero-balance (or missing catalog) gate. Restock of items that still have quantity is the normal HCS buy cycle (e.g. kertas A4 while 10 rim remain).

## Decision

- A Supply Order may be raised while ending balance is still positive. The system does not require quantity = 0.
- Order lines are **existing Supply Items**. A brand-new good is added on Katalog (GAA/GAC) first, then ordered.
- Both the employee and HCS/GA may create an order; project is still the creator’s active administration.

## Considered options

- Allow Order only at zero stock: rejected.
- Free-text lines for items not in the catalog: rejected for Phase 1.
