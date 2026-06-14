# Plan: Phase 5 — Budget Tracking + Disbursements (Core Tracking Flow)

## Summary
Replace the legacy server-rendered, redirect-driven "budget tracking" disbursement flow with a clean, stateless JSON REST API (`/api/v1/disbursement-*`) and a Vue SPA multi-step wizard. The flow records monthly disbursement amounts (allocated / transfer / disbursed / pending / po) per expense item, scoped to an organization + fiscal year + month → activity. Scope is the **core tracking flow only** (sessions → activities → records → tracking amounts); the legacy `DisbursementController` header/detail CRUD and the execution-dashboard export are explicitly deferred.

## User Story
As an HR division staff member, I want to record monthly budget disbursement amounts through a guided multi-step form that validates as I type and never loses my place to a page reload or expired session, so I can finish monthly tracking quickly and accurately.

## Problem → Solution
**Current:** `BudgetController` drives tracking via HTML form POSTs + redirects, passing state through URL query params (`session_id`, `record_id`) and rendering expense tabs server-side. Coupled to PHP views; no inline validation; not consumable by the SPA.
**Desired:** Stateless JSON endpoints expose create-or-fetch session, activity list, create-or-fetch record, record detail (with existing amounts), and save (upsert trackings). A Pinia-backed wizard holds draft state client-side; TanStack Query handles server cache/invalidation.

## Metadata
- **Complexity**: Large
- **Source PRD**: `.claude/PRPs/prds/vue-spa-refactor.prd.md`
- **PRD Phase**: Phase 5 — Budget Tracking + Disbursements
- **Scope decision (user, 2026-06-14)**: Core tracking flow only
- **Estimated Files**: ~23 (13 backend, 9 frontend, 1 e2e)

---

## UX Design

### Before
```
/budgets/tracking/create  (PHP form: pick FY+month+org)
   → POST store-session  → 302 redirect → /activities?session_id=X
   → pick activity (checkbox) → POST → 302 → /{record}/form?type_id=Y
   → fill HTML tabs → POST /{record}/save → 302 redirect (full reload each step)
```

### After
```
/disbursements  (SPA list of sessions: FY / month / org / status)
   └ "+ บันทึกการเบิกจ่าย"
       Step 1  เลือกหน่วยงาน + ปีงบ + เดือน      → POST /disbursement-sessions (idempotent)
       Step 2  เลือกกิจกรรม                       → GET  /disbursement-sessions/{id}/activities
                                                   → POST /disbursement-records (idempotent)
       Step 3  กรอกยอด (allocated/transfer/...)   → GET  /disbursement-records/{id}  +  GET /expense-structure
                                                   → PUT  /disbursement-records/{id}  (upsert + status=completed)
       Step 4  สรุป → กลับสู่รายการ (no reload)
```

### Interaction Changes
| Touchpoint | Before | After | Notes |
|---|---|---|---|
| Navigation between steps | Full page reload + 302 | Client-side wizard step | Pinia holds draft |
| Amount entry | Submit-then-see-error | Inline validation (vee-validate/zod) | per-row numeric ≥ 0 |
| Resume | Re-fetch via URL params | TanStack Query cache + record detail | idempotent create-or-fetch |
| State storage | URL params + DB | Pinia (draft) + DB (persisted) | zero `$_SESSION` |

---

## Mandatory Reading

