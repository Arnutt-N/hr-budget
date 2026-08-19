# Unify list-table pattern: migrate hand-rolled tables to PrimeVue DataTable, one page size

Written against: f6badd4

## Evidence chain

- Surface: the two main workflow list pages vs the admin list pages
- Problem (verified): two UI dialects for the same "filterable, paginated list" task:
  - Admin pages use PrimeVue `DataTable` + built-in paginator — exemplar
    `FiscalYearListPage.vue:159-206` — but page size differs arbitrarily: 10 rows
    (`FiscalYearListPage.vue:164`, `PlanListPage.vue:153`) vs 15 (`RoleListPage.vue:193`,
    `PositionListPage.vue:332`).
  - Workflow pages hand-roll everything: `RequestListPage.vue:91-177` (raw `<table>`, custom
    prev/next pagination buttons `:159-177`, plain-text loading `:106`, native filter controls
    per `:64-96`) and `DisbursementListPage.vue:90-206` (raw `<table>` `:145-174`, native
    `<select>` filters `:101-125`, plain `+ บันทึกการเบิกจ่าย` button `:91-97`, same custom
    pagination `:190-206`). Visual language, control chrome, and pagination UX differ from the
    admin surface for the same user task.
- Design evidence: internal contract — 21 files share the DataTable dialect; the header/CTA
  pattern (`Button icon="pi pi-plus"`) is standard on 23 pages; the two workflow pages are the
  only raw-table survivors. (Note: their server-side pagination itself is a *strength* to keep —
  `PER_PAGE = 20` + page params, `RequestListPage.vue:10`.)
- Owner: `RequestListPage.vue`, `DisbursementListPage.vue` (page-local query composables
  unchanged); admin rows-per-page constants.
- Scope and affected surfaces: `/requests`, `/disbursements`, `/roles`, `/positions`.
- Uncertainty: none material — PrimeVue DataTable supports lazy/server pagination natively;
  TanStack Query wiring (query keys, params, `keepPreviousData`-style behavior) must be
  preserved exactly.

## Design decision

Migrate the two workflow pages to the PrimeVue DataTable dialect with **lazy** (server-side)
pagination — `:lazy`, `:totalRecords`, `:rows="20"`, `@page` — keeping their existing query
logic and 20-per-page server contract. Standardize admin DataTables on **10 rows** (the
majority convention: FiscalYear/Plan). Replace native filter controls with the PrimeVue
equivalents used everywhere else. If `c4-extract-shared-ui-patterns.md` has landed, the migrated
pages adopt `PageHeader` / `QueryErrorState` / `ListEmptyState` instead of inline markup.

No backend changes; no query-key changes; no filter-semantics changes.

## Reuse

- DataTable exemplar: `src/pages/FiscalYearListPage.vue:159-206` (columns, `#empty`,
  `data-key`, card classes `overflow-hidden rounded-lg border border-dark-border shadow`)
- Filter exemplar (PrimeVue Select + InputText + search button):
  `src/pages/PositionListPage.vue:301-321`
- Status pill: existing `StatusBadge` component (keep inside a Column body template)
- Shared primitives from `c4` if available (PageHeader, QueryErrorState, ListEmptyState)
- Thai strings: keep every existing label/message verbatim.

## Changes

1. `src/pages/RoleListPage.vue:193` and `src/pages/PositionListPage.vue:332`
   - Change: `:rows="15"` → `:rows="10"`.
   - Preserve: everything else.
   - Verify: paginator shows 10/page.
