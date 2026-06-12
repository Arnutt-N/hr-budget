# Plan: Phase 1 — SPA Foundation Upgrade + Auth Hardening

## Summary
The Vue SPA in `frontend/` already exists (Vue 3.5 + TS + Pinia + Router + login + 10 pages) but lacks the PRD's decided stack (PrimeVue, TanStack Query, vee-validate/zod, Vitest, dayjs) and stores JWT in localStorage (XSS-stealable). This phase installs the target stack, switches auth to an httpOnly cookie with CSRF guard, converts LoginPage as the pattern exemplar, and wires unit/E2E tests — establishing the conventions every later phase copies.

## User Story
As an HR staff user, I want my login session to be immune to token theft via script injection, so that budget data stays protected; as the developer, I want the component/query/validation stack installed with one worked example, so later pages follow a proven pattern.

## Problem → Solution
JWT in localStorage + hand-rolled forms/fetch, no unit tests → JWT in httpOnly SameSite=Strict cookie (+ `X-Requested-With` CSRF guard), PrimeVue + TanStack Query + vee-validate/zod wired with LoginPage as exemplar, Vitest running.

## Metadata
- **Complexity**: Large
- **Source PRD**: `.claude/PRPs/prds/vue-spa-refactor.prd.md`
- **PRD Phase**: Phase 1 — SPA Scaffold + Auth (revised: scaffold exists; this is upgrade + hardening)
- **Estimated Files**: ~20 (12 frontend, 4 PHP, 4 tests/config)

---

## UX Design

### Before
```
┌──────────────────────────────────────┐
│ Login (hand-rolled Tailwind form)    │
│ token → localStorage (JS-readable)   │
│ refresh page → still logged in       │
│ logout = client-side wipe only       │
└──────────────────────────────────────┘
```

### After
```
┌──────────────────────────────────────┐
│ Login (PrimeVue InputText/Password/  │
│   Button + vee-validate inline errs) │
│ token → httpOnly cookie (JS-blind)   │
│ refresh page → /auth/me rehydrates   │
│ logout = server clears cookie        │
└──────────────────────────────────────┘
```

### Interaction Changes
| Touchpoint | Before | After | Notes |
|---|---|---|---|
| Login form | native inputs, single error line | PrimeVue components, per-field zod messages (Thai) | Visual style stays Tailwind-consistent |
| Page refresh | reads localStorage instantly | brief bootstrap call to `/auth/me` | Guard awaits one-time hydration |
| Logout | instant client wipe | `POST /auth/logout` then redirect | Server expires cookie |
| All API calls | `Authorization: Bearer` header | cookie sent automatically + `X-Requested-With` header | apiFetch handles centrally |

---

## Mandatory Reading

| Priority | File | Lines | Why |
|---|---|---|---|
| P0 | `frontend/src/stores/auth.ts` | all | The store being rewritten — current login/fetchMe/logout shape |
| P0 | `frontend/src/composables/useApi.ts` | all | Central fetch wrapper — Bearer removal + credentials happen here |
| P0 | `src/Api/Middleware/AuthMiddleware.php` | all | Where cookie fallback + CSRF check are added |
| P0 | `src/Api/Controllers/AuthController.php` | all | login() gains Set-Cookie; logout() is added here |
| P1 | `frontend/src/router/index.ts` | 81-95 | Guard must await async bootstrap |
| P1 | `frontend/src/pages/LoginPage.vue` | all | Exemplar page being converted |
| P1 | `src/Core/Jwt.php` | 25-37, 84-91 | TTL comes from `config/api.php > jwt_ttl` |
| P1 | `src/Api/Responses/ApiResponse.php` | all | Envelope + `$exit=false` test seam |
| P2 | `frontend/vite.config.ts` | all | Proxy keeps dev same-origin (critical for cookies) |
| P2 | `tests/Integration/BudgetRequestSecurityTest.php` | all | Integration-test style to mirror |
| P2 | `frontend/src/main.ts` | all | Plugin registration point |
| P2 | `config/api.php` | all | `jwt_ttl`, `cors_origins` keys |

## External Documentation

