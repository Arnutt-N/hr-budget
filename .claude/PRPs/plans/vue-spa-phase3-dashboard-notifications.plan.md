# Plan: Phase 3 — Dashboard + Notifications (Vue SPA)

## Summary
Replace the 25-line placeholder `DashboardPage.vue` with a real dashboard: stat
cards + a monthly-expenditure chart via **vue-chartjs**, fed by a **new
JWT-authed `/api/v1/dashboard` API chain** (the existing `/api/dashboard/chart-data`
is a *web* route using PHP session auth — unusable from the cookie+JWT SPA).
Notifications are already functional (`NotificationBell.vue` polls every 60s with
a Pinia store); Phase 3 adds a dedicated **notifications list page** and migrates
the notification server-state off Pinia onto the Phase-2 TanStack convention.

## PRD
- Source: `.claude/PRPs/prds/vue-spa-refactor.prd.md` — Phase 3
- Depends: Phase 2 (merged `79fa637`). Independent of Phase 4.

## Problem → Solution
- Dashboard is a placeholder; chart-data endpoint is session-authed + outside `/api/v1` → **new `DashboardController` (API layer)** with `summary` + `chartData`, JWT-authed, logic ported from `src/Controllers/DashboardController.php`.
- Notification server-state lives in a Pinia store (violates Phase-2 "no Pinia for server state") → **TanStack composables** (`queries/useNotifications.ts`); keep the bell, add a full list page.

## Mandatory Reading
| Priority | File | Why |
|---|---|---|
| P0 | `src/Controllers/DashboardController.php` (`getChartData`, `getCurrentFiscalYear`, any summary/stat methods) | logic to port into the API Service; chart-data shape `{labels:[12 Thai months], data:[12 numbers]}` |
| P0 | `src/Api/Controllers/FiscalYearController.php` + `src/Services/FiscalYearService.php` | API_CONTROLLER + service pattern to mirror (CORS→Auth→ApiResponse) |
| P0 | `frontend/src/pages/FiscalYearListPage.vue` | DataTable/Dialog conventions (for the notifications list page) |
| P0 | `frontend/src/queries/useFiscalYears.ts` | TANSTACK_CRUD pattern for `useNotifications.ts` |
| P1 | `frontend/src/components/NotificationBell.vue` + `frontend/src/stores/notifications.ts` + `frontend/src/api/notifications.ts` + `frontend/src/types/notification.ts` | existing notification surface to reuse / migrate off Pinia |
| P1 | `frontend/src/composables/useApi.ts` | apiFetch (note: 204 handling already added in Phase 2) |
| P1 | `frontend/src/lib/date.ts` (`formatThaiDate`) | Thai BE date formatting for cards/list |
| P2 | `frontend/src/layouts/AppLayout.vue` (topbar BE-year badge, currentFiscalYear computed) | reuse year context; topbar title is a `<div>` not heading |
| P2 | `tests/e2e/api/auth-flow.spec.ts` | E2E style; dashboard heading is `getByRole('heading',{name:/Dashboard/i})` — keep ONE heading on the page |

## External Documentation
| Topic | Source | Takeaway |
|---|---|---|
| vue-chartjs v5 | vue-chartjs.org | `<Bar :data :options />`; register chart.js components once (`Chart.register(...)`); reactive `data` prop |
| chart.js v4 | chartjs.org | tree-shakeable; register `CategoryScale, LinearScale, BarElement, Tooltip, Legend` |
| TanStack Query polling | tanstack.com/query | `useQuery({ refetchInterval: 60_000 })` replaces the manual `setInterval` in the bell |