| Priority | File | Lines | Why |
|---|---|---|---|
| P0 | `src/Controllers/BudgetController.php` | 455–889 | Source logic: storeSession / activities / createRecord / disbursementForm / saveDisbursement / deleteSession to port |
| P0 | `src/Api/Controllers/BudgetRequestController.php` | 1–244 | Controller pattern (CORS→Auth→DTO→Service→ApiResponse, sub-actions, try/catch) |
| P0 | `src/Services/BudgetRequestService.php` | 32–169 | Service pattern: repo injection, role/state gating, `Database::beginTransaction/commit/rollback` |
| P0 | `src/Repositories/BudgetRequestRepository.php` | 11–90 | Repo pattern: `Database::query/queryOne`, `applyFilters`, pagination, whitelist update |
| P0 | `src/Dtos/CreateFiscalYearDto.php` + `UpdateFiscalYearDto.php` + `BudgetRequestListQueryDto.php` | all | DTO `fromRequest/fromQueryString` + `validate()` + `toFilters/offset` |
| P1 | `src/Core/Database.php` | all | `query/queryOne/insert/update/delete/beginTransaction`, `setInstance/resetInstance` for tests |
| P1 | `tests/Unit/Services/FiscalYearServiceTest.php` | all | SQLite in-memory test scaffold (setUp/tearDown) |
| P1 | `tests/Unit/Dtos/FiscalYearDtoTest.php` | all | DTO unit test shape |
| P0 | `frontend/src/queries/useBudgetRequests.ts` | 1–127 | TanStack pattern: KEY arrays, computed reactive `queryKey`, mutation invalidation |
| P0 | `frontend/src/api/budgetRequests.ts` | 1–82 | `apiFetch` usage, URLSearchParams for GET, JSON body for POST/PUT |
| P0 | `frontend/src/composables/useApi.ts` | 10–56 | `/api/v1` prefix, `X-Requested-With`, credentials, 401→logout, 204 handling |
| P0 | `frontend/src/pages/RequestCreatePage.vue` + `components/ItemEditor.vue` | all | Dynamic line-item form + mutation + toast + navigation |
| P1 | `frontend/src/stores/auth.ts` | 1–87 | Pinia store shape (defineStore + refs + computed + actions) |
| P1 | `frontend/src/router/index.ts` | 4–140 | Route declaration (`meta.title`/`requiresAdmin`) + guards |
| P1 | `frontend/src/layouts/AppLayout.vue` | 75–136 | Sidebar nav links; where to add "บันทึกการเบิกจ่าย" |
| P1 | `tests/e2e/budget-request-workflow.spec.ts` | all | E2E login helper + selector/assertion style |
| P2 | `database/hr_budget_only.sql` | 612–665, 816–871, 880–988, 1658–1687 | budget_trackings / disbursement_* / expense_* / source_of_truth_mappings schema + seed |

## External Documentation
| Topic | Source | Key Takeaway |
|---|---|---|
| TanStack Query (Vue) reactive keys | internal exemplar (Phase 4) | Wrap key in `computed(() => [...KEY, toValue(x)])`; `enabled: () => toValue(id) > 0` |
| Pinia store | internal `stores/auth.ts` | `defineStore('disbursementWizard', () => { refs + computed + actions; return {...} })` |

No external research needed — established internal patterns.

---

## Patterns to Mirror

### CONTROLLER (CORS → Auth → DTO → Service → ApiResponse + try/catch)
```php
// SOURCE: src/Api/Controllers/BudgetRequestController.php:1-60,156-211
public function create(): void
{
    CorsMiddleware::apply();
    $user = AuthMiddleware::require();
    try {
        $dto = CreateDisbursementSessionDto::fromRequest();
        $errors = $dto->validate();
        if (!empty($errors)) { ApiResponse::validationFailed($errors); return; }
        $session = $this->service->createOrFetchSession($user['role'] ?? 'staff', (int)$user['id'], $dto);
        ApiResponse::created($session);
    } catch (\Throwable $e) {
        error_log("[DisbursementSessionController::create] {$e->getMessage()}");
        ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
    }
}
```

### SERVICE (repo injection, role gate, atomic transaction)
```php
// SOURCE: src/Services/BudgetRequestService.php:57-90,115-169
public function __construct(
    private readonly DisbursementSessionRepository $sessionRepo = new DisbursementSessionRepository(),
    private readonly DisbursementRecordRepository $recordRepo = new DisbursementRecordRepository(),
    private readonly ExpenseStructureRepository $expenseRepo = new ExpenseStructureRepository(),
) {}
// transaction:
Database::beginTransaction();
try { /* upsert each item; updateStatus completed */ Database::commit(); return true; }
catch (\Throwable $e) { Database::rollback(); return false; }
```

