# Phase 6 — Cutover + Cleanup (Vue SPA is the only frontend)

> PRP plan. Final phase of the Vue SPA refactor (PRD: `.claude/PRPs/prds/vue-spa-refactor.prd.md`, Phase 6 row).
> Generated 2026-06-15. Single-pass implementation target — no further codebase searching should be required.

---

## Summary

The Vue 3 SPA (`frontend/`) now covers every migrated feature — auth, dashboard, notifications, all admin master-data CRUD, the budget-request lifecycle, and the disbursement/tracking wizard — over the existing JWT-cookie REST API (`/api/v1/*`). Phase 6 makes the SPA the **only** frontend:

1. **Build the SPA for subdirectory deployment** and serve its shell from PHP.
2. **Add a single catch-all web route** that returns the SPA `index.html` for any non-API, non-asset path, so deep links (`/disbursements/wizard`, `/requests/42`) load the SPA instead of a PHP 404.
3. **Retire the legacy web/MVC surface** (web controllers + `resources/views`) by moving it out of the live route table — reversibly, preserving git history.
4. **Remove session-auth web login** (the `/login` `/logout` `/forgot-password` web routes + `Auth::attempt`/session login path) now that `/api/v1/auth/*` + httpOnly-cookie JWT fully replaces it.
5. **Keep `/api/v1/*` and its entire `src/Api` → `src/Services` → `src/Repositories` → `src/Dtos` stack 100% untouched.**
6. **Update docs + test config**: CLAUDE.md (SPA-only architecture), a top-level README note, Playwright base config, and quarantine/retire legacy E2E specs that target removed PHP views.

Success signal: `npm test` (PHPUnit Unit + Integration, then Playwright E2E) is green, and the repo serves exactly one frontend.

