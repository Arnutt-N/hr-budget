# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

HR Budget Management System (ระบบจัดการงบประมาณทรัพยากรบุคคล) — a Thai-language budgeting app for a government HR division. Stack: **PHP 8.3 custom MVC** backend exposing a **JSON API (`/api/v1/*`)** + a **Vue 3 SPA** (`frontend/`, PrimeVue + TanStack Query, JWT-cookie auth) as the only user-facing frontend. MySQL/MariaDB. Deployed under Laragon at the subdirectory `/hr_budget/public/`.

> **Phase 6 cutover (2026-06-15):** the SPA replaced the server-rendered web/MVC pages. PHP now serves only `/api/v1/*` plus the compiled SPA shell (`public/app/index.html`, via the `Router::notFound()` catch-all). A small set of **legacy web remnants** with no SPA equivalent are still wired up: ThaID login (`/thaid/login`), budget-execution reporting (`/budgets`, `/budgets/export`), and the document vault (`/files`, `/folders`). The retired controllers/views are recoverable from the annotated git tag `pre-spa-cutover`.

## Commands

```bash
# Frontend = Vue 3 SPA in frontend/ (Vite dev server on :5174)
cd frontend && npm run dev
cd frontend && npm run build           # DEFAULT build → frontend/dist (base '/'), the CI artifact
# Deploy build → public/app/ (tracked, served by PHP) with the subdirectory base:
cd frontend && VITE_BASE=/hr_budget/public/app/ npm run build   # bash
#   PowerShell: $env:VITE_BASE='/hr_budget/public/app/'; npm run build
# The base + outDir switch is gated ONLY on the VITE_BASE env var (NOT on
# production mode), so plain `npm run build` stays base '/' → dist for CI.

# PHP tests (PHPUnit 10.5)
vendor/bin/phpunit --testsuite Unit
vendor/bin/phpunit --testsuite Integration
vendor/bin/phpunit --filter=testSomething tests/Unit/Foo.php   # single test
npm run test:coverage                  # HTML coverage → coverage/

# E2E (Playwright, Chromium only by default)
npm run test:e2e
npm run test:e2e:ui
BASE_URL=http://localhost/hr_budget/public npx playwright test tests/e2e/foo.spec.ts

# Full suite: unit → integration → e2e
npm test
```

Test environment reads `DB_NAME=hr_budget_test` (set in `phpunit.xml` and `tests/bootstrap.php`). Ensure that database exists separately from `hr_budget`.

## Architecture

### Request lifecycle

`public/index.php` → loads `vendor/autoload.php` → `App\Core\ErrorHandler::register()` → `Dotenv::safeLoad()` → `Auth::init()` → `routes/web.php` (register routes) → `Router::dispatch()`.

The root-level `index.php` simply `require`s `public/index.php` so the app runs whether the document root is the repo root or `public/`. `.htaccess` rewrites non-existent paths into `public/`. Real files under `public/app/assets/*` are served directly as static files (the `!-f` rewrite condition), never hitting PHP.

**SPA shell serving (Phase 6):** any unmatched, non-API path falls through to `Router::notFound()`, which returns the compiled SPA shell `public/app/index.html` (HTTP 200, `text/html`) so deep links and hard refreshes boot the Vue app and let Vue Router resolve client-side. `/api/*` misses stay a JSON 404. `/api/v1/*` plus the SPA shell are the only server-rendered surfaces (besides the kept legacy remnants below).

### Routing (`src/Core/Router.php`)

- Static facade: `Router::get('/path/{id}', [Controller::class, 'method'])`
- Route params are regex-extracted and passed positionally to the handler
- `POST` with `_method=PUT|DELETE` field is treated as the actual method (still used by the legacy remnant routes)
- `dispatch()` strips the script directory prefix from the URI, so the same routes work whether accessed via `/hr_budget/public/foo` or `/foo` (script prefix awareness is critical — do not hardcode leading `/hr_budget/public` in route definitions)
- `routes/web.php` = the `/api/v1/*` block (the live app surface) + a short **legacy web remnant** block (ThaID login, budget-execution reporting, document vault). Everything else the SPA replaced was retired in the Phase 6 cutover.
- Unmatched, non-API paths → the SPA shell via `notFound()`; unmatched `/api/*` paths → JSON 404.