### REPOSITORY (Database static, applyFilters, pagination, portable upsert)
```php
// SOURCE: src/Repositories/BudgetRequestRepository.php:11-26 (list+count)
// Portable UPSERT (NO MySQL ON DUPLICATE KEY — must work on SQLite for unit tests):
public function upsertTracking(array $row): void {
    $existing = Database::queryOne(
        "SELECT id FROM budget_trackings WHERE disbursement_record_id = ? AND expense_item_id = ?",
        [$row['disbursement_record_id'], $row['expense_item_id']]
    );
    if ($existing) {
        Database::update('budget_trackings',
            ['allocated'=>$row['allocated'],'transfer'=>$row['transfer'],'disbursed'=>$row['disbursed'],
             'pending'=>$row['pending'],'po'=>$row['po']],
            'id = ?', [$existing['id']]);
    } else {
        Database::insert('budget_trackings', $row); // budget_category_item_id stays NULL
    }
}
```

### DTO (fromRequest JSON body, validate → field=>msg map, Thai messages)
```php
// SOURCE: src/Dtos/CreateFiscalYearDto.php + BudgetRequestItemDto.php
public static function fromRequest(): self { /* json_decode(php://input) → new self(...) */ }
public function validate(): array { $e=[]; if ($this->month<1||$this->month>12) $e['record_month']='เดือนต้องอยู่ระหว่าง 1-12'; return $e; }
```

### TanStack Query (computed key, throw on !success, invalidate)
```ts
// SOURCE: frontend/src/queries/useBudgetRequests.ts:20-57
const KEY = ['disbursements'] as const
export function useDisbursementSessions(filters: MaybeRef<SessionFilters> = {}) {
  return useQuery({
    queryKey: computed(() => [...KEY, 'sessions', toValue(filters)]),
    queryFn: async () => { const res = await fetchSessions(toValue(filters)); if (!res.success||!res.data) throw new Error(res.error ?? 'โหลดไม่สำเร็จ'); return { data: res.data, meta: res.meta ?? null } },
  })
}
// mutation: onSuccess: () => qc.invalidateQueries({ queryKey: KEY })
```

### Pinia draft store (UI-only state for the wizard)
```ts
// SOURCE: frontend/src/stores/auth.ts (defineStore composition form)
export const useDisbursementWizard = defineStore('disbursementWizard', () => {
  const step = ref(1)
  const session = ref<DisbursementSession | null>(null)
  const record = ref<DisbursementRecord | null>(null)
  const amounts = ref<Record<number, TrackingAmount>>({}) // keyed by expense_item_id
  function reset() { step.value = 1; session.value = null; record.value = null; amounts.value = {} }
  return { step, session, record, amounts, reset }
})
```

### TEST (SQLite in-memory, setInstance/resetInstance)
```php
// SOURCE: tests/Unit/Services/FiscalYearServiceTest.php
protected function setUp(): void { $this->pdo = new \PDO('sqlite::memory:'); $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); Database::setInstance($this->pdo); $this->pdo->exec("CREATE TABLE ..."); }
protected function tearDown(): void { Database::resetInstance(); }
```

---

## Files to Change

### Backend (13)
| File | Action | Justification |
|---|---|---|
| `src/Dtos/CreateDisbursementSessionDto.php` | CREATE | validate org/fiscal_year/month/date |
| `src/Dtos/DisbursementSessionListQueryDto.php` | CREATE | list filters + pagination |
| `src/Dtos/CreateDisbursementRecordDto.php` | CREATE | validate session_id + activity_id |
| `src/Dtos/SaveTrackingItemsDto.php` | CREATE | validate items[] (expense_item_id, amounts ≥ 0) |
| `src/Repositories/DisbursementSessionRepository.php` | CREATE | sessions CRUD + findByOrgYearMonth + activities-for-session (incl. source_of_truth filter + has_record) + cascade delete |
| `src/Repositories/DisbursementRecordRepository.php` | CREATE | records create-or-fetch, detail join, trackings read + portable upsert, status transition |
| `src/Repositories/ExpenseStructureRepository.php` | CREATE | types→groups→items reference tree + resolve item→group/type ids |
| `src/Services/DisbursementService.php` | CREATE | orchestrate flow, role/org gating, transactions, remaining calc |
| `src/Api/Controllers/DisbursementSessionController.php` | CREATE | list/create/show/delete/activities |
| `src/Api/Controllers/DisbursementRecordController.php` | CREATE | create/show/update + expenseStructure |
| `routes/web.php` | UPDATE | register 9 `/api/v1/disbursement-*` + `/expense-structure` routes |
| `tests/Unit/Services/DisbursementServiceTest.php` | CREATE | idempotency, upsert, status, org gating, cascade |
| `tests/Unit/Dtos/DisbursementDtoTest.php` | CREATE | DTO validation |

