# PRP — Codebase Review Fixes 2026-08-30

## Metadata

- **Date:** 2026-08-30 · **Branch:** `fix/codebase-review-2026-08-30` (already created from `main` @ `2c630db`)
- **Source spec (binding):** `.claude/PRPs/findings/codebase-2026-08-30-findings.md` · **PRD:** `.claude/PRPs/codebase-2026-08-30-prd.md`
- **Stack:** PHP 8.3 custom MVC (no framework) JSON API under `src/` (layering: `Api/Controllers` → `Services` → `Repositories` + `Dtos`; responses only via `App\Api\Responses\ApiResponse`) + Vue 3 SPA under `frontend/` (PrimeVue 4 dark, TanStack Query v5, vee-validate+zod, Thai UI strings). MySQL. Deployed at `/hr_budget/public/`.
- **Verification gates (detect-once, reuse everywhere):**
  - PHP analysis+unit: `PATH="/d/laragon/bin/php/php-8.3.30-Win32-vs16-x64:$PATH" composer verify` (repo root; PHPStan + PHPUnit `Unit`)
  - Integration suite (needs `hr_budget_test`; create with `bash scripts/setup_test_db.sh` if absent): `PATH="/d/laragon/bin/php/php-8.3.30-Win32-vs16-x64:$PATH" vendor/bin/phpunit --testsuite Integration`
  - Frontend: `cd frontend && npm run verify` (vue-tsc + build) · frontend unit: `cd frontend && npm run test:unit` (vitest)
- **Conventions:** English commit messages / Thai UI strings; API layering strict; never `json_encode` outside `ApiResponse`; destructive actions confirmed; tests must not be weakened to pass.

## Files to Change

Backend: `src/Services/FileService.php`, `src/Services/BudgetExecutionService.php`, `src/Repositories/BudgetExecutionRepository.php`, `src/Api/Controllers/BudgetExecutionController.php`, `src/Services/BudgetRequestService.php`, `src/Dtos/CreateFileDto.php`, `src/Api/Controllers/FileController.php`, `src/Models/Budget.php`, `src/Models/BudgetPlan.php`, `phpstan-baseline.neon`, `phpstan.neon.dist`, `phpunit.xml`, `routes/web.php`, `database/hr_budget_only.sql`
Frontend: `frontend/src/lib/queryClient.ts` (new), `frontend/src/main.ts`, `frontend/src/stores/auth.ts`, `frontend/tsconfig.node.json`, `frontend/src/pages/{DisbursementWizardPage,NotificationListPage,ComputeBudgetPage,BudgetExecutionPage,AnalyticsPage,DocumentVaultPage,RequestDetailPage}.vue`, `frontend/src/components/NotificationBell.vue`, `frontend/src/components/ItemEditor.vue`, `frontend/src/api/positions.ts`, `frontend/src/api/base.ts`, `frontend/src/queries/usePersonnel.ts`, `frontend/src/queries/usePositions.ts`
Docs/tests: `DEPLOY.md` (only for the exact Laragon-path typo — see Task 18 L2 guard), `tests/Unit/Api/JwtTest.php`, `tests/Unit/Services/FileScopeTest.php` (extend), `tests/Unit/Services/BudgetExecutionServiceTest.php` (extend), `tests/Integration/AuthServiceTest.php` (new), `tests/Integration/BudgetRequestSecurityTest.php`, `tests/Integration/BudgetRequestTotalCalculationTest.php`, `frontend/src/stores/__tests__/auth.spec.ts` (extend)

## NOT Building

Everything in PRD §"Explicitly NOT building": no rate limiting, no JWT revocation/denylist, no storage relocation, no vault org-scoping change, no vitest/vite upgrade, no RoleService/OrgService/CategoryService test suites, no Integration-gate wiring, no pre-push hook edits, no AnalyticsService changes. No drive-by refactors beyond listed files.

## Step-by-Step Tasks

