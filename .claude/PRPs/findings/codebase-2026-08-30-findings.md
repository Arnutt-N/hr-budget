# Codebase Review Findings — 2026-08-30

- **Scope:** whole codebase (no argument given)
- **Method:** 4 review passes per `codebase-review-fix` Step 3 — frontend (Vue/SPA agent), security (agent), tests+config (agent), backend PHP (in-context, agent fell 3× per documented flake fallback). All findings evidence-verified against the working tree before acceptance.
- **Stack:** PHP 8.3 custom MVC JSON API (`/api/v1/*`, layered Api→Services→Repositories+Dtos) + Vue 3 SPA (PrimeVue 4, TanStack Query v5, vee-validate/zod) + MySQL. Gates: `composer verify`, `cd frontend && npm run verify`, pre-push hook. CI disabled.
- **Status:** binding spec for PRD/PRP (`codebase-2026-08-30`). Every accepted finding maps to ≥1 plan task (B7).

## Counts (after merge/dedupe)

| Severity | Accepted | Deferred (documented) | Rejected |
|---|---|---|---|
| Critical | 0 | 0 | 0 |
| High | 8 | 0 | 0 |
| Medium | 11 | 5 | 1 |
| Low | 9 | 1 | 1 |

No Critical findings. Clean areas reported by all agents: SQLi in live repositories (all parameterized), XSS in SPA (zero `v-html`), CORS allowlist, JWT signing hygiene (`assertSecretSafe`, alg pinned), cookie flags (httpOnly/SameSite=Strict), CSRF guard on cookie mutations, session fixation (`session_regenerate_id`), mass assignment (DTO field extraction), envelope compliance (no stray `json_encode`), event-listener leaks in SPA, router guards.

---

## High — accepted (8)

### H1. Vault files deletable by any authenticated user via the request-attachment endpoint
- **Area:** security · **Category:** vulnerability · **Reporters:** security-agent (High), security-agent Medium download-bypass (same root cause, merged), verified in-context
- **Location:** `src/Services/FileService.php:142` (delete), `:114` (download — merged)
- **Evidence:** `if ((int) $file['request_id'] > 0) { $request = Database::queryOne(...); if ($request !== null && $role !== 'admin' && (int) $request['created_by'] !== $userId) { return false; } }` — vault files are stored with `request_id` NULL (`VaultService::upload` inserts no request_id), so the ownership check is skipped entirely: any authenticated user (even viewer) can `DELETE /api/v1/files/{id}`, unlinking the blob and the row, bypassing VaultService's `admin|editor` gate. Download branch has the same hole shape, though for vault files open-read currently matches the vault's documented read policy — the fix aligns both branches with that policy.
- **Fix direction:** for `request_id` null/0 files, apply the VaultService policy (read = any authenticated; mutate = `admin|editor`) instead of falling through to unlink+delete.

### H2. `GET /api/v1/budget-execution` (+`/export`) ignores RBAC org scope
- **Area:** security · **Category:** vulnerability · **Reporters:** security-agent, verified in-context
- **Location:** `src/Api/Controllers/BudgetExecutionController.php:156`
- **Evidence:** `private function resolveOrg(): ?int { $param = $_GET['org'] ?? null; if ($param !== null && ctype_digit((string) $param) && (int) $param > 0) { return (int) $param; } return null; }` — attacker-chosen `?org=` reaches `report()`/`exportRows()` with no `AccessScopeResolver` check (Analytics/Dashboard/Disbursement all scope; this controller does not).
- **Fix direction:** scope `resolveOrg()` through `AccessScopeResolver::can()` like AnalyticsService/DisbursementService so non-admins only see their granted subtree.

### H3. TanStack Query cache survives logout — cross-user data leak in same tab
- **Area:** frontend · **Category:** vulnerability · **Reporters:** frontend-agent, verified in-context
- **Location:** `frontend/src/stores/auth.ts:66`
- **Evidence:** `async function logout(): Promise<void> { ... user.value = null; initialized.value = false` — only local auth state cleared; the query cache (incl. `['me','permissions']` with `staleTime` 5 min in `usePermissions.ts:16`) survives, so the next login on the same tab renders the previous user's cached data/permissions.
- **Fix direction:** `queryClient.clear()` on logout (or key user-scoped queries by user id).