### Frontend (9)
| File | Action | Justification |
|---|---|---|
| `frontend/src/types/disbursement.ts` | CREATE | DisbursementSession/Record/Activity/ExpenseNode/TrackingAmount + filters |
| `frontend/src/api/disbursements.ts` | CREATE | typed apiFetch wrappers for 9 endpoints |
| `frontend/src/queries/useDisbursements.ts` | CREATE | TanStack queries + mutations |
| `frontend/src/stores/disbursementWizard.ts` | CREATE | Pinia draft state |
| `frontend/src/pages/DisbursementListPage.vue` | CREATE | sessions list + "+ บันทึก" |
| `frontend/src/pages/DisbursementWizardPage.vue` | CREATE | 4-step wizard |
| `frontend/src/router/index.ts` | UPDATE | add `/disbursements` + `/disbursements/wizard` routes |
| `frontend/src/layouts/AppLayout.vue` | UPDATE | sidebar link "บันทึกการเบิกจ่าย" |
| `tests/e2e/disbursement-tracking-workflow.spec.ts` | CREATE | full flow e2e |

## NOT Building
- Legacy `DisbursementController` header/detail CRUD (`/budgets/disbursements/*`) — separate older alternative; deferred.
- Execution dashboard / Excel export (`BudgetExecutionController`) — already has a read endpoint via DashboardService; out of scope.
- Deleting the legacy `/budgets/tracking/*` web routes/views — PRD keeps web views as reference spec until **Phase 6** cutover. (We do NOT touch `src/Controllers/BudgetController.php`.)
- New DB migration — all tables (`disbursement_sessions`, `disbursement_records`, `budget_trackings`, `expense_*`) already exist with seed.
- `budget_transactions` audit writes — legacy `update()` audit trail, not part of the tracking wizard.
- Hierarchical `is_header`/`parent_id` deep expense tree editing — render flat active leaf items grouped by type/group; respect `is_header` for display only.

---

## Step-by-Step Tasks

### Task 1: DTOs
- **ACTION**: Create the 4 DTO files.
- **IMPLEMENT**:
  - `CreateDisbursementSessionDto(organizationId:int, fiscalYear:int, recordMonth:int, recordDate:string)`; validate: org>0, fiscalYear 2400–2700, month 1–12, recordDate `Y-m-d` (default today if empty).
  - `DisbursementSessionListQueryDto(fiscalYear?, organizationId?, recordMonth?, page=1, perPage=20)`; `offset()`, `toFilters()`.
  - `CreateDisbursementRecordDto(sessionId:int, activityId:int)`; validate both > 0.
  - `SaveTrackingItemsDto(items: array<{expenseItemId:int, allocated, transfer, disbursed, pending, po}>)`; `fromRequest()` reads `items` array; validate: non-empty, each expenseItemId>0, each amount numeric & ≥ 0 (store as string for decimal fidelity, like `BudgetRequestItemDto`).
- **MIRROR**: `CreateFiscalYearDto`, `BudgetRequestItemDto`, `BudgetRequestListQueryDto`.
- **GOTCHA**: Amounts must accept "0"/empty → coerce to "0.00"; keep numeric strings (avoid float drift) — mirror `BudgetRequestItemDto` numeric handling.
- **VALIDATE**: `tests/Unit/Dtos/DisbursementDtoTest.php` passes.

### Task 2: ExpenseStructureRepository
- **ACTION**: Create repo returning the reference tree + an item→ids resolver.
- **IMPLEMENT**:
  - `tree(): array` → expense_types (is_active=1) each with `groups` (expense_groups where expense_type_id, is_active=1, deleted_at IS NULL) each with `items` (expense_items where expense_group_id, is_active=1, deleted_at IS NULL ORDER BY sort_order, id). Include `is_header`, `level`, `code`, `name_th`.
  - `resolveItem(int $expenseItemId): ?array` → returns `{expense_item_id, expense_group_id, expense_type_id}` (read from expense_items row; fall back via group if type null).