| Topic | Source | Key Takeaway |
|---|---|---|
| PrimeVue 4 theming | primevue.org/theming/styled | v4 uses preset themes: `app.use(PrimeVue, { theme: { preset: Aura } })`. GOTCHA: theme package was renamed `@primevue/themes` → `@primeuix/themes` around v4.3 — install whichever matches the primevue version npm resolves; check the install banner on primevue.org if peer-dep errors appear |
| tailwindcss-primeui | primevue.org/tailwind | Official plugin bridges PrimeVue design tokens into Tailwind utilities; supports Tailwind 3 (frontend stays on 3.4 — see Decisions) |
| TanStack Query Vue | tanstack.com/query/latest/docs/framework/vue | `app.use(VueQueryPlugin)`; v5 requires Vue 3.3+. Phase 1 only wires the plugin; per-resource adoption starts Phase 2 |
| vee-validate + zod | vee-validate.logaretm.com | Use `@vee-validate/zod`'s `toTypedSchema(zodSchema)` with `useForm` composition API |
| dayjs Buddhist era | day.js.org plugin buddhistEra | `dayjs.extend(buddhistEra)` + locale `th`; format token `BBBB` renders พ.ศ. |
| MDN SameSite cookies | developer.mozilla.org | `SameSite=Strict` + httpOnly + custom-header requirement = layered CSRF defense; `Secure` flag must be off for http dev |

---

## Patterns to Mirror

### API_RESPONSE_ENVELOPE
```php
// SOURCE: src/Api/Responses/ApiResponse.php:23-29
public static function ok(mixed $data = null, array $meta = [], bool $exit = true): void
{
    $body = ['success' => true, 'data' => $data];
    // success -> { success: true, data } / error -> { success: false, error, details? }
```

### AUTH_DENIAL_LOGGING
```php
// SOURCE: src/Api/Middleware/AuthMiddleware.php:83-89 — every denial logs reason+path+ip, client gets uniform 401
private static function logDenied(string $reason, array $context = []): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '?';
    $path = $_SERVER['REQUEST_URI'] ?? '?';
    error_log("[auth] api_denied reason={$reason} path={$path} ip={$ip}{$extra}");
```

### THIN_CONTROLLER
```php
// SOURCE: src/Api/Controllers/AuthController.php:29-46 — DTO validate → service → ApiResponse; Thai user-facing errors
$dto = LoginRequestDto::fromRequest();
$errors = $dto->validate();
if ($errors !== []) { ApiResponse::validationFailed($errors); return; }
$result = $this->service->authenticate($dto->email, $dto->password);
if ($result === null) { ApiResponse::unauthorized('อีเมลหรือรหัสผ่านไม่ถูกต้อง'); return; }
ApiResponse::ok($result->toArray());
```

### FETCH_WRAPPER
```typescript
// SOURCE: frontend/src/composables/useApi.ts:10-44 — typed envelope, 401 → logout, Thai fallback errors
export async function apiFetch<T = unknown>(path: string, options: RequestInit = {}, isFormData = false): Promise<ApiResponse<T>> {
  // prepends /api/v1, central error normalization
```

### PINIA_SETUP_STORE
```typescript
// SOURCE: frontend/src/stores/auth.ts:31-37 — setup-style stores (ref/computed/function, not options API)
export const useAuthStore = defineStore('auth', () => {
  const token = ref<string>(...)
  const isAuthenticated = computed(() => ...)
```

### ROUTER_GUARD_META
```typescript
// SOURCE: frontend/src/router/index.ts:81-95 — meta.requiresAuth / meta.requiresAdmin checked in beforeEach
router.beforeEach((to) => {
  const requiresAuth = to.matched.some((r) => r.meta.requiresAuth)
  if (requiresAuth && !auth.isAuthenticated) return { name: 'login', query: { redirect: to.fullPath } }
```

### VUE_PAGE_STYLE
```vue
// SOURCE: frontend/src/pages/LoginPage.vue:1-30 — <script setup lang="ts">, ref state, async onSubmit with loading flag, Thai UI strings
```

### TEST_STRUCTURE
```php
// SOURCE: tests/Unit/JwtTest.php + tests/Integration/BudgetRequestSecurityTest.php
// PHPUnit 10.5, ApiResponse::$lastBody / $lastStatus for asserting responses with $exit=false
```

---

## Files to Change

