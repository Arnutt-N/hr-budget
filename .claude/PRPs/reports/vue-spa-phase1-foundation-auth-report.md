# Implementation Report: Phase 1 — SPA Foundation Upgrade + Auth Hardening

## Summary
Installed the target frontend stack (PrimeVue 4.5 styled/Aura, TanStack Query 5, vee-validate 4 + zod, Vitest 4, dayjs+buddhistEra) into the existing Vue SPA, and migrated JWT auth from localStorage to an httpOnly SameSite=Strict cookie with an `X-Requested-With` CSRF guard. LoginPage converted as the pattern exemplar. PHP side gained cookie issuance, a logout endpoint, and cookie-aware middleware while keeping the Bearer path intact.

## Assessment vs Reality

| Metric | Predicted (Plan) | Actual |
|---|---|---|
| Complexity | Large | Large |
| Confidence | 8/10 | ~8/10 — one self-inflicted detour (PowerShell encoding corrupted Thai strings in legacy specs; restored from git, redone with Edit tool) |
| Files Changed | ~20 | 21 (excl. plan/PRD docs) |

## Tasks Completed

| # | Task | Status | Notes |
|---|---|---|---|
| 1 | Install frontend deps | done | zod resolved to 3.25 (not v4) — ideal for @vee-validate/zod; `@primeuix/themes` worked first try |
| 2 | Wire plugins (main.ts, tailwind) | done | |
| 3 | PHP cookie issuance + logout | done | `AuthMiddleware::COOKIE_NAME` shared constant; headers_sent() guard for test runs |
| 4 | Middleware cookie fallback + CSRF | done | Bearer takes precedence; distinct logDenied reasons |
| 5 | AuthCookieTest (integration) | done | **Deviation**: only success paths in-process — rejection paths exit the runner; covered via E2E instead |
| 6 | Auth store rewrite (cookie mode) | done | `bootstrap()` + `initialized`; logout idempotent without network when logged out |
| 7 | apiFetch + async router guard | done | Also fixed `AppLayout.vue` which referenced removed `fetchMe()` (not in plan's file list) |
| 8 | LoginPage exemplar | done | Added `name` attrs so legacy `input[name=…]` selectors keep working |
| 9 | Vitest + date util + unit tests | done | 10 tests |
| 10 | E2E spec + regression | done | 7 new tests incl. CSRF-403 and httpOnly assertions |
| 11 | PRD update | done | Phase 1 → complete |

## Validation Results

| Level | Status | Notes |
|---|---|---|
| Static Analysis | Pass | `vue-tsc --noEmit` exit 0 |
| Unit Tests (frontend) | Pass | 10/10 (Vitest) |
| Unit Tests (PHP) | Pass* | 135/140 — 5 failures pre-existing in `BudgetRequestItemTest`/`BudgetTrackingTest` (undefined model methods; untouched files) |
| Integration (PHP) | Pass | 12/12 incl. 3 new AuthCookieTest; 1 pre-existing session_destroy warning (also fires on old tests) |
| Build | Pass | `vue-tsc -b && vite build` 8.3s |
| E2E | Pass* | 26/38 — ALL auth-related green (7 new + 14 Day-1 auth-flow incl. CORS/Bearer). 12 failures are legacy specs (`budget-requests-*.spec.ts` TC09–TC23) written for the old PHP views but running against the SPA — pre-existing drift, slated for Phase 2/4 parity work |

## Files Changed

| File | Action |
|---|---|
| `src/Api/Controllers/AuthController.php` | UPDATED — cookie issue, logout() |
| `src/Api/Middleware/AuthMiddleware.php` | UPDATED — cookie fallback, CSRF gate, COOKIE_NAME |
| `routes/web.php` | UPDATED — POST /api/v1/auth/logout |
| `tests/Integration/AuthCookieTest.php` | CREATED — 3 tests |
| `frontend/package.json`, `package-lock.json` | UPDATED — 9 deps + test:unit script |
| `frontend/src/main.ts` | UPDATED — PrimeVue/Aura, ToastService, VueQueryPlugin |
| `frontend/tailwind.config.js` | UPDATED — tailwindcss-primeui |
| `frontend/src/stores/auth.ts` | UPDATED — cookie mode (no localStorage) |
| `frontend/src/composables/useApi.ts` | UPDATED — credentials + CSRF header |
| `frontend/src/router/index.ts` | UPDATED — async bootstrap guard |
| `frontend/src/pages/LoginPage.vue` | UPDATED — PrimeVue + vee-validate/zod exemplar |
| `frontend/src/layouts/AppLayout.vue` | UPDATED — drop fetchMe, await logout |
| `frontend/src/lib/date.ts` | CREATED — formatThaiDate (พ.ศ.) |
| `frontend/vitest.config.ts` | CREATED |
| `frontend/src/lib/__tests__/date.spec.ts` | CREATED — 3 tests |
| `frontend/src/stores/__tests__/auth.spec.ts` | CREATED — 7 tests |
| `tests/e2e/auth-login-logout.spec.ts` | CREATED — 7 tests |
| `tests/e2e/api/auth-flow.spec.ts` | UPDATED — localStorage-persistence test → cookie-mode assertions |
| `tests/e2e/budget-requests-dashboard.spec.ts` | UPDATED — credential fix only (admin123) |
| `tests/e2e/budget-requests-security.spec.ts` | UPDATED — credential fix only (admin123/viewer123) |

## Deviations from Plan
1. **AuthCookieTest scope** — middleware rejection paths exit the process (ApiResponse exit=true); asserted over real HTTP in E2E instead (401 + CSRF 403 both covered).
2. **AppLayout.vue** — not in plan's file list; referenced the removed `fetchMe()`. Updated to rely on guard bootstrap.
3. **types/api.ts untouched** — plan suggested making `token` optional; backend still returns it, so no change needed (smaller diff).
4. **Legacy spec credential fixes** — `password` → role-specific passwords matching the dev DB; required to validate the changed login path end-to-end.

## Issues Encountered
1. **MySQL/Apache not running** (Laragon closed) — started `mysqld` directly; served PHP via `php -S 127.0.0.1:8000 public/index.php` with `VITE_API_URL` pointing at it.
2. **Playwright browsers missing** — `npx playwright install chromium`.
3. **Dev DB passwords drifted from specs** — admin=admin123, viewer=viewer123, editor=password.
4. **PowerShell text replace corrupted Thai strings** (ANSI read of UTF-8) — restored via `git checkout --`, redone with the Edit tool. Lesson: never bulk-edit UTF-8 Thai files via PS5.1 `Get-Content`/`-replace`.
5. **`api/auth-flow.spec.ts` API-Direct tests need `API_URL`** env (default :18080) — documented here; passing with `API_URL=http://127.0.0.1:8000`.

## Tests Written

| Test File | Tests | Coverage |
|---|---|---|
| `tests/Integration/AuthCookieTest.php` | 3 | cookie acceptance, CSRF-header pass, Bearer precedence |
| `frontend/src/stores/__tests__/auth.spec.ts` | 7 | bootstrap (200/401/once), login (ok/fail), logout (api/skip) |
| `frontend/src/lib/__tests__/date.spec.ts` | 3 | พ.ศ. year, empty, invalid |
| `tests/e2e/auth-login-logout.spec.ts` | 7 | full cookie flow + 401 + CSRF 403 + httpOnly + zod UI |

## Known Pre-existing Issues (NOT addressed — out of scope)
- PHPUnit: `BudgetRequestItemTest`, `BudgetTrackingTest` — 5 failures (undefined model methods / stale stats assertion)
- E2E: `budget-requests-dashboard.spec.ts`, `budget-requests-security.spec.ts` — 12 tests target old PHP-view selectors/seed data; rewrite belongs to Phase 2 (admin/dashboard parity) and Phase 4 (request workflow)
- PHPUnit warning: `session_destroy(): Trying to destroy uninitialized session` from `TestCase::setUp` → `Auth::logout()`

## Next Steps
- [ ] `/code-review` the diff, then commit
- [ ] Phase 2: Admin Master Data CRUD (`/ecc:prp-plan` selects it automatically) — includes rewriting the legacy dashboard/security specs against the SPA
