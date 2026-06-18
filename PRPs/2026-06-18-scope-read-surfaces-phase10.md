# PRP — Phase 10: ปิด org-scope ให้ครบทุก read surface

**วันที่:** 2026-06-18
**Branch:** `feat/phase10-scope-read-surfaces`
**ต่อยอดจาก:** Phase 9 (org-scope บน budget-request list/detail)

## 1. ปัญหา (Problem)

Phase 9 ทำให้ **budget-request list/detail** เคารพ RBAC subtree grants (ผ่าน
`AccessScopeResolver`) แบบ additive — ผู้ได้รับสิทธิ์ระดับหน่วยงานแม่เห็นคำขอของ
ทั้ง subtree ได้. แต่ surface อ่านอื่นยัง **ไม่สอดคล้อง**:

- **Disbursement (sessions/records)** — `DisbursementService` ยังใช้โมเดล single-org
  แบบเก่า (`$user['org_id']`, `canAccessOrg` = exact match). RBAC grant ถูกเพิกเฉย:
  regional supervisor เห็น budget-request ของลูกข่าย (Phase 9) แต่เห็น disbursement
  session ของลูกข่ายไม่ได้ → **half-honored grant** (inconsistent, under-permissive).
- **File แนบคำขอ** — `FileService::listByRequest`/`getDownloadInfo` ยัง owner-only
  (`created_by !== userId`). ผู้เห็นคำขอผ่าน subtree grant เปิด/ดาวน์โหลดไฟล์แนบไม่ได้
  → inconsistent กับ Phase 9 `canViewRequest`.

**ไม่อยู่ใน scope:** VaultFile (คลังเอกสารกลาง ไม่มี dimension org), category/region
scope (เลื่อนไป phase ถัดไป), หรือการ gate ด้วย hard `request.view` permission
(เสี่ยง lockout — Phase 9 จงใจคง additive ไว้).

## 2. หลักการ (Design principle) — "reads widen, writes stay"

ทุกการเปลี่ยนเป็น **additive READ เท่านั้น**:
- super admin (`role==='admin'`) / `scope=all` → ไม่จำกัด
- มี org grant → เห็น **own org ∪ subtree ที่ได้รับสิทธิ์**
- ไม่มี grant → เห็นแค่ own org / own request (legacy behavior คงเดิม — ไม่มี lockout)

**WRITE path ทั้งหมดไม่แตะ** — disbursement เขียนยัง force own-org (`resolveOrgId`,
`canAccessOrg`); file upload/delete ยัง owner-only.

## 3. การเปลี่ยนแปลง (Changes)

### 3.1 `DisbursementSessionRepository::applyFilters`
เพิ่ม filter `organization_ids` (IN-list) แยกจาก `organization_id` เดิม:
- non-empty → `ds.organization_id IN (?, ?, …)`
- `[]` → `1=0` (deny-all; กัน empty-IN syntax error)

### 3.2 `DisbursementService`
- inject `AccessScopeResolver $scopeResolver`
- เพิ่ม `readableOrgIds(role, user): ?array` — `null` = ไม่จำกัด (admin/all);
  ไม่งั้นคืน `unique(scope.orgIds ∪ ownOrg)`
- เพิ่ม `canReadOrg(role, user, targetOrgId): bool` — ใช้ `readableOrgIds`
- `listSessions` → set `filters['organization_ids'] = readableOrgIds()` (ถ้าไม่ null)
- `getSession` / `getActivities` / `getRecordDetail` → เปลี่ยน read-guard จาก
  `canAccessOrg` → `canReadOrg`
- `createOrFetch*` / `saveRecordItems` / `deleteSession` / `resolveOrgId` → **คงเดิม**

### 3.3 `FileService`
- inject `AccessScopeResolver $scopeResolver`
- เพิ่ม `canReadRequest(userId, role, request): bool` — mirror Phase 9 `canViewRequest`
  (admin OR own OR `org_id ∈ scope.orgIds` OR `scope.hasAll`)
- `listByRequest` → ดึง `created_by, org_id`; gate ด้วย `canReadRequest`
- `getDownloadInfo` → gate ด้วย `canReadRequest` (ดึง `org_id` เพิ่ม)
- `upload` / `delete` → **คงเดิม** (owner-only write)

## 4. Tests (TDD — RED ก่อน)

- `DisbursementScopeTestCase` (extends `RbacSqliteTestCase`) — เพิ่มตาราง
  `disbursement_sessions`
- `DisbursementScopeTest`:
  - admin เห็นทุก session
  - ungranted เห็นแค่ own org
  - org-granted เห็น own ∪ subtree
  - all-scope เห็นทุก session
  - `getSession`: subtree เปิดได้, out-of-scope → null
- `FileScopeTest` (extends RBAC base + `budget_requests`/`files`):
  - admin / owner / subtree-granted เห็นไฟล์; ungranted non-owner → `[]`

## 5. Verify (CI paused → local)
- `vendor/bin/phpunit --testsuite Unit` ผ่านทั้งหมด (ไม่มี regression)
- code review (1 รอบ) → commit → push → PR (no-CI mode จนถึง 1 ก.ค.)
