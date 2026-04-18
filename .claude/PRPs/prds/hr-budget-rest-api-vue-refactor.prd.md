# HR Budget Management — REST API + Vue Refactor

> Refactor `hr_budget` จาก server-rendered PHP MVC → PHP REST API (proper OOP + PDO) + Vue 3 SPA frontend

## Problem Statement

ฝ่ายงบประมาณของ HR ปัจจุบันทำงานด้วย Excel + กระดาษ — ทำคำขอช้า, รวบรวมข้อมูลยาก, ไม่มี dashboard สำหรับผู้บริหาร, วิเคราะห์/คาดการณ์งบประมาณไม่ได้ โปรเจกต์ `hr_budget` อยู่ในช่วงพัฒนาเป็น custom PHP MVC แต่ยังไม่ได้ launch — **ช่วง pre-launch นี้เป็นโอกาสทองที่จะ refactor architecture** ให้ UX snappy, backend maintainable, และส่งต่อทีมต่อได้ง่าย ก่อนจะมี user จริงเข้ามาล็อคให้แก้ลำบาก

## Evidence

- **User-reported (พนง.2-3 คน)**: รอบเตรียมงบ/รวบรวมข้อมูล Excel + เขียนสูตร ใช้เวลา **~30 วัน/รอบ** (คน 2-3 คนรวมกัน)
- **User-reported**: Excel export เหนื่อยมาก — ต้องทำ **3-4 ครั้ง/สัปดาห์** สำหรับรายงานผู้บริหาร
- **Observable**: ไม่มีการรวบรวมข้อมูล historical = ผู้บริหารขอรายงานทีต้อง build-from-scratch ทุกครั้ง
- **Observable from codebase**: `hr_budget` มี 97 routes, 31 models, 67 migrations — แปลว่า domain model ถูกออกแบบไว้แล้ว แต่ architecture ข้างในใช้ static methods + session + server-rendered views ซึ่ง test ยาก ส่งต่อทีมยาก
- **Gap**: เดิมคือ *"มันจะ refactor ดีไหม?"* → คำตอบ *"ทำตอนนี้ก่อน user ใช้ real data"* (migration cost ต่ำ)

## Proposed Solution

ใช้ **Strangler Fig pattern** refactor ทีละ feature โดย:

1. **Backend**: เพิ่ม API layer (`src/Api/`, `src/Services/`, `src/Repositories/`, `src/Dtos/`) ข้าง ๆ MVC เดิม
   - คืน JSON ทุก endpoint ภายใต้ `/api/v1/*`
   - Proper OOP: Controller (thin) → Service (business logic) → Repository (PDO + query builder) → Entity/DTO (typed)
   - Auth: session cookie (same-domain, simple)
   - Reuse existing 67 migrations เดิมต่อ
2. **Frontend**: แยก `frontend/` directory ใหม่
   - Vue 3 SPA + TypeScript + Vite + Pinia + Vue Router
   - Component library: ใช้ component-based แบบที่ smart-port ทำ (StatCard, EmptyState, SkeletonLoader, PaginationBar)
   - Deploy: `npm run build` → อัปโหลด `dist/` ไปที่ `public_html/` บน topzlab.com
3. **Database**: PDO + extended `SimpleQueryBuilder` (ไม่ใช้ Doctrine — overkill)
4. **Legacy MVC views ยังทำงาน** จน feature ใหม่ stable ค่อยลบ

**ทำไมไม่ใช้ทางเลือกอื่น:**

- ❌ **Doctrine ORM**: learning curve สูงมาก, migration entity 34 ตาราง = rewrite, overhead performance — ไม่คุ้มสำหรับ solo dev + shared hosting
- ❌ **Laravel full-stack rewrite**: 1-3 เดือนงาน, ไม่ได้ business value เพิ่ม
- ❌ **Big-bang rewrite**: risk สูง, solo dev burnout
- ❌ **Inertia.js**: ต้อง port adapter มาใช้กับ custom framework (เสียเวลา)

## Key Hypothesis

เราเชื่อว่า ถ้าเปลี่ยน budget workflow จาก Excel/กระดาษ → ระบบดิจิทัล (PHP REST API + Vue SPA) จะช่วยให้เจ้าหน้าที่ HR **ลดเวลารวบรวมข้อมูล/ทำรายงานจาก ~30 วัน เหลือ ~30 นาที/รอบ** (reduction ≈ 1,440×) สำหรับเจ้าหน้าที่งบประมาณของกอง HR (2-3 คน)

