# Plan Review: 2026-09-13-design-review-fixes-prp (round 4)

**Plan**: PRPs/2026-09-13-design-review-fixes-prp.md (v2r3, 607 lines, 24 tasks)
**Reviewed**: 2026-09-13
**Source PRD**: PRPs/2026-09-13-design-review-fixes-prd.md
**Verdict**: READY
**Confidence Score**: 8/10 — single-pass implementation

File note: this round-4 report uses an `-r4` suffix to preserve the round-1
through round-3 reports.

## Reviewer Verdicts

| Reviewer | Diversity | Verdict | Critical issues |
|---|---|---|---|
| A4 | general-purpose | PASS | 1 |
| B4 | general-purpose, context isolation only | PASS | 0 |

Diversity achieved: context isolation only (platform offers no per-dispatch
model choice). A4 spot-checked 12+ SOURCE refs; B4 spot-checked 40+ SOURCE
refs — all resolved with matching content. Parent corroborated the
load-bearing B2 finding plus the two highest-impact suggestions directly
with grep/sed (see evidence notes below).

## Rubric Results

| # | Criterion | A4 | B4 | Evidence |
|---|---|---|---|---|
| A1 | Context Completeness | PASS | PASS | Files table + idioms + none-new deps |
| A2 | Implementation Readiness | PASS | PASS | All 24 tasks carry full fields + import paths |
| A3 | Pattern Faithfulness | PASS | PASS | 12–40+ refs verified, zero dead |
| A4 | Validation Coverage | PASS | PASS | All gates exist with matching semantics |
| A5 | UX Clarity | PASS | PASS | 11 Before/After pairs with WCAG refs |
| A6 | No Prior Knowledge Test | PASS | PASS | Literal strings + runnable commands throughout |
| B1 | Spec Coverage | PASS | PASS | R1–R6 incl. all minors mapped (jargon triaged) |
| B2 | Internal Consistency | FAIL | PASS | See disagreement note |
| B3 | Technical Soundness | PASS | PASS | Real APIs, version gotchas noted |
| B4 | Scope Discipline | PASS | PASS | NOT Building honored, F optional |
| B5 | Risk Coverage | PASS | PASS | Risks + GOTCHA on all 24 tasks |
| B6 | Testability | PASS | PASS | Input/expected pairs + edge checklist |

Score: 10 − 2×0 − 1×1 (B2) − 1×1 (🟡) = 8 → READY band.

## 🔴 Critical (must fix before implementing)

- None.

## 🟡 Important (should fix)

- T-B6/Corrections assert "grep 36 จุดไม่พบใน button" (expect count = 0) but
  `<p>` inside native `<button>` exists in at least 2 blocks:
  NotificationBell.vue:131–146 (3 `<p>` at :141/:142/:143) and
  NotificationListPage.vue:99–122 (`<p>` at :111/:119/:120) —
  parent-verified. Correct the expectation and name the two files
  (non-blocking: T-B6's own fix-if-found branch self-corrects at runtime).

## 🟢 Minor / Suggestions

- T-B2: AllowanceTypeListPage checkboxes :226/:233/:236/:261 are ALREADY
  wrapped in `<label>` (parent-verified :225–228, :232–237, :260–262) — the
  "ห่อ `<label>`" step is a no-op and :226 needs no aria-label; convert to
  verify-first (implementer must NOT double-wrap)
- T-B6 misses AppLayout.vue:264 `<Calendar class="mr-1 h-4 w-4" />`
  (class-not-first, parent-verified) — enumerate :264 and widen the VALIDATE
  grep beyond `class="h-[0-9]`
- T-D2.1: disambiguate the two buttons (บันทึกร่าง :146 vs ส่งอนุมัติ :153) —
  state which becomes type="submit", set the other type="button", give
  `<form>` a real submit handler instead of bare @submit.prevent
- T-D1.3: explicitly state isDirty must be added to the store return block
  (:64–76), not just declared
- T-B2 VALIDATE: "6 หน้า Phase 9" covers 5 pages in T-B2 (the 6th, Position,
  is T-B3) — fix to 5 or name all six
- T-A3 LoginPage:155: :300→:400 remap equals the base text-primary-400,
  erasing the hover cue — confirm intended or pick a distinct state shade
- T-F1 GOTCHA "workflow หลัก disabled อยู่": ci.yml shows active triggers —
  verify disabled state at repo-settings level (wording only; minutes
  caution still stands)
- Jargon scope-out needs explicit approver sign-off since PRD R6 names it
- T-E1: give the five chart components as full paths instead of bare names
- Re-check the "dead prop" characterization of responsive-layout before
  deleting BudgetExecutionPage:170 (overflow VALIDATE covers behavior anyway)

## Reviewer Disagreements

- **B2 (A4 FAIL / B4 PASS)**: A4 fails on the false p-in-button "none in
  button" expectation (repo proves otherwise). B4 passes, treating it as
  covered by T-B6's fix-if-found branch and noting only minor count wobbles.
  Counts as failed per gate rule; recorded as the single 🟡 above.

## Recommended Next Step

implement from the plan (optionally fold the 1 🟡 + 🟢 cleanups in first —
none blocks single-pass implementation)