### H4. `vue-tsc -b` emits `vite.config.js` that shadows the tracked `.ts`
- **Area:** config · **Category:** regression-risk · **Reporters:** tests-config-agent, verified in-context
- **Location:** `frontend/tsconfig.node.json:12`
- **Evidence:** `"include": ["vite.config.ts"]` with `composite:true` and no `noEmit` — `vue-tsc -b` emits `vite.config.js`/`.d.ts` (present on disk; `.js` mtime 2026-06-15 02:12 is **older** than `.ts` 03:34). Vite resolves `vite.config.js` before `vite.config.ts`, so a stale untracked `.js` silently wins, endangering the VITE_BASE deploy contract (`base`/`outDir` switching).
- **Fix direction:** `"noEmit": true` in tsconfig.node.json (drop `composite` if required), delete the emitted `vite.config.js`/`vite.config.d.ts`.

### H5. JWT expiry and secret-safety paths have zero test coverage
- **Area:** tests · **Category:** missing-test · **Reporters:** tests-config-agent, verified in-context
- **Location:** `tests/Unit/Api/JwtTest.php:46` (file ends at wrong-signature test); `src/Core/Jwt.php:56,98`
- **Evidence:** `catch (ExpiredException $e) { ... return null; }` untested; `Jwt::assertSecretSafe` (placeholder/short-secret rejection) never exercised.
- **Fix direction:** unit tests: token issued with negative TTL → `verify()==null`; `assertSecretSafe` rejects placeholders/short secrets (reflection reset of `Jwt::$config` + env override, restore after).

### H6. `AuthService::authenticate` untested (login failure modes)
- **Area:** tests · **Category:** missing-test · **Reporters:** tests-config-agent
- **Location:** `src/Services/AuthService.php:25`
- **Evidence:** `public function authenticate(string $email, string $password): ?AuthResponseDto` — no unit/integration test anywhere (grep matches only ThaIdAuthServiceTest); wrong-password / unknown-email / inactive-user paths and the uniform-null anti-enumeration contract only covered by opt-in e2e.
- **Fix direction:** Integration test against `hr_budget_test` seeding a known user (or sqlite-backed pattern like `RbacSqliteTestCase`) covering all failure modes + success token claims.

### H7. CI schema snapshot drifted behind migration 093 — `files.folder_id` still NOT NULL
- **Area:** config · **Category:** regression-risk · **Reporters:** tests-config-agent, verified in-context
- **Location:** `database/hr_budget_only.sql:1244`
- **Evidence:** `  \`folder_id\` int NOT NULL,` while migration `093_make_files_folder_id_nullable.sql` (commit `3aae2d1`) made it nullable; `FileService::upload` inserts `'folder_id' => null` and fails against the snapshot used by CI/e2e.
- **Fix direction:** re-dump `database/hr_budget_only.sql` from the migrated dev DB with the same dump options (mirroring the existing file header) after verifying `hr_budget.files.folder_id` is nullable.

### H8. Integration "security" tests are placeholders that cannot fail
- **Area:** tests · **Category:** bad-practice · **Reporters:** tests-config-agent
- **Location:** `tests/Integration/BudgetRequestSecurityTest.php:152` (also `:16`, `:46`)
- **Evidence:** `'$this->assertTrue(true); // Placeholder'` (cannot_delete_others_request_items); `viewer_cannot_approve_requests` and `user_cannot_approve_own_request` never invoke any approve code — they re-read a row nothing mutated, passing regardless of backend behavior.
- **Fix direction:** rewrite against `BudgetRequestService::approve/reject` with real viewer/admin/owner role args asserting the false-path; delete the placeholder body.

---

## Medium — accepted (11)

### M1. Wizard Step 3 ignores record-detail query errors → editable zero-table over a saved record
- **Area:** frontend · **Category:** edge-case · **Reporters:** frontend-agent
- **Location:** `frontend/src/pages/DisbursementWizardPage.vue:106`
- **Evidence:** `const { data: recordDetail, isLoading: recordLoading } = useDisbursementRecord(recordId)` — `isError` never checked; a failed detail fetch renders a fully editable all-zero table over the existing record, risking overwrite of saved amounts.
- **Fix direction:** show `QueryErrorState` and block navigation/save while the detail query is in error state.

### M2. Unhandled `mutateAsync` rejections in notification UIs (silent no-op clicks)
- **Area:** frontend · **Category:** bug · **Reporters:** frontend-agent (two locations, merged — same root cause)
- **Location:** `frontend/src/components/NotificationBell.vue:39,45` · `frontend/src/pages/NotificationListPage.vue:26,31`
- **Evidence:** `await markRead.mutateAsync(id)` / `await markAllRead.mutateAsync()` with no try/catch — a failed mark-read raises an unhandled rejection and swallows the click (no navigation).
- **Fix direction:** try/catch + Thai error toast (`…ไม่สำเร็จ` life 5000) around both call sites in each file.

