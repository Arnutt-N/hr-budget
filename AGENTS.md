# AGENTS.md

**Single source of truth for every coding agent working in this repo** — Claude Code, Kilo,
Codex, Cursor, or anything else. `CLAUDE.md` is a thin pointer to this file; do not
duplicate guidance there.

HR Budget Management System (ระบบจัดการงบประมาณทรัพยากรบุคคล) — a Thai-language budgeting
app for a government HR division.

## Project Overview

Stack: **PHP 8.3 custom MVC** backend exposing a **JSON API (`/api/v1/*`)** + a **Vue 3 SPA**
(`frontend/`, PrimeVue + TanStack Query, JWT-cookie auth) as the only user-facing frontend.
MySQL/MariaDB. Deployed under Laragon at the subdirectory `/hr_budget/public/`.

> **Phase 6 cutover (2026-06-15):** the SPA replaced the server-rendered web/MVC pages. PHP
> now serves only `/api/v1/*` plus the compiled SPA shell (`public/app/index.html`, via the
> `Router::notFound()` catch-all). A single **legacy web remnant** with no SPA equivalent is
> still wired up: ThaID login (`/thaid/login`, a 302 alias to the SPA-facing API flow).
> Budget-execution reporting (`/budgets`, `/budgets/export`) and the document vault
> (`/files`, `/folders`) were both retired post-cutover once the SPA reached parity (recover
> from the `pre-budgets-retire` / `pre-files-retire` tags). Other retired controllers/views
> are recoverable from the annotated git tag `pre-spa-cutover`.

## Commands

```bash
# Frontend = Vue 3 SPA in frontend/ (Vite dev server on :5174)
cd frontend && npm run dev
cd frontend && npm run build           # DEFAULT build → frontend/dist (base '/'), the CI artifact
# Deploy build → public/app/ (tracked, served by PHP) with the subdirectory base:
cd frontend && VITE_BASE=/hr_budget/public/app/ npm run build   # bash
#   PowerShell: $env:VITE_BASE='/hr_budget/public/app/'; npm run build

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

# Verification gate (see "Verification & CI" below — this is what actually runs today)
composer verify                        # repo root: PHPStan + PHPUnit Unit
cd frontend && npm run verify          # typecheck + build
```

### Command details that trip agents up

- **Two `package.json` files, two toolchains:**
  - Root `package.json` — Playwright runner only (`npm run test:e2e`, `test:e2e:ui`,
    `test:e2e:headed`) + PHP test aliases (`test:unit`, `test:integration`, `test`,
    `test:coverage` that shell out to `vendor/bin/phpunit`).
  - `frontend/package.json` — the Vue 3 SPA (`npm run dev|build|typecheck|test:unit|verify`).
    Run SPA commands from `frontend/`, not the root.
- **Frontend build has two modes gated ONLY by the `VITE_BASE` env var (NOT by `mode`):**
  - Default (`npm run build`) → base `/`, outDir `dist` (CI artifact, git-ignored).
  - Deploy (`VITE_BASE=/hr_budget/public/app/ npm run build`) → base `/hr_budget/public/app/`,
    outDir `../public/app` (tracked, served by PHP).
  - Never gate the deploy base on `mode === 'production'` — a production build must stay base `/`.
  - Build the SPA from PowerShell rather than Git Bash to avoid MSYS path mangling of `VITE_BASE`.
- **Vite dev proxies `/api` to `http://hr_budget.test`** (Laragon host) by default; override
  with `VITE_API_URL`. Same-origin in dev exercises CORS end-to-end.
- Test environment reads `DB_NAME=hr_budget_test` (set in `phpunit.xml` and
  `tests/bootstrap.php`). Ensure that database exists separately from `hr_budget`.

## Architecture

### Request lifecycle

`public/index.php` → loads `vendor/autoload.php` → `App\Core\ErrorHandler::register()` →
`Dotenv::safeLoad()` → `Auth::init()` → `routes/web.php` (register routes) → `Router::dispatch()`.

The root-level `index.php` simply `require`s `public/index.php` so the app runs whether the
document root is the repo root or `public/`. `.htaccess` rewrites non-existent paths into
`public/`. Real files under `public/app/assets/*` are served directly as static files (the
`!-f` rewrite condition), never hitting PHP.

