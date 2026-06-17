# PRD — RBAC + Multi-org Access Control (Phase 1)

- **Date:** 2026-06-17
- **Branch:** `feat/rbac-multiorg-phase1`
- **Status:** Approved design → implementation
- **Author:** brainstorm session (decisions locked with product owner)

## 1. Problem & Context

ระบบ `hr_budget` ปัจจุบันรองรับสิทธิ์แบบหยาบ: `users.role` เป็น enum `admin/editor/viewer` ระดับ **global** และ `users.department` เป็น free-text ที่ไม่ผูกกับตาราง `organizations` จริง แม้ตารางข้อมูลหลายตัว (budget_allocations, budget_monthly_snapshots, budget_requests, disbursement_*, budget_targets, files, folders) จะมีคอลัมน์ `organization_id` แล้ว แต่ไม่มี **สะพานเชื่อม user ↔ organization** จึงบังคับขอบเขต "ผู้ใช้เห็นเฉพาะหน่วยงานของตน" ไม่ได้ (= งาน per-org access scoping ที่ค้างอยู่)

เป้าหมายคือขยายจากระบบเฉพาะ HR → รองรับ **ทุกหน่วยงาน ทุกบทบาท ทุกสิทธิ์** ของสำนักงานปลัดกระทรวงยุติธรรม (~30 หน่วยงาน, โครงสร้างกระทรวง→กรม→กอง→ส่วน) พร้อมรองรับสายงานที่ตัดขวางข้ามหน่วยงาน (การเงิน, IT, ก่อสร้าง, บุคลากร, ประสานจังหวัด)

## 2. Goals / Non-Goals

**Goals (Phase 1 — backend):**
- โครงสร้าง RBAC เต็มรูป: roles + permissions + role_permissions (กำหนดสิทธิ์รายข้อ, สร้างบทบาทเองได้, เปิด/ปิดบทบาทได้)
- ตารางมอบหมาย `user_access_grants` ที่รองรับ scope 4 แบบ (organization / all / category / region)
- บังคับขอบเขตข้อมูลจริงในชั้น API (deny-by-default) สำหรับ scope `organization` (org + ลูกหลาน) และ `all`
- Endpoint จัดการบทบาท/สิทธิ์/การมอบหมาย + `GET /me/permissions`
- Seed บทบาทตั้งต้น 11 + permissions มาตรฐาน + mapping

**Non-Goals (เลื่อนไป phase ถัดไป):**
- enforcement ของ scope `category` + `region` → **Phase 3** (ตอน import งบจริง จะ pin คอลัมน์ที่แทน "ประเภทงบ/จังหวัด")
- หน้าจอ admin บน SPA สำหรับจัดการ RBAC → **Phase 1b**
- multi-step approval chain (ใช้บทบาท approver_division/department/ministry) → **Phase 4**
- import หน่วยงานจริง + งบเบิกจ่ายสะสม → **Phase 2 / Phase 3**

## 3. Locked Design Decisions

| # | คำถาม | คำตอบที่อนุมัติ |
|---|---|---|
| D1 | ความละเอียด RBAC | **เต็มรูป** (roles + permissions รายข้อ) |
| D2 | scope ตามลำดับชั้น | **เห็นตัวเอง + ลูกหลานใต้สังกัด** |
| D3 | scope ข้ามหน่วยงาน | **เต็มรูป 4 แบบ** organization/all/category/region |
| D4 | ชุดบทบาท | 11 บทบาท เปิด/ปิดได้ (ยกเว้น super_admin) |
| D5 | ผู้ใช้เดิม | `users.role='admin'` = Super Admin (เห็นทุกอย่าง), department เลิกใช้ |

## 4. Schema (migrations 066–069)

