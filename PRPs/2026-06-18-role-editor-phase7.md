# PRP — Phase 7: SPA custom-role + permission-set editor

**Date:** 2026-06-18
**Branch:** `feat/role-editor-phase7`
**Depends on:** Phase 1 (role/permission backend, PR #25) + Phase 5 (Roles page scaffold, PR #29).

## 1. Problem / Goal

Phase 5 gave admins a Roles page that can only **toggle** active/inactive and **view**
permissions. To truly support "ทุกสิทธิ์และบทบาท" — defining roles for new agencies beyond the
11 seeded — admins need to **create** custom roles, **edit** a role's name/description, and
**assign its permission set**. The backend already supports all of this (Phase 1:
`RoleController::create/update/delete`, `CreateRoleDto`/`UpdateRoleDto` accept `permissions[]`,
`PermissionController::list`). Phase 7 is the missing frontend.

## 2. Scope (in)

- Extend `api/rbac.ts` + `queries/useRoles.ts`: `createRole`, `deleteRole`, and `updateRole`
  with the full `UpdateRolePayload` (name/description/`permissions[]`/sort/active).
- Rewrite `RoleListPage.vue`:
  - "เพิ่มบทบาท" → create dialog (code [validated `^[a-z][a-z0-9_]{1,49}$`], name_th/en,
    description, permission picker grouped by `resource`).
  - "แก้ไข" per custom role → same dialog prefilled; code locked.
  - "ลบ" per custom role (confirm). System roles (`is_system`) render the dialog **view-only**
    and expose no delete (backend rejects both).
  - Keep the existing active/inactive toggle.
- Tests: extend `api/__tests__/rbac.spec.ts` (createRole / deleteRole / updateRole+permissions).

## 3. Scope (out)

- No backend change (fully built in Phase 1).
- No new permission *definitions* — the catalogue is fixed/seeded.

## 4. Approach

Reuse Phase 5's layered RBAC plumbing + the already-present `usePermissionCatalogue()`. The
permission picker groups the catalogue by `resource` and binds an array of permission `code`s
via PrimeVue `Checkbox` value-arrays. Manual validation (mirrors Phase 5's grant dialog);
backend remains the gate (system-role / privileged-role guards return 422/403 → toast).

## 5. Testing / Acceptance

- `npm run typecheck`, `npm run test:unit` (vitest), `npm run build` — all green locally.
- Manual: create a `regional_supervisor` role with `request.view` + grant it to a user with
  org-scope on a `PROV-RGN-*` node (Phase 6) → that user sees only that region's requests.
- CI gate = typecheck + build (frontend job). No backend/E2E impact.

## 6. Risks

- Permission picker UX with ~17 permissions — mitigated by grouping under `resource` headings.
- Editing a system role: prevented in UI (view-only) and by backend (422).
