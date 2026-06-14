# Plan: Phase 4 — Budget Request Workflow (Vue SPA)

## Summary
The Budget Request backend (`/api/v1/requests` CRUD + submit/approve/reject, file
upload, role gating, notifications) **and** four interactive Vue pages already
exist — but the pages drive **server state through Pinia stores**
(`stores/budgetRequests.ts`, `stores/files.ts`), violating the Phase 2/3
convention (TanStack Query for server state). Phase 4 migrates the request +
file server-state onto **TanStack Query composables**, refactors the 4 pages +
`FileUploader.vue` to consume them, deletes the two Pinia stores, and adds
**real Vue-SPA Playwright e2e** for the full workflow (the existing
`budget-requests-*.spec.ts` target the legacy PHP `/requests/dashboard` view).

## User Story
As a **budget officer**, I want to create a request with line items, save it as a
draft, submit it for approval, and (as an admin) approve or reject it with a note
— all in the Vue SPA with instant, cache-consistent UI — so the request workflow
no longer depends on the legacy PHP views or ad-hoc Pinia state.

## Problem → Solution
4 pages backed by Pinia server-state + e2e covering only the old PHP view →
TanStack-backed pages (cache + auto-invalidation), Pinia stores deleted, and
SPA-targeting e2e covering create→submit→approve and reject-with-note + upload.

## Metadata
- **Complexity**: Medium–Large (frontend migration, no backend change)
- **Source PRD**: `.claude/PRPs/prds/vue-spa-refactor.prd.md`
- **PRD Phase**: Phase 4 — Budget Request Workflow
- **Estimated Files**: ~12 (2 created queries, 5 refactored Vue, 2 deleted stores, 1 dump seed, 1–2 e2e)

---

## Mandatory Reading

| Priority | File | Why |
|---|---|---|
| P0 | `frontend/src/queries/useNotifications.ts` | TanStack exemplar — `useQuery` + `useMutation` + `invalidateQueries`, `MaybeRefOrGetter` enabled, reactive keys (built Phase 3) |
| P0 | `frontend/src/queries/useFiscalYears.ts` | TanStack list+mutation exemplar (throw on `!res.success`, invalidate on mutate) |
| P0 | `frontend/src/api/budgetRequests.ts` | The 8 API functions the request composables wrap (do not change) |
| P0 | `frontend/src/api/files.ts` | `fetchRequestFiles`/`uploadRequestFile`/`deleteFile` the file composable wraps |
| P0 | `frontend/src/types/budget-request.ts` | `RequestStatus` enum, `BudgetRequest`, `BudgetRequestItem`, `CreateBudgetRequest`, `UpdateBudgetRequest`, `ListFilters`, `ListMeta`, `STATUS_LABELS` |
| P0 | `frontend/src/stores/budgetRequests.ts` | The store being replaced — its method list maps 1:1 to the new composables; shows which pages call what |
| P0 | `frontend/src/stores/files.ts` | The file store being replaced (`fetchForRequest`/`upload`/`remove`) |
| P0 | `frontend/src/pages/RequestListPage.vue` | refactor target — list + filters + pagination |
| P0 | `frontend/src/pages/RequestCreatePage.vue` | refactor target — create + "save draft" / "save & submit"; item editor |
| P0 | `frontend/src/pages/RequestEditPage.vue` | refactor target — edit draft/saved + FileUploader |
| P0 | `frontend/src/pages/RequestDetailPage.vue` | refactor target — detail + items + approval history + submit/approve/reject + FileUploader |
| P0 | `frontend/src/components/FileUploader.vue` | refactor target — currently uses `useFileStore` |
| P1 | `frontend/src/types/file.ts` | `FileAttachment` shape used by file composable |
| P1 | `tests/e2e/dashboard.spec.ts` + `tests/e2e/notifications.spec.ts` | e2e login helper (`loginAsAdmin`) + assertion conventions to mirror |
| P1 | `src/Services/BudgetRequestService.php` | **VERIFY**: does `approve()` block self-approval? Determines e2e seed (one admin vs admin+requester) |
| P2 | `frontend/src/composables/useApi.ts` | `apiFetch` (204 handled); FormData path for uploads (`isFormData` 3rd arg) |
| P2 | `frontend/src/router/index.ts` | request routes already wired (`/requests`, `/requests/create`, `/requests/:id`, `/requests/:id/edit`) — no change expected |

