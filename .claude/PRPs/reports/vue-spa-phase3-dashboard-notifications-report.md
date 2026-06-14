# Implementation Report: Phase 3 — Dashboard + Notifications (Vue SPA)

## Summary
Replaced the placeholder `DashboardPage.vue` with a real dashboard (stat cards +
monthly-expenditure bar chart via **vue-chartjs**), fed by a new JWT-authed
`/api/v1/dashboard` API chain. Migrated notification server-state off the Pinia
store onto the Phase-2 **TanStack Query** convention, kept the polling bell, and
added a dedicated `/notifications` list page.

## Assessment vs Reality

| Metric | Predicted (Plan) | Actual |
|---|---|---|
| Complexity | M–L | M |
| Confidence | High | High |
| Files Changed | ~18 | 20 (12 created, 7 modified, 1 deleted) |

## Tasks Completed

| # | Task | Status | Notes |
|---|---|---|---|
| 0 | Audit legacy `DashboardController` | ✅ | Found two data sources: `budget_trackings` (summary, BE year) + `budget_transactions` (chart, derived year) |
| 1 | Backend `/api/v1/dashboard` chain | ✅ | DashboardService + API Controller + 2 routes + DashboardServiceTest (4 tests, SQLite) |
| 2 | Chart deps + dashboard UI | ✅ | chart.js + vue-chartjs (npm@10 lock); StatCard + MonthlyExpenditureChart + DashboardPage rewrite |
| 3 | Notifications → TanStack + list page | ✅ | useNotifications composables; bell migrated; Pinia store deleted; NotificationListPage + route + nav |
| 4 | Wrap-up | ✅ | code review (1 HIGH fixed), report, PRD update, plan archive |

## Validation Results

| Level | Status | Notes |
|---|---|---|
| Static Analysis (vue-tsc) | ✅ Pass | zero type errors |
| Unit Tests (PHP) | ✅ Pass | DashboardServiceTest 4/4; CI subset Services/Api/Dtos 166/166 |
| Build (vite) | ✅ Pass | 2119 modules transformed |
| Integration / E2E | ⏳ CI | dashboard.spec.ts + notifications.spec.ts run in CI (Playwright) |
| Edge Cases | ✅ Pass | empty data → zeros; unread 0 → no badge; fiscal-window boundary |

> Note: pre-existing `tests/Unit/Models/*` require a live MySQL connection and
> error locally / are excluded from CI (CI runs only `Unit/{Api,Dtos,Services}`).

## Files Changed

### Backend
| File | Action |
|---|---|
| `src/Services/DashboardService.php` | CREATED — `summary()` + `monthlyExpenditure()` (driver-portable SQL) |
| `src/Api/Controllers/DashboardController.php` | CREATED — `summary()` + `chartData()`, JWT-authed |
| `routes/web.php` | UPDATED — import + 2 dashboard routes |
| `tests/Unit/Services/DashboardServiceTest.php` | CREATED — 4 tests (summary math, 12-month buckets, fiscal boundary, zeros) |
| `database/hr_budget_only.sql` | UPDATED — seed 1 FY-2569 expenditure + 1 unread notification (admin) for E2E |

### Frontend
| File | Action |
|---|---|
| `frontend/src/types/dashboard.ts` | CREATED |
| `frontend/src/api/dashboard.ts` | CREATED |
| `frontend/src/queries/useDashboard.ts` | CREATED |
| `frontend/src/queries/useNotifications.ts` | CREATED (TanStack; `refetchInterval:60_000`) |
| `frontend/src/components/StatCard.vue` | CREATED |
| `frontend/src/components/MonthlyExpenditureChart.vue` | CREATED (vue-chartjs Bar, dark theme) |
| `frontend/src/pages/NotificationListPage.vue` | CREATED |
| `frontend/src/pages/DashboardPage.vue` | REWRITTEN — cards grid + chart; one `<h1>` |
| `frontend/src/components/NotificationBell.vue` | UPDATED — consumes TanStack, not Pinia |
| `frontend/src/router/index.ts` | UPDATED — `/notifications` route |
| `frontend/src/layouts/AppLayout.vue` | UPDATED — Bell nav link |
| `frontend/src/stores/notifications.ts` | DELETED |
| `frontend/package.json` + lock | UPDATED — chart.js, vue-chartjs |
| `tests/e2e/dashboard.spec.ts` | CREATED |
| `tests/e2e/notifications.spec.ts` | CREATED |

## Deviations from Plan
- **Fiscal-year window for the chart (intentional correctness fix).** The plan
  said to mirror the legacy `getChartData()` calendar-year filter
  (`YEAR(created_at) = fiscalYear-543`). Code review (HIGH) flagged that this
  drops Oct–Dec of the fiscal year (which starts Oct 1). **Fix:** scan the real
  fiscal window `[Oct 1 (ceYear-1), Oct 1 ceYear)` instead. Unit-test seeds and
  the SQL dump seed were aligned to the corrected window.
- **SQL kept driver-portable** (no MySQL `YEAR()`/`MONTH()`), bucketing in PHP,
  so `DashboardService` is unit-testable on SQLite in-memory.

## Issues Encountered
- `vue-tsc -b` build failed on `Intl.format(ctx.parsed.y)` (chart.js tooltip
  type) → fixed by `Number(ctx.parsed.y)` coercion.
- Local PHP not on PATH (Bash) → ran PHPUnit via Laragon PHP 8.3 in PowerShell.

## Tests Written

| Test File | Tests | Coverage |
|---|---|---|
| `tests/Unit/Services/DashboardServiceTest.php` | 4 | summary aggregation, 12-month bucketing, fiscal boundary, empty→zeros |
| `tests/e2e/dashboard.spec.ts` | 1 | heading + stat cards + chart canvas |
| `tests/e2e/notifications.spec.ts` | 1 | badge, dropdown, list page, mark-all-read |

## Next Steps
- [x] Code review via code-reviewer (1 HIGH fixed)
- [ ] PR → CI 3 jobs green → squash merge
- [ ] Phase 4 (Budget Request Workflow)
