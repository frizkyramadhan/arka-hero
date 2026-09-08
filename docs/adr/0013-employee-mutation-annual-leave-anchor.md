# Annual leave anniversary follows latest employee mutation, not a new administration row

- Status: accepted
- Date: 2026-09-08

## Context

Employee detail already stores employment as `administrations` (NIK, DOH, project). Leave entitlement for Group 1 (non-roster) used **Service Start DOH** as the annual-leave anniversary: first DOH in a continuous service period (EOC rehire continues; other terminations reset).

A project transfer (mutasi) is not a new hire and not a termination. Recording it as another administration row would overload `doh` (Date of Hire) and would also move LSL / years of service if we switched the calculator to “latest administration DOH”. PAR letter type `mutasi` is a document, not this history.

HR needs: date + destination project; many rows per employee; latest row only for **Cuti Tahunan**; leave-type settings (roster vs non-roster) follow that destination project.

## Decision

- New table `employee_mutations`: `employee_id`, `project_id` (destination only), `mutated_at`, `status` (`active`/`inactive`), `remarks`.
- CRUD lives on Employee detail, Employment tab. No origin-project column.
- Saving the latest **active** mutation updates the active administration’s `project_id`.
- **Cuti Tahunan** period (Group 1 anniversary) uses the latest **active** mutation date; if none, Service Start DOH.
- Leave settings (`projects.leave_type`, eligible categories) use the latest **active** mutation’s project; if none, active administration project.
- **LSL** and years of service stay on Service Start DOH. Roster projects keep calendar-year periods.

## Considered options

- New administration row + “use latest DOH”: rejected. `doh` would mean transfer date; EOC continuity and LSL would break.
- From-project + to-project columns: rejected. Destination only; origin is the previous mutation or the employment row.

## Consequences

- Generate entitlements after a mutation so the new anniversary and project group take effect.
- Deleting all mutations does not revert administration project (origin was never stored).
- Existing entitlements are not rewritten on mutation save.