### M3. `auth.login()` network failure = silent no-op
- **Area:** frontend · **Category:** bug · **Reporters:** frontend-agent
- **Location:** `frontend/src/stores/auth.ts:48`
- **Evidence:** `const json = (await res.json()) as ApiResponse<AuthResponse>` — fetch/parse unwrapped; `LoginPage` `onSubmit` uses try/finally with no catch, so a network failure / non-JSON 500 shows nothing.
- **Fix direction:** wrap fetch/parse in try/catch returning `{ ok:false, error }` so LoginPage can show its existing error Message.

### M4. Positions silently capped at 100 rows
- **Area:** frontend · **Category:** edge-case · **Reporters:** frontend-agent
- **Location:** `frontend/src/api/positions.ts:15`
- **Evidence:** `const params = new URLSearchParams({ per_page: '100' })` — PositionListPage client-paginates this single fetch; positions beyond the 100th are unreachable in the table and every position Select.
- **Fix direction:** fetch all pages (loop until `meta.total_pages`) in the composable and merge before client pagination.

### M5. `runCommit` performs a destructive year-wide write with no confirmation
- **Area:** frontend · **Category:** bad-practice · **Reporters:** frontend-agent
- **Location:** `frontend/src/pages/ComputeBudgetPage.vue:114`
- **Evidence:** `<Button label="คำนวณ + เขียนงบ" icon="pi pi-save" ... @click="runCommit" />` — replaces computed `budget_line_items` rows for the year in one unconfirmed click.
- **Fix direction:** `confirm.require()` (inline confirm for non-delete destructive action, per skill) before running.

### M6. Header filter Selects lack accessible names (4 pages)
- **Area:** frontend · **Category:** bad-practice · **Reporters:** frontend-agent
- **Location:** `frontend/src/pages/BudgetExecutionPage.vue:91`, `DocumentVaultPage.vue:204`, `AnalyticsPage.vue:105`, `ComputeBudgetPage.vue:92`
- **Evidence:** `<Select v-model="selectedYear" :options="yearOptions" ...>` — no label/aria-label; screen readers announce nothing (violates ui_design_system labeling contract).
- **Fix direction:** Thai `aria-label` on each (visually-hidden label not needed for icon-less filter selects; aria-label sufficient per contract).

### M7. Vault upload `description` is always NULL — masked by PHPStan baseline
- **Area:** backend · **Category:** bug · **Reporters:** tests-config-agent, verified in-context
- **Location:** `src/Services/VaultService.php:204` (property missing at `src/Dtos/CreateFileDto.php:11-19`; baseline `phpstan-baseline.neon:28`)
- **Evidence:** `'description' => $dto->description ?? null` — `CreateFileDto` has no `$description` property (undefined-property warning → null); baseline permanently ignores it.
- **Fix direction:** add `description` to `CreateFileDto` (fromUpload + property), remove the baseline entry.

### M8. DB errors indistinguishable from business rejections in BudgetRequestService
- **Area:** backend · **Category:** bad-practice · **Reporters:** in-context backend review
- **Location:** `src/Services/BudgetRequestService.php:104-107` (same pattern `:205,238,279,317,359`)
- **Evidence:** `} catch (\Throwable $e) { Database::rollback(); return null; }` — swallowed with no `error_log`, unlike DisbursementService which logs (`[DisbursementService::deleteSession] …`).
- **Fix direction:** add `error_log("[BudgetRequestService::<method>] {$e->getMessage()}")` in each rollback catch (matching DisbursementService style).

### M9. `failOnRisky=false` lets assertion-less tests pass
- **Area:** tests · **Category:** bad-practice · **Reporters:** tests-config-agent
- **Location:** `phpunit.xml:10`
- **Evidence:** `'failOnRisky="false"'` + `'failOnWarning="false"'` — exactly the shape that let H8's placeholders exist unnoticed.
- **Fix direction:** set `failOnRisky="true"` (validate the suite stays green; fix any newly-flagged risky tests).

### M10. BudgetRequestTotalCalculationTest re-implements the math it claims to test
- **Area:** tests · **Category:** bad-practice · **Reporters:** tests-config-agent
- **Location:** `tests/Integration/BudgetRequestTotalCalculationTest.php:25`
- **Evidence:** `'// Simulate controller recalculation'` — asserts its own loop (`$total += $item["quantity"] * $item["unit_price"]`), never exercising production total logic.
- **Fix direction:** call `BudgetRequestService::create/update` and assert the persisted `total_amount`.

