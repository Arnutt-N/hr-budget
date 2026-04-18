# Plan: Day 3 — Master Data Admin CRUD + Pickers

## Summary
Add REST API endpoints and Vue admin pages for 4 master data entities: Fiscal Years, Organizations, Budget Categories (with Items), and Users. Each gets a full CRUD API (Repository → Service → Controller) and a Vue admin page with list/create/edit/delete. The existing budget request form's fiscal year and org pickers get wired to live API data instead of hardcoded values.

## User Story
As an HR admin, I want to manage fiscal years, organizations, budget categories, and users through the web app, so that budget requests can reference accurate master data instead of hardcoded values.

## Problem → Solution
Hardcoded fiscal year (2569), no org picker, no admin UI for master data → Full CRUD API + Vue admin pages for all 4 entities, with pickers wired into the budget request form.

## Metadata
- **Complexity**: Large
- **Source PRD**: `.claude/PRPs/prds/hr-budget-rest-api-vue-refactor.prd.md`
- **PRD Phase**: Day 3 (core master data portion)
- **Estimated Files**: ~40

---

## UX Design

### Before
```
[Request Create Form]
├── Fiscal Year: hardcoded input (user types 2569)
├── Organization: empty dropdown
└── No admin pages for managing master data
```

### After
```
[Admin Sidebar]
├── คำของบประมาณ (existing)
├── ปีงบประมาณ (fiscal years list + CRUD)
├── หน่วยงาน (organizations list + CRUD)
├── หมวดงบประมาณ (categories + items CRUD)
└── จัดการผู้ใช้ (users list + CRUD)

[Request Create Form]
├── Fiscal Year: dropdown populated from /api/v1/fiscal-years
└── Organization: dropdown populated from /api/v1/organizations
```

### Interaction Changes
| Touchpoint | Before | After | Notes |
|---|---|---|---|
| Fiscal year picker | Hardcoded number input | Dropdown from API | Fetches active fiscal years |
| Org picker | Empty | Dropdown from API | Fetches all orgs |
| Admin management | Legacy MVC pages only | Vue SPA admin pages | New REST API endpoints |
| Category items | None in SPA | Nested tree CRUD | Admin-only access |

---

## NOT Building
- File upload API + UI (deferred to Day 4)
- Notifications / bell icon (deferred to Day 4)
- Filter/search on budget request list page (deferred to Day 4)
- Multi-level approval (deferred to Day 4)
- Password reset flow (deferred to Day 4)

---

## Patterns to Mirror

### REPOSITORY_PATTERN
// SOURCE: src/Repositories/BudgetRequestRepository.php
```php
namespace App\Repositories;
use App\Core\Database;

class BudgetRequestRepository
{
    public function findAll(array $filters, int $limit, int $offset): array
    {
        $sql = "SELECT br.*, u.name as created_by_name...";
        $params = $this->applyFilters($sql, $filters);
        $sql .= " ORDER BY br.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        return Database::query($sql, $params);
    }
    public function insert(array $data): int { return Database::insert('table', $data); }
    public function update(int $id, array $data): bool { return Database::update('table', $data, 'id = ?', [$id]) > 0; }
    public function delete(int $id): bool { return Database::delete('table', 'id = ?', [$id]) > 0; }
}
```

### SERVICE_PATTERN
// SOURCE: src/Services/BudgetRequestService.php
```php
namespace App\Services;
use App\Core\Database;
use App\Repositories\BudgetRequestRepository;

class BudgetRequestService
{
    public function __construct(
        private readonly BudgetRequestRepository $requestRepo = new BudgetRequestRepository(),
    ) {}
    // Methods call repo, handle auth checks, wrap mutations in transactions
}
```

### CONTROLLER_PATTERN
// SOURCE: src/Api/Controllers/BudgetRequestController.php
```php
final class BudgetRequestController
{
    public function __construct(
        private readonly BudgetRequestService $service = new BudgetRequestService()
    ) {}

    public function list(): void
    {
        CorsMiddleware::apply();
        $user = AuthMiddleware::require();
        try {
            // ... validate DTO, call service, return ApiResponse
        } catch (\Throwable $e) {
            error_log("[Controller::method] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }
}
```

### DTO_PATTERN
// SOURCE: src/Dtos/BudgetRequestListQueryDto.php
```php
final class BudgetRequestListQueryDto
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly int $page = 1,
        public readonly int $perPage = 20,
    ) {}
    public function validate(): array { /* Thai error messages */ }
    public static function fromQueryString(): self { /* reads $_GET */ }
    public function toFilters(): array { /* non-null keys */ }
}
```

