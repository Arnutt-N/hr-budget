# Local Code Review — Day 1 Foundation + Frontend Bootstrap

**Reviewed**: 2026-04-17
**Branch**: feat/day1-foundation-api-vue
**Scope**: 8 modified + 33 new files (after artifact cleanup)
**Decision**: **APPROVE with SUGGESTIONS**

## Summary

Solid Day 1 foundation. REST API scaffold + Vue SPA shell built cleanly following established patterns. Zero CRITICAL issues. 1 HIGH issue (gitignore build artifacts) fixed during review. All validations pass — 15 unit tests, TS clean, production build ok. A few MEDIUM/LOW items worth noting for Day 2+.

## Findings

### 🔴 CRITICAL
**None.**

Summary of what was verified clean:
- ✅ No hardcoded production secrets (`JWT_SECRET` from env, `.env` gitignored)
- ✅ No SQL injection (all queries via PDO prepared statements in `Database::*`)
- ✅ No XSS risk (Vue auto-escapes `{{ }}` interpolation; PHP uses `JSON_UNESCAPED_UNICODE` in API responses — no raw HTML)
- ✅ No path traversal (no file ops with user input in this scope)
- ✅ No CORS wildcard (strict allowlist from `config/api.php`)
- ✅ Timing-safe password verification (`password_verify` in `AuthService`)
- ✅ No user enumeration (login returns same error for "not found" vs "wrong password")
- ✅ JWT signature validated via library (`firebase/php-jwt v6` with `Key` object — not hand-rolled)

### 🟠 HIGH

**H1. Build artifacts polluting git** (FIXED during review)
- **Files**: `frontend/vite.config.js`, `frontend/vite.config.d.ts`, `frontend/tsconfig.node.tsbuildinfo`
- **Issue**: `vue-tsc -b` with `composite: true` in `tsconfig.node.json` emits these alongside `vite.config.ts`. If committed via `git add -A`, they bloat repo and conflict with source `.ts` file.
- **Fix applied**: Added `*.tsbuildinfo`, `vite.config.js`, `vite.config.d.ts` to `frontend/.gitignore` + removed existing artifact files from working tree.
- **Verify**: `git ls-files --others --exclude-standard frontend/` no longer shows these.

### 🟡 MEDIUM

**M1. `scripts/server-check.php` uses weak static token `hrbudget2026`**
- **File**: `scripts/server-check.php:16`
- **Issue**: Static token is guessable. File documents "DELETE after use" but human error: if left on topzlab, anyone who knows the pattern dumps PHP config (PHP version, loaded modules, memory_limit, paths).
- **Suggestion**: Replace with generated random token at upload time (print to console for the owner to paste in URL), or add self-destruct after first successful read.
- **Severity**: Medium — only exploitable if user leaves script deployed, which the docs warn against.

**M2. JWT in localStorage (XSS exfiltration risk)**
- **File**: `frontend/src/stores/auth.ts:54`
- **Issue**: Standard SPA trade-off. XSS in any page → attacker reads `localStorage.hr_budget_token` → session takeover.
- **Alternative**: `httpOnly` cookie + CSRF token (harder for attacker to steal, but adds complexity for CSRF).
- **Recommendation**: Accept for MVP; document the trade-off. If XSS threat model grows (3rd-party scripts, user-generated HTML), migrate to httpOnly cookies.

**M3. No rate limiting on `/api/v1/auth/login`**
- **File**: `src/Api/Controllers/AuthController.php:27-35`
- **Issue**: Attacker can brute-force passwords without throttling.
- **Suggestion**: Add simple in-memory or DB-backed rate limiter (e.g. 5 attempts / 5 min / IP). Day 2+ scope, not blocking Day 1.

### 🟢 LOW

**L1. Predictable `JWT_SECRET` in `phpunit.xml`**
- **File**: `phpunit.xml:40`
- **Value**: `test-secret-min-32-chars-for-hs256-0123456789abcdef`
- **Issue**: Hardcoded test secret visible in repo means anyone with access can forge valid JWTs *for test env*. Not prod risk.
- **Suggestion**: Accept — tests need deterministic secret. No change needed.

**L2. Hardcoded Thai error messages in `LoginRequestDto`**
- **File**: `src/Dtos/LoginRequestDto.php:22-36`
- **Issue**: Messages like `"กรุณากรอกอีเมล"` are inlined — makes i18n later require sweep.
- **Suggestion**: Leave for MVP (project is Thai-first per constraints). If EN support arrives, extract to translation key system.

**L3. `AuthMiddleware::require()` exits response directly**
- **File**: `src/Api/Middleware/AuthMiddleware.php:26-60`
- **Issue**: Middleware couples to response rendering — harder to unit test middleware in isolation.
- **Suggestion**: Leave for MVP. Consider returning `?array` + letting controller decide response in Day 2+ if testability becomes a need.

