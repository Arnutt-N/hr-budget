# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

HR Budget Management System (ระบบจัดการงบประมาณทรัพยากรบุคคล) — a Thai-language budgeting app for a government HR division. Stack: **PHP 8.3 custom MVC** (not Laravel) + MySQL/MariaDB + Vite/Tailwind 4 + vanilla JS (Chart.js, SweetAlert2, dayjs). Deployed under Laragon at the subdirectory `/hr_budget/public/`.

## Commands

```bash
# Frontend (Vite dev server on :5173)
npm run dev
npm run build                          # outputs to public/assets/ with manifest

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

The root-level `index.php` simply `require`s `public/index.php` so the app runs whether the document root is the repo root or `public/`. `.htaccess` rewrites non-existent paths into `public/`.

### Routing (`src/Core/Router.php`)

- Static facade: `Router::get('/path/{id}', [Controller::class, 'method'])`
- Route params are regex-extracted and passed positionally to the handler
- `POST` with `_method=PUT|DELETE` field is treated as the actual method — **forms that "update" or "delete" use this convention** (see many `/{id}/update`, `/{id}/delete` routes in `routes/web.php`)
- `dispatch()` strips the script directory prefix from the URI, so the same routes work whether accessed via `/hr_budget/public/foo` or `/foo` (script prefix awareness is critical — do not hardcode leading `/hr_budget/public` in route definitions)
- Unmatched routes render `errors/404`

### Data layer (`src/Core/Database.php`, `Model.php`, `SimpleQueryBuilder.php`)

- Singleton PDO via `Database::getInstance()` / `getPdo()` — there is no connection pool or container
- `App\Core\Model` is a thin base with `all()`, `find($id)`, `where()`, `create()`; subclasses in `src/Models/` mostly declare `protected $table` + `$fillable`. Some models add static helpers (e.g. hierarchy walks)
- **No ORM / migrations framework.** Migrations are hand-written SQL files in `database/migrations/` numbered `001_*.sql`...`062_*.sql`, applied via `run_migrations.bat` / `run_migrations.sh` (each script shells out to the `mysql` CLI). New migration files must be numbered sequentially and the runner script updated if you want it batched
- Config from env: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. `config/database.php` is git-ignored — seed via `.env`

### Views (`src/Core/View.php` + `resources/views/`)

Two layouts only: `layouts/main.php` (authenticated app with sidebar) and `layouts/auth.php` (login/forgot password). Views are plain PHP templates with `<?= ... ?>` — no Blade, no Twig.

**Two non-obvious rules** documented in `.agents/workflows/view-template-guide.md`:

1. **Do NOT use `View::section()` / `View::endSection()` in new views.** It produces blank pages in this project. Write HTML/PHP directly in the view body; the layout captures output via `ob_start()`.
2. **Always wrap internal URLs with `View::url()`**: `href="<?= \App\Core\View::url('/budgets') ?>"`. Hardcoded `/budgets` breaks when deployed under a subdirectory (which is the default for this app).

`View::render('viewname', $data, 'main')` renders a view into a layout; `$data` is `extract()`ed so keys become local variables. `Auth::user()` and `config/app.php` are auto-injected as `$auth` and `$config`.

### Authentication (`src/Core/Auth.php`)

Session-based with configurable cookie params from `config/app.php > session`. `Auth::init()` starts the session and hydrates the user from `$_SESSION[session.key]`. `allowed_domains` in `config/auth.php` gates login by email domain. Thai ID login flow exists at `/thaid/login`.

### Domain modules

Controllers follow thin-controller/fat-model style, grouped roughly by domain:

- **Budget Request workflow** (`BudgetRequestController`): create → submit → approve/reject. Backed by `BudgetRequest`, `BudgetRequestItem`, `BudgetRequestApproval`, configurable approvers via `ApprovalSetting` + `Approver`
- **Budget Execution / Tracking / Disbursement** (`BudgetController`, `BudgetExecutionController`, `DisbursementController`): multi-step form that stashes intermediate state server-side via `/budgets/tracking/store-session` before finalizing a `BudgetRecord` / `Disbursement`
- **Admin CRUD** (`Admin*Controller`): organizations, fiscal years, approval settings, budget categories / category items (hierarchical via `parent_id` self-reference on category items), target types
- **Files** (`FileController` + `File`/`Folder` models): per-fiscal-year document vault; `/files/init` bootstraps folder structure for a new year
- **Dashboard** (`DashboardController`): chart data endpoint at `/api/dashboard/chart-data` feeds Chart.js widgets

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

- **Many routes under `/admin/{resource}/{id}/delete` are POST-only** (not DELETE); the `_method` override is not currently used for these. Match existing routes rather than inventing RESTful variants
- `src/Core/Router.php` has a leftover temp debug block that writes to `$log` when the URI contains `execution`. It doesn't actually log anywhere — safe to ignore, but don't rely on it either
- Email/session/DB env vars are loaded with `Dotenv::safeLoad()`, so a missing `.env` won't throw — config fallbacks in `config/*.php` apply instead
- Test bootstrap calls `Auth::init()` which starts a session; `ob_start()` is also called to swallow header output during test runs — any test that asserts on response headers must account for this
- `composer audit` and `vendor/bin/phpstan` are not wired up; there is no CI config checked in