Each task: ACTION (goal) → IMPLEMENT (specifics) → MIRROR (existing pattern to copy) → VALIDATE (scoped command). **Validate after every task; never accumulate broken state.**

### Task 1 — H1 + M11: FileService authorization for non-request (vault) files + scoping tests

**ACTION:** Close the vault-file delete/read authorization bypass and cover it with tests.
**IMPLEMENT:**
1. `src/Services/FileService.php` — `delete(int $id, int $userId, string $role)`: today the ownership check runs only inside `if ((int) $file['request_id'] > 0)`. Restructure to three explicit branches:
   - `request_id > 0` and parent request row exists → keep current rule (owner or admin).
   - `request_id > 0` but parent request row is **missing** → deny for non-admin (currently falls through and allows).
   - `request_id` NULL/0 (vault files) → allow only `admin` or `editor` (vault mutate policy — `VaultService::ROLES_MUTATE`).
2. Same file, download/read path (~line 114): same three branches — vault files and request files keep current read policies (vault read = any authenticated user, per VaultService docblock; request files = `canReadRequest`), but **missing parent request** now denies for non-admin instead of allowing.
3. Tests in `tests/Unit/Services/FileScopeTest.php` (extend — read its existing sqlite base/fixures first): viewer cannot delete a vault file (request_id NULL) but admin/editor can; owner deletes own request attachment; non-owner (even editor) cannot; missing-parent-request file denies delete for non-admin; viewer CAN download/read a vault file (policy) and CANNOT read another user's request attachment.
**MIRROR:** `src/Services/VaultService.php:33,254` (`ROLES_MUTATE`, `canMutate`) for the role gate; existing `FileScopeTest` patterns for fixtures.
**VALIDATE:** `PATH="/d/laragon/bin/php/php-8.3.30-Win32-vs16-x64:$PATH" vendor/bin/phpunit --filter FileScope` then `... composer analyse`.

### Task 2 — H2: BudgetExecution org scoping (service + repository, mirror Analytics)

**ACTION:** `?org=` (and the unfiltered case) on `/api/v1/budget-execution` + `/export` must respect the caller's RBAC subtree — including the `org_chart` aggregates inside the web response.
**IMPLEMENT:** The SQL lives in `src/Repositories/BudgetExecutionRepository.php` (`breakdownRows(int $fiscalYear, ?int $orgId)` at :35, `orgTotals(int $fiscalYear, ?int $orgId, int $limit)` at :73 — org column is **`ds.organization_id`** in both). Scope in the **service**, mirroring `AnalyticsService::buildScope()`:
1. `src/Repositories/BudgetExecutionRepository.php`: append an optional `?array $scopeFilter = null` param to **both** `breakdownRows()` and `orgTotals()`. When provided and `$scopeFilter['sql'] !== '1=1'`, append ` AND {$scopeFilter['sql']}` (the fragment already names the column, e.g. `ds.organization_id IN (…)` / `1=0`) and merge `$scopeFilter['params']` into the bound params — **before** `orgTotals`' trailing `LIMIT ?` param. When `'1=1'` or null → unchanged.
2. `src/Services/BudgetExecutionService.php` (today: `report(int $fiscalYear, ?int $orgId): array` at :47, `exportRows(int, ?int)` at :95, and **`org_chart` flows through `$this->orgChart()` → `repo->orgTotals()` at :80 → :247-249 — it must be scoped too**): add `private readonly AccessScopeResolver $scopeResolver = new AccessScopeResolver()` to the constructor and append `?array $user = null` to `report()`, `exportRows()`, and `orgChart()`. When `$user !== null`: build `$filter = $this->scopeResolver->orgScopeFilter($user, 'ds.organization_id')`. If an explicit `$orgId` is requested and the filter is not `'1=1'` and `$orgId` is not among `$filter['params']` (or the sql is `'1=0'`) → return `null` (denied). Otherwise pass the filter down to every repo call (breakdown, org totals/chart) **and keep passing `$orgId` through** (the repo's own `AND ds.organization_id = ?` still applies when set; when `$orgId` is null and the user is scoped, the filter constrains to their subtree). **`exportRows()` must forward its `$user` to the internal `$this->report(...)` call (:98) so the null-denial propagates to `/export`** — a missed forward silently re-opens H2 on the export endpoint. Admin/`'1=1'` behavior unchanged.
3. `src/Api/Controllers/BudgetExecutionController.php`: pass `AuthMiddleware::require()`'s user into both calls; map a `null` return to `ApiResponse::forbidden('ไม่มีสิทธิ์เข้าถึงข้อมูลหน่วยงานนี้')` (403). **Note the return types widen** (`array` → `array|null`) on both service methods — PHPStan enforces updating them; the controller is the only caller (verified) plus the existing test.
4. Tests in `tests/Unit/Services/BudgetExecutionServiceTest.php` (extend — sqlite-backed like the other scope tests): viewer with a scoped grant denied on a foreign org id (service returns null), allowed on a granted org id; **the `org_chart` payload in the report excludes non-granted organizations**; admin sees all; scoped viewer without `?org` sees only their subtree rows; a user with **no grants at all** (`'1=0'` deny-all filter) gets null; and the **`exportRows` denial case** (same inputs as the report denial).
**MIRROR:** `src/Services/AnalyticsService.php:278` — `$filter = $this->scopeResolver->orgScopeFilter($user, $column);` inside `buildScope()`; filter shape verified at `src/Services/AccessScopeResolver.php` (`orgScopeFilter` ≈ lines 137–149: `'1=1'` / `IN(...)` / `'1=0'`).
**VALIDATE:** `vendor/bin/phpunit --filter BudgetExecutionServiceTest` + `composer analyse`.

### Task 3 — H3: clear TanStack Query cache on logout

**ACTION:** No cached user-scoped data may survive logout in the same tab.
**IMPLEMENT:**
1. Create `frontend/src/lib/queryClient.ts`: `import { QueryClient } from '@tanstack/vue-query'` → `export const queryClient = new QueryClient()`. Note: `main.ts` currently registers the plugin with **no client and no options** (`app.use(VueQueryPlugin)` at `frontend/src/main.ts:43`) — do not invent defaults; the fresh client preserves current behavior while giving logout a handle to clear.
2. `main.ts`: change to `app.use(VueQueryPlugin, { queryClient })` importing from `@/lib/queryClient`.
3. `frontend/src/stores/auth.ts` `logout()`: after clearing local state, call `queryClient.clear()` (import from `@/lib/queryClient` — module-level import, no circular dependency: the store does not import `main.ts`).
4. Extend `frontend/src/stores/__tests__/auth.spec.ts`: after `logout()`, `queryClient.getQueryCache().getAll()` is empty (mock the logout fetch as the existing tests do).
**MIRROR:** existing `auth.spec.ts` fetch-mocking idioms.
**VALIDATE:** `cd frontend && npx vitest run src/stores` then `npm run typecheck`.

### Task 4 — H4: stop emitting vite.config.js

**ACTION:** `vue-tsc -b` must never produce a shadowing `vite.config.js`.
**IMPLEMENT:** `frontend/tsconfig.node.json`: keep `composite: true` (required by project references) and add `"emitDeclarationOnly": true`. Delete stale artifacts `frontend/vite.config.js` and `frontend/vite.config.d.ts` (both untracked; `frontend/.gitignore:9` already ignores `vite.config.js`). Then run a full build and confirm no `vite.config.js` reappears (a regenerated `vite.config.d.ts` is harmless — Vite never resolves `.d.ts` as config).
**VALIDATE:** `cd frontend && npm run verify` + `ls frontend/vite.config.*` (must show only `.ts` and possibly a fresh `.d.ts`, never `.js`).

### Task 5 — H5: JWT expired-token + secret-safety tests

**ACTION:** Unit-test the two untested paths of `src/Core/Jwt.php`.
**IMPLEMENT:** Extend `tests/Unit/Api/JwtTest.php`:
1. Expired token: build a payload with `'exp' => time() - 10` and encode it via `Firebase\JWT\JWT::encode` using the current test secret (`$_ENV['JWT_SECRET']` from phpunit.xml) with the configured algo (`config/api.php > jwt_algo`); assert `Jwt::verify($token) === null`.
2. Expired token + `assertSecretSafe`: save current `$_ENV['JWT_SECRET']`/`putenv`, set a forbidden placeholder (`'changeme'`) then a short secret (`'abc'`), reset the private cache via **static** reflection — `Jwt::$config` is a `private static ?array $config` (`src/Core/Jwt.php:17`), so use `new \ReflectionProperty(Jwt::class, 'config')` + `setValue(null, null)`. The env override works because `config/api.php:11` reads `$_ENV['JWT_SECRET']` at `require` time (do NOT rely on `putenv()` alone — `config/api.php` does not read `getenv()`). Assert `RuntimeException` on `Jwt::issue(1)` for each case, restore the original env + reflection-reset in a `finally`.
**MIRROR:** existing tests in the same file for imports/style.
**VALIDATE:** `vendor/bin/phpunit --filter JwtTest`.

### Task 6 — H6: AuthService::authenticate tests

**ACTION:** Cover login failure modes + success claims.
**IMPLEMENT:** New `tests/Integration/AuthServiceTest.php` (against `hr_budget_test`): in `setUp` insert a dedicated user (unique email e.g. `auth-test+<uniq>@hr.local`, `password_hash('pass1234', PASSWORD_DEFAULT)`, `is_active = 1`); in `tearDown` delete it. Assert: wrong password → `null`; unknown email → `null` (same response shape — anti-enumeration); deactivated user → `null`; success → `AuthResponseDto` with non-empty token that `Jwt::verify()` accepts (sub = user id) and user data without password.
**MIRROR:** `tests/Integration/AuthCookieTest.php` for DB setUp/tearDown idioms (bootstrap gives PDO via `App\Core\Database`).
**VALIDATE:** `vendor/bin/phpunit --filter AuthServiceTest` (requires `hr_budget_test`; create via `bash scripts/setup_test_db.sh` if missing).

### Task 7 — H7: refresh the CI schema snapshot

**ACTION:** `database/hr_budget_only.sql` must match the migrated dev schema.
**IMPLEMENT:**
1. Confirm dev DB is current: `mysql -u <user> -p hr_budget -e "SHOW COLUMNS FROM files LIKE 'folder_id'"` must show `NULL` allowed (migration 093 applied; vault uploads already insert NULL in dev, so it should).
2. Inspect the first ~20 lines of the existing `hr_budget_only.sql` and re-dump with **the same dump options** so the shape stays comparable (it embeds `CREATE DATABASE` + `USE hr_budget` + data — i.e. dumped with `--databases`). Windows Laragon ships `mysqldump` under the PHP-adjacent MySQL `bin` — reuse the credentials from `.env` (do NOT print them).
3. Sanity-check the result: `grep -n "folder_id" database/hr_budget_only.sql` shows the nullable form; line count same order of magnitude as before; `git diff --stat` shows the file changed but no unrelated tables vanished.
**VALIDATE (mirror CI's import path exactly — do NOT rely on `--filter`, which can silently select zero tests):**
```
mysql -u <user> -p -e "DROP DATABASE IF EXISTS hr_budget_snapshot_check; CREATE DATABASE hr_budget_snapshot_check"
sed -E '/^(CREATE DATABASE|USE )/d' database/hr_budget_only.sql | mysql -u <user> -p hr_budget_snapshot_check
mysql -u <user> -p hr_budget_snapshot_check -e "SHOW COLUMNS FROM files LIKE 'folder_id'"   # must show Null = YES
mysql -u <user> -p hr_budget_snapshot_check -e "SELECT COUNT(*) FROM files"                  # must match dev DB count
```
(adjust the pattern to whatever `CREATE DATABASE`/`USE` lines the dump actually contains — read the file header first; the current dump has a second `USE \`hr_budget\`;` mid-file around line 2634, which is exactly why the line-based `-E` delete (not a range) is prescribed; the row-count check is the backstop). Then `bash scripts/setup_test_db.sh` and one Integration smoke run: `vendor/bin/phpunit --testsuite Integration` (full suite; it is small).

### Task 8 — H8 + M10: make the Integration "security" tests real

**ACTION:** Replace placeholder/no-op tests with service-level assertions.
**IMPLEMENT:**
1. `tests/Integration/BudgetRequestSecurityTest.php`: read the current fixture setup; rewrite `viewer_cannot_approve_requests` (call `BudgetRequestService::approve` as a viewer-role user on an existing submitted request → returns false, `request_status` unchanged), `user_cannot_approve_own_request` (owner calls approve → false, unchanged), and replace the `assertTrue(true) // Placeholder` in `cannot_delete_others_request_items` with a real non-owner denial assertion via the service. Reuse/extend the file's existing fixture helpers; create any missing users/requests in `setUp` with teardown.
2. `tests/Integration/BudgetRequestTotalCalculationTest.php`: replace the self-computed loop with `BudgetRequestService::create` (items with known quantity × unit_price) and assert the **persisted** `total_amount` from `budget_requests`.
**MIRROR:** there is **no existing DB-backed BudgetRequestServiceTest** (`tests/Integration/` contains only `AuthCookieTest.php` + the two files being fixed + `Api/`; `tests/Unit/Services/BudgetRequestServiceTest.php` is repo-stub based, not DB-backed). Use this file's own existing fixture setup + `tests/bootstrap.php`'s PDO (`App\Core\Database::getInstance()`) and construct the real `BudgetRequestService` (defaults wire real repositories).
**VALIDATE:** `vendor/bin/phpunit --testsuite Integration --filter "BudgetRequestSecurityTest|BudgetRequestTotalCalculationTest"`.

### Task 9 — M1: wizard Step 3 error state

**ACTION:** A failed record-detail fetch must not render an editable zero-table.
**IMPLEMENT:** `frontend/src/pages/DisbursementWizardPage.vue`: pull `isError`/`error` from `useDisbursementRecord(recordId)`; while in error state render `<QueryErrorState :error="..." />` in place of the amounts table and disable the step's save/next buttons.
**MIRROR:** `QueryErrorState` usage in `frontend/src/pages/CategoryListPage.vue:115`.
**VALIDATE:** `cd frontend && npm run typecheck` (+ visual check optional).

### Task 10 — M2: notification mutation error handling

**ACTION:** Failed mark-read / mark-all-read must surface an error toast, not an unhandled rejection.
**IMPLEMENT:** `frontend/src/components/NotificationBell.vue` (`handleClick`, `handleMarkAllRead`) and `frontend/src/pages/NotificationListPage.vue` (`open`, `markAll`): **neither file imports `useToast` today** — add `import Toast from 'primevue/toast'`-independent composable import `import { useToast } from 'primevue/usetoast'` and `const toast = useToast()` in each `<script setup>`. Then wrap the `mutateAsync` calls in try/catch; on catch `toast.add({ severity: 'error', summary: 'ทำรายการไม่สำเร็จ', detail: 'ลองใหม่อีกครั้ง', life: 5000 })` (match the established toast shape, e.g. `CategoryListPage.vue:89`). Keep navigation only on success path.
**MIRROR:** existing Thai error-toast pattern (`…ไม่สำเร็จ` life 5000) — grep `severity: 'error'` in `pages/`.
**VALIDATE:** `cd frontend && npm run typecheck`.

### Task 11 — M3: login network-failure feedback

**ACTION:** Network failure / non-JSON response on login shows an error, not silence.
**IMPLEMENT:** `frontend/src/stores/auth.ts` `login()`: wrap the `fetch` + `res.json()` block in try/catch → on failure return the same failure shape used for wrong credentials with Thai message `'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้'` (read the function's current return contract first and preserve it). Extend `frontend/src/stores/__tests__/auth.spec.ts`: `fetch` rejecting → `login()` resolves `{ ok: false, error }` without throwing.
**VALIDATE:** `npx vitest run src/stores` + `npm run typecheck`.

### Task 12 — M4: positions fetch all pages

**ACTION:** No position may be unreachable due to the `per_page=100` cap. (The change lands entirely in `frontend/src/queries/usePositions.ts` — `frontend/src/api/positions.ts` is expected to remain unchanged; it is listed for completeness only.)
**IMPLEMENT:** In the positions composable (`frontend/src/queries/usePositions.ts` — read how it calls `frontend/src/api/positions.ts`): loop `page=1..meta.total_pages` (Promise.all of page fetches after the first response reveals total_pages) and concatenate results before returning; keep the query's existing key/loading semantics. PositionListPage's client-side pagination continues to work unchanged.
**VALIDATE:** `npm run typecheck` + `npx vitest run` (suite green).

### Task 13 — M5: confirm before `runCommit`

**ACTION:** Year-wide budget-line replacement requires confirmation.
**IMPLEMENT:** `frontend/src/pages/ComputeBudgetPage.vue`: wrap `runCommit` in `confirm.require({ header: 'ยืนยันการคำนวณและบันทึกงบ', message: '…จะแทนที่รายการงบประมาณของปีงบนี้ทั้งหมด ต้องการดำเนินการต่อหรือไม่?', accept: runCommit })` (message in Thai, non-delete confirmation = inline `confirm.require` per skill). **The page currently imports neither `useConfirm` nor registers `ConfirmationService`** — check `main.ts` for the global `app.use(ConfirmationDialogService...)` wiring and mirror `FiscalYearListPage.vue:123` (`const confirm = useConfirm()` from `primevue/useconfirm`).
**VALIDATE:** `npm run typecheck`.

### Task 14 — M6: accessible names for header filter Selects

**IMPLEMENT:** Add Thai `aria-label` to the four header Selects: `BudgetExecutionPage.vue:91` (`aria-label="เลือกปีงบประมาณ"`), `DocumentVaultPage.vue:204` (`"เลือกปีงบประมาณ"`), `AnalyticsPage.vue:105` (match its control's purpose — year/org), `ComputeBudgetPage.vue:92` (`"เลือกปีงบประมาณ"`).
**VALIDATE:** `npm run typecheck`.

### Task 15 — M7: CreateFileDto description + baseline cleanup

**IMPLEMENT:**
1. `src/Dtos/CreateFileDto.php`: add `public readonly ?string $description = null` to the constructor and map it in `fromUpload()` from `$_POST['description']` (trim → null when empty). **The dto is built in `src/Api/Controllers/VaultFolderController.php:185`** (`$dto = CreateFileDto::fromUpload()`) — no controller change is needed; `VaultService::upload` already consumes `$dto->description` at line 204.
2. Remove the `CreateFileDto::$description` entry from `phpstan-baseline.neon`.
**VALIDATE:** `PATH=… composer analyse` (must be clean with the baseline entry gone).

### Task 16 — M8: log swallowed DB errors in BudgetRequestService

**IMPLEMENT:** In every `catch (\Throwable $e) { Database::rollback(); … }` of `src/Services/BudgetRequestService.php` (~6 sites: lines 104, 205, 238, 279, 317, 359) add `error_log("[BudgetRequestService::<method>] {$e->getMessage()}");` before the return.
**MIRROR:** `src/Services/DisbursementService.php:230` exact style (`error_log("[DisbursementService::deleteSession] {$e->getMessage()}")`).
**VALIDATE:** `composer analyse` + `vendor/bin/phpunit --filter BudgetRequestServiceTest`.

### Task 17 — M9: failOnRisky

**IMPLEMENT:** `phpunit.xml`: set `failOnRisky="true"`. Run the full Unit suite; if any test is newly flagged risky, give it meaningful assertions (do not delete tests, do not add bare `assertTrue(true)`).
**VALIDATE:** `vendor/bin/phpunit --testsuite Unit` (green) — if flags surface, fix then re-run.

### Task 18 — L1–L9 small fixes batch (one commit)

- **L1:** `frontend/src/queries/usePersonnel.ts:229` — delete the `['budget-line-items']` invalidation line.
- **L2:** `frontend/src/api/base.ts:7` — comment path `/hr-budget/public` → `/hr_budget/public`; grep `DEPLOY.md` for the same underscore-typo (`/hr-budget/public` in a Laragon context) and fix only those — **do NOT touch the Plesk deployment occurrences (`topzlab.com/hr-budget`, `httpdocs/hr-budget`) which are a different, dash-named deployment and are correct as-is.** (Verified: all 9 `/hr-budget/public` occurrences in DEPLOY.md sit in the Plesk section, so DEPLOY.md is expected to end up unchanged — only fix it if a Laragon-context occurrence actually exists.)
- **L3:** `frontend/src/components/ItemEditor.vue` — key rows by a stable id (generate `_key` on add/normalize) instead of array index.
- **L4:** same file, total: compute in integer cents (pattern: `DisbursementWizardPage` `toCents`) and format from cents.
- **L5:** `frontend/src/pages/RequestDetailPage.vue:246` — replace `:disabled="false"` with the page's existing ownership/role condition (read how `canSubmit`/edit gating is computed there; uploader enabled only for users who may edit the request).
- **L6:** `src/Api/Controllers/FileController.php` — serve downloads via `App\Core\Download::sendFile()` (MIRROR: the vault download controller that already uses it).
- **L7:** `src/Models/Budget.php:76` — `(int) $limit`, `(int) $offset` in the interpolation; `src/Models/BudgetPlan.php:45` — whitelist `$column` against an allowed-column array.
- **L8:** `routes/web.php:56` — remove the `'env'` field from the health payload.
- **L9:** `phpstan.neon.dist` — remove the `src/Core/Http/*` exclude, run `composer analyse`; **pre-authorized deviation:** if more than ~20 new errors surface, revert this sub-change and record the error count under "deviations" (defer with counts).
**VALIDATE:** after the batch — `composer verify` + `cd frontend && npm run verify`.

## Validation Commands (full, Step 9)

```
PATH="/d/laragon/bin/php/php-8.3.30-Win32-vs16-x64:$PATH" composer verify
PATH="/d/laragon/bin/php/php-8.3.30-Win32-vs16-x64:$PATH" vendor/bin/phpunit --testsuite Integration
cd frontend && npm run verify
cd frontend && npm run test:unit
```
E2E (`npm run test:e2e`) is out of scope this pass (needs live stack + seeded users; opt-in on CI).

## Testing Strategy

- New/updated tests are mandatory deliverables of Tasks 1, 2, 3, 5, 6, 8, 11 (they ARE the fix for H5/H6/H8/M10/M11).
- Integration tests run only locally against `hr_budget_test` — ensure it exists (`bash scripts/setup_test_db.sh`) and is refreshed after Task 7's re-dump.
- Never weaken existing assertions; risky-test flags get real assertions.
- Scoped validation after each task; full gate before commit/PR.

## Acceptance Criteria

1. `git diff` against `main` shows a change (or documented-deviation entry) for every accepted finding H1–H8, M1–M11, L1–L9 — cross-check against the findings file, zero orphans.
2. All four full-gate commands green.
3. New tests fail on the pre-fix code (spot-check Task 1 & 2 by reasoning: the new scoping tests exercise branches that previously returned true for viewers).
4. Deferred items (D1–D5 + tests/config deferrals) untouched in code, documented in findings file.
5. Commit messages follow `fix(scope): …` / `test(scope): …` convention; UI strings Thai.