- **MIRROR**: `FiscalYearRepository` query style.
- **GOTCHA**: `expense_items.expense_type_id` can be NULL → resolve via the group's `expense_type_id`.
- **VALIDATE**: Service test asserts tree non-empty & resolveItem(15) → group 1/type 1.

### Task 3: DisbursementSessionRepository
- **ACTION**: Create repo for sessions + activities.
- **IMPLEMENT**:
  - `findAll(filters,limit,offset)` + `count(filters)` joining organizations (`o.name_th`); ORDER BY fiscal_year DESC, record_month DESC, id DESC.
  - `findById(id)`, `findByOrgYearMonth(org,fy,month)`.
  - `insert(['organization_id','fiscal_year','record_month','record_date','created_by'])`.
  - `activitiesForSession(int $orgId, int $fiscalYear, int $sessionId)`: if rows exist in `source_of_truth_mappings` for (organization_id=org, fiscal_year=fy, is_official=1) → return those activities (JOIN activities a ON a.id = m.activity_id), else all `activities` where `fiscal_year = fy AND is_active=1 AND deleted_at IS NULL`. LEFT JOIN `disbursement_records dr ON dr.activity_id=a.id AND dr.session_id=?` to expose `record_id` + `record_status` (has_record). Return `{activity_id, code, name_th, plan_name?, project_name?, record_id, record_status}`.
  - `deleteCascade(int $sessionId)`: DELETE budget_trackings WHERE disbursement_record_id IN (SELECT id FROM disbursement_records WHERE session_id=?); DELETE disbursement_records WHERE session_id=?; DELETE disbursement_sessions WHERE id=?. (Run inside a transaction in the Service.)
- **MIRROR**: `BudgetRequestRepository::findAll/applyFilters`.
- **GOTCHA**: Confirm `source_of_truth_mappings` columns at line 1658 of the dump before writing the JOIN (seed row: fy=2569, org=3, …, activity_id=31, is_official=1). For SQLite tests, the subquery DELETE form is portable (avoid multi-table DELETE JOIN syntax).
- **VALIDATE**: Service test: activities for org3/fy2569 returns activity 31 with has_record flag.

### Task 4: DisbursementRecordRepository
- **ACTION**: Create repo for records + trackings.
- **IMPLEMENT**:
  - `findById(id)`, `findBySessionAndActivity(sessionId, activityId)`, `insert(['session_id','activity_id'])` (status defaults 'draft').
  - `updateStatus(id, status)`.
  - `trackingsByRecord(recordId): array` (rows keyed later by expense_item_id in service).
  - `upsertTracking(array $row)`: portable SELECT-then-update/insert on `(disbursement_record_id, expense_item_id)` (see Patterns). Insert sets `disbursement_record_id, activity_id, expense_type_id, expense_group_id, expense_item_id, fiscal_year, record_month, organization_id, allocated, transfer, disbursed, pending, po` (leave `budget_category_item_id, budget_type_id, plan_id, project_id` NULL — matches seed row 57).
- **MIRROR**: portable upsert pattern above; `FiscalYearRepository::update` whitelist.
- **GOTCHA**: Do NOT use `ON DUPLICATE KEY UPDATE` (breaks SQLite unit tests). Keep `budget_category_item_id` NULL so `unique_tracking(fiscal_year, budget_category_item_id)` never collides across records.
- **VALIDATE**: Service test: first save inserts, second save updates same row (no duplicate).

