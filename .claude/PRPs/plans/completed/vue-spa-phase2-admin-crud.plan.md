# Plan: Phase 2 — Admin Master Data CRUD (MVP gate)

## Summary
Convert all 8 admin master-data resources to the Phase-1 stack conventions: PrimeVue DataTable + Dialog forms (vee-validate/zod) + TanStack Query for server state. Four resources (fiscal-years, organizations, users, categories+items) already have APIs and hand-built pages → migrate them; four (divisions, plans, targets, target-types) need full API chains (Repository → DTOs → Service → Controller → routes → unit tests) plus new pages. Fiscal-years goes first as the exemplar; everything else copies it.

## User Story
As an admin user, I want fast, consistent tables and validated forms for all master data, so that yearly setup (ปีงบประมาณ/หน่วยงาน/หมวดงบ/แผน/เป้าหมาย) is quick and error-free; as the developer, I want one proven CRUD pattern so each subsequent resource is mechanical.

## Problem → Solution
Hand-rolled tables + `alert()`/`confirm()` + Pinia stores duplicating server state, and 4 resources still PHP-only → PrimeVue DataTable everywhere, toast/ConfirmDialog UX, TanStack Query cache (stores deleted), full REST coverage for all admin resources.

## Metadata
- **Complexity**: XL — execute milestone-by-milestone, validate at each checkpoint before continuing
- **Source PRD**: `.claude/PRPs/prds/vue-spa-refactor.prd.md`
- **PRD Phase**: Phase 2 — Admin Master Data CRUD
- **Estimated Files**: ~50 (PHP ~24, frontend ~22, e2e ~5)

---

## UX Design

### Before
```
┌────────────────────────────────────────┐
│ Hand-built <table>, no sort/filter     │
│ Modal = v-if div; errors via alert()   │
│ Delete via confirm(); reload full list │
│ divisions/plans/targets/target-types   │
│   → only exist as PHP-rendered pages   │
└────────────────────────────────────────┘
```

### After
```
┌────────────────────────────────────────┐
│ PrimeVue DataTable: sort + filter +    │
│   paginator (client-side, ≤ a few 100) │
│ Dialog + zod inline Thai errors        │
│ useConfirm() dialog + useToast() feedback│
│ TanStack Query: auto refetch on mutate │
│ All 8 resources in the SPA sidebar     │
└────────────────────────────────────────┘
```

### Interaction Changes
| Touchpoint | Before | After | Notes |
|---|---|---|---|
| List | static table, reload-all | DataTable sort/filter/paginate | columns per resource |
| Create/Edit | bare modal + alert | Dialog + vee-validate/zod per-field Thai errors | mirror LoginPage exemplar |
| Delete | `confirm()` | PrimeVue ConfirmDialog | register `ConfirmationService` |
| Feedback | none/alert | Toast (success/error) | ToastService registered in Phase 1 |
| Sidebar | 4 admin links | 8 admin links | AppLayout |

---

## Mandatory Reading

| Priority | File | Lines | Why |
|---|---|---|---|
| P0 | `frontend/src/pages/LoginPage.vue` | all | vee-validate/zod + PrimeVue form exemplar from Phase 1 |
| P0 | `src/Api/Controllers/FiscalYearController.php` | all | THE API controller pattern: CORS → Auth → admin-role check → DTO → Service → ApiResponse (+ try/catch 500 with Thai messages) |
| P0 | `frontend/src/api/fiscalYears.ts` | all | typed apiFetch wrapper per resource — keep this layer, TanStack sits on top |
| P0 | `frontend/src/pages/FiscalYearListPage.vue` | all | page being replaced — shows current columns/fields/labels to preserve |
| P1 | `src/Services/FiscalYearService.php` + `src/Repositories/FiscalYearRepository.php` | all | service/repository pattern incl. role gate in service |
| P1 | `src/Dtos/CreateFiscalYearDto.php`, `UpdateFiscalYearDto.php` | all | DTO fromRequest()/validate() shape (PSR-4: one class per file!) |
| P1 | `tests/Unit/Services/FiscalYearServiceTest.php`, `tests/Unit/Dtos/FiscalYearDtoTest.php` | all | unit test patterns — CI runs `tests/Unit/{Api,Dtos,Services}` only |
| P1 | `frontend/src/stores/fiscalYears.ts` | all | store being DELETED — its API mapping moves to TanStack composables |
| P2 | `tests/e2e/auth-login-logout.spec.ts` | all | E2E style (Page type, admin123 creds, baseURL :5174) |
| P2 | `src/Controllers/DivisionController.php`, `BudgetPlanController.php`, `BudgetTargetController.php`, `AdminTargetTypeController.php` | all | field/validation/table source-of-truth for the 4 new APIs (Task 0) |
| P2 | `frontend/src/router/index.ts`, `frontend/src/layouts/AppLayout.vue` | all | route + sidebar registration |

## External Documentation

| Topic | Source | Key Takeaway |
|---|---|---|
| PrimeVue DataTable | primevue.org/datatable | client-side `sortable` per Column + `filterDisplay="row"` + `paginator :rows` covers all needs; no lazy mode (datasets are small) |
| PrimeVue ConfirmDialog | primevue.org/confirmdialog | needs `app.use(ConfirmationService)` + `<ConfirmDialog/>` once in AppLayout; `useConfirm().require({...})` |
| TanStack Query Vue v5 | tanstack.com/query | `useQuery({ queryKey: ['fiscal-years'], queryFn })`; mutations call `queryClient.invalidateQueries({ queryKey })` on success — replaces the manual `fetchList()` refresh pattern |
| vee-validate + zod | (already proven in LoginPage) | `toTypedSchema`; coerce numbers with `z.coerce.number()` for `<InputNumber>` values |

---

## Patterns to Mirror

### API_CONTROLLER (per-method shape)
```php
// SOURCE: src/Api/Controllers/FiscalYearController.php:20-35
public function list(): void
{
    CorsMiddleware::apply();
    $user = AuthMiddleware::require();
    if (($user['role'] ?? '') !== 'admin') {
        ApiResponse::forbidden('ไม่มีสิทธิ์เข้าถึง');
        return;
    }
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = min(100, max(1, (int) ($_GET['per_page'] ?? 50)));
    $result = $this->service->list($page, $perPage);
    ApiResponse::ok($result['data'], $result['meta']);
}
// create/update wrap in try/catch → error_log("[XController::method] {$e->getMessage()}") → 500 'เกิดข้อผิดพลาดในระบบ'
// mutations re-fetch the row and return it: $item = $this->service->findById($id); ApiResponse::ok($item)
```

### RESOURCE_API_CLIENT (frontend)
```ts
// SOURCE: frontend/src/api/fiscalYears.ts:5-27 — one file per resource, typed apiFetch calls
export async function fetchFiscalYears(page = 1, perPage = 50): Promise<ApiResponse<FiscalYear[]>> {
  return apiFetch<FiscalYear[]>(`/fiscal-years?page=${page}&per_page=${perPage}`)
}
```

### FORM_EXEMPLAR
```vue
// SOURCE: frontend/src/pages/LoginPage.vue:18-28 — schema + defineField; Thai messages in zod
const schema = toTypedSchema(z.object({
  email: z.string().min(1, 'กรุณากรอกอีเมล').email('รูปแบบอีเมลไม่ถูกต้อง'),
}))
const { defineField, handleSubmit, errors } = useForm({ validationSchema: schema })
```

### TANSTACK_CRUD (new in this phase — the convention later phases copy)
```ts
// frontend/src/queries/useFiscalYears.ts (to create — see Task A2)
export function useFiscalYearList() {
  return useQuery({
    queryKey: ['fiscal-years'],
    queryFn: async () => {
      const res = await fetchFiscalYears()
      if (!res.success || !res.data) throw new Error(res.error ?? 'โหลดข้อมูลไม่สำเร็จ')
      return res.data
    },
  })
}
export function useCreateFiscalYear() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: createFiscalYear,
    onSuccess: () => qc.invalidateQueries({ queryKey: ['fiscal-years'] }),
  })
}
```

### TEST_STRUCTURE
```php
// SOURCE: tests/Unit/Services/FiscalYearServiceTest.php + tests/Unit/Dtos/FiscalYearDtoTest.php
// AAA; DTO tests: validate() returns [] on good input / field-keyed Thai errors on bad
// Service tests: role 'viewer' → denied; CI gate runs tests/Unit/{Api,Dtos,Services}
```

### E2E_STYLE
```ts
// SOURCE: tests/e2e/auth-login-logout.spec.ts:14-20 — typed Page, admin123, fill by name=
```

---

## Files to Change

### Milestone A — Exemplar (fiscal-years)
| File | Action |
|---|---|
| `frontend/src/main.ts` | UPDATE — `app.use(ConfirmationService)` |
| `frontend/src/layouts/AppLayout.vue` | UPDATE — `<ConfirmDialog/>` + `<Toast/>` once; later: sidebar links |
| `frontend/src/queries/useFiscalYears.ts` | CREATE — TanStack composables (TANSTACK_CRUD) |
| `frontend/src/pages/FiscalYearListPage.vue` | REWRITE — DataTable + Dialog/zod + confirm/toast |
| `frontend/src/stores/fiscalYears.ts` | DELETE |
| `tests/e2e/admin-fiscal-years.spec.ts` | CREATE — CRUD happy path + validation error + delete confirm |

### Milestone B — Migrate organizations, users, categories(+items)
Per resource: `queries/useX.ts` CREATE, `pages/XListPage.vue` REWRITE, `stores/x.ts` DELETE, `tests/e2e/admin-x.spec.ts` CREATE. Categories: hierarchical → DataTable `expander` rows for items (keep `ItemEditor.vue` if reusable).

### Milestone C — New APIs ×4 (divisions, plans, targets, target-types)
Per resource (mirror fiscal-years chain exactly):
`src/Repositories/XRepository.php`, `src/Dtos/CreateXDto.php` + `UpdateXDto.php`, `src/Services/XService.php`, `src/Api/Controllers/XController.php`, `routes/web.php` (5 routes each), `tests/Unit/Dtos/XDtoTest.php`, `tests/Unit/Services/XServiceTest.php`

### Milestone D — New UI ×4
Per resource: `frontend/src/types/x.ts`, `frontend/src/api/x.ts`, `frontend/src/queries/useX.ts`, `frontend/src/pages/XListPage.vue`, router entry (`meta.requiresAdmin`), sidebar link, `tests/e2e/admin-x.spec.ts`

### Milestone E — Wrap-up
PRD status, report, CLAUDE.md note if conventions changed

## NOT Building
- Server-side pagination/lazy DataTable — datasets are small; client-side is simpler
- Generic `<CrudPage>` mega-abstraction — shared TanStack composable *pattern* is copied per resource, not abstracted into a framework (YAGNI; columns/forms differ too much)
- Rewriting legacy `budget-requests-*.spec.ts` — those target requests/dashboard (Phase 3/4)
- Archiving PHP admin views/controllers — Phase 6 cutover
- Touching budget-request pages/stores — Phase 4

---

## Step-by-Step Tasks

### Task 0: API audit for the 4 new resources (bounded, 30 min)
- **ACTION**: Read `DivisionController.php`, `BudgetPlanController.php`, `BudgetTargetController.php`, `AdminTargetTypeController.php` + models `Division`, `Plan` (+ find the `TargetType` class — imported by AdminTargetTypeController but not obviously in `src/Models` listing; check namespace/file) + relevant `database/migrations/*divisions|plans|targets*` to extract: table name, fillable fields, validation rules, soft-delete/is_active conventions, FK relations (e.g. plans→fiscal_year_id?)
- **VALIDATE**: produce a 4-row field table in the implementation log BEFORE writing any DTO. **GOTCHA**: known-unknown — `TargetType` model location; `Plan::all()` filters `is_active = 1` so new repos must respect is_active soft-delete.

### Task A1: Wire ConfirmationService + shared chrome
- **IMPLEMENT**: `main.ts` add `import ConfirmationService from 'primevue/confirmationservice'` + `app.use`; AppLayout add `<Toast />` + `<ConfirmDialog />` (single instances)
- **VALIDATE**: `npm run typecheck`; dev server boots clean.

### Task A2: fiscal-years TanStack composables
- **IMPLEMENT**: `queries/useFiscalYears.ts` per TANSTACK_CRUD — list query + create/update/delete/setCurrent mutations, all invalidating `['fiscal-years']`
- **MIRROR**: TANSTACK_CRUD; reuse `api/fiscalYears.ts` unchanged
- **GOTCHA**: throw inside queryFn on `!res.success` so DataTable error state works; don't swallow.

### Task A3: Rewrite FiscalYearListPage
- **IMPLEMENT**: DataTable (columns: year sortable, start_date/end_date via `formatThaiDate`, status Tag, actions); Dialog form with zod schema `{ year: z.coerce.number().int().min(2500,'ปี พ.ศ. ไม่ถูกต้อง'), start_date: z.string().min(1,'กรุณาเลือกวันเริ่มต้น'), end_date: ..., is_current: z.boolean().optional() }`; `useConfirm().require` for delete + setCurrent; toast on success/error
- **MIRROR**: FORM_EXEMPLAR; keep all Thai labels from the old page verbatim
- **GOTCHA**: vee-validate `resetForm({ values })` when opening edit dialog — don't bind store objects directly (immutability). Delete `stores/fiscalYears.ts` and confirm no imports remain (`grep -r "stores/fiscalYears"`).
- **VALIDATE**: typecheck + manual CRUD in dev + `tests/e2e/admin-fiscal-years.spec.ts` green.

### Task A4: E2E admin-fiscal-years.spec.ts
- **IMPLEMENT**: login as admin → create year (unique number e.g. 9999-rand) → row visible → edit → toast → delete w/ confirm accept → row gone; validation: submit empty → Thai zod error visible
- **MIRROR**: E2E_STYLE. **GOTCHA**: PrimeVue Dialog renders in a portal — scope selectors with `page.getByRole('dialog')`.

**CHECKPOINT A**: typecheck ✓ vitest ✓ new e2e ✓ — conventions locked; commit.

### Task B1–B3: Replicate for organizations → users → categories(+items)
- **ACTION**: same 4-file treatment per resource (queries, page rewrite, store delete, e2e). Order: organizations (simplest), users (extra fields: role select, is_active toggle, password only on create), categories last (hierarchical: parent rows expand to items via `expander` + nested DataTable; item CRUD endpoints already exist `/categories/{id}/items`)
- **GOTCHA users**: never send empty password on update — omit key. **GOTCHA categories**: restoreItem endpoint exists (soft delete) — surface as "กู้คืน" action on deleted items if API exposes them.
- **VALIDATE per resource**: typecheck + its e2e spec before starting the next.

**CHECKPOINT B**: all 4 existing resources migrated; `grep -r "stores/"` shows only `auth` + notification/budget-request stores (Phase 3/4 scope); commit.

### Task C1–C4: New API chains — divisions, plans, target-types, targets (this order; targets last since it may FK target-types)
- **IMPLEMENT per resource** (copy fiscal-years chain): Repository (PDO via `Database::`, respect `is_active`), Create/Update DTOs (one class per file, `fromRequest()` + `validate()` with Thai field errors), Service (admin-role gate on mutations like `FiscalYearService`), Controller (API_CONTROLLER pattern), 5 routes in `routes/web.php` beside the existing API block, DTO + Service unit tests
- **MIRROR**: API_CONTROLLER + TEST_STRUCTURE
- **GOTCHA**: route registration order matters for `{id}` patterns (static paths first); CI runs the new tests automatically (`tests/Unit/{Dtos,Services}`) — they must pass without DB? No: Services tests hit DB → they live in Unit dir but use `hr_budget_test` (CI provides MySQL service + schema import from `database/hr_budget_only.sql`). **If the 4 tables are missing from `database/hr_budget_only.sql`, CI fails — check and update that dump file in the same commit.**
- **VALIDATE per resource**: `php vendor/phpunit/phpunit/phpunit --filter=XServiceTest` + `--filter=XDtoTest`.

**CHECKPOINT C**: full Unit suite shows no NEW failures (5 pre-existing Models failures remain); commit.

### Task D1–D4: New UI ×4
- **IMPLEMENT per resource**: `types/x.ts` (snake_case fields matching DTO), `api/x.ts` (RESOURCE_API_CLIENT), `queries/useX.ts`, `pages/XListPage.vue` (copy nearest-shaped migrated page), router child route with `meta.requiresAdmin`, sidebar link in AppLayout (Thai labels: ฝ่าย/กลุ่มงาน, แผนงาน, เป้าหมาย, ประเภทเป้าหมาย — confirm exact wording from old PHP views in `resources/views/{divisions,plans,targets,target-types}`), e2e spec each
- **VALIDATE per resource**: typecheck + e2e.

**CHECKPOINT D**: all 8 admin resources working in SPA.

### Task E: Wrap-up
- **ACTION**: PRD Phase 2 → complete + report link; write report (`.claude/PRPs/reports/vue-spa-phase2-admin-crud-report.md`); full validation sweep
- **GOTCHA**: if `frontend/package-lock.json` changes for any reason, regenerate with `npx -y npm@10 install` (CI runs npm 10 — see Phase-1 lesson).

---

## Testing Strategy

### Unit (PHP — runs in CI)
| Test | Coverage |
|---|---|
| `{Division,Plan,Target,TargetType}DtoTest` | empty/invalid fields → Thai errors; valid → [] |
| `{...}ServiceTest` | viewer role denied on create/update/delete; CRUD round-trip; is_active filtering |

### E2E (per resource, 8 specs total incl. existing pattern)
login → list renders → create → edit → validation error path → delete with confirm → row gone

### Edge Cases Checklist
- [ ] Duplicate unique values (e.g. fiscal year ซ้ำ) → 422 surfaced as toast, not crash
- [ ] viewer role direct API hit → 403 (service gate)
- [ ] Empty list → DataTable empty state (Thai message)
- [ ] Category item under deleted parent / restore flow
- [ ] users: update without password keeps old hash

## Validation Commands
```bash
cd frontend && npm run typecheck && npm run test:unit && npm run build
php vendor/phpunit/phpunit/phpunit --testsuite Unit        # expect: only the 5 pre-existing failures
php vendor/phpunit/phpunit/phpunit --testsuite Integration
# E2E (php -S on :8000 + vite :5174 w/ VITE_API_URL=http://127.0.0.1:8000; MySQL via mysqld — see memory)
$env:BASE_URL='http://localhost:5174'; npx playwright test tests/e2e/admin-*.spec.ts --workers=1
```

## Acceptance Criteria
- [ ] 8 admin resources: full CRUD in SPA via DataTable+Dialog+zod+TanStack
- [ ] Per-resource Pinia stores for these resources deleted (server state in TanStack only)
- [ ] 4 new API chains with DTO+Service unit tests passing in CI
- [ ] All `admin-*.spec.ts` E2E green
- [ ] **MVP gate (PRD hypothesis)**: adding the 8th resource took materially less effort than the 1st — note actual experience in report

## Risks
| Risk | L | I | Mitigation |
|---|---|---|---|
| `database/hr_budget_only.sql` lacks the 4 new tables → CI red | M | H | Task C GOTCHA: verify/update dump in same commit |
| TargetType model location unknown | M | L | Task 0 audit resolves before any code |
| Categories hierarchy UI complexity blows up | M | M | Expander + nested table only; no drag-drop/tree editing |
| XL scope fatigue / context length | H | M | Hard checkpoints A–D with commits; safe to pause/resume at any checkpoint |

## Notes
- Architecture decision: **per-resource TanStack composable files, no generic CRUD framework** — repetition across 8 resources is acceptable and grep-able; abstraction can be extracted in Phase 6 if patterns prove identical (rule: don't duplicate server state into Pinia — stores die in this phase).
- Commit per checkpoint (A→E), conventional messages, regenerate lock with npm@10 only.