### M11. Upload (write) paths in FileService/VaultService have no tests
- **Area:** tests · **Category:** missing-test · **Reporters:** tests-config-agent (partial accept — the delete-scoping half is mandatory via H1)
- **Location:** `tests/Unit/Services/FileScopeTest.php:13` (docblock), `src/Services/FileService.php:46`, `src/Services/VaultService.php:168`
- **Evidence:** `'WRITE paths (upload/delete) stay owner-only and are not exercised here'` — only DTO `validate()` is unit-tested.
- **Fix direction:** with H1's fix, add delete-scoping tests (viewer denied on vault files, owner/admin allowed); a full storage seam for `move_uploaded_file` is deferred (see D3).

---

## NEW finding — discovered during implementation (Task 8 test)

### N1 (High). `BudgetRequestService::delete()` always fails — delete-before-log violates the approvals FK
- **Area:** backend · **Category:** bug · **Reporters:** in-context, surfaced by the rewritten `cannot_delete_others_request` integration test (H8 work)
- **Location:** `src/Services/BudgetRequestService.php:234-235` (pre-fix order)
- **Evidence:** the method called `$this->requestRepo->delete($id)` **then** `$this->approvalRepo->log($id, 'deleted', $userId)`. `budget_request_approvals.budget_request_id` has `FOREIGN KEY … REFERENCES budget_requests(id) ON DELETE CASCADE`, so once the request row is deleted the follow-up approval INSERT fails with `SQLSTATE[23000] … 1452` → catch → rollback → return false. **Every owner delete of a draft/saved request has been failing at runtime** (the UI delete action can never succeed for non-admin owners).
- **Fix:** log `deleted` BEFORE the delete (inside the same transaction), then delete; the cascade still removes the history row with the request, but the operation itself now succeeds. A durable audit trail for deletions would need a schema change (approval table not cascading / soft delete) — deferred as D6.
- **Status:** fixed on the branch (same PR); validated by the new integration test.

### D6 (deferred). Deleted-request audit trail does not survive — schema follow-up
`budget_request_approvals` cascades with its request, so a 'deleted' history entry cannot persist. Preserving it needs a schema decision (drop the cascade for this FK, or soft-delete requests). Follow-up issue; not in this pass.

## Deferred with documented reason (previous list)

### D1. No rate limiting on `POST /api/v1/auth/login` — `src/Services/AuthService.php:25`
Deferred: needs a cache/counter infrastructure decision (APCu/file/DB) and product policy (lockout thresholds) — cross-cutting, wrong size for this fix pass. Recommended follow-up issue.

### D2. No server-side JWT revocation on logout — `src/Api/Controllers/AuthController.php:68`
Deferred: stateless-token revocation (denylist or per-user token-version claim) touches every authenticated request's hot path; 1 h TTL mitigates. Follow-up issue.

### D3. Uploads live inside the web root (`public/uploads/...`) protected only by `.htaccess` — `src/Services/FileService.php:58`, `src/Services/VaultService.php:184`
Deferred: moving storage outside the document root requires a data migration of existing blobs + deployment coordination (Apache-only assumption today, nginx fails open). Follow-up issue.

### D4. Vault org-scoping intentionally open to all authenticated users — `src/Services/VaultService.php:21`
Documented in-code as a deliberate deferral ("visibility is currently open to all authenticated users (legacy parity). Tightening … is a tracked follow-up."). Not re-reported as a finding; remains a tracked follow-up.

### D5. vitest 4 runs a nested vite 8 while the project pins vite 5 — `frontend/package.json:40`
Deferred: toolchain alignment (pin vitest 3.x or upgrade vite + @vitejs/plugin-vue together) is an upgrade wave needing its own verification pass; current vitest run works today.

---

## Low — accepted (9)

