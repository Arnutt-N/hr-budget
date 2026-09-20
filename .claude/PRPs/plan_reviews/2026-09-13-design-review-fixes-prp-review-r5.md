# Plan Review: 2026-09-13-design-review-fixes-prp (round 5)

**Plan**: PRPs/2026-09-13-design-review-fixes-prp.md (v2r4, 628 lines, 24 tasks)
**Reviewed**: 2026-09-13
**Source PRD**: PRPs/2026-09-13-design-review-fixes-prd.md (+ all four prior
review reports as extended B1 scope this round)
**Verdict**: READY
**Confidence Score**: 8/10 — single-pass implementation

File note: this round-5 report uses an `-r5` suffix to preserve the round-1
through round-4 reports. Scope extension this round: B1 additionally required
every finding from all four prior reports to be addressed or dispositioned —
both reviewers confirm full coverage (e.g. r2 DataTable-29, r3 :137/:174
refile, r4 p-in-button + :264).

## Reviewer Verdicts

| Reviewer | Diversity | Verdict | Critical issues |
|---|---|---|---|
| A5 | general-purpose | PASS | 0 |
| B5 | general-purpose, context isolation only | PASS | 0 |

Diversity achieved: context isolation only (platform offers no per-dispatch
model choice). A5 spot-checked 20+ SOURCE refs; B5 spot-checked 10+ SOURCE
refs — all resolved with matching content. Parent corroborated the B2
finding and all three suggestions directly with grep/sed.

## Rubric Results

| # | Criterion | A5 | B5 | Evidence |
|---|---|---|---|---|
| A1 | Context Completeness | PASS | PASS | Files table + idioms + none-new deps |
| A2 | Implementation Readiness | PASS | PASS | All 24 tasks carry full fields + imports |
| A3 | Pattern Faithfulness | PASS | PASS | 10–20+ refs verified, zero dead |
| A4 | Validation Coverage | PASS | PASS | All gates exist with matching semantics |
| A5 | UX Clarity | PASS | PASS | 11 Before/After pairs with WCAG refs |
| A6 | No Prior Knowledge Test | PASS | PASS | Literals + runnable checks throughout |
| B1 | Spec Coverage | PASS | PASS | PRD mapped + all r1–r4 findings dispositioned |
| B2 | Internal Consistency | FAIL | FAIL | Icon tally gloss vs grep (see 🟡) |
| B3 | Technical Soundness | PASS | PASS | Real APIs, gotchas noted |
| B4 | Scope Discipline | PASS | PASS | NOT Building honored |
| B5 | Risk Coverage | PASS | PASS | Risks + GOTCHA on all 24 tasks |
| B6 | Testability | PASS | PASS | Input/expected pairs + edge checklist |

Score: 10 − 2×0 − 1×1 (B2) − 1×1 (🟡) = 8 → READY band.

## 🔴 Critical (must fix before implementing)

- None.

## 🟡 Important (should fix)

- T-B6 tally gloss "(39 เติมใหม่ + 4 เดิม)" is arithmetically wrong per grep:
  of the 43 widened-grep hits only 2 are pre-existing (LoginPage :142/:143
  Eye/EyeOff, single-line); the other 2 (:102/:122 Mail/Lock) sit in
  multi-line tags the line-based grep never reports (parent-verified) — true
  split is 41 new + 2 in-set (+ 2 out-of-grep). AppLayout count is 29, not
  "~31" (parent-verified). Non-blocking: the 43-total and re-grep VALIDATE
  self-correct, but fix the gloss to avoid confusion.

## 🟢 Minor / Suggestions

- T-D2.2 MIRROR "RoleListPage.vue:224" points at `</div>` — the name_th/code
  spans are :222–:223 (parent-verified); repoint all three cites
  (IMPLEMENT, MIRROR, GOTCHA)
- Add one explicit sentence confirming no PrimeVue primary-severity control
  contributes contrast fails (HrBudgetPreset stays on sky.*) — currently
  covered only implicitly by the contrast=0 gate (both reviewers suggest)
- Reviewer error — DO NOT APPLY: B5's "T-F1 :242 is wrong, actual step is
  :238–239" is incorrect — :238–239 is the frontend install while :242 is
  the root `npm ci --ignore-optional` the plan cites (parent-verified);
  the plan's citation stands

## Reviewer Disagreements

- None. Both reviewers PASS overall and fail B2 on the same icon-tally
  evidence with the same non-blocking assessment.

## Recommended Next Step

implement from the plan (optionally fold the 1 🟡 + 2 🟢 cleanups in first —
none blocks single-pass implementation)
