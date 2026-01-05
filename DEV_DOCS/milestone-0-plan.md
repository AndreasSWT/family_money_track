# Milestone 0 Implementation Plan

Goal: lock scope and technical decisions so development can start without rework.

## 1) Decisions to finalize
- OCR provider: self-hosted (Tesseract).
- Roles: owner / editor / viewer (permissions matrix).
- Data privacy: receipt image retention, raw OCR storage policy, export/delete rules.
- Hosting baseline: PHP runtime, MySQL version, local storage only.
- Offline sync: conflict strategy (last write wins).

## 2) Deliverables
- Decision log with rationale for each choice.
- Updated `DEV_DOCS/PRD.md` (Open Questions resolved).
- Updated `DATABASE/DB.sql` if any schema changes are required.
- `.env.example` updated with final provider/config keys.

## 3) Progress
- [x] Document decisions in `DEV_DOCS/milestone-0-decisions.md`.
- [x] Update `DEV_DOCS/PRD.md` to reflect scope lock.
- [x] Confirm DB schema changes (no changes required).
- [x] Update `.env.example` with OCR-specific keys.

## 4) Tasks
1. Review PRD open questions and gather constraints (cost, privacy, hosting).
2. Document self-hosted OCR setup requirements (Tesseract versions, language packs).
3. Define roles and permissions matrix.
4. Define retention policy for receipts and OCR text.
5. Confirm deployment target assumptions (PHP 8.4, MySQL 8+).
6. Apply documentation updates and tag milestone completion.

## 5) Acceptance criteria
- All open questions in PRD are resolved and documented.
- Provider/role/retention decisions are signed off by owner.
- No blocking unknowns remain for Milestone 1 work.

## 6) Suggested timeline
- 1-2 working days total, depending on OCR provider evaluation.
