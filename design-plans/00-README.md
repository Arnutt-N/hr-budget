# Design plans — Frontend UI audit (Critical/High findings)

Source: full UI/UX + accessibility + performance audit of `frontend/src` (Vue 3 + PrimeVue 4 +
Tailwind 3 SPA), run per `.claude/skill-collections-20260815.md` routing (ui-skills vertical:
`improve-ui` + `fixing-accessibility` + `web-design-guidelines`).

Written against: `f6badd4` (clean tree). Standards invoked: project `accessibility_guidelines`
skill (WCAG 2.1 AA minimum), Vercel web-interface-guidelines, internal consistency contracts.

Each plan is self-contained — an executor needs no context from the audit conversation.

## Plans

| File | Finding | Change |
|---|---|---|
| `p2-favicon-and-title.md` | P2 | Add favicon + Thai `<title>` |
| `a1-error-text-contrast.md` | A1 | Swap failing `text-red-600` → passing red on dark (35 spots) |
| `c2-confirm-attachment-delete.md` | C2 | `confirm.require` before attachment delete |
| `a6-chart-reduced-motion.md` | A6 | Honor `prefers-reduced-motion` in 2 remaining charts |
| `a3-a4-icon-control-names-keyboard.md` | A3+A4 | `aria-label` on icon-only controls; keyboard-reachable file picker |
| `a5-disclosure-keyboard-semantics.md` | A5 | `aria-expanded`/Escape/focus-return for bell dropdown + mobile sidebar |
| `c3-unify-form-validation.md` | C3 | RoleListPage + PositionListPage sub-dialogs → zod + per-field errors |
| `c4-extract-shared-ui-patterns.md` | C4 | `PageHeader`, `QueryErrorState`, `ListEmptyState`, `useDeleteConfirm` |
| `a2-form-label-wiring.md` | A2 | Wire every label (`for`/`id`/`input-id`/`aria-labelledby`) + `aria-describedby` errors |
| `c1-unify-list-table-pattern.md` | C1 | Migrate hand-rolled list tables to PrimeVue DataTable; one page size |

## Execution waves (respect dependencies)

- **Wave 1 — quick, independent:** `p2`, `a1`, `c2`, `a6`, `a3-a4`
- **Wave 2 — medium, independent:** `a5`, `c3`
- **Wave 3 — structural:** `c4` (creates the shared primitives)
- **Wave 4 — largest, last:** `a2` (sweep after c3 owns the PositionListPage sub-dialogs),
  then `c1` (adopts `PageHeader`/`QueryErrorState`/`ListEmptyState` from c4 on the migrated pages)

Boundary notes:

- `c3` owns the PositionListPage **versions/allowances sub-dialogs** (labels + validation there);
  `a2` must exclude those two forms.
- `c4` deliberately **excludes** a FormField wrapper — label/error wiring rules live in `a2`;
  revisit a FormField primitive only after `a2` lands.
- `c2`: if `c4` has already landed, use its `useDeleteConfirm` instead of the inline pattern.
- `c1`: if `c4` has landed, the migrated pages use its primitives instead of inline markup.

## Verification gate for every plan

`cd frontend && npm run verify` (typecheck + build) must pass; run the plan's own grep checks.
Do not commit from within a plan — leave the working tree for review.