## External Documentation
No external research needed — uses established internal patterns (TanStack Query
Vue, PrimeVue, Playwright) already proven in Phases 2–3.

---

## Patterns to Mirror

### TANSTACK_LIST_WITH_REACTIVE_FILTERS
```ts
// SOURCE: frontend/src/queries/useNotifications.ts + useFiscalYears.ts
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { toValue, type MaybeRefOrGetter } from 'vue'

const KEY = ['budget-requests'] as const

export function useBudgetRequestList(filters: MaybeRefOrGetter<ListFilters> = {}) {
  return useQuery({
    queryKey: [...KEY, 'list', filters],          // reactive: refetches when filters change
    queryFn: async () => {
      const res = await fetchRequests(toValue(filters))
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดคำขอไม่สำเร็จ')
      return { data: res.data, meta: res.meta as ListMeta | undefined }
    },
  })
}
```
> NOTE: passing a ref/getter inside `queryKey` makes TanStack re-run the query when filters change — no manual refetch.

### TANSTACK_SINGLE_BY_ID
```ts
export function useBudgetRequest(id: MaybeRefOrGetter<number>) {
  return useQuery({
    queryKey: [...KEY, 'detail', id],
    queryFn: async () => {
      const res = await fetchRequestById(toValue(id))
      if (!res.success || !res.data) throw new Error(res.error ?? 'ไม่พบคำขอ')
      return res.data
    },
    enabled: () => toValue(id) > 0,
  })
}
```

### TANSTACK_MUTATION_WITH_INVALIDATION
```ts
export function useApproveBudgetRequest() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, note }: { id: number; note?: string }) => {
      const res = await approveRequest(id, note)
      if (!res.success) throw new Error(res.error ?? 'ไม่สามารถอนุมัติได้')
      return res.data
    },
    onSuccess: (_d, { id }) => {
      qc.invalidateQueries({ queryKey: [...KEY] })                 // list + detail
      qc.invalidateQueries({ queryKey: ['notifications'] })        // approve notifies requester
    },
  })
}
```

### TANSTACK_FILE_UPLOAD (FormData via apiFetch isFormData)
```ts
// SOURCE: frontend/src/api/files.ts (uploadRequestFile already builds FormData + calls apiFetch(path, {body}, true))
const FILE_KEY = (rid: number) => ['request-files', rid] as const
export function useRequestFiles(requestId: MaybeRefOrGetter<number>) { /* useQuery */ }
export function useUploadRequestFile() { /* useMutation → invalidate FILE_KEY(rid) */ }
export function useDeleteRequestFile() { /* useMutation → invalidate FILE_KEY(rid) */ }
```

### E2E_LOGIN_HELPER
```ts
// SOURCE: tests/e2e/dashboard.spec.ts
async function loginAsAdmin(page) {
  await page.goto('/login');
  await page.fill('input[name="email"]', 'admin@moj.go.th');
  await page.fill('input[name="password"]', 'admin123');
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard');
}
```

---

## Files to Change

| File | Action | Justification |
|---|---|---|
| `frontend/src/queries/useBudgetRequests.ts` | CREATE | TanStack list/detail + 6 mutations |
| `frontend/src/queries/useRequestFiles.ts` | CREATE | TanStack per-request file list + upload/delete |
| `frontend/src/pages/RequestListPage.vue` | UPDATE | consume `useBudgetRequestList`; keep existing table UI |
| `frontend/src/pages/RequestCreatePage.vue` | UPDATE | `useCreateBudgetRequest` (+ submit chain for save&submit) |
| `frontend/src/pages/RequestEditPage.vue` | UPDATE | `useBudgetRequest` + `useUpdateBudgetRequest` + FileUploader |
| `frontend/src/pages/RequestDetailPage.vue` | UPDATE | `useBudgetRequest` + submit/approve/reject mutations |
| `frontend/src/components/FileUploader.vue` | UPDATE | consume `useRequestFiles` instead of `useFileStore` |
| `frontend/src/stores/budgetRequests.ts` | DELETE | replaced by TanStack (after no imports remain) |
| `frontend/src/stores/files.ts` | DELETE | replaced by TanStack (after no imports remain) |
| `database/hr_budget_only.sql` | UPDATE | seed for e2e (see Task 8 — verify self-approval first) |
| `tests/e2e/budget-request-workflow.spec.ts` | CREATE | SPA full-workflow e2e (create→submit→approve; reject-with-note) |
| `tests/e2e/budget-request-files.spec.ts` | CREATE (optional) | file upload on a draft via SPA |

