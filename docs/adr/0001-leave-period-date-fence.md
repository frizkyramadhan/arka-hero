# Leave Period is a date fence only for annual and long-service leave

- Status: accepted
- Date: 2026-08-19

## Context

Leave Request forms treated Leave Period (the entitlement window) as min/max bounds on the request’s dates for every leave type. That blocked Cuti Dibayar and Izin Tanpa Upah whose calendar dates often fall outside the current entitlement year (especially retrospective sick leave).

## Decision

- **Cuti Tahunan** and **Cuti Panjang** keep Leave Period as a **date fence**: the request’s dates must lie inside the period that funds them.
- **Cuti Dibayar** and **Izin Tanpa Upah** use Leave Period only as an **accounting window**. Dates are not bounded by the period. Remaining days of the current period still gate whether the type appears and how many days can be taken.
- The Leave Period copied onto the request at create time is the snapshot charged on approval and cancellation. Editing a draft/pending request keeps that snapshot unless employee or leave type changes.
- The Leave Period field is hidden on create/edit for paid/unpaid, and still shown on detail, print, email, and approval.

## Consequences

- Approval must resolve entitlement by the snapshot label, not by whether the request dates sit inside a period.
- Paid/unpaid date pickers have no period min/max; weekend and national-holiday rules are unchanged.
- Bulk/roster periodic leave is out of scope.
