# Extract shared UI primitives: PageHeader, QueryErrorState, ListEmptyState, useDeleteConfirm

Written against: f6badd4

## Evidence chain

- Surface: all admin/list pages (PrimeVue dialect)
- Problem: the same four blocks are copy-pasted across the codebase (all counts verified by
  grep + spot-reads):
  1. Page header — `mb-6 flex items-center justify-between` + `h1.text-2xl.font-bold.text-white`
     + primary action — **23 files** (e.g. `FiscalYearListPage.vue:150-153`,
     `PlanListPage.vue:139-142`, `PositionListPage.vue:296-299`).
  2. Load-error banner — `<Message v-if="isError" severity="error" :closable="false">
     {{ error?.message ?? 'ไม่สามารถโหลดข้อมูลได้' }}</Message>` — **19 files**
     (e.g. `FiscalYearListPage.vue:155-157`, `RoleListPage.vue:184-186`).
  3. DataTable empty slot — `<template #empty><p class="py-4 text-center text-dark-muted">…`
     — **21 files** (e.g. `PlanListPage.vue:157-159`).
  4. Delete confirm — the ~15-line `confirm.require` block with identical Thai labels
     (`header: 'ยืนยันการลบ'`, `icon: 'pi pi-exclamation-triangle'`, `acceptLabel: 'ลบ'`,
     `rejectLabel: 'ยกเลิก'`, `acceptClass: 'p-button-danger'`) — **19 files, 23 calls**
     (canonical: `FiscalYearListPage.vue:102-120` vs `PlanListPage.vue:116-134` vs
     `PositionListPage.vue:151-169`).
- Design evidence: internal contract — these copies are byte-similar; any future change
  (e.g. an error-text color fix) currently needs 19+ edits. The audit's a11y plans (a1/a2)
  become one-place fixes once these are shared.
- Owner: new files under `src/components/` + `src/composables/`; consumers are the listed pages.
- Scope and affected surfaces: every admin list page + any page using the four patterns.
- Uncertainty: exact per-page empty-state copy varies (Thai message per entity) — primitives
  must take the message as a prop, not hardcode it.

## Design decision

Create four tiny, explicit primitives that capture the copied markup/behavior verbatim, then
sweep consumers onto them. Deliberately **no** FormField wrapper and **no** toast helper in this
plan: label/error wiring is owned by `a2-form-label-wiring.md` (a FormField extraction decided
after that lands), and per-entity Thai toast strings make a toast wrapper an over-abstraction.

Rejected alternative: a full `AppDataTable` wrapper forwarding all PrimeVue slots — slot
forwarding through wrappers is fragile; the only shared table need today is the empty state,
which `ListEmptyState` covers.

## Reuse

- Markup tokens: `text-dark-muted`, `border-dark-border`, etc. — same classes as the copies.
- PrimeVue `Message`, `DataTable` `#empty` slot, `useConfirm` — unchanged APIs.
- Exemplars: `FiscalYearListPage.vue:150-157` (header + error), `PlanListPage.vue:157-159`
  (empty), `FiscalYearListPage.vue:102-120` (delete confirm).

## Changes

1. `src/components/PageHeader.vue` (new)
   - Change:
     ```vue
     <script setup lang="ts">
     defineProps<{ title: string }>()
     </script>

     <template>
       <div class="mb-6 flex items-center justify-between">
         <h1 class="text-2xl font-bold text-white">{{ title }}</h1>
         <slot />
       </div>
     </template>
     ```
   - Preserve: exact classes from the copied pattern; actions go in the default slot.
   - Verify: a pilot page renders identically to before.
2. `src/components/QueryErrorState.vue` (new)
   - Change: props `{ error?: unknown }`; render
     ```vue
     <Message severity="error" :closable="false">{{ message }}</Message>
     ```
     where `message = props.error instanceof Error ? props.error.message : 'ไม่สามารถโหลดข้อมูลได้'`.
     Consumers use `<QueryErrorState v-if="isError" :error="error" />`.
   - Preserve: severity, non-closable, Thai fallback string — verbatim from the 19 copies.
   - Verify: forcing a query error on the pilot page shows the banner as before.