## NOT Building
- ❌ Any backend/API change (controller/service/DTO/routes are complete & green)
- ❌ Bulk actions (bulk approve/delete) — explicitly out of scope
- ❌ Hierarchical / multi-level approvers — out of scope (admin-only approve stays)
- ❌ Swapping the list table to PrimeVue DataTable — keep the existing custom table; only swap the data source (avoid UI churn)
- ❌ Date-range (`date_from`/`date_to`) server filtering — backend doesn't implement it; leave the inputs as-is or drop, do not add backend filtering
- ❌ Retiring the legacy PHP `/requests/dashboard` view or its old e2e specs (that is Phase 6 cutover)

---

## Step-by-Step Tasks

### Task 1: `queries/useBudgetRequests.ts`
- **ACTION**: Create the TanStack composables wrapping `api/budgetRequests.ts`.
- **IMPLEMENT**: `useBudgetRequestList(filters)`, `useBudgetRequest(id)`, and mutations `useCreateBudgetRequest`, `useUpdateBudgetRequest`, `useDeleteBudgetRequest`, `useSubmitBudgetRequest`, `useApproveBudgetRequest`, `useRejectBudgetRequest`. List returns `{ data, meta }`. Every mutation `invalidateQueries({ queryKey: ['budget-requests'] })`; approve/reject/submit also invalidate `['notifications']` (server dispatches notifications).
- **MIRROR**: TANSTACK_LIST_WITH_REACTIVE_FILTERS / SINGLE_BY_ID / MUTATION_WITH_INVALIDATION.
- **IMPORTS**: `useQuery, useMutation, useQueryClient` from `@tanstack/vue-query`; `toValue, type MaybeRefOrGetter` from `vue`; the 8 fns from `@/api/budgetRequests`; types from `@/types/budget-request`.
- **GOTCHA**: `reject` requires a non-empty `note`; `approve` note is optional. Keep mutation input typed (`{ id, note }`). Do not read `.value` off a `MaybeRefOrGetter` — use `toValue`.
- **VALIDATE**: `npm run typecheck`.

### Task 2: `queries/useRequestFiles.ts`
- **ACTION**: Create TanStack composables wrapping `api/files.ts`.
- **IMPLEMENT**: `useRequestFiles(requestId)` (query, `enabled: () => toValue(requestId) > 0`), `useUploadRequestFile()` (mutation `{ requestId, file }` → `uploadRequestFile`), `useDeleteRequestFile()` (mutation `{ requestId, fileId }` → `deleteFile`). Both mutations invalidate `['request-files', requestId]`.
- **MIRROR**: TANSTACK_FILE_UPLOAD.
- **IMPORTS**: `@/api/files`, `@/types/file` (`FileAttachment`).
- **GOTCHA**: upload goes through `apiFetch(path, { method:'POST', body: formData }, /*isFormData*/ true)` — already handled inside `uploadRequestFile`; the composable just calls it. Do not set Content-Type for FormData.
- **VALIDATE**: `npm run typecheck`.

