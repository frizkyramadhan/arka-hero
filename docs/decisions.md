**Purpose**: Record technical decisions and rationale for future reference
**Last Updated**: 2026-01-09

# Technical Decision Records - ARKA HERO HRMS

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

### Decision: Remove Unused leave_calculations Table - 2026-01-15

**Context**: The `leave_calculations` table and `LeaveCalculation` model were introduced as future infrastructure for an audit trail of leave entitlement calculations, but no code ever populated or queried this table. All current leave logic relies on `leave_entitlements` + `leave_requests` with runtime calculations in `LeaveEntitlement::getLeaveCalculationDetails()`.

**Options Considered**:

1. **Implement Full Audit Trail Using leave_calculations**
    - ✅ Pros: Strong historical audit trail, point-in-time balance snapshots, better for compliance
    - ❌ Cons: Additional complexity, data duplication, requires service layer and backfill, not currently needed by business
2. **Remove Table and Model as Unused Infrastructure**
    - ✅ Pros: Simpler schema, no dead code, clearer architecture, no maintenance cost for unused components
    - ❌ Cons: Losing prepared path for future audit-trail implementation (would need new migration later)
3. **Keep Table and Model but Still Unused**
    - ✅ Pros: Future option remains open without immediate work
    - ❌ Cons: Technical debt (dead schema + model), confusing for future developers, misleading documentation

**Decision**: Remove unused `leave_calculations` table and `LeaveCalculation` model.

**Rationale**:

- No production code path ever writes to or reads from `leave_calculations` (confirmed via code search and DB count = 0)
- Current leave features (entitlements, requests, reports) work entirely via `leave_entitlements` and `leave_requests`
- Keeping unused schema and model adds cognitive load and can mislead future maintenance
- If a formal audit trail is required in the future, it can be reintroduced with a fresh design aligned to real requirements

**Implementation**:

- Added migration `2026_01_15_120000_drop_leave_calculations_table.php` to drop `leave_calculations` (with down() recreating the latest known structure)
- Deleted `app/Models/LeaveCalculation.php`
- Removed `leaveCalculations()` relationship from `LeaveRequest` model
- Updated `docs/architecture.md` to remove `LeaveCalculation` and `leave_calculations` from the current model/table list

**Review Date**: 2026-12-01 (revisit if audit/compliance requirements around leave balances appear)

### Decision: Leave Entitlement Dual-System Architecture - 2025-09-XX

**Context**: ARKA has two types of projects with different leave management needs:

-   Standard projects: Traditional office-based work with DOH-based leave entitlements
-   Operational projects: Shift-based work requiring roster management and periodic leave

**Options Considered**:

1. **Single System with Complex Rules**
    - ✅ Pros: Unified codebase, single table structure, easier maintenance
    - ❌ Cons: Complex conditional logic, difficult to understand, prone to bugs
2. **Separate Systems for Each Project Type**
    - ✅ Pros: Clear separation, easier to understand, independent evolution
    - ❌ Cons: Code duplication, separate UIs, harder to maintain consistency
3. **Hybrid System with Project Classification**
    - ✅ Pros: Single codebase with clear business rules, flexible, maintainable
    - ❌ Cons: Requires project classification configuration, some conditional logic

**Decision**: Hybrid System with Project Classification

**Rationale**:

-   Projects table includes `leave_type` field ('standard' vs 'periodic')
-   Single entitlement generation system that adapts based on project classification
-   Group 1 (standard): DOH-based calculations only
-   Group 2 (periodic): Hybrid calculation (roster-based periodic + DOH-based standard types)
-   Maintains single database schema while supporting different business rules
-   Clear documentation in technical flow document

**Implementation**:

-   `projects.leave_type` column determines calculation method
-   `LeaveEntitlementController::generateProjectEntitlements()` contains business logic
-   `docs/LEAVE_ENTITLEMENT_TECHNICAL_FLOW.md` documents the rules
-   LSL special rules implemented for Group 2 (requires 10 days periodic leave taken)
-   Roster system integrated with `levels` table for pattern configuration

**Review Date**: 2026-06-01 (after 6 months of production use)

---

### Decision: Recruitment Multi-Stage Table Architecture - 2025-08-XX

**Context**: Initial recruitment system used single `recruitment_assessments` and `recruitment_offers` tables. As requirements grew, it became difficult to manage different data structures for each recruitment stage (CV review, psychometric test, theory test, interviews, offering, MCU, hiring).

**Options Considered**:

