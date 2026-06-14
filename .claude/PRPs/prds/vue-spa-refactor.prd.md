# Vue SPA Refactor — hr_budget Frontend

## Problem Statement

The HR Budget system's frontend is server-rendered PHP templates with vanilla JS. Every interaction triggers a full page reload, complex multi-step flows (budget tracking) depend on fragile server-side session state, and the UI codebase (59 view files across 97 web routes) is increasingly hard to extend. The project is pre-production — the cheapest possible moment to fix the foundation before real users arrive.

## Evidence

- 59 PHP view files / 14 web controllers / 97 web routes vs only 46 `/api/v1` routes — the API covers roughly 1/3 of the system surface (measured 2026-06-12)
- Budget tracking multi-step flow stores intermediate state server-side via `/budgets/tracking/store-session` — hard to test, couples UI to PHP session
- Forms use the `_method=PUT|DELETE` override convention and full-page reloads; no client-side validation feedback until submit
- A JWT-secured REST API layer (`src/Api` → `src/Services` → `src/Repositories` + `src/Dtos`) already exists from Day 1–3b — half the separation work is done
- Assumption (needs validation in Phase 2): PrimeVue DataTable covers Thai-locale table needs (filter/sort/pagination) without custom work

## Proposed Solution

Rebuild the frontend as a Vue 3 SPA (Option B — full SPA, not incremental islands) in `frontend/` within the same repo, backed by the existing PHP REST API. PHP retains only `/api/v1/*` plus one route serving the SPA shell. Chosen over incremental embedding (Option A) because pre-production status removes the downtime risk that justified A's coexistence glue, and over a separate repo (Option C) because a solo maintainer benefits from one checkout.

## Key Hypothesis

We believe a Vue 3 SPA over the existing REST API will make complex budget workflows faster to build and safer to change for the solo developer maintaining this system.
We'll know we're right when the Admin Master Data module (Phase 2) reaches feature parity with its PHP-view counterpart with all CRUD E2E tests passing — and adding a new master-data screen takes measurably less code than the PHP equivalent.

## What We're NOT Building

- **Mobile app** — the API becomes mobile-ready as a side effect, but no app in scope
- **Real-time websockets** — notifications use polling (or SSE later); websocket infra is overkill for current needs
- **SSR (server-side rendering) / SEO work** — internal app behind login; irrelevant
- **New features during migration** — feature freeze on PHP views; parity first, enhancements after Phase 6
- **Redesign of visual identity** — keep current Tailwind look; this is an architecture refactor, not a redesign

## Success Metrics

| Metric | Target | How Measured |
|--------|--------|--------------|
| MVP milestone (Phases 1+2) | Login + all admin master-data CRUD via Vue, parity with PHP views | Playwright E2E suite green on SPA routes |
| API statelessness | 0 endpoints depending on PHP session (currently: tracking flow) | Code review of `src/Api` + grep for `$_SESSION` |
| Frontend test coverage | ≥80% on stores/composables/utils (project standard) | Vitest coverage report |
| Page-to-page navigation | No full reloads after initial load | Manual + Playwright navigation assertions |
| Bundle size (app shell) | < 300kb gzipped JS | `vite build` output |

## Open Questions

- [x] CSRF strategy: SameSite=Strict httpOnly cookie + mandatory `X-Requested-With` header on cookie-authed mutations + CORS allowlist (decided in Phase 1 plan)
- [ ] Notification freshness: polling interval vs SSE — decide in Phase 3
- [x] Do `plans`, `divisions`, `targets`, `target-types` admin resources need new API endpoints in Phase 2? **Resolved (Phase 2):** all four needed new `/api/v1/*` chains (Repository→DTO→Service→Controller). `plans` table already existed (built against real schema, not the broken legacy `BudgetPlanController`); `divisions`/`target_types`/`budget_targets` tables created via migration 064. No `TargetType` model existed (legacy admin page was already broken).
- [ ] vue-i18n now or hardcode Thai strings (current approach)? Leaning hardcode-Thai to match existing views; revisit if bilingual requirement appears
- [ ] Server-side token revocation on logout (JWT stays valid until TTL) — review jwt_ttl length before production; revocation list only if a real requirement appears (security review 2026-06-12)
- [ ] Login-CSRF hardening (Origin check on /auth/login) and removing `env` from public /health — deferred follow-ups from Phase 1 security review