### Task 3: Refactor `RequestListPage.vue`
- **ACTION**: Replace `useBudgetRequestStore` with `useBudgetRequestList(filters)`.
- **IMPLEMENT**: Hold `filters` as a `reactive`/`ref<ListFilters>`; pass into the composable so editing filters refetches. Bind table rows to `query.data.value?.data ?? []`, pagination to `query.data.value?.meta`. Map loading/error to `query.isLoading.value`/`query.isError.value`. Keep exactly one `<h1>` (page already has its heading — verify, do not add a second).
- **MIRROR**: useNotifications/useFiscalYears consumption in existing pages (e.g. `FiscalYearListPage.vue`).
- **GOTCHA**: AppLayout topbar is a `<div>` not a heading — page owns the only `<h1>`. Reuse `STATUS_LABELS` for the status column.
- **VALIDATE**: typecheck + build.

### Task 4: Refactor `RequestCreatePage.vue`
- **ACTION**: Replace store `create` with `useCreateBudgetRequest`; for "save & submit", chain `useSubmitBudgetRequest` after create resolves with the new id.
- **IMPLEMENT**: `await createMut.mutateAsync(payload)` → on success route to detail or list; "save & submit" → create then `submitMut.mutateAsync(newId)`. Toast on success/error (PrimeVue `useToast`, as Phase 2 pages do).
- **GOTCHA**: `CreateBudgetRequest.items` is `Omit<BudgetRequestItem,'id'|'budget_request_id'|'amount'>[]` — do NOT send `amount` (server computes). Keep the existing ItemEditor; only swap the submit path.
- **VALIDATE**: typecheck + build.

### Task 5: Refactor `RequestEditPage.vue`
- **ACTION**: Replace store with `useBudgetRequest(id)` (load) + `useUpdateBudgetRequest` (save). FileUploader now self-manages via `useRequestFiles`.
- **GOTCHA**: editing only allowed for `draft`/`saved` (guard already exists — keep it). After update, the detail/list caches invalidate automatically.
- **VALIDATE**: typecheck + build.

### Task 6: Refactor `RequestDetailPage.vue`
- **ACTION**: Replace store with `useBudgetRequest(id)` + `useSubmitBudgetRequest`/`useApproveBudgetRequest`/`useRejectBudgetRequest`.
- **IMPLEMENT**: Submit button visible to owner when `draft`/`saved`; Approve/Reject visible to admin when `pending`. Reject opens a note dialog (note required). Render `items` table + `approvals` history (`ApprovalLog`). After any action the detail re-fetches via invalidation.
- **GOTCHA**: role check uses `useAuthStore().user?.role === 'admin'`. Keep one `<h1>`. `note` required for reject — validate before calling mutate.
- **VALIDATE**: typecheck + build.

### Task 7: Refactor `FileUploader.vue`
- **ACTION**: Replace `useFileStore` with `useRequestFiles(requestId)` + upload/delete mutations.
- **IMPLEMENT**: list = `filesQuery.data.value ?? []`; on file selected → `uploadMut.mutateAsync({ requestId, file })`; on remove → `deleteMut.mutateAsync({ requestId, fileId })`. Keep client-side validation (ext allowlist, 10 MB) unchanged.
- **VALIDATE**: typecheck + build.

### Task 8: Seed e2e data + verify self-approval
- **ACTION**: Read `src/Services/BudgetRequestService.php::approve()`. **IF** it blocks self-approval (approver ≠ creator), seed a *requester* user + a `pending` `budget_requests` row (with items) owned by that requester into `database/hr_budget_only.sql`, so admin can approve it. **IF** self-approval is allowed, no seed needed — the e2e creates its own data through the UI as admin.
- **GOTCHA**: dump loads `budget_requests`/`budget_request_items` before `users`; FK checks are disabled during import (`FOREIGN_KEY_CHECKS=0`) so referencing a user id that exists by import-end is safe. Admin user id = 2 (`admin@moj.go.th`/`admin123`). Match the real column order of `budget_requests`/`budget_request_items` from their `CREATE TABLE` in the dump.
- **VALIDATE**: n/a (verified by the e2e run).

