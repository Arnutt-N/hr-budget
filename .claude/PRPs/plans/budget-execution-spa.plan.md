# Plan: Budget Execution Reporting → Vue SPA

## Summary
Port the legacy server-rendered budget-execution report (`/budgets`, `BudgetExecutionController` + `budgets/execution.php` + `public/export_execution.php`) to the Vue SPA over a new read-only `/api/v1/budget-execution/*` API. Delivers KPI stats, a project→activity breakdown with quarterly disbursement columns, two ranked charts (top projects, top organizations), a year+organization filter, and an Excel (xlsx) export — all scoped by Buddhist-era fiscal year.

## User Story
As an HR-budget officer, I want to see and export budget-execution results (allocated vs disbursed, by project/activity and quarter) inside the SPA, so that I no longer depend on the retired server-rendered `/budgets` page.

## Problem → Solution
Legacy `/budgets` is a kept web remnant with no SPA equivalent → a layered API + SPA page replaces it, consistent with the dashboard/disbursement modules already on the SPA.

## Metadata
- **Complexity**: Medium
- **Source PRD**: N/A (post-cutover follow-up #1)
- **Estimated Files**: ~12 (4 backend, 2 backend tests, 6 frontend)

---

## Key Design Decisions

1. **Mirror the legacy 5-table join, do NOT query budget_trackings denormalized columns directly.**
   Money comes from `budget_trackings` (allocated/transfer/disbursed/po/pending); activity/project/org/fiscal_year/record_month come from `disbursement_records → disbursement_sessions → activities → projects → organizations`. The legacy report deliberately sourced those dimensions from records/sessions (the disbursement source-of-truth), because `budget_trackings.{record_month,project_id,organization_id}` are nullable and not reliably populated by the SPA disbursement flow. Joins are INNER and SQLite-portable → unit-testable.

2. **Derive grand-total stats by summing the breakdown in PHP** (not a separate aggregate query) so the headline KPI numbers are guaranteed to equal Σ(project rows). Avoids the legacy inconsistency where top-level "spending" excluded `pending` but per-project "spending" included it.

3. **`total_used = disbursed + pending + po`** everywhere (matches the SPA `DashboardService.summary()` definition already shown to users) so the execution report agrees with the dashboard. Individual components stay exposed for the table.

4. **Quarters (Thai fiscal year):** Q1=Oct,Nov,Dec (10,11,12) · Q2=Jan,Feb,Mar (1,2,3) · Q3=Apr,May,Jun (4,5,6) · Q4=Jul,Aug,Sep (7,8,9), bucketed on `disbursement_sessions.record_month`, summing `disbursed`.

5. **Charts respect the same (year, org) filter** as stats/breakdown — predictable & consistent. One reusable horizontal-bar component (`HorizontalBarChart.vue`) serves both the top-projects and top-orgs charts (ranked name→amount data, long Thai labels read better horizontally).

6. **Export = xlsx via the API** (`GET /api/v1/budget-execution/export`), reusing PhpSpreadsheet (already a dependency). Row-building (`exportRows()`) is a pure, testable service method; the controller does only thin streaming. Filename is `budget_execution_<year>.xlsx` (int year only → no user-controlled string in the header). Auth via the JWT httpOnly cookie on a same-origin GET (no CSRF header needed for GET).

---

## API Contract

`GET /api/v1/budget-execution?year={be}&org={id?}` → `ApiResponse.ok`:
```
{
  fiscal_year, organization_id,
  stats: { allocated, transfer, total_budget, disbursed, pending, po, total_used, remaining, used_percent },
  projects: [ { project_id, project_name, org_name, allocated, transfer, net_budget,
                disbursed, po, pending, total_used, balance, used_percent,
                q1, q2, q3, q4,
                activities: [ { activity_id, activity_name, ...same money/quarter fields } ] } ],
  category_chart: { labels:[], values:[] },   // top-5 projects by allocated + "อื่นๆ"
  org_chart:      { labels:[], values:[] }     // top-6 orgs by allocated
}
```
`GET /api/v1/budget-execution/years` → `ApiResponse.ok([{ fiscal_year }...])` (DISTINCT ds.fiscal_year DESC).
`GET /api/v1/budget-execution/export?year=&org=` → streamed xlsx (200) or 401 JSON.

Auth: `AuthMiddleware::require()` — any authenticated user (read-only; mirrors legacy `Auth::require()`). `CorsMiddleware::apply()` first.

---

## Files to Change

| File | Action | Justification |
|---|---|---|
| `src/Repositories/BudgetExecutionRepository.php` | CREATE | SQL: breakdownRows, orgTotals, availableYears |
| `src/Services/BudgetExecutionService.php` | CREATE | group/derive/shape report + exportRows |
| `src/Api/Controllers/BudgetExecutionController.php` | CREATE | report / years / export endpoints |
| `routes/web.php` | UPDATE | register 3 routes (static `/years`,`/export` before base) + alias |
| `tests/Unit/Services/BudgetExecutionServiceTest.php` | CREATE | SQLite seed of full chain; assert stats=Σrows, quarters, charts, filter |
| `frontend/src/types/budget-execution.ts` | CREATE | response types |
| `frontend/src/api/budgetExecution.ts` | CREATE | apiFetch wrappers + export URL helper |
| `frontend/src/queries/useBudgetExecution.ts` | CREATE | TanStack queries (report, years) |
| `frontend/src/components/HorizontalBarChart.vue` | CREATE | reusable ranked bar chart |
| `frontend/src/pages/BudgetExecutionPage.vue` | CREATE | KPI cards + filters + charts + expandable table + export |
| `frontend/src/router/index.ts` | UPDATE | `/budget-execution` route |
| `frontend/src/layouts/AppLayout.vue` | UPDATE | sidebar link |

## NOT Building
- Per-organization access scoping (read stays open to any authenticated user — legacy parity; org filter is a view filter, not authz).
- Retiring the legacy `/budgets` web routes/controller/view + `public/export_execution.php` (defer until SPA parity confirmed in production).
- Plan/search filters from the legacy filter bar (only year + org are wired; plan/search were unused placeholders in legacy — `$plans = []`).
- PDF export, drill-through to disbursement records.

---

## Patterns to Mirror
- **API controller**: `src/Api/Controllers/DashboardController.php` (CorsMiddleware→AuthMiddleware→try/ApiResponse::ok, `error_log` + `ApiResponse::error(...,500)`; `resolveFiscalYear()` from `?year=` or config default 2569).
- **Service SQL portability**: `src/Services/DashboardService.php` (COALESCE(SUM…), no MySQL-only funcs, BE/CE notes).
- **Repository style**: `src/Repositories/DisbursementSessionRepository.php` (`Database::query/queryOne`, `applyFilters(&$sql,$filters)` for optional org).
- **Service test (SQLite)**: `tests/Unit/Services/VaultServiceTest.php` (`Database::setInstance($pdo)` / `resetInstance()`, CREATE TABLE in setUp, `/** @test */`).
- **Frontend**: `pages/DashboardPage.vue` (StatCard grid, loading skeletons, error panel), `api/dashboard.ts`, `queries/useDashboard.ts`, `components/MonthlyExpenditureChart.vue` (chart.js register + dark theme colors), `pages/DocumentVaultPage.vue` (year Select filter + PrimeVue DataTable).

## Validation Commands
- `vendor/bin/phpunit tests/Unit/Services/BudgetExecutionServiceTest.php` → green
- `vendor/bin/phpunit tests/Unit/Api tests/Unit/Dtos tests/Unit/Services tests/Unit/Core` → no regressions (CI set)
- `cd frontend && npx vue-tsc --noEmit` → 0 errors
- `cd frontend && npm run build` → ok; then deploy build `VITE_BASE=/hr_budget/public/app/ npm run build` → rebuild `public/app`

## Risks
| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| budget_trackings rows with NULL disbursement_record_id excluded by INNER JOIN | Med | Med | Intentional — matches legacy; such rows lack a dimensional home. Document. |
| MySQL `GROUP BY p.id,a.id` with non-aggregated names | Low | Low | Wrap names in `MAX()` (legacy does this) — SQLite & MySQL-safe |
| Export auth via cookie on window.open | Low | Low | Same-origin GET sends httpOnly cookie; 401→JSON acceptable |