### ROUTE_PATTERN
// SOURCE: routes/web.php
```php
use App\Api\Controllers\FiscalYearController as ApiFiscalYearController;
Router::get('/api/v1/fiscal-years', [ApiFiscalYearController::class, 'list']);
Router::post('/api/v1/fiscal-years', [ApiFiscalYearController::class, 'create']);
```

### FRONTEND_API_PATTERN
// SOURCE: frontend/src/api/budgetRequests.ts
```typescript
export async function fetchFiscalYears(): Promise<ApiResponse<FiscalYear[]>> {
  return apiFetch<FiscalYear[]>('/fiscal-years')
}
export async function createFiscalYear(data: CreateFiscalYear): Promise<ApiResponse<FiscalYear>> {
  return apiFetch<FiscalYear>('/fiscal-years', { method: 'POST', body: JSON.stringify(data) })
}
```

### FRONTEND_STORE_PATTERN
// SOURCE: frontend/src/stores/budgetRequests.ts
```typescript
export const useFiscalYearStore = defineStore('fiscalYears', () => {
  const fiscalYears = ref<FiscalYear[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  async function fetchList(): Promise<boolean> { ... }
  return { fiscalYears, loading, error, fetchList, ... }
})
```

### TEST_PATTERN
// SOURCE: tests/Unit/Services/BudgetRequestServiceTest.php
```php
class StubFiscalYearRepo extends FiscalYearRepository {
    public function findAll(array $filters, int $limit, int $offset): array { return []; }
    public function insert(array $data): int { return 1; }
    // ... override all methods
}
// setUp: Database::setInstance(new \PDO('sqlite::memory:'));
// tearDown: Database::resetInstance();
```

---

## Files to Change

| File | Action | Justification |
|---|---|---|
| `src/Repositories/FiscalYearRepository.php` | CREATE | Data access for fiscal years |
| `src/Repositories/OrganizationRepository.php` | CREATE | Data access for organizations |
| `src/Repositories/BudgetCategoryRepository.php` | CREATE | Data access for categories |
| `src/Repositories/BudgetCategoryItemRepository.php` | CREATE | Data access for category items |
| `src/Repositories/UserRepository.php` | CREATE | Data access for users (admin) |
| `src/Services/FiscalYearService.php` | CREATE | Business logic for fiscal years |
| `src/Services/OrganizationService.php` | CREATE | Business logic for organizations |
| `src/Services/BudgetCategoryService.php` | CREATE | Business logic for categories + items |
| `src/Services/UserService.php` | CREATE | Business logic for user management |
| `src/Dtos/FiscalYearDto.php` | CREATE | Create/Update DTOs for fiscal years |
| `src/Dtos/OrganizationDto.php` | CREATE | Create/Update DTOs for organizations |
| `src/Dtos/BudgetCategoryDto.php` | CREATE | Create/Update DTOs for categories |
| `src/Dtos/UserDto.php` | CREATE | Create/Update DTOs for users |
| `src/Api/Controllers/FiscalYearController.php` | CREATE | API endpoints |
| `src/Api/Controllers/OrganizationController.php` | CREATE | API endpoints |
| `src/Api/Controllers/BudgetCategoryController.php` | CREATE | API endpoints |
| `src/Api/Controllers/UserController.php` | CREATE | API endpoints |
| `routes/web.php` | UPDATE | Add 4 resource route groups |
| `frontend/src/types/fiscal-year.ts` | CREATE | TypeScript interfaces |
| `frontend/src/types/organization.ts` | CREATE | TypeScript interfaces |
| `frontend/src/types/budget-category.ts` | CREATE | TypeScript interfaces |
| `frontend/src/types/user.ts` | CREATE | TypeScript interfaces |
| `frontend/src/api/fiscalYears.ts` | CREATE | API functions |
| `frontend/src/api/organizations.ts` | CREATE | API functions |
| `frontend/src/api/budgetCategories.ts` | CREATE | API functions |
| `frontend/src/api/users.ts` | CREATE | API functions |
| `frontend/src/stores/fiscalYears.ts` | CREATE | Pinia store |
| `frontend/src/stores/organizations.ts` | CREATE | Pinia store |
| `frontend/src/stores/budgetCategories.ts` | CREATE | Pinia store |
| `frontend/src/stores/users.ts` | CREATE | Pinia store |
| `frontend/src/pages/FiscalYearListPage.vue` | CREATE | Admin CRUD page |
| `frontend/src/pages/OrganizationListPage.vue` | CREATE | Admin CRUD page |
| `frontend/src/pages/CategoryListPage.vue` | CREATE | Admin CRUD page |
| `frontend/src/pages/UserListPage.vue` | CREATE | Admin CRUD page |
| `frontend/src/pages/RequestCreatePage.vue` | UPDATE | Wire fiscal year + org pickers |
| `frontend/src/pages/RequestEditPage.vue` | UPDATE | Wire fiscal year + org pickers |
| `frontend/src/router/index.ts` | UPDATE | Add 4 admin routes |
| `frontend/src/layouts/AppLayout.vue` | UPDATE | Add admin nav links |
| `tests/Unit/Dtos/FiscalYearDtoTest.php` | CREATE | DTO validation tests |
| `tests/Unit/Dtos/OrganizationDtoTest.php` | CREATE | DTO validation tests |
| `tests/Unit/Dtos/UserDtoTest.php` | CREATE | DTO validation tests |
| `tests/Unit/Services/FiscalYearServiceTest.php` | CREATE | Service logic tests |
| `tests/Unit/Services/UserServiceTest.php` | CREATE | Service logic tests |

