# Plan: Document Vault → SPA + API

## Summary
Port the legacy server-rendered document vault (`FileController` + `File`/`Folder`
models + `files/index.php`) to the Vue SPA over a new layered `/api/v1/vault/*` API.
Brings the last file-management surface onto the SPA; the legacy web routes/views/
controller can be retired in a follow-up once parity is confirmed.

## User Story
As an HR division user, I want to browse the per-fiscal-year folder tree, upload /
download / delete documents, and create / delete my own folders inside the SPA, so
that I no longer drop out to the old server-rendered page for document management.

## Problem → Solution
Legacy vault is PHP MVC (`/files`, `/folders`, `/files/init`) rendering `files/index.php`.
→ New `/api/v1/vault/*` (Repository→DTO→Service→Controller) + a `DocumentVaultPage.vue`
consuming it via TanStack Query, mirroring Phase 2-5 conventions.

## Metadata
- **Complexity**: Large
- **Source PRD**: N/A (post-cutover follow-up; the 6-phase SPA refactor PRD is complete)
- **Estimated Files**: ~16 (6 backend src + 2 backend tests + ~6 frontend + routes + sidebar)

---

## Key facts discovered (ground truth)

- **`files` table** (`hr_budget_only.sql:997`): `folder_id INT NOT NULL` (FK→folders, ON DELETE CASCADE),
  `organization_id` (nullable), `original_name`, `stored_name`, `file_path`, `file_type`,
  `file_size`, `mime_type`, `description`, `uploaded_by`, `created_at`.
  **No `request_id` in the tracked schema** → in CI `files` is the vault table; vault queries
  use `WHERE folder_id = ?` (no request filtering needed).
- **`folders` table** (`hr_budget_only.sql:1069`): `name`, `fiscal_year` (nullable), `organization_id`
  (nullable), `budget_category_id` (nullable), `parent_id` (nullable, FK→folders CASCADE),
  `folder_path`, `description`, `is_system` (tinyint), `created_by`, timestamps. Seeded with 4 rows
  (ids 1-4; fiscal_year 2568/2569; id 3-4 are 2569).