3. `src/components/ListEmptyState.vue` (new)
   - Change: props `{ message: string }`; render
     ```vue
     <p class="py-4 text-center text-dark-muted">{{ message }}</p>
     ```
     plus a default slot after the `<p>` for an optional CTA. Consumers:
     `<template #empty><ListEmptyState message="ยังไม่มีข้อมูลปีงบประมาณ" /></template>`.
   - Preserve: per-page Thai messages (passed in, not hardcoded).
   - Verify: empty table state looks identical.
4. `src/composables/useDeleteConfirm.ts` (new)
   - Change:
     ```ts
     import { useConfirm } from 'primevue/useconfirm'

     export function useDeleteConfirm() {
       const confirm = useConfirm()
       return function confirmDelete(options: {
         message: string
         accept: () => void | Promise<void>
       }): void {
         confirm.require({
           header: 'ยืนยันการลบ',
           icon: 'pi pi-exclamation-triangle',
           acceptLabel: 'ลบ',
           rejectLabel: 'ยกเลิก',
           acceptClass: 'p-button-danger',
           ...options,
         })
       }
     }
     ```
   - Preserve: per-call `message` (contains the entity name) and `accept` payload incl. its
     toasts; non-delete confirms (e.g. `confirmSetCurrent`, `FiscalYearListPage.vue:122-139`)
     keep calling `confirm.require` directly.
   - Verify: delete flow on the pilot page behaves exactly as before.
5. Consumer sweep (23 pages; start with `FiscalYearListPage.vue` as pilot, then the rest)
   - Change: replace each copied block with the primitive; delete now-unused imports
     (`Message`, `useConfirm`) where the page no longer needs them.
   - Preserve: all page-specific props, messages, handlers, and table columns byte-for-byte.
   - Verify: `grep -c "ไม่สามารถโหลดข้อมูลได้" frontend/src` drops to 1 (inside
     QueryErrorState); `grep -c "ยืนยันการลบ" frontend/src` drops to 1 (+ any page whose
     non-delete confirm shares the header string — check before/after counts).

## Scope

- Inherit: all 23 header pages, 19 error-banner files, 21 empty-slot files, 19 delete-confirm
  files (heavy overlap — most admin pages get all four).
- Verify: pages with **richer** empty states keep them — `RequestListPage.vue:148-156` (CTA
  link) and `DashboardPage.vue:78-84` (icon + message) are exclusions, not defects.
- Exclude: FormField/form-field wrapper (see `a2-form-label-wiring.md` boundary); toast helper;
  `AppDataTable` slot-forwarding wrapper (rejected above); any visual restyling — this plan is
  pure extraction, output must render pixel-identical.

## Validation

- Product: click through 3 pilot-then-swept admin pages (`/fiscal-years`, `/plans`, `/roles`) —
  header, error banner (throttle network / kill API), empty table, and delete confirm all
  behave exactly as before.
- Interface: mobile + desktop widths; long page titles wrap the same (same classes).
- System: primitives live in `src/components/` / `src/composables/` and are the only owners of
  their markup; no page re-introduces a copy.
- Repository: `cd frontend && npm run verify` → passes; the two grep counts above drop as
  stated; `grep -rn "mb-6 flex items-center justify-between" frontend/src/pages` → only pages
  with deliberately custom headers remain (list them in the PR description).

## Stop conditions

- Stop the sweep (primitives still land) if a page's copy differs meaningfully (extra buttons in
  the header row, non-standard error content) — leave that page inline and note it in the PR;
  do not contort the primitive with extra props for one-off cases.

## Design documentation

- After acceptance: add the four primitives to the "Components" section of the project
  `ui_design_system` skill when that stale doc is next refreshed (not part of this plan).
