# Full Development Plan

This plan expands the milestone list into a complete delivery roadmap for the family PWA budget tracker.

## Assumptions
- Self-hosted/shared hosting, no cloud services.
- PHP 8.4, MySQL 8+ or MariaDB 10.6+.
- Single currency (HUF).
- Roles: owner / editor / viewer.

## Milestone 0 - Scope lock
Objective: finalize decisions to avoid rework.

Scope and tasks:
- Lock OCR to self-hosted Tesseract and define language packs.
- Define roles and permissions matrix.
- Define data retention policy for receipt images and OCR text.
- Confirm hosting baseline and offline conflict strategy.

Deliverables:
- `DEV_DOCS/milestone-0-decisions.md`
- Updated `DEV_DOCS/PRD.md`
- Updated `.env.example`

Exit criteria:
- All open questions resolved and signed off.

Risks:
- Unclear retention requirements.

## Milestone 1 - Core platform
Objective: establish auth, households, and membership.

Scope and tasks:
- User registration/login.
- Household create, invite link/QR join, member management.
- Role checks for owner/editor/viewer.
- Initial database migrations.

Deliverables:
- Auth screens and APIs.
- Household and member APIs.
- DB migrations aligned with `DATABASE/DB.sql`.

Exit criteria:
- A user can create a household and invite another user.

Risks:
- Role rules not enforced consistently.

## Milestone 2 - Expense entry
Objective: build manual expense recording.

Scope and tasks:
- CRUD for expenses.
- Category management at household level.
- Lists and filters (date, category, member).
- Basic validation and permissions.

Deliverables:
- Expense form + list UI.
- Category UI.

Exit criteria:
- A household can record and view expenses with filters.

Risks:
- UI flow not clear on mobile.

## Milestone 3 - PWA and offline
Objective: support offline capture and sync.

Scope and tasks:
- Service worker cache strategy.
- Offline queue with `client_uuid`.
- Sync conflict policy (last write wins).
- Install prompt and basic manifest.

Deliverables:
- PWA installable and functional offline for new entries.

Exit criteria:
- A user can add expenses offline and sync later.

Risks:
- Data loss during sync edge cases.

## Milestone 4 - Charts and analysis
Objective: show trends and summaries.

Scope and tasks:
- Monthly trend chart.
- Category breakdown chart.
- Top categories and totals.
- Basic summary widgets.

Deliverables:
- Dashboard charts with filters.

Exit criteria:
- Charts render correctly for a household for a chosen month.

Risks:
- Performance with large datasets.

## Milestone 5 - Receipt OCR
Objective: enable photo-based expense capture.

Scope and tasks:
- Upload receipt photo.
- OCR pipeline (Tesseract CLI).
- Parsing and field extraction.
- Review/edit UI for parsed data.
- Create expenses from receipt items or total.

Deliverables:
- Receipt upload flow with editable results.

Exit criteria:
- A receipt photo can create a valid expense with manual confirmation.

Risks:
- Low OCR quality on poor images.

## Milestone 6 - Polish and release
Objective: stabilize and prepare MVP release.

Scope and tasks:
- UI polish and consistency pass.
- Performance review and query indexing.
- Basic tests for critical flows.
- Documentation update and deployment notes.

Deliverables:
- Release candidate build.
- Updated docs and changelog.

Exit criteria:
- MVP ready for household use with no critical bugs.

Risks:
- Last-minute scope creep.

## Cross-cutting practices
- Security: password hashing, CSRF protection, role checks on every API.
- Privacy: no third-party processing, local storage only.
- Observability: log errors, basic audit notes for edits.
