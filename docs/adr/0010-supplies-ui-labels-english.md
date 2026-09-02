# Supplies module UI labels are English

- Status: accepted
- Date: 2026-08-31

## Context

The source workbook uses Indonesian sheet and column names (Katalog, Masuk, Keluar, Lokasi Kebutuhan, Penanggungjawab). The rest of GAMMA (FOA, Flight, RCR admin screens) is English. Mixing Indonesian labels in this module would split the product language.

## Decision

- All user-visible labels in Supplies are **English**: menus, columns, buttons, statuses, emails, print.
- Workbook words stay as origin notes only, never as UI copy. Category on screen comes from **Item Category** master (default names Office Supply / Consumable; codes GAA / GAC).

## Label map

| Workbook / talk | UI label |
|---|---|
| Katalog | Catalog |
| Masuk | Stock In |
| Keluar | Stock Out |
| Awal / Akhir | Opening balance / Ending balance |
| Lokasi kebutuhan | Location |
| Penanggungjawab | PIC |
| Request / order barang | Supply Order |
| ATK (as a category) | Office Supply (Item Category) |
