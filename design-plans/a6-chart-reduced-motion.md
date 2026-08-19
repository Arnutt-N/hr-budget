# Honor prefers-reduced-motion in the two remaining charts

Written against: f6badd4

## Evidence chain

- Surface: dashboard + budget-execution charts
- Problem: 3 of 5 chart components disable Chart.js entrance animation under
  `prefers-reduced-motion` via the shared composable — `RequestApprovalChart.vue:31`,
  `ComparisonChart.vue:28`, `ForecastChart.vue:46` — but `MonthlyExpenditureChart.vue` and
  `HorizontalBarChart.vue` never import it, so their bar animations always run. Direct internal
  contradiction for the same chart-entrance behavior.
- Design evidence: project `accessibility_guidelines` skill (WCAG 2.1 AA; motion
  consideration); Vercel web-interface-guidelines — "Honor `prefers-reduced-motion` (provide
  reduced variant or disable)"; the composable exists precisely for charts:
  `src/composables/usePrefersReducedMotion.ts:4` ("Reactive `prefers-reduced-motion` flag.
  Charts feed this into Chart.js").
- Owner: each chart component's `options`; the composable is the shared owner of the flag.
- Scope and affected surfaces:
  - `src/components/MonthlyExpenditureChart.vue` (DashboardPage) — `options` is a **static**
    object (`:40-73`)
  - `src/components/HorizontalBarChart.vue` (BudgetExecutionPage) — `options` is already a
    `computed` (`:44-75`)
- Uncertainty: none.

## Design decision

Bring the two stragglers onto the existing composable so all five charts behave identically:
when the user prefers reduced motion, Chart.js `animation` is disabled (bars render instantly).
No new dependencies, no visual change for default users.

## Reuse

- Composable: `src/composables/usePrefersReducedMotion.ts` (`usePrefersReducedMotion(): Ref<boolean>`)
- Exemplars: `src/components/ForecastChart.vue` (`:46`) and `src/components/ComparisonChart.vue`
  (`:28`) — copy exactly how they feed `reduced` into their Chart.js options.

## Changes

1. `src/components/HorizontalBarChart.vue`
   - Change: import the composable; add `const reduced = usePrefersReducedMotion()`; inside the
     existing `options` computed (`:44-75`), set `animation: reduced.value ? false : undefined`
     the same way the exemplars do.
   - Preserve: `indexAxis: 'y'`, tooltip callbacks, scale colors, props (`color`/`hoverColor`).
   - Verify: with OS reduced-motion on, bars appear without animating.
2. `src/components/MonthlyExpenditureChart.vue`
   - Change: import the composable; convert the static `options` (`:40-73`) into
     `computed<ChartOptions<'bar'>>` and apply the same `animation` flag as the exemplars.
   - Preserve: all existing scales/tooltip config; the static object becomes computed **only**
     to read the reactive flag — no other behavioral change.
   - Verify: same reduced-motion check on the Dashboard page.

## Scope

- Inherit: DashboardPage, BudgetExecutionPage.
- Verify: the three already-correct charts keep working untouched; chart tooltips/labels
  unchanged in both motion modes.
- Exclude: sidebar/overlay CSS transitions (`AppLayout.vue:79` `transition-transform`) — small
  UI transitions, separate decision; Chart.js `transitions` namespace fine-tuning.

## Validation

- Product: toggle OS "reduce motion" (or DevTools → Rendering → emulate
  `prefers-reduced-motion: reduce`) — dashboard and budget-execution charts render statically;
  with it off, animations run as before.
- Interface: reload each page after toggling (media change is reactive, but verify a fresh load).
- System: all 5 chart components import the composable —
  `grep -L "usePrefersReducedMotion" frontend/src/components/*Chart.vue` → no output.
- Repository: `cd frontend && npm run verify` → passes.

## Stop conditions

- Stop if Chart.js v4 typings reject `animation: false` in this codebase's chart options type —
  then follow precisely whatever shape the working exemplars (`ForecastChart`) already compile
  with; do not invent a different mechanism.

## Design documentation

- After acceptance: none (matches the composable's existing doc comment).
