# Domain

Glossary only. No implementation details.

## Leave Type Category

A classification of a leave type. The four categories in use are **Cuti Tahunan**, **Cuti Panjang**, **Cuti Dibayar**, and **Izin Tanpa Upah**. Rules about whether a Leave Period fences dates follow the category, not the display name of an individual type.

## Cuti Tahunan

Annual leave. A Leave Request of this category is date-fenced: its date range must lie inside the Leave Period that funds it.

## Cuti Panjang

Long-service leave. Date-fenced the same way as Cuti Tahunan.

## Cuti Dibayar

Paid leave as a category — many named types (marriage, sick, childbirth, pilgrimage, and so on), not a single type. A Leave Request of this category is not date-fenced by Leave Period. Leave Period is only the accounting window whose remaining days are charged.

## Izin Tanpa Upah

Unpaid leave. Not date-fenced by Leave Period. Leave Period is only the accounting window whose remaining days are charged.

## Leave Period

The entitlement window for one employee, one leave type, and one cycle (typically a year; longer for Cuti Panjang). It is the quota that remaining days are taken from.

**Not:** the start and end dates of a Leave Request.

## Date fence

Using a Leave Period as the allowed min and max of a Leave Request’s date range. Applies only to Cuti Tahunan and Cuti Panjang.

## Accounting window

Using a Leave Period only to choose which quota is charged, without restricting the request’s dates. Applies to Cuti Dibayar and Izin Tanpa Upah. The type still appears only when the current Leave Period has remaining days. The request’s dates may be in the past or the future; Leave Period does not bound them.

## Leave Period Snapshot

The Leave Period copied onto a Leave Request when it is created. For Cuti Dibayar and Izin Tanpa Upah it is not shown on the create/edit form, but it is still recorded and is what approval and cancellation charge. It remains visible on the request’s detail, print, email, and approval views so the charged quota is obvious. Editing a draft or pending request keeps that snapshot unless the employee or leave type changes, in which case it is taken again from the current Leave Period of the new type.

**Not:** re-resolving “today’s” Leave Period on the day of approval.

## Leave Request

A request to take days of a leave type. Its date range is independent of Leave Period for Cuti Dibayar and Izin Tanpa Upah, and must sit inside Leave Period for Cuti Tahunan and Cuti Panjang.
