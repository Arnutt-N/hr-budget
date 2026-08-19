# Accessible names for icon-only controls + keyboard-reachable file picker

Written against: f6badd4

## Evidence chain

- Surface: line-item editor, attachment uploader, document vault table
- Problems (verified):
  1. `src/components/ItemEditor.vue:100-107` — row-delete button renders only the glyph `✕`
     with `title="ลบรายการ"`; `title` is not a reliable accessible name.
  2. `src/components/FileUploader.vue:124-131` — attachment-delete button renders only `&#10005;`
     with `title="ลบไฟล์"`; same defect.
  3. `src/pages/DocumentVaultPage.vue:357-362` — download `<a>` renders only
     `<i class="pi pi-download"></i>`, no `aria-label`, no `title` — while its sibling delete
     button in the same cell **has** `aria-label="ลบไฟล์"` (`:363-371`). Direct contradiction.
  4. `src/components/FileUploader.vue:103-106` — the file picker is a `<label>` wrapping
     `<input type="file" class="hidden">`; `display:none` removes the input from the Tab order
     and the label is not a focusable control, so keyboard users cannot open the picker at all.
- Design evidence: project `accessibility_guidelines` skill — WCAG 2.1 AA minimum, "icon-only
  buttons need an accessible name", "all interactive elements reachable by Tab";
  `fixing-accessibility` rules 1-2 (accessible names, keyboard access — both rated critical).
- Owner: the three cited components.
- Scope and affected surfaces: ItemEditor (used by RequestCreatePage + RequestEditPage),
  FileUploader (RequestEditPage + RequestDetailPage), DocumentVaultPage (`/vault`).
- Uncertainty: Lucide icons elsewhere render decorative SVG inside text-labelled buttons/links
  — no action needed there; confirm Lucide's default `aria-hidden` while touching these files.

## Design decision

Give every icon-only control an explicit Thai `aria-label` (keeping `title` as the sighted
tooltip), and rebuild the file-picker trigger as a real `<button>` that forwards to the hidden
input. Minimal, targeted edits — no library migration, no restyling.

## Reuse

- Naming exemplar: `DocumentVaultPage.vue:369` (`aria-label="ลบไฟล์"`) and
  `NotificationBell.vue:67` (`aria-label="การแจ้งเตือน"`)
- Button styling: keep the existing Tailwind classes on each control.

## Changes

1. `src/components/ItemEditor.vue:100-107`
   - Change: add `aria-label="ลบรายการ"` to the ✕ button.
   - Preserve: `title`, `removeItem(index)` behavior, classes.
   - Verify: screen reader / DevTools accessibility tree shows name "ลบรายการ".
2. `src/components/FileUploader.vue:124-131`
   - Change: add `aria-label="ลบไฟล์"` to the ✕ button.
   - Preserve: `title`, delete flow (which `c2-confirm-attachment-delete.md` may have wrapped in
     a confirm dialog — that plan and this one touch the same button; apply both edits).
   - Verify: accessible name "ลบไฟล์".
3. `src/pages/DocumentVaultPage.vue:357-362`
   - Change: add `aria-label="ดาวน์โหลดไฟล์"` and `title="ดาวน์โหลด"` to the download `<a>`.
   - Preserve: href, `p-button p-button-text p-button-sm p-button-secondary` classes.
   - Verify: accessible name present; matches sibling trash button's pattern.
4. `src/components/FileUploader.vue:101-107` (picker trigger)
   - Change: replace the label-wrapped hidden input with a button + ref pattern:
     ```html
     <input
       ref="fileInput"
       type="file"
       class="hidden"
       multiple
       :accept="ALLOWED_EXTENSIONS.map(e => '.' + e).join(',')"
       @change="onFileInput"
     />
     <button
       type="button"
       class="mt-2 inline-block rounded bg-primary-600 px-3 py-1.5 text-xs text-white hover:bg-primary-500"
       @click="fileInput?.click()"
     >
       เลือกไฟล์
     </button>
     ```
     with `const fileInput = ref<HTMLInputElement | null>(null)` in `<script setup>`.
   - Preserve: `onFileInput` handler (including its `input.value = ''` reset), `multiple`,
     accept list, disabled state (the whole block sits under the `v-else` of the disabled check —
     keep it there).
   - Verify: Tab reaches the "เลือกไฟล์" button and Enter/Space opens the OS file dialog;
     mouse click path unchanged.

## Scope

- Inherit: RequestCreatePage, RequestEditPage, RequestDetailPage, DocumentVaultPage.
- Verify: the drop-zone drag/drop path (`FileUploader.vue:91-97`) untouched; upload accept
  filtering unchanged.
- Exclude: adding `aria-hidden` audits to every Lucide icon (verify-only here); the
  DisbursementWizard icon button flagged in the audit already has an `aria-label`.

## Validation

- Product: keyboard-only walk — Tab to item-row ✕, attachment ✕, vault download, and
  "เลือกไฟล์"; each announces its Thai name; Enter activates.
- Interface: no visual change at any viewport (labels are attributes, not layout).
- System: `grep -n 'title="ลบ' frontend/src/components/*.vue` → each hit also carries
  `aria-label`; no new dependencies.
- Repository: `cd frontend && npm run verify` → passes.

## Stop conditions

- Stop if a plan conflict arises on the FileUploader delete button (`c2` edits its `@click`);
  resolve by applying both changes to the same element — they are orthogonal.

## Design documentation

- After acceptance: none.