---

## Step-by-Step Tasks

### Task 1: Fiscal Year — Backend (Repository + DTO + Service + Controller + Routes)
- **ACTION**: Create full CRUD API for fiscal years
- **IMPLEMENT**:
  - `FiscalYearRepository` with `findAll()`, `findById()`, `insert()`, `update()`, `delete()`, `setCurrent()`
  - DTOs: `CreateFiscalYearDto` (year, start_date, end_date, is_current) with validation, `UpdateFiscalYearDto` (all optional)
  - `FiscalYearService` with list, findById, create, update, delete, setCurrent — all admin-only
  - Controller: list, create, show, update, delete, setCurrent — all methods call `AuthMiddleware::require()` then check `$role === 'admin'`
  - Routes: `GET/POST /api/v1/fiscal-years`, `GET/PUT/DELETE /api/v1/fiscal-years/{id}`, `POST /api/v1/fiscal-years/{id}/set-current`
- **MIRROR**: REPOSITORY_PATTERN, SERVICE_PATTERN, CONTROLLER_PATTERN, DTO_PATTERN, ROUTE_PATTERN
- **IMPORTS**: `App\Core\Database`, `App\Api\Middleware\{AuthMiddleware,CorsMiddleware}`, `App\Api\Responses\ApiResponse`
- **GOTCHA**: `setCurrent()` must unset previous current (UPDATE is_current=0 WHERE is_current=1) then set new — wrap in transaction
- **VALIDATE**: `D:/laragon/bin/php/php-8.4.12-nts-Win32-vs17-x64/php.exe vendor/bin/phpunit --no-configuration tests/Unit/Services/FiscalYearServiceTest.php`

### Task 2: Organization — Backend (full CRUD)
- **ACTION**: Create full CRUD API for organizations
- **IMPLEMENT**:
  - `OrganizationRepository` with `findAll()`, `findById()`, `insert()`, `update()`, `delete()`, `getForSelect()`
  - DTOs: `CreateOrganizationDto` (code, name_th, abbreviation?, org_type?, region?, parent_id?) with validation (code unique, max 50 chars)
  - `OrganizationService` with list, findById, create, update, delete — admin-only
  - Controller + Routes: same pattern as fiscal years
- **MIRROR**: Same as Task 1
- **GOTCHA**: `org_type` is enum — validate against allowed values. `code` must be unique.
- **VALIDATE**: PHP syntax check + unit tests

### Task 3: Budget Categories + Items — Backend (CRUD + nested items)
- **ACTION**: Create full CRUD API for categories and their items
- **IMPLEMENT**:
  - `BudgetCategoryRepository` with `findAll()`, `findById()`, `getTree()`, `insert()`, `update()`, `delete()`
  - `BudgetCategoryItemRepository` with `findByCategoryId()`, `insert()`, `update()`, `delete()`, `softDelete()`, `restore()`
  - DTOs: `CreateCategoryDto`, `UpdateCategoryDto`, `CreateCategoryItemDto`, `UpdateCategoryItemDto`
  - `BudgetCategoryService` with list, tree, create, update, delete for both categories and items — admin-only
  - Controller: categories CRUD + items CRUD under categories
  - Routes:
    - `GET/POST /api/v1/categories`, `GET/PUT/DELETE /api/v1/categories/{id}`
    - `GET/POST /api/v1/categories/{id}/items`, `GET/PUT/DELETE /api/v1/categories/{categoryId}/items/{id}`
    - `POST /api/v1/categories/{categoryId}/items/{id}/restore`, `POST .../toggle-active`