**SPA shell serving (Phase 6):** any unmatched, non-API path falls through to
`Router::notFound()`, which returns the compiled SPA shell `public/app/index.html` (HTTP 200,
`text/html`) so deep links and hard refreshes boot the Vue app and let Vue Router resolve
client-side. `/api/*` misses stay a JSON 404. `/api/v1/*` plus the SPA shell are the only
server-rendered surfaces (besides the kept legacy remnant below).

### Routing (`src/Core/Router.php`)

- Static facade: `Router::get('/path/{id}', [Controller::class, 'method'])`
- Route params are regex-extracted and passed **positionally** to the handler
- `POST` with `_method=PUT|DELETE` field is treated as the actual method (still used by the
  legacy remnant routes)
- `dispatch()` strips the script directory prefix from the URI, so the same routes work
  whether accessed via `/hr_budget/public/foo` or `/foo` (script prefix awareness is critical
  — do not hardcode leading `/hr_budget/public` in route definitions)
- `routes/web.php` = the `/api/v1/*` block (the live app surface) + a tiny **legacy web
  remnant** block (ThaID login alias + `/logout` only). Everything else the SPA replaced was
  retired in the Phase 6 cutover and afterwards.
- Unmatched, non-API paths → the SPA shell via `notFound()`; unmatched `/api/*` paths → JSON 404.

### Data layer (`src/Core/Database.php`, `Model.php`, `SimpleQueryBuilder.php`)

- Singleton PDO via `Database::getInstance()` / `getPdo()` — there is no connection pool or container
- `App\Core\Model` is a thin base with `all()`, `find($id)`, `where()`, `create()`; subclasses
  in `src/Models/` mostly declare `protected $table` + `$fillable`. Some models add static
  helpers (e.g. hierarchy walks)
- **No ORM / migrations framework.** Migrations are hand-written SQL files in
  `database/migrations/` numbered sequentially (`001_*.sql`… — check the latest number before
  adding; a few unnumbered one-off SQL files also live there), applied via
  `run_migrations.bat` / `run_migrations.sh` (each script shells out to the `mysql` CLI)
- Config from env: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
  `config/database.php` is git-ignored — seed via `.env`

### Views (`src/Core/View.php` + `resources/views/`)

**The primary UI is the Vue SPA in `frontend/`.** The server-rendered view layer is
essentially gone — the only views left are `resources/views/errors/*`: standalone HTML error
pages (403/404/500/502/503/504/505) rendered by `App\Core\Auth` (403 on authz failure),
`Router::notFound()` (404 when the SPA build is missing), and `App\Core\ErrorHandler`
(500/{code}). The legacy layouts (`layouts/main.php`, `layouts/auth.php`), shared
`components/**`, and `auth/login.php` were removed once their last consumers
(the budget-execution + document-vault pages) were retired — recover from the
`pre-views-sweep` tag. **Do not add server-rendered pages — add SPA pages instead.**

`View::render('errors/404', $data)` renders a view; `$data` is `extract()`ed so keys become
local variables, and `Auth::user()` + `config/app.php` are auto-injected as `$auth` and
`$config`. A layout arg is still accepted but is now always a no-op: `View::render` degrades
to standalone output when the named `layouts/*.php` is absent (so `ErrorHandler`'s `'error'`
layout arg just renders the error view bare). One rule still applies to error views: wrap
internal URLs with `View::url()` (e.g. `href="<?= \App\Core\View::url('/') ?>"`) so links
stay correct under the `/hr_budget/public/` subdirectory deployment.

### Authentication

**Primary auth is JWT-cookie via `/api/v1/auth/*` + the SPA login page.** The legacy web
session-login routes/methods (`GET/POST /login`, `/logout`, forgot-password) were removed in
the Phase 6 cutover. `src/Core/Auth.php` is unchanged and still in use: `Auth::init()` runs in
bootstrap (and in the PHPUnit bootstrap) to start the session and hydrate
`$_SESSION[session.key]`. The one remaining session-login path is **ThaID**
(`/thaid/login` → `AuthController::thaidLogin`), a documented parity gap (the SPA has no ThaID
flow yet) — it mints a session via `Auth::mockThaIDLogin()` and redirects to the SPA shell at
`/`. The API `AuthController` (`App\Api\Controllers\AuthController`) is a separate JWT class,
independent of the web `Auth` session login.

