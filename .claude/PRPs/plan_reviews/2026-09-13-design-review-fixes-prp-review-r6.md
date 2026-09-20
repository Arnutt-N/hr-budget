# Plan Review: 2026-09-13-design-review-fixes-prp (round 6)

**Plan**: PRPs/2026-09-13-design-review-fixes-prp.md (v2r5, 637 lines, 24 tasks)
**Reviewed**: 2026-09-13
**Source PRD**: PRPs/2026-09-13-design-review-fixes-prd.md
**Verdict**: READY
**Confidence Score**: 8/10 — single-pass implementation

File note: this round-6 report uses an `-r6` suffix to preserve the round-1
through round-5 reports. Process note: the first B6 dispatch carried a
parent-garbled rubric (A1/A2 merged) and was cancelled before producing
output; B6 was re-dispatched with the full correct rubric, so both
reviewers judged identical criteria with isolation intact.

## Reviewer Verdicts

| Reviewer | Diversity | Verdict | Critical issues |
|---|---|---|---|
| A6 | general-purpose | PASS | 1 |
| B6 | general-purpose, context isolation only | PASS | 0 |

Diversity achieved: context isolation only (platform offers no per-dispatch
model choice). A6 spot-checked ~30 SOURCE refs; B6 spot-checked 8+ SOURCE
refs — all resolved. Parent corroborated the B2 finding and the favicon
suggestion directly (spec source, Vite dist, tracked deploy build).

## Rubric Results

| # | Criterion | A6 | B6 | Evidence |
|---|---|---|---|---|
| A1 | Context Completeness | PASS | PASS | Files table + idioms + none-new deps |
| A2 | Implementation Readiness | PASS | PASS | All 24 tasks carry full fields + imports |
| A3 | Pattern Faithfulness | PASS | PASS | 8–30 refs verified, zero dead |
| A4 | Validation Coverage | PASS | PASS | All gates exist with matching semantics |
| A5 | UX Clarity | PASS | PASS | 11 Before/After pairs with WCAG refs |
| A6 | No Prior Knowledge Test | PASS | PASS | Literals + runnable checks throughout |
| B1 | Spec Coverage | PASS | PASS | R1–R6 mapped (jargon triaged) |
| B2 | Internal Consistency | FAIL | PASS | Audit-spec "zero expects" claim (see 🟡) |
| B3 | Technical Soundness | PASS | PASS | Real APIs, gotchas noted |
| B4 | Scope Discipline | PASS | PASS | NOT Building honored |
| B5 | Risk Coverage | PASS | PASS | Risks + GOTCHA on all 24 tasks |
| B6 | Testability | PASS | PASS | Input/expected pairs + edge checklist |

Score: 10 − 2×0 − 1×1 (B2) − 1×1 (🟡) = 8 → READY band.

## 🔴 Critical (must fix before implementing)

- None.

## 🟡 Important (should fix)

- Plan claims the audit spec has "expect = 0 ตัว" / "รันผ่านเสมอ" but
  tests/e2e/audit/spa-audit.spec.mjs:84 already soft-asserts axe
  serious/critical per route (parent-verified; the earlier "zero expects"
  probe missed it because the grep pattern `expect(` never matches
  `expect.soft(`). A baseline run with live violations goes red — reword
  T0.1 GOTCHA/Files-table to "spec has only a soft axe assertion; baseline
  may be red — record numbers regardless" and make T-F1 upgrade/keep the
  existing assertion instead of adding a duplicate. Non-blocking: T0.1
  VALIDATE reads summary.json numbers and T-F1 edits the file directly.

## 🟢 Minor / Suggestions

- T-D2.2 favicon: DROP the `%BASE_URL%` change to verify-only. Ground truth
  from the tracked deploy build: Vite already rebases `/favicon.svg` →
  `/hr_budget/public/app/favicon.svg` (parent-verified in public/app/index.html),
  and installed Vite dist contains no `%BASE_URL%` substitution (grep = 0) —
  so the current absolute href already works and the planned edit would write
  a literal `%BASE_URL%favicon.svg` 404. Replace IMPLEMENT with "confirm 200
  in deploy build (already correct)"; drop the relative-path fallback (breaks
  on deep SPA routes)
- Jargon scope-out needs explicit PRD-owner sign-off before PR-4b (both
  reviewers; already flagged in Open Questions)
- T-A1's tailwind.config.js require→import ESM change touches build config —
  consider running a full frontend build on the main-branch baseline first
  to isolate breakage
- Phase F is optional; confirm the CI-minutes decision early since its gate
  validates all prior phases

## Reviewer Disagreements

- **B2 (A6 FAIL / B6 PASS)**: A6 fails on the false "zero expects" claim
  (spec source proves otherwise). B6 passes B2, having verified counts but
  not the expects claim. Counts as failed per gate rule; recorded as the
  single 🟡 above.

## Recommended Next Step

implement from the plan (optionally fold the 1 🟡 + 4 🟢 cleanups in first —
none blocks single-pass implementation)
