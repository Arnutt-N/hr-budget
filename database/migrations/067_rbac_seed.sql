-- 067_rbac_seed.sql
-- Phase 1: seed standard permissions, 11 default roles, and role->permission map.
-- Idempotent: re-runnable safely (INSERT IGNORE / join-by-code, no hardcoded ids).

SET NAMES utf8mb4;

-- 1) Permissions (resource.action)
INSERT IGNORE INTO `permissions` (`code`, `name_th`, `resource`) VALUES
  ('budget.view',        'ดูข้อมูลงบประมาณ',          'budget'),
  ('budget.create',      'สร้างข้อมูลงบประมาณ',        'budget'),
  ('budget.edit',        'แก้ไขข้อมูลงบประมาณ',        'budget'),
  ('budget.delete',      'ลบข้อมูลงบประมาณ',          'budget'),
  ('request.view',       'ดูคำขอ',                    'request'),
  ('request.create',     'สร้างคำขอ',                  'request'),
  ('request.submit',     'ส่งคำขอ',                    'request'),
  ('request.approve',    'อนุมัติคำขอ',                'request'),
  ('request.reject',     'ไม่อนุมัติคำขอ',             'request'),
  ('disbursement.view',  'ดูข้อมูลการเบิกจ่าย',        'disbursement'),
  ('disbursement.record','บันทึกการเบิกจ่าย',          'disbursement'),
  ('org.view',           'ดูข้อมูลหน่วยงาน',           'org'),
  ('org.manage',         'จัดการหน่วยงาน',             'org'),
  ('user.manage',        'จัดการผู้ใช้และการมอบบทบาท',  'user'),
  ('role.manage',        'จัดการบทบาทและสิทธิ์',        'role'),
  ('masterdata.manage',  'จัดการข้อมูลหลัก',           'masterdata'),
  ('report.view',        'ดูรายงาน',                  'report');

-- 2) Roles (11; super_admin = system, cannot be disabled)
INSERT IGNORE INTO `roles` (`code`, `name_th`, `name_en`, `is_system`, `is_active`, `sort_order`) VALUES
  ('super_admin',        'ผู้ดูแลระบบสูงสุด',        'Super Administrator', 1, 1, 1),
  ('org_admin',          'ผู้ดูแลหน่วยงาน',          'Organization Admin',  0, 1, 2),
  ('planner',            'เจ้าหน้าที่แผน/จัดทำคำขอ',  'Planner',             0, 1, 3),
  ('budget_editor',      'เจ้าหน้าที่งบประมาณ',       'Budget Editor',       0, 1, 4),
  ('finance_officer',    'เจ้าหน้าที่การเงิน/เบิกจ่าย','Finance Officer',     0, 1, 5),
  ('approver_division',  'ผู้อนุมัติระดับกอง',        'Division Approver',   0, 1, 6),
  ('approver_department','ผู้อนุมัติระดับกรม',        'Department Approver', 0, 1, 7),
  ('approver_ministry',  'ผู้อนุมัติระดับกระทรวง',    'Ministry Approver',   0, 1, 8),
  ('auditor',            'ผู้ตรวจสอบ',               'Auditor',             0, 1, 9),
  ('executive',          'ผู้บริหาร (ดูภาพรวม)',      'Executive',           0, 1, 10),
  ('viewer',             'ผู้ดูข้อมูลทั่วไป',         'Viewer',              0, 1, 11);

-- 3) role_permissions (join by code; idempotent)
-- super_admin = ทุกสิทธิ์
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r CROSS JOIN `permissions` p WHERE r.code = 'super_admin';

-- helper pattern: INSERT ... SELECT จาก roles+permissions ตาม code list
-- org_admin
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p
WHERE r.code = 'org_admin' AND p.code IN
  ('budget.view','budget.create','budget.edit','budget.delete','report.view',
   'disbursement.view','disbursement.record','request.view','request.create',
   'request.submit','request.approve','request.reject','org.view','user.manage');

-- planner
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p
WHERE r.code = 'planner' AND p.code IN
  ('budget.view','budget.create','budget.edit','report.view','disbursement.view',
   'request.view','request.create','request.submit','org.view');

-- budget_editor
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p
WHERE r.code = 'budget_editor' AND p.code IN
  ('budget.view','budget.create','budget.edit','report.view','disbursement.view',
   'disbursement.record','request.view','request.create','request.submit','org.view');

-- finance_officer
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p
WHERE r.code = 'finance_officer' AND p.code IN
  ('budget.view','budget.create','budget.edit','report.view','disbursement.view',
   'disbursement.record','request.view','org.view');

-- approver_division / approver_department / approver_ministry (สิทธิ์เหมือนกัน ต่างที่ระดับ scope)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p
WHERE r.code IN ('approver_division','approver_department','approver_ministry') AND p.code IN
  ('budget.view','report.view','disbursement.view','request.view','request.approve','request.reject','org.view');

-- auditor / executive / viewer (อ่านอย่างเดียว)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p
WHERE r.code IN ('auditor','executive','viewer') AND p.code IN
  ('budget.view','report.view','disbursement.view','request.view','org.view');
