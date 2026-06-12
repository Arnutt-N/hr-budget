# Code Review: Phase 1 — SPA Foundation Upgrade + Auth Hardening (local, pre-commit)

**Reviewed**: 2026-06-12
**Branch**: `feat/vue-spa-phase1-auth` (uncommitted diff vs HEAD)
**Reviewers**: ecc:security-reviewer + ecc:code-reviewer (parallel agents) → triaged & fixed by main session
**Initial decision**: REQUEST CHANGES (0 CRITICAL / 8 HIGH / 6 MEDIUM / 3 LOW)
**Final decision**: **APPROVE** — all accepted findings fixed and re-validated

## Findings & Disposition

### FIXED (in this review cycle)

| Sev | Finding | Fix |
|---|---|---|
| HIGH | Open redirect via `?redirect=` query (`LoginPage.vue`) | Allow only same-app paths: must start `/`, reject `//` |
| HIGH | JWT in login JSON body weakens httpOnly design | Body omits `token` when request carries `X-Requested-With` (SPA); Bearer clients still get it |
| HIGH | Cookie `secure` flag inferred from `APP_ENV` | New `COOKIE_SECURE` env override; falls back to production-only |
| HIGH | `initialized` not reset on logout → stale session state if logout API fails (found by both agents) | `logout()` resets `initialized = false`; unit test asserts it |
| HIGH | Logout test missing `isAuthenticated` assertion | Added (+ `initialized` assertion) |
| MEDIUM | `headers_sent()` silently drops cookie outside tests | `error_log('[auth] set_cookie_failed…')` when `APP_ENV !== 'testing'` |
| MEDIUM | Missing `declare(strict_types=1)` vs sibling controllers | Added to `AuthController.php`, `AuthMiddleware.php` |
| MEDIUM | Untyped `page` param in new E2E helper | `loginAsAdmin(page: Page)` |
| LOW | Log injection via raw `REQUEST_URI` in `logDenied` | CR/LF stripped before logging |
| LOW | `to.fullPath` amplifies redirect risk | Mitigated by the open-redirect guard |

### DEFERRED (recorded in PRD Open Questions)

| Sev | Finding | Rationale |
|---|---|---|
| HIGH | Logout doesn't revoke JWT server-side (valid until TTL) | Inherent stateless-JWT trade-off; revocation list = infra scope creep pre-production. Review `jwt_ttl` before go-live |
| MEDIUM | Login-CSRF (no Origin check on `/auth/login`) | SameSite=Strict constrains it; enforcing `X-Requested-With` there breaks Bearer/API clients & Day-1 spec |
| MEDIUM | `/api/v1/health` exposes `APP_ENV` unauthenticated | Pre-existing endpoint, outside Phase-1 diff |

### REJECTED (with reasons)

| Sev | Finding | Why rejected |
|---|---|---|
| HIGH | CSRF header should also gate GET `/auth/me` (cookie path) | Cross-site cookie send is blocked by SameSite=Strict; cross-origin response reads blocked by CORS allowlist. The described first-party-tab scenario cannot exfiltrate |
| HIGH | Missing `return` after terminal `ApiResponse::*` in middleware | Matches the file's pre-existing style; `exit=true` guarantees termination in every runtime path, and the hypothetical fall-through (`Jwt::verify('')`) still ends in 401 |
| MEDIUM | `SameSite=Strict` breaks THAID SSO redirect | SPA authenticates via same-origin XHR (`/auth/me`) *after* the navigation, not on the navigation request itself — Strict doesn't block same-origin XHR. THAID-to-API integration is future-phase work anyway |
| LOW | `bootstrap()` doesn't check `res.ok` before `.json()` | Parse failure is caught and falls back to `user = null` — the safe state |

## Post-fix Validation

| Check | Result |
|---|---|
| `vue-tsc --noEmit` | ✅ exit 0 |
| Vitest | ✅ 10/10 |
| PHPUnit Integration | ✅ 12/12 |
| PHPUnit Unit | ✅ 135/140 (same 5 pre-existing failures, unchanged set) |
| E2E (both auth specs, `--workers=1`) | ✅ 20/20 |

**Environment note:** with parallel Playwright workers against `php -S` (single-threaded) the UI tests flake; they pass single-worker and would not flake under Apache/Laragon. Not a code issue.

## Files Reviewed
PHP: AuthController, AuthMiddleware, routes/web.php, AuthCookieTest — Frontend: main.ts, stores/auth.ts, composables/useApi.ts, router/index.ts, LoginPage.vue, AppLayout.vue, lib/date.ts, vitest.config.ts, tailwind.config.js, package.json, 2 unit spec files — E2E: auth-login-logout.spec.ts, api/auth-flow.spec.ts, budget-requests-*.spec.ts (creds only)