2. `src/pages/RequestListPage.vue`
   - Change:
     - Header row → `PageHeader` (from c4) or keep inline markup + swap the create button to the
       standard `Button icon="pi pi-plus"` pattern.
     - Filter row (`:64-96`) → PrimeVue `Select` (status), `InputText` (search) with label
       wiring per `a2-form-label-wiring.md` rules, `Button` for submit.
     - Table (`:109-145`) → `DataTable` with `:value="requests"`, `:lazy="true"`,
       `:loading="query.isLoading.value"`, `paginator`, `:rows="20"`, `:totalRecords="total"`,
       `@page="goToPage($event.page + 1)"` (PrimeVue pages are 0-based; `goToPage` is 1-based),
       `:first="(currentPage - 1) * 20"`, `data-key="id"`, the standard card classes; columns
       for ชื่อคำขอ (router-link body), สถานะ (`StatusBadge` in body template), ยอดรวม
       (right-aligned body), ผู้สร้าง, วันที่, จัดการ ("ดู" link body).
     - `#empty` slot carries the existing CTA (`:148-156`: "ไม่มีคำของบประมาณ" + "สร้างคำขอใหม่").
     - Delete the custom prev/next block (`:159-177`) and the plain loading div (`:106`); the
       error banner (`:101-103`) becomes `QueryErrorState` if c4 landed, else stays as-is.
   - Preserve: `PER_PAGE = 20`, query keys/params, `formatAmount`/`formatDate`, router links,
     empty-state CTA, error message text.
   - Verify: page 2 fetch fires with the same API params as before; filter submit refetches page
     1; row links navigate; loading/empty/error states render in the DataTable idiom.
3. `src/pages/DisbursementListPage.vue`
   - Change: same migration — native `<select>` filters (`:101-125`) → PrimeVue `Select`
     (fiscal year + month options, same option shapes), "ค้นหา" `Button`; header button
     (`:91-97`) → standard `Button icon="pi pi-plus" label="บันทึกการเบิกจ่าย"`; table
     (`:145-174`) → lazy DataTable (`:rows="20"`, `@page` mapping as above); delete action stays
     a text `Button label="ลบ" text severity="danger" size="small"` calling the unchanged
     `confirmDelete` (`:164-170`); empty CTA (`:178-187`) into `#empty`.
   - Preserve: `MONTH_LABELS` mapping, `confirmDelete` flow, filter semantics, server contract.
   - Verify: same checks as RequestListPage plus the delete confirm still fires.

## Scope

- Inherit: `/requests`, `/disbursements` (all roles), `/roles`, `/positions`.
- Verify: pagination edge cases — page beyond last after a filter change (existing reset
  behavior must be kept); slow-network loading state; empty result with filters active.
- Exclude: admin-table **client-side** pagination (fine at master-data scale — recorded in the
  audit as a non-finding); adding sortable columns to the migrated tables (server sort is a
  backend decision — not this plan); DisbursementWizardPage / RequestCreatePage forms;
  `ItemEditor.vue`'s index-key `v-for` (`:57`) — a functional code-quality note, not a UI
  contract fix; track separately if desired.

## Validation

- Product: full click-through of `/requests` and `/disbursements` — list, filter, paginate to
  last page, open a row, delete a disbursement — identical outcomes to pre-migration.
- Interface: mobile width (table scrolls within its card — DataTable default + existing
  `overflow-x` behavior), desktop, and 200% zoom.
- System: zero raw `<table>` list surfaces left outside ItemEditor's line-item editor (a form
  grid, not a list):
  `grep -ln "<table" frontend/src/pages/*.vue` → no RequestList/DisbursementList hits.
- Repository: `cd frontend && npm run verify` → passes; run E2E if the runner is available
  (`npm run test:e2e` at repo root — needs the Laragon backend up; note result in the PR).

## Stop conditions

- Stop and fall back to the minimal variant **if lazy DataTable + the existing TanStack query
  composable cannot preserve the current request contract** (e.g. the composable can't expose a
  total count): keep the hand-rolled table but (a) add `scope="col"` to its `<th>`s and
  (b) replace the custom prev/next buttons with PrimeVue `Paginator`. Record the divergence and
  reason in the PR; do not force the migration.
- Stop if `c4` has not landed and its absence blocks nothing here — inline equivalents are
  acceptable for this plan; do not create the shared primitives inside this plan.

## Design documentation

- After acceptance: record "list surfaces = PrimeVue DataTable (lazy when server-paginated),
  10 rows admin / 20 rows workflow" in the project `ui_design_system` skill when that stale doc
  is next refreshed.