- **MIRROR**: Same as Task 1, plus `replaceItemsUnsafe()` pattern from Day 2 for category items
- **GOTCHA**: Categories have `parent_id` self-reference (tree). Items also have `parent_id` self-reference. Level must be calculated from parent. Soft delete on items uses `deleted_at` column.
- **VALIDATE**: PHP syntax check + unit tests

### Task 4: User Management — Backend (CRUD + password handling)
- **ACTION**: Create full CRUD API for users (admin-only)
- **IMPLEMENT**:
  - `UserRepository` with `findAll()`, `findById()`, `insert()`, `update()`, `delete()`, `emailExists()`
  - DTOs: `CreateUserDto` (email, password, name, role, is_active?), `UpdateUserDto` (all optional, password optional — if provided, hash it)
  - `UserService` with list, findById, create, update, delete — admin-only. Password hashing in service on create/update.
  - Controller + Routes: `GET/POST /api/v1/users`, `GET/PUT/DELETE /api/v1/users/{id}`
- **MIRROR**: Same as Task 1
- **GOTCHA**: `role` is enum('admin','editor','viewer') — validate against allowed values. Password hashed with `password_hash()`. Email uniqueness check. Never return password in API responses.
- **VALIDATE**: PHP syntax check + unit tests

### Task 5: Frontend — Types + API functions + Stores
- **ACTION**: Create TypeScript types, API functions, and Pinia stores for all 4 entities
- **IMPLEMENT**:
  - Types: `fiscal-year.ts` (FiscalYear, CreateFiscalYear, UpdateFiscalYear), `organization.ts`, `budget-category.ts` (BudgetCategory, BudgetCategoryItem), `user.ts` (User type extends api.ts User, CreateUser, UpdateUser)
  - API: `fiscalYears.ts`, `organizations.ts`, `budgetCategories.ts`, `users.ts` — each with fetch, create, update, delete functions
  - Stores: `fiscalYears.ts`, `organizations.ts`, `budgetCategories.ts`, `users.ts` — each with list, create, update, remove actions
- **MIRROR**: FRONTEND_API_PATTERN, FRONTEND_STORE_PATTERN
- **IMPORTS**: `apiFetch` from `@/composables/useApi`, `defineStore` from `pinia`, `ref, computed` from `vue`
- **GOTCHA**: Organization API must strip `budget_allocated` formatting — keep as string for BC math consistency
- **VALIDATE**: `cd frontend && npx vue-tsc --noEmit`

### Task 6: Frontend — Admin Pages (4 CRUD pages)
- **ACTION**: Create Vue admin pages for each entity
- **IMPLEMENT**:
  - `FiscalYearListPage.vue` — table with year, dates, is_current badge, set-current button, create/edit modal
  - `OrganizationListPage.vue` — table with code, name, type, parent, create/edit modal
  - `CategoryListPage.vue` — expandable tree table with items, create/edit modal for both categories and items
  - `UserListPage.vue` — table with email, name, role, is_active, create/edit modal (password field only on create)
- **MIRROR**: RequestListPage.vue pattern (table + filters + pagination)
- **GOTCHA**: Category tree needs recursive rendering or flat list with indent level. Keep it simple — flat list with `--` prefix for depth.
- **VALIDATE**: `cd frontend && npm run build`

### Task 7: Wire Pickers into Budget Request Form
- **ACTION**: Update RequestCreatePage and RequestEditPage to use live API data for fiscal year and org dropdowns
- **IMPLEMENT**:
  - On mount, fetch fiscal years and organizations from their stores
  - Replace hardcoded `<input type="number" v-model="fiscalYear">` with `<select>` populated from `fiscalYears`
  - Replace empty org dropdown with `<select>` populated from `organizations`
  - Set default fiscal year to current (is_current = 1)
- **GOTCHA**: Must handle loading state while fetching picker data. Don't block form interaction if API is slow.
- **VALIDATE**: Manual — create form shows fiscal year dropdown with real data