### 066 — RBAC core
```
roles
  id PK, code VARCHAR(50) UNIQUE, name_th VARCHAR(255), name_en VARCHAR(255) NULL,
  description TEXT NULL, is_system TINYINT(1) DEFAULT 0, is_active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0, created_at, updated_at

permissions
  id PK, code VARCHAR(100) UNIQUE (เช่น 'budget.edit'), name_th VARCHAR(255),
  resource VARCHAR(50) (กลุ่ม: budget/request/disbursement/org/user/role/report),
  created_at

role_permissions
  role_id INT, permission_id INT, PRIMARY KEY(role_id, permission_id),
  FK→roles, FK→permissions (ON DELETE CASCADE)
```

### 067 — assignment / membership
```
user_access_grants
  id PK, user_id INT NOT NULL, role_id INT NOT NULL,
  scope_type ENUM('organization','all','category','region') NOT NULL DEFAULT 'organization',
  scope_ref_id INT NULL  (organization_id | budget classification id | region/province id; NULL เมื่อ scope=all),
  is_active TINYINT(1) DEFAULT 1, created_by INT NULL, created_at, updated_at,
  UNIQUE(user_id, role_id, scope_type, scope_ref_id),
  FK→users, FK→roles
```

### 068 — users adjust (backward-compatible)
- คง `users.role` ไว้ (admin = super admin); ทำเครื่องหมาย `users.department` เป็น deprecated (ยังไม่ DROP — กัน data loss; ลบจริง Phase 2)

### 069 — seed roles/permissions/role_permissions (idempotent INSERT ... ON DUPLICATE KEY)

## 5. Seed — Roles × Permissions

**Permissions (resource.action):**
`budget.view, budget.create, budget.edit, budget.delete,
request.view, request.create, request.submit, request.approve, request.reject,
disbursement.view, disbursement.record,
org.view, org.manage, user.manage, role.manage, masterdata.manage, report.view`

**Roles (11):** super_admin🔒, org_admin, planner, budget_editor, finance_officer, approver_division, approver_department, approver_ministry, auditor, executive, viewer

| permission | super | org_admin | planner | budget_editor | finance_officer | appr_div | appr_dept | appr_min | auditor | executive | viewer |
|---|:-:|:-:|:-:|:-:|:-:|:-:|:-:|:-:|:-:|:-:|:-:|
| budget.view / report.view | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| budget.create/edit | ✓ | ✓ | ✓ | ✓ | ✓ | | | | | | |
| budget.delete | ✓ | ✓ | | | | | | | | | |
| disbursement.view | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| disbursement.record | ✓ | ✓ | | ✓ | ✓ | | | | | | |
| request.view | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| request.create/submit | ✓ | ✓ | ✓ | ✓ | | | | | | | |
| request.approve/reject | ✓ | ✓ | | | | ✓ | ✓ | ✓ | | | |
| org.view | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| user.manage | ✓ | ✓ | | | | | | | | | |
| org.manage | ✓ | | | | | | | | | | |
| role.manage / masterdata.manage | ✓ | | | | | | | | | | |

*(super_admin ได้ทุก permission โดยปริยายผ่าน bypass — ไม่ผูกแถวใน role_permissions ก็ได้ แต่ seed ให้ครบเพื่อความชัดเจน)*

**สายงานจริง → grant ตัวอย่าง (ใช้ตอน Phase 2/3):**
| หน่วยงาน | role | scope_type | scope_ref |
|---|---|---|---|
| กองยุทธศาสตร์และแผนงาน | planner + executive | all | — |
| กองบริหารการคลัง | finance_officer | all | — |
| กองบริหารทรัพยากรบุคคล | budget_editor | category | งบบุคลากร |
| ศูนย์เทคโนโลยีสารสนเทศฯ | budget_editor | category | เทคโนโลยี/ดิจิทัล |
| กองออกแบบและก่อสร้าง | budget_editor | category | อาคาร/ก่อสร้าง |
| กองประสานราชการยุติธรรมจังหวัด | org_admin | region | จังหวัด |

## 6. Enforcement Model