**เราจะรู้ว่าสมมติฐานถูกเมื่อ**:

- เจ้าหน้าที่ทั้ง 2-3 คน ใช้ระบบต่อเนื่อง ≥ 1 เดือน (ไม่กลับไป Excel)
- **เวลารวบรวมข้อมูล/ทำรายงานประจำรอบ ≤ 30 นาที** (vs. ~30 วัน ใน Excel)
- Export Excel report ได้ในคลิกเดียว ≥ **3-4 ครั้ง/สัปดาห์** (ตรงกับรอบงานจริง)
- ผู้บริหาร feedback ว่า dashboard มีข้อมูลที่ต้องการ

## What We're NOT Building

| Out of scope | เหตุผล |
|---|---|
| Scale ไปทั้งกระทรวง / multi-org / multi-tenant | Long-term vision, ไม่ใช่ MVP — เพิ่ม complexity มหาศาล |
| DPIS integration (real) | ยังไม่มี spec/API docs → ใช้ **mock adapter** ไว้ก่อน, real ตอน DPIS team ให้ spec |
| GFMIS integration (real) | Heavy restriction, ต้อง official approval → **mock adapter** ก่อน |
| Mobile native app (iOS/Android) | Desktop-first, responsive web พอสำหรับ MVP |
| External auditor portal | Role-based access เพิ่มใน Phase หลัง |
| Public API / third-party access | Internal only สำหรับ 12-18 เดือนแรก |
| PDPA consent UI / data export portal | ต้อง implement ก่อน go-live — อยู่นอก scope refactor |
| Microservices / Kubernetes / CDN | Over-engineering สำหรับ shared hosting + tens of users |
| AI/ML forecasting | ต่อเมื่อมี historical data ≥ 2 ปี |
| Multi-level approval hierarchy | MVP = 1-level, multi-level ใน Phase 6 |
| Audit log **viewer UI** | Basic audit log เก็บ — แต่ UI ดู log ใน Phase 7 |

## Success Metrics

| Metric | Target | How Measured |
|--------|--------|--------------|
| Active pilot users | 2-3 HR officers (100% ของ population), 1 เดือนต่อเนื่อง | Usage log + self-report |
| **รอบรวบรวมข้อมูล/ทำรายงาน** | **≤ 30 นาที (vs ~30 วัน Excel)** | User timing ก่อน/หลัง |
| Excel export usage | ≥ 3-4 ครั้ง/สัปดาห์ | Server log endpoint hit |
| Dashboard page load time | ≤ 2 วินาที | Browser performance API |
| Code coverage (backend API) | ≥ 60% lines on new code | PHPUnit --coverage |
| Test pass rate | 100% before each deploy | CI or local |
| API response p95 | ≤ 500ms | Server-side timing |

## Open Questions

- [ ] topzlab.com PHP version? (target: ≥ 8.1; verify before start)
- [ ] PDO extension enabled on topzlab.com? MySQL version?
- [ ] Max upload size, memory_limit, max_execution_time limits บน shared hosting?
- [ ] Excel template เดิมที่ HR ใช้ — pending review (user จะส่งให้)
- [ ] Seed data realism — gap analysis ไหน fit กับ workflow ใครจริง
- [ ] Concurrent users expected during pilot? (>10 = อาจต้อง session-handling เหนื่อย)
- [ ] Backup policy บน topzlab.com? (auto? manual?)
- [ ] Domain/subdomain สำหรับ production pilot — `topzlab.com/hr-budget`? หรือ `hr-budget.topzlab.com`?

---

## Users & Context

**Primary User**

- **Who**: เจ้าหน้าที่งบประมาณฝ่าย HR (ข้าราชการ age ~30-50)
- **Current behavior**: ทำคำขอใน Excel → ส่งไฟล์ผ่าน email/USB → ผู้อนุมัติ print + sign → scan กลับ
- **Trigger**: ตามรอบงบประมาณ (รายเดือน/ไตรมาส), คำสั่งจากผู้บริหารให้ทำรายงาน
- **Success state**: กดคลิกเดียวได้คำขอใหม่, เห็นสถานะ approve/reject ทันที, export Excel ให้ผู้บริหารในเมนูเดียว
- **Tech literacy**: คล่อง Word/Excel, ไม่คุ้นกับ web app ที่ซับซ้อน → UI ต้อง **flat, explicit, ไทย 100%**