### Task 8: Routes + Navigation
- **ACTION**: Add admin routes and nav links
- **IMPLEMENT**:
  - `router/index.ts`: Add routes for `/fiscal-years`, `/organizations`, `/categories`, `/users`
  - `AppLayout.vue`: Add nav links: `ปีงบประมาณ`, `หน่วยงาน`, `หมวดงบประมาณ`, `จัดการผู้ใช้` (group under admin section or show only for admin role)
- **GOTCHA**: Admin pages should check `auth.user?.role === 'admin'` — redirect non-admins. Add `meta: { requiresAdmin: true }` to routes.
- **VALIDATE**: `cd frontend && npm run build`

### Task 9: Tests
- **ACTION**: Write unit tests for DTOs and Services
- **IMPLEMENT**:
  - `FiscalYearDtoTest.php`: validate year range (2400-2700), date format, required fields
  - `OrganizationDtoTest.php`: validate code uniqueness format, org_type enum, required fields
  - `UserDtoTest.php`: validate email format, password min length, role enum, required fields
  - `FiscalYearServiceTest.php`: test setCurrent resets previous, admin-only checks, CRUD
  - `UserServiceTest.php`: test password hashing, email uniqueness, admin-only checks
- **MIRROR**: TEST_PATTERN from BudgetRequestServiceTest.php
- **GOTCHA**: Use `Database::setInstance(new \PDO('sqlite::memory:'))` in setUp, `Database::resetInstance()` in tearDown
- **VALIDATE**: All tests pass

---

## Testing Strategy

### Unit Tests

| Test | Input | Expected Output | Edge Case? |
|---|---|---|---|
| FiscalYear DTO: year too low | year=2000 | error on year field | Yes |
| FiscalYear DTO: valid year | year=2569 | no errors | No |
| Organization DTO: invalid org_type | org_type='invalid' | error on org_type | Yes |
| User DTO: duplicate email | email that exists | error on email | Yes |
| User DTO: short password | password='123' | error on password | Yes |
| FiscalYear Service: setCurrent | two fiscal years | only one has is_current=1 | Yes |
| User Service: create hashes password | plain text password | stored hash verifies | No |

### Edge Cases Checklist
- [x] Empty input on create DTOs
- [x] Maximum length on string fields
- [x] Invalid enum values (org_type, role)
- [x] Duplicate unique fields (email, code)
- [x] Admin-only enforcement on all endpoints
- [x] Self-delete prevention (user cannot delete own account)

---

## Validation Commands

### PHP Tests
```bash
D:/laragon/bin/php/php-8.4.12-nts-Win32-vs17-x64/php.exe vendor/bin/phpunit --no-configuration tests/Unit/Dtos/ tests/Unit/Services/
```
EXPECT: All tests pass

### TypeScript
```bash
cd frontend && npx vue-tsc --noEmit
```
EXPECT: Zero type errors

### Build
```bash
cd frontend && npm run build
```
EXPECT: Build succeeds

### CI
```bash
gh pr checks <PR_NUMBER>
```
EXPECT: All checks pass

---

## Acceptance Criteria
- [ ] 4 REST API resource groups (fiscal-years, organizations, categories, users) with full CRUD
- [ ] Admin-only enforcement on all write endpoints
- [ ] Budget request form shows live fiscal year + org dropdowns
- [ ] All DTOs have Thai validation messages
- [ ] Password hashing on user create/update
- [ ] Fiscal year setCurrent is atomic (transaction)
- [ ] 20+ unit tests passing
- [ ] TypeScript compiles clean
- [ ] Frontend builds successfully
- [ ] CI passes

## Completion Checklist
- [ ] Code follows Day 2 patterns exactly (Repository → Service → Controller)
- [ ] No hardcoded values — all pickers from API
- [ ] Error handling with error_log in catch blocks
- [ ] Transactions on mutations with audit logging
- [ ] Tests use SQLite in-memory isolation

## Risks
| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Category tree complexity | M | M | Keep flat list with indent, avoid recursive rendering |
| User password hashing edge cases | L | H | Mirror User model's existing password_hash pattern |
| Large number of files (~40) | M | M | Use clear naming, follow Day 2 templates exactly |
| Admin-only enforcement inconsistency | M | M | Service-level check consistent with Day 2 pattern |

## Notes
- File upload and notifications are explicitly deferred to Day 4
- Organizations have complex fields (org_type, region, province_code) — keep admin form simple, expose all fields
- Budget category items have soft delete (`deleted_at`) — include restore endpoint
- User roles: `admin`, `editor`, `viewer` — enum in DB, validate in DTO