**Two issues that MUST be resolved in this plan (and are, below):**
- **`archives/` is git-ignored** (`.gitignore:56`). Moving code there deletes it from version control. → We do **not** archive into `archives/`. See [Archive strategy decision](#archive-strategy-decision-critical).
- **There is no production SPA-serving route today**, and the SPA currently builds with `base: '/'` which breaks under the `/hr_budget/public/` subdirectory. → We define the serving mechanism and fix the base. See [Catch-all route design](#catch-all-route-design) and [Task 1](#task-1--build-the-spa-for-subdirectory-deployment).

---

## User Story

**As** the solo developer (and the HR division staff who use the app),
**I want** the PHP backend to serve only the JSON API plus the compiled Vue SPA shell, with the legacy server-rendered pages removed from the live routing,
**so that** there is exactly one frontend to maintain, deep links resolve to the SPA, and no stale PHP page can be reached or accidentally edited.

**Acceptance criteria**
- Visiting `/hr_budget/public/` (or `/`) serves the SPA shell; the SPA boots and routes client-side.
- Deep-linking directly to `/hr_budget/public/disbursements/wizard` (hard refresh) serves the SPA shell, and Vue Router lands on the wizard — not a PHP 404.
- All `/api/v1/*` endpoints respond exactly as before (verified by the unchanged PHPUnit + API E2E suites).
- The legacy web login (`GET/POST /login`, `/logout`) no longer exists as web routes; primary auth happens via `/api/v1/auth/*`. (ThaID `/thaid/login` is KEPT as a documented parity-gap remnant; see Parity gaps.)
- `vendor/bin/phpunit` (Unit + Integration) and `npm run test:e2e` are green; CI's 3 jobs (PHP Tests, Frontend Build, E2E) pass.
- **Partial cutover (per user decision 2026-06-15):** the SPA (`frontend/`) is the **primary** frontend; the only web/MVC surfaces still wired into routing are the documented legacy remnants — document vault (`/files`, `/folders`), ThaID login (`/thaid/login`), budget-execution reporting (`/budgets`, `/budgets/list`, `/budgets/export`), error views, and (conditionally) forgot-password. All admin-CRUD, dashboard, budget-request, and tracking/disbursement web surfaces are retired.

---

## Mandatory Reading (read these before/while implementing)

| File | Why |
|------|-----|
| `routes/web.php` | The single file that wires everything. API block (lines 32–143) stays; web block (lines 145–266) is what we retire. Catch-all goes at the very end. |
| `src/Core/Router.php` | Static facade + `dispatch()` script-prefix stripping (lines 79–139), `notFound()` (167–171). Catch-all must be a real registered route OR a change to `notFound()`. |
| `public/index.php` | Bootstrap order: ErrorHandler → Dotenv → CORS-for-`/api/` → `Auth::init()` → routes → `Router::dispatch()`. The SPA shell route runs through this same pipeline. |
| `index.php` (root) | Just `require __DIR__ . '/public/index.php'`. No change. |
| `.htaccess` + `public/.htaccess` | Rewrite of non-existent paths into `public/index.php`. Already correct for SPA deep links — verify, don't rewrite. |
| `src/Core/View.php` | `baseUrl()` (139–143) returns the script-dir prefix (e.g. `/hr_budget/public`). `vite()` (251–273) is the **old vanilla-JS** Vite helper — NOT used by the Vue SPA. The SPA shell route needs its own tiny shell renderer or static file. |
| `frontend/vite.config.ts` | `build.outDir: 'dist'`, no `base` set (defaults to `/`). Must add `base` for subdirectory. |
| `frontend/index.html` | The shell template. References `/src/main.ts` (dev) → rewritten to hashed `/assets/*.js` on build. |
| `frontend/src/router/index.ts` | `createWebHistory()` with no base (line 125). Must pass the subdirectory base so client routes match the deployed path. |
| `frontend/package.json` | `build` = `vue-tsc -b && vite build`. Output → `frontend/dist/`. |
| `.gitignore` | Line 8 ignores `/frontend/dist/`; line 56 ignores `archives/`. Both matter (see decisions). |
| `playwright.config.ts` | `baseURL` defaults to `http://localhost:5174` (Vite dev server); `webServer` commented out. |
| `.github/workflows/ci.yml` | **The real merge gate.** 3 jobs: `backend` runs `phpunit tests/Unit/{Api,Dtos,Services}` (web-side retirement does not touch these); `frontend` runs `npm run build` (production mode) + uploads `frontend/dist` (line 147) → the DEFAULT build MUST keep base `/` + `dist` output; `e2e` builds + `vite preview`s the SPA at `:5174` (base `/`) and runs **only `tests/e2e/api`** (line 304) — the SPA UI specs are NOT run in CI. So the cutover is CI-safe as long as the default build, `/api/*` JSON-404, and the api e2e specs stay intact. Do NOT change the subdirectory base in a way that alters `npm run build`. |
| `phpunit.xml` | Unit + Integration suites; `source` already excludes `resources` and `public`. Archiving views/controllers will not change coverage config. |
| `CLAUDE.md` | Sections to update: Request lifecycle (35), Routing (41), Views (56), Authentication (67), Domain modules (71), Key gotchas (110). |
| `tests/e2e/budget-requests-dashboard.spec.ts` | Legacy spec hitting PHP view `/requests/dashboard` with PHP-DOM assertions — will break post-cutover. Must be retired/quarantined. |

---

## Patterns to Mirror

- **Router facade**: register routes via `Router::get('/path', handler)`. The catch-all is a closure handler (mirrors the existing inline closures at `routes/web.php:33` health and `:167` legacy redirect). Route params use `{name}` → regex; the catch-all needs a wildcard the current router does NOT natively support (`{id}` excludes `/`). See GOTCHA in Task 2 for the chosen approach.
- **Subdirectory awareness**: never hardcode `/hr_budget/public`. Use `View::baseUrl()` (script-dir prefix) on the PHP side and Vite `base` / `import.meta.env.BASE_URL` on the Vue side — exactly as the SPA already does for `/api` (same-origin relative paths).
- **API response envelope**: untouched — do NOT add any new API endpoint in this phase. Cutover is web-side only.
- **E2E shape**: existing SPA specs (e.g. `tests/e2e/admin-fiscal-years.spec.ts`, `disbursement-tracking-workflow.spec.ts`) log in via the SPA `/login` page and assert on PrimeVue/Vue DOM. New/updated specs mirror this; they target `baseURL` (the SPA), never `http://hr_budget.test/...`.
- **Reversible retirement via git**: use `git mv` (or `git rm` with history retained) — never a raw filesystem move into a git-ignored path.

---

## Files to Change

**Modify**
- `frontend/vite.config.ts` — add `base` (subdirectory-aware) for production build.
- `frontend/src/router/index.ts` — pass `createWebHistory(import.meta.env.BASE_URL)`.
- `routes/web.php` — remove web/MVC routes (lines ~145–266); add the SPA catch-all + shell route at the end; KEEP the API block.
- `playwright.config.ts` — document/lock the SPA base URL; optionally add a `webServer` block to auto-start the SPA preview for E2E.
- `CLAUDE.md` — rewrite the web-MVC architecture sections to describe SPA-only serving.
- `README.md` (top-level) — create if absent; add a short "Frontend = Vue SPA in `frontend/`, served by PHP catch-all" note.
- `.gitignore` — see decision: we will commit the built SPA shell + assets to a **tracked** location (`public/app/`), so add an allowlist exception OR build into a non-ignored dir. (Resolved below.)

**Create**
- `public/app/` (tracked) — the deployed SPA build output (`index.html` + `assets/`). Vite `build.outDir` points here. (Resolution of the dist-is-git-ignored problem.)
- A thin PHP shell route handler in `routes/web.php` (closure) OR `src/Controllers/SpaController.php` (one file) that reads and echoes `public/app/index.html`.

**Retire (move out of live routing — see Archive strategy)**
- `src/Controllers/*.php` — all 14 legacy web controllers EXCEPT see parity-gap exclusions.
- `resources/views/**` — all legacy views EXCEPT `errors/*` (still used by `Router::notFound()` for non-SPA/API 404s if we keep that path) — see Task 4 for the errors decision.
- `public/export_execution.php` — legacy execution export script (web-side).

**Do NOT touch**
- `src/Api/**`, `src/Services/**`, `src/Repositories/**`, `src/Dtos/**`, `src/Core/Auth.php`, `src/Core/Jwt.php`, `src/Api/Middleware/**`, `src/Api/Responses/**`.
- The entire `/api/v1/*` route block (`routes/web.php:32–143`).
- `tests/Unit/**`, `tests/Integration/**` (API-layer tests — unaffected).

---

## NOT Building

- **No new API endpoints.** If a legacy web feature lacks an API, it is a parity gap and is KEPT, not ported (porting is a follow-up, not Phase 6).
- **No SSR / prerendering.** Static shell only.
- **No deletion of git history.** Retirement is reversible.
- **No change to JWT/cookie auth, CORS, or CSRF strategy.**
- **No visual redesign** of the SPA.
- **No removal of `Auth.php` session machinery wholesale** — `Auth::init()` still runs in bootstrap and the API login path may rely on session-backed pieces; we only remove the **web login routes/controller methods**, not the `Auth` class. (Verify `src/Api/Controllers/AuthController` does not call web `Auth::attempt`; if the API has its own login it's independent.)
- **No migration of the standalone document vault (`/files`)** — parity gap, see below.

---

## Parity Check (web feature → SPA route)

| Legacy web controller / route | SPA equivalent | Status |
|---|---|---|
| `AuthController` `/login`,`/logout`,`/forgot-password`,`/thaid/login` | SPA `LoginPage.vue` + `/api/v1/auth/*` | Replaced (login/logout). **ThaID + forgot-password = gaps, see below.** |
| `DashboardController` `/`,`/dashboard`,`/api/dashboard/chart-data` | `DashboardPage.vue` + `/api/v1/dashboard/*` | Replaced. (Legacy `/api/dashboard/chart-data` is a *web* endpoint distinct from `/api/v1/dashboard/chart-data` — retire the web one.) |
| `BudgetRequestController` `/requests*`, `/requests/dashboard` | `RequestListPage`/`RequestCreatePage`/`RequestEditPage`/`RequestDetailPage` + `/api/v1/requests/*` | Replaced (list/create/edit/detail/submit/approve/reject). `/requests/dashboard` has **no exact SPA route** — see gap note. |
| Admin* controllers (`AdminBudgetCategory`, `AdminBudgetCategoryItem`, `AdminOrganization`, `AdminTargetType`), `BudgetTargetController`, `DivisionController`, `BudgetPlanController` | `CategoryListPage`, `OrganizationListPage`, `TargetTypeListPage`, `TargetListPage`, `DivisionListPage`, `PlanListPage`, `UserListPage` + `/api/v1/*` | Replaced. |
| `BudgetController` / `BudgetExecutionController` (`/budgets`, `/budgets/list`, `/budgets/tracking*`) | Disbursement wizard (`DisbursementWizardPage`) + `/api/v1/disbursement-*`, `/api/v1/expense-structure` | Replaced for tracking/disbursement. **Budget *execution/list read views* (`/budgets`, `/budgets/export`) = gap, see below.** |
| `DisbursementController` (`/budgets/disbursements*`) | `DisbursementListPage` + wizard | Replaced. |
| `FileController` (`/files`, `/folders`, `/files/init` — document vault) | SPA only has **request-attachment** upload (`FileUploader.vue` → `/api/v1/requests/{id}/files`). The standalone per-fiscal-year **document vault has no SPA page and no `/api/v1/files` listing/folder API.** | **PARITY GAP — KEEP.** |

### Parity gaps (KEEP these — exclude from cutover, flag as Phase-6 follow-up)

1. **Document vault** — `src/Controllers/FileController.php` + `resources/views/files/**` + routes `routes/web.php:244–250` (`/files`, `/files/upload`, `/files/{id}/download`, `/files/{id}/delete`, `/folders`, `/folders/{id}/delete`, `/files/init`) + `File`/`Folder` models. No SPA equivalent, no API. **Keep these web routes + controller + views live.**
2. **ThaID login** (`/thaid/login`, `Auth::mockThaIDLogin`) — the SPA login does not implement a ThaID flow. **Keep the `/thaid/login` route + `AuthController::thaidLogin` live** (it sets a session and redirects). NOTE: this means we cannot delete `AuthController` entirely, nor the `auth` layout/login view it might fall back to — keep `thaidLogin` (and the `Auth` session path it needs). Reassess in a follow-up: ThaID should issue a JWT cookie and hand off to the SPA.
3. **Forgot-password** (`/forgot-password`) — currently a stub (`TODO: send email`). The SPA has no forgot-password screen. **Keep the route + view** until the SPA adds one (low value; could also be dropped with user sign-off — default: keep to avoid a dead link).
4. **Budget execution read views** (`/budgets`, `/budgets/list`, `/budgets/export`) — these are *reporting/list* surfaces from `BudgetExecutionController`/`BudgetController` that predate the disbursement wizard. The SPA wizard covers *recording* disbursements but there is **no SPA budget-execution overview/export page**. **Keep `/budgets`, `/budgets/list`, `/budgets/export` live** (read-only reporting) and flag for a follow-up SPA page. (If the user confirms these are fully superseded, they can move with the rest — default is KEEP because no SPA route renders them.)
5. **`/requests/dashboard`** — a PHP overview page. The SPA `/dashboard` is the general dashboard; there is no request-specific dashboard route. Low risk (the data is reachable via `/requests` + `/dashboard`). **Default: retire the web route** (covered by SPA `/dashboard` + `/requests`), and **retire the legacy E2E spec** that targets it (Task 6). Confirm with user; this is the only ambiguous retire.

> Net effect: the cutover retires the **admin CRUD, dashboard, budget-request, and disbursement/tracking** web surfaces. It **keeps** the document vault, ThaID login, forgot-password, and budget-execution reporting web routes as an explicit, documented "legacy remnant" set until follow-up SPA work lands.

---

## Archive strategy decision (CRITICAL)

**Problem:** `.gitignore:56` ignores `archives/` (and `archives/data/*.sql`, etc.). The PRD says "archive to `archives/`", but anything moved there is untracked → it **disappears from the repo** and from history going forward. That defeats "reversible archive".

**Decision: do NOT move retired code into `archives/`. Retire via git history instead.**

Two acceptable, reversible mechanisms — pick **Option A** (preferred):

- **Option A (preferred) — `git rm` from working tree, history retains it.** Remove the fully-superseded web controllers + views from the working tree with `git rm`. Git history (and the pre-cutover tag, see Rollback) keeps every byte; restoring is `git checkout <tag> -- <path>`. This keeps the working tree clean (one frontend, no dead PHP) and is fully reversible. The *route table* (`routes/web.php`) is the real "switch" — once the routes are gone, the controllers are unreachable anyway.
  - **Before** `git rm`, create an annotated tag `pre-spa-cutover` on the current commit so the exact legacy tree is trivially recoverable.
- **Option B (if the user wants the code visibly present) — move to a tracked `legacy/` dir.** Create a **new, non-ignored** top-level `legacy/` directory (NOT `archives/`), `git mv` `src/Controllers` → `legacy/Controllers` and `resources/views` → `legacy/views`, and ensure PSR-4 no longer autoloads them (remove/repoint the `App\Controllers\` mapping in `composer.json` so the moved classes are not loaded). This keeps files browseable but out of the app.

> **This plan implements Option A.** It is cleaner, the history is the archive, and `routes/web.php` + the pre-cutover tag are the reversible switch. If the user prefers Option B, the only deltas are: create `legacy/`, `git mv` instead of `git rm`, and adjust `composer.json` autoload + re-run `composer dump-autoload`.

**Do NOT** `git rm` the parity-gap files (FileController, files views, the auth layout/login view still needed by ThaID fallback, budget-execution controllers/views). They stay.

---

## Catch-all route design

### How the SPA is served in production (the mechanism this phase defines)

Today: nothing serves the Vue SPA in production. `frontend/dist/` exists but is git-ignored and is not referenced by any PHP route. `View::vite()` points at `public/assets/.vite/manifest.json` — that is the **legacy vanilla-JS** pipeline, unrelated to the Vue SPA.

**New mechanism:**
1. Vite builds the SPA to **`public/app/`** (a tracked dir), with `base` set to the subdirectory-aware public path so hashed asset URLs resolve correctly under `/hr_budget/public/`.
2. Built assets in `public/app/assets/*` are served **directly by the web server** (the `.htaccess` rule only rewrites *non-existent* files to `index.php`, so real asset files are served as static files — no PHP route needed for assets).
3. A **catch-all PHP route** returns `public/app/index.html` for every path that is **not** `/api/...` and **not** an existing static file. This makes deep links (hard refresh on `/disbursements/wizard`) return the shell, and Vue Router takes over client-side.

### Why a catch-all is needed (and the Router gotcha)

`Router::dispatch()` matches registered routes; unmatched → `notFound()` → renders `resources/views/errors/404.php`. After we remove the web routes, a hard refresh on `/requests/42` would 404. We need the shell served instead.

**Router limitation:** `addRoute()` converts `{name}` to `(?P<name>[^/]+)` — `[^/]+` does **not** cross `/`, so `/{path}` cannot match multi-segment deep links like `/requests/42/edit`. There is no built-in `*`/wildcard.

**Chosen approach — change `notFound()` to fall through to the SPA shell** (smallest, most robust, and inherently catches every unmatched non-API path at any depth):

- In `routes/web.php`, register API routes + the kept legacy/parity-gap web routes as today.
- Modify `Router::notFound()` (`src/Core/Router.php:167`) so that, instead of always rendering the 404 view, it:
  - If the (script-prefix-stripped) URI starts with `/api/` → keep returning a JSON 404 / the existing behavior (API 404s must stay JSON-shaped; today API misses fall here too — verify and preserve).
  - Else → serve the SPA shell: `http_response_code(200); readfile(BASE_PATH . '/public/app/index.html');` (with `Content-Type: text/html`). Guard with `file_exists`; if the shell is missing, fall back to the existing 404 view (so a missing build is a visible error, not a blank page).

> **Alternative (no Router edit):** add a literal `Router::get('/{path}', …)` won't work for nested paths (the `[^/]+` issue). A regex-wildcard route type would require extending `Router`. Modifying `notFound()` is less code and catches all depths — **use it.** Keep the change minimal and well-commented; the existing leftover `$log` debug block in `dispatch()` is unrelated and untouched.

### Subdirectory base

- The deployed public base is the script-dir prefix, e.g. `/hr_budget/public`. `View::baseUrl()` already computes this server-side.
- **Vite `base`**: set `build`-time base to the deploy path. Because Laragon serves at `/hr_budget/public/`, set `base: '/hr_budget/public/app/'` for the production build (the SPA lives under `public/app/`). Assets then load from `/hr_budget/public/app/assets/...`. If the user later fronts the app at a clean host root, this base is the single knob to change. Make it overridable via an env var (`VITE_BASE`) with the subdirectory value as the default, so dev (`base: '/'`) and prod differ cleanly.
- **Vue Router base**: `createWebHistory(import.meta.env.BASE_URL)` so client-side routes are prefixed identically. (`import.meta.env.BASE_URL` === Vite `base`.)
- **The catch-all/shell** must work when the request path includes `/hr_budget/public/...`; serving the static `index.html` (which already contains base-prefixed asset URLs from the build) handles this without server-side string munging.

---

## Step-by-Step Tasks

### Task 1 — Build the SPA for subdirectory deployment

**ACTION:** Point the production build at a tracked `public/app/` directory and set a subdirectory-aware base, then build.

**IMPLEMENT:**
- **CRITICAL — keep the DEFAULT build unchanged so CI stays green.** CI's `frontend` job runs `npm run build` (= `vite build`, production mode) and **uploads `frontend/dist`** (`.github/workflows/ci.yml:147`); the `e2e` job runs `npx vite preview --port 5174` and health-checks `http://127.0.0.1:5174/` expecting **base `/`** (`ci.yml:285–288`). Therefore the subdirectory base + `public/app` output MUST be a SEPARATE, env-gated **deploy** build — do **NOT** gate on `mode === 'production'` (CI's build IS production mode and must stay `base: '/'` → `frontend/dist`).
- `frontend/vite.config.ts`: gate on an explicit `VITE_BASE` env var (read via the existing `loadEnv`), NOT on `mode`:
  - `const deployBase = env.VITE_BASE || ''` ; `const base = deployBase || '/'`.
  - `build.outDir`: `deployBase ? '../public/app' : 'dist'` (default stays `dist`); `emptyOutDir: true`.
  - Plain `npm run build` (no `VITE_BASE`) → `base: '/'`, outDir `dist` (CI behavior preserved). Deploy build (`VITE_BASE` set) → subdirectory base, outDir `public/app`.
- `frontend/src/router/index.ts`: change `createWebHistory()` → `createWebHistory(import.meta.env.BASE_URL)` (safe in both: `BASE_URL` is `/` by default, the subdirectory in the deploy build).
- **Deploy build command (run locally, commit `public/app/`):** on Windows PowerShell `$env:VITE_BASE='/hr_budget/public/app/'; npm run build` (from `frontend/`). Optionally add a portable `"build:deploy"` script using `cross-env` (`cross-env VITE_BASE=/hr_budget/public/app/ vite build`) — only if adding the dep is acceptable; otherwise document the PowerShell form.
- `.gitignore`: line 8 ignores `/frontend/dist/` (KEEP — that's the CI artifact). The deploy output `public/app/` must be **tracked**; the `dist/`/`build/` rules (lines 39–40) do NOT match `public/app` — verify with `git status`, add `!public/app/` only if it shows ignored.

**MIRROR:** the SPA already uses relative `/api` paths (`auth.ts` uses `fetch('/api/v1/...')`) which resolve same-origin under any base — no change needed there. The proxy in dev (`vite.config.ts` server.proxy) is unaffected.

**GOTCHA:**
- **Do NOT gate the subdirectory base on `mode === 'production'`** — CI runs `vite build` (production mode) for the frontend job + E2E preview and REQUIRES `base: '/'` + `dist` output. Gate on the explicit `VITE_BASE` env, set only for deploy builds.
- The dev server (`npm run dev` at :5174) has no `VITE_BASE` → stays `base: '/'`; local dev + existing E2E unaffected.
- `import.meta.env.BASE_URL` is a trailing-slashed string (`/hr_budget/public/app/`); `createWebHistory` tolerates the trailing slash. Verify SPA routes still resolve in the deploy build (test a deep link).
- The deploy `index.html` references `/hr_budget/public/app/assets/*`; the default `dist` build references root `/assets/*` — correct for CI preview. Confirm no cross-leakage.

**VALIDATE:**
- Default build (CI parity): `cd frontend && npm run build` → `frontend/dist`, asset hrefs root-relative (`/assets/...`). Keeps CI frontend job + E2E preview green.
- Deploy build: from `frontend/`, `$env:VITE_BASE='/hr_budget/public/app/'; npm run build` → `public/app/index.html` + `public/app/assets/*` exist; asset hrefs prefixed `/hr_budget/public/app/assets/`.
- `git status` shows `public/app/**` as new tracked files (not ignored); `frontend/dist` stays ignored.

---

### Task 2 — Add the SPA shell catch-all (serve shell for unmatched non-API paths)

**ACTION:** Make any unmatched, non-API route return `public/app/index.html` (HTTP 200) so SPA deep links work; preserve JSON 404 for `/api/*` misses.

**IMPLEMENT:** in `src/Core/Router.php`, edit `notFound()`:
```php
private static function notFound(): void
{
    // API misses stay JSON-shaped (never serve HTML to an API client).
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '';
    if (str_contains($uri, '/api/')) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Not found']);
        return;
    }

    // Everything else falls through to the SPA shell (client-side routing).
    $shell = BASE_PATH . '/public/app/index.html';
    if (is_file($shell)) {
        http_response_code(200);
        header('Content-Type: text/html; charset=UTF-8');
        readfile($shell);
        return;
    }

    // Build missing — fail visibly rather than blank.
    http_response_code(404);
    View::render('errors/404');
}
```
- `BASE_PATH` is defined in `public/index.php` (`dirname(__DIR__)`), available at dispatch time.
- Add a brief comment block explaining this is the SPA cutover catch-all.

**MIRROR:** the existing `notFound()` already centralizes the miss path; we extend it rather than adding a wildcard route (which the `{...}` → `[^/]+` regex can't express for nested paths).

**GOTCHA:**
- Keep the API JSON-404 branch FIRST. After Task 4 removes web routes, an unknown `/api/v1/x` still falls here — it must return JSON, not the shell.
- The root path `/` is currently handled by `DashboardController::index` (`routes/web.php:155`) which we are removing. After removal, `/` becomes an unmatched route → falls through to the shell. Good — but double-check no *other* kept route accidentally matches `/`.
- `readfile` echoes raw HTML; ensure no prior output/whitespace from bootstrap (the app uses `ob_start` in tests but not in `public/index.php`). The shell HTML already includes base-prefixed assets, so no server-side rewriting.
- Static assets under `public/app/assets/*` are real files → `.htaccess` serves them directly (the `!-f` condition), never reaching PHP. Verify a built asset loads with a direct URL.

**VALIDATE:**
- After Task 4, hard-refresh `http://hr_budget.test/disbursements/wizard` (or the Laragon host) returns the SPA shell (200, HTML) and the wizard renders.
- `curl -s -o /dev/null -w '%{http_code} %{content_type}' http://hr_budget.test/api/v1/does-not-exist` → `404 application/json`.
- A direct asset URL (`/hr_budget/public/app/assets/index-*.js`) returns the JS file (200), not the shell.

---

### Task 3 — Tag the pre-cutover state (reversibility anchor)

**ACTION:** Create an annotated git tag capturing the full legacy tree before any retirement, so rollback is one command.

**IMPLEMENT:**
- `git tag -a pre-spa-cutover -m "Full legacy web/MVC surface before Phase 6 cutover"`
- (Push the tag when the branch is pushed.)

**GOTCHA:** Do this BEFORE Task 4's `git rm`. The tag is the archive — `archives/` is git-ignored and cannot serve this purpose.

**VALIDATE:** `git tag -l pre-spa-cutover` lists it; `git show pre-spa-cutover:src/Controllers/BudgetController.php` prints the legacy file.

---

### Task 4 — Retire superseded web routes + controllers + views

**ACTION:** Remove the web/MVC routes that the SPA fully replaces, and `git rm` their controllers/views. Keep the API block and all parity-gap remnants.

**IMPLEMENT — `routes/web.php`:**
- **KEEP** lines 32–143 (the entire `/api/v1/*` block) verbatim.
- **REMOVE** these web routes (the SPA replaces them):
  - Auth: `GET /login`, `POST /login`, `GET /logout`, `POST /logout` (lines 146–149). **KEEP `GET /thaid/login` (150)** — parity gap. **KEEP `/forgot-password` (151–152)** — parity gap (or drop with user sign-off).
  - Dashboard: `GET /` , `GET /dashboard`, `GET /api/dashboard/chart-data` (155–157).
  - Budget request: all `/requests*` web routes (188–202).
  - Admin: all `/admin/*` routes (205–233).
  - Budget targets web: `/budgets/targets*` (235–240).
  - Disbursement web: `/budgets/disbursements*` + `/api/budget-plans/*` (252–265).
  - Tracking web: `/budgets/tracking*` (170–180, 242), `/execution` redirect (167), `/budgets/create`/`{id}` CRUD (181–185).
- **KEEP (parity gaps):**
  - `GET /thaid/login` (150) + `/forgot-password` (151–152).
  - Document vault: `/files`, `/files/upload`, `/files/{id}/download`, `/files/{id}/delete`, `/folders`, `/folders/{id}/delete`, `/files/init` (244–250).
  - Budget execution reporting: `GET /budgets` (160), `GET /budgets/export` (161), `GET /budgets/list` (164). (KEEP — no SPA equivalent. Confirm with user; default keep.)
- Remove now-unused `use` imports at the top (e.g. `DashboardController`, `BudgetRequestController`, `BudgetExecutionController`, `DisbursementController`) **only if** no kept route references them. **`BudgetController` is still referenced** by kept tracking? No — tracking is retired; but `BudgetExecutionController` is referenced by kept `/budgets*`. Recheck each `use` against remaining routes before deleting it. `AuthController` is still referenced by `/thaid/login` + `/forgot-password` → **keep its import.**
- Add the comment marking the kept block as "Legacy remnants — pending SPA parity (document vault, ThaID, budget-execution reporting)".

**IMPLEMENT — `git rm` the fully-superseded controllers:**
- `src/Controllers/DashboardController.php`
- `src/Controllers/BudgetRequestController.php`
- `src/Controllers/BudgetController.php` (tracking — fully retired)
- `src/Controllers/DisbursementController.php`
- `src/Controllers/AdminBudgetCategoryController.php`
- `src/Controllers/AdminBudgetCategoryItemController.php`
- `src/Controllers/AdminOrganizationController.php`
- `src/Controllers/AdminTargetTypeController.php`
- `src/Controllers/BudgetTargetController.php`
- `src/Controllers/DivisionController.php`
- `src/Controllers/BudgetPlanController.php`
- **KEEP:** `src/Controllers/AuthController.php` (ThaID + forgot-password), `src/Controllers/BudgetExecutionController.php` (`/budgets` reporting), `src/Controllers/FileController.php` (document vault).

**IMPLEMENT — `git rm` the fully-superseded views:**
- `resources/views/dashboard/**`, `resources/views/requests/**`, `resources/views/admin/**`, `resources/views/budgets/dashboard.php`, `resources/views/budgets/list.php`, `resources/views/budgets/form.php`, `resources/views/budgets/tracking*/**`, `resources/views/budgets/partials/**`, `resources/views/budgets/_*` snippets, `resources/views/budgets/targets/**`, `resources/views/disbursements/**`.
- **KEEP:** `resources/views/errors/**` (used by `notFound()` fallback), `resources/views/layouts/auth.php` + `resources/views/auth/login.php` (ThaID/forgot-password fallback may render auth layout — verify; `thaidLogin` redirects and does not render login, but `forgot-password` renders `auth/forgot-password` — that view is **missing from the inventory!** see GOTCHA), `resources/views/files/**` (document vault), `resources/views/budgets/execution.php` (`/budgets` reporting), `resources/views/layouts/main.php` (still used by kept `/budgets`, `/files` pages), `resources/views/components/**` (shared by kept views).

**GOTCHA:**
- **`resources/views/auth/forgot-password.php` was NOT in the glob inventory** — only `auth/login.php` exists. `AuthController::showForgotPassword()` renders `auth/forgot-password` which may already be a broken/missing view. **Before keeping `/forgot-password`, verify the view exists**; if it doesn't, the route is already dead → safe to remove the route + `showForgotPassword`/`forgotPassword` methods, and this stops being a parity gap. Resolve at implementation: read `resources/views/auth/` — if no `forgot-password.php`, drop forgot-password entirely.
- `layouts/main.php` is shared by kept reporting/vault views — do NOT remove it. Removing a view that a kept route renders → `View::render` throws `RuntimeException`.
- After `git rm`, run `composer dump-autoload` is NOT needed (PSR-4 maps a directory; removed files just stop existing). But confirm no `require`/`use` in kept code references a removed class (grep).
- The `_method` override and other Router behaviors are untouched.

**VALIDATE:**
- `php -l routes/web.php` passes.
- Grep for references to removed controllers across `src/` and `routes/` returns only the (removed) lines — no dangling `use`/`new`.
- App boots: `/` serves the SPA shell; `/files` (kept) still renders; `/budgets` (kept) still renders.
- `vendor/bin/phpunit` (Unit + Integration) green — these don't touch web controllers, but confirm no autoload break.

---

### Task 5 — Remove web session-login wiring (keep `Auth` class + API login intact)

**ACTION:** Ensure the removed `/login` POST path no longer exists and nothing web-side calls `Auth::attempt()` for browser login; the SPA + `/api/v1/auth/*` is the only login.

**IMPLEMENT:**
- Routes already removed in Task 4 (`/login`, `/logout`).
- In `src/Controllers/AuthController.php` (KEPT for ThaID): remove `showLogin()`, `login()`, `logout()` methods only if unreferenced after route removal; **keep `thaidLogin()`** (and `forgotPassword`/`showForgotPassword` only if the view exists per Task 4 GOTCHA). Keep `logActivity()` (used by `thaidLogin`).
- **Do NOT modify `src/Core/Auth.php`.** `Auth::init()` still runs in `public/index.php` bootstrap (ThaID + API may use the session). The API auth (`src/Api/Controllers/AuthController`) is independent (JWT cookie) and untouched.

**GOTCHA:**
- Confirm the **API** login (`/api/v1/auth/login`) does not depend on the web `AuthController` — it is `App\Api\Controllers\AuthController` (aliased `ApiAuthController` in routes), a different class. Removing web login methods is safe.
- `Auth::init()` starting a session is also relied on by the PHPUnit bootstrap (`tests/bootstrap.php` calls `Auth::init()`). Do not remove it.

**VALIDATE:**
- `curl -i http://hr_budget.test/login` → falls through to SPA shell (200 HTML) — there is no web login page anymore.
- `/api/v1/auth/login` still authenticates (covered by `tests/Integration/AuthCookieTest.php` + `tests/e2e/api/auth-flow.spec.ts`).
- SPA login E2E (`tests/e2e/auth-login-logout.spec.ts`) still green.

---

### Task 6 — Update / quarantine legacy E2E specs

**ACTION:** Fix E2E specs that target removed PHP views so the suite is green against the SPA-only app.

**IMPLEMENT:**
- `tests/e2e/budget-requests-dashboard.spec.ts` — targets PHP view `/requests/dashboard` with PHP-DOM assertions (`.bg-dark-card`, `select[name="year"]`, page reloads via `waitForURL(/year=2567/)`). **This page no longer exists.** Options: (a) **delete** the spec (the SPA equivalents are covered by `dashboard.spec.ts` + `budget-request-workflow.spec.ts`), or (b) rewrite against the SPA `/dashboard` + `/requests`. **Default: delete** (it asserts on a retired surface; SPA coverage exists). Note in the PR.
- `tests/e2e/budget-request-workflow.spec.ts` — verify it targets SPA routes (it logs in via SPA `/login` per the Phase-4 pattern). If any step hits `/requests/dashboard` or a PHP route, update to the SPA route. (It matched the grep for `requests/dashboard` — inspect and fix; likely just a navigation assertion.)
- All other `tests/e2e/*.spec.ts` (admin-*, dashboard, notifications, disbursement-tracking-workflow, auth-login-logout) already target the SPA `baseURL` — no change.
- `tests/e2e/api/auth-flow.spec.ts` — comment references `http://hr_budget.test` for the API; that is the API, which is KEPT — no change.

**GOTCHA:** Run the full E2E suite against the **built SPA served by PHP** (not the dev server) at least once, because the cutover's whole point is the PHP-served shell + catch-all. The dev-server baseURL won't exercise the catch-all. See Task 7.

**VALIDATE:** `npm run test:e2e` green with no specs hitting removed PHP views.

---

### Task 7 — Playwright base config for the cutover SPA

**ACTION:** Make E2E runnable against the PHP-served SPA (catch-all path), keeping the dev-server flow available.

**IMPLEMENT — `playwright.config.ts`:**
- Keep `baseURL: process.env.BASE_URL || 'http://localhost:5174'` (dev-server default for fast local runs).
- Document a second mode: set `BASE_URL=http://hr_budget.test/hr_budget/public` (the Laragon-served built SPA) to exercise the catch-all + subdirectory base. Add a comment block making this explicit.
- Optionally add a `webServer` block to auto-serve the built SPA for CI:
  ```ts
  webServer: process.env.BASE_URL ? undefined : {
    command: 'npm --prefix frontend run preview',
    url: 'http://localhost:5174',
    reuseExistingServer: !process.env.CI,
  },
  ```
  (Only if CI doesn't already start the SPA; keep it conditional so local runs aren't forced.)

**GOTCHA:**
- `vite preview` serves with the production `base` (`/hr_budget/public/app/`), so previewed URLs differ from dev. If using `preview` for E2E, the baseURL must include that base, or set a separate `VITE_BASE=/` build for preview/CI. Simplest: keep CI E2E on the **dev server** (`npm --prefix frontend run dev`) with `base: '/'`, and run the **catch-all/subdirectory** verification as a manual smoke check against Laragon. Document both.
- Do not break the existing default (`localhost:5174`) that all current SPA specs rely on.

**VALIDATE:**
- `npm run test:e2e` green on the default (dev-server) baseURL.
- Manual smoke: `BASE_URL=http://hr_budget.test/hr_budget/public npx playwright test tests/e2e/auth-login-logout.spec.ts` passes against the PHP-served SPA (proves catch-all + base).

---

### Task 8 — Documentation: CLAUDE.md + README

**ACTION:** Rewrite the web-MVC architecture sections of `CLAUDE.md` to describe the SPA-only world; add a README note.

**IMPLEMENT — `CLAUDE.md`:**
- **Request lifecycle (§ line 35):** add that unmatched non-API paths fall through to the SPA shell (`public/app/index.html`) via `Router::notFound()`; `/api/v1/*` is the only server-rendered surface besides the shell.
- **Routing (§41):** note web/MVC routes are retired except documented legacy remnants (document vault, ThaID, budget-execution reporting, error views); the catch-all serves the SPA.
- **Views (§56):** mark `resources/views/**` as **legacy remnant only** (error pages + kept vault/reporting/auth-fallback views); the primary UI is the Vue SPA in `frontend/`. Keep the `View::section()` and `View::url()` notes scoped to the remaining legacy views.
- **Authentication (§67):** web session login removed; auth is JWT-cookie via `/api/v1/auth/*` + SPA. `Auth::init()` still runs (ThaID + session bootstrap); document the ThaID parity gap.
- **Domain modules (§71):** note these are now SPA modules over the API; the listed web controllers are retired (point to git tag `pre-spa-cutover`).
- **Key gotchas (§110):** add: SPA served from `public/app/` (tracked build output); Vite `base`/Vue Router base must match the subdirectory; catch-all lives in `Router::notFound()`; `archives/` is git-ignored so retirement uses git history + the `pre-spa-cutover` tag, not `archives/`.
- Add a **frontend build/serve** note to the Commands section: `cd frontend && npm run build` → outputs to `public/app/`.

**IMPLEMENT — `README.md` (top-level, create if missing):**
- One short section: "Frontend is a Vue 3 SPA in `frontend/`, built to `public/app/` and served by the PHP catch-all. Backend exposes only `/api/v1/*`. Run `cd frontend && npm run dev` for development; `npm run build` for production."

**GOTCHA:** CLAUDE.md is Thai-context but mostly English technical prose — keep the existing tone. Do not edit Thai-language view files with PS 5.1 (per project memory) — but here we are only editing Markdown, which is safe.

**VALIDATE:** CLAUDE.md no longer claims the web MVC pages are the active frontend; README present.

---

### Task 9 — Final full-suite verification

**ACTION:** Run the whole suite and confirm one-frontend state.

**IMPLEMENT:** see Validation Commands.

**VALIDATE:** `vendor/bin/phpunit --testsuite Unit` + `--testsuite Integration` green (excluding the known-environmental `tests/Unit/Models/*` failures noted in scope); `npm run test:e2e` green; `git status` shows retired files removed, `public/app/**` tracked, no `archives/` involvement.

---

## Testing Strategy

- **PHP Unit/Integration (unchanged surface):** `tests/Unit/{Api,Dtos,Services}` and `tests/Integration/*` test the API layer and are unaffected by web-side retirement. They must stay green. The `tests/Unit/Models/*` failures are pre-existing/environmental (DB) — not introduced here; note them, don't chase them in Phase 6.
- **E2E (the real cutover signal):**
  - **CI scope caveat:** the CI `e2e` job runs **only `tests/e2e/api`** (`ci.yml:304`), which exercises the KEPT `/api/v1/*` layer — so CI alone will NOT catch a broken SPA UI spec. SPA-spec correctness is therefore a **local pre-merge gate**: run `npm run test:e2e` locally (dev-server baseURL) before pushing, and confirm green.
  - Keep all SPA specs green against the dev-server baseURL.
  - Delete/rewrite `budget-requests-dashboard.spec.ts` (retired PHP view) — needed for local `npm run test:e2e` hygiene (CI doesn't run it, but a stale spec hitting a removed view is misleading).
  - Add **one cutover smoke spec** (or reuse an existing one run with the Laragon baseURL) asserting: (a) hard-refresh on a deep SPA route returns the shell and renders, (b) `/api/v1/<miss>` returns JSON 404, (c) a removed web route (`/requests/dashboard`) now serves the SPA shell rather than the old PHP page.
- **Manual smoke (subdirectory):** load `http://hr_budget.test/hr_budget/public/`, deep-link `/disbursements/wizard`, confirm assets load from `/hr_budget/public/app/assets/...`.
- **Coverage:** no new app code beyond the `notFound()` edit + config; the `notFound()` branch is covered by the E2E catch-all assertions. No 80% unit-coverage gate applies to a routing fallthrough — assert via E2E.

---

## Validation Commands

```bash
# 1. Build the SPA to the tracked public/app dir (subdirectory base)
cd frontend && npm run build && cd ..
ls public/app/index.html public/app/assets        # exist
grep -o '/hr_budget/public/app/assets/[^"]*' public/app/index.html | head   # base-prefixed

# 2. PHP lint the route file + confirm no dangling references to removed controllers
php -l routes/web.php
grep -rEn "DashboardController|BudgetRequestController|DisbursementController|Admin[A-Za-z]+Controller|BudgetTargetController|DivisionController|BudgetPlanController|\\\\App\\\\Controllers\\\\BudgetController" routes/ src/ \
  | grep -v "BudgetExecutionController"            # expect: no matches (besides kept ones)

# 3. API untouched + shell catch-all (run against Laragon host)
curl -s -o /dev/null -w '%{http_code} %{content_type}\n' http://hr_budget.test/api/v1/health           # 200 application/json
curl -s -o /dev/null -w '%{http_code} %{content_type}\n' http://hr_budget.test/api/v1/does-not-exist    # 404 application/json
curl -s -o /dev/null -w '%{http_code} %{content_type}\n' http://hr_budget.test/disbursements/wizard      # 200 text/html (SPA shell)
curl -s -o /dev/null -w '%{http_code}\n' http://hr_budget.test/hr_budget/public/app/assets/$(ls public/app/assets | grep -m1 '\.js$')  # 200 (static asset)

# 4. PHP tests (API layer — must stay green)
vendor/bin/phpunit --testsuite Unit
vendor/bin/phpunit --testsuite Integration

# 5. E2E (SPA) — default dev-server baseURL
npm run test:e2e
# Cutover smoke against PHP-served SPA (subdirectory):
BASE_URL=http://hr_budget.test/hr_budget/public npx playwright test tests/e2e/auth-login-logout.spec.ts

# 6. One-frontend / reversibility checks
git tag -l pre-spa-cutover                          # exists (rollback anchor)
git status                                          # public/app tracked; retired files deleted; no archives/ churn
```

---

## Risks

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| **`archives/` git-ignored → archived code lost** | (Was) High | High | Resolved: retire via `git rm` + `pre-spa-cutover` tag (Option A); never move into `archives/`. |
| **Deep-link hard refresh 404s** (catch-all wrong) | Medium | High | `notFound()` serves the shell for all non-API misses at any depth; E2E smoke asserts a deep refresh. |
| **Subdirectory base mismatch** (assets 404, blank app) | Medium | High | Vite `base` (prod only) + `createWebHistory(BASE_URL)` aligned to `/hr_budget/public/app/`; validate built `index.html` asset hrefs + a direct asset fetch. |
| **Dev server breaks** because Vite `base` applied in dev | Medium | Medium | Gate `base` behind `mode === 'production'`; keep `:5174` dev on `base: '/'`. |
| **Removing a view a kept route renders** (`layouts/main.php`, error views, vault/reporting views) | Medium | Medium | Explicit KEEP list; grep kept controllers for `View::render` targets before `git rm`. |
| **Parity gap shipped as removed** (document vault, ThaID, budget-execution reporting, forgot-password) | Medium | High | Explicit gap list; those routes/controllers/views KEPT and documented as legacy remnants. |
| **`forgot-password` view may already be missing** (broken route) | Low | Low | Implementation checks `resources/views/auth/` first; drop the route entirely if the view is absent. |
| **API 404 returns HTML instead of JSON** after catch-all | Medium | Medium | API branch is first in `notFound()`; curl assertion verifies JSON 404. |
| **Built SPA not committed** (`public/app` accidentally ignored) | Low | Medium | `.gitignore` check + `git status` validation; add `!public/app/` if any rule catches it. |
| **Legacy E2E spec fails the suite** (`budget-requests-dashboard`) | High (if untouched) | Medium | Delete/rewrite in Task 6; run full E2E before merge. |
| **`/` root no longer served** after removing DashboardController route | Medium | Medium | `/` falls through to shell via `notFound()`; SPA redirects `''`→`/dashboard`. Verified by smoke. |

---

## Rollback Note

The cutover is reversible without `archives/`:

1. **Routes/controllers/views:** `git checkout pre-spa-cutover -- routes/web.php src/Controllers resources/views` restores the entire legacy web surface; or `git revert` the cutover commit(s).
2. **Catch-all:** revert the `Router::notFound()` change to restore the plain 404 behavior.
3. **Build output:** `public/app/` is a build artifact — `rm -rf public/app && cd frontend && npm run build` regenerates it; reverting `vite.config.ts`/`router/index.ts` restores the previous (root-base) build.
4. **Tag:** `pre-spa-cutover` is the canonical pre-cutover snapshot — never delete it until the SPA-only app has run in production for at least one fiscal-cycle data-entry burst.

No source is ever placed in the git-ignored `archives/` directory; git history + the annotated tag are the archive.