1. **Single Table with JSON Columns**
    - ✅ Pros: Simple schema, easy to add fields, flexible structure
    - ❌ Cons: No query optimization, difficult validation, poor data integrity, hard to report
2. **Single Table with Many Nullable Columns**
    - ✅ Pros: Relational structure, query optimization possible
    - ❌ Cons: Very wide table, many unused columns per record, confusing schema
3. **Separate Table Per Stage**
    - ✅ Pros: Clear data structure, optimal queries, strong validation, easy to extend
    - ❌ Cons: More migrations, more models, more complexity in code

**Decision**: Separate Table Per Stage

**Rationale**:

-   Each recruitment stage has distinct data requirements
-   CV Review: result, notes
-   Psikotes: provider, result, score, notes
-   Theory Test: result, score, notes
-   Interview: 3 separate interview records (user, HR, director) with different assessors
-   Offering: salary offer, negotiation, acceptance decision
-   MCU: provider, result, notes
-   Hiring: agreement type, start date, employee creation

**Implementation**:

-   Created 7 stage-specific tables: `recruitment_cv_reviews`, `recruitment_psikotes`, `recruitment_tes_teori`, `recruitment_interviews`, `recruitment_offerings`, `recruitment_mcu`, `recruitment_hiring`
-   `recruitment_sessions` table tracks current stage and overall status
-   `RecruitmentSessionController` manages stage transitions
-   Migration 2025_08_07_150012 drops old tables
-   Each stage has dedicated update methods in controller

**Review Date**: 2026-03-01

---

### Decision: Centralized Letter Numbering System - 2025-06-XX

**Context**: Multiple document types (Official Travel, Recruitment FPTK, future documents) require sequential letter numbers. Manual assignment was error-prone and caused number conflicts.

**Options Considered**:

1. **Per-Module Letter Numbering**
    - ✅ Pros: Simple per-module implementation, no dependencies
    - ❌ Cons: Duplicate code, inconsistent formats, no centralized tracking
2. **Centralized Service with Database**
    - ✅ Pros: Single source of truth, consistent format, lifecycle tracking, integration ready
    - ❌ Cons: Additional complexity, requires API integration
3. **Manual Assignment Only**
    - ✅ Pros: Simple, no automation needed
    - ❌ Cons: Error-prone, slow, no tracking

**Decision**: Centralized Service with Database

**Rationale**:

-   Letter numbers are critical business documents requiring auditability
-   Sequential number generation needs to be thread-safe and conflict-free
-   Multiple document types will need letter numbers in the future
-   Letter number lifecycle (available → reserved → used → cancelled) needed for proper tracking
-   API integration allows documents to auto-request numbers upon approval

**Implementation**:

-   `letter_categories` table: category configuration (code, format, numbering behavior)
-   `letter_subjects` table: subject templates per category
-   `letter_numbers` table: letter number records with status tracking
-   `LetterNumberApiController`: API endpoints for document integration
-   Format: `{sequential}/{category_code}/{subject_code}/{project_code}/{month_roman}/{year}`
-   Integration points: `OfficialtravelController`, `RecruitmentRequestController`
-   Auto-assignment on approval via API call

**Review Date**: 2026-06-01

---

### Decision: Laravel Sanctum for API Authentication - 2025-03-XX

**Context**: Need to provide RESTful API access for potential mobile app, third-party integrations, and JavaScript SPA features while maintaining session-based authentication for web interface.

**Options Considered**:

1. **Laravel Passport (OAuth2)**
    - ✅ Pros: Full OAuth2 implementation, supports client credentials, industry standard
    - ❌ Cons: Overkill for internal API, complex setup, more overhead
2. **Laravel Sanctum (Token-based)**
    - ✅ Pros: Lightweight, simple token management, works with SPA and mobile, built for Laravel
    - ❌ Cons: No OAuth2 flows, simpler than Passport
3. **JWT (tymon/jwt-auth)**
    - ✅ Pros: Stateless, standard JWT implementation
    - ❌ Cons: Third-party package, more complex to integrate with Laravel ecosystem

**Decision**: Laravel Sanctum

**Rationale**:

-   Lightweight solution perfect for first-party API authentication
-   Supports both SPA authentication and mobile app tokens
-   Easy integration with existing session-based authentication
-   Built and maintained by Laravel team
-   Sufficient for current and foreseeable future needs
-   Simple token management (issue, revoke, expiry)

**Implementation**:

-   Installed `laravel/sanctum` package
-   API routes protected with `auth:sanctum` middleware
-   Authentication endpoints: `/api/v1/auth/login`, `/api/v1/auth/logout`, `/api/v1/auth/user`
-   Token stored in `personal_access_tokens` table
-   Legacy `/api/*` routes remain unprotected for backward compatibility
-   Versioned `/api/v1/*` routes require authentication

**Review Date**: 2027-01-01 (after 2 years of use)

---

### Decision: AdminLTE 3 for UI Framework - 2025-XX-XX

**Context**: Need professional admin dashboard interface with comprehensive UI components, responsive design, and active community support.

**Options Considered**:

1. **Custom Bootstrap 4 Implementation**
    - ✅ Pros: Full control, no third-party dependencies, lightweight
    - ❌ Cons: Time-consuming, need to build all components from scratch, harder to maintain
2. **AdminLTE 3 (Bootstrap 4 based)**
    - ✅ Pros: Comprehensive components, professional design, active community, Laravel integration, pre-built widgets
    - ❌ Cons: Some unused features, specific design constraints
3. **CoreUI**
    - ✅ Pros: Modern design, good documentation
    - ❌ Cons: Less Laravel-focused, smaller community

**Decision**: AdminLTE 3

**Rationale**:

-   Most popular admin template for Laravel (large community)
-   Comprehensive widget library (cards, tables, forms, charts)
-   Bootstrap 4 based (familiar to developers)
-   Excellent documentation and examples
-   Professional appearance suitable for enterprise HR system
-   Active maintenance and regular updates
-   Includes Chart.js integration for dashboards
-   Pre-built authentication pages

**Implementation**:

-   AdminLTE 3 assets in `public/assets/`
-   Main layout: `resources/views/layouts/app.blade.php`
-   Blade components for common widgets
-   Custom CSS in `resources/css/app.css` for ARKA-specific branding
-   JavaScript initialization in view files
-   Badge color system for recruitment results: success (green), danger (red), warning (yellow), secondary (gray)

**Review Date**: 2026-12-01

---

### Decision: Spatie Laravel Permission for RBAC - 2025-03-XX

**Context**: Need robust role-based access control system to manage permissions for different user types (Administrator, HR Supervisor, HR Manager, Division Manager, Employee).

**Options Considered**:

1. **Custom RBAC Implementation**
    - ✅ Pros: Full control, no dependencies, tailored to needs
    - ❌ Cons: Time-consuming, need to handle all edge cases, maintenance burden
2. **Spatie Laravel Permission**
    - ✅ Pros: Battle-tested, flexible, comprehensive features, active maintenance, Laravel-first
    - ❌ Cons: Learning curve, some features may not be needed
3. **Laravel Built-in Gates & Policies Only**
    - ✅ Pros: Native Laravel, simple for basic needs
    - ❌ Cons: No role management UI, manual permission assignment, lacks advanced features

**Decision**: Spatie Laravel Permission

**Rationale**:

-   Industry-standard package for Laravel RBAC
-   Supports roles and direct permissions
-   Flexible: assign permissions to roles or directly to users
-   Middleware support for route protection
-   Blade directives for UI permission checks (@role, @can)
-   Database-backed (easy to manage via UI)
-   Caching support for performance
-   Widely used and well-documented

**Implementation**:

