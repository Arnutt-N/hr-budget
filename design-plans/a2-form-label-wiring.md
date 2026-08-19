# Wire every form label to its control + link field errors (aria-describedby)

Written against: f6badd4

## Evidence chain

- Surface: all form dialogs and native forms across the SPA
- Problem: of 87 `<label>` elements in `frontend/src`, only 42 (12 files) are programmatically
  associated via `for=`; `aria-describedby` and `aria-labelledby` have **0 occurrences**
  codebase-wide (verified by grep). Worst verified case: the PositionListPage create/edit dialog
  (`PositionListPage.vue:392-458`) — 10 labels, none wired (no `for`, no `id`), e.g.
  `<label class="text-sm font-medium text-dark-muted">เลขถือจ่าย</label><InputText v-model="payNo" …/>`
  at `:393-394`. Native-form pages skip `for` too (e.g. `RequestCreatePage.vue:106,115`).
- Design evidence: project `accessibility_guidelines` skill — "Always Use Labels" (explicit
  `for`/`id` or wrapped label), "errors must be linked to fields using aria-describedby";
  `fixing-accessibility` rules 1 + 5 (both critical/high); internal contract — the codebase
  already demonstrates the correct pattern in `FiscalYearListPage.vue:211-237`
  (`for` + `input-id`/`id`, Checkbox `input-id`) and `PlanListPage.vue:216-219` (Select).
- Owner: each page's dialog template; there is no shared FormField component (deliberately —
  see Scope/Exclude).
- Scope and affected surfaces: every vee-validate dialog (Login, FiscalYear, Plan, Organization,
  Division, Category, Target, TargetType, User, SalaryScale, Position main dialog, DocumentVault,
  CategoryItemsPanel) + native-form pages (RequestCreatePage, RequestEditPage, and any `<label>`
  lacking `for`). Find them mechanically:
  `grep -rn "<label" frontend/src --include=*.vue` — every hit without `for=` on the same
  element is in scope.
- Uncertainty: whether PrimeVue 4's `:invalid` prop already renders `aria-invalid` on the inner
  input — **check the rendered DOM once**; if it does, do not add a duplicate attribute.

## Design decision

Apply the codebase's own FiscalYear wiring rules everywhere, plus error linkage:

- `InputText` / `Textarea` / native `<input>`/`<select>` → `<label for="X">` + control `id="X"`.
- `InputNumber` / `Checkbox` / other PrimeVue input wrappers → `input-id="X"` (the prop PrimeVue
  forwards to the inner input).
- `Select` (renders a button, not a labellable input) → label gets `id="X-label"` and the
  Select gets `aria-labelledby="X-label"`.
- Every `<small class="text-red-600" role="alert">` error (or `text-red-400` if
  `a1-error-text-contrast.md` landed) gets `id="X-error"`, and its control gets
  `:aria-describedby="errors.X ? 'X-error' : undefined"`.
- Use stable, page-scoped ids (`fy-year` style, per the exemplar) — no `Math.random()` ids.

No visual change; attributes only.

## Reuse

- Exemplar: `src/pages/FiscalYearListPage.vue:208-237` (copy its id naming convention)
- Select labeling precedent: `src/pages/PlanListPage.vue:216-219`
- No new dependencies.

## Changes

1. PrimeVue-dialog pages (12 pages + `CategoryItemsPanel.vue`)
   - Change: per field, add the wiring above. Known gaps: `PositionListPage.vue:392-458` (all 10
     labels), plus any label the grep finds without `for=`. Fields already wired (most of
     FiscalYear, parts of Plan) need only the `aria-describedby` addition.
   - Preserve: zod schemas, `:invalid` props, error text, layout classes (`flex flex-col gap-1`
     etc.), Thai label strings.
   - Verify: clicking each label focuses/activates its control; DevTools accessibility tree
     shows the name and the description when an error is present.
2. Native-form pages (`RequestCreatePage.vue`, `RequestEditPage.vue`, others found by grep)
   - Change: same `for`/`id` wiring on native `<input>`/`<select>`/`<textarea>`; error
     `<p>`/`<div>` messages get ids + `aria-describedby`.
   - Preserve: the hand-rolled validation logic and Tailwind classes (migration of these pages
     to PrimeVue/zod is **not** this plan).
   - Verify: same label-click + accessibility-tree checks.

## Scope

- Inherit: every form surface listed above; screen-reader and keyboard users.
- Verify: LoginPage (standalone page, dark card) — it is a page too, don't skip it.
- Exclude — **do not touch, owned elsewhere**:
  - PositionListPage versions (`:509-551`) and allowances (`:578-602`) sub-dialogs → owned by
    `c3-unify-form-validation.md` (they currently have placeholders instead of labels and get
    labels + validation there).
  - `FileUploader.vue` picker trigger → owned by `a3-a4-icon-control-names-keyboard.md`.
  - Creating a shared `FormField` component → deferred; revisit only after this plan lands.
- Exclude behavior changes: no validation-rule edits, no message-text edits.

## Validation

- Product: tab through the Position create dialog and the request-create form with a screen
  reader (or accessibility inspector) — every field announces its Thai label; submitting invalid
  input links the error to the field.
- Interface: label click focuses the control (mouse path); no layout shift at any viewport.
- System: `grep -rn "<label" frontend/src --include=*.vue | grep -v "for="` → only wrapping
  labels (e.g. `FiscalYearListPage.vue:234` Checkbox wrapper) and c3-owned sub-dialog rows
  remain; `aria-describedby` occurrences > 0 and match error-element ids.
- Repository: `cd frontend && npm run verify` → passes; existing unit tests
  (`npm run test:unit` in `frontend/`) still pass.

## Stop conditions

- Stop if a PrimeVue component in use forwards neither `input-id` nor `aria-*` to a focusable
  element (check rendered DOM) — wrap that case in a `<label>` around the control instead and
  note the exception in the PR; do not invent a new prop convention.

## Design documentation

- After acceptance: record the four wiring rules (label/id, input-id, aria-labelledby for
  Select, aria-describedby for errors) as the form-field contract in the project
  `ui_design_system` skill when that stale doc is next refreshed.