---

## Users & Context

**Primary User**
- **Who**: HR division staff (government) entering budget requests, tracking execution, and recording disbursements; plus admin users maintaining master data
- **Current behavior**: Use the PHP-rendered app in dev/UAT; every action reloads the page; multi-step tracking form loses context if session expires
- **Trigger**: Fiscal-year budget cycle (Buddhist calendar, Oct 1 boundary) drives bursts of data entry and approval activity
- **Success state**: Filter/sort large budget tables instantly, complete multi-step forms with inline validation, see notification badges without refreshing

**Job to Be Done**
When entering and tracking budget data through the fiscal year, I want responsive tables and forms that validate as I type, so I can finish data entry quickly without losing work to page reloads or expired sessions.

**Non-Users**
Public citizens / external agencies — internal system only. No anonymous or public surface.

---

## Solution Detail

### Core Capabilities (MoSCoW)

| Priority | Capability | Rationale |
|----------|------------|-----------|
| Must | SPA shell: Vue Router (subdirectory-aware base), main layout w/ sidebar, auth guard | Foundation everything sits on |
| Must | JWT auth via httpOnly cookie + refresh handling | Security baseline; replaces PHP session for SPA |
| Must | Admin master data CRUD (fiscal years, organizations, categories+items, users, + remaining admin resources) | MVP validation target; APIs largely exist |
| Must | API gap closure per phase (tracking/disbursement endpoints are the big one) | SPA cannot ship a module without its API |
| Should | Dashboard (vue-chartjs) + notification badge/list | High-visibility win; APIs exist |
| Should | Budget request workflow (create→submit→approve/reject) w/ vee-validate+zod | Core business flow; API exists from Day 2–3 |
| Should | Budget tracking multi-step in Pinia (replace server-session flow) + disbursements | Hardest module; deliberately late |
| Could | vue-sonner toasts replacing SweetAlert2 | Cosmetic; SweetAlert2 works fine meanwhile |
| Could | SSE notifications | Polling is acceptable v1 |
| Won't | Mobile app, websockets, SSR, visual redesign, new features mid-migration | See "Not Building" |

### MVP Scope

Phases 1+2: a user logs in via the SPA (JWT cookie), lands on the main layout, and performs full CRUD on every admin master-data resource with PrimeVue DataTables — at parity with the PHP views, verified by Playwright.

### User Flow (critical path)

Login → main layout (sidebar) → Admin → Fiscal Years table → create/edit in validated form → toast confirms → table refreshes (TanStack Query invalidation) — no page reload at any step.

---

## Technical Approach

**Feasibility**: HIGH — API layer, JWT (`App\Core\Jwt`), Vite 6, and Tailwind 4 already in place; gap is UI code and ~2/3 of API surface.

**Architecture Notes**
- Monorepo layout: `frontend/` (Vue 3 + TS) beside existing `src/` (PHP). Vite dev server proxies `/api` → Laragon PHP. Production build outputs to `public/` for PHP to serve statically
- Layering: SFC views → composables → TanStack Query (server state) / Pinia (client state) → typed API client (`frontend/src/api/`)
- Types generated/hand-written from `src/Dtos` shapes — one source of truth for payload contracts
- dayjs + `buddhistEra` plugin + `th` locale for พ.ศ. handling (fiscal year 2569)
- Auth: `POST /api/v1/auth/login` sets httpOnly cookie; `AuthMiddleware` already validates Bearer — needs cookie-mode support added (small PHP change)
- PHP web controllers/views remain untouched (reference spec) until Phase 6 archive

