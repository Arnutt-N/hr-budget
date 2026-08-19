# Confirm before deleting a request attachment

Written against: f6badd4

## Evidence chain

- Surface: request-attachment list in `src/components/FileUploader.vue` (rendered by
  `RequestEditPage` and `RequestDetailPage`)
- Problem: `handleDelete` (`FileUploader.vue:68-75`) calls `deleteMut.mutateAsync(...)`
  immediately from the ✕ button (`:124-131`) — no confirmation for a destructive action.
- Design evidence: direct contradiction within the product — every other destructive action goes
  through `confirm.require` (23 calls / 19 files), including vault file delete
  (`DocumentVaultPage.vue:370`, `confirmDeleteFile`). Canonical delete-confirm block:
  `src/pages/FiscalYearListPage.vue:102-120`. Vercel web-interface-guidelines: "Destructive
  actions need confirmation modal or undo window — never immediate."
- Owner: `src/components/FileUploader.vue` (single consumer pair: RequestEdit/RequestDetail).
- Scope and affected surfaces: attachment rows on the request edit + detail pages. The global
  `<ConfirmDialog />` already exists (`src/layouts/AppLayout.vue:68`) — no new mount needed.
- Uncertainty: none.

## Design decision

Wrap the attachment delete in the same `confirm.require` pattern the other 19 files use, with
the same Thai labels and danger styling. One component, no new primitives.

If `c4-extract-shared-ui-patterns.md` has already landed, use its `useDeleteConfirm` composable
instead of the inline `confirm.require` block below — same behavior, shared owner.

## Reuse

- Pattern: `confirm.require` delete block — exemplar `src/pages/FiscalYearListPage.vue:102-120`
- Service: `useConfirm` from `primevue/useconfirm`; global `<ConfirmDialog />` at
  `src/layouts/AppLayout.vue:68`
- Toasts: existing `toast.add` Thai pattern (`…สำเร็จ` life 3000 / `…ไม่สำเร็จ` life 5000)

## Changes

1. `src/components/FileUploader.vue`
   - Change:
     - Import `useConfirm` (`primevue/useconfirm`) and `useToast` if not already imported.
     - Replace `handleDelete(fileId)` body so it confirms first:
       ```ts
       function handleDelete(fileId: number, fileName: string): void {
         confirm.require({
           message: `ยืนยันลบไฟล์ "${fileName}"?`,
           header: 'ยืนยันการลบ',
           icon: 'pi pi-exclamation-triangle',
           acceptLabel: 'ลบ',
           rejectLabel: 'ยกเลิก',
           acceptClass: 'p-button-danger',
           accept: async () => {
             try {
               await deleteMut.mutateAsync({ requestId: props.requestId, fileId })
               toast.add({ severity: 'success', summary: 'ลบไฟล์สำเร็จ', life: 3000 })
               emit('removed')
             } catch (e) {
               const message = e instanceof Error ? e.message : 'ลบไฟล์ไม่สำเร็จ'
               toast.add({ severity: 'error', summary: 'ลบไฟล์ไม่สำเร็จ', detail: message, life: 5000 })
             }
           },
         })
       }
       ```
     - Update the delete button (`:124-131`) to `@click="handleDelete(f.id, f.original_name)"`.
     - Keep the existing `uploadError` inline `<p>` (`:110`) for non-delete upload errors only;
       delete errors now go through the toast like every other CRUD page.
   - Preserve: `emit('removed')` after success, disabled gating (`v-if="!disabled"`), upload flow.
   - Verify: clicking ✕ opens the ConfirmDialog; "ยกเลิก" leaves the file; "ลบ" removes it and
     toasts success.

## Scope

- Inherit: `RequestEditPage`, `RequestDetailPage` (both mount FileUploader).
- Verify: attachment delete still blocked when `disabled` prop is true; `removed` refetch flow.
- Exclude: ApprovalChainPanel approve/reject (not destructive in the same sense; separate
  decision); DocumentVault file delete (already confirms).

## Validation

- Product: on a draft request with an attachment, delete → confirm dialog → file gone, success
  toast; cancel → file stays, no network call (check DevTools Network).
- Interface: dialog is modal + focus-trapped (PrimeVue default) at desktop and mobile widths.
- System: no second ConfirmDialog mount added; no new dependency.
- Repository: `grep -n "confirm.require" frontend/src/components/FileUploader.vue` → 1 match;
  `cd frontend && npm run verify` → passes.

## Stop conditions

- Stop if product wants attachments recoverable (soft-delete/undo window) — that is a backend
  change, out of this plan's scope.

## Design documentation

- After acceptance: none (behavior matches the already-documented destructive-action pattern).