**Job to Be Done**

> **เมื่อ** ต้องทำคำขอ/บันทึก/วิเคราะห์งบประมาณ
> **ฉันอยาก** ใช้ระบบดิจิทัลแทน Excel/กระดาษ
> **เพื่อให้** งานเสร็จเร็วขึ้น + เก็บข้อมูลไว้วิเคราะห์/คาดการณ์ได้ในอนาคต

**Non-Users (ไม่ design ให้)**

- ประชาชนทั่วไป / ผู้รับบริการภายนอก
- ข้าราชการกองอื่นที่ไม่ได้ทำงบประมาณโดยตรง
- External auditor (Phase หลัง + role separate)
- Mobile-first users (responsive OK แต่ไม่ optimize)
- Developer ภายนอก (ไม่เปิด public API)

---

## Solution Detail

### Core Capabilities (MoSCoW)

| Priority | Capability | Rationale |
|----------|------------|-----------|
| **Must** | Auth (**JWT**, 1 role: HR officer) | user เลือก JWT เพื่อเตรียมรับ mobile อนาคต |
| **Must** | **Password reset** flow (email token) | user ลืม password ต้องทำเองได้ |
| **Must** | **User management** (admin เพิ่ม/แก้/ลบ user) | seed 2-3 user ไม่พอ, ต้องมี flow add user |
| **Must** | **Fiscal year picker** (พ.ศ. Buddhist calendar) | ทุก query scope ด้วยปีงบ |
| **Must** | **Budget codes / หมวดงบประมาณ master data** | ไม่มี master data → สร้างคำขอไม่ได้ |
| **Must** | Budget Request CRUD (REST API + Vue UI) | Core MVP — validate hypothesis |
| **Must** | **Multi-level approval workflow** | HR จริงต้องส่ง ผอ. + รอง ผอ. + ผู้เซ็น |
| **Must** | **File upload (แนบเอกสาร)** | คำขอส่วนมากมีเอกสารประกอบ |
| **Must** | **In-app notification (bell)** | ผู้อนุมัติต้องเห็นคำขอใหม่ |
| **Must** | **Filter/search คำขอ** | List page ต้อง filter ตามสถานะ/ช่วงเวลา |
| **Must** | Dashboard (สถานะคำขอ + ยอดรวมกอง) | Validate "ดูข้อมูลไวกว่า Excel" |
| **Must** | Export Excel | Integration กับ workflow เดิม — export 3-4 ครั้ง/สัปดาห์ |
| **Must** | Basic audit log (write + view UI) | กัน "ใครลบคำขอของผม" + เตรียม PDPA (ย้ายมาจาก Could) |
| **Should** | Budget Execution (บันทึกการเบิก) | Feature หลัง MVP |
| **Should** | Disbursement + PO | หลัง Execution |
| **Could** | Advanced analytics (คาดการณ์งบ) | เมื่อ data ≥ 2 ปี |
| **Won't (v1)** | DPIS integration | รอ spec (mock adapter) |
| **Won't (v1)** | GFMIS integration | รอ approval (mock adapter) |
| **Won't (v1)** | Multi-org / multi-ministry | Out of pilot scope |
| **Won't (v1)** | Mobile native app | Desktop first (JWT ready for future) |
| **Won't (v1)** | Public API | Internal only |
| **Won't (v1)** | PDPA/consent UI | Add before go-live |

### MVP Scope

**Feature ที่ต้องครบ end-to-end ก่อน**: Budget Request Workflow

```
[Login] 
   ↓
[หน้ารายการคำขอของฉัน] ← Vue dashboard
   ↓ (คลิก "สร้างใหม่")
[Form สร้างคำขอ] → POST /api/v1/requests
   ↓
[Submit] → POST /api/v1/requests/{id}/submit
   ↓
[ผู้อนุมัติ เห็นใน notification] → GET /api/v1/requests?status=pending
   ↓ (คลิก approve/reject)
[อัปเดตสถานะ] → POST /api/v1/requests/{id}/approve|reject
   ↓
[เจ้าของคำขอเห็นสถานะ] ← auto-refresh dashboard
   ↓
[Export Excel] → GET /api/v1/requests/export.xlsx
```

### User Flow (Critical Path)

**เส้นทางที่ "เร็วสุดจาก login → value"**:

