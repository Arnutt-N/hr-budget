# Implementation Report: Day 1 — Foundation + Frontend Bootstrap

## Summary

Delivered REST API scaffold (JWT auth, 3 endpoints) + Vue 3 TS SPA (login/dashboard) on top of existing PHP MVC, with 15 unit tests passing and 6 end-to-end integration tests verified against a live PHP + MySQL stack.

## Assessment vs Reality

| Metric | Predicted (Plan) | Actual |
|---|---|---|
| Complexity | Large | Large — matched |
| Confidence | 7/10 | 9/10 — fewer gotchas than expected |
| Files Changed | ~25 new, 3 updated | 29 new, 6 updated |
| Time Estimate | 6-8 hours | ~1.5 hours (Claude-assisted) |

## Tasks Completed

| # | Task | Status | Notes |
|---|---|---|---|
| 1 | composer.json + env vars | ✓ Complete | `firebase/php-jwt v6.11.1` installed |
| 2 | config/api.php | ✓ Complete | |
| 3 | src/Core/Jwt.php | ✓ Complete | |
| 4 | ApiResponse helper | ✓ Complete | **Deviated**: added `$exit=false` flag for testability |
| 5 | CorsMiddleware | ✓ Complete | |
| 6 | AuthMiddleware | ✓ Complete | |
| 7 | LoginRequestDto | ✓ Complete | |
| 8 | AuthResponseDto | ✓ Complete | |
| 9 | AuthService | ✓ Complete | |
| 10 | Api/Controllers/AuthController | ✓ Complete | |
| 11 | Routes + CORS in bootstrap | ✓ Complete | |
| 12 | ApiResponseTest | ✓ Complete | 6 tests |
| 13 | JwtTest | ✓ Complete | 4 tests |
| 14 | LoginRequestDtoTest | ✓ Complete | 5 tests |
| 15 | frontend/package.json | ✓ Complete | **Deviated**: Tailwind 3 (stable) instead of 4 beta |
| 16 | vite.config.ts + tsconfig | ✓ Complete | **Deviated**: split into tsconfig.app.json + tsconfig.node.json |
| 17 | types/api.ts | ✓ Complete | |
| 18 | composables/useApi.ts | ✓ Complete | |
| 19 | stores/auth.ts | ✓ Complete | |
| 20 | router/index.ts with auth guard | ✓ Complete | |
| 21 | main.ts, App.vue, style.css | ✓ Complete | |
| 22 | pages/LoginPage.vue | ✓ Complete | Thai UI |
| 23 | layouts/AppLayout.vue + DashboardPage.vue | ✓ Complete | |
| 24 | .gitignore updates | ✓ Complete | |
| 25 | Integration smoke test | ✓ Complete | **6 live curl tests passed** |

## Validation Results

| Level | Status | Notes |
|---|---|---|
| PHP Syntax (9 new files) | ✓ PASS | `php -l` clean |
| Unit Tests (Backend) | ✓ PASS | 15 tests, 33 assertions, 100% |
| TypeScript Check (Frontend) | ✓ PASS | `vue-tsc --noEmit` clean |
| Frontend Build | ✓ PASS | 97KB gzipped bundle, 4 lazy chunks |
| Integration (Live) | ✓ PASS | 6 curl tests: health, 401 no-token, 401 wrong-pw, 200 login, 200 /me, 204 CORS |

## Files Changed

### Created (29)

| File | Type |
|---|---|
| `config/api.php` | PHP config |
| `src/Core/Jwt.php` | PHP class |
| `src/Api/Responses/ApiResponse.php` | PHP class |
| `src/Api/Middleware/CorsMiddleware.php` | PHP class |
| `src/Api/Middleware/AuthMiddleware.php` | PHP class |
| `src/Api/Controllers/AuthController.php` | PHP class |
| `src/Services/AuthService.php` | PHP class |
| `src/Dtos/LoginRequestDto.php` | PHP DTO |
| `src/Dtos/AuthResponseDto.php` | PHP DTO |
| `tests/Unit/Api/ApiResponseTest.php` | PHP test |
| `tests/Unit/Api/JwtTest.php` | PHP test |
| `tests/Unit/Api/LoginRequestDtoTest.php` | PHP test |
| `frontend/package.json` | Node manifest |
| `frontend/package-lock.json` | Lockfile |
| `frontend/tsconfig.json` | TS config |
| `frontend/tsconfig.app.json` | TS config |
| `frontend/tsconfig.node.json` | TS config |
| `frontend/vite.config.ts` | Vite config |
| `frontend/tailwind.config.js` | Tailwind config |
| `frontend/postcss.config.js` | PostCSS config |
| `frontend/index.html` | HTML entry |
| `frontend/.env.example` | Env template |
| `frontend/.env.development` | Env dev |
| `frontend/.gitignore` | Git ignore |
| `frontend/src/main.ts` | App entry |
| `frontend/src/App.vue` | Root component |
| `frontend/src/env.d.ts` | TS type decl |
| `frontend/src/style.css` | Tailwind entry |
| `frontend/src/types/api.ts` | API types |
| `frontend/src/stores/auth.ts` | Pinia store |
| `frontend/src/composables/useApi.ts` | Fetch wrapper |
| `frontend/src/router/index.ts` | Vue Router |
| `frontend/src/pages/LoginPage.vue` | Login UI |
| `frontend/src/pages/DashboardPage.vue` | Dashboard UI |
| `frontend/src/layouts/AppLayout.vue` | Auth layout |

### Updated (6)

