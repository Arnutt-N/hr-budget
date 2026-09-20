# Plan Review: 2026-09-13-design-review-fixes-prp (round 3)

**Plan**: PRPs/2026-09-13-design-review-fixes-prp.md (v2r2, 581 lines, 24 tasks)
**Reviewed**: 2026-09-13
**Source PRD**: PRPs/2026-09-13-design-review-fixes-prd.md
**Verdict**: NOT READY
**Confidence Score**: 2/10 — single-pass implementation

File note: this round-3 report uses an `-r3` suffix to preserve the round-1
and round-2 reports.

## Reviewer Verdicts

| Reviewer | Diversity | Verdict | Critical issues |
|---|---|---|---|
| A3 | general-purpose | PASS | 1 |
| B3 | general-purpose, context isolation only | FAIL | 5 |

Diversity achieved: context isolation only (platform offers no per-dispatch
model choice). Both reviewers spot-checked 15–20+ SOURCE refs read-only (all
resolved). Parent corroborated every disputed fact directly with grep/sed
(see evidence notes below).

## Rubric Results

| # | Criterion | A3 | B3 | Evidence |
|---|---|---|---|---|
| A1 | Context Completeness | PASS | PASS | Files table + idioms with live SOURCE refs |
| A2 | Implementation Readiness | PASS | FAIL | See disagreement note |
| A3 | Pattern Faithfulness | PASS | PASS | 15–20+ refs verified, no invented snippet |
| A4 | Validation Coverage | PASS | PASS | composer/frontend/audit/contrast gates all exist |
| A5 | UX Clarity | PASS | PASS | Before/After with ratios + WCAG refs |
| A6 | No Prior Knowledge Test | PASS | FAIL | See disagreement note |
| B1 | Spec Coverage | PASS | PASS | R1–R6 all mapped (jargon explicitly deferred) |
| B2 | Internal Consistency | FAIL | FAIL | 28-vs-29 header; :137/:174 misattributed; "6 PRs" vs 7 |
| B3 | Technical Soundness | PASS | PASS | Aura shape, sky labels, TS7016 rationale all verified |
| B4 | Scope Discipline | PASS | PASS | NOT Building honored |
| B5 | Risk Coverage | PASS | PASS | Risks + per-task GOTCHA on all 24 tasks |
| B6 | Testability | PASS | PASS | Input/expected pairs + 9-item edge checklist |

Score: 10 − 2×2 (A2,A6) − 1×1 (B2) − 1×3 (🟡) = 2.
Note: the score is driven by reviewer-strictness variance on 5 small
enumerated items (A3 passed the plan outright at 11/12); the foundation
itself is sound — see Recommended Next Step.

## 🔴 Critical (must fix before implementing)

- Files to Change D1 row points at RequestEditPage:137/:174, but those lines
  are RequestDetailPage's and RequestEditPage has only 156 lines
  (parent-verified) — wrong-file reference blocks single-pass work. —
  Files to Change; evidence: plan "RequestEditPage:92 (+:137 live, :174 คงเดิม)"
- Corrections claims "'1-12': grep ไม่พบ" but grep finds
  PositionListPage.vue:71, 208 (zod messages) and :515 (user-visible label
  "เดือนที่นับ (1-12)", parent-verified) — false verification claim silently
  closes a PRD copy item. — T-D2.3/Corrections; evidence: plan "'1-12' ไม่พบ"

## 🟡 Important (should fix)

- T-D2.3 em-dash sweep has no file list, search command, or expected count —
  implementer must discover scope alone (give grep + count or convert to
  verify-first with explicit close criteria)
- T-D1.5 gives exact hint strings for 6 points but none for
  DisbursementWizardPage:562 (submitFinal, pending/recordFailed) and
  DocumentVaultPage:224 (upload, folder-null) — implementer must read code to
  invent them
- Count contradictions: T-C1 header "(28 DataTables + 1 native)" vs its own
  29-point list + VALIDATE `wc -l = 29` (fix header to 29); Metadata "6 PRs"
  vs 7 actual (PR-1, PR-2, PR-3, PR-4a, PR-4b, PR-5, PR-6)

## 🟢 Minor / Suggestions

- T-B6 LoginPage parenthetical: existing aria-hidden is at :102/:122/:142/:143,
  not :92/:186 (parent-verified) — repoint so the implementer skips the right lines
- Record the jargon deferral in NOT Building (visibility at scope level)
- Reconcile Success Criteria baseline "contrast 6" vs PRD "contrast engine 5 จุด"
  (or confirm the 6th fail source) so T0.1 has one unambiguous number
- Add one explicit "no new dependencies" line
- T-B6: put the exact icon-grep locator in IMPLEMENT, not only VALIDATE
- T-D2.2 favicon: state the fallback explicitly if %BASE_URL% is unsubstituted
- Reviewer errors — DO NOT APPLY: B3's "T-D2.1 :105/:120 → :106/:121"
  (verified :105/:120 are the `<button>` lines; :106/:121 are `type=` attrs);
  A3's "button `as` :136 → :135" (verified declaration is at :136)

## Reviewer Disagreements

- **A2 (A3 PASS / B3 FAIL)**: A3 holds every task carries the required fields
  with approximate refs located by file + WHAT to find. B3 holds T-D2.3's
  unbounded sweep and T-D1.5's two missing strings force codebase searching.
  Counts as failed per gate rule.
- **A6 (A3 PASS / B3 FAIL)**: A3 holds exact strings + runnable VALIDATEs carry
  a newcomer through. B3 holds the wrong-file ref, invented strings, and
  unbounded sweep block single-pass. Counts as failed per gate rule.

## Recommended Next Step

revise and re-validate — fix the 2 🔴 + 3 🟡 (+ 🟢 cleanups), then re-run
prp-validate-plan

Band note (disclosure): the arithmetic (2/10) falls in the <6 RE-PLAN band,
but that band's premise ("the plan's foundation is off") does not hold here —
A3 passed outright (11/12), B3 passed 9/12, and every finding is a small
enumerated fix with exact file:lines. A full re-plan would discard a sound
foundation; the revise loop is the truthful next step. Round-over-round: r1
needed structural rewrite → r2 had 3 factual errors → r3 has 5 small
nits + 1 wrong ref + 1 false close.