| File | Action | Justification |
|---|---|---|
| `frontend/package.json` | UPDATE | Add primevue, themes pkg, tailwindcss-primeui, @tanstack/vue-query, vee-validate, @vee-validate/zod, zod, dayjs, vitest, @vue/test-utils, happy-dom; add `test:unit` script |
| `frontend/src/main.ts` | UPDATE | Register PrimeVue (Aura preset), ToastService, VueQueryPlugin |
| `frontend/tailwind.config.js` | UPDATE | Add `tailwindcss-primeui` plugin |
| `frontend/src/lib/date.ts` | CREATE | dayjs + buddhistEra + th locale, `formatThaiDate()` helper |
| `frontend/src/stores/auth.ts` | UPDATE | Cookie-based: drop localStorage/token; add `initialized` + `bootstrap()`; logout calls API |
| `frontend/src/composables/useApi.ts` | UPDATE | Remove Bearer; add `credentials: 'same-origin'` + `X-Requested-With: XMLHttpRequest` |
| `frontend/src/router/index.ts` | UPDATE | Guard awaits `auth.bootstrap()` once before first resolution |
| `frontend/src/pages/LoginPage.vue` | UPDATE | PrimeVue InputText/Password/Button + vee-validate/zod (exemplar) |
| `frontend/src/types/api.ts` | UPDATE | AuthResponse: token now optional (compat) |
| `frontend/vitest.config.ts` | CREATE | happy-dom env, `@` alias |
| `frontend/src/stores/__tests__/auth.spec.ts` | CREATE | Unit tests: bootstrap/login/logout (mock fetch) |
| `frontend/src/lib/__tests__/date.spec.ts` | CREATE | พ.ศ. formatting (2026 → 2569) |
| `src/Api/Controllers/AuthController.php` | UPDATE | login() sets cookie; add logout() clearing it |
| `src/Api/Middleware/AuthMiddleware.php` | UPDATE | Cookie fallback + CSRF header check for cookie-authed mutations |
| `routes/web.php` | UPDATE | Add `POST /api/v1/auth/logout` |
| `tests/Integration/AuthCookieTest.php` | CREATE | Cookie set on login, cleared on logout, middleware accepts cookie, CSRF rejection |
| `tests/e2e/auth-login-logout.spec.ts` | CREATE | Login → dashboard → refresh stays in → logout → guard redirects |
| `.claude/PRPs/prds/vue-spa-refactor.prd.md` | UPDATE | Phase 1 → in-progress + plan link + revised decisions |

## NOT Building
- Production build serving under `/hr_budget/public/` (PHP route for SPA shell) — deferred to Phase 6 cutover; dev flow stays Vite :5174 + proxy
- Migrating existing pages/stores (fiscal-years, requests, etc.) to TanStack Query / PrimeVue — Phase 2+ does this page-by-page
- Refresh tokens / sliding sessions — single TTL from `config/api.php > jwt_ttl` is sufficient pre-production
- Tailwind 3.4 → 4 upgrade in `frontend/` — orthogonal churn, no user value now (root Vite/Tailwind-4 build for PHP views is untouched and unrelated)
- Removing the Bearer-token path — kept for PHPUnit tests and future mobile

---

## Step-by-Step Tasks

### Task 1: Install frontend dependencies
- **ACTION**: In `frontend/`: `npm i primevue @primeuix/themes tailwindcss-primeui @tanstack/vue-query vee-validate @vee-validate/zod zod dayjs` and `npm i -D vitest @vue/test-utils happy-dom`
- **GOTCHA**: If `@primeuix/themes` peer-conflicts with the resolved primevue version, use `@primevue/themes` instead (pre-4.3 name). Existing `overrides: rollup 4.24.0` stays.
- **VALIDATE**: `npm run typecheck` passes; `npm ls primevue` resolves.

### Task 2: Wire plugins in main.ts + tailwind config
- **IMPLEMENT**: `app.use(PrimeVue, { theme: { preset: Aura } })`, `app.use(ToastService)`, `app.use(VueQueryPlugin)`; add `require('tailwindcss-primeui')` to tailwind plugins
- **MIRROR**: existing main.ts ordering (pinia → router)
- **VALIDATE**: `npm run dev` boots with no console errors.

### Task 3: PHP — cookie issuance + logout endpoint
- **IMPLEMENT** in `AuthController::login()` after successful auth, before `ApiResponse::ok`:
  ```php
  setcookie('hr_budget_token', $result->token, [
      'expires'  => time() + $result->expiresIn,
      'path'     => '/',
      'httponly' => true,
      'samesite' => 'Strict',
      'secure'   => ($_ENV['APP_ENV'] ?? '') === 'production',
  ]);
  ```
  Add `logout(): void` → same setcookie with `'expires' => time() - 3600`, value `''`, then `ApiResponse::noContent()`. Register `Router::post('/api/v1/auth/logout', ...)` beside the login route (`routes/web.php:33`).
- **MIRROR**: THIN_CONTROLLER; keep returning token in JSON body (Bearer compat)
- **GOTCHA**: `secure` must be conditional — Laragon dev is plain http; a Secure cookie would silently never be stored.
- **VALIDATE**: `vendor/bin/phpunit --testsuite Integration` (after Task 5 tests exist).

