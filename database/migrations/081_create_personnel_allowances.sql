-- ============================================================================
-- 081_create_personnel_allowances.sql
-- Phase 9 (งบบุคลากร) — ตารางที่ 6/13
-- `personnel_allowances` = "การรับจริง" ระดับคน — ใช้ตอนเบิกจ่ายจริง
-- โคลนแนวคิด per_pos_mgtsalaryhis / per_extrahis (per_id + จำนวนเงิน + ช่วงวันที่)
--
-- person_id เป็น VARCHAR อิสระโดยตั้งใจ — hr_budget ไม่มีตารางบุคลากร
-- (ตำแหน่ง ≠ ผู้ใช้ ตามเอกสารออกแบบ) เก็บรหัสบุคคลจากระบบ HR เดิม
-- ห้าม FK ไป users — users เป็นบัญชีผู้ใช้ระบบ ไม่ใช่ทะเบียนบุคลากร
--
-- ใช้ตอนตั้งงบ scope=personal (ค่าเช่าบ้าน): snapshot ของผู้รับ ณ วันอ้างอิง
-- — budget_basis='actuals' ตามสูตร "ยอดปัจจุบัน คำนวณตั้งเบิก" (ยืนยันผู้ใช้)
--
-- ออกแบบเต็ม: PRPs/2026-08-09_personnel-budget-schema-design.md
-- Idempotent: CREATE TABLE IF NOT EXISTS.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS personnel_allowances (
  id                INT NOT NULL AUTO_INCREMENT,
  person_id         VARCHAR(50) NOT NULL COMMENT 'รหัสบุคคลจากระบบ HR — VARCHAR อิสระ ไม่ FK (ไม่มีตารางบุคลากรในระบบนี้)',
  position_id       INT NOT NULL COMMENT 'FK: positions.id — ตำแหน่งที่ผูกการรับนี้',
  allowance_type_id INT NOT NULL COMMENT 'FK: allowance_types.id',
  amount            DECIMAL(12,2) NOT NULL COMMENT 'ยอดที่รับจริงรายเดือน',
  effective_from    DATE NOT NULL COMMENT 'วันเริ่มรับ',
  effective_to      DATE DEFAULT NULL COMMENT 'NULL = ยังรับอยู่',
  doc_no            VARCHAR(100) DEFAULT NULL COMMENT 'เลขที่คำสั่ง',
  doc_date          DATE DEFAULT NULL COMMENT 'วันที่คำสั่ง',
  is_active         TINYINT(1) DEFAULT 1,
  deleted_at        TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
  created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by        INT DEFAULT NULL COMMENT 'ผู้สร้าง',
  updated_by        INT DEFAULT NULL COMMENT 'ผู้แก้ไขล่าสุด',
  PRIMARY KEY (id),
  UNIQUE KEY uk_personnel_allowances (person_id, allowance_type_id, effective_from),
  KEY idx_personnel_allowances_position (position_id, allowance_type_id),
  KEY idx_personnel_allowances_effective (effective_from, effective_to),
  KEY idx_personnel_allowances_deleted (deleted_at),
  CONSTRAINT fk_personnel_allowances_position FOREIGN KEY (position_id)
    REFERENCES positions (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_personnel_allowances_type FOREIGN KEY (allowance_type_id)
    REFERENCES allowance_types (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='การรับจริงเงินเพิ่มรายคน — ใช้ตอนเบิกจ่าย (สิทธิ์อยู่ที่ position_allowances)';

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'personnel_allowances exists' AS check_name,
       COUNT(*) AS actual, 1 AS expected
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personnel_allowances';