**Technical Risks**

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| API gap larger than estimated (tracking/disbursement domain logic buried in web controllers) | M | Phase-start API audit; port logic into Services (pattern exists) |
| JWT-in-cookie auth done wrong (XSS/CSRF) | M | httpOnly + SameSite=Strict + CSRF token; security review gate on Phase 1 |
| Authorization holes — SPA users can hit API directly | M | Every endpoint enforces authz in Service layer; integration tests per role |
| Learning curve slows Phases 1–2 (first Vue project) | H | Accepted — no deadline; PrimeVue templates + AI assistance; patterns locked in Phase 2 become copy-paste for later phases |
| PrimeVue unstyled + Tailwind 4 beta friction | L/M | Validate in Phase 1 scaffold; fallback to styled PrimeVue theme |

---

## Implementation Phases

<!--
  STATUS: pending | in-progress | complete
  PARALLEL: phases that can run concurrently
  DEPENDS: phases that must complete first
  PRP: link to generated plan file once created
-->

| # | Phase | Description | Status | Parallel | Depends | PRP Plan |
|---|-------|-------------|--------|----------|---------|----------|
| 1 | SPA Scaffold + Auth | (Revised: scaffold pre-exists) Stack upgrade (PrimeVue+TanStack+vee-validate+Vitest) + JWT httpOnly cookie auth hardening | complete | - | - | [plan](../plans/completed/vue-spa-phase1-foundation-auth.plan.md) · [report](../reports/vue-spa-phase1-foundation-auth-report.md) |
| 2 | Admin Master Data CRUD | All admin resources via PrimeVue DataTable + validated forms; close admin API gaps | complete | - | 1 | [plan](../plans/completed/vue-spa-phase2-admin-crud.plan.md) · [report](../reports/vue-spa-phase2-admin-crud-report.md) |
| 3 | Dashboard + Notifications | vue-chartjs dashboard, notification badge/list (polling) | pending | with 4 | 2 | - |
| 4 | Budget Request Workflow | create→submit→approve/reject + file upload UI | pending | with 3 | 2 | - |
| 5 | Budget Tracking + Disbursements | New stateless APIs replacing store-session; multi-step form state in Pinia | pending | - | 2 | - |
| 6 | Cutover + Cleanup | Archive PHP views/web controllers, single SPA entry route, update CLAUDE.md + tests | pending | - | 3, 4, 5 | - |

### Phase Details

**Phase 1: SPA Scaffold + Auth**
- **Goal**: Running SPA skeleton a developer can build every later phase on
- **Scope**: `frontend/` project; Vite proxy config; login page; JWT cookie flow (incl. PHP `AuthMiddleware` cookie support + CSRF decision); router with subdirectory base; main layout w/ sidebar; Vitest + Playwright wiring; CI-able `npm run build`
- **Success signal**: Login → protected dashboard placeholder → logout works end-to-end under `/hr_budget/public/`; unauth access redirects to login

**Phase 2: Admin Master Data CRUD (MVP gate)**
- **Goal**: Prove the full CRUD pattern; lock conventions for all later phases
- **Scope**: fiscal years, organizations, categories+items (hierarchical), users, target types, divisions, plans, targets; API audit + new endpoints where missing; DataTable list + vee-validate/zod forms + TanStack Query mutations; E2E parity tests
- **Success signal**: Hypothesis validated — all admin CRUD green in Playwright; PHP admin views unused

**Phase 3: Dashboard + Notifications**
- **Goal**: Visual/system-status surfaces on Vue
- **Scope**: chart-data endpoint consumption via vue-chartjs; notification list + unread badge with polling
- **Success signal**: Dashboard parity; badge updates without manual refresh

**Phase 4: Budget Request Workflow**
- **Goal**: First core business flow on Vue
- **Scope**: request CRUD, item lines, submit, approve/reject with notification dispatch, file attachments (upload API exists)
- **Success signal**: Full request lifecycle E2E green, incl. approver role

