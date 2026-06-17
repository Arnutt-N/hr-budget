# PRD — Multi-step Approval Chain (Phase 4)

- **Date:** 2026-06-17
- **Branch:** `feat/multistep-approval-phase4`
- **Depends on:** Phase 1 (approver_* roles + AccessScopeResolver)

## Problem
The approval flow was single-step (a flat `approvers` list per org). Government
budget requests need a sequential chain: **กอง → กรม → กระทรวง**, each step acted
on by the matching approver role and within the approver's org scope.

## Design (additive — does not refactor the existing single-step flow)
- **migration 070**: `approval_levels` (level, code, name_th, role_code) seeded with
  3 levels bound to the Phase 1 roles (`approver_division/department/ministry`);
  `budget_requests.current_level` (pending step); `budget_request_approvals.level`.
- **ApprovalChainService.act(actor, requestId, decision, note)**:
  - request must be `pending` and in the chain (`current_level >= 1`)
  - non-super actor must hold the role for the current level (active grant) AND have
    the request's `org_id` within scope (AccessScopeResolver subtree / hasAll)
  - records the action at its level; `reject` → status `rejected`; `approve` →
    advance `current_level`, or finalize as `approved` at the last level
  - super admin (`users.role='admin'`) may act at any level
- **Endpoints** (permission-gated): `GET /approval-levels`,
  `GET /requests/{id}/approval`, `POST /requests/{id}/approval/{approve|reject}`
  (reuses the existing `ApprovalActionDto` for the note; reject requires a note).

## Tests / verification
- ✅ 7 SQLite unit tests: advance div→dept→ministry, finalize approved, reject stops,
  wrong-level role 403, out-of-scope 403, super-admin any level, non-pending guard.
- ✅ Full unit suite 328 green (no regression).
- ✅ HTTP smoke: `GET /approval-levels` (admin) 200 with 3 levels; no token → 401.
- ✅ migration 070 applied to live DB; `php -l` clean.

## Deferred / follow-up
- Wire the SPA + the existing single-step approve UI to use the stepped endpoints.
- Optional: per-organization custom chains (currently one global 3-level chain).

## Roadmap status
[1✅] RBAC · [2✅] org tree · [3✅] budget snapshots · **[4✅] multi-step approval** — roadmap complete.