### Task 5: DisbursementService
- **ACTION**: Orchestrate the flow with role/org gating + transactions.
- **IMPLEMENT**:
  - `listSessions(role,userId,query)`: non-admin → force `organization_id = user's org`. Returns `{data, meta}`.
  - `createOrFetchSession(role,userId,dto)`: non-admin → override `organizationId` with the user's org (ignore body). `findByOrgYearMonth` → return existing else insert (created_by=userId) and return fresh row. Idempotent.
  - `getSession(id)`.
  - `deleteSession(role,userId,id)`: load session; non-admin must own org; wrap `deleteCascade` in `beginTransaction/commit` (rollback+false on error).
  - `getActivities(sessionId)`: load session → `activitiesForSession(org,fy,sessionId)`.
  - `createOrFetchRecord(sessionId,activityId)`: validate session exists; `findBySessionAndActivity` → existing else insert. Idempotent.
  - `getRecordDetail(recordId)`: record + its session + activity + `trackings` keyed by `expense_item_id` (each with computed `remaining = allocated + transfer - (disbursed + pending + po)` via a private `calcRemaining`).
  - `saveRecordItems(recordId,dto)`: load record+session (for fiscal_year, record_month, organization_id, activity_id). `beginTransaction`; for each item → `resolveItem` for group/type ids → `upsertTracking`; `updateStatus(recordId,'completed')`; `commit`. Return true; rollback+false on error.
  - `expenseStructure()`: delegate to `ExpenseStructureRepository::tree()`.
- **MIRROR**: `BudgetRequestService` role gating + transaction structure.
- **GOTCHA**: `calcRemaining` uses `bcsub/bcadd` or float? Mirror `BudgetRequestItemDto::amount()` which uses `bcmul` — use `bcadd/bcsub` with scale 2 to match decimal columns. Coerce DB string amounts before bc ops.
- **VALIDATE**: All service tests green (idempotency, upsert, status='completed', cascade delete, non-admin org override).

### Task 6: API Controllers + routes
- **ACTION**: Create 2 controllers; register routes.
- **IMPLEMENT**:
  - `DisbursementSessionController`: `list()` (GET), `create()` (POST→201), `show($id)` (GET→404 if null), `delete($id)` (DELETE→`noContent` or 403/404), `activities($id)` (GET).
  - `DisbursementRecordController`: `create()` (POST→201, validate session+activity exist), `show($id)` (GET→404), `update($id)` (PUT, validate SaveTrackingItemsDto, 422 on errors, returns refreshed detail), `expenseStructure()` (GET).
  - `routes/web.php` (in the `/api/v1` block, static before parameterized):
    ```php
    Router::get('/api/v1/expense-structure', [ApiDisbursementRecordController::class, 'expenseStructure']);
    Router::get('/api/v1/disbursement-sessions', [ApiDisbursementSessionController::class, 'list']);
    Router::post('/api/v1/disbursement-sessions', [ApiDisbursementSessionController::class, 'create']);
    Router::get('/api/v1/disbursement-sessions/{id}/activities', [ApiDisbursementSessionController::class, 'activities']);
    Router::get('/api/v1/disbursement-sessions/{id}', [ApiDisbursementSessionController::class, 'show']);
    Router::delete('/api/v1/disbursement-sessions/{id}', [ApiDisbursementSessionController::class, 'delete']);
    Router::post('/api/v1/disbursement-records', [ApiDisbursementRecordController::class, 'create']);
    Router::get('/api/v1/disbursement-records/{id}', [ApiDisbursementRecordController::class, 'show']);
    Router::put('/api/v1/disbursement-records/{id}', [ApiDisbursementRecordController::class, 'update']);
    ```
- **MIRROR**: `BudgetRequestController` + route registration block; alias imports as `ApiDisbursement*Controller` like existing `ApiBudgetRequestController`.
- **GOTCHA**: `/disbursement-sessions/{id}/activities` must be registered BEFORE `/disbursement-sessions/{id}` so the more specific route wins (Router matches in registration order).
- **VALIDATE**: Manual curl smoke (login → create session → activities → create record → PUT items → GET record shows amounts).

### Task 7: Frontend types + api client
- **ACTION**: Create `types/disbursement.ts` + `api/disbursements.ts`.
- **IMPLEMENT**: Types mirror API JSON. `api/disbursements.ts`: `fetchSessions(filters)`, `createSession(body)`, `fetchSession(id)`, `deleteSession(id)`, `fetchActivities(sessionId)`, `createRecord(body)`, `fetchRecord(id)`, `saveRecord(id, {items})`, `fetchExpenseStructure()` — all via `apiFetch`, GET filters via URLSearchParams.
- **MIRROR**: `frontend/src/api/budgetRequests.ts`, `frontend/src/types/budget-request.ts`.
- **VALIDATE**: `vue-tsc -b` clean.

