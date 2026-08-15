-- ============================================================================
-- 082_create_salary_scales.sql
-- Phase 9 (งบบุคลากร) — ตารางที่ 7/13 · กลุ่มเลื่อนเงินเดือน
-- `salary_scales` = อัตราเงินเดือนขั้นต่ำ/ขั้นสูง รายระดับ — ไม่มีเลยในสกีมาเดิม
--
-- max_amount คือ "เพดานตอนประมาณการเลื่อน":
--   projected = MIN( base_salary × (1 + pct/100), salary_scales.max_amount )
-- ลืมเพดานนี้เมื่อไหร่ งบสูงเกินจริงเฉพาะกลุ่มอาวุโส = กลุ่มที่เงินก้อนใหญ่ที่สุด
--
-- ข้อมูลจริง (อัตราเงินเดือนขั้นต่ำ–ขั้นสูง) รอจากผู้ใช้ — บล็อก P4 seed
-- แต่ไม่บล็อกโครงตาราง
--
-- ออกแบบเต็ม: PRPs/2026-08-09_personnel-budget-schema-design.md
-- Idempotent: CREATE TABLE IF NOT EXISTS.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS salary_scales (
  id                INT NOT NULL AUTO_INCREMENT,
  employee_category ENUM('civil_servant','government_employee','permanent_employee')
                    NOT NULL COMMENT 'ข้าราชการ · พนักงานราชการ · ลูกจ้างประจำ',
  level_code        VARCHAR(20) NOT NULL COMMENT 'ระดับตำแหน่ง',
  effective_from    DATE NOT NULL COMMENT 'วันเริ่มใช้อัตราศาลนี้',
  effective_to      DATE DEFAULT NULL COMMENT 'NULL = ใช้ถึงปัจจุบัน',
  min_amount        DECIMAL(12,2) NOT NULL COMMENT 'อัตราเงินเดือนขั้นต่ำ',
  max_amount        DECIMAL(12,2) NOT NULL COMMENT 'อัตราเงินเดือนขั้นสูง — เพดานตอนประมาณการเลื่อน',
  doc_no            VARCHAR(100) DEFAULT NULL COMMENT 'เลขที่คำสั่ง/ประกาศกำหนดอัตรา',
  is_active         TINYINT(1) DEFAULT 1,
  deleted_at        TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
  created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by        INT DEFAULT NULL COMMENT 'ผู้สร้าง',
  updated_by        INT DEFAULT NULL COMMENT 'ผู้แก้ไขล่าสุด',
  PRIMARY KEY (id),
  UNIQUE KEY uk_salary_scales (employee_category, level_code, effective_from),
  KEY idx_salary_scales_deleted (deleted_at),
  CONSTRAINT chk_salary_scales_range CHECK (max_amount >= min_amount)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='อัตราเงินเดือนขั้นต่ำ/ขั้นสูงรายระดับ — max_amount เป็นเพดานประมาณการเลื่อน';

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'salary_scales exists' AS check_name,
       COUNT(*) AS actual, 1 AS expected
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'salary_scales';