### Task 4: PHP — AuthMiddleware cookie fallback + CSRF guard
- **IMPLEMENT** in `AuthMiddleware::require()` before the Bearer check fails: if no Bearer header, read `$_COOKIE['hr_budget_token'] ?? ''`. When the token came **from the cookie** and `$_SERVER['REQUEST_METHOD']` is not GET/HEAD/OPTIONS, require `$_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'`, else `logDenied('csrf_header_missing')` + 403 `ApiResponse::forbidden('Missing CSRF header')`.
- **MIRROR**: AUTH_DENIAL_LOGGING — every new rejection path logs a distinct reason
- **GOTCHA**: Bearer path must keep working unchanged (all existing integration tests + `CorsMiddleware` allow-header list already includes `X-Requested-With`).
- **VALIDATE**: existing Integration suite still green.

### Task 5: PHP — AuthCookieTest integration tests
- **IMPLEMENT**: `tests/Integration/AuthCookieTest.php` — (a) login response carries Set-Cookie semantics (assert via `xdebug_get_headers()` if available, else assert `ApiResponse::$lastBody` + cookie superglobal injection), (b) middleware accepts `$_COOKIE` token for GET, (c) POST with cookie but no `X-Requested-With` → 403, (d) with header → passes, (e) logout clears.
- **MIRROR**: TEST_STRUCTURE (`ApiResponse::$lastStatus/$lastBody`, `$exit=false`)
- **GOTCHA**: test bootstrap already calls `ob_start()` — header assertions need that context (documented CLAUDE.md gotcha).
- **VALIDATE**: `vendor/bin/phpunit --filter=AuthCookieTest`.

### Task 6: Frontend — rewrite auth store (cookie mode)
- **IMPLEMENT**: remove TOKEN_KEY/USER_KEY/localStorage helpers. State: `user`, `initialized`. `bootstrap()`: calls `/auth/me` via apiFetch once, sets user or null, flips `initialized`. `login()`: same endpoint, on success `user.value = json.data.user` (no token storage). `logout()`: `POST /auth/logout` then clear user + `router` redirect handled by caller. `isAuthenticated = computed(() => user.value !== null)`.
- **MIRROR**: PINIA_SETUP_STORE; keep `{ ok, error }` return contract so LoginPage diff stays small
- **VALIDATE**: `frontend/src/stores/__tests__/auth.spec.ts` (Task 9).

### Task 7: Frontend — apiFetch credentials + CSRF header, router bootstrap
- **IMPLEMENT**: in `useApi.ts` drop Bearer block; add `credentials: 'same-origin'` and `headers.set('X-Requested-With', 'XMLHttpRequest')`. In `router/index.ts` make `beforeEach` async: `if (!auth.initialized) await auth.bootstrap()` as the first statement.
- **MIRROR**: ROUTER_GUARD_META — keep existing redirect/query logic intact
- **GOTCHA**: 401-→logout in apiFetch must not loop when `/auth/me` itself 401s during bootstrap — guard by path check or by `logout()` being idempotent without an API call when `user === null`.
- **VALIDATE**: dev server: refresh on `/dashboard` while logged in stays in; logged out redirects to `/login`.

### Task 8: Frontend — LoginPage exemplar (PrimeVue + vee-validate/zod)
- **IMPLEMENT**: `useForm` + `toTypedSchema(z.object({ email: z.string().email('รูปแบบอีเมลไม่ถูกต้อง'), password: z.string().min(1, 'กรุณากรอกรหัสผ่าน') }))`; PrimeVue `InputText`, `Password :feedback="false"`, `Button :loading`. Keep Thai strings + the redirect-query behavior verbatim.
- **MIRROR**: VUE_PAGE_STYLE
- **VALIDATE**: invalid email shows inline Thai error without submitting; valid login redirects.

### Task 9: Frontend — Vitest wiring + unit tests + date util
- **IMPLEMENT**: `vitest.config.ts` (happy-dom, `@` alias mirroring vite.config.ts); `lib/date.ts` (`dayjs.extend(buddhistEra)`, `.locale('th')`, `formatThaiDate(iso) → 'D MMM BBBB'`); specs for auth store (mock global fetch per AAA pattern) and date util (`2026-06-12` → contains `2569`); package script `"test:unit": "vitest run"`.
- **VALIDATE**: `npm run test:unit` green.

### Task 10: E2E spec + regression pass
- **IMPLEMENT**: `tests/e2e/auth-login-logout.spec.ts` — login via UI → expect dashboard; `page.reload()` → still dashboard; logout → `/login`; direct `goto('/fiscal-years')` logged-out → redirected with `?redirect=`.
- **MIRROR**: existing `budget-requests-security.spec.ts` style (baseURL :5174)
- **VALIDATE**: `npm run test:e2e` — new spec + 2 existing specs all green (existing specs exercise the changed auth path end-to-end).

