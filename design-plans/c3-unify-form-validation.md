# Unify form validation: RoleListPage + PositionListPage sub-dialogs → zod + per-field errors

Written against: f6badd4

## Evidence chain

- Surface: admin dialogs on `/roles` and `/positions`
- Problems (verified):
  1. `src/pages/RoleListPage.vue:44-99` — hand-rolled validation: plain refs, a regex constant
     (`CODE_RE`, `:83`), and a single `formError` string shown in one `<Message>` box
     (`:268`); errors are not attached to fields.
  2. `src/pages/PositionListPage.vue` — the two sub-dialogs are unvalidated plain-`ref` forms
     whose inputs use **`placeholder` instead of labels**: versions form (`:509-551`, script
     refs at `:177-188`) and allowances form (`:578-602`, script refs at `:241-245`); gating is
     only `:disabled` on the submit buttons (`:547`, `:598`).
  3. Direct contradiction: 12 sibling pages + `CategoryItemsPanel` use vee-validate + zod with
     per-field `<small class="text-red-600" role="alert">` errors — exemplar
     `src/pages/FiscalYearListPage.vue:41-54` (schema) and `:208-237` (template wiring).
- Design evidence: internal contract — the zod + `useForm` + per-field-error pattern is the
  product's established form dialect; project's Thai-validation-message convention (AGENTS.md:
  Thai for user-facing errors); `fixing-accessibility` rule 5 (errors linked to fields).
- Owner: each page's form script + dialog template.
- Scope and affected surfaces: RoleListPage create/edit dialog; PositionListPage "เวอร์ชัน" and
  "สิทธิ์เงินเพิ่ม" sub-dialogs. **This plan owns those two PositionListPage sub-dialogs —
  including their missing labels.** (`a2-form-label-wiring.md` excludes them.)
- Uncertainty: exact zod field rules for version/allowance payloads — mirror the main Position
  dialog's existing schema (`PositionListPage.vue:53-67`) and the API DTO expectations; where
  the API is lenient, keep rules minimal (required + numeric bounds only).

## Design decision

Migrate the three stragglers to the codebase's own vee-validate + zod dialect: typed schema,
`useForm`, per-field Thai error messages, and real labels. Keep behavior (system-role view-only
gate, auto-close of the previous version, submit gating) identical.

## Reuse

- Exemplar schema/template: `src/pages/FiscalYearListPage.vue:41-54,208-237`
- Existing Position schema for shared fields: `src/pages/PositionListPage.vue:53-67`
- Error element: `<small v-if="errors.X" class="text-red-600" role="alert">{{ errors.X }}</small>`
  (if `a1-error-text-contrast.md` has landed, use `text-red-400` instead)
- Libraries already installed: `vee-validate`, `@vee-validate/zod`, `zod`

## Changes

1. `src/pages/RoleListPage.vue`
   - Change:
     - Replace refs `formCode/formNameTh/formNameEn/formDescription` validation with
       `useForm({ validationSchema: toTypedSchema(z.object({ ... })) })`:
       ```ts
       const schema = toTypedSchema(
         z.object({
           code: z.string().regex(/^[a-z][a-z0-9_]{1,49}$/,
             'รหัสบทบาทต้องเป็น a-z, 0-9, _ ขึ้นต้นด้วยตัวอักษร (≤50)'),
           name_th: z.string().min(1, 'กรุณาระบุชื่อบทบาท'),
           name_en: z.string().optional(),
           description: z.string().optional(),
         }),
       )
       ```
       (Reuse the existing `CODE_RE` constant inside `.regex()` instead of duplicating the
       expression; `code` is only validated on create — keep the current
       "code immutable when editing" behavior by disabling the field, as today.)
     - Template: per-field `<small role="alert">` under code/name inputs; keep the dialog's
       `<Message>` box **only** for API-level submit errors (mutation catch), not validation.
     - `selectedPerms` checkbox set stays a plain ref (no validation change).
   - Preserve: system-role view-only early-return (`:87-91`), permission grouping (`:36-42`),
     mutation payloads (`:100-109`), Thai messages verbatim.
   - Verify: empty name → inline Thai error under the field, form does not submit; bad code on
     create → inline error with the existing message text; system role dialog unchanged.
2. `src/pages/PositionListPage.vue` — versions sub-dialog (`:509-551`)
   - Change: wrap the grid inputs in the standard field pattern
     (`<div class="flex flex-col gap-1"><label …>…</label><control/><small role="alert"/></div>`)
     with **visible labels** replacing placeholder-only usage (placeholders may stay as
     examples, never as the only name); add a zod schema for `versionForm`:
     `effective_from` (required date), `base_salary` (number ≥ 0), `level_code`, `organization_id`,
     `occupancy`, `months_counted` (int 1–12), `salary_basis`, `approval_status`, `order_doc_no`
     (optional) — matching the main dialog's rules for the same fields.
   - Preserve: the "ปิดเวอร์ชันเดิมอัตโนมัติ" note (`:510`), the add-version mutation, and the
     submit-disabled-until-required behavior (`:547`) — implement it via the form's
     validity/meta instead of hand-picked conditions if trivial, otherwise keep as-is.
   - Verify: adding a version with no `effective_from` shows an inline error instead of only a
     dead disabled button; successful add behaves exactly as before.
3. `src/pages/PositionListPage.vue` — allowances sub-dialog (`:578-602`)
   - Change: same treatment — labels + zod schema for `allowanceForm`
     (`allowance_type_id` required, `effective_from` required date, `doc_no` optional) and
     per-field errors.
   - Preserve: `:disabled="!allowanceForm.allowance_type_id || !allowanceForm.effective_from"`
     semantics (`:598`), delete-confirm flow (`confirmDeleteAllowance`, `:573`).
   - Verify: inline errors on invalid submit; unchanged happy path.

## Scope

- Inherit: `/roles`, `/positions` dialogs.
- Verify: the main Position create/edit dialog (`:389-473`) is **not** part of this plan except
  its label wiring, which `a2-form-label-wiring.md` owns; don't refactor it here.
- Exclude: the Tailwind-native workflow/personnel pages (RequestCreatePage, RequestEditPage,
  DisbursementWizardPage, ItemEditor, ApprovalChainPanel, Phase 9 personnel pages) — they are a
  separate, larger migration decision; do not touch them in this plan.

## Validation

- Product: create/edit a role; add a position version; add an allowance — invalid input produces
  per-field Thai errors, valid input performs the same API calls as before.
- Interface: dialogs at mobile width (grid columns collapse) — labels remain visible above
  fields; errors announced via existing `role="alert"`.
- System: `grep -n "toTypedSchema" frontend/src/pages/RoleListPage.vue frontend/src/pages/PositionListPage.vue`
  → present; no new dependencies.
- Repository: `cd frontend && npm run verify` → passes.

## Stop conditions

- Stop if the versions/allowances API rejects a rule mirrored from the main schema (e.g. a field
  the backend treats as optional) — relax that rule to match the backend; do not tighten beyond
  existing server behavior.

## Design documentation

- After acceptance: none (converges on the already-dominant pattern).