**Phase 5: Budget Tracking + Disbursements**
- **Goal**: Eliminate the last server-session dependency; hardest domain last
- **Scope**: design stateless tracking/disbursement APIs (port logic from `BudgetController`/`BudgetExecutionController`/`DisbursementController` into Services); multi-step wizard with Pinia draft state; disbursement recording
- **Success signal**: `store-session` route deleted; tracking flow E2E green; zero `$_SESSION` use in API layer

**Phase 6: Cutover + Cleanup**
- **Goal**: SPA is the only frontend
- **Scope**: archive `resources/views` + web controllers to `archives/`; single catch-all route serves SPA; remove session-auth web login; update CLAUDE.md, README, Playwright base config
- **Success signal**: Full `npm test` suite green; repo contains one frontend

### Parallelism Notes

Phases 3 and 4 are independent modules sharing only Phase 2's conventions — safe to interleave or run as parallel worktrees. Phase 5 is sequenced alone because its API design benefits from lessons in 3–4, and it's the highest-risk module. Phase 6 must be last.

---

## Decisions Log

| Decision | Choice | Alternatives | Rationale |
|----------|--------|--------------|-----------|
| Migration strategy | Full SPA (Option B) | A: incremental islands; C: separate repo | Pre-production → no downtime risk; avoids throwaway coexistence glue; solo dev → one repo |
| Language | TypeScript | Plain JS | Catches data-shape mistakes at compile time in a data-heavy app; better AI assistance; user-confirmed |
| UI library | PrimeVue 4 (unstyled + Tailwind) | shadcn-vue | Built-in DataTable suits table-heavy government workflows; user-confirmed |
| Server state | TanStack Query (Vue) | Hand-rolled fetch + Pinia | Cache/invalidation/loading handled; avoids duplicating server state into stores |
| JWT storage | httpOnly cookie | localStorage | JS cannot read token (XSS-proof); accept CSRF mitigation work |
| Forms | vee-validate + zod | Manual validation | Schema-based, matches complex budget forms |
| Charts/dates | vue-chartjs, dayjs+buddhistEra | New libraries | Keep existing, proven deps; พ.ศ. support required |
| MVP gate | Phases 1+2 complete | Phase 1 only / through Phase 3 | CRUD parity proves the pattern end-to-end; user-confirmed |
| Timeline | No fixed deadline, quality-first | — | User-confirmed |

---

## Research Summary

**Market Context**
Standard, well-trodden migration path (server-rendered PHP → REST + SPA). The chosen stack (Vue 3 + Pinia + TanStack Query + PrimeVue) is a mainstream 2025–2026 combination with strong documentation; no exotic dependencies.

**Technical Context**

> **Revision 2026-06-12:** Phase-1 exploration found `frontend/` already contains a working Vue 3.5 + TS SPA (login, auth guard, AppLayout, Pinia stores, typed API clients, ~10 pages on port 5174 with Vite proxy) from the Day 1–3b work. Phases re-scoped from "build from scratch" to "upgrade + close gaps". Stack deltas decided with user: adopt PrimeVue (styled Aura mode, not unstyled), TanStack Query, vee-validate/zod, Vitest; migrate JWT from localStorage to httpOnly cookie; frontend stays on Tailwind 3.4 (not 4).

- `src/Api/{Controllers,Middleware,Responses}` + `src/Services` + `src/Repositories` + `src/Dtos` already implement the layered API (Day 1–3b work) with JWT (`App\Core\Jwt`, `AuthMiddleware` at `src/Api/Middleware/AuthMiddleware.php`)
- 46 API routes today: auth, fiscal-years, organizations, categories(+items), users, budget-requests, files, notifications
- Missing APIs (≈ Phase 2/5 work): plans, divisions, targets, target-types, budgets/execution/tracking, disbursements
- Router (`src/Core/Router.php`) is subdirectory-aware — SPA catch-all route must respect script-prefix stripping
- Existing Playwright config (Chromium, `BASE_URL`) reusable for SPA E2E

---

*Generated: 2026-06-12*
*Status: DRAFT - needs validation*
