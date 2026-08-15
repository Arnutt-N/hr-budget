-- ============================================================================
-- 076_create_positions.sql
-- Phase 9 (งบบุคลากร) — ตารางที่ 1/13
-- `positions` คือ "ตัวตนของอัตรากำลัง" ที่ไม่เปลี่ยนตลอดชีพของอัตรานั้น
-- ทุกสิ่งที่เปลี่ยนได้ (เลขที่ตำแหน่ง หน่วยงาน ระดับ เงินเดือน) อยู่ใน
-- position_versions (migration 077) — โคลนแนวคิด per_pos_move ของระบบ HR เดิม
-- ที่ PK = (pos_id, pos_date) คือแหล่งความจริง ส่วน per_position เป็น cache
--
-- ตำแหน่ง ≠ ผู้ใช้: อัตราว่างที่ยังไม่มีคนครองก็มีตัวตนและอาจกินงบ
-- ห้ามผูกกับตาราง users — users.department เป็น VARCHAR อิสระ คนละเรื่อง
--
-- employee_category มี 3 ค่าเท่านั้น (ยืนยันผู้ใช้ 2026-08-10): ลูกจ้างชั่วคราว
-- ไม่มีจริงในระบบ ห้ามเพิ่มค่าที่ 4 เผื่อไว้ (จะกลายเป็น branch ที่ไม่มีวันทำงาน)
--
-- ออกแบบเต็ม: PRPs/2026-08-09_personnel-budget-schema-design.md
-- Idempotent: CREATE TABLE IF NOT EXISTS.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS positions (
  id                INT NOT NULL AUTO_INCREMENT,
  pay_no            VARCHAR(20) NOT NULL COMMENT 'เลขถือจ่าย — รหัสอ้างอิงอัตราที่ไม่เปลี่ยน',
  employee_category ENUM('civil_servant','government_employee','permanent_employee')
                    NOT NULL COMMENT 'ข้าราชการ · พนักงานราชการ · ลูกจ้างประจำ (3 ค่า ห้ามเพิ่ม)',
  created_doc_no    VARCHAR(100) DEFAULT NULL COMMENT 'เลขที่คำสั่งที่ตั้งอัตรา',
  is_active         TINYINT(1) DEFAULT 1,
  deleted_at        TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
  created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by        INT DEFAULT NULL COMMENT 'ผู้สร้าง',
  updated_by        INT DEFAULT NULL COMMENT 'ผู้แก้ไขล่าสุด',
  PRIMARY KEY (id),
  UNIQUE KEY uk_positions_pay_no (pay_no),
  KEY idx_positions_category (employee_category),
  KEY idx_positions_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='อัตรากำลัง — ตัวตนของอัตราที่ไม่เปลี่ยน (ทุกอย่างที่เปลี่ยนอยู่ใน position_versions)';

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'positions exists' AS check_name,
       COUNT(*) AS actual, 1 AS expected
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'positions';