1. Login → Dashboard 
2. เห็นยอดรวมคำขอเดือนนี้ + สถานะ
3. กด "สร้างคำขอ" → form โผล่ (Vue component, ไม่ reload)
4. กรอก + submit → เห็น toast success ทันที
5. Dashboard refresh → คำขอใหม่โผล่ในตาราง
6. เวลารอ ~0.5 วินาที (ต่างจาก Excel ที่ต้อง save, close, attach email)

---

## Technical Approach

**Feasibility**: **HIGH** for backend refactor and Vue frontend, **LOW** for eventual integrations (deferred via mock adapters)

### Architecture Notes

```
┌─────────────────────────────────────────────────┐
│ Frontend (Vue 3 + TS)                            │
│  frontend/                                       │
│  ├─ src/                                         │
│  │  ├─ pages/       (BudgetRequestListPage.vue) │
│  │  ├─ components/  (shared UI)                 │
│  │  ├─ stores/      (Pinia: auth, requests)     │
│  │  ├─ router/      (Vue Router)                │
│  │  └─ composables/ (useApi, useAudit, etc.)    │
│  └─ dist/ ← build output (upload to topzlab)    │
└─────────────────────────────────────────────────┘
                        ↕ fetch JSON
┌─────────────────────────────────────────────────┐
│ Backend (PHP REST API — layered OOP)             │
│  src/                                            │
│  ├─ Core/           (EXISTING: Router, Database) │
│  ├─ Api/                                         │
│  │  ├─ Controllers/ (thin: parse request, call  │
│  │  │                service, return JSON)      │
│  │  ├─ Middleware/  (AuthMiddleware,            │
│  │  │                CorsMiddleware, etc.)      │
│  │  └─ Responses/   (JsonResponse helper)       │
│  ├─ Services/       (business logic, testable)  │
│  ├─ Repositories/   (PDO + SimpleQueryBuilder)  │
│  ├─ Dtos/           (typed request/response)    │
│  ├─ Entities/       (domain objects)            │
│  ├─ Adapters/                                   │
│  │  ├─ Dpis/   → mock impl + interface         │
│  │  └─ Gfmis/  → mock impl + interface         │
│  └─ Controllers/    (EXISTING MVC — legacy)     │
└─────────────────────────────────────────────────┘
                        ↕ PDO
┌─────────────────────────────────────────────────┐
│ MySQL (reuse 67 migrations เดิม)                 │
└─────────────────────────────────────────────────┘
```

### Key Design Decisions

- **Auth = JWT** (Firebase PHP-JWT library, stateless, future mobile-ready)
- **Query layer = extend `SimpleQueryBuilder`** (ไม่ Doctrine, low lock-in)
- **Response envelope**: `{ success: bool, data: T, error: string?, meta: {...}? }`
- **URL pattern**: `/api/v1/<resource>` + HTTP verbs (GET/POST/PUT/DELETE)
- **Status codes**: 2xx/4xx/5xx ตาม standard ไม่ใช่ always 200
- **Legacy MVC** coexists during transition — not deleted until feature ported end-to-end

### Technical Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| topzlab.com PHP version < 8.1 | M | Verify first; downgrade target if needed |
| Shared hosting no Docker = hard local/prod parity | M | Document deploy steps in `DEPLOYMENT.md`, use `.env` for all config |
| Strangler Fig legacy+new coexist confusion | M | Clear namespace separation (`App\Api\*` vs `App\Controllers\*`), no overlap in routes |
| Solo dev scope creep | H | Enforce Won't list strictly; review PRD before each phase |
| DPIS/GFMIS spec eventually arrives with breaking requirements | H | Design adapter interface day 1, swap impl later |
| Vue SPA + shared hosting routing (404 on refresh) | M | Configure `.htaccess` to fall through to `index.html` for non-`/api/*` paths |
| Session storage on shared hosting (limited disk) | L | Monitor, consider DB sessions if needed |
| Solo dev burnout | H | Milestone every ~2 weeks, celebrate MVP pilot |

---

## Implementation Phases

<!--
  STATUS: pending | in-progress | complete
  PARALLEL: phases that can run concurrently
  DEPENDS: phases that must complete first
  PRP: link to generated plan file once created
-->

**Timeline**: 3-4 วัน compressed MVP (Claude Code-assisted + user testing in-the-loop)