### Data layer (`src/Core/Database.php`, `Model.php`, `SimpleQueryBuilder.php`)

- Singleton PDO via `Database::getInstance()` / `getPdo()` — there is no connection pool or container
- `App\Core\Model` is a thin base with `all()`, `find($id)`, `where()`, `create()`; subclasses in `src/Models/` mostly declare `protected $table` + `$fillable`. Some models add static helpers (e.g. hierarchy walks)
- **No ORM / migrations framework.** Migrations are hand-written SQL files in `database/migrations/` numbered sequentially (`001_*.sql`... — check the latest number before adding; a few unnumbered one-off SQL files also live there), applied via `run_migrations.bat` / `run_migrations.sh` (each script shells out to the `mysql` CLI). New migration files must be numbered sequentially and the runner script updated if you want it batched
- Config from env: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. `config/database.php` is git-ignored — seed via `.env`

### Views (`src/Core/View.php` + `resources/views/`)

**The primary UI is the Vue SPA in `frontend/`.** `resources/views/**` is now a **legacy remnant** rendered only by the kept web routes: `errors/*` (the `notFound()` build-missing fallback), `budgets/execution.php` (budget-execution reporting), `files/**` (document vault), `layouts/main.php` + `layouts/auth.php` + shared `components/**`. Do not build new server-rendered pages here — add SPA pages instead. Views are plain PHP templates with `<?= ... ?>` — no Blade, no Twig.

**Two non-obvious rules** (apply to the remaining legacy views) documented in `.agents/workflows/view-template-guide.md`:

1. **Do NOT use `View::section()` / `View::endSection()` in new views.** It produces blank pages in this project. Write HTML/PHP directly in the view body; the layout captures output via `ob_start()`.
2. **Always wrap internal URLs with `View::url()`**: `href="<?= \App\Core\View::url('/budgets') ?>"`. Hardcoded `/budgets` breaks when deployed under a subdirectory (which is the default for this app).

`View::render('viewname', $data, 'main')` renders a view into a layout; `$data` is `extract()`ed so keys become local variables. `Auth::user()` and `config/app.php` are auto-injected as `$auth` and `$config`.

### Authentication

**Primary auth is JWT-cookie via `/api/v1/auth/*` + the SPA login page.** The legacy web session-login routes/methods (`GET/POST /login`, `/logout`, forgot-password) were removed in the Phase 6 cutover. `src/Core/Auth.php` is unchanged and still in use: `Auth::init()` runs in bootstrap (and in the PHPUnit bootstrap) to start the session and hydrate `$_SESSION[session.key]`. The one remaining session-login path is **ThaID** (`/thaid/login` → `AuthController::thaidLogin`), a documented parity gap (the SPA has no ThaID flow yet) — it mints a session via `Auth::mockThaIDLogin()` and redirects to the SPA shell at `/`. The API `AuthController` (`App\Api\Controllers\AuthController`) is a separate JWT class, independent of the web `Auth` session login.

### Domain modules

These are now **SPA modules over the `/api/v1/*` API** (pages in `frontend/src/pages/`, queries in `frontend/src/queries/`): budget-request workflow (create → submit → approve/reject), disbursement/tracking wizard, dashboard + notifications, and all admin master-data CRUD (organizations, fiscal years, categories/items, divisions, plans, target types, targets, users). The legacy web controllers that served these (`DashboardController`, `BudgetRequestController`, `BudgetController`, `DisbursementController`, `Admin*Controller`, `BudgetTargetController`, `DivisionController`, `BudgetPlanController`) were **retired** in the Phase 6 cutover — recover them from the `pre-spa-cutover` git tag if needed.

