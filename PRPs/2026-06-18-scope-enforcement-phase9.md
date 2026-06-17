# PRP — Phase 9: enforce RBAC org scope on the budget-request list

**Date:** 2026-06-18
**Branch:** `feat/scope-enforcement-phase9`
**Depends on:** Phase 1 (AccessScopeResolver, PR #25), Phase 6 (org tree, PR #30).

## 1. Problem / Goal

The RBAC backend (Phase 1) computes each user's org scope, but nothing **consumed**
`AccessScopeResolver` on read paths — so grants did not actually change what a user sees.
Worse, the request list applied only `if role != admin → created_by = userId`, meaning a user
granted an org-scoped **approver** role could not even *see* the requests they are supposed to
approve unless they created them. Phase 9 makes org scope real on the request list.

## 2. Approach — additive visibility (safe)

Grants only **widen** visibility; no one loses access (important while CI/E2E is paused).

In `BudgetRequestService::list(array $user, …)`:
- `role === 'admin'` (super admin) → all requests (unchanged).
- otherwise resolve the user's scope:
  - `hasAll` (scope=all grant) → all requests (org-wide role).
  - non-empty `orgIds` → **own requests OR requests whose `org_id` is in the granted
    subtree** (`owner_or_orgs` filter).
  - no grants → **own requests only** (legacy behaviour, unchanged).

`BudgetRequestRepository::applyFilters` gains an `owner_or_orgs` branch emitting
`(br.created_by = ? OR br.org_id IN (…))`. The controller now passes the full `$user` array
to the service (was `id` + `role`); it is the only caller of this method.

## 3. Scope (out / deferred)

- `findById` / `show` (single request) still does not deny by scope — Phase 9 is purely the
  **additive list-widening** (the user-facing "approvers can see their queue" win). Tightening
  direct-id access (a *restrictive* change) is a deliberate, separate follow-up.
- Disbursements, files, and other list surfaces — same pattern can follow later.
- Strict deny-by-default (`orgScopeFilter`'s `1=0`) is intentionally NOT used here, to avoid
  locking out ungranted users.

## 4. Testing / Acceptance

`tests/Unit/Services/BudgetRequestScopeTest.php` (SQLite, CI-compatible), 4 cases:
- admin sees all; ungranted sees only own; org-granted sees own + parent/child subtree but
  not unrelated orgs; `all`-scope sees everything.

Verified locally: the 4 new tests pass; the CI-gated suites
(`tests/Unit/{Api,Dtos,Services,Core}`) stay green at 332 tests. (The 4 errors in
`tests/Unit/Models/BudgetTrackingTest` are pre-existing and unrelated — Models is not run in
CI.) No frontend change (the SPA consumes the same endpoint). Shipped via PR with CI paused.

## 5. Risks

- Behaviour change for granted users (they now see more) — intended, and additive so nothing
  is hidden that was previously visible.
- Recursive-CTE subtree resolution already covered by Phase 1 tests; re-exercised here at
  parent→child depth.
