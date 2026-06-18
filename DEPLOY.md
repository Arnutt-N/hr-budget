# Deploy — topzlab.com/hr-budget (Plesk + Git)

Runbook for deploying this app to a Plesk host under the subdirectory
`httpdocs/hr-budget`, served at **`https://topzlab.com/hr-budget/public/app/`**.

The app is a PHP 8.3 JSON API + a compiled Vue SPA. Plesk pulls `main` from
GitHub; two things Git does NOT carry (`vendor/`, `.env`) are supplied on the
server.

---

## 1. Plesk Git — pull + deployment action

Websites & Domains → `topzlab.com` → **Git**:

- **Repository**: `https://github.com/Arnutt-N/hr-budget.git`
- **Branch**: `main`
- **Deployment path**: `httpdocs/hr-budget`
- **Enable additional deployment actions** → run after every pull:

  ```
  composer install --no-dev --optimize-autoloader
  ```

  `vendor/` is git-ignored, so without this the app dies at
  `require vendor/autoload.php` → **HTTP 500**. This action rebuilds it on the
  server each pull.

> If Plesk has no Composer, use the prebuilt fallback ZIP (ships with `vendor/`):
> upload + extract into `httpdocs/hr-budget`. Then you can skip the action.

## 2. `.env` on the server (once — survives Git pulls)

File Manager → `httpdocs/hr-budget/` → copy `.env.production.example` → `.env`,
then fill:

- `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` — from Plesk → Databases
- `JWT_SECRET` — a long random string (login signs/verifies JWT with it). Generate:
  `powershell -c "$b=New-Object byte[] 32;[Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($b);-join($b|%{$_.ToString('x2')})"`
- Keep `APP_DEBUG=false`, `COOKIE_SECURE=true`, `SESSION_SECURE=true` (HTTPS).

## 3. Database

Create a MySQL database in Plesk, then load the schema + a safe seed.

> ⚠️ The production `hr_budget` DB holds **real MoJ budget figures**
> (`budget_allocations`, `budget_monthly_snapshots`). Do NOT export real
> financial data to this public host — ship **schema + reference/demo data only**
> (RBAC, organizations, provinces, fiscal years, chart of accounts, one demo
> admin). Transactional/financial tables stay empty.

PHP version: ensure the domain runs **PHP 8.3+** (Plesk → PHP Settings).

## 4. Open the app

`https://topzlab.com/hr-budget/public/app/` — the trailing **`/app/`** is
required: the Vue Router base equals the Vite build base
(`/hr-budget/public/app/`). Bookmark that URL.

---

## How it routes (why the paths look the way they do)

- Document root is `httpdocs`; the app lives in `httpdocs/hr-budget/`.
- `BASE_URL` (PHP) is derived from the script dir — no hardcoded path.
- SPA is built with `VITE_BASE=/hr-budget/public/app/` (asset base) and
  `VITE_API_BASE_URL=/hr-budget/public` (so `apiFetch` calls
  `/hr-budget/public/api/v1/*`, not the domain root). **Rebuild in PowerShell**,
  never Git Bash — MSYS path conversion mangles `/hr-budget/...` into
  `/Program Files/Git/...` and bakes it into `index.html`:

  ```powershell
  cd frontend
  $env:VITE_BASE='/hr-budget/public/app/'; $env:VITE_API_BASE_URL='/hr-budget/public'
  npm run build   # → ../public/app  (commit this artifact)
  ```

## Troubleshooting

| Symptom | Cause | Fix |
|---|---|---|
| HTTP 500, log: `Failed opening ... vendor/autoload.php` | `vendor/` missing | run `composer install` (deployment action) |
| HTTP 500, log: `Invalid command 'Order'` | Apache 2.4 without mod_access_compat | already guarded with `<IfModule>` in `public/.htaccess` — pull latest |
| Login fails / 401 everywhere | `JWT_SECRET` empty in `.env` | set a long random `JWT_SECRET` |
| SPA loads blank, assets 404 at `/Program Files/Git/...` | SPA built in Git Bash (MSYS mangling) | rebuild in PowerShell (above), commit `public/app/` |
| API calls 404 / hit the domain root | SPA built without `VITE_API_BASE_URL` | rebuild with `VITE_API_BASE_URL=/hr-budget/public` |
| `No matching DirectoryIndex` at domain root | hitting `/` not `/hr-budget/public/app/` | use the full app URL |

To see the real error behind a bare 500: set `APP_DEBUG=true` in `.env`
temporarily, reload, then set it back to `false`.