### Task 8: TanStack queries + Pinia wizard store
- **ACTION**: Create `queries/useDisbursements.ts` + `stores/disbursementWizard.ts`.
- **IMPLEMENT**:
  - Queries: `useDisbursementSessions(filters)`, `useDisbursementSession(id)`, `useSessionActivities(sessionId)`, `useDisbursementRecord(id)`, `useExpenseStructure()` (long `staleTime` — reference data). Mutations: `useCreateSession`, `useDeleteSession`, `useCreateRecord`, `useSaveRecord` — invalidate `['disbursements']` (+ the record/session keys).
  - Pinia store: draft step/session/record/amounts + `reset()`; `amounts` keyed by `expense_item_id`.
- **MIRROR**: `useBudgetRequests.ts` (computed keys, `enabled: () => toValue(id) > 0`), `stores/auth.ts`.
- **GOTCHA**: `useExpenseStructure` reference data → `staleTime: Infinity` (or large) to avoid refetch churn across wizard steps.
- **VALIDATE**: `vue-tsc -b` clean.

### Task 9: List + Wizard pages, router, sidebar
- **ACTION**: Create `DisbursementListPage.vue`, `DisbursementWizardPage.vue`; wire router + sidebar.
- **IMPLEMENT**:
  - List page: table of sessions (org / ปีงบ / เดือน / วันที่บันทึก / actions: ดู/ลบ) + "+ บันทึกการเบิกจ่าย" → wizard. Dark theme, exactly one `<h1>` ("บันทึกการเบิกจ่ายงบประมาณ").
  - Wizard: 4 steps driven by Pinia `step`. Step 1 select org (admin)/fixed-own (staff) + fiscal year (from `useFiscalYearList`) + month (1–12 Thai labels). Step 2 activity list (radio) → create record. Step 3 expense structure grouped by type→group, numeric inputs (allocated/transfer/disbursed/pending/po) per active leaf item, prefilled from record detail; show live `remaining`. Step 4 summary + "บันทึก" (PUT) → toast → router back to list, `wizard.reset()`.
  - Router: `{ path: 'disbursements', name:'disbursements', component: DisbursementListPage, meta:{title:'บันทึกการเบิกจ่าย'} }`, `{ path:'disbursements/wizard', name:'disbursement-wizard', component: DisbursementWizardPage, meta:{title:'บันทึกการเบิกจ่าย'} }` (authed, NOT admin-only — staff record their own).
  - Sidebar: add link between "คำของบประมาณ" and the admin section in `AppLayout.vue`.
- **MIRROR**: `RequestCreatePage.vue` (form+mutation+toast+nav), `RequestListPage.vue` (table+filters), `ItemEditor.vue` (dynamic numeric rows).
- **GOTCHA**: AppLayout topbar title is a `<div>`, not a heading — page owns the single `<h1>`. Wrap all internal links/routes the Vue-router way (not `View::url`).
- **VALIDATE**: `npm run build` succeeds; manual click-through in dev.

### Task 10: E2E
- **ACTION**: Create `tests/e2e/disbursement-tracking-workflow.spec.ts`.
- **IMPLEMENT**: login as admin → goto `/disbursements` → "+ บันทึก" → Step1 pick org (กระทรวงยุติธรรม / org 3), fiscal year 2569, **month 11** (avoid clashing with seed session month 10) → next → Step2 pick activity (31 / `บริหารจัดการสินค้าคงคลัง`) → next → Step3 fill one item's `disbursed`=100, `allocated`=100 → next → Step4 "บันทึก" → assert success toast + list shows the new session row. Re-run safe (create-or-fetch idempotent).
- **MIRROR**: `tests/e2e/budget-request-workflow.spec.ts` login helper + selectors.
- **GOTCHA**: Use resilient selectors (placeholder/role/text). The create-or-fetch idempotency makes the spec rerun-safe locally; CI reseeds fresh anyway.
- **VALIDATE**: `npm run test:e2e -- disbursement-tracking-workflow` green locally.

---

## Testing Strategy

