# Code Review: Day 3 — Master Data Admin CRUD

**Reviewed**: 2026-04-19
**Branch**: feat/day-3-master-data-admin-crud
**Decision**: REQUEST CHANGES — CRITICAL + HIGH issues must be fixed

## Summary
Backend patterns are solid (prepared statements, DTO whitelisting, transactions) but has a password hash leak and missing admin guards on read endpoints. Frontend modals silently swallow errors.

## Findings

### CRITICAL (2)

**C1: `AuthMiddleware::require()` returns password hash in `$user` array**
- `src/Api/Middleware/AuthMiddleware.php:58` / `src/Models/User.php:21`
- `User::find()` uses `SELECT *`, returning the bcrypt hash. All controllers hold this in `$user`.
- **Fix**: `unset($user['password'])` in `AuthMiddleware::require()` before returning.

**C2: `Database::insert()/update()` interpolate column names without validation**
- `src/Core/Database.php:120,136`
- Table/column names are string-interpolated. If any future caller passes untrusted keys → SQL injection.
- **Fix**: Add `validateIdentifier()` guard in Database methods.

### HIGH (5)

**H1: Read endpoints on FiscalYear/Organization/Category controllers lack admin role check**
- `FiscalYearController.php:20-29,59-71`, `OrganizationController.php:20-29,59-71`, `BudgetCategoryController.php:24-43,72-84`
- Any authenticated user (viewer) can list/view master data. Write ops check admin; reads don't.
- **Fix**: Add admin check to list/show methods, or use `AuthMiddleware::requireAdmin()`.

**H2: All frontend `save()` modals silently swallow API errors**
- All 4 list pages (`FiscalYearListPage.vue:26-33`, `OrganizationListPage.vue:35-42`, etc.)
- Modal closes even on API failure. User sees no feedback.
- **Fix**: Check `result.ok` before closing modal; display `result.error`.

**H3: No pagination bounds validation — DoS via `per_page=999999999`**
- All 4 controllers
- **Fix**: `$page = max(1, (int)(...)); $perPage = min(100, max(1, (int)(...)));`

**H4: `FiscalYearService::setCurrent()` race condition**
- `src/Services/FiscalYearService.php:129-150`
- No `SELECT ... FOR UPDATE` lock; concurrent requests could leave two fiscal years as "current".
- **Fix**: Add row-level lock before update.

**H5: `UserRepository::findByEmail()` returns full row with password hash**
- `src/Repositories/UserRepository.php:35-38`
- **Fix**: Use explicit column list excluding password, or `unset($row['password'])`.

### MEDIUM (6)

| # | Issue | File(s) |
|---|---|---|
| M1 | No fiscal year usage check before delete | FiscalYearService.php:120 |
| M2 | Silent level=0 when parentId references non-existent parent | BudgetCategoryService.php:101, OrganizationService.php:105 |
| M3 | No form validation in admin pages before submit | All 4 list page modals |
| M4 | Modals lack focus trap, Escape key, aria-modal | All 4 list pages |
| M5 | Shared `loading` ref causes race condition | stores/budgetCategories.ts:26 |
| M6 | Password minimum length only 6 chars (NIST recommends 8+) | Dtos/UserDto.php:33 |

### LOW (3)

| # | Issue | File(s) |
|---|---|---|
| L1 | Repeated `fromRequest()` boilerplate across all DTOs | All Dtos |
| L2 | `Record<string, unknown>` cast in UserListPage bypasses type safety | UserListPage.vue:37 |
| L3 | No empty-state messaging when tables have 0 rows | All list pages |

## Validation Results

| Check | Result |
|---|---|
| PHP Syntax | Pass (all 22 files) |
| Frontend Build | Pass |
| PHPUnit Tests | Skipped (no vendor/) |

## Verified Secure (PASS)

- SQL injection: All queries use prepared statements ✓
- Auth on every endpoint: All methods call `AuthMiddleware::require()` ✓
- Password hashing: `password_hash(PASSWORD_DEFAULT)` ✓
- Self-delete prevention: `$userId === $targetId` check ✓
- DTO field whitelisting: readonly typed properties ✓
- Role enum validation: admin/editor/viewer ✓
