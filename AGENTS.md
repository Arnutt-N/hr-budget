# AGENTS.md

Compact agent guide. **`CLAUDE.md` is the primary instruction file** — read it first for architecture, request lifecycle, retirement tags, and fiscal-year conventions. This file captures only what CLAUDE.md omits, gets stale, or where defaults mislead.

## Commands that differ from defaults

- **Two `package.json` files, two toolchains:**
  - Root `package.json` — Playwright runner only (`npm run test:e2e`, `test:e2e:ui`, `test:e2e:headed`) + PHP test aliases (`test:unit`, `test:integration`, `test`, `test:coverage` that shell out to `vendor/bin/phpunit`).
  - `frontend/package.json` — the Vue 3 SPA (`npm run dev|build|typecheck|test:unit`). Run SPA commands from `frontend/`, not the root.
- **CI runs different PHPUnit paths than CLAUDE.md's `--testsuite` examples.** `.github/workflows/ci.yml` invokes `vendor/bin/phpunit tests/Unit/Api/ tests/Unit/Dtos/ tests/Unit/Services/ tests/Unit/Core/` (explicit paths, no `--testsuite`). Locally, `vendor/bin/phpunit --testsuite Unit` is still correct.
- **E2E is opt-in on CI.** The `e2e` job only runs on `workflow_dispatch` or when the PR carries the `run-e2e` label. PHP + frontend jobs gate every PR.
- **Frontend build has two modes gated ONLY by `VITE_BASE` env (not `mode`):**
  - Default (`npm run build`) → base `/`, outDir `dist` (CI artifact, git-ignored).
  - Deploy (`VITE_BASE=/hr_budget/public/app/ npm run build`) → base `/hr_budget/public/app/`, outDir `../public/app` (tracked, served by PHP). PowerShell: `$env:VITE_BASE='/hr_budget/public/app/'; npm run build`.
  - Never gate the deploy base on `mode === 'production'` — CI's build IS production and must stay base `/`.
- **Vite dev proxies `/api` to `http://hr_budget.test`** (Laragon host) by default; override with `VITE_API_URL`. Same-origin in dev exercises CORS end-to-end.

## CI prerequisites CLAUDE.md doesn't mention

CI exists (`.github/workflows/ci.yml`) — **CLAUDE.md's "no CI config checked in" is stale.** Key facts:

- **`config/database.php` is git-ignored** (contains local dev creds). CI materializes it inline from env. Locally, seed it from `.env` (DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD).
- **CI loads schema from `database/hr_budget_only.sql`**, not the numbered migrations. Migrations are for local/sequential DB evolution; the consolidated SQL is the CI snapshot.
- **CI's materialized DB config sets `PDO::ATTR_EMULATE_PREPARES => false`.** Local `config/database.php` is user-supplied — if you hit edge cases with `LIMIT ? OFFSET ?`, check this flag.
- **E2E seeds `e2e@hr.local` / `pass1234`** (role `viewer`). Tests read `E2E_USER_EMAIL` / `E2E_USER_PASSWORD` env.
- **Backend in CI:** `php -S 127.0.0.1:18080 -t public/ public/index.php`. Frontend: `npx vite preview --port 5174` after `npm run build`. `BASE_URL` + `API_URL` env switch Playwright target.
- **CI skips docs/data/migration-only PRs** via `paths-ignore`. Don't expect CI runs on `*.md`/`PRPs/**`/`database/migrations/**`-only changes.

## Test env quirks (verified in `tests/bootstrap.php`)

- `phpunit.xml` uses short env names (`DB_NAME`, `DB_USER`, `DB_PASS`) but `config/database.php` reads `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. The bootstrap bridges them — keep both names in sync if you add new DB-related env.
- Bootstrap calls `Auth::init()` (starts session) + `ob_start()`. Tests asserting on response headers must account for the output buffer.
- Test DB is `hr_budget_test` (separate from `hr_budget`). Create it before running integration tests.
- `JWT_SECRET` in `phpunit.xml` is a 64-char test value; real `.env` must have ≥32 bytes or `Jwt::assertSecretSafe()` throws at first JWT use.

## Conventions not obvious from filenames

- **PR title format** (`.github/PULL_REQUEST_TEMPLATE.md`): `<type>: <short description>` — e.g. `feat(api): add budget request approval endpoint`. Commit messages in `git log` follow the same `feat(phase-N): ...` / `fix(deploy): ...` pattern.
- **Language rule:** Thai for all UI strings + user-facing error messages; English for logs, identifiers, commit types. Existing code mixes Thai strings in DTO `validate()` error messages — match that.
- **API responses must go through `src/Api/Responses/ApiResponse.php`** envelope (`success`/`data`/`error`/`details`). Never `echo json_encode(...)` from an API controller.
- **API layer is layered** (`Controllers → Services → Repositories` + `Dtos`, PSR-4 one class per file). The web/`Core` side is thin-controller/fat-model with static `Models/*` methods — do not mix the two styles in new API code.
- **Retirement is via git tags, not `archives/`.** `archives/` is git-ignored — moving code there deletes it from VCS. Restore retired files with `git checkout <tag> -- <path>`: `pre-spa-cutover`, `pre-budgets-retire`, `pre-files-retire`, `pre-views-sweep`.

## Stale docs to trust less than the code

- **`README.md` mentions `/budgets`, `/files`, `/folders` as live legacy remnants — they are retired** (per CLAUDE.md + git tags). The only live server-rendered remnant is ThaID login (`/thaid/login` → 302 alias).
- **`CLAUDE.md` says "no CI config checked in" — false; see ci.yml above.**
- **`CLAUDE.md` says `composer audit` / `vendor/bin/phpstan` aren't wired — still true** (not in CI, not in composer scripts).

## Migration gotchas

- **No migration framework.** Files in `database/migrations/` are hand-written SQL applied via `run_migrations.bat` / `run_migrations.sh` (shell out to `mysql` CLI).
- **Number collisions exist:** `022_*` (two files), `023_*` (two files), `024_*` (two files). Several numbers are skipped (`005`, `006`, `020`, `030`, `039`, `042–049`, `055–059`). Check the directory before adding a new one — don't just increment the highest existing number.
- **Some migrations come in pairs** (`NNN_*.sql` + `NNN_rollback_*.sql`) — keep that pattern if you add a reversible change.

## Kilo config

`.kilo/kilo.jsonc` is minimal: `$schema` + `snapshot: false` (git snapshots off). No project-level agent/command/skill overrides — defaults apply.