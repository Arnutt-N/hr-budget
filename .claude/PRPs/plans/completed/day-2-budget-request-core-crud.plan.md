# Plan: Day 2 — Budget Request Core CRUD

## Summary

Build the complete Budget Request workflow as a vertical slice: REST API endpoints (list/create/view/edit/delete + submit/approve/reject) backed by a Repository-Service-Controller layer, with a Vue 3 SPA frontend (list page, create form, detail view, approval actions). Includes basic audit logging via the existing `budget_request_approvals` table.

## User Story

As a **HR budget officer**,
I want **to create, submit, and track budget requests digitally**,
So that **I can manage the entire approval workflow without paper/Excel, seeing status changes in real time**.

## Problem → Solution

**Current state**: Budget requests exist only in the legacy MVC (server-rendered PHP views, POST-only forms, session auth). No REST API, no Vue SPA interaction.

**Desired state**: Full CRUD REST API under `/api/v1/requests/*` with JWT auth, plus Vue SPA pages that call the API. The legacy MVC continues to work (Strangler Fig).

## Metadata

- **Complexity**: **Large** (vertical slice: backend layer + 11 API endpoints + 4 Vue pages + audit log)
- **Source PRD**: `.claude/PRPs/prds/hr-budget-rest-api-vue-refactor.prd.md`
- **PRD Phase**: Phase 2 / Day 2 — Budget Request Core CRUD
- **Depends On**: Day 1 (complete) — API scaffold, JWT auth, Vue shell
- **Estimated Files**: ~20 new, 3 updated
- **Time Estimate**: 8 hours (Claude Code-assisted)

---

## UX Design

### Before (Day 1 state)

```
┌─────────────────────────────────────────────────┐
│  Vue SPA (:5174)                                 │
│                                                   │
│  /login → LoginPage.vue           (works)        │
│  /dashboard → DashboardPage.vue   (placeholder)  │
│                                                   │
│  [No budget request functionality yet]           │
└─────────────────────────────────────────────────┘
```

### After (Day 2 deliverable)

```
┌─────────────────────────────────────────────────┐
│  Vue SPA (:5174)                                 │
│                                                   │
│  /requests          → RequestListPage.vue        │
│    ├─ filter bar (status, fiscal year, search)   │
│    ├─ table: title, status, amount, date, actions│
│    └─ pagination                                  │
│                                                   │
│  /requests/create   → RequestCreatePage.vue      │
│    ├─ form: title, fiscal year, org              │
│    ├─ item editor (add/remove rows)              │
│    │   item_name, quantity, unit_price, amount   │
│    └─ save draft / submit buttons                │
│                                                   │
│  /requests/:id      → RequestDetailPage.vue      │
│    ├─ request info card                           │
│    ├─ items table (read-only or editable)        │
│    ├─ approval history timeline                  │
│    └─ approve/reject buttons (if approver)       │
│                                                   │
│  /requests/:id/edit → RequestEditPage.vue        │
│    └─ same form as create, pre-filled             │
└─────────────────────────────────────────────────┘
```

### Interaction Changes

| Touchpoint | Before | After | Notes |
|---|---|---|---|
| View requests | Legacy PHP table | Vue SPA paginated table | Filter by status/fiscal year |
| Create request | Multi-page PHP form | Single-page Vue form with item editor | Auto-calc amount per item |
| Submit request | POST form → PHP redirect | Click "Submit" → API call → toast | Status changes to `pending` |
| Approve/reject | POST form on PHP page | Click button → API call → status badge update | Audit log auto-written |
| View history | N/A | Approval timeline on detail page | From `budget_request_approvals` |

---

## Mandatory Reading

### Day 1 established patterns (MIRROR these exactly)

| Priority | File | Lines | Why |
|---|---|---|---|
| **P0** | `src/Api/Controllers/AuthController.php` | all | Controller pattern — thin, final class, constructor injection |
| **P0** | `src/Services/AuthService.php` | all | Service pattern — null on failure, audit logging |
| **P0** | `src/Dtos/LoginRequestDto.php` | all | DTO pattern — readonly props, `fromRequest()`, `validate()` |
| **P0** | `src/Dtos/AuthResponseDto.php` | all | Response DTO pattern — `toArray()` whitelist |
| **P0** | `src/Api/Responses/ApiResponse.php` | all | Response helpers — `ok()`, `created()`, `notFound()`, `validationFailed()` |
| **P0** | `src/Api/Middleware/AuthMiddleware.php` | all | Auth guard pattern — `AuthMiddleware::require()` |
| **P0** | `routes/web.php` | 19-27 | API route registration style |
| **P0** | `config/api.php` | all | Config format |

### Domain model reference (understand data shape, DO NOT mirror static-SQL style)

| Priority | File | Lines | Why |
|---|---|---|---|
| **P1** | `src/Models/BudgetRequest.php` | all | Existing SQL queries — understand column names, joins, filters |
| **P1** | `src/Models/BudgetRequestItem.php` | all | Item CRUD — understand upsert pattern, column names |
| **P1** | `src/Models/BudgetRequestApproval.php` | all | Audit log — understand `log()` signature, `getByRequestId()` |
| **P1** | `src/Core/Database.php` | all | PDO wrapper — `query()`, `insert()`, `update()`, `delete()`, transactions |
| **P1** | `src/Core/SimpleQueryBuilder.php` | all | Fluent query builder — for Repository layer |

### Frontend Day 1 patterns

| Priority | File | Lines | Why |
|---|---|---|---|
| **P0** | `frontend/src/composables/useApi.ts` | all | API call pattern — `apiFetch<T>(path, options)` |
| **P0** | `frontend/src/stores/auth.ts` | all | Pinia setup store pattern |
| **P0** | `frontend/src/types/api.ts` | all | Type definitions — `ApiResponse<T>`, naming conventions |
| **P0** | `frontend/src/router/index.ts` | all | Route registration, nested routes, lazy imports |
| **P1** | `frontend/src/pages/LoginPage.vue` | all | Page component pattern — `<script setup>`, form handling |
| **P1** | `frontend/src/layouts/AppLayout.vue` | all | Layout wrapper pattern |

---

## External Documentation

| Topic | Source | Key Takeaway |
|---|---|---|
| No external research needed | N/A | Feature uses established Day 1 internal patterns + existing domain models. All SQL schemas known. |

---

## Patterns to Mirror

### CONTROLLER_PATTERN
```php
// SOURCE: src/Api/Controllers/AuthController.php:1-63
final class BudgetRequestController
{
    public function __construct(
        private readonly BudgetRequestService $service = new BudgetRequestService()
    ) {}

    public function list(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();

        // parse query params → call service → ApiResponse::ok()
    }
}
```

### SERVICE_PATTERN
```php
// SOURCE: src/Services/AuthService.php:1-68
final class BudgetRequestService
{
    // Returns DTO on success, null on failure
    // Logs failures with [budget_request] prefix via error_log()
    // No exceptions for business failures — only for unexpected errors
    public function create(int $userId, CreateBudgetRequestDto $dto): ?int
    {
        // validate business rules
        // delegate to repository
        // write audit log
        // return result
    }
}
```

### DTO_PATTERN
```php
// SOURCE: src/Dtos/LoginRequestDto.php:1-60
final class CreateBudgetRequestDto
{
    public function __construct(
        public readonly string $requestTitle,
        public readonly int $fiscalYear,
        public readonly ?int $orgId,
        /** @var BudgetRequestItemDto[] */
        public readonly array $items,
    ) {}

    public static function fromRequest(): self { /* parse php://input */ }
    public function validate(): array { /* return ['field' => 'ข้อความไทย'] */ }
}
```

### REPOSITORY_PATTERN
```php
// New pattern for Day 2 — wraps Database:: calls
final class BudgetRequestRepository
{
    public function findAll(array $filters, int $limit, int $offset): array
    {
        // Use Database::query() with parameterized SQL
        // Return array of raw rows (not models)
    }

    public function findById(int $id): ?array { /* ... */ }
    public function insert(array $data): int { /* Database::insert() */ }
    public function update(int $id, array $data): bool { /* Database::update() */ }
    public function delete(int $id): bool { /* Database::delete() */ }
}
```

### API_RESPONSE_PATTERN
```php
// SOURCE: src/Api/Responses/ApiResponse.php:1-94
// Success: ApiResponse::ok($data)        → { success: true, data: ... }
// Created: ApiResponse::created($data)   → 201 + { success: true, data: ... }
// Not Found: ApiResponse::notFound()     → 404 + { success: false, error: "..." }
// Validation: ApiResponse::validationFailed($details) → 422
// Error: ApiResponse::error($msg, 500)   → { success: false, error: "..." }
```

### ROUTE_REGISTRATION
```php
// SOURCE: routes/web.php:19-27
use App\Api\Controllers\BudgetRequestController as ApiBudgetRequestController;

Router::get('/api/v1/requests', [ApiBudgetRequestController::class, 'list']);
Router::post('/api/v1/requests', [ApiBudgetRequestController::class, 'create']);
Router::get('/api/v1/requests/{id}', [ApiBudgetRequestController::class, 'show']);
Router::put('/api/v1/requests/{id}', [ApiBudgetRequestController::class, 'update']);
Router::delete('/api/v1/requests/{id}', [ApiBudgetRequestController::class, 'delete']);
Router::post('/api/v1/requests/{id}/submit', [ApiBudgetRequestController::class, 'submit']);
Router::post('/api/v1/requests/{id}/approve', [ApiBudgetRequestController::class, 'approve']);
Router::post('/api/v1/requests/{id}/reject', [ApiBudgetRequestController::class, 'reject']);
```

### VUE_API_CALL_PATTERN
```typescript
// SOURCE: frontend/src/composables/useApi.ts:1-37
// All API calls use apiFetch<T>(path, options)
// Auto-prefixed with /api/v1
// Auto-attaches Bearer token
// Auto-logs out on 401

import { apiFetch } from '@/composables/useApi'
import type { ApiResponse, BudgetRequest } from '@/types/api'

const res = await apiFetch<BudgetRequest>('/requests', {
  method: 'POST',
  body: JSON.stringify(payload),
})
```

### PINIA_STORE_PATTERN
```typescript
// SOURCE: frontend/src/stores/auth.ts:1-83
export const useBudgetRequestStore = defineStore('budgetRequests', () => {
  const requests = ref<BudgetRequest[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchList(filters?: ListFilters): Promise<boolean> { /* ... */ }
  async function create(data: CreateRequest): Promise<{ ok: boolean; error?: string }> { /* ... */ }

  return { requests, loading, error, fetchList, create }
})
```

### VUE_PAGE_PATTERN
```vue
<!-- SOURCE: frontend/src/pages/LoginPage.vue:1-78 -->
<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useBudgetRequestStore } from '@/stores/budgetRequests'

const store = useBudgetRequestStore()
const loading = ref(false)
</script>

<template>
  <!-- Thai language UI, Tailwind CSS, role="alert" for errors -->
</template>
```

---

## Files to Change

### Backend — New Files

| File | Action | Justification |
|---|---|---|
| `src/Repositories/BudgetRequestRepository.php` | CREATE | Data access layer for `budget_requests` |
| `src/Repositories/BudgetRequestItemRepository.php` | CREATE | Data access layer for `budget_request_items` |
| `src/Repositories/BudgetRequestApprovalRepository.php` | CREATE | Data access layer for `budget_request_approvals` |
| `src/Dtos/CreateBudgetRequestDto.php` | CREATE | Request DTO for creating budget requests + items |
| `src/Dtos/UpdateBudgetRequestDto.php` | CREATE | Request DTO for updating budget requests |
| `src/Dtos/BudgetRequestItemDto.php` | CREATE | Nested DTO for budget request items |
| `src/Dtos/ApprovalActionDto.php` | CREATE | Request DTO for approve/reject actions |
| `src/Dtos/BudgetRequestListQueryDto.php` | CREATE | Query param DTO for list filtering + pagination |
| `src/Services/BudgetRequestService.php` | CREATE | Business logic for CRUD + workflow |
| `src/Api/Controllers/BudgetRequestController.php` | CREATE | REST API controller |

### Backend — Updated Files

| File | Action | Justification |
|---|---|---|
| `routes/web.php` | UPDATE | Add 11 API routes for `/api/v1/requests/*` |

### Frontend — New Files

| File | Action | Justification |
|---|---|---|
| `frontend/src/types/budget-request.ts` | CREATE | TypeScript interfaces for BudgetRequest, Item, etc. |
| `frontend/src/api/budgetRequests.ts` | CREATE | API call functions using `apiFetch` |
| `frontend/src/stores/budgetRequests.ts` | CREATE | Pinia store for budget request state |
| `frontend/src/pages/RequestListPage.vue` | CREATE | List view with filters + pagination |
| `frontend/src/pages/RequestCreatePage.vue` | CREATE | Create form with item editor |
| `frontend/src/pages/RequestDetailPage.vue` | CREATE | Detail view with approval timeline + actions |
| `frontend/src/pages/RequestEditPage.vue` | CREATE | Edit form (reuses item editor) |
| `frontend/src/components/StatusBadge.vue` | CREATE | Status badge component (shared) |
| `frontend/src/components/ItemEditor.vue` | CREATE | Budget item add/remove/edit component |

### Frontend — Updated Files

| File | Action | Justification |
|---|---|---|
| `frontend/src/router/index.ts` | UPDATE | Add 4 new routes for budget requests |

### Tests — New Files

| File | Action | Justification |
|---|---|---|
| `tests/Unit/Api/BudgetRequestControllerTest.php` | CREATE | Controller unit tests |
| `tests/Unit/Services/BudgetRequestServiceTest.php` | CREATE | Service unit tests |

## NOT Building

- Multi-level approval chain (Day 4 scope) — single-level only
- File upload/attachment (Day 3 scope)
- Notification bell/UI (Day 3 scope)
- Fiscal year picker or budget code master data UI (Day 3 scope)
- Excel export (Day 4 scope)
- Dashboard charts/metrics (Day 4 scope)
- Password reset flow (Day 4 scope)
- Audit log viewer UI (Day 4 scope) — audit entries are WRITTEN but no UI to browse them

---

## Step-by-Step Tasks

### Task 1: Create BudgetRequestItemDto
- **ACTION**: Create `src/Dtos/BudgetRequestItemDto.php`
- **IMPLEMENT**: Readonly DTO with `itemName`, `quantity`, `unitPrice`, `remark`, optional `categoryItemId`. Include `validate()` returning Thai error messages. Include computed `amount()` that returns `quantity * unitPrice`.
- **MIRROR**: `src/Dtos/LoginRequestDto.php` — same `final class`, `readonly` props, `validate(): array`, no base class
- **IMPORTS**: None needed
- **GOTCHA**: Use `bcmath` or string math for decimal multiplication to avoid float precision issues with `decimal(15,2)` money values
- **VALIDATE**: `new BudgetRequestItemDto('Test', 2, '100.00', null)->amount()` returns `'200.00'`

### Task 2: Create CreateBudgetRequestDto
- **ACTION**: Create `src/Dtos/CreateBudgetRequestDto.php`
- **IMPLEMENT**: Readonly DTO with `requestTitle` (string), `fiscalYear` (int), `orgId` (?int), `items` (array of BudgetRequestItemDto). Include `fromRequest(): self` parsing JSON body, and `validate(): array` checking title required, fiscalYear > 0, at least 1 item.
- **MIRROR**: `src/Dtos/LoginRequestDto.php` — same pattern with `fromRequest()` factory
- **IMPORTS**: `App\Dtos\BudgetRequestItemDto`
- **GOTCHA**: Items come as JSON array — parse each element into BudgetRequestItemDto instances. Missing items array = validation error.
- **VALIDATE**: POST body `{"request_title":"Test","fiscal_year":2569,"items":[{"item_name":"A","quantity":1,"unit_price":"500.00"}]}` parses correctly

### Task 3: Create UpdateBudgetRequestDto
- **ACTION**: Create `src/Dtos/UpdateBudgetRequestDto.php`
- **IMPLEMENT**: Similar to Create but all fields optional (partial update). Items are optional — if provided, replace all items; if absent, keep existing items.
- **MIRROR**: `src/Dtos/LoginRequestDto.php`
- **IMPORTS**: `App\Dtos\BudgetRequestItemDto`
- **GOTCHA**: Only draft/saved requests can be updated — the SERVICE layer enforces this, not the DTO
- **VALIDATE**: Empty body `{}` is valid (no-op update)

### Task 4: Create ApprovalActionDto
- **ACTION**: Create `src/Dtos/ApprovalActionDto.php`
- **IMPLEMENT**: Readonly DTO with `note` (?string) for approve/reject reason. Thai validation: reject requires a note.
- **MIRROR**: `src/Dtos/LoginRequestDto.php`
- **IMPORTS**: None
- **GOTCHA**: For approve, note is optional. For reject, note is required (service layer checks action type + DTO together)
- **VALIDATE**: Empty note on reject returns `['note' => 'กรุณาระบุเหตุผลการปฏิเสธ']`

### Task 5: Create BudgetRequestListQueryDto
- **ACTION**: Create `src/Dtos/BudgetRequestListQueryDto.php`
- **IMPLEMENT**: Parses `$_GET` query params: `status` (?string), `fiscal_year` (?int), `search` (?string), `page` (int, default 1), `per_page` (int, default 20). `fromQueryString(): self`. Validate: `per_page` max 100, `page` min 1.
- **MIRROR**: `src/Dtos/LoginRequestDto.php` — but reads from `$_GET` not `php://input`
- **IMPORTS**: None
- **GOTCHA**: `status` must be one of: `draft`, `saved`, `confirmed`, `pending`, `approved`, `rejected` — validate against enum
- **VALIDATE**: `?status=pending&page=2` parses to `{status: 'pending', page: 2, perPage: 20}`

### Task 6: Create BudgetRequestRepository
- **ACTION**: Create `src/Repositories/BudgetRequestRepository.php`
- **IMPLEMENT**: `findAll(filters, limit, offset): array`, `count(filters): int`, `findById(id): ?array`, `insert(data): int`, `update(id, data): bool`, `delete(id): bool`. SQL mirrors existing `BudgetRequest::all()` / `find()` / `create()` / `update()` queries but as instance methods. Use `Database::query()`, `Database::insert()`, `Database::update()`, `Database::delete()`.
- **MIRROR**: SQL from `src/Models/BudgetRequest.php` — same JOINs, same filter logic, same column names
- **IMPORTS**: `App\Core\Database`
- **GOTCHA**: Column `fiscal_year` is Thai Buddhist year (int 2569). Column `request_status` is enum string. JOIN users for `created_by` name. JOIN organizations for `org_id` name.
- **VALIDATE**: `findAll(['status' => 'pending'], 20, 0)` returns array with correct joins

### Task 7: Create BudgetRequestItemRepository
- **ACTION**: Create `src/Repositories/BudgetRequestItemRepository.php`
- **IMPLEMENT**: `findByRequestId(requestId): array`, `insert(data): int`, `update(id, data): bool`, `delete(id): bool`, `deleteByRequestId(requestId): bool`, `replaceItems(requestId, items): void` (delete all + re-insert in transaction).
- **MIRROR**: SQL from `src/Models/BudgetRequestItem.php` — same table, same column names
- **IMPORTS**: `App\Core\Database`
- **GOTCHA**: Column is `amount` (not `total_amount`). `budget_request_id` FK has CASCADE delete. `replaceItems` should use `Database::beginTransaction()` / `commit()` / `rollback()`.
- **VALIDATE**: `replaceItems(1, [{item_name: 'A', quantity: 2, unit_price: '100', amount: '200'}])` works

### Task 8: Create BudgetRequestApprovalRepository
- **ACTION**: Create `src/Repositories/BudgetRequestApprovalRepository.php`
- **IMPLEMENT**: `log(requestId, action, userId, note): int`, `findByRequestId(requestId): array`. SQL mirrors existing `BudgetRequestApproval::log()` and `getByRequestId()`.
- **MIRROR**: SQL from `src/Models/BudgetRequestApproval.php` — same table, same column names (`user_id`, `note`, `created_at`)
- **IMPORTS**: `App\Core\Database`
- **GOTCHA**: `action` column is `varchar(50)` not enum. JOIN users for user name in `findByRequestId`.
- **VALIDATE**: `log(1, 'approved', 5, 'อนุมัติแล้ว')` inserts row and returns ID

### Task 9: Create BudgetRequestService
- **ACTION**: Create `src/Services/BudgetRequestService.php`
- **IMPLEMENT**: Business logic class with methods:
  - `list(userId, role, queryDto): array` — if role !== 'admin', filter by `created_by = userId`. Returns `{data: [...], meta: {total, page, per_page}}`.
  - `create(userId, dto): ?int` — validates fiscal year, creates request with status `draft`, creates items, writes audit log `created`, returns ID.
  - `findById(userId, role, id): ?array` — ownership check (admin sees all, user sees own). Returns request + items + approvals.
  - `update(userId, role, id, dto): bool` — ownership check + status check (only `draft`/`saved` editable). Updates fields, replaces items if provided, writes audit log `modified`.
  - `delete(userId, role, id): bool` — ownership check + status check (only `draft`/`saved` deletable). Deletes items (CASCADE) + request, writes audit log `deleted`.
  - `submit(userId, id): bool` — ownership check, status must be `draft`/`saved`. Changes to `pending`, sets `submitted_at`. Writes audit log `submitted`.
  - `approve(userId, id, dto): bool` — status must be `pending`. Changes to `approved`, sets `approved_at`. Writes audit log `approved`.
  - `reject(userId, id, dto): bool` — status must be `pending`. Changes to `rejected`, sets `rejected_at` + `rejected_reason`. Writes audit log `rejected`.
- **MIRROR**: `src/Services/AuthService.php` — `final class`, returns null on failure, `self::logFailure()` pattern
- **IMPORTS**: `App\Repositories\BudgetRequestRepository`, `App\Repositories\BudgetRequestItemRepository`, `App\Repositories\BudgetRequestApprovalRepository`, DTOs
- **GOTCHA**: Status transition enforcement is the core business rule. `draft/saved → pending → approved/rejected`. No backward transitions. Each state change MUST write to audit log.
- **VALIDATE**: Create → submit → approve flow produces 3 audit log entries with correct actions

### Task 10: Create BudgetRequestController (API)
- **ACTION**: Create `src/Api/Controllers/BudgetRequestController.php`
- **IMPLEMENT**: Thin controller with methods matching 11 endpoints:
  - `list()` — CorsMiddleware, Auth, parse query DTO, call service, ApiResponse::ok with meta
  - `create()` — CorsMiddleware, Auth, parse CreateBudgetRequestDto, validate, call service, ApiResponse::created
  - `show(id)` — CorsMiddleware, Auth, call service, ApiResponse::ok or ApiResponse::notFound
  - `update(id)` — CorsMiddleware, Auth, parse UpdateBudgetRequestDto, validate, call service, ApiResponse::ok
  - `delete(id)` — CorsMiddleware, Auth, call service, ApiResponse::noContent
  - `submit(id)` — CorsMiddleware, Auth, call service, ApiResponse::ok
  - `approve(id)` — CorsMiddleware, Auth, parse ApprovalActionDto, call service, ApiResponse::ok
  - `reject(id)` — CorsMiddleware, Auth, parse ApprovalActionDto, validate, call service, ApiResponse::ok
- **MIRROR**: `src/Api/Controllers/AuthController.php` — `final class`, constructor injection with default, thin methods
- **IMPORTS**: `App\Api\Responses\ApiResponse`, `App\Api\Middleware\AuthMiddleware`, `App\Api\Middleware\CorsMiddleware`, `App\Services\BudgetRequestService`, DTOs
- **GOTCHA**: Route params come from Router dispatch — `{id}` is passed as method argument. Check `Router::dispatch()` to confirm param passing convention.
- **VALIDATE**: `GET /api/v1/requests?status=pending` returns `{ success: true, data: [...], meta: { total, page, per_page } }`

### Task 11: Register API Routes
- **ACTION**: Update `routes/web.php` to add 11 new routes
- **IMPLEMENT**: Add import for `ApiBudgetRequestController`, register routes under `/api/v1/requests/*`. Place AFTER existing auth routes, BEFORE legacy web routes.
- **MIRROR**: Existing route registration in `routes/web.php:19-27`
- **IMPORTS**: `use App\Api\Controllers\BudgetRequestController as ApiBudgetRequestController;`
- **GOTCHA**: Router supports `Router::get()`, `Router::post()`, `Router::put()`, `Router::delete()` — verify all exist. The `PUT` method may need to check if Router handles it natively or if we use POST + `_method=PUT` override. **Check `src/Core/Router.php` first.**
- **VALIDATE**: `php -S localhost:8000 -t public/` → `GET /api/v1/requests` returns JSON (even if empty)

### Task 12: Create Frontend Types
- **ACTION**: Create `frontend/src/types/budget-request.ts`
- **IMPLEMENT**: TypeScript interfaces mirroring backend DTOs:
  ```typescript
  export interface BudgetRequest {
    id: number
    fiscal_year: number
    request_title: string
    request_status: RequestStatus
    total_amount: string | null
    created_by: number
    org_id: number | null
    creator_name?: string
    org_name?: string
    created_at: string
    updated_at: string
    submitted_at: string | null
    approved_at: string | null
    rejected_at: string | null
    rejected_reason: string | null
    items?: BudgetRequestItem[]
    approvals?: ApprovalLog[]
  }
  export type RequestStatus = 'draft' | 'saved' | 'confirmed' | 'pending' | 'approved' | 'rejected'
  export interface BudgetRequestItem { /* ... */ }
  export interface ApprovalLog { /* ... */ }
  export interface CreateBudgetRequest { /* ... */ }
  export interface UpdateBudgetRequest { /* ... */ }
  export interface ListFilters { /* ... */ }
  export interface ListMeta { total: number; page: number; per_page: number }
  ```
- **MIRROR**: `frontend/src/types/api.ts` — snake_case for backend fields, matching PHP column names
- **IMPORTS**: None
- **GOTCHA**: `total_amount` and dates are strings (JSON serialization). `RequestStatus` is a union type matching the PHP enum.
- **VALIDATE**: TypeScript compiles with no errors

### Task 13: Create Frontend API Module
- **ACTION**: Create `frontend/src/api/budgetRequests.ts`
- **IMPLEMENT**: Export functions wrapping `apiFetch`:
  ```typescript
  export function fetchRequests(filters?: ListFilters): Promise<ApiResponse<BudgetRequest[]> & { meta?: ListMeta }>
  export function fetchRequestById(id: number): Promise<ApiResponse<BudgetRequest>>
  export function createRequest(data: CreateBudgetRequest): Promise<ApiResponse<BudgetRequest>>
  export function updateRequest(id: number, data: UpdateBudgetRequest): Promise<ApiResponse<BudgetRequest>>
  export function deleteRequest(id: number): Promise<ApiResponse<void>>
  export function submitRequest(id: number): Promise<ApiResponse<BudgetRequest>>
  export function approveRequest(id: number, note?: string): Promise<ApiResponse<BudgetRequest>>
  export function rejectRequest(id: number, note: string): Promise<ApiResponse<BudgetRequest>>
  ```
- **MIRROR**: `frontend/src/composables/useApi.ts` — uses `apiFetch<T>` internally
- **IMPORTS**: `import { apiFetch } from '@/composables/useApi'`, types from `@/types/budget-request`
- **GOTCHA**: `meta` comes only on list responses. Build query string from `ListFilters` for GET requests.
- **VALIDATE**: `fetchRequests({ status: 'pending' })` calls `GET /api/v1/requests?status=pending`

### Task 14: Create Pinia Store
- **ACTION**: Create `frontend/src/stores/budgetRequests.ts`
- **IMPLEMENT**: Setup store pattern with `requests`, `currentRequest`, `loading`, `error`, `filters`, `meta`. Actions: `fetchList()`, `fetchById(id)`, `create(data)`, `update(id, data)`, `remove(id)`, `submit(id)`, `approve(id, note?)`, `reject(id, note)`.
- **MIRROR**: `frontend/src/stores/auth.ts` — setup store pattern, `ref`/`computed`
- **IMPORTS**: API module functions, types
- **GOTCHA**: After create/update/submit/approve/reject, refresh the list or current request to reflect changes. Don't optimistically update — let the backend be source of truth.
- **VALIDATE**: `await store.fetchList({ status: 'pending' })` populates `store.requests` and `store.meta`

### Task 15: Create StatusBadge Component
- **ACTION**: Create `frontend/src/components/StatusBadge.vue`
- **IMPLEMENT**: Small shared component. Props: `status: RequestStatus`. Maps status to Tailwind color classes:
  - `draft` → gray, `saved` → blue, `confirmed` → indigo, `pending` → yellow, `approved` → green, `rejected` → red
  - Displays Thai label: ร่าง, บันทึกแล้ว, ยืนยันแล้ว, รออนุมัติ, อนุมัติแล้ว, ปฏิเสธ
- **MIRROR**: Day 1 Tailwind styling patterns
- **IMPORTS**: `RequestStatus` type
- **GOTCHA**: Keep component small and reusable — this will be used in list, detail, and notifications later
- **VALIDATE**: `<StatusBadge status="pending" />` renders yellow badge with "รออนุมัติ"

### Task 16: Create ItemEditor Component
- **ACTION**: Create `frontend/src/components/ItemEditor.vue`
- **IMPLEMENT**: Editable table for budget items. Props: `modelValue: BudgetRequestItem[]` (v-model). Each row: item_name (text), quantity (number), unit_price (number), computed amount, remark (text), delete button. "Add item" button appends empty row. Emit `update:modelValue`.
- **MIRROR**: Composition API + `<script setup>` pattern
- **IMPORTS**: Types
- **GOTCHA**: Amount = quantity × unit_price, computed per row. Use `@input` handlers that create new item objects (immutable pattern). Total amount at bottom = sum of all item amounts.
- **VALIDATE**: Add 2 items, verify total updates. Remove 1 item, verify total updates.

### Task 17: Create RequestListPage
- **ACTION**: Create `frontend/src/pages/RequestListPage.vue`
- **IMPLEMENT**: Main budget request list page.
  - Top: "สร้างคำขอใหม่" button → router.push('/requests/create')
  - Filter bar: status dropdown, fiscal year input, search text, apply button
  - Table: columns = ชื่อคำขอ, สถานะ (StatusBadge), ยอดรวม, ผู้สร้าง, วันที่, จัดการ (view/edit/delete)
  - Pagination: previous/next buttons using `meta.page` and `meta.total`
  - Empty state: "ไม่มีคำของบประมาณ" message
- **MIRROR**: `frontend/src/pages/LoginPage.vue` — `<script setup lang="ts">`, Tailwind, Thai text
- **IMPORTS**: Store, StatusBadge, router
- **GOTCHA**: Load list `onMounted`. Only show edit/delete for `draft`/`saved` status. Only show approve/reject for `pending` status to admin/approvers.
- **VALIDATE**: Page loads with request list. Clicking "สร้างคำขอใหม่" navigates to create page.

### Task 18: Create RequestCreatePage
- **ACTION**: Create `frontend/src/pages/RequestCreatePage.vue`
- **IMPLEMENT**: Create form page.
  - Form fields: request_title (text), fiscal_year (number, default from config or 2569), org (dropdown — hardcode empty for now, Day 3 adds proper picker)
  - ItemEditor component
  - Total amount display (computed from items)
  - Two buttons: "บันทึกร่าง" (save draft) → create with status draft, "ส่งอนุมัติ" (submit) → create + submit
  - On success: toast message + redirect to `/requests/:id`
- **MIRROR**: `frontend/src/pages/LoginPage.vue` — form handling pattern with loading state
- **IMPORTS**: Store, ItemEditor, router
- **GOTCHA**: "ส่งอนุมัติ" is a two-step operation: first create (returns ID), then submit. Handle failure at each step.
- **VALIDATE**: Fill form + add item + click "บันทึกร่าง" → redirects to detail page showing new request

### Task 19: Create RequestDetailPage
- **ACTION**: Create `frontend/src/pages/RequestDetailPage.vue`
- **IMPLEMENT**: Read-only detail view (with conditional edit/approve actions).
  - Request info card: title, status (StatusBadge), fiscal year, org, creator, dates, amounts
  - Items table (read-only): name, qty, unit price, amount, remark
  - Approval history timeline: list from `approvals` array — shows action, user, date, note
  - Action buttons (conditional):
    - Owner + draft/saved → "แก้ไข" (edit) + "ส่งอนุมัติ" (submit)
    - Approver + pending → "อนุมัติ" (approve) + "ปฏิเสธ" (reject, with reason input)
  - Reject dialog: textarea for reason (required)
- **MIRROR**: Layout patterns from AppLayout
- **IMPORTS**: Store, StatusBadge, router, route (for :id param)
- **GOTCHA**: Load request `onMounted` using `route.params.id`. Handle not-found gracefully.
- **VALIDATE**: Open detail page → see all info. Click "อนุมัติ" → status changes to approved.

### Task 20: Create RequestEditPage
- **ACTION**: Create `frontend/src/pages/RequestEditPage.vue`
- **IMPLEMENT**: Edit form — same structure as Create but pre-filled with existing data.
  - Load existing request + items on mount
  - Pre-fill form fields and ItemEditor
  - Save updates via PUT
  - Redirect back to detail page on success
- **MIRROR**: RequestCreatePage — reuse same layout
- **IMPORTS**: Store, ItemEditor, router, route
- **GOTCHA**: Only accessible for `draft`/`saved` requests. If request is already submitted, redirect to detail page.
- **VALIDATE**: Edit title + save → redirects to detail showing updated title

### Task 21: Update Vue Router
- **ACTION**: Update `frontend/src/router/index.ts`
- **IMPLEMENT**: Add 4 child routes under the authenticated `AppLayout` parent:
  ```typescript
  { path: '/requests', component: () => import('@/pages/RequestListPage.vue') },
  { path: '/requests/create', component: () => import('@/pages/RequestCreatePage.vue') },
  { path: '/requests/:id', component: () => import('@/pages/RequestDetailPage.vue') },
  { path: '/requests/:id/edit', component: () => import('@/pages/RequestEditPage.vue') },
  ```
- **MIRROR**: Existing route registration in same file
- **IMPORTS**: None new (lazy imports inline)
- **GOTCHA**: Route order matters — `/requests/create` must come BEFORE `/requests/:id` to avoid `:id` matching "create"
- **VALIDATE**: Navigate to `/requests` → shows list page. Navigate to `/requests/1` → shows detail.

### Task 22: Update AppLayout Navigation
- **ACTION**: Update `frontend/src/layouts/AppLayout.vue`
- **IMPLEMENT**: Add navigation link to budget requests list. Simple text link or button in the header area: "คำของบประมาณ" → `/requests`.
- **MIRROR**: Existing header layout in AppLayout
- **IMPORTS**: `RouterLink` from vue-router
- **GOTCHA**: Keep minimal — full sidebar navigation comes in Day 3
- **VALIDATE**: Click "คำของบประมาณ" → navigates to `/requests`

### Task 23: Write Backend Tests
- **ACTION**: Create `tests/Unit/Services/BudgetRequestServiceTest.php` and `tests/Unit/Api/BudgetRequestControllerTest.php`
- **IMPLEMENT**: Test the critical business rules:
  - Service: create returns ID, submit changes status, approve/reject transitions, cannot edit submitted request, ownership check
  - Controller: list returns 200 + envelope, create returns 201, not found returns 404, validation returns 422
- **MIRROR**: Existing test patterns — `tests/Unit/Api/JwtTest.php`, `tests/Unit/Api/ApiResponseTest.php`
- **IMPORTS**: PHPUnit\Framework\TestCase, service/controller classes
- **GOTCHA**: Use `$exit = false` on ApiResponse for testing. Service tests should mock repositories (or use test DB).
- **VALIDATE**: `vendor/bin/phpunit --testsuite Unit` passes

---

## Testing Strategy

### Unit Tests

| Test | Input | Expected Output | Edge Case? |
|---|---|---|---|
| Create request with valid data | title, fiscal_year, 1 item | Returns int ID | No |
| Create request with no items | title, fiscal_year, [] | Validation error | Yes |
| Submit draft request | draft request ID | Status = `pending`, `submitted_at` set | No |
| Submit already-submitted request | pending request ID | Returns null (failure) | Yes |
| Approve pending request | pending ID + note | Status = `approved`, `approved_at` set | No |
| Reject pending request | pending ID + reason | Status = `rejected`, `rejected_reason` set | No |
| Reject without reason | pending ID + empty note | Validation error | Yes |
| Edit approved request | approved ID + changes | Returns null (failure) | Yes |
| Delete draft request | draft ID | Returns true, audit log written | No |
| Delete approved request | approved ID | Returns null (failure) | Yes |
| List as non-admin user | userId=5, role=staff | Only requests where created_by=5 | Yes |
| List as admin | userId=1, role=admin | All requests | Yes |

### Edge Cases Checklist
- [x] Empty items array on create
- [x] Request not found (404)
- [x] Unauthorized access (different user's request)
- [x] Invalid status transition (approved → draft)
- [x] Concurrent submit (submit already-submitted request)
- [x] SQL injection in search filter (parameterized queries)
- [x] Pagination beyond last page
- [x] Negative amount / quantity in items

---

## Validation Commands

### Static Analysis
```bash
# PHP syntax check (no phpstan configured)
php -l src/Repositories/*.php
php -l src/Services/BudgetRequestService.php
php -l src/Api/Controllers/BudgetRequestController.php
php -l src/Dtos/*.php
```
EXPECT: No syntax errors

### Unit Tests
```bash
vendor/bin/phpunit --testsuite Unit
```
EXPECT: All tests pass

### TypeScript Type Check
```bash
cd frontend && npx vue-tsc --noEmit
```
EXPECT: Zero type errors

### Frontend Build
```bash
cd frontend && npm run build
```
EXPECT: Build succeeds, dist/ output generated

### Browser Validation
```bash
# Terminal 1: PHP built-in server
cd public && php -S localhost:8000

# Terminal 2: Vue dev server
cd frontend && npm run dev
```
EXPECT:
1. Navigate to `http://localhost:5174/requests` → see list page
2. Click "สร้างคำขอใหม่" → create form loads
3. Fill form + add items + click "บันทึกร่าง" → redirects to detail page
4. Click "ส่งอนุมัติ" → status changes to "รออนุมัติ"
5. Click "อนุมัติ" → status changes to "อนุมัติแล้ว"

### Manual Validation
- [ ] Login works → navigate to /requests
- [ ] Create request with items → see in list
- [ ] Edit draft request → changes persist
- [ ] Submit request → status badge changes
- [ ] Approve request → status changes + audit log entry
- [ ] Reject request with reason → status changes + reason saved
- [ ] Delete draft → removed from list
- [ ] Pagination works when > 20 requests
- [ ] Search filter works
- [ ] Status filter works
- [ ] Cannot edit/delete submitted/approved requests

---

## Acceptance Criteria
- [ ] All 11 API endpoints return correct JSON envelope + HTTP status codes
- [ ] Budget request CRUD works end-to-end: create → list → view → edit → delete
- [ ] Approval workflow works: submit → approve OR submit → reject
- [ ] Status transitions are enforced (cannot edit approved requests)
- [ ] Audit log entries written for all state changes
- [ ] Vue SPA has 4 working pages: list, create, detail, edit
- [ ] ItemEditor allows adding/removing items with auto-calculated amounts
- [ ] StatusBadge displays Thai labels with color coding
- [ ] TypeScript compiles with zero errors
- [ ] Unit tests pass for service layer business rules
- [ ] No hardcoded values — config from env where applicable

## Completion Checklist
- [ ] Code follows Day 1 established patterns (final classes, readonly DTOs, ApiResponse envelope)
- [ ] Error handling matches codebase style (null returns, Thai messages, audit logging)
- [ ] Repository layer uses parameterized queries (no SQL injection)
- [ ] Tests follow existing test patterns
- [ ] No hardcoded secrets or values
- [ ] Legacy MVC routes still work (Strangler Fig — no deletions)
- [ ] Self-contained — no questions needed during implementation

## Risks

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Router doesn't support PUT/DELETE natively | M | H | Check Router.php first; if needed, use POST + `_method` override like legacy |
| Decimal precision issues with money | M | M | Use string math in PHP (bcmul), format as string in JSON |
| Vue form complexity (item editor) | M | L | Keep simple — flat table, no drag-drop, no categories yet |
| Test DB not populated with seed data | M | M | Tests use mock repositories where possible; integration tests use `hr_budget_test` |
| Frontend API call inconsistency (raw fetch vs apiFetch) | L | L | Use `apiFetch` exclusively for all new Day 2 API calls |
| Status enum mismatch between PHP and TS | L | M | Define status as union type in both; DTO validates against list |

## Notes

- The existing BudgetRequest/BudgetRequestItem/BudgetRequestApproval models use static methods with raw SQL. Day 2 introduces the Repository pattern as an instance-based alternative. This is intentional — the new API layer should use repositories for testability while the legacy MVC continues using the static models.
- The `budget_request_items` column is `amount` (not `total_amount`) in the production schema. The initial `hr_budget.sql` dump uses `total_amount` but migration 010 and the live DB use `amount`.
- No `Repository` base class or interface is created — following the Day 1 pattern of no inheritance hierarchies. Each repository is a standalone `final class`.
- The approval workflow is **single-level** for Day 2. The `approvers` and `approval_settings` tables from migration 060 are NOT used yet. Any authenticated user can approve/reject `pending` requests. Multi-level approval comes in Day 4.
- Fiscal year dropdown is hardcoded to current year (2569) for now. Day 3 adds the master data picker.
