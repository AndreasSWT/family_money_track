# Milestone 1 Implementation Plan - Core Platform

Goal: establish authentication, household management, and role-based access so the app can start capturing data in Milestone 2.

## Scope
- User registration and login.
- Household creation and membership.
- Invite link/QR join flow.
- Role enforcement (owner / editor / viewer).
- Database migrations aligned with `DATABASE/DB.sql`.

## Tasks
1. Define API routes for auth, households, invites, and members.
2. Implement authentication (email + password) with secure hashing.
3. Create household and membership CRUD (owner-only for invites and member removal).
4. Generate invite codes and accept joins.
5. Enforce role checks on all endpoints.
6. Build minimal UI screens: login/register, household create, join by code/link, member list.
7. Add basic logging for auth and membership actions.

## Deliverables
- Auth endpoints and screens.
- Household + members endpoints and screens.
- Invite flow (link + QR stub if needed).
- Migration set for core tables.

## Acceptance Criteria
- A new user can register, log in, create a household, and invite another user.
- A second user can join via invite and has correct role permissions.
- Viewer role cannot edit or delete data.

## Risks
- Role checks missing in some endpoints.
- Invite codes not invalidated after use.

## Suggested Timeline
- 3-5 working days, depending on UI scope.
