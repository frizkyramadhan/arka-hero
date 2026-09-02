# Supply Order numbers use ORD; Stock Out uses SO

- Status: accepted
- Date: 2026-08-31 (supersedes 2026-08-31 `SO-{projectCode}-{seq}` for orders)

## Context

Phase 1 uses document numbers per Project for Stock In (SI), Stock Out (SO), and Supply Order. **SO** is now the Stock Out document prefix. Supply Order must not reuse SO.

## Decision

- **Supply Order**: `ORD-{projectCode}-{seq}` (4-digit seq), one sequence per Project.
- **Stock In**: `SI-{projectCode}-{seq}`.
- **Stock Out**: `SO-{projectCode}-{seq}`.
- Sequences are independent per document type and per Project. No calendar-year reset in Phase 1.

## Considered options

- Keep `SO-` for Supply Order: rejected; SO is Stock Out in UI and operator vocabulary.
- Global sequences: rejected; HO and site must not share one counter.