**L4. `AppLayout.vue` `fetchMe()` has no error feedback**
- **File**: `frontend/src/layouts/AppLayout.vue:11-15`
- **Issue**: If network fails or token is expired after page refresh, user sees "Loading..." briefly then nothing.
- **Suggestion**: Add error toast / fallback UI in Day 2+.

**L5. `frontend/vite.config.ts` hardcoded API target fallback**
- **File**: `frontend/vite.config.ts:9`
- **Value**: `'http://hr_budget.test'` as fallback
- **Issue**: Only works if Laragon auto-pretty-URL is set up. Other environments will fail silently.
- **Suggestion**: Consider throwing error if `VITE_API_URL` is empty in production mode.

## Validation Results

| Check | Result | Details |
|---|---|---|
| PHP Syntax (9 new files) | ✅ Pass | `php -l` all clean |
| PHP Unit Tests | ✅ Pass | 15 tests, 33 assertions |
| Frontend Typecheck (`vue-tsc`) | ✅ Pass | No errors |
| Frontend Build (`vite build`) | ✅ Pass | 97KB bundle / 38KB gzipped |
| Live Integration (6 curl tests) | ✅ Pass | Run during implementation |

## Files Reviewed

### Modified (8)
- `.env.example` — Added JWT_SECRET, JWT_TTL, CORS_ORIGINS templates
- `.gitignore` — Added frontend/node_modules, dist
- `composer.json` — Added firebase/php-jwt
- `composer.lock` — Regenerated
- `phpunit.xml` — Added JWT env vars for tests
- `public/index.php` — Apply CorsMiddleware for /api/*
- `routes/web.php` — Register /api/v1/* routes
- `tests/bootstrap.php` — Mirror env → $_ENV, bridge DB naming

### Added (33 total — subset reviewed)

**Backend PHP (9)**
- `config/api.php`
- `src/Core/Jwt.php`
- `src/Api/Responses/ApiResponse.php`
- `src/Api/Middleware/CorsMiddleware.php`
- `src/Api/Middleware/AuthMiddleware.php`
- `src/Api/Controllers/AuthController.php`
- `src/Services/AuthService.php`
- `src/Dtos/LoginRequestDto.php`
- `src/Dtos/AuthResponseDto.php`

**Backend Tests (3)**
- `tests/Unit/Api/ApiResponseTest.php` (6 tests)
- `tests/Unit/Api/JwtTest.php` (4 tests)
- `tests/Unit/Api/LoginRequestDtoTest.php` (5 tests)

**Frontend Vue (16)**
- `frontend/package.json`, `tsconfig*.json`, `vite.config.ts`
- `frontend/tailwind.config.js`, `postcss.config.js`
- `frontend/index.html`, `frontend/.env.*`, `frontend/.gitignore`
- `frontend/src/main.ts`, `App.vue`, `style.css`, `env.d.ts`
- `frontend/src/types/api.ts`
- `frontend/src/stores/auth.ts`
- `frontend/src/composables/useApi.ts`
- `frontend/src/router/index.ts`
- `frontend/src/pages/LoginPage.vue`
- `frontend/src/pages/DashboardPage.vue`
- `frontend/src/layouts/AppLayout.vue`

**Misc**
- `scripts/server-check.php` (diagnostic, not production code)
- `.claude/PRPs/{prds,plans,reports}/*.md` (documentation)

## Code Quality Metrics

| Metric | Value | Target | Pass |
|---|---|---|---|
| Max file size (new) | 94 lines | < 800 | ✅ |
| Max function size | ~30 lines | < 50 | ✅ |
| Max nesting depth | 3 | < 4 | ✅ |
| Total new source LOC | ~800 | — | ✅ |
| Type coverage (PHP) | High (readonly props, return types) | — | ✅ |
| Type coverage (TS) | 100% strict mode | — | ✅ |

## Decision

**APPROVE with SUGGESTIONS** — ready to commit.

Day 1 scope is well-contained. No blockers. Suggested improvements (M1, M2, M3, L-series) are Day 2+ concerns, not regressions.

## Recommended Next Actions

1. `git add -A && git commit -m "feat(api): Day 1 foundation — REST API scaffold + Vue SPA + JWT auth"`
2. Optionally: `/prp-pr` to open PR for main-branch review before merge
3. Proceed to Day 2: `/everything-claude-code:prp-plan .claude/PRPs/prds/hr-budget-rest-api-vue-refactor.prd.md`

## Items to Track for Day 2+

- [ ] M1: Consider random-token in `server-check.php` or auto-delete
- [ ] M3: Add rate limiting to `/api/v1/auth/login`
- [ ] L4: Add error UI for `fetchMe()` failure in `AppLayout.vue`
- [ ] L5: Validate `VITE_API_URL` presence in production build
- [ ] Before Day 4: Enable `ext-zip` in php.ini for Excel export (PhpSpreadsheet dep)