| # | Day | Phase | Deliverable | Status |
|---|-----|-------|-------------|--------|
| 1 | Day 1 | Foundation + Frontend bootstrap | API scaffold, JWT auth, Vue+Pinia+Router, 1 health endpoint, server-check.php | **complete** | [Plan](../plans/completed/day-1-foundation-frontend-bootstrap.plan.md) · [Report](../reports/day-1-foundation-frontend-bootstrap-report.md) |
| 2 | Day 2 | Core CRUD | Budget Request list/create/view/edit + single-level approval + basic audit log | **complete** | [Plan](../plans/completed/day-2-budget-request-core-crud.plan.md) · [Report](../reports/day-2-budget-request-core-crud-report.md) |
| 3 | Day 3 | Master data + UX | Fiscal year, Budget codes, User mgmt, File upload, Notifications, Filter/search | pending |
| 4 | Day 4 | Polish + Deploy | Multi-level approval, Password reset, Dashboard, Excel export, Audit UI, deploy topzlab | pending |

### Day-by-Day Plan

**Day 1: Foundation** *(~6-8 ชม.)*
- **Goal**: API scaffold + Vue shell ทำงาน, login ได้ end-to-end
- **Scope**:
  - `server-check.php` (upload to topzlab → verify PHP/MySQL version)
  - Backend: `src/Api/` structure, JWT middleware, DTO base, response envelope, error handler
  - Frontend: Vue 3 + TS + Vite + Pinia + Vue Router + Tailwind setup
  - 1 endpoint: `POST /api/v1/auth/login`, `GET /api/v1/auth/me`
  - Vue: login page + dashboard placeholder with auth guard
- **Success signal**: `npm run dev` + backend PHP serve → login → redirect → เห็นชื่อ user

**Day 2: Budget Request Core CRUD** *(~8 ชม.)*
- **Goal**: Budget Request feature ครบ single-level workflow
- **Scope**:
  - REST API: Controllers + Services + Repositories + DTOs for BudgetRequest(+Items)
  - Endpoints: `GET/POST/PUT/DELETE /api/v1/requests`, `POST /api/v1/requests/{id}/submit|approve|reject`
  - Vue pages: list, create form, detail, approval actions
  - Basic audit log writer (middleware)
- **Success signal**: user สร้าง → submit → approver เห็น → approve → เจ้าของเห็นสถานะ

**Day 3: Master data + Support features** *(~8 ชม.)*
- **Goal**: ระบบรองรับ context จริง (ปีงบ, หมวดงบ, user, ไฟล์, แจ้งเตือน)
- **Scope**:
  - Master data: fiscal years, budget codes, users — CRUD (admin) + picker (user)
  - File upload endpoint + Vue uploader (validator, size/type limits)
  - In-app notifications: unread count, bell dropdown, mark-read API
  - Filter/search: list page filters (status, date range, fiscal year)
- **Success signal**: เลือกปีงบ → ดูคำขอในปีนั้น + แนบไฟล์ได้ + bell แจ้งเตือนเมื่อมี approve เข้า

**Day 4: Polish + Deploy** *(~8 ชม.)*
- **Goal**: Pilot-ready — deploy topzlab.com, ทดสอบกับ seed data
- **Scope**:
  - Multi-level approval workflow (approval chain config)
  - Password reset flow (email token — หรือ skip email ใช้ copy-paste link ช่วง pilot)
  - Dashboard + Chart.js metrics
  - Excel export (PhpSpreadsheet)
  - Audit log viewer UI
  - Deploy: upload backend to `topzlab.com/api/`, frontend `dist/` to `topzlab.com/`
  - Seed data: 2-3 users, 1 fiscal year, 10-20 sample requests
- **Success signal**: เปิดเว็บจริง → login → ทำ workflow ครบ → export Excel ได้

### Parallelism Notes

- **Day 1 AM/PM**: Backend foundation (AM) + Frontend bootstrap (PM) — ลำดับ, ไม่ parallel เพราะ solo dev
- **Day 2**: Single track — Budget Request เป็น vertical slice (backend + frontend + test together)
- **Day 3-4**: Feature-by-feature

### Contingency

ถ้า end-of-day 2 เห็นว่าติด:
- **Green zone**: timeline holds → Day 3-4 ตามแผน
- **Yellow zone**: เสีย 0.5-1 วัน → ตัด: multi-level approval → single-level ก่อน (Phase หลัง), audit UI → viewer ภายใน 3 fields
- **Red zone**: เสีย > 1 วัน → ตัด: file upload, password reset (email flow), notification → flag "Phase post-MVP"