| File | Action |
|---|---|
| `composer.json` | Add firebase/php-jwt |
| `composer.lock` | Update |
| `.env` | Add JWT + CORS vars |
| `.env.example` | Add JWT + CORS template |
| `phpunit.xml` | Add JWT env for tests |
| `tests/bootstrap.php` | Bridge DB_NAME/DB_DATABASE + mirror env to $_ENV |
| `routes/web.php` | Register /api/v1/* routes |
| `public/index.php` | Apply CORS for /api/* |
| `.gitignore` | Exclude frontend/node_modules/, dist/ |

## Deviations from Plan

1. **ApiResponse::*() accepts `$exit=false` param** (plan had `never` return type always exiting)
   - *Why*: PHPUnit can't test `exit` output without `@runInSeparateProcess` — slow. Parameter keeps production safe (default `true`) while tests opt out.

2. **Unit tests extend `PHPUnit\Framework\TestCase` directly, not `Tests\TestCase`**
   - *Why*: Base class hits DB in `setUp()` — pure unit tests (ApiResponse, Jwt, DTOs) don't need DB. Integration tests still extend `Tests\TestCase`.

3. **bootstrap.php bridges DB_NAME → DB_DATABASE naming**
   - *Why*: Pre-existing inconsistency — phpunit.xml uses `DB_NAME`, config/database.php reads `DB_DATABASE`. Fixed in bootstrap without touching shared config.

4. **Tailwind 3 instead of Tailwind 4 beta**
   - *Why*: Tailwind 4 beta's `@tailwindcss/oxide` native binding hit npm optional-deps bug on Windows. Plan's risk register predicted this. Tailwind 3.4 is stable with standard PostCSS setup.

5. **Rollup 4.24 pinned via `overrides`**
   - *Why*: Latest Rollup 4.60+ shipped "source phase imports" that rejects Vite's `<script type="module" src="/src/main.ts">` in index.html. Pin to 4.24 — last version without this breaking check.

6. **Split `tsconfig.json` → tsconfig.app.json + tsconfig.node.json**
   - *Why*: vite.config.ts needs Node types; src/ needs DOM types. Union causes conflicts. Vue community standard = split.

7. **Added `@types/node` to frontend devDependencies**
   - *Why*: Required for `process.cwd()` + `node:url` in vite.config.ts.

## Issues Encountered

1. **PHP not in PATH after Laragon reinstall** (environment-level)
   - *Resolution*: Used full path `/d/laragon/bin/php/php-8.3.30-.../php.exe` for all PHP commands.

2. **ext-zip missing** (required by phpspreadsheet, not yet Day 1 scope)
   - *Resolution*: Composer run with `--ignore-platform-req=ext-zip`. Add to php.ini before Day 4 Excel export.

3. **Tailwind 4 + npm optional deps bug** (see deviation #4)
   - *Resolution*: Downgrade to stable Tailwind 3.

4. **Rollup 4.60 source-phase import semantic change** (see deviation #5)
   - *Resolution*: `overrides.rollup = 4.24.0` in package.json.

## Tests Written

| Test File | Tests | Coverage |
|---|---|---|
| `tests/Unit/Api/ApiResponseTest.php` | 6 | Envelope shape, status codes, Thai UTF-8 |
| `tests/Unit/Api/JwtTest.php` | 4 | Issue/verify roundtrip, tamper detection, wrong signature |
| `tests/Unit/Api/LoginRequestDtoTest.php` | 5 | Validation rules (email format, required, min length) |
| **Total** | **15** | **33 assertions** |

Integration test (Task 25) run live with curl, not automated — add `tests/Integration/Api/AuthControllerTest.php` in Day 2.

## Live Integration Test Results

| # | Test | Result |
|---|---|---|
| 1 | `GET /api/v1/health` | 200 `{success:true, data:{version,time,env}}` |
| 2 | `GET /api/v1/auth/me` (no token) | 401 `{success:false, error:"Missing Bearer token"}` |
| 3 | `POST /api/v1/auth/login` wrong pw | 401 `{success:false, error:"อีเมลหรือรหัสผ่านไม่ถูกต้อง"}` (Thai) |
| 4 | `POST /api/v1/auth/login` correct | 200 with JWT + user |
| 5 | `GET /api/v1/auth/me` (Bearer) | 200 `{id,email,name,role}` |
| 6 | `OPTIONS /api/v1/auth/login` (CORS) | 204 with `Access-Control-Allow-Origin: http://localhost:5174` |

## Next Steps

- [ ] Commit changes: `git add -A && git commit -m "feat(api): Day 1 foundation — REST API + Vue SPA + JWT auth"`
- [ ] Day 2: Budget Request CRUD (REST endpoints + Vue pages + single-level approval)
- [ ] Before Day 4: enable `ext-zip` in php.ini for Excel export
- [ ] Optional: add `phpunit.xml` entry for `tests/Integration/Api/`
- [ ] Optional: create E2E test seed (`tests/Integration/Api/AuthControllerTest.php`)

## Bundle Size (Frontend)

```
dist/index.html                      0.47 kB │ gzip:  0.31 kB
dist/assets/index-*.css              9.18 kB │ gzip:  2.49 kB
dist/assets/AppLayout-*.js           1.07 kB │ gzip:  0.67 kB
dist/assets/DashboardPage-*.js       1.07 kB │ gzip:  0.69 kB
dist/assets/LoginPage-*.js           2.00 kB │ gzip:  1.10 kB
dist/assets/index-*.js              97.22 kB │ gzip: 38.05 kB
```

Total initial load: **~43KB gzipped** — well under 150KB landing-page budget.
