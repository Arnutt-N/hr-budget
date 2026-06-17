# PRP — Phase 5: SPA Admin UI for RBAC + Stepped Approval

**Date:** 2026-06-18
**Branch:** `feat/spa-admin-rbac-phase5`
**Depends on:** Phase 1 (RBAC backend, PR #25) + Phase 4 (multi-step approval, PR #28) — both merged to `main`.

## 1. Problem / Goal

Phases 1 & 4 shipped the **backend** for role-based access control (roles, permissions,
per-user role-scope grants) and a multi-step approval chain (กอง → กรม → กระทรวง), but there
is **no user-facing UI**. Admins cannot manage roles or grant access without raw SQL, and
approvers cannot act on the stepped chain from the SPA. Phase 5 wires the existing
`/api/v1/*` endpoints into the Vue 3 SPA so these backends become usable.

This is a **frontend-only** phase. No backend, schema, or migration changes.

## 2. Scope (in)

1. **Effective-permissions composable** — `useMyPermissions()` over `GET /me/permissions`,
   so the SPA can show/hide actions by permission instead of only the coarse `role==='admin'`.
2. **Roles management page** (`/roles`, admin-only): list seeded + custom roles, show each
   role's permission codes, and **toggle active/inactive** (`PUT /roles/{id}` `is_active`) —
   this is the explicit "เปิด/ปิดการใช้งานผ่านการตั้งค่า" requirement. System roles
   (`is_system=1`, e.g. super_admin) render the toggle disabled (backend rejects disabling).
3. **User access-grants page** (`/users/{id}/access-grants`, admin-only): list a user's
   grants (role + scope + active), **assign** a new grant (role + scope_type +
   org picker for `organization` scope), and **revoke** a grant. Reached via a per-row
   "สิทธิ์" action on the existing user-management table.
4. **Stepped-approval panel** on `RequestDetailPage`: a `ApprovalChainPanel.vue` that reads
   `GET /requests/{id}/approval` + `GET /approval-levels`, renders a level stepper +
   per-level action history, and offers **approve/reject** via the chain endpoints. Buttons
   shown only when the viewer holds `request.approve`; the **backend remains the security
   gate** (wrong-level role / out-of-scope → 403, surfaced as a Thai toast).

## 3. Scope (out / deferred)

- Creating/editing **custom** roles + editing a role's permission set (the 11 seeded roles
  cover current need; backend supports it — defer the editor UI).
- `category` / `region` scope assignment from the UI (backend still rejects these for
  non-super actors; org-level data lacks the category dimension — see Phase 3 follow-ups).
- Replacing the legacy single-step admin quick-approve already on `RequestDetailPage`
  (kept as-is; the stepped panel is additive and only renders when the request is in a chain).

## 4. Approach / Architecture

Follow the SPA's existing layered convention exactly:
`types/*.ts` → `api/*.ts` (thin `apiFetch` wrappers) → `queries/use*.ts`
(TanStack Query hooks, throw on `!res.success`) → `pages/*.vue` + `components/*.vue`.

New files:
- `types/rbac.ts` — `Role`, `Permission`, `AccessGrant`, `MyPermissions`, `AssignGrantPayload`,
  `ApprovalLevel`, `ApprovalStatus`, `ApprovalHistoryItem`, `ApprovalActionResult`, `ScopeType`.
- `api/rbac.ts`, `api/approvalChain.ts`.
- `queries/usePermissions.ts`, `queries/useRoles.ts`, `queries/useAccessGrants.ts`,
  `queries/useApprovalChain.ts`.
- `pages/RoleListPage.vue`, `pages/UserAccessGrantsPage.vue`.
- `components/ApprovalChainPanel.vue`.
- `lib/rbac.ts` — pure display helpers (scope-type label, role severity) → unit-tested.

Modified files:
- `router/index.ts` — `/roles` + `/users/:id/access-grants` (both `requiresAdmin`).
- `layouts/AppLayout.vue` — "บทบาท/สิทธิ์" nav link under the admin "จัดการ" section.
- `pages/UserListPage.vue` — per-row "สิทธิ์" router-link to the grants page.
- `pages/RequestDetailPage.vue` — embed `<ApprovalChainPanel>`.

Auth/permission model: management pages stay gated by the router's existing
`requiresAdmin` (`role==='admin'`), which mirrors the backend `role.manage` / `user.manage`
guards (super-admin only). The finer `useMyPermissions()` gate is used for the approval
panel buttons, where non-admin approver roles must see actions.

## 5. Testing / Acceptance

- `lib/rbac.ts` pure helpers covered by vitest (`lib/__tests__/rbac.spec.ts`).
- `api/rbac.ts` request-shape covered by vitest with a mocked `fetch`
  (`api/__tests__/rbac.spec.ts`) — asserts correct method/URL/body per endpoint.
- **CI gate (must be green):** `npm run typecheck` (vue-tsc) + `npm run build`. The frontend
  CI job runs typecheck + production build; vitest is local. PHP + E2E jobs are unaffected
  (no backend change).
- Manual acceptance: as admin, toggle a role inactive → it disappears from assignable roles;
  assign org-scoped approver role to a user → that user can approve only their org's requests;
  approve through all 3 levels → request reaches `approved`.

## 6. Risks

- **Type drift** vs backend JSON (tinyint `is_active` arrives as `0|1` number). Mitigated by
  reading the exact controller/repository shapes before typing.
- Two approval flows on one page. Mitigated by making the stepped panel additive + clearly
  labelled "สายอนุมัติหลายชั้น".
