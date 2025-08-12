## Decision: Auto-close FPTK 6 months after approval

-   Date: 2025-08-12
-   Rule: FPTK closes automatically 6 months after it was approved.
-   How approval date is determined: take the latest `updated_at` from `approval_plans` for the `recruitment_request` with `status = approved (1)`.
-   Implementation:
    -   New command `recruitment:close-expired-fptk` checks approved FPTKs daily and closes those whose approval age > 6 months.
    -   Scheduled in `app/Console/Kernel.php` to run daily at 01:00.
-   Notes: This does not consider `positions_filled`. Manual close still available.

## Decision: Allow adding candidates beyond required_qty

-   Date: 2025-08-12
-   Context: Recruitment sessions previously blocked adding candidates when `currentSessions >= required_qty` and various views filtered FPTK lists by `positions_filled < required_qty`.
-   Decision: Remove the cap. As long as FPTK is `approved` (active) and not manually closed/cancelled, candidates can be added even if it exceeds `required_qty` until a hire is finalized. This supports ongoing sourcing while the request remains active.
-   Changes:
    -   `RecruitmentSessionController@store`: removed max-candidate validation.
    -   `RecruitmentRequest::canReceiveApplications()`: now returns true when status is `approved` regardless of `positions_filled`.
    -   `RecruitmentCandidateController`: relaxed FPTK availability queries by removing `whereColumn('positions_filled','<','required_qty')` filters.
-   Implications: Dashboards and analytics that show remaining positions still compute from `required_qty` and `positions_filled`, but application intake is no longer constrained by those values.

**Purpose**: Record technical decisions and rationale for future reference
**Last Updated**: [Auto-updated by AI]

# Technical Decision Records

## Update: Set candidate global_status to hired on Hiring stage save - 2025-08-12

**Context**: Previously, candidate `global_status` transitioned to `hired` only when onboarding completed. Operationally, once Hiring (PKWT/PKWTT) letter is issued, candidate should be globally marked as hired to prevent duplicate processing across FPTKs.

**Decision**: Update `RecruitmentSessionController@updateHiring` to set related `recruitment_candidates.global_status = 'hired'` when hiring data is saved, while the session itself advances to `onboarding` and keeps progress at `hire` until onboarding completes.

**Implementation**:

-   Controller edit in `app/Http/Controllers/RecruitmentSessionController.php` after marking letter number used:
    -   `if ($session->candidate && $session->candidate->global_status !== 'hired') { $session->candidate->update(['global_status' => 'hired']); }`
-   Session status flow unchanged: session moves to `onboarding`, progress remains at `hire` (95%) until onboarding completes.

**Implications**:

-   Prevents the same candidate from applying to other FPTKs once hiring is issued.
-   Dashboards reflecting candidate availability will show them as hired earlier.
-   Final session completion and FPTK positions filled logic remain at onboarding completion where applicable.

## Decision Template

Decision: [Title] - [YYYY-MM-DD]

**Context**: [What situation led to this decision?]

**Options Considered**:

1. **Option A**: [Description]
    - ✅ Pros: [Benefits]
    - ❌ Cons: [Drawbacks]
2. **Option B**: [Description]
    - ✅ Pros: [Benefits]
    - ❌ Cons: [Drawbacks]

**Decision**: [What we chose]

**Rationale**: [Why we chose this option]

**Implementation**: [How this affects the codebase]

**Review Date**: [When to revisit this decision]

---

## Recent Decisions

[Add your decisions as you make them using the template above]

## Automate Employee + Administration creation on Hiring - 2025-08-12

**Context**: When a candidate reaches Hiring, HR needs their personal data in `employees` and administrative assignment in `administrations` (NIK, project, position, grade, level, DOH, POH, class, agreement, FOC for PKWT). Previously, this required manual creation in Employee module.

**Decision**: On Hiring save, create or update an `employees` record (by identity_card) and create a new active `administrations` record (auto-deactivating previous ones). Enforce NIK and identity_card uniqueness. PKWT requires FOC.

**Implementation**:

-   Controller: `RecruitmentSessionController@updateHiring`
    -   Validate nested `employee[...]` and `administration[...]` fields
    -   Reuse existing employee by `identity_card`, else create
    -   Deactivate previous active `administrations` for the employee, then create a new one
    -   Agreement derived from UI `agreement_type`; if PKWT then `foc` shown + required
-   View: `resources/views/recruitment/sessions/partials/modals.blade.php`
    -   Added Personal and Administration sections to Hire modal; FOC toggled by agreement type
-   Scripts: `resources/views/recruitment/sessions/show-session.blade.php`
    -   Toggle FOC requirement based on agreement type buttons

**Implications**:

-   Streamlines handover from Candidate to Employee lifecycle
-   Prevents duplicate employees via `identity_card` uniqueness
-   Guarantees exactly one active administration per employee
