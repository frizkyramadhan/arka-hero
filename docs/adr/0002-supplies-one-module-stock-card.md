# Supplies is one stock-card module plus a replenishment order

- Status: accepted
- Date: 2026-08-28

## Context

`docs/GA_MODULES_ANALYSIS.md` (Jan 2026) designed Office Supplies as an eight-table mini-ERP (dual Dept Head → GA approval, separate distribution, stock opname). Consumable had no design of its own; “consumption” there meant meeting-room supplies, which Room & Consumption Request later took with four fixed food/drink types and no stock.

GA today runs a workbook `01. ATK STOCK 2026.xlsx`: Katalog (code, name, description, Awal / Masuk / Keluar / Akhir), Masuk (date, item, qty), Keluar (date, item, qty, lokasi kebutuhan, penanggungjawab). One location (HO Balikpapan), no approval, no PO.

## Decision

- **One module** for ATK and other consumables. They are categories of a **Supply Item**, not two products.
- **Phase 1 operating loop** mirrors the workbook: catalog, Stock In, Stock Out. Ending balance is derived (Awal + Masuk − Keluar), perpetual (not a new book each year). Cut-over opening comes from the latest workbook when provided.
- **Two paths in Phase 1**: GA records **Stock In** / **Stock Out** as multi-line documents (header + item lines). People taking goods from the cupboard do not file anything in the app. Separately, the employee or HCS/GA may raise a **Supply Order** to buy or restock catalog items. Approval of an order is not a movement; Stock In is recorded when goods arrive.
- Meeting-room coffee/lunch/dinner stays in RCR. No stock link.

## Considered options

- Two modules (Consumable vs Office Supply): same warehouse and same in/out loop; rejected.
- Request-first mini-ERP from the 2026 analysis: heavier than the live workbook; rejected for Phase 1.
- Fold ATK issue into RCR: rejected; RCR already decided consumption is not stock.
