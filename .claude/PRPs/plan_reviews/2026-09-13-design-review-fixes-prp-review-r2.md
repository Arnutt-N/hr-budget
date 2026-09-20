# Plan Review: 2026-09-13-design-review-fixes-prp (round 2)

**Plan**: PRPs/2026-09-13-design-review-fixes-prp.md (v2, 559 lines, 24 tasks)
**Reviewed**: 2026-09-13
**Source PRD**: PRPs/2026-09-13-design-review-fixes-prd.md
**Verdict**: NOT READY
**Confidence Score**: 6/10 — single-pass implementation

File note: this round-2 report uses an `-r2` suffix to preserve the round-1
report at `2026-09-13-design-review-fixes-prp-review.md`.

## Reviewer Verdicts

| Reviewer | Diversity | Verdict | Critical issues |
|---|---|---|---|
| A2 | general-purpose | FAIL | 3 |
| B2 | general-purpose, context isolation only | FAIL | 2 |

Diversity achieved: context isolation only (platform offers no per-dispatch
model choice). Both reviewers spot-checked 15–20+ SOURCE refs read-only (all
resolved). Parent corroborated every disputed fact directly with grep/node
(see evidence notes below). Both reviewers miscounted tasks as 20; actual
count is 24 (`grep -c "ACTION:"`) — immaterial to all verdicts.

## Rubric Results

| # | Criterion | A2 | B2 | Evidence |
|---|---|---|---|---|
| A1 | Context Completeness | PASS | PASS | Files table + `@/components/FormField.vue` (SOURCE CategoryListPage:14) |
| A2 | Implementation Readiness | PASS | PASS | Every task has ACTION/IMPLEMENT/MIRROR/VALIDATE + import paths |
| A3 | Pattern Faithfulness | PASS | PASS | 15–20+ SOURCE refs spot-checked, all resolve with matching content |
| A4 | Validation Coverage | PASS | PASS | composer verify + frontend test:unit/verify + test:e2e:audit all exist |
| A5 | UX Clarity | PASS | PASS | Before/After per phase with ratios + WCAG refs |
| A6 | No Prior Knowledge Test | FAIL | FAIL | Unsatisfiable VALIDATEs (counts) + verify-breaking spec import |
| B1 | Spec Coverage | PASS | PASS | R1.1–R6 all map to tasks (B2 gives full mapping) |
| B2 | Internal Consistency | FAIL | FAIL | "27 จุด" asserted 5× vs 28 listed vs 29 actual; "no 4th shade file" vs ApprovalChainPanel |
| B3 | Technical Soundness | PASS | FAIL | See disagreement note |
| B4 | Scope Discipline | PASS | PASS | NOT Building present; tasks honor it |
| B5 | Risk Coverage | PASS | PASS | Real risks + mitigations + per-PR revert; GOTCHA on all but T-E2 |
| B6 | Testability | PASS | PASS | Input/expected pairs for both specs + edge-case checklist |

Score: 10 − 2×1 (A6) − 1×2 (B2,B3) − 1×0 (🟡) = 6 → REVISE band.

## 🔴 Critical (must fix before implementing)

- T-C1/DataTable count wrong three ways: plan asserts "27 จุด" (5×) but
  enumerates 28 file:lines, while ground truth is 29 lines in 24 files —
  missing `frontend/src/components/CategoryItemsPanel.vue:133`
  (parent-verified). VALIDATE `wc -l = 27` can never pass and one table stays
  unwrapped (R3.1 gap). — T-C1/Files table; evidence: plan "23 ไฟล์ 27 จุด"
- T-A3 shade claim false: "3 ไฟล์… ไม่มี 200 และไม่มีไฟล์ที่ 4" is wrong —
  `frontend/src/components/ApprovalChainPanel.vue:58` uses `text-primary-200`
  (in script) and `:135` uses `text-primary-300` (parent-verified). Truth is
  4 files with shades 200/300/900; grep-zero VALIDATE unachievable as written.
  — T-A3/Corrections; evidence: plan "ไม่มี 200 และไม่มีไฟล์ที่ 4"
- tokens.spec import breaks the mandated gate: strict:true + include
  src/**/*.ts + no allowJs (parent-verified tsconfig) means vue-tsc emits
  TS7016 on `import tailwindConfig from '../../../tailwind.config.js'`, yet
  T-A1 VALIDATE demands `npm run verify` green with no remedy — specify
  @ts-expect-error, a .d.ts shim, or JSON-embedded values. — T-A1/Testing
  Strategy; evidence: plan "import tailwindConfig from ..."

## 🟡 Important (should fix)

- None. (Reviewer `suggestions` map to 🟢 per skill; no other finding meets the
  🟡 "forces questions/searching" bar — all remaining findings leave the
  prescribed implementation unchanged.)

## 🟢 Minor / Suggestions

- T-A2 GOTCHA inverted: `.app-dark` IS on the DOM (`frontend/index.html:2`,
  parent-verified) so PrimeVue renders the dark scheme, not light — fix the
  rationale sentence; keep overriding both schemes (approach unaffected)
- T-B5 GOTCHA wrong: `primevue/button/index.d.ts:136` declares `as?: string |
  Component` (parent-verified) — keep the plain-anchor approach, fix or drop
  the "no as prop" justification
- Sky-N labels wrong (hex values correct, parent-verified via tailwindcss
  colors.js: sky-700 #0369a1, sky-800 #075985, sky-900 #0c4a6e): D6 "#075985
  is sky-700" should be sky-800; T-A1 fallback "#0c4a6e is sky-800" should be
  sky-900. Note also new primary-600 #0369a1 IS sky-700 — flag the confusion
  risk, and confirm no PrimeVue primary-severity control contributes contrast
  fails (HrBudgetPreset stays on sky.*)
- Aura SOURCE path prefix: `node_modules/...` → `frontend/node_modules/...`
  (shape `colorScheme.{light,dark}.info.color` itself verified real)
- T-D2.3: explicitly address the PRD R6 'jargon' sub-item or scope it out with
  a reason (currently silent)
- T-A2: call out the PRD R1.3 `primary-400` vs plan `{sky.400}` token-system
  switch explicitly (same hex #38bdf8)
- T-E2 carries no GOTCHA (uniformity nit; B5 still PASS)

## Reviewer Disagreements

- **B3 (A2 PASS / B2 FAIL)**: both agree on the underlying facts (tokens.spec
  import unsound under strict tsconfig; app-dark premise inverted). A2 holds
  the approach sound (no invented APIs/keys) with the falsehoods flagged as
  issues; B2 fails B3 on the two technical defects. Counts as failed per gate
  rule; the fix list is identical either way.

## Recommended Next Step

revise and re-validate — fix the 3 🔴 items (+ 🟢 rationale/label corrections),
then re-run prp-validate-plan
