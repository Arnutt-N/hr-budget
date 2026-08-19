# Fix error-text contrast on dark surfaces (text-red-600 → text-red-400)

Written against: f6badd4

## Evidence chain

- Surface: field-level error text in dialog forms + login, all rendered inside the static dark
  theme (`<html class="app-dark">`, `index.html:2`; body `#0f172a`, cards `#1e293b`,
  `style.css:6-11`, `tailwind.config.js`)
- Problem: `class="text-red-600"` (#dc2626) on `#0f172a` page bg ≈ **3.7:1**, on `#1e293b` card
  bg ≈ **3.0:1** — both below the WCAG 2.1 AA 4.5:1 minimum for small text. 35 occurrences in
  13 files, all of them the `<small class="text-red-600" role="alert">` field-error pattern.
- Design evidence: project `accessibility_guidelines` skill — "Follow WCAG 2.1 Level AA as the
  minimum standard", contrast table (normal text 4.5:1). Internal precedent: `text-red-400`
  (#f87171 ≈ 6.5:1 on both dark surfaces — passes) is already the error red in
  `src/components/FileUploader.vue:110` and `src/pages/RequestListPage.vue:101`;
  `style.css:65-68` `.badge-red` also uses `#f87171`.
- Owner: Tailwind red scale applied per-template; no single CSS owner.
- Scope and affected surfaces (all verified; each line is a `<small ... role="alert">` in a dark
  dialog/page):
  - `src/components/CategoryItemsPanel.vue:188`
  - `src/pages/CategoryListPage.vue:167,173`
  - `src/pages/DivisionListPage.vue:180,186`
  - `src/pages/DocumentVaultPage.vue:385`
  - `src/pages/FiscalYearListPage.vue:219,225,231`
  - `src/pages/LoginPage.vue:88,102`
  - `src/pages/OrganizationListPage.vue:183,189`
  - `src/pages/PlanListPage.vue:196,202,227`
  - `src/pages/PositionListPage.vue:395,414,428,440,452,457`
  - `src/pages/SalaryScaleListPage.vue:169,175,182,187,194`
  - `src/pages/TargetListPage.vue:256,271`
  - `src/pages/TargetTypeListPage.vue:168,174`
  - `src/pages/UserListPage.vue:200,215,221,227`
- Uncertainty: ratios were hand-computed (WCAG formula); validate one swapped instance with a
  contrast checker (e.g. WebAIM) before sweeping.

## Design decision

Replace `text-red-600` with `text-red-400` for inline field-error text. This reuses the red
already proven in this codebase on the same surfaces, instead of inventing a new token. The
`:invalid` border treatment and `role="alert"` behavior stay untouched.

## Reuse

- Token: Tailwind `red-400` (#f87171)
- Exemplar: `src/components/FileUploader.vue:110`, `src/pages/RequestListPage.vue:101`

## Changes

1. Each file listed under "Scope and affected surfaces"
   - Change: `text-red-600` → `text-red-400` on the cited error-`<small>` lines only.
   - Preserve: `role="alert"`, `v-if="errors.*"` gating, `:invalid` props, all layout classes.
   - Verify: per file, the swapped element still renders only when its field error is set.
2. Whole-surface check
   - Change: none — verification step only.
   - Verify: `grep -rn "text-red-600" frontend/src` → **0 matches**.

## Scope

- Inherit: all 13 listed files.
- Verify: PositionListPage sub-dialogs are being rebuilt under `c3-unify-form-validation.md`;
  if that plan lands first, its new error text must also use `text-red-400` (no double work —
  this plan's swap there is harmless either way).
- Exclude: `text-red-400` (already passing), `.badge-red` in `style.css` (passes on its tinted
  bg), `severity="danger"` PrimeVue buttons (themed by the Aura preset, out of scope), toast/Message
  severities. Do not introduce a new semantic color token in this plan.

## Validation

- Product: submit an invalid form (e.g. Fiscal Year create with empty year) — error text is the
  brighter red and remains next to its field.
- Interface: check a dialog form (`/fiscal-years`) and the standalone `/login` page at 100% and
  200% zoom.
- System: single red for inline errors app-wide (no `text-red-500`/`600` stragglers in new code).
- Repository: `grep -rn "text-red-600" frontend/src` → 0 matches;
  `cd frontend && npm run verify` → passes.

## Stop conditions

- Stop if any cited line turns out to render on a **light** background (re-check contrast there
  instead of swapping blindly — none were found at audit time, all are dark dialogs/pages).

## Design documentation

- After acceptance: record "inline error text = `text-red-400` on dark surfaces" in the project
  `ui_design_system` skill when that doc is next refreshed (currently stale; do not edit as part
  of this plan).