Three thin web controllers remain for the documented legacy remnants:

- **Budget Execution reporting** (`BudgetExecutionController` → `/budgets`, `/budgets/export`, renders `budgets/execution.php`; export redirects to `public/export_execution.php`) — read-only overview/export, no SPA equivalent yet.
- **Document vault** (`FileController` + `File`/`Folder` models → `/files`, `/folders`, `/files/init`) — per-fiscal-year file vault; `/files/init` bootstraps folder structure for a new year. The SPA only has request-attachment upload (`/api/v1/requests/{id}/files`).
- **ThaID login** (`AuthController::thaidLogin` → `/thaid/login`).

### REST API layer (`/api/v1/*`)

Separate from the web MVC side — API routes are registered at the top of `routes/web.php`:

- **Layering**: `src/Api/Controllers` → `src/Services` → `src/Repositories`, with request/response shapes in `src/Dtos` (PSR-4: one class per file). This differs from the web side's thin-controller/fat-model style — follow the layered style for API work
- **Auth**: JWT Bearer tokens (`App\Core\Jwt`); `src/Api/Middleware/AuthMiddleware.php` rejects with 401 JSON. Login at `POST /api/v1/auth/login`
- **Responses**: always use the `src/Api/Responses/ApiResponse.php` envelope (`success`/`data`/`error` + pagination meta) — never echo raw JSON from API controllers
- **CORS**: handled by `src/Api/Middleware/CorsMiddleware.php`
- Resources: fiscal-years, organizations, categories (+items), users, budget-requests (submit/approve/reject + notifications), files (upload), notifications

### Fiscal year conventions

- Buddhist calendar — `config/app.php > fiscal_year.current = 2569` (= 2026 CE)
- Year boundary: October 1 → September 30 (`start_month=10`, `end_month=9`)
- Most budget queries scope by `fiscal_year_id`; avoid assuming Gregorian year in date math

## Project layout conventions

Defined in `.agents/workflows/folder-structure.md`:

- `research/` — analysis documents written before planning
- `PRPs/` — implementation plans (Pre-work Request Proposals) created before coding
- `project-log-md/` — session logs, task checklists, walkthroughs
- `python/` — one-off analysis / migration scripts written in Python (separate venv)
- `scripts/` — shell helpers, migration runners
- `archives/` — retired code/data; git-ignored subpaths include `archives/`, `*.sql`, `*.bak`

One-shot PHP debug scripts drop into the repo root or `public/` (e.g. `inspect_schema.php`, `public/debug_ids.php`). They are working scratchpads — do not treat them as part of the supported surface.

## Key gotchas

- **SPA is served from `public/app/`** — a **tracked** build artifact (committed for production serving), produced by the deploy build (`VITE_BASE=/hr_budget/public/app/ npm run build`). The Vite `base` and Vue Router base (`createWebHistory(import.meta.env.BASE_URL)`) must match the subdirectory; the default build (no `VITE_BASE`) stays base `/` → `frontend/dist` (the CI artifact, git-ignored). Do not gate the deploy base on `mode === 'production'` — CI's build IS production mode and must stay base `/`.
- **The SPA catch-all lives in `Router::notFound()`** (not a wildcard route — the `{name}` → `[^/]+` regex can't match nested paths). Keep the `/api/` JSON-404 branch FIRST so API misses never get HTML.
- **Retirement is via git history + the `pre-spa-cutover` tag, NOT `archives/`** (`archives/` is git-ignored, so moving code there would delete it from version control). Restore a retired file with `git checkout pre-spa-cutover -- <path>`.
- Email/session/DB env vars are loaded with `Dotenv::safeLoad()`, so a missing `.env` won't throw — config fallbacks in `config/*.php` apply instead
- Test bootstrap calls `Auth::init()` which starts a session; `ob_start()` is also called to swallow header output during test runs — any test that asserts on response headers must account for this
- `composer audit` and `vendor/bin/phpstan` are not wired up; there is no CI config checked in