- **AccessScopeResolver** (service): รับ user → คืน "ขอบเขต" = { isSuperAdmin, orgIds[], categoryIds[], regionCodes[], permissions[] }
  - super admin (`users.role='admin'`) → `isSuperAdmin=true` → ข้ามตัวกรองทั้งหมด
  - รวมทุก grant ที่ `is_active=1` และ `role.is_active=1`
  - `scope=organization` → org + ลูกหลานทั้งหมด ผ่าน **recursive CTE** บน `organizations.parent_id` (MySQL 8 รองรับ)
  - `scope=all` → ทุก org
  - permissions = union ของ permission จากทุก role ที่ถือ
- **AuthMiddleware / controller guard**: ต้องมี permission ที่ระบุต่อ route; ถ้าไม่มี → 403 (deny-by-default)
- **Repository scoping**: query บนตารางที่มี `organization_id` ต่อท้าย `WHERE organization_id IN (:orgIds)` (หรือไม่กรองถ้า super admin / มี scope=all) — ใช้ parameterized เสมอ (กัน SQL injection)
- Phase 1 บังคับเฉพาะ `organization` + `all`; `category`/`region` เก็บไว้ resolve แต่ยังไม่กรอง (รอ Phase 3)

## 7. API Surface (Phase 1)

- `GET /api/v1/permissions` — รายการสิทธิ์ทั้งหมด (role.manage)
- `GET/POST/PUT/DELETE /api/v1/roles`, `GET/PUT /api/v1/roles/{id}/permissions` (role.manage)
- `PATCH /api/v1/roles/{id}` (toggle is_active)
- `GET/POST/DELETE /api/v1/users/{id}/access-grants` (user.manage; org_admin จำกัดในสังกัด)
- `GET /api/v1/me/permissions` — สิทธิ์ + ขอบเขตของผู้ใช้ปัจจุบัน (ให้ SPA ใช้)
- ทุก endpoint ใช้ `ApiResponse` envelope + AuthMiddleware (JWT) เดิม

## 8. Security Considerations (enterprise)

- **Deny-by-default**: ไม่มี permission → ปฏิเสธ; ไม่มี grant → เห็นเฉพาะข้อมูล public/ของตน (Phase 1: ข้อมูล org NULL เห็นเฉพาะ super admin)
- **Privilege escalation guard**: ผู้ที่มี `user.manage` (org_admin) มอบได้เฉพาะบทบาทที่ ≤ สิทธิ์ตน และเฉพาะ org ในสังกัด; ห้ามมอบ super_admin/role.manage
- **Audit trail**: บันทึกการเปลี่ยน role/grant ลง `activity_logs` (action: role.assign/revoke/update)
- **SQL injection**: scope filter ใช้ prepared statements + bound array เท่านั้น
- **Mass assignment guard**: DTO รับเฉพาะ field ที่อนุญาต
- **is_system roles**: ลบ/แก้ code ไม่ได้; super_admin ปิดไม่ได้
- ไม่มี secret hardcode; ทดสอบด้วยบัญชี seed local เท่านั้น

## 9. Testing (TDD)

- **Unit**: AccessScopeResolver (descendant resolution, grant union, super-admin bypass, inactive role/grant excluded), permission checker
- **Integration**: endpoint roles/permissions/grants CRUD; deny-by-default 403; org-scoped list คืนเฉพาะ in-scope; super admin เห็นทั้งหมด; org_admin มอบบทบาทข้ามสังกัดไม่ได้ (403)
- **Regression**: login เดิม + endpoint เดิมยังทำงาน (admin ไม่ถูกบล็อก)
- เป้า coverage ≥ 80% เฉพาะโค้ดใหม่

## 10. Rollout / Backfill

- migrations 066–069 รันผ่าน `run_migrations.*` (อัปเดต runner)
- ผู้ใช้เดิม: admin → super admin (ไม่ต้อง grant); editor/viewer → ยังไม่มี grant จนกว่า admin มอบ (ปลอดภัยโดย default)
- ข้อมูล `organization_id = NULL` → เห็นเฉพาะ super admin จนกว่า assign (Phase 2)
- rollback: drop ตารางใหม่ + คืน users.department (ไม่ถูกแตะ)