---

## Decisions Log

| Decision | Choice | Alternatives | Rationale |
|----------|--------|--------------|-----------|
| Backend auth | **JWT** (Firebase PHP-JWT) | Session cookie, OAuth | User ต้องการเตรียมรับ mobile อนาคต; JWT stateless = scale ได้ง่าย |
| DB layer | PDO + extended SimpleQueryBuilder | Doctrine ORM, Eloquent standalone | Reuse migrations 67 ตัว, learning curve ต่ำ, performance ดี, low lock-in |
| Frontend framework | Vue 3 + TypeScript | Vue JS, React, htmx, Inertia, vanilla | User chose Vue + TS; TS type-safety สำคัญสำหรับ "handoff ทีม" |
| Deploy target | topzlab.com shared hosting | Laragon-only local, Render.com, self-hosted VPS | User chose + zero budget |
| Migration strategy | Strangler Fig (feature-by-feature) | Big bang rewrite, parallel rewrite | Lower risk for solo dev, ไม่ต้อง stop feature delivery |
| Integration to DPIS/GFMIS | Mock adapter + interface | Skip entirely, หรือเริ่มจากจริง | Future-proof without blocking on external team |
| Database schema changes | Keep existing 67 migrations | Rewrite from scratch | Save weeks of work, data shape already validated |
| API versioning | `/api/v1/*` prefix | No version / header-based | Simple, easy to deprecate later |
| Response shape | `{success, data, error, meta}` envelope | Raw JSON, JSend, JSON:API | Consistent, easy for Vue to destructure |
| HTTP verb discipline | GET/POST/PUT/DELETE ตรง ๆ | POST-only (เหมือน legacy) | REST convention — tooling (Postman, Swagger) ใช้ง่าย |
| Testing | PHPUnit (backend) + Vitest (frontend) | Jest, Pest, no tests | Reuse existing PHPUnit; Vitest standard สำหรับ Vite projects |
| Audit log | Write-only in MVP, UI in Phase 8 | Full audit UI in MVP | Scope management — write gives safety, UI nice-to-have |

---

## Research Summary

### Market Context

- **Government internal budget systems** ในประเทศไทยส่วนมากเป็น:
  - Legacy VB6/ASP classic
  - Custom PHP + MySQL (CodeIgniter / native)
  - GFMIS (กระทรวงการคลัง) = centralized on-prem system
  - DPIS (HR data) = central system with limited API
- **Modern OSS budget tools** (OpenBudget, etc.) ส่วนใหญ่ออกแบบสำหรับ municipality budgets, ไม่ค่อย fit กับ workflow ราชการไทย
- **Industry trend**: SPA + REST API แยก เป็น standard สำหรับ new builds, monolith rewrites ลดลง

### Technical Context

**จาก `hr_budget` (existing codebase)**:
- `src/Core/Router.php`:59-74 — regex-based param extraction, `_method` POST override, script-prefix awareness (ยังใช้ได้ใน REST refactor)
- `src/Core/Database.php`:22-36 — singleton PDO, easy to extend
- `src/Core/SimpleQueryBuilder.php` — fluent interface ที่ขยายเพิ่มได้ไม่ต้องลง Doctrine
- 67 migrations ใน `database/migrations/` — schema stable
- Existing domain models: BudgetRequest(+Item,+Approval), BudgetExecution/Record/Tracking, Disbursement, POCommitment, ApprovalSetting, Approver

**จาก `smart-port` (reference project)**:
- Vue 3 + Pinia + Vue Router pattern เรียบร้อย — component library (StatCard, EmptyState, SkeletonLoader, PaginationBar) ใช้ซ้ำได้
- JWT hand-rolled — ไม่ควรก็อปปี้ (ใช้ library + session แทน)
- Backend switch/case routing — anti-pattern ไม่ควรทำตาม
- CORS + allowed origins — reference pattern

**External References**:
- PSR-7 (HTTP message), PSR-15 (middleware), PSR-11 (container), PSR-4 (autoload)
- Firebase PHP-JWT (ถ้าต้อง JWT ในอนาคต)
- PhpSpreadsheet (Excel export — มีใน composer.json อยู่แล้ว: `phpoffice/phpspreadsheet`)
- Vue 3 Composition API + `<script setup>` — modern
- Vitest — standard test runner for Vite

---

*Generated: 2026-04-17*
*Status: DRAFT — needs validation via pilot*
