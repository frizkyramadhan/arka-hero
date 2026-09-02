# Approving a Supply Order does not move stock; Keluar is walk-in only

- Status: accepted
- Date: 2026-08-28

## Context

“Request” was first modelled as an employee ask to take items from the cupboard, then Stock Out. The operator corrected this: the in-app document is a **Supply Order** to buy or restock catalog items. Asking to take what is already on the shelf is off-system; GA only writes the Keluar card.

## Decision

- Quantity changes only via **Stock In** / **Stock Out**.
- Approving a Supply Order is permission to obtain goods, not a movement. GA later records **Stock In** when goods arrive (qty may be less than ordered) and may link that Stock In to the order.
- **Stock Out** is never created from a Supply Order. No in-app “permintaan keluar”.
- A Stock Out that would make that Project’s ending balance negative is rejected.

## Considered options

- Auto Stock In on approve: rejected; goods are not on hand yet.
- In-app request to take from stock: rejected; Keluar stays a walk-in card like the Excel workbook.