### Domain modules

These are now **SPA modules over the `/api/v1/*` API** (pages in `frontend/src/pages/`,
queries in `frontend/src/queries/`): budget-request workflow (create → submit →
approve/reject), disbursement/tracking wizard, dashboard + notifications, and all admin
master-data CRUD (organizations, fiscal years, categories/items, divisions, plans, target
types, targets, users). The legacy web controllers that served these (`DashboardController`,
`BudgetRequestController`, `BudgetController`, `DisbursementController`, `Admin*Controller`,
`BudgetTargetController`, `DivisionController`, `BudgetPlanController`) were **retired** in
the Phase 6 cutover — recover them from the `pre-spa-cutover` git tag if needed.

One legacy web remnant remains after the Phase 6 cutover. Budget-execution reporting and the
document vault were both retired once the SPA reached parity (PR #17 / `pre-budgets-retire`;
SPA vault PR #16 + fiscal-year init, `pre-files-retire`). The vault now lives entirely in the
SPA over `/api/v1/vault/*` (folders/files CRUD + `POST /api/v1/vault/years` to scaffold a
year's system folders):

- **ThaID login** (`/thaid/login` → 302 alias to `/api/v1/auth/thaid/login`, handled by
  `App\Api\Controllers\ThaIdController`).

> Not a remnant: **request-attachment** upload (`/api/v1/requests/{id}/files`) is a live SPA
> feature served by `App\Api\Controllers\FileController` + `App\Services\FileService`
> (distinct from the retired web `App\Controllers\FileController`).

### REST API layer (`/api/v1/*`)

Separate from the web MVC side — API routes are registered at the top of `routes/web.php`:

- **Layering**: `src/Api/Controllers` → `src/Services` → `src/Repositories`, with
  request/response shapes in `src/Dtos` (PSR-4: one class per file). This differs from the web
  side's thin-controller/fat-model style with static `Models/*` methods — **follow the layered
  style for API work and do not mix the two styles in new API code**
- **Auth**: JWT Bearer tokens (`App\Core\Jwt`); `src/Api/Middleware/AuthMiddleware.php` rejects
  with 401 JSON. Login at `POST /api/v1/auth/login`
- **Responses**: always use the `src/Api/Responses/ApiResponse.php` envelope
  (`success`/`data`/`error`/`details` + pagination meta) — **never `echo json_encode(...)`
  from an API controller**
- **CORS**: handled by `src/Api/Middleware/CorsMiddleware.php`
- Resources: fiscal-years, organizations, categories (+items), users, budget-requests
  (submit/approve/reject + notifications), files (upload), notifications

### Fiscal year conventions

- Buddhist calendar — `config/app.php > fiscal_year.current = 2569` (= 2026 CE)
- Year boundary: October 1 → September 30 (`start_month=10`, `end_month=9`)
- Most budget queries scope by `fiscal_year_id`; avoid assuming Gregorian year in date math

## Verification & CI

**The GitHub Actions CI workflow is currently `disabled_manually`** to conserve free Actions
minutes. Verification runs locally instead. Do not assume a PR was gated by CI.

### Local verification gate (what actually runs today)

- **`composer verify`** (repo root) — PHPStan + PHPUnit Unit suite (`@analyse` then `@test:unit`).
- **`npm run verify`** (`frontend/`) — `typecheck` (`vue-tsc --noEmit`) + `build` (`vue-tsc -b && vite build`).
- **git `pre-push` hook** (local-only, lives in `.git/hooks/pre-push`, **NOT committed**) —
  auto-runs before every `git push`:
  - Gate 1 (blocks): PHPStan static analysis
  - Gate 2 (blocks): Frontend typecheck + build
  - Advisory (prints, does NOT block): PHPUnit Unit suite — currently clean;
    `BudgetRequestItemTest::getTree_returns_hierarchical_structure` is skipped because it
    asserts a parent/child hierarchy the schema and `getTree()` never implemented.
  - Bypass with `git push --no-verify` when intentional.
  - Resolves Laragon PHP 8.3 automatically; no PATH setup needed.

Run `composer verify` and `npm run verify` manually for full local parity. The hook covers the
push moment; the scripts cover ad-hoc runs.

### Static analysis status

- **`vendor/bin/phpstan` IS wired** — exposed via `composer analyse`, included in
  `.github/workflows/ci.yml` as a DB-independent step (PR #39). Pre-existing findings are
  captured in `phpstan-baseline.neon`.
- **`composer audit` is still NOT wired** (not in CI, not in composer scripts).

### CI facts (accurate if/when the workflow is re-enabled)

- **`config/database.php` is git-ignored** (contains local dev creds). CI materializes it
  inline from env. Locally, seed it from `.env`
  (DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD).
- **CI loads schema from `database/hr_budget_only.sql`**, not the numbered migrations.
  Migrations are for local/sequential DB evolution; the consolidated SQL is the CI snapshot.
- **`database/hr_budget_only.sql` embeds `CREATE DATABASE hr_budget` + `USE hr_budget`**
  (dumped with `--databases`) and also contains INSERT data despite the "only" in its name.
  Piping it at a database name does **NOT** work — the embedded `USE` wins and the import
  lands in `hr_budget`, silently leaving the intended target empty. CI strips those lines with
  `sed` before importing; locally use `scripts/setup_test_db.sh`.
- **CI runs `--testsuite UnitCI` = the `Unit` suite minus `tests/Unit/Models/`** (declared in
  `phpunit.xml`, not as a directory list in `ci.yml`, so a new `tests/Unit/<X>/` is picked up
  automatically). Model tests need seeded reference rows the snapshot doesn't guarantee, so
  they are covered locally (`composer verify`) but not in CI. **Treat CI green as partial coverage.**
- **The `Integration` suite is never invoked by CI** — it runs only when a developer runs
  `vendor/bin/phpunit --testsuite Integration` locally against `hr_budget_test`.
- **CI's materialized DB config sets `PDO::ATTR_EMULATE_PREPARES => false`.** Local
  `config/database.php` is user-supplied — if you hit edge cases with `LIMIT ? OFFSET ?`,
  check this flag.
- **E2E is opt-in on CI.** The `e2e` job only runs on `workflow_dispatch` or when the PR
  carries the `run-e2e` label, and even then it executes `tests/e2e/api` only.
- **E2E seeds `e2e@hr.local` / `pass1234`** (role `viewer`). Tests read `E2E_USER_EMAIL` /
  `E2E_USER_PASSWORD` env.
- **Backend in CI:** `php -S 127.0.0.1:18080 -t public/ public/index.php`. Frontend:
  `npx vite preview --port 5174` after `npm run build`. `BASE_URL` + `API_URL` env switch the
  Playwright target.
- **CI skips docs/data/migration-only PRs** via `paths-ignore`. Don't expect CI runs on
  `*.md` / `PRPs/**` / `database/migrations/**`-only changes.

## Test environment (verified in `tests/bootstrap.php`)

- **Set up the local test database with `bash scripts/setup_test_db.sh`** (copies `hr_budget`
  schema + data into `hr_budget_test`). Do NOT pipe `database/hr_budget_only.sql` at it — see
  the CI section above for why that overwrites the dev database instead.
- **The test suite refuses to run against a database whose name doesn't end in `_test`**
  (guard in `tests/bootstrap.php`). Previously a local `.env` with `DB_DATABASE=hr_budget`
  silently pointed the whole suite at real data; `phpunit.xml`'s env values now override
  `.env` unconditionally.
- `phpunit.xml` uses short env names (`DB_NAME`, `DB_USER`, `DB_PASS`) but
  `config/database.php` reads `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. The bootstrap
  bridges them — keep both names in sync if you add new DB-related env.
- Bootstrap calls `Auth::init()` (starts a session) + `ob_start()`. **Any test asserting on
  response headers must account for the output buffer.**
- Test DB is `hr_budget_test` (separate from `hr_budget`). Create it before running
  integration tests.
- `JWT_SECRET` in `phpunit.xml` is a 64-char test value; a real `.env` must have ≥32 bytes or
  `Jwt::assertSecretSafe()` throws at first JWT use.

## Project layout conventions

Defined in `.agents/workflows/folder-structure.md`:

- `research/` — analysis documents written before planning
- `PRPs/` — implementation plans (Pre-work Request Proposals) created before coding
- `project-log-md/` — session logs, task checklists, walkthroughs (git-ignored)
- `python/` — one-off analysis / migration scripts written in Python (separate venv)
- `scripts/` — shell helpers, migration runners
- `docs/agents/` — per-repo agent configuration (issue tracker, triage labels, domain docs)
- `archives/` — retired code/data; git-ignored subpaths include `archives/`, `*.sql`, `*.bak`

One-shot PHP debug scripts drop into the repo root or `public/` (e.g. `inspect_schema.php`,
`public/debug_ids.php`). They are working scratchpads — do not treat them as part of the
supported surface.

## Conventions not obvious from filenames

- **PR title format** (`.github/PULL_REQUEST_TEMPLATE.md`): `<type>: <short description>` —
  e.g. `feat(api): add budget request approval endpoint`. Commit messages in `git log` follow
  the same `feat(phase-N): ...` / `fix(deploy): ...` pattern.
- **Language rule:** Thai for all UI strings + user-facing error messages; English for logs,
  identifiers, and commit types. Existing code mixes Thai strings into DTO `validate()` error
  messages — match that.
- **Retirement is via git tags, not `archives/`.** `archives/` is git-ignored — moving code
  there deletes it from version control. Restore retired files with
  `git checkout <tag> -- <path>`. Tags: `pre-spa-cutover`, `pre-budgets-retire`,
  `pre-files-retire`, `pre-views-sweep`.

## Migration gotchas

- **No migration framework.** Files in `database/migrations/` are hand-written SQL applied via
  `run_migrations.bat` / `run_migrations.sh` (shell out to `mysql` CLI).
- **Number collisions exist:** `022_*` (two files), `023_*` (two files), `024_*` (two files).
  Several numbers are skipped (`005`, `006`, `020`, `030`, `039`, `042–049`, `055–059`).
  **Check the directory before adding a new one — don't just increment the highest existing number.**
- **Some migrations come in pairs** (`NNN_*.sql` + `NNN_rollback_*.sql`) — keep that pattern if
  you add a reversible change.

## Key gotchas

- **SPA is served from `public/app/`** — a **tracked** build artifact (committed for production
  serving), produced by the deploy build. The Vite `base` and Vue Router base
  (`createWebHistory(import.meta.env.BASE_URL)`) must match the subdirectory; the default build
  (no `VITE_BASE`) stays base `/` → `frontend/dist` (git-ignored).
- **The SPA catch-all lives in `Router::notFound()`** (not a wildcard route — the `{name}` →
  `[^/]+` regex can't match nested paths). Keep the `/api/` JSON-404 branch **FIRST** so API
  misses never get HTML.
- Email/session/DB env vars are loaded with `Dotenv::safeLoad()`, so a missing `.env` won't
  throw — config fallbacks in `config/*.php` apply instead.

## Stale docs to trust less than the code

- **`README.md` mentions `/budgets`, `/files`, `/folders` as live legacy remnants — they are
  retired.** The only live server-rendered remnant is ThaID login (`/thaid/login` → 302 alias).

## Kilo config

`.kilo/kilo.jsonc` is minimal: `$schema` + `snapshot: false` (git snapshots off). No
project-level agent/command/skill overrides — defaults apply.

## Agent skills

Per-repo configuration that the engineering skills read. Full detail lives in `docs/agents/`;
these are the one-line summaries.

### Issue tracker

GitHub Issues on `Arnutt-N/hr-budget`, via the `gh` CLI. External PRs are **not** a triage
surface — `/triage` reads issues only. See `docs/agents/issue-tracker.md`.

### Triage labels

Canonical five-label vocabulary, unmapped (each role's label string equals its name). All five
exist on GitHub — no setup needed. See `docs/agents/triage-labels.md`.

### Domain docs

Single-context: one `CONTEXT.md` + `docs/adr/` at the repo root. Neither exists yet — they are
created lazily by `/domain-modeling`. See `docs/agents/domain.md`.
