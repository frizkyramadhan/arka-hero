# Supplies — Design

**Status**: Implemented (Phase 1, refactored 2026-08-31)  
**Last updated**: 2026-08-31  
**Supersedes**: `docs/GA_MODULES_ANALYSIS.md` Module 1 (Office Supplies mini-ERP)  
**Glossary**: `CONTEXT.md` (Supplies)  
**ADRs**: `docs/adr/0002`, `0006`, `0007`–`0011`

Workbook origin: `01. ATK STOCK 2026.xlsx` (Catalog / Stock In / Stock Out as three sheets). Meeting-room food and drink stay in Room & Consumption Request (RCR).

## Decisions

| Aspect | Decision |
|--------|----------|
| Module | One **Supplies** module. Categories are **Item Category** master data, not hard-coded kinds. |
| UI language | **English** for every label (menus, columns, buttons, statuses, print, mail). Approval help for Supply Order is **Indonesian** with English terms kept. |
| Item Category | Master: name + prefix + description. Default seed: Office Supply / GAA, Consumable / GAC. |
| Catalog | Global list. Item code from category prefix + sequence. |
| Stock | Perpetual **ending balance** per item per **Project**. Opening from cut-over. |
| Stock location | Project (`project_code`). No warehouse master. No transfer document. |
| Stock In / Stock Out | Multi-line **documents** (header + lines), like Supply Order. |
| Stock Out line extras | **Location** and **PIC** (free text per line). |
| Negative stock | Rejected on Stock Out create and Stock In delete. |
| Taking from the cupboard | Off-system. GA only records Stock Out. |
| Supply Order | Buy / restock. Header: Order No, Project, Date, Department. Lines: item, qty, remarks. |
| Order who | Employee or HCS/GA. Project = creator’s active administration. No project picker. |
| Approval | Manual approvers + `supply_order` approval plan. Approve does not move stock. |
| Receive | After approved, GA records **Stock In** (multi-line, may link order), then **close**. |
| Numbering | Per project, per doc type: `ORD-`, `SI-`, `SO-` + `{projectCode}-{seq}` (4 digits). |
| Portal | GAMMA → Item Categories, Catalog, Stock In, Stock Out, Orders. My Features → My Supply Orders. |

## Schema (logical)

### `supply_item_categories`

- `name`, `prefix` (unique), `description`, `status`

### `supply_items`

- `code`, `supply_item_category_id`, `name`, `description`, `stock_unit`, `status`

### `supply_stock_ins` / `supply_stock_in_items`

- Header: `document_number` (SI-…), `project_id`, `stock_date`, `notes`, optional `supply_order_id`
- Lines: `supply_item_id`, `quantity`, optional `supply_order_item_id`

### `supply_stock_outs` / `supply_stock_out_items`

- Header: `document_number` (SO-…), `project_id`, `stock_date`, `notes`
- Lines: `supply_item_id`, `quantity`, `location`, `person_in_charge`

### `supply_orders` / `supply_order_items`

- Header: `order_number` (ORD-…), `project_id`, `administration_id`, `department_id`, `order_date`, `requested_by`, approval fields
- Lines: `supply_item_id`, `quantity_ordered`, `remarks`
- Received qty from linked Stock In lines

## Permissions

```
supplies.item-categories.{show,create,edit,delete}
supplies.catalog.{show,create,edit,delete}
supplies.stock-in.{show,create,edit,delete}
supplies.stock-out.{show,create,edit,delete}
supplies.orders.{show,create,edit,delete,close}
personal.supplies.orders.{view-own,create-own,edit-own,cancel-own}
```

Seeder: `SupplyPermissionSeeder`.

## Routes (web)

- `/supplies/item-categories` — Item Categories
- `/supplies/catalog` — Catalog
- `/supplies/stock-ins` — Stock In (create/show multi-line)
- `/supplies/stock-outs` — Stock Out (create/show multi-line)
- `/supplies/orders`, `/supplies/orders/my-orders` — Supply Order

## UI copy (English)

| Screen | Fields |
|--------|--------|
| Item Categories | Name, Prefix, Description, Status |
| Catalog | Item Category, Item code, Name, Description, Stock unit |
| Stock In | SI No, Project, Date, Notes; lines: Item, Description, Qty in |
| Stock Out | SO No, Project, Date, Notes; lines: Item, Description, Qty out, Location, PIC |
| Supply Order | Order No, Project, Date, Department; lines: Item, Description, Qty, Remarks |

Do not use Katalog, Masuk, Keluar, ATK, Need location as labels.
