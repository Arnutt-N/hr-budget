# PRD — Codebase Review Fixes 2026-08-30

**Source spec:** `.claude/PRPs/findings/codebase-2026-08-30-findings.md` (binding)
**Branch:** `fix/codebase-review-2026-08-30`

## Problem statement

A four-pass read-only review (frontend / security / tests+config agents + in-context backend pass) of the whole codebase produced 28 accepted findings: **8 High, 11 Medium, 9 Low** (no Critical). Every finding below restates root cause → impact → why now.

### High (must fix now)

| ID | Root cause | Impact | Why now |
|---|---|---|---|
| H1 | `FileService::delete/download` skip authorization when `files.request_id` is NULL/0 — exactly how vault files are stored | Any authenticated user (incl. viewer) can **delete any vault file** (blob + row) via `DELETE /api/v1/files/{id}`, bypassing VaultService's `admin\|editor` gate | Privilege bypass on a government document vault; exploit needs only a login |
| H2 | `BudgetExecutionController::resolveOrg()` accepts `?org=` with no `AccessScopeResolver` check | Any account reads every organization's budget-execution data (web + export) | Cross-org data exposure; sibling endpoints (Analytics/Dashboard/Disbursement) already scope correctly — this one was missed |
| H3 | `stores/auth.ts` `logout()` clears only local auth state, not the TanStack Query cache | Next login in the same tab renders the previous user's cached notifications/requests/permissions (permissions staleTime 5 min) | Cross-user data leak in the primary shared-computer scenario of an office app |
| H4 | `frontend/tsconfig.node.json` uses `composite` without `noEmit`/`emitDeclarationOnly`; `vue-tsc -b` emitted `vite.config.js` (on disk, **older** than the tracked `.ts`) | Vite resolves `vite.config.js` first → stale untracked config silently wins → the VITE_BASE deploy contract (`base`/`outDir` switch) can break unpredictably per machine | A deploy build following the documented flow can 404 the SPA under `/hr_budget/public/app/` |
| H5 | JWT expired-token branch and `assertSecretSafe` have no tests | Secret-regression or expiry-handling regression would pass the suite silently | Auth core; cheap to close now that the suite is otherwise green |
| H6 | `AuthService::authenticate` (all login failure modes + uniform-null anti-enumeration) untested | Login regressions (e.g. enumeration introduced by an error-message change) undetectable | Login is the security boundary; every other core service already has tests |
| H7 | `database/hr_budget_only.sql` (CI/e2e snapshot) predates migration 093 — `files.folder_id int NOT NULL` | `FileService::upload` inserts NULL `folder_id` → **fails** against CI/e2e DB; vault upload untestable on snapshot DBs | Drift grows with every migration; next CI re-enable would fail confusingly |
| H8 | `tests/Integration/BudgetRequestSecurityTest.php` contains `assertTrue(true)` placeholders and no-op security tests that never call the service | Security coverage theater — approval/delete guards could regress with zero test failure | Same pattern masked by `failOnRisky=false` (M9); fixing together |

### Medium (fix now — small, safe, high-value)

- **M1** `DisbursementWizardPage` ignores record-detail query errors → editable zero-table over a saved record risks overwriting real amounts. *(data integrity)*
- **M2** Notification UIs call `markRead/markAllRead.mutateAsync` without catch → unhandled rejections, clicks silently no-op. *(UX bug)*
- **M3** `auth.login()` lets fetch/parse errors escape → network failure shows nothing on LoginPage. *(UX bug)*
- **M4** Positions API hard-caps `per_page=100` with a single fetch → 101st+ position unreachable anywhere in the SPA. *(correctness)*
- **M5** `ComputeBudgetPage` `runCommit` replaces a year's `budget_line_items` with no confirmation. *(destructive action)*
- **M6** Four page-header filter `Select`s have no accessible name. *(a11y contract violation)*
- **M7** `CreateFileDto` lacks `description`; `VaultService:204` reads an undefined property → vault descriptions always NULL, permanently masked by a phpstan-baseline entry. *(real bug hidden by baseline)*
- **M8** `BudgetRequestService` rollback catches return null/false without `error_log` → DB failures indistinguishable from business rejections when debugging. *(observability)*
- **M9** `phpunit.xml` `failOnRisky=false` → assertion-less tests pass silently (enabled H8). *(test integrity)*
- **M10** `BudgetRequestTotalCalculationTest` re-implements the sum in the test and asserts its own arithmetic. *(coverage theater)*
- **M11** FileService/VaultService upload+delete write paths untested (partial accept: delete-scoping tests are mandatory with H1). *(coverage)*

### Low (fix now — trivial)

L1 no-op `['budget-line-items']` invalidation · L2 wrong subdirectory path in `api/base.ts` comment · L3 ItemEditor index keys · L4 ItemEditor float-cent total · L5 `FileUploader :disabled="false"` hardcoded on RequestDetailPage · L6 FileController download headers bypass `Download::sendFile` · L7 legacy model SQL interpolation (`Models/Budget.php`, `Models/BudgetPlan.php`) · L8 `/api/v1/health` discloses env name · L9 phpstan excludes `src/Core/Http/*` wholesale.

### Explicitly NOT building (deferred/rejected — see findings file for reasons)

Rate limiting on login (D1), JWT server-side revocation (D2), uploads outside web root (D3), vault org-scoping (D4, documented in code), vitest/vite toolchain alignment (D5), full upload storage-seam testing beyond H1's delete-scoping tests (M11 remainder), RoleService/OrgService/CategoryService test suites, Integration suite wiring into automated gates, local pre-push hook edits, AnalyticsService float casts (rejected: display-only).

## Success criteria

1. Every accepted finding H1–H8, M1–M11, L1–L9 has a corresponding change on the branch (M11 via Task 1) — no orphans.
2. All deferred items appear only in the findings file with reasons — no silent drops.
3. Gates green: `composer verify` (PHPStan + Unit) · `vendor/bin/phpunit --testsuite Integration` · `cd frontend && npm run verify` · `npx vitest run` — with the new tests in place.
4. No behavior regressions in existing suites; no weakening of tests/validation to pass.