## Patterns to Mirror
- **API_CONTROLLER**: `src/Api/Controllers/FiscalYearController.php` — CORS → AuthMiddleware → (no admin gate; dashboard is read-only for any authed user) → Service → `ApiResponse::ok($data)`; try/catch → `error_log` + 500 Thai.
- **SERVICE**: read-only `DashboardService` (no role gate beyond authed); methods `summary(int $fiscalYear)`, `monthlyExpenditure(int $fiscalYear)`. Reuse `Database::query` with bound params (mirror the web controller's SQL).
- **TANSTACK**: `queries/useDashboard.ts` (`useDashboardSummary`, `useMonthlyChart` — both `useQuery`, no mutations); `queries/useNotifications.ts` (`useUnreadCount` with `refetchInterval: 60_000`, `useNotificationList`, `useMarkRead`/`useMarkAllRead` mutations invalidating `['notifications']` + `['notifications','unread']`).
- **CHART COMPONENT**: a small `components/MonthlyExpenditureChart.vue` wrapping vue-chartjs `<Bar>`, props `{ labels, data }`, dark-theme grid/tick colors.

## Files to Change
### Backend (new `/api/v1/dashboard` chain)
| File | Action |
|---|---|
| `src/Services/DashboardService.php` | CREATE — `summary()` + `monthlyExpenditure()` ported from web `DashboardController` (bound params, BE→CE year) |
| `src/Api/Controllers/DashboardController.php` (namespace `App\Api\Controllers`) | CREATE — `summary()`, `chartData()` methods (JWT-authed, ApiResponse envelope) |
| `routes/web.php` | UPDATE — `GET /api/v1/dashboard/summary`, `GET /api/v1/dashboard/chart-data` (+ import) |
| `tests/Unit/Services/DashboardServiceTest.php` | CREATE — SQLite in-memory; seed budget_transactions; assert 12-month array + summary totals; BE/CE boundary |

### Frontend
| File | Action |
|---|---|
| `frontend/package.json` + lock (`npx -y npm@10 install chart.js vue-chartjs`) | UPDATE — add deps (regenerate lock with npm@10 for CI) |
| `frontend/src/types/dashboard.ts` | CREATE — `DashboardSummary`, `MonthlyChart` interfaces |
| `frontend/src/api/dashboard.ts` | CREATE — `fetchDashboardSummary`, `fetchMonthlyChart` |
| `frontend/src/queries/useDashboard.ts` | CREATE — TanStack read queries |
| `frontend/src/components/MonthlyExpenditureChart.vue` | CREATE — vue-chartjs Bar wrapper (dark theme) |
| `frontend/src/components/StatCard.vue` | CREATE — reusable stat card (label, value, accent, optional icon) |
| `frontend/src/pages/DashboardPage.vue` | REWRITE — stat cards grid + chart; keep exactly ONE `<h1>` heading |
| `frontend/src/queries/useNotifications.ts` | CREATE — TanStack (unread-count polling + list + mark mutations) |
| `frontend/src/components/NotificationBell.vue` | UPDATE — consume TanStack composables instead of Pinia store |
| `frontend/src/pages/NotificationListPage.vue` | CREATE — full list (DataTable or simple list) + mark read/all; row click → link |
| `frontend/src/router/index.ts` | UPDATE — add `/notifications` route (`meta.title`) |
| `frontend/src/layouts/AppLayout.vue` | UPDATE — make the bell/nav link to `/notifications` |
| `frontend/src/stores/notifications.ts` | DELETE (after bell migrated) |
| `tests/e2e/dashboard.spec.ts` | CREATE — login → dashboard → stat cards + chart canvas visible |
| `tests/e2e/notifications.spec.ts` | CREATE — bell badge, dropdown, list page, mark-all-read |

## Step-by-Step Tasks
### Task 0: Audit (bounded)
Read web `DashboardController` fully — list every stat it computes (budget allocated/spent/remaining, request counts by status, etc.) and the exact SQL/tables (`budget_transactions`, `budget_requests`, `fiscal_years`). Confirm `getCurrentFiscalYear()` logic. Produce a field list for `DashboardSummary` before coding.

### Task 1: Backend `/api/v1/dashboard`
- DashboardService (SQLite-testable, bound params) → API DashboardController → 2 routes → DashboardServiceTest.
- **GOTCHA**: BE↔CE year (`fiscal_year - 543` for `YEAR(created_at)`); month index `($month - 10 + 12) % 12` for the Oct-start labels.
- VALIDATE: `phpunit --filter=DashboardServiceTest`; smoke `curl` both endpoints (UTF-8 via `printf|--data-binary`).

### Task 2: Chart deps + dashboard UI
- Install chart.js + vue-chartjs with `npx -y npm@10`. Register chart.js components once (a `lib/chart.ts` or in the component).
- StatCard + MonthlyExpenditureChart + DashboardPage rewrite (TanStack queries; loading/empty/error states; dark theme).
- **GOTCHA**: keep exactly ONE page heading (`auth-flow.spec.ts` asserts `getByRole('heading',{name:/Dashboard/i})` — strict mode). Topbar title is a `<div>` (Phase 2), so the page `<h1>` is the only heading — make it contain "Dashboard" or update that assertion intentionally.
- VALIDATE: typecheck + build + dashboard e2e.

### Task 3: Notifications → TanStack + list page
- `useNotifications.ts` (unread-count `refetchInterval:60_000`); migrate `NotificationBell.vue` off Pinia; delete `stores/notifications.ts` (grep no imports remain).
- NotificationListPage + route + nav.
- VALIDATE: typecheck + notifications e2e; bell still polls.

### Task 4: Wrap-up
PRD Phase 3 → complete + report; archive plan; full sweep.

## Testing Strategy
- **Unit (PHP, CI)**: `DashboardServiceTest` — summary math, 12-month bucketing, BE/CE boundary, empty data → zeros.
- **E2E**: `dashboard.spec.ts` (cards + `<canvas>` visible after login), `notifications.spec.ts` (badge count, dropdown list, mark-all clears badge, list page renders).
- **Edge**: fiscal year with zero transactions → all-zero chart, no crash; unread-count 0 → no badge.

## Validation Commands
```bash
cd frontend && npm run typecheck && npm run test:unit && npm run build
php vendor/phpunit/phpunit/phpunit --testsuite Unit     # only pre-existing Models errors
# E2E: php -S :8000 + vite :5174 (VITE_API_URL=http://127.0.0.1:8000) + mysqld
$env:BASE_URL='http://localhost:5174'; $env:API_URL='http://127.0.0.1:8000'
npx playwright test tests/e2e/dashboard.spec.ts tests/e2e/notifications.spec.ts tests/e2e/api/auth-flow.spec.ts --workers=1
```

## Acceptance Criteria
- [ ] Dashboard shows real stat cards + monthly chart from `/api/v1/dashboard/*` (JWT-authed)
- [ ] vue-chartjs renders; dark-theme styled; loading/empty/error states
- [ ] Notification server-state on TanStack (Pinia `stores/notifications.ts` deleted); bell still polls; `/notifications` list page works
- [ ] `DashboardServiceTest` green in CI; all e2e green incl. `auth-flow`
- [ ] CI 3 jobs green → squash merge

## Risks
| Risk | Mitigation |
|---|---|
| chart.js/vue-chartjs lock breaks CI (npm 10 vs 11) | install with `npx -y npm@10`; no Windows-only optionalDeps (Phase-1 lesson) |
| Dashboard heading collides with auth-flow strict-mode | exactly one page heading; decide its text vs the test deliberately |
| Web DashboardController SQL assumes session/tables not in `hr_budget_only.sql` | DashboardServiceTest uses SQLite with its own seed; verify `budget_transactions` is in the CI dump or the test is self-contained |
| Notification migration breaks the bell | migrate + run notifications e2e before deleting the Pinia store |
```