### L1. No-op query invalidation `['budget-line-items']` — `frontend/src/queries/usePersonnel.ts:229` — remove (no query uses that key).
### L2. Wrong subdirectory path in comment — `frontend/src/api/base.ts:7` says `/hr-budget/public`, actual is `/hr_budget/public` (AGENTS.md); fix comment (check DEPLOY.md occurrence too).
### L3. `ItemEditor` rows keyed by array index — `frontend/src/components/ItemEditor.vue:57` — key by stable generated id.
### L4. `ItemEditor` total computed in floats — `frontend/src/components/ItemEditor.vue:16` — compute in integer cents (mirror `DisbursementWizardPage.toCents`).
### L5. `FileUploader` hardcodes `:disabled="false"` — `frontend/src/pages/RequestDetailPage.vue:246` — gate on the page's existing ownership/role affordance instead of offering an upload the backend must reject.
### L6. `FileController` download headers hand-rolled — `src/Api/Controllers/FileController.php:71` — route through `App\Core\Download::sendFile()` (MIME grammar check, RFC 5987 filename, nosniff) like the vault controller.
### L7. Legacy models interpolate into SQL — `src/Models/Budget.php:76` (`LIMIT {$limit} OFFSET {$offset}`), `src/Models/BudgetPlan.php:45` (`WHERE $column = ?`) — unreachable from current routes; cast to int / whitelist column.
### L8. Health endpoint discloses env name — `routes/web.php:56` (`'env' => $_ENV['APP_ENV'] ?? 'unknown'`) — drop the field.
### L9. `phpstan.neon.dist:10` excludes `src/Core/Http/*` entirely — re-include; fix any newly surfaced errors (if the error count is large, defer with counts recorded at implementation time).

## Low — deferred (2) / rejected (1)

- **Deferred:** `.git/hooks/pre-push` stale advisory note + runs `Unit` not `UnitCI` (`.git/hooks/pre-push:98`) — the hook is local-only and not committed; changing the developer's local hook policy is the user's call, not a code fix.
- **Deferred (during implementation, supersedes accepted L3):** ItemEditor index keys — the original fix direction ("key by stable generated id") requires either mutating the row objects (pollutes the API payload: ItemRow is the model handed to parents and submitted to the backend) or identity-tracking machinery that breaks anyway because `updateField` recreates each row object per keystroke. All inputs are fully controlled (`:value` + `@input`), so index keys are behaviorally correct today; the risk the finding guards against ("if inputs ever become uncontrolled") is hypothetical. Deferred with this reason instead of shipping a no-win change.
- **Rejected:** `AnalyticsService` float casts on chart aggregates (`src/Services/AnalyticsService.php:95` etc.) — display-only aggregation for Chart.js series; authoritative money stays `decimal(15,2)` strings. Not a defect.

---

## Final review (Step 10) — dispositions

Two independent read-only reviewers ran on the fix diff (security + code lens; the change is security-sensitive so both act as the dual adversarial pass). **Both confirmed the flagged Highs are genuinely fixed; zero Critical/High remained (G3 satisfied).**

Accepted and fixed during the review round:
- RequestDetailPage: `v-if="isOwner || isAdmin"` regressed attachment LIST visibility for viewers who may read — restored to rendering the component with `:disabled="!(isOwner || isAdmin)"`.
- FileService: added `containedPath()` (realpath containment mirroring VaultService) for delete/download paths — defense-in-depth flagged by the security reviewer.
- BudgetRequestService::delete: now captures attachment blob paths before the delete and unlinks them after commit (request deletion cascade-deletes `files` rows, orphaning blobs on disk); capture is best-effort so stub-backed unit tests and DB hiccups never block the delete.
- FileScopeTest: disk fixtures moved to `uploads/test-tmp/` (never overlapping real uploads) + directory cleanup in tearDown.
- BudgetRequestService::update: restored correct try/catch indentation (pre-existing).
- ItemEditor cents math now mirrors the backend's `bcmul` truncation (round-at-4dp, truncate-at-2dp) so displayed totals equal persisted totals.

Deferred with reasons (no code change):
- 'deleted' audit row is cascade-deleted with the request — durable deletion audit needs the D6 schema decision.
- CreateFileDto `$description` is currently settable only via API callers (no SPA upload input yet) — the DTO fix itself resolves the undefined-property bug M7; a UI input would be a new feature.

## Reporter coverage

| Pass | Raw findings | Accepted (post-merge) | Deferred | Rejected |
|---|---|---|---|---|
| Frontend agent | 13 | 13 (F1–F13 → H3, M1–M6, L1–L5) | 0 | 0 |
| Security agent | 11 | 8 (S1+S2 merged, S3, S8–S11 → H1, H2, L6–L8) | 2 (D1, D3) + D4 documented | 0 |
| Tests/config agent | 15 | 10 (T1–T6, T8–T11 → H4–H8, M7, M9–M11) | 4 (D5, T12–T13 below) | 0 |
| Backend (in-context) | 3 | 2 (B1→M8, B2→L3-adjacent) | 0 | 1 (float casts) |

Additional documented deferrals from tests/config pass: RoleService/OrganizationService/BudgetCategoryService untested (coverage expansion beyond fix scope); Integration suite not wired into any automated gate (gate policy change = maintainer decision).
