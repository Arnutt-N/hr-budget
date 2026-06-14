# Implementation Report: Phase 4 — Budget Request Workflow (Vue SPA)

## Summary
Migrated the Budget Request + file-attachment **server-state from Pinia stores to
TanStack Query** composables, refactored all 4 request pages + `FileUploader.vue`
to consume them, deleted the two Pinia stores, and added **Vue-SPA Playwright
e2e** covering the full workflow. The backend API was already complete and was
**not changed**.

## Assessment vs Reality

| Metric | Predicted (Plan) | Actual |
|---|---|---|
| Complexity | Medium–Large | Medium |
| Confidence | High | High |
| Files Changed | ~12 | 10 (2 created queries, 5 refactored, 2 deleted, 1 e2e created) |
| Backend changes | 0 | 0 |

## Tasks Completed

| # | Task | Status | Notes |
|---|---|---|---|
| A | `useBudgetRequests.ts` + `useRequestFiles.ts` (TanStack) | ✅ | list/detail + create/update/delete/submit/approve/reject; file list + upload/delete |
| B | Refactor 4 pages + FileUploader off Pinia | ✅ | reactive query data; mutations; one-shot `watch` populate on Edit |
| C | Delete `stores/budgetRequests.ts` + `stores/files.ts` | ✅ | grep-verified no imports; vitest 10/10 still green |
| D | Verify self-approval + e2e | ✅ | backend allows self-approval → admin drives whole flow; no DB seed needed |
| E | Review + wrap-up | ✅ | 0 CRITICAL, 2 HIGH → both fixed |

## Validation Results

| Level | Status | Notes |
|---|---|---|
| Static Analysis (vue-tsc -b) | ✅ Pass | zero type errors |
| Frontend Unit (vitest) | ✅ Pass | 2 files / 10 tests |
| Build (vite) | ✅ Pass | 2119 modules transformed |
| PHP Unit (CI subset) | ✅ Pass | backend untouched (166/166 Services/Api/Dtos) |
| E2E (Playwright) | ⏳ CI | `budget-request-workflow.spec.ts` runs in CI |

## Files Changed

| File | Action |
|---|---|
| `frontend/src/queries/useBudgetRequests.ts` | CREATE — TanStack list/detail + 6 mutations |
| `frontend/src/queries/useRequestFiles.ts` | CREATE — TanStack file list + upload/delete |
| `frontend/src/pages/RequestListPage.vue` | UPDATE — `useBudgetRequestList(filters)`; dropped dead date inputs |
| `frontend/src/pages/RequestCreatePage.vue` | UPDATE — create + submit mutations; Toast on submit-fail |
| `frontend/src/pages/RequestEditPage.vue` | UPDATE — `useBudgetRequest` + update mutation; `watch` populate |
| `frontend/src/pages/RequestDetailPage.vue` | UPDATE — submit/approve/reject mutations; admin-only approve gate |
| `frontend/src/components/FileUploader.vue` | UPDATE — `useRequestFiles` composables |
| `frontend/src/stores/budgetRequests.ts` | DELETE |
| `frontend/src/stores/files.ts` | DELETE |
| `tests/e2e/budget-request-workflow.spec.ts` | CREATE — create→submit→approve + reject-with-note |

## Deviations from Plan
- **No DB seed needed (Task 8/D simplified).** Verified `BudgetRequestService::approve()`
  gates on `role === 'admin'` only and does **not** block self-approval, so a single
  admin user drives create→submit→approve in the e2e. The planned requester-seed was
  unnecessary.
- **Dropped the dead date-range inputs** on the list page — `api/budgetRequests.ts`
  never sent `date_from`/`date_to` and the backend doesn't filter on them, so the
  controls were non-functional (honest cleanup, not new backend filtering).

## Review Findings (fixed)
- **HIGH — `saveAndSubmit` swallowed the submit error.** Now raises a PrimeVue Toast
  (app-level in AppLayout → survives the route change to the detail page).
- **HIGH (consistency) — reactive `queryKey`.** Wrapped the ref-parameterised keys
  (`useBudgetRequestList`, `useBudgetRequest`, `useRequestFiles`) in
  `computed(() => [...])` — the explicit, version-robust reactive form.
- **Noted as improvement (no change):** `showApproveReject` now gates on `isAdmin`
  only (was `isAdmin || !isOwner`), matching the admin-only backend rule.

## Tests Written / Affected

| Test File | Tests | Coverage |
|---|---|---|
| `tests/e2e/budget-request-workflow.spec.ts` | 2 | create→submit→approve; create→submit→reject-with-note |

## Next Steps
- [x] Code review (2 HIGH fixed)
- [ ] PR → CI 3 jobs green → squash merge
- [ ] Phase 5 (Budget Tracking + Disbursements)
