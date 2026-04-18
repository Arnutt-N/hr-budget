# Plan: Day 3 Remaining — File Upload + Notifications + Filter/Search

## Summary
Complete the remaining Day 3 PRD scope: file upload API + Vue uploader for budget request attachments, in-app notification system (bell icon, unread count, mark-read) integrated with the approval workflow, and filter/search enhancements (fiscal year picker, date range filter) on the budget request list page.

## User Story
As an HR officer, I want to attach files to budget requests, receive in-app notifications when my requests are acted upon, and filter requests by fiscal year and date range — so that I can work with real documents, stay informed without checking manually, and find requests quickly.

## Problem → Solution
**Problem**: No file attachments on requests, no notification system, request list lacks fiscal year and date filters.
**Solution**: File upload REST API + Vue dropzone, notification bell with real-time unread count, fiscal year + date range filters on list page.

## Metadata
- **Complexity**: Large
- **Source PRD**: `.claude/PRPs/prds/hr-budget-rest-api-vue-refactor.prd.md`
- **PRD Phase**: Day 3 (remaining items: file upload, notifications, filter/search)
- **Estimated Files**: ~30

---

## UX Design

### Before
```
[Budget Request Detail Page]
├── No file attachment section
├── No notification bell in header
└── Request List Page: only status + text search filters

[Approval Actions]
├── Approve/reject → no notification sent to requester
```

### After
```
[App Header]
├── 🔔 (bell icon with red badge showing unread count)
│   └── Dropdown: latest 10 notifications, click to mark read + navigate

[Budget Request Create/Edit Page]
├── File attachment area (drag-drop or click to upload)
│   └── List of attached files with download + delete

[Budget Request Detail Page]
├── File attachments section showing uploaded files

[Request List Page Filters]
├── Status dropdown (existing)
├── Fiscal Year dropdown (from API)
├── Date range (from/to inputs)
├── Text search (existing)
```

### Interaction Changes
| Touchpoint | Before | After | Notes |
|---|---|---|---|
| File attachments | None | Upload via drag-drop, list with delete | Per-request, stored in `files` table |
| Notification on submit | None | Requester sees "request submitted" | Auto-created |
| Notification on approve/reject | None | Requester gets bell notification | Link to request detail |
| Fiscal year filter | Missing from UI | Dropdown populated from API | Backend already supports it |
| Date range filter | Not implemented | From/To date inputs | New backend filter params |

---

## Mandatory Reading

| Priority | File | Lines | Why |
|---|---|---|---|
| P0 | `src/Models/File.php` | all | Legacy upload pattern — validation, storage, DB insert |
| P0 | `src/Services/BudgetRequestService.php` | 203-300 | submit/approve/reject — notification integration points |
| P0 | `database/migrations/060_approval_workflow.sql` | 24-35 | `notifications` table schema — already exists |
| P1 | `src/Repositories/FiscalYearRepository.php` | all | Repository pattern to mirror |
| P1 | `src/Services/FiscalYearService.php` | all | Service pattern to mirror |
| P1 | `frontend/src/layouts/AppLayout.vue` | all | Where bell icon goes |
| P1 | `frontend/src/pages/RequestListPage.vue` | all | Where filter UI goes |
| P2 | `src/Dtos/BudgetRequestListQueryDto.php` | all | Extend with date filters |
| P2 | `src/Repositories/BudgetRequestRepository.php` | 109-134 | `applyFilters()` — extend for date range |

---

## Patterns to Mirror

### REPOSITORY_PATTERN
// SOURCE: src/Repositories/FiscalYearRepository.php
```php
namespace App\Repositories;
use App\Core\Database;

class FiscalYearRepository
{
    public function findAll(array $filters, int $limit, int $offset): array
    {
        $sql = "SELECT * FROM fiscal_years WHERE 1=1";
        $params = $this->applyFilters($sql, $filters);
        $sql .= " ORDER BY year DESC LIMIT ? OFFSET ?";
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
// SOURCE: src/Services/FiscalYearService.php
```php
namespace App\Services;
use App\Core\Database;
use App\Repositories\FiscalYearRepository;