### Task 9: SPA e2e — `tests/e2e/budget-request-workflow.spec.ts`
- **ACTION**: Cover the SPA happy path + reject path.
- **IMPLEMENT**: (a) login → `/requests/create` → fill title + 1 item → "save & submit" → assert it appears as `รออนุมัติ`; (b) open its detail → admin "อนุมัติ" → assert `อนุมัติแล้ว`; (c) a second request → reject with a note → assert `ปฏิเสธ` + reason shown. Use `loginAsAdmin` + role-aware buttons.
- **GOTCHA**: if backend blocks self-approval, drive approve/reject against the *seeded* requester-owned pending request instead of an admin-owned one. Keep selectors resilient (getByRole + Thai names). One `--workers=1` run (shared DB).
- **VALIDATE**: `npx playwright test tests/e2e/budget-request-workflow.spec.ts` (CI runs it too).

### Task 10: Delete Pinia stores + sweep
- **ACTION**: `grep` for `useBudgetRequestStore` / `useFileStore` / `stores/budgetRequests` / `stores/files`; once only the store files themselves match, delete both.
- **VALIDATE**: typecheck + build (no dangling imports) + full PHP Unit subset (Services/Api/Dtos) stays green (backend untouched).

---

## Testing Strategy

### Unit / Type
- `npm run typecheck` (vue-tsc) — zero errors after each refactor.
- Backend unchanged → existing `BudgetRequestServiceTest`/`BudgetRequestDtoTest` must stay green (do not edit).

### E2E (Playwright, SPA)
| Test | Flow | Assert |
|---|---|---|
| create→submit | create with item → save&submit | row status `รออนุมัติ` |
| approve | admin approves pending | status `อนุมัติแล้ว` |
| reject | admin rejects with note | status `ปฏิเสธ` + reason visible |
| file upload (opt) | upload pdf on a draft | file appears in list |

### Edge Cases Checklist
- [ ] reject with empty note → blocked client-side
- [ ] non-admin cannot see approve/reject buttons
- [ ] edit blocked for `pending`/`approved`/`rejected`
- [ ] empty list → empty state, no crash
- [ ] create with zero items → validation error (server requires ≥1 item)

---

## Validation Commands
```bash
cd frontend && npm run typecheck && npm run build
# PHP (backend untouched — confirm no regression in CI subset):
php vendor/phpunit/phpunit/phpunit tests/Unit/Services tests/Unit/Api tests/Unit/Dtos
# E2E (CI also runs): php -S :8000 + vite :5174 (VITE_API_URL=http://127.0.0.1:8000) + mysqld
$env:BASE_URL='http://localhost:5174'; $env:API_URL='http://127.0.0.1:8000'
npx playwright test tests/e2e/budget-request-workflow.spec.ts --workers=1
```

## Acceptance Criteria
- [ ] `queries/useBudgetRequests.ts` + `queries/useRequestFiles.ts` created (TanStack)
- [ ] All 4 request pages + FileUploader consume TanStack; no Pinia server-state
- [ ] `stores/budgetRequests.ts` + `stores/files.ts` deleted; no dangling imports
- [ ] vue-tsc + vite build green; PHP Unit subset green (no backend change)
- [ ] SPA workflow e2e green (create→submit→approve + reject-with-note)
- [ ] CI 3 jobs green → squash merge

## Risks
| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Backend blocks self-approval → admin-only e2e can't approve own request | Medium | Med | Task 8 verifies; seed a requester-owned pending request if so |
| FileUploader FormData breaks under TanStack mutation | Low | Med | `uploadRequestFile` already encapsulates FormData+apiFetch; composable only calls it |
| `queryKey` with a reactive filters object doesn't refetch | Low | Med | put the ref/getter directly in the key array (TanStack deep-tracks) — mirror exemplar |
| Old PHP-targeting e2e specs now fail in CI | Low | Low | leave them (PHP view still exists until Phase 6); new spec is separate |
| dump seed column-order mismatch | Low | Med | copy exact column order from `CREATE TABLE budget_requests`/`_items` in the dump |

## Notes
- Pure frontend migration — the lowest-risk core of Phase 4 is that the new
  composables call the **same** `api/*` functions the Pinia stores already used,
  so behavior parity is the bar.
- Keep PrimeVue Toast/ConfirmDialog usage consistent with Phase 2 pages.