- **Reusable patterns**:
  - `App\Dtos\CreateFileDto` (validate + fromUpload; allowed types pdf/xlsx/xls/csv/doc/docx/png/jpg/jpeg/gif, 10MB) — reuse as-is for upload.
  - `App\Services\FileService::detectMimeType()` (finfo + ext map) — replicate privately in VaultService (don't touch existing FileService).
  - `App\Core\Download::sendFile()` (PR #15 — CRLF/MIME guard, nosniff, RFC5987) — reuse for vault download.
  - `App\Api\Responses\ApiResponse` envelope; `CorsMiddleware::apply()` + `AuthMiddleware::require()` first in every controller method (returns `['id','role',...]`).
  - Legacy `Folder` model logic (getRootFolders/getSubfolders/getTree/getBreadcrumb CTE/initializeForYear/create with folder_path) — port into `FolderRepository`.
- **`Database` static API**: `query($sql,$p):array`, `queryOne($sql,$p):?array`, `insert($table,$data):int`, `delete($table,$where,$p):int`, `update($table,$data,$where,$p):int`. SQLite test harness: `Database::setInstance($pdo)` / `resetInstance()`.
- **Routes** registered in `routes/web.php`; api block ~line 118-145. Static/more-specific routes BEFORE parameterized `{id}` (e.g. `/vault/folders/tree`, `/vault/folders/init` before `/vault/folders/{id}`).
- **Frontend conventions**: `composables/useApi.ts > apiFetch<T>(path, init?, isFormData?)`; TanStack `queries/useX.ts` (throw on `!res.success`, invalidate keys on mutate); reactive queryKey wraps `computed(() => [...])`; `fileDownloadUrl()` returns absolute `/api/v1/...` URL for `<a href>` download; PrimeVue DataTable/Dialog/Tree; exactly one `<h1>` per page; sidebar links + route `meta.title`.

---

## Authorization model (this pass)
- **Read** (list folders/files, tree, breadcrumb, years, download): any authenticated user (parity with legacy `Auth::require`).
- **Mutate** (create folder, delete folder, upload, delete file, init year): role ∈ {`admin`,`editor`} (light RBAC hardening — viewers read-only). Legacy allowed any authed user; this is a deliberate, cheap improvement aligned with the app's roles.
- **System folders** (`is_system = 1`) cannot be deleted (legacy behavior).
- **NOT doing org-scoping** (folders.organization_id mostly null; visibility rules are a product decision) → documented follow-up.

---

## Files to Change

| File | Action | Notes |
|---|---|---|
| `src/Repositories/FolderRepository.php` | CREATE | port Folder model queries (roots/children/tree/breadcrumb/find/create/delete/years/init) |
| `src/Repositories/FileRepository.php` | UPDATE | add `findByFolderId(int): array` |
| `src/Dtos/CreateFolderDto.php` | CREATE | name (required, ≤255), parentId?, fiscalYear?, description?; `fromRequest()`+`validate()` |
| `src/Services/VaultService.php` | CREATE | orchestrate folders+files; authz; detectMimeType; path build+containment; uses Download |
| `src/Api/Controllers/VaultFolderController.php` | CREATE | listFolders, tree, years, create, delete, initYear, listFiles, upload |
| `src/Api/Controllers/VaultFileController.php` | CREATE | download, delete |
| `routes/web.php` | UPDATE | register `/api/v1/vault/*` (static before param) |
| `tests/Unit/Services/VaultServiceTest.php` | CREATE | SQLite: create/delete folder (system protected), list, authz, upload-dir |
| `tests/Unit/Dtos/CreateFolderDtoTest.php` | CREATE | validation cases |
| `frontend/src/types/vault.ts` | CREATE | `VaultFolder`, `VaultFile`, `Breadcrumb` |
| `frontend/src/api/vault.ts` | CREATE | typed apiFetch wrappers + `vaultFileDownloadUrl` |
| `frontend/src/queries/useVault.ts` | CREATE | TanStack queries+mutations (invalidate `['vault',...]`) |
| `frontend/src/pages/DocumentVaultPage.vue` | CREATE | tree + breadcrumb + file table + upload + create/delete dialogs |
| `frontend/src/router/index.ts` | UPDATE | route `/vault` (`meta.title`) |
| `frontend/src/layouts/AppLayout.vue` | UPDATE | sidebar link to `/vault` |

## NOT Building
- Org-scoping of vault visibility (follow-up).
- Folder rename / move, file description edit (legacy had no rename UI beyond create; keep parity minimal).
- Retiring the legacy `/files` web routes/controller/views (separate cutover follow-up once SPA parity confirmed).
- Touching the existing `/api/v1/requests/{id}/files` request-attachment controller/service.

---

## Step-by-Step Tasks

### Task 1: `FileRepository::findByFolderId`
- IMPLEMENT: `public function findByFolderId(int $folderId): array` → `SELECT f.*, u.name as uploaded_by_name FROM files f LEFT JOIN users u ON f.uploaded_by=u.id WHERE f.folder_id=? ORDER BY f.created_at DESC`.
- MIRROR: existing `findByRequestId`.
- VALIDATE: `php -l`.

### Task 2: `FolderRepository`
- IMPLEMENT static-free instance class (`final`), namespace `App\Repositories`, methods:
  - `findById(int): ?array` (join created_by_name)
  - `findRoots(int $year): array`, `findChildren(int $parentId): array` (+ subfolder_count/file_count subqueries as legacy)
  - `findTree(int $year): array` (flat query → build nested in PHP; mirror `Folder::buildTree`)
  - `breadcrumb(int $id): array` (recursive CTE with PHP fallback loop — copy legacy `getBreadcrumb`)
  - `availableYears(): array`
  - `create(array): int` (build `folder_path` from parent/year like legacy `Folder::create`)
  - `delete(int): int` (raw delete; system-folder guard lives in Service)
  - `topLevelBudgetCategories(): array` via `\App\Models\BudgetCategory::getTopLevelCategories()` (for init)
- MIRROR: `src/Models/Folder.php` queries verbatim; `FileRepository` style.
- GOTCHA: keep recursive CTE in a try/catch with the iterative fallback (SQLite supports CTE; MySQL ≥8 does too) so unit tests pass on SQLite.
- VALIDATE: `php -l`.

### Task 3: `CreateFolderDto`
- IMPLEMENT: readonly ctor (`?string $name`, `?int $parentId`, `?int $fiscalYear`, `?string $description`); `validate(): array` (name required + trim + ≤255; fiscalYear if present 4-digit-ish int); `fromRequest(): self` reading JSON body or `$_POST` (mirror other DTOs — check an existing `fromRequest`).
- MIRROR: an existing Dto with `fromRequest` (e.g. `CreateFiscalYearDto`/`CreateOrganizationDto`).
- VALIDATE: unit test.

### Task 4: `VaultService`
- IMPLEMENT (`final`, ctor injects `FolderRepository`, `FileRepository`):
  - `const ROLES_MUTATE = ['admin','editor']`; private `canMutate(string $role): bool`.
  - `listFolders(int $year, ?int $parentId): array` → roots or children.
  - `tree(int $year)`, `years()`, `breadcrumb(?int $folderId)`.
  - `listFiles(int $folderId): array` → `FileRepository::findByFolderId`.
  - `createFolder(CreateFolderDto $dto, int $userId, string $role): array{success,folder?,error?}` — gate canMutate; inherit fiscal_year from parent if parentId; insert via repo; return created row.
  - `deleteFolder(int $id, string $role): array{success,error?}` — gate; load; reject if `is_system`; delete.
  - `upload(int $folderId, CreateFileDto $dto, int $userId, string $role): array{success,file?,error?}` — gate; ensure folder exists; build `uploads/<folder_path|fiscalYear/orphan>` (sanitize `..`), mkdir, `move_uploaded_file`, insert with `detectMimeType($dto->tmpPath,$dto->extension)`.
  - `deleteFile(int $id, string $role): array{success,error?}` — gate; load; unlink (path-contained) + repo delete.
  - `getDownloadInfo(int $id): ?array{path,name,mime}` — load file; resolve `realpath`, contain under `BASE_PATH/public`; null if missing/outside.
  - private `detectMimeType()` (copy from FileService).
- MIRROR: `src/Services/FileService.php` (upload/getDownloadInfo/delete shape, `array{success,...}` returns, `BASE_PATH.'/public/'` paths).
- GOTCHA: gate mutations with `canMutate` returning `['success'=>false,'error'=>'ไม่มีสิทธิ์ดำเนินการ']`. Reuse the legacy `folder_path` for storage dir; sanitize `str_replace('..','',$path)`.
- VALIDATE: unit tests.

### Task 5: Controllers `VaultFolderController` + `VaultFileController`
- IMPLEMENT (`final`, ctor injects `VaultService`); every method: `CorsMiddleware::apply(); $user = AuthMiddleware::require();` then try/catch → `ApiResponse`. Map service `success=false` → `ApiResponse::error($err, 403|422)`.
  - VaultFolderController: `listFolders` (read `$_GET['year']`, `$_GET['parent']`), `tree`, `years`, `create` (CreateFolderDto::fromRequest + validate→`validationFailed`), `delete(string $id)`, `initYear` (read year from body), `listFiles(string $folderId)`, `upload(string $folderId)` (CreateFileDto::fromUpload + validate).
  - VaultFileController: `download(string $id)` (getDownloadInfo→`Download::sendFile` or notFound), `delete(string $id)`.
- MIRROR: `src/Api/Controllers/FileController.php` exactly (CORS→Auth→try/catch→ApiResponse; error_log on Throwable).
- VALIDATE: `php -l`.

### Task 6: Routes
- IMPLEMENT in `routes/web.php` after the File Attachments block (~131), with aliases:
  ```php
  use App\Api\Controllers\VaultFolderController as ApiVaultFolderController;
  use App\Api\Controllers\VaultFileController as ApiVaultFileController;
  // Document Vault — static/more-specific BEFORE parameterized {id}
  Router::get('/api/v1/vault/years', [ApiVaultFolderController::class, 'years']);
  Router::get('/api/v1/vault/folders/tree', [ApiVaultFolderController::class, 'tree']);
  Router::post('/api/v1/vault/folders/init', [ApiVaultFolderController::class, 'initYear']);
  Router::get('/api/v1/vault/folders', [ApiVaultFolderController::class, 'listFolders']);
  Router::post('/api/v1/vault/folders', [ApiVaultFolderController::class, 'create']);
  Router::get('/api/v1/vault/folders/{id}/files', [ApiVaultFolderController::class, 'listFiles']);
  Router::post('/api/v1/vault/folders/{id}/files', [ApiVaultFolderController::class, 'upload']);
  Router::delete('/api/v1/vault/folders/{id}', [ApiVaultFolderController::class, 'delete']);
  Router::get('/api/v1/vault/files/{id}/download', [ApiVaultFileController::class, 'download']);
  Router::delete('/api/v1/vault/files/{id}', [ApiVaultFileController::class, 'delete']);
  ```
- GOTCHA: `/vault/folders/tree` + `/init` + `/years` MUST precede `/vault/folders/{id}` so they aren't captured as `{id}`. `/vault/folders/{id}/files` is fine (deeper path).
- VALIDATE: hit routes locally / unit-level Router not required.

### Task 7: Frontend types + api + queries
- `types/vault.ts`: `VaultFolder { id; name; fiscal_year; parent_id; is_system; subfolder_count?; file_count?; created_by_name? }`, `VaultFile { id; original_name; file_type; file_size; mime_type?; description?; uploaded_by_name?; created_at }`, `Breadcrumb { id; name }`.
- `api/vault.ts`: `fetchYears()`, `fetchFolders(year, parent?)`, `fetchTree(year)`, `createFolder(payload)`, `deleteFolder(id)`, `initYear(year)`, `fetchFiles(folderId)`, `uploadVaultFile(folderId, file)` (FormData, isFormData=true), `deleteVaultFile(id)`, `vaultFileDownloadUrl(id)`.
- `queries/useVault.ts`: `useVaultYears`, `useVaultFolders(year, parent)`, `useVaultFiles(folderId)`, mutations `useCreateFolder/useDeleteFolder/useUploadVaultFile/useDeleteVaultFile/useInitYear` — invalidate `['vault','folders',year,parent]` / `['vault','files',folderId]` / `['vault','years']`.
- MIRROR: `api/files.ts` + `queries/useRequestFiles.ts` (reactive computed queryKey, throw on !success, `enabled` guards).

### Task 8: `DocumentVaultPage.vue`
- IMPLEMENT: `<h1>คลังเอกสาร</h1>`; year `<Select>` (from useVaultYears, default current 2569); left PrimeVue folder list/tree (roots+drill via parent state ref) with breadcrumb `<Breadcrumb>`/buttons; right PrimeVue `DataTable` of files (name, type, size formatted, uploaded_by, date) with download `<a :href="vaultFileDownloadUrl(id)">` + delete; toolbar: create-folder `Dialog` (vee-validate/zod) + `FileUpload`/native input upload to current folder; delete-folder (guard is_system in UI). Use Toast for errors. Gate mutate buttons on user role (admin/editor) from auth store.
- MIRROR: an existing list page (e.g. `OrganizationListPage.vue`) + `FileUploader.vue`.
- GOTCHA: keep exactly one `<h1>`; AppLayout topbar is a div. Size format helper inline (KB/MB).

### Task 9: Router + sidebar
- `router/index.ts`: `{ path: '/vault', name: 'vault', component: DocumentVaultPage, meta: { title: 'คลังเอกสาร' } }` (authed; no requiresAdmin).
- `AppLayout.vue`: sidebar link `/vault` with an icon (pi pi-folder).

---

## Testing Strategy
- **Unit (CI)**: `VaultServiceTest` (SQLite folders+files schema): create folder (admin/editor ok, viewer denied), delete folder (system protected, non-system ok), listFolders roots/children, listFiles, breadcrumb; `CreateFolderDtoTest` (name required/trim/length).
- **Frontend**: `vue-tsc -b` clean + `npm run build`. UI e2e is a local gate (CI e2e only runs tests/e2e/api) — skip new e2e to bound scope; manual browser check.

## Validation Commands
```powershell
$env:Path = "D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;" + $env:Path
php -l <each touched .php>
vendor/bin/phpunit tests/Unit/Api/ tests/Unit/Dtos/ tests/Unit/Services/ tests/Unit/Core/ --no-coverage   # EXPECT all green
cd frontend; npm run build                                                                                  # EXPECT vue-tsc + vite clean
```

## Acceptance Criteria
- [ ] `/api/v1/vault/*` endpoints layered (Repo→DTO→Service→Controller), ApiResponse envelope, auth required, RBAC on mutations
- [ ] Vault download uses hardened `Download::sendFile` + realpath containment
- [ ] SPA DocumentVaultPage browses tree, uploads, downloads, deletes, creates/deletes folders
- [ ] Unit tests green (incl. CI invocation); `vue-tsc` + build clean
- [ ] No change to existing request-attachment API; no org-scoping regressions

## Risks
| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| `files.folder_id NOT NULL` vs legacy request-attachment null usage | L | M | vault always sets folder_id; don't touch request-attachment path |
| Recursive CTE breadcrumb unsupported on test DB | L | M | try/catch iterative fallback (copied from legacy) |
| Folder tree perf (N+1) | L | M | tree = 1 flat query + PHP nest (mirror legacy) |
| Scope/cost creep | M | M | bounded feature set; rename/move/org-scope explicitly deferred |

## Notes
- Confidence: 7/10 single-pass (frontend page is the largest unknown).
- Reuses PR #15 `Download` helper → vault download inherits CRLF/MIME/nosniff/RFC5987 hardening for free; finfo MIME detection also closes the prior H-2 concern for vault uploads.