### Unit Tests
| Test | Input | Expected | Edge? |
|---|---|---|---|
| createOrFetchSession new | org/fy/month not existing | inserts, returns row | |
| createOrFetchSession dup | same org/fy/month twice | returns SAME id (no dup) | ✔ idempotent |
| non-admin org override | staff posts other org | session uses staff's own org | ✔ authz |
| createOrFetchRecord dup | same (session,activity) twice | same record id | ✔ idempotent |
| saveRecordItems insert→update | save then re-save same item | one tracking row, updated amounts | ✔ upsert |
| saveRecordItems status | after save | record status='completed' | |
| getRecordDetail remaining | allocated 100, disbursed 30 | remaining=70.00 | ✔ calc |
| deleteSession cascade | session w/ record+trackings | all three tables cleared | ✔ cascade |
| DTO month out of range | record_month=13 | error['record_month'] | ✔ |
| DTO negative amount | disbursed=-5 | validation error | ✔ |
| DTO empty items | items=[] | validation error | ✔ |

### Edge Cases Checklist
- [ ] Empty items array → 422
- [ ] Negative / non-numeric amounts → 422
- [ ] Duplicate session/record → idempotent fetch (no 500/duplicate)
- [ ] Non-admin acting on another org → forced to own org / 403 on delete
- [ ] Activity with no source_of_truth mapping → fallback list
- [ ] Re-save (resume) updates existing trackings, not duplicates

---

## Validation Commands

### Static Analysis (frontend)
```bash
cd frontend && npx vue-tsc -b
```
EXPECT: zero type errors

### PHP Unit Tests (CI scope)
```powershell
$env:Path = "D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;" + $env:Path
php vendor/phpunit/phpunit/phpunit --testsuite Unit --filter Disbursement
```
EXPECT: all green (Dtos + Services)

### Frontend Build
```bash
cd frontend && npm run build
```
EXPECT: success, app shell < 300kb gzipped

### E2E
```bash
npm run test:e2e -- disbursement-tracking-workflow
```
EXPECT: green

### $_SESSION audit (success signal)
```bash
grep -rn "\$_SESSION" src/Api src/Services
```
EXPECT: zero matches in new API layer

### Manual smoke (curl)
```bash
# login → cookie; then create session → activities → record → PUT items → GET record
```
EXPECT: amounts persist; remaining computed

---

## Acceptance Criteria
- [ ] 9 endpoints live and return `ApiResponse` envelope
- [ ] Wizard completes create→activity→amounts→save with no full reload
- [ ] `budget_trackings` upsert is idempotent (resume safe)
- [ ] Zero `$_SESSION` in `src/Api` + `src/Services`
- [ ] Unit tests (Dtos+Services) + e2e green; `vue-tsc` + build clean
- [ ] PRD Phase 5 success signals met (tracking flow E2E green; stateless API)

## Completion Checklist
- [ ] Follows controller/service/repo/DTO patterns exactly
- [ ] Portable upsert (SQLite-testable, no ON DUPLICATE KEY)
- [ ] TanStack for server state; Pinia only for wizard draft
- [ ] One `<h1>` per page; dark theme; Thai strings
- [ ] No new migration (tables exist); no edits to `src/Controllers/BudgetController.php`
- [ ] Self-contained — no codebase search needed during impl

## Risks
| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| `source_of_truth_mappings` column names differ from assumption | M | M | Read dump line 1658 before writing the JOIN (Task 3 gotcha) |
| `ON DUPLICATE KEY` sneaks in → SQLite unit test fails | M | H | Portable SELECT-then-write upsert mandated (Task 4) |
| Expense tree too large/deep for a clean form | L | M | Render active leaf items grouped by type/group; `is_header` display-only |
| E2E flakiness on multi-step wizard | M | M | Resilient selectors; idempotent create-or-fetch; month 11 avoids seed clash |
| budget_trackings `unique_tracking(fiscal_year, budget_category_item_id)` collision | L | H | Keep `budget_category_item_id` NULL (matches seed) |

## Notes
- Scope locked to **core tracking flow** per user (2026-06-14). Legacy disbursement header/detail CRUD + execution export deferred to a follow-up phase.
- Confidence: **8/10** for single-pass — risk concentrated in the activities/source_of_truth JOIN and the portable upsert, both flagged with explicit gotchas.
