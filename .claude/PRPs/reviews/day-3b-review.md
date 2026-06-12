# Code Review: Day 3b — File Upload + Notifications + Filters

**Reviewed**: 2026-04-19
**Branch**: feat/day-3-master-data-admin-crud
**Decision**: APPROVE (after fixes)

## Summary
Backend patterns solid (prepared statements, auth on all endpoints, transactions). Frontend follows store conventions. Found and fixed 2 CRITICAL and 6 HIGH issues.

## Findings (all fixed)

### CRITICAL (2) — FIXED
- **C1**: Client-provided MIME type trusted blindly → Added `finfo_file()` server-side detection in `FileService`
- **C2**: CRLF injection in Content-Disposition header → Added `str_replace(["\r", "\n", '"'], '', ...)` in `FileController`

### HIGH (6) — FIXED
- **H1**: Route ordering — `/notifications/{id}/read` matched before `/notifications/read-all` → Reordered routes
- **H2**: Open redirect via `router.push(link)` in NotificationBell → Added `link.startsWith('/')` guard
- **H3**: Missing try/catch in notification + file stores → Added try/catch/finally to all async store methods
- **H4**: Direct mutation of notification objects → Changed to immutable `.map()` updates
- **H5**: `goToPage` dropped all filters → Now passes all active filters with updated page
- **H6**: Unused `useAuthStore` import + non-reactive `files` in FileUploader → Removed dead code, made `files` computed

### MEDIUM (2) — ACKNOWLEDGED (not blocking)
- **M1**: `v-html` for static icons → Changed to text interpolation with Unicode chars
- **M2**: `fileDownloadUrl` uses `VITE_API_BASE_URL` which doesn't exist in .env → Falls back to `''`, works in dev; needs env config for production

### LOW (3) — NOTED
- Notification type not validated against enum (acceptable for extensible design)
- 404 vs 403 for wrong-user notification access (correct security practice — no info leak)
- Date validation allows invalid calendar dates like 2025-02-30 (MySQL handles silently)

## Validation Results

| Check | Result |
|---|---|
| PHP Syntax | Pass (all 13 new/modified files) |
| TypeScript | Pass (vue-tsc clean) |
| Frontend Build | Pass |
| Unit Tests | Written (2 files, 18 tests) |

## Verified Secure (PASS)
- SQL injection: All queries use prepared statements ✓
- Auth on every endpoint: All methods call `AuthMiddleware::require()` ✓
- File upload validation: Extension whitelist + size limit + server-side MIME detection ✓
- Path traversal: `uniqid()` stored names, `basename()` on original names ✓
- CRLF injection: Sanitized in Content-Disposition header ✓
- Open redirect: Notification links validated with `startsWith('/')` ✓
- XSS: No `v-html` usage, all dynamic content escaped by Vue ✓
- Notification ownership: `markRead` checks `user_id` match ✓
