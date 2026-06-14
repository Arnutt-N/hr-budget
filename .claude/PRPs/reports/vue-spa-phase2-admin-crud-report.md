# Implementation Report: Phase 2 — Admin Master Data CRUD

## Summary
Converted all **8** admin master-data resources to the Phase-1 SPA stack
(PrimeVue DataTable + Dialog forms with vee-validate/zod + TanStack Query for
server state). Four existing resources were migrated off hand-built tables and
Pinia stores; four resources got brand-new REST API chains plus UI. The legacy
dark theme (sidebar/topbar from `resources/views/layouts/main.php`) was
reproduced in the SPA shell so the new pages match the old app visually.

## Assessment vs Reality

| Metric | Predicted (Plan) | Actual |
|---|---|---|
| Complexity | XL | XL — confirmed; milestone checkpoints were essential |
| Confidence | High (mechanical after exemplar) | Held — resources 2–8 were largely copy-paste of fiscal-years |
| Files Changed | ~50 (PHP ~24, FE ~22, e2e ~5) | ~100 across 4 commits (incl. theme pass + screenshots backup) |

## MVP Gate Result (PRD hypothesis)
**Validated.** Adding the 8th resource (targets) took materially less *new
thinking* than the 1st (fiscal-years): the TanStack composable + DataTable +
Dialog/zod shape is now a fill-in-the-fields template. The only real per-resource
work is the field list, the zod schema, and FK dropdowns. PrimeVue DataTable
covered all Thai-locale sort/filter/paginate needs with zero custom table code.

## Milestones Completed

| Checkpoint | Scope | Commit |
|---|---|---|
| A | fiscal-years exemplar + legacy dark theme + ConfirmationService wiring | `66edfa2` |
| B | migrate organizations, users, categories(+items expander) | `a801d17` |
| C | new API chains ×4 (divisions, plans, target-types, targets) + migration 064 + dump | `73acaa3` |
| D | new UI ×4 + 8-link sidebar + router routes | `d2620c5` |
| E | PRD status, report, archive plan | (this commit) |

## Validation Results

| Level | Status | Notes |
|---|---|---|
| Static Analysis (vue-tsc) | Pass | zero type errors across all FE files |
| Unit — frontend (vitest) | Pass | existing specs green |
| Unit — PHP (phpunit Unit) | Pass | 188 tests; **48 new** (Dtos+Services, SQLite in-memory). 4 pre-existing `tests/Unit/Models/*` errors remain (undefined methods / missing column — unrelated; CI runs only `Unit/{Api,Dtos,Services}`) |
| Build (vite) | Pass | production build ~26s, 6 chunks |
| E2E (Playwright) | Pass | **32/32** `admin-*.spec.ts` green (8 specs × 4) |
| Live API smoke | Pass | all 4 new endpoints round-tripped with Thai UTF-8 |

## Key Decisions & Deviations

1. **`plans` built against the real schema, not the legacy controller.** The
   live `plans` table has `budget_type_id`/`deleted_at` (soft delete); the legacy
   `BudgetPlanController` wrote `plan_type`/`parent_id`/`level` columns that don't
   exist. New chain mirrors the real table and soft-deletes via `deleted_at`.
2. **`TargetType` model never existed** — the legacy admin target-types page was
   already broken (imported a missing class). New chain is fully self-contained.
3. **migration 064** creates `divisions`/`target_types`/`budget_targets`. Per repo
   convention (global `*.sql` gitignore — none of 001–063 are tracked), the
   migration file is local-only; the canonical schema was added to the tracked
   `database/hr_budget_only.sql` so CI seeds `hr_budget_test` correctly.
4. **Router param fix (checkpoint B):** route params are now passed positionally;
   string keys became PHP 8 named arguments and fatally errored when a placeholder
   name (`{id}`) differed from the method parameter (`$categoryId`).
5. **`apiFetch` 204 handling:** DELETE returns 204 No Content; `res.json()` threw
   and was reported as failure — now treated as success.
6. **zod coerced-number + Select:** a required `<Select>` bound to a coerced-number
   field shows zod's English "Expected number, received nan" on empty submit unless
   `invalid_type_error` carries the Thai message. Fixed in TargetListPage.

## Issues Encountered & Resolved
- **Blank Milestone-D pages in first e2e run** — not a code bug; Vite cold-start
  was ~148s under disk pressure, so first-navigation lazy-route compiles exceeded
  the 15s heading timeout. Re-run with a warm server → green.
- **curl Thai payloads failed validation in smoke tests** — a Windows curl/bash
  arg-encoding artifact (mis-encoded UTF-8), reproducible against the *existing*
  organizations endpoint too. `printf | curl --data-binary @-` and the real
  browser both send correct UTF-8. Not a backend defect.

## Files Changed (high level)
- **PHP:** 4× (Repository, CreateDto, UpdateDto, Service, Api Controller) + 8 unit
  test files + 20 routes + `src/Core/Router.php` fix + `database/hr_budget_only.sql`
- **Frontend:** 8 list pages (4 rewritten, 4 new) + `CategoryItemsPanel.vue`,
  per-resource `types/`+`api/`+`queries/`, `main.ts`/`AppLayout.vue`/`router` wiring,
  dark-theme pass over dashboard/request pages + shared components, `style.css`,
  `tailwind.config.js`, `@lucide/vue` dependency
- **Tests:** 8 `tests/e2e/admin-*.spec.ts`
- **Deleted:** `stores/{fiscalYears,organizations,users,budgetCategories}.ts`

## Next Steps
- [ ] Code review of the branch diff; address HIGH/CRITICAL
- [ ] Push branch + open PR; merge once green
- [ ] Phase 3 (Dashboard + Notifications) and/or Phase 4 (Budget Request Workflow)
      — independent, may run in parallel; both depend only on Phase 2 conventions