### Task 11: Update PRD
- **ACTION**: Phase 1 row → `in-progress`, PRP column → this file; append Decisions Log rows (PrimeVue styled Aura mode; frontend stays Tailwind 3.4; cookie+header CSRF strategy — resolves Open Question #1); correct Technical Context to note the pre-existing SPA.
- **VALIDATE**: PRD table renders correctly.

---

## Testing Strategy

### Unit Tests
| Test | Input | Expected Output | Edge Case? |
|---|---|---|---|
| auth.bootstrap — valid cookie session | mocked 200 `/auth/me` | `user` set, `initialized=true` | |
| auth.bootstrap — no session | mocked 401 | `user=null`, `initialized=true`, no redirect loop | ✓ |
| auth.login — wrong creds | mocked `{success:false, error}` | `{ok:false, error}` Thai message | |
| auth.logout | mocked 204 | user cleared | |
| formatThaiDate | `'2026-06-12'` | contains `2569` | |
| formatThaiDate — invalid | `''` | safe fallback, no throw | ✓ |
| AuthCookieTest (PHP) | see Task 5 | cookie issue/accept/CSRF-403/clear | ✓ |

### Edge Cases Checklist
- [ ] Expired cookie token → 401 → bootstrap leaves user null → login redirect (no loop)
- [ ] POST with cookie, missing X-Requested-With → 403 (CSRF)
- [ ] Bearer-only request (no cookie) → still works
- [ ] Login while already authenticated → router pushes dashboard (existing guard line 88)
- [ ] `secure` flag off under http dev, on under `APP_ENV=production`

## Validation Commands

### Static Analysis
```bash
cd frontend && npm run typecheck
```
EXPECT: zero errors

### Unit Tests
```bash
cd frontend && npm run test:unit
vendor/bin/phpunit --testsuite Unit
```
EXPECT: all pass

### Integration
```bash
vendor/bin/phpunit --testsuite Integration
```
EXPECT: existing + AuthCookieTest pass (needs `hr_budget_test` DB)

### Build
```bash
cd frontend && npm run build
```
EXPECT: vue-tsc + vite build succeed

### E2E (PHP app running under Laragon, frontend dev server up)
```bash
cd frontend && npm run dev   # :5174 (background)
npm run test:e2e             # from repo root
```
EXPECT: auth spec + 2 existing budget-request specs green

### Manual Validation
- [ ] DevTools → Application → Cookies: `hr_budget_token` shows HttpOnly ✓ SameSite=Strict ✓
- [ ] DevTools → Application → Local Storage: no `hr_budget_token` remains
- [ ] Console: `document.cookie` does NOT reveal the token

## Acceptance Criteria
- [ ] All 11 tasks complete; all validation commands pass
- [ ] Token never readable from JavaScript
- [ ] LoginPage uses PrimeVue + zod validation with Thai messages
- [ ] Existing E2E specs unbroken
- [ ] PRD updated

## Completion Checklist
- [ ] PHP follows PSR-12 / strict scalar types / Thai user-facing errors
- [ ] New rejection paths all log via `logDenied` pattern
- [ ] No `console.log` left in frontend changes
- [ ] No scope creep into Phase 2 page migrations

## Risks
| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| PrimeVue theme pkg naming churn (@primevue/themes vs @primeuix/themes) | M | L | Task 1 GOTCHA — try @primeuix first, fall back |
| Async guard introduces redirect loop on 401 bootstrap | M | M | Task 7 GOTCHA + dedicated unit test |
| Existing E2E specs assume localStorage token | L | M | Specs drive UI, not storage — verify in Task 10; fix selectors only if broken |
| `xdebug_get_headers` unavailable for cookie assertions | M | L | Fallback: assert behavior via $_COOKIE injection instead of header capture |

## Notes
- Decision (supersedes PRD): PrimeVue **styled mode (Aura preset)** + `tailwindcss-primeui`, not unstyled/pass-through — far less work for one developer; revisit only if the theme fights the existing Tailwind look.
- Decision: frontend stays on **Tailwind 3.4** (Tailwind 4 upgrade = churn with no Phase-1 value).
- CSRF strategy resolved (PRD Open Question #1): SameSite=Strict httpOnly cookie + mandatory `X-Requested-With` on cookie-authed mutations + CORS origin allowlist.
- TanStack Query is wired but intentionally unused until Phase 2 — first consumer will be the fiscal-years page migration.