class FiscalYearService
{
    public function __construct(
        private readonly FiscalYearRepository $repo = new FiscalYearRepository(),
    ) {}
    // Methods call repo, handle auth checks, wrap mutations in transactions
}
```

### CONTROLLER_PATTERN
// SOURCE: src/Api/Controllers/FiscalYearController.php
```php
final class FiscalYearController
{
    public function __construct(
        private readonly FiscalYearService $service = new FiscalYearService()
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

### FILE_UPLOAD_PATTERN (legacy to adapt)
// SOURCE: src/Models/File.php:43-111
```php
const ALLOWED_TYPES = ['pdf', 'xlsx', 'xls', 'csv', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'gif'];
const MAX_SIZE = 10 * 1024 * 1024; // 10MB

// Validate → generate stored name (uniqid + time) → mkdir → move_uploaded_file → DB insert
// Store path: uploads/{folder_path}/{storedName}
// Return: insert ID or error string
```

### FRONTEND_API_PATTERN
// SOURCE: frontend/src/api/budgetRequests.ts
```typescript
export async function fetchNotifications(): Promise<ApiResponse<Notification[]>> {
  return apiFetch<Notification[]>('/notifications')
}
```

### FRONTEND_STORE_PATTERN
// SOURCE: frontend/src/stores/fiscalYears.ts
```typescript
export const useNotificationStore = defineStore('notifications', () => {
  const notifications = ref<Notification[]>([])
  const unreadCount = ref(0)
  const loading = ref(false)
  // ...
  return { notifications, unreadCount, loading, fetchList, markRead }
})
```

### EXISTING_FILTER_BACKEND
// SOURCE: src/Repositories/BudgetRequestRepository.php:109-134
```php
private function applyFilters(string &$sql, array $filters): array
{
    $params = [];
    if (isset($filters['fiscal_year'])) {
        $sql .= " AND br.fiscal_year = ?";
        $params[] = $filters['fiscal_year'];
    }
    if (isset($filters['status'])) {
        $sql .= " AND br.request_status = ?";
        $params[] = $filters['status'];
    }
    if (isset($filters['created_by'])) {
        $sql .= " AND br.created_by = ?";
        $params[] = $filters['created_by'];
    }
    if (!empty($filters['search'])) {
        $sql .= " AND br.request_title LIKE ?";
        $params[] = "%" . $filters['search'] . "%";
    }
    return $params;
}
```

---

## NOT Building
- Folder management UI (existing legacy MVC handles this)
- File preview/inline viewer (download only)
- Push notifications / email notifications (in-app only)
- Real-time WebSocket notifications (polling from frontend)
- Notification preferences/settings (all notifications on)
- Search across multiple fields (title only as current)

---

## Files to Change

| File | Action | Justification |
|---|---|---|
| `src/Repositories/FileRepository.php` | CREATE | Data access for file attachments |
| `src/Repositories/NotificationRepository.php` | CREATE | Data access for notifications |
| `src/Services/FileService.php` | CREATE | Upload logic, validation, storage |
| `src/Services/NotificationService.php` | CREATE | Create/read/mark-read/notifications |
| `src/Dtos/CreateFileDto.php` | CREATE | File upload validation DTO |
| `src/Dtos/NotificationQueryDto.php` | CREATE | Pagination for notification list |
| `src/Api/Controllers/FileController.php` | CREATE | File upload/download/delete API |
| `src/Api/Controllers/NotificationController.php` | CREATE | Notification API endpoints |
| `routes/web.php` | UPDATE | Add file + notification route groups |
| `src/Services/BudgetRequestService.php` | UPDATE | Add notification dispatch on submit/approve/reject |
| `src/Dtos/BudgetRequestListQueryDto.php` | UPDATE | Add date_from, date_to params |
| `src/Repositories/BudgetRequestRepository.php` | UPDATE | Add date range filter to applyFilters() |
| `frontend/src/types/file.ts` | CREATE | TypeScript interfaces for files |
| `frontend/src/types/notification.ts` | CREATE | TypeScript interfaces for notifications |
| `frontend/src/api/files.ts` | CREATE | File API functions |
| `frontend/src/api/notifications.ts` | CREATE | Notification API functions |
| `frontend/src/stores/files.ts` | CREATE | File store for uploads |
| `frontend/src/stores/notifications.ts` | CREATE | Notification store + polling |
| `frontend/src/components/FileUploader.vue` | CREATE | Drag-drop upload component |
| `frontend/src/components/NotificationBell.vue` | CREATE | Bell icon + dropdown |
| `frontend/src/layouts/AppLayout.vue` | UPDATE | Add NotificationBell component |
| `frontend/src/pages/RequestCreatePage.vue` | UPDATE | Add FileUploader component |
| `frontend/src/pages/RequestEditPage.vue` | UPDATE | Add FileUploader component |
| `frontend/src/pages/RequestDetailPage.vue` | UPDATE | Show attached files |
| `frontend/src/pages/RequestListPage.vue` | UPDATE | Add fiscal year + date range filters |
| `tests/Unit/Dtos/CreateFileDtoTest.php` | CREATE | File upload validation tests |
| `tests/Unit/Services/NotificationServiceTest.php` | CREATE | Notification logic tests |

---

## Step-by-Step Tasks

### Task 1: Notification Backend — Repository + DTO + Service + Controller + Routes
- **ACTION**: Create full notification API
- **IMPLEMENT**:
  - `NotificationRepository`:
    - `findByUserId(int $userId, int $limit, int $offset): array` — ordered by created_at DESC
    - `countUnread(int $userId): int`
    - `insert(array $data): int` — create notification
    - `markRead(int $id, int $userId): bool` — single notification
    - `markAllRead(int $userId): bool` — bulk mark read
  - `NotificationQueryDto`: `page`, `perPage` with validation
  - `NotificationService`:
    - `list(int $userId, int $page, int $perPage): array{data, meta}`
    - `getUnreadCount(int $userId): int`
    - `notify(int $userId, string $type, string $title, ?string $message, ?string $link): int` — create notification
    - `markRead(int $id, int $userId): bool`
    - `markAllRead(int $userId): bool`
  - `NotificationController`:
    - `list()` — GET /api/v1/notifications — list user's notifications
    - `unreadCount()` — GET /api/v1/notifications/unread-count
    - `markRead()` — POST /api/v1/notifications/{id}/read
    - `markAllRead()` — POST /api/v1/notifications/read-all
  - Routes: register all 4 endpoints
- **MIRROR**: REPOSITORY_PATTERN, SERVICE_PATTERN, CONTROLLER_PATTERN
- **IMPORTS**: `App\Core\Database`, `App\Api\Middleware\{AuthMiddleware,CorsMiddleware}`, `App\Api\Responses\ApiResponse`
- **GOTCHA**: All endpoints filter by `AuthMiddleware::require()['id']` — user can only see own notifications
- **VALIDATE**: PHP syntax check

### Task 2: Integrate Notifications into Approval Workflow
- **ACTION**: Add notification dispatch to BudgetRequestService
- **IMPLEMENT**:
  - Add `NotificationService` as constructor dependency
  - In `submit()`: after successful submit, call `notify()` for all approvers of the org (query `approvers` table by org_id)
    - `type: 'approval_request'`, `title: 'มีคำของบประมาณรออนุมัติ'`, `message: '{request_title}'`, `link: '/requests/{id}'`
  - In `approve()`: after successful approve, call `notify()` for the requester
    - `type: 'approved'`, `title: 'คำขอได้รับการอนุมัติ'`, `message: '{request_title}'`, `link: '/requests/{id}'`
  - In `reject()`: after successful reject, call `notify()` for the requester
    - `type: 'rejected'`, `title: 'คำขอถูกปฏิเสธ'`, `message: '{request_title} — {reason}'`, `link: '/requests/{id}'`
  - Wrap each `notify()` call in try/catch so notification failure doesn't break the workflow
- **MIRROR**: SERVICE_PATTERN from BudgetRequestService.php
- **GOTCHA**: Notification dispatch must not be in the DB transaction — if notification insert fails, the approval/rejection still succeeds. Use try/catch + error_log.
- **VALIDATE**: PHP syntax check

### Task 3: File Upload Backend — Repository + Service + Controller + Routes
- **ACTION**: Create file upload/download/delete API for budget request attachments
- **IMPLEMENT**:
  - `FileRepository`:
    - `findByRequestId(int $requestId): array` — files attached to a request
    - `findById(int $id): ?array`
    - `insert(array $data): int`
    - `delete(int $id): bool`
  - `CreateFileDto`: validates `$_FILES` upload — extension against ALLOWED_TYPES, size ≤ 10MB, no error
    - `fromUpload(): self` — reads from `$_FILES['file']`
    - `validate(): array` — Thai error messages
  - `FileService`:
    - `upload(int $requestId, array $fileData, int $userId): array` — validates, moves file, inserts DB record
      - Storage path: `uploads/requests/{requestId}/{storedName}`
      - Validate request exists and user owns it (or is admin)
    - `listByRequest(int $requestId, int $userId): array` — list files for a request
    - `download(int $id, int $userId): ?array` — returns file info for download (path, name, mime)
    - `delete(int $id, int $userId): bool` — delete file from disk + DB, owner/admin only
  - `FileController`:
    - `upload()` — POST /api/v1/requests/{id}/files — multipart/form-data
    - `list()` — GET /api/v1/requests/{id}/files
    - `download()` — GET /api/v1/files/{id}/download
    - `delete()` — DELETE /api/v1/files/{id}
  - Routes: register all 4 endpoints
- **MIRROR**: REPOSITORY_PATTERN, SERVICE_PATTERN, CONTROLLER_PATTERN, FILE_UPLOAD_PATTERN
- **IMPORTS**: `App\Core\Database`, `App\Models\File` (for ALLOWED_TYPES, MAX_SIZE constants)
- **GOTCHA**:
  - `move_uploaded_file()` only works with multipart form data — controller must NOT JSON-decode body
  - Storage directory `public/uploads/requests/{requestId}/` must be created if not exists
  - File deletion removes from disk AND database
  - Validate request ownership: `$request['created_by'] === $userId || $role === 'admin'`
- **VALIDATE**: PHP syntax check

### Task 4: Filter/Search — Extend Backend for Date Range
- **ACTION**: Add date_from/date_to filters to budget request listing
- **IMPLEMENT**:
  - Update `BudgetRequestListQueryDto`:
    - Add `public readonly ?string $dateFrom = null` and `public readonly ?string $dateTo = null`
    - In `fromQueryString()`: read `$_GET['date_from']`, `$_GET['date_to']`
    - In `validate()`: validate date format (Y-m-d) if provided
    - In `toFilters()`: include date_from, date_to if non-null
  - Update `BudgetRequestRepository::applyFilters()`:
    - Add: `if (isset($filters['date_from'])) { $sql .= " AND br.created_at >= ?"; $params[] = $filters['date_from'] . ' 00:00:00'; }`
    - Add: `if (isset($filters['date_to'])) { $sql .= " AND br.created_at <= ?"; $params[] = $filters['date_to'] . ' 23:59:59'; }`
- **MIRROR**: EXISTING_FILTER_BACKEND pattern
- **GOTCHA**: Date filter uses `created_at` column. Format must be `Y-m-d`. Append time to make it inclusive.
- **VALIDATE**: PHP syntax check

### Task 5: Frontend — Notification Types + API + Store + Bell Component
- **ACTION**: Create notification frontend infrastructure
- **IMPLEMENT**:
  - `frontend/src/types/notification.ts`:
    ```typescript
    export interface Notification {
      id: number
      user_id: number
      type: 'approval_request' | 'approved' | 'rejected'
      title: string
      message: string | null
      link: string | null
      is_read: boolean
      created_at: string
    }
    ```
  - `frontend/src/api/notifications.ts`: fetchList, fetchUnreadCount, markRead, markAllRead
  - `frontend/src/stores/notifications.ts`:
    - State: `notifications`, `unreadCount`, `loading`
    - Actions: `fetchList`, `fetchUnreadCount`, `markRead`, `markAllRead`
    - Auto-poll: `setInterval` every 60s calling `fetchUnreadCount` (started in AppLayout onMount, cleared on unmount)
  - `frontend/src/components/NotificationBell.vue`:
    - Bell icon (SVG) with red badge showing `unreadCount` when > 0
    - Click toggles dropdown showing latest 10 notifications
    - Each notification: icon by type, title, message, time ago, click → markRead + router.push(link)
    - "อ่านทั้งหมด" (Mark all read) button at bottom
    - Click outside closes dropdown
- **MIRROR**: FRONTEND_API_PATTERN, FRONTEND_STORE_PATTERN
- **GOTCHA**: Dropdown must close on outside click — use `v-if` + click-outside detection via `@click.self` on overlay div
- **VALIDATE**: `cd frontend && npx vue-tsc --noEmit`

### Task 6: Frontend — File Upload Types + API + Store + Uploader Component
- **ACTION**: Create file upload frontend infrastructure
- **IMPLEMENT**:
  - `frontend/src/types/file.ts`:
    ```typescript
    export interface FileAttachment {
      id: number
      request_id: number
      original_name: string
      stored_name: string
      file_type: string
      file_size: number
      uploaded_by: number
      uploaded_by_name?: string
      created_at: string
    }
    ```
  - `frontend/src/api/files.ts`: upload (FormData), fetchByRequest, download, remove
  - `frontend/src/stores/files.ts`: byRequestId map, loading, upload/remove actions
  - `frontend/src/components/FileUploader.vue`:
    - Drag-drop zone with "คลิกหรือลากไฟล์มาวาง" text
    - Shows list of existing attachments with download link + delete button
    - Upload: creates FormData, POSTs to `/api/v1/requests/{requestId}/files`
    - File size/type validation client-side (mirror server rules)
    - Props: `requestId: number`, `disabled?: boolean`
    - Emits: `uploaded`, `removed`
- **GOTCHA**: Upload must use `FormData`, not JSON body. Header must NOT set `Content-Type` (browser sets multipart boundary automatically).
- **VALIDATE**: `cd frontend && npx vue-tsc --noEmit`

### Task 7: Frontend — Integrate FileUploader into Request Pages
- **ACTION**: Add FileUploader to create/edit/detail pages
- **IMPLEMENT**:
  - `RequestCreatePage.vue`: Add FileUploader after form submit (only after request ID exists — files need a requestId)
    - Strategy: After successful create, redirect to edit page where uploader is available
  - `RequestEditPage.vue`: Add FileUploader component, pass `requestId` from route params
  - `RequestDetailPage.vue`: Show file list (read-only for non-owner, with delete for owner/admin)
- **GOTCHA**: Files cannot be uploaded before the request exists (need requestId). On create page, show "บันทึกก่อน จึงจะแนบไฟล์ได้" message. After save, redirect to edit page.
- **VALIDATE**: Manual — create request, edit to attach file, view detail with file

### Task 8: Frontend — Add NotificationBell to AppLayout + Fiscal Year Filter
- **ACTION**: Wire notification bell and fiscal year/date filters
- **IMPLEMENT**:
  - `AppLayout.vue`:
    - Import NotificationBell component
    - Place in header between nav links and user name: `<NotificationBell />`
    - Start unread count polling in onMounted, stop in onUnmounted
  - `RequestListPage.vue`:
    - Import `useFiscalYearStore` to get fiscal year options
    - Add fiscal year `<select>` dropdown before status dropdown in filter bar
    - Add two `<input type="date">` for date_from and date_to
    - Update `applyFilters()` to include fiscal_year, date_from, date_to
    - Fetch fiscal year list on mount
- **GOTCHA**: Fiscal year store should already exist from Day 3 master data plan. Date inputs should be clearable (add "x" button or "ทั้งหมด" option).
- **VALIDATE**: `cd frontend && npm run build`

### Task 9: Tests
- **ACTION**: Write unit tests for new DTOs and services
- **IMPLEMENT**:
  - `tests/Unit/Dtos/CreateFileDtoTest.php`:
    - Valid upload → no errors
    - File too large → error
    - Invalid extension → error
    - Empty file → error
  - `tests/Unit/Services/NotificationServiceTest.php`:
    - Create notification → returns ID
    - List by user → only that user's notifications
    - Unread count → only unread
    - Mark read → is_read = 1
    - Mark all read → all for user
    - Different users isolated
  - Use SQLite in-memory: `Database::setInstance(new \PDO('sqlite::memory:'))` in setUp
  - Create notifications table in setUp: `CREATE TABLE notifications (...)`
- **MIRROR**: TEST_PATTERN from FiscalYearServiceTest.php
- **GOTCHA**: SQLite doesn't support `INSERT IGNORE` — use standard INSERT. Notifications table DDL must match MySQL schema.
- **VALIDATE**: All tests pass

---

## Testing Strategy

### Unit Tests

| Test | Input | Expected Output | Edge Case? |
|---|---|---|---|
| File DTO: valid PDF | file with .pdf ext, 5MB | no errors | No |
| File DTO: oversized | file with 15MB | error on size | Yes |
| File DTO: .exe extension | file with .exe ext | error on type | Yes |
| Notification Service: create | userId=1, type='approved' | returns int ID | No |
| Notification Service: mark read | unread notification | is_read = 1 | No |
| Notification Service: wrong user | userId=2 reads user1's notif | returns false | Yes |
| Notification Service: unread count | 3 unread, 2 read | returns 3 | No |
| Notification Service: mark all read | 5 unread | all 5 → is_read=1 | No |

### Edge Cases Checklist
- [ ] Upload file to non-existent request → 404
- [ ] Upload file to another user's request → 403
- [ ] Delete another user's file → 403
- [ ] Download file → binary stream with correct headers
- [ ] Notification for non-existent user → gracefully handled
- [ ] Empty notification list → empty array, not error
- [ ] Date from without date to → filter from date only
- [ ] Date to without date from → filter to date only

---

## Validation Commands

### PHP Tests
```bash
D:/laragon/bin/php/php-8.4.12-nts-Win32-vs17-x64/php.exe vendor/bin/phpunit --no-configuration tests/Unit/Dtos/CreateFileDtoTest.php tests/Unit/Services/NotificationServiceTest.php
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
- [ ] File upload/download/delete API works for budget request attachments
- [ ] File validation: type whitelist + 10MB max
- [ ] Notification bell shows unread count, dropdown lists notifications
- [ ] Notifications auto-created on submit/approve/reject
- [ ] Click notification → mark read + navigate to request
- [ ] Fiscal year filter dropdown on request list page
- [ ] Date range filter on request list page
- [ ] All DTOs have Thai validation messages
- [ ] Unit tests pass
- [ ] TypeScript compiles clean
- [ ] Frontend builds successfully
- [ ] CI passes

## Completion Checklist
- [ ] Code follows Repository → Service → Controller pattern
- [ ] File upload validates server-side (not just client)
- [ ] Notifications don't break approval workflow on failure
- [ ] Error handling with error_log in catch blocks
- [ ] No hardcoded values
- [ ] Tests use SQLite in-memory isolation

## Risks
| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| File upload storage path issues on shared hosting | M | H | Use relative paths, test on Laragon first |
| Notification polling performance | L | M | 60s interval is conservative; increase if needed |
| File size validation bypass | M | H | Server-side validation always; client-side is UX only |
| Date range filter performance on large datasets | L | L | `created_at` is not indexed but small dataset OK for now |

## Notes
- File attachments use the existing `files` table but with `folder_id = NULL` and reference by request ID via a query parameter approach (or add `reference_type`/`reference_id` columns). The simplest approach: create a `budget_request_files` junction table mapping `request_id` → `file_id`.
- Actually, reviewing the schema more carefully: the current `files` table has `folder_id` (not polymorphic). For budget request attachments, we should either: (a) add `reference_type` + `reference_id` columns to `files`, or (b) create a new `request_attachments` junction table. Option (b) is cleaner — doesn't modify existing schema.
- Wait — looking at migration 002, there WAS a `reference_type`/`reference_id` design, but migration 012 dropped that in favor of `folder_id`. Since migration 012 is the one that's applied (it's later), the current `files` table only has `folder_id`. For request attachments, create a simple `request_attachments` table: `id, request_id, file_id, created_at`.
- Correction — simplest approach that avoids schema changes: add `folder_id = NULL` and use a separate `request_attachments` junction table. But even simpler: just reuse the `files` table with `folder_id = NULL` and add a new column `request_id` to `files`. But that requires a migration.
- **Final decision**: Create a new migration adding `request_id` column to `files` table (nullable INT, FK to budget_requests). When `folder_id` is null and `request_id` is set, it's a request attachment. This avoids a junction table and keeps things simple.

### Migration needed
```sql
-- Migration: Add request_id to files table for budget request attachments
ALTER TABLE files ADD COLUMN request_id INT NULL AFTER folder_id;
ALTER TABLE files ADD CONSTRAINT fk_files_request 
    FOREIGN KEY (request_id) REFERENCES budget_requests(id) ON DELETE CASCADE;
CREATE INDEX idx_files_request ON files(request_id);
```
