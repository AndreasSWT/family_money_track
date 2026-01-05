# Milestone 0 Decisions (Scope Lock)

## Summary
This document records the core scope and technical decisions for Milestone 0.

## Approval
- Approved by: project owner (AndreasSWT)
- Date: 2026-01-05

## Decisions
1) OCR provider: self-hosted Tesseract only.
   - Rationale: no cloud use, full data control, predictable cost.
   - Impact: server must install Tesseract and language packs.

2) Hosting baseline: shared/self-hosted Linux.
   - PHP 8.4, MySQL 8+ or MariaDB 10.6+.
   - Local file storage for receipt images.

3) Roles and permissions: owner / editor / viewer.
   - Owner: manage household, invites, members, categories; full edit/delete.
   - Editor: add/edit/delete expenses and receipts; manage categories.
   - Viewer: read-only access to lists, charts, and exports.

4) Data retention and privacy:
   - Receipt images and parsed data are stored locally until the household deletes them.
   - No third-party processing or sharing.
   - Export and delete are available at household level.

5) Offline conflict strategy:
   - Idempotency via `client_uuid` per expense.
   - Last write wins based on server `updated_at`.
   - If conflict is detected, the newest server state is returned to the client.

## Out of scope (MVP)
- Bank sync, multi-currency, child mode.

## Tesseract setup requirements
- Package: `tesseract-ocr` (>= 5.x).
- Language packs: `hun`, `eng`.
- CLI test: `tesseract input.jpg stdout -l hun+eng`.