-   Package: `spatie/laravel-permission: ^6.16`
-   Tables: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`
-   Predefined roles: administrator, hr-supervisor, hr-manager, div-manager, user
-   Seeders: `RoleAndPermissionSeeder`, `RecruitmentRolePermissionSeeder`
-   Controllers: `RoleController`, `PermissionController` for management UI
-   Middleware: `role`, `permission` for route protection
-   Models: `User` model uses `HasRoles` trait

**Review Date**: 2027-01-01

---

### Decision: Project-Based Leave Type Classification - 2025-09-XX

**Context**: Different projects have different leave management requirements. Need a way to distinguish between standard office work and shift-based operational work.

**Options Considered**:

1. **Hardcoded Project Lists in Code**
    - ✅ Pros: Simple, no database changes needed
    - ❌ Cons: Requires code deployment for changes, not flexible, hard to maintain
2. **Boolean Flag: is_operational**
    - ✅ Pros: Simple database change, clear distinction
    - ❌ Cons: Limited to two types, not extensible if more types needed
3. **Enum Field: leave_type**
    - ✅ Pros: Flexible for future types, clear semantics, database-driven
    - ❌ Cons: Requires migration, need to update existing records

**Decision**: Enum Field: leave_type

**Rationale**:

-   Future-proof: can add more leave types if needed (e.g., 'hybrid', 'remote')
-   Database-driven: no code deployment needed to classify new projects
-   Clear business meaning: 'standard' vs 'periodic'
-   Single source of truth in database
-   Easy to query and report on

**Implementation**:

-   Migration 2025_09_29_093451: added `leave_type` enum to `projects` table
-   Values: 'standard' (default), 'periodic'
-   Group 1 Projects (standard): 000H, 001H, APS, 021C, 025C
-   Group 2 Projects (periodic): 017C, 022C
-   Leave entitlement generation logic checks this field
-   Documented in `docs/LEAVE_ENTITLEMENT_TECHNICAL_FLOW.md`

**Review Date**: 2026-09-01

---

### Decision: Toast Helper Functions Over Toastr Library - 2025-XX-XX

**Context**: Need consistent notification system across the application. Initially using toastr JavaScript library directly, causing inconsistent styling and implementation.

**Options Considered**:

1. **Direct Toastr JavaScript Library Usage**
    - ✅ Pros: Full control, client-side only
    - ❌ Cons: Inconsistent usage, no server-side validation messages, different styles across modules
2. **SweetAlert2 Only**
    - ✅ Pros: Beautiful modals, comprehensive features
    - ❌ Cons: Overkill for simple notifications, more intrusive
3. **Custom Toast Helper Functions**
    - ✅ Pros: Consistent usage, server-side integration, simple API, unified styling
    - ❌ Cons: Abstraction layer, need to implement helper functions

**Decision**: Custom Toast Helper Functions

**Rationale**:

-   Consistent API across all controllers: `toast_success()`, `toast_error()`, `toast_warning()`, `toast_info()`
-   Server-side session flash messages automatically displayed
-   Single point of configuration for styling
-   Easy to switch underlying library if needed
-   Forces consistent usage patterns
-   Works with both redirect responses and API responses
-   English messages for consistency

**Implementation**:

-   Helper functions in `app/Helpers/Common.php`
-   Functions: `toast_success($message)`, `toast_error($message)`, `toast_warning($message)`, `toast_info($message)`
-   Auto-loaded via `composer.json` autoload files
-   Controllers return with toast helpers instead of direct toastr
-   JavaScript toast display in main layout
-   Package: `realrashid/sweet-alert` for underlying functionality

**Review Date**: 2026-06-01

---

### Decision: Postman API MCP for API Documentation - 2025-XX-XX

**Context**: Need to maintain API documentation synchronized with codebase. Manual Postman collection updates are time-consuming and error-prone.

**Options Considered**:

1. **Manual Postman Collection Updates**
    - ✅ Pros: Simple, no automation needed, full control
    - ❌ Cons: Time-consuming, error-prone, often outdated, hard to maintain
2. **Swagger/OpenAPI Specification**
    - ✅ Pros: Industry standard, auto-documentation, interactive UI
    - ❌ Cons: Requires extensive annotations, Laravel integration not perfect, learning curve
3. **Postman API MCP Integration**
    - ✅ Pros: Automated sync, maintains Postman format, programmatic access, folder organization
    - ❌ Cons: Requires MCP setup, depends on Postman API

**Decision**: Postman API MCP Integration

**Rationale**:

-   Automated sync of API routes to Postman collection
-   Maintains familiar Postman interface for testing
-   Programmatic collection updates via MCP tools
-   Organized folder structure matching Laravel route groups
-   Environment variables for BASE_URL and TOKEN
-   Can generate collections from OpenAPI specs
-   Supports mock server creation for testing
-   Team already uses Postman, no new tool to learn

**Implementation**:

-   MCP server: `postman-api-mcp`
-   Collection name: "ARKA HERO - API"
-   Workspace-scoped collection management
-   Folder organization:
    -   Authentication
    -   Departments, Employees, Official Travels
    -   Leave Management, Recruitment
    -   Master Data, Dashboard, Letter Numbers
-   Variables: `BASE_URL`, `TOKEN`
-   Rules documented in `.cursor/rules/postman-api.mdc`
-   Standard workflow: get workspace → get collection → update/create requests → sync

**Review Date**: 2026-06-01

---

## Future Decisions to Document

-   Testing strategy (when implemented)
-   CI/CD pipeline (when implemented)
-   Production deployment strategy
-   Backup and disaster recovery plan
-   Performance optimization strategies
-   Mobile app architecture (if developed)
-   Third-party integration patterns

---

**Next Review**: Review all decisions quarterly to ensure they remain valid and update as needed.
