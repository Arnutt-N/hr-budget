# Plan Review: 2026-09-13-design-review-fixes-prp

**Plan**: PRPs/2026-09-13-design-review-fixes-prp.md (127 lines)
**Reviewed**: 2026-09-13
**Source PRD**: PRPs/2026-09-13-design-review-fixes-prd.md
**Verdict**: NOT READY
**Confidence Score**: 1/10 — single-pass implementation

## Reviewer Verdicts

| Reviewer | Diversity | Verdict | Critical issues |
|---|---|---|---|
| A | general-purpose | FAIL | 5 |
| B | general-purpose, context isolation only | FAIL | 5 |

Diversity achieved: context isolation only (platform offers no per-dispatch model choice).
Both reviewers read the plan + PRD independently and spot-checked refs read-only
(FormField.vue, QueryErrorState.vue, tailwind.config.js:19, BudgetExecutionPage.vue:170,
package.json scripts). Parent corroborated the A4 dispute directly (see below).

## Rubric Results

| # | Criterion | A | B | Evidence |
|---|---|---|---|---|
| A1 | Context Completeness | FAIL | FAIL | "remap `primary-200/300/900` (4 ไฟล์)" — 4 files unnamed; zero file:line refs anywhere |
| A2 | Implementation Readiness | FAIL | FAIL | "### Phase A — Tokens + contrast (PR-1)" + dash bullets — not a PRP-format plan |
| A3 | Pattern Faithfulness | FAIL | FAIL | "Idiom ที่ audit รับรองแล้ว: `FormField`, `useDeleteConfirm`" — zero snippets, zero SOURCE refs |
| A4 | Validation Coverage | FAIL | PASS | "Verify: `contrast.py` คู่ปุ่ม/message" — see disagreement note |
| A5 | UX Clarity | FAIL | PASS | "`primary-600 #0284c7 → #0369a1`" + "เทียบ screenshots ปุ่ม" — see disagreement note |
| A6 | No Prior Knowledge Test | FAIL | FAIL | "หาจุด override PrimeVue theme (เริ่มที่ `frontend/src/main.ts`/css)" — discovery delegated to implementer |
| B1 | Spec Coverage | PASS | PASS | Every R1.1–R6 maps to a phase (Phases A/B/C/D1/D2/E) |
| B2 | Internal Consistency | PASS | PASS | "Backend ไม่ต้องแตะ" matches PRD non-goals; numbers match PRD baseline (4+4+1=9) |
| B3 | Technical Soundness | PASS | PASS | Token edit matches verified frontend/tailwind.config.js:19; D3 avoids version-coupled PrimeVue API |
| B4 | Scope Discipline | PASS | PASS | "## ขอบเขตและไม่ทำ" mirrors PRD Non-goals; all tasks stay frontend-only |
| B5 | Risk Coverage | FAIL | FAIL | "## ความเสี่ยง / Rollback" exists, but zero tasks carry GOTCHA fields |
| B6 | Testability | FAIL | FAIL | "## การยืนยัน" is audit/manual only — zero unit input/expected pairs, zero edge-case checklist |

Score: 10 − 2×6 (A1–A6) − 1×2 (B5,B6) − 1×3 (🟡) → floored at 1. NOT READY caps at 7 regardless.

## 🔴 Critical (must fix before implementing)

- Not a PRP-format plan: no ACTION/IMPLEMENT/MIRROR/VALIDATE/GOTCHA fields, no import paths, no file:line refs — implementer must search the codebase (A+B; "## แผนงาน" phases)
- Target files unnamed: the 4 token-remap files, 23 DataTable files, Phase 9 dialog paths, remaining lucide icon files (B; A concurs via A1)
- PrimeVue theme override location unresolved in-plan; implementer told to go find it, blocking Phase A contrast work (A+B; Phase A + "## ความเสี่ยง / Rollback")
- Validation command `contrast.py` dead as written — no such file at root; real path is `scripts/contrast.py` (A; parent-corroborated)
- Table scroll wrapper API undecided ("class ใน `style.css` หรือ component เล็ก") covering 23+ tables — highest-blast-radius change left as implementer design (A; Phase C)
- Zero SOURCE-cited snippets — no verified copy-paste anchors for FormField/QueryErrorState/dirty-guard/live-region reuse (A+B; "## บริบทและข้อเท็จจริง")

## 🟡 Important (should fix)

- Audit-only verification: no unit tests with input/expected pairs, though `frontend test:unit` (vitest) exists and every change is frontend-only (A+B)
- No edge-case checklist beyond mock artifacts (360px routes, drawer focus trap, disabled-with-hint, edit-guard toast) (A+B)
- Before/after UI states not documented per phase — disputed, see below (A)

## 🟢 Minor / Suggestions

- Consolidate Files to Change with line numbers; verify claimed counts ("4 ไฟล์", "23 DataTables") by grep (A)
- Pin the PrimeVue override point by reading frontend/src/main.ts + frontend/src/style.css theme wiring before Phase A (B; candidate lines main.ts:45, style.css:73)
- Rewrite each phase as PRP tasks with ACTION/IMPLEMENT/MIRROR/VALIDATE/GOTCHA + exact import paths (A+B)

## Reviewer Disagreements

- **A4 (A FAIL / B PASS)**: B verified the gates exist (root `test:e2e:audit`, `composer verify`, `frontend verify`). A found the named `contrast.py` unrunnable as written and the frontend unit leg (`test:unit`, exists in frontend/package.json) never invoked. Parent corroborated A: `contrast.py` absent at root, `scripts/contrast.py` real. Stays a finding.
- **A5 (A FAIL / B PASS)**: B accepts quantitative before/after (ratios, axe counts, "= 0" targets) as documented states. A requires documented UI states or an explicit N/A marker for a visual change. Genuine split; kept visible per gate rule.

## Recommended Next Step

re-plan — expand the roadmap into true PRP format (e.g. via /prp-plan), then re-run prp-validate-plan

Note: both reviewers unanimously PASS strategy/soundness (B1–B4). The foundation is solid —
phasing, PRD coverage, token-first approach, and scope are all sound. What is missing is
mechanical expansion to implementer-ready PRP format (per-task fields, file:lines, snippets,
runnable validation), not rethinking the plan.
