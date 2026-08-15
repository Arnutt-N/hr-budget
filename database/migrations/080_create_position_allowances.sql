-- ============================================================================
-- 080_create_position_allowances.sql
-- Phase 9 (งบบุคลากร) — ตารางที่ 5/13
-- `position_allowances` = "สิทธิ์" ระดับตำแหน่ง — ใช้ตอนตั้งงบ (ยังไม่รู้ว่าใครจะครอง)
-- โคลนแนวคิด per_pos_mgtsalary (PK pos_id+ex_code ไม่มีคอลัมน์จำนวนเงิน)
--
-- คู่ตรงข้าม: personnel_allowances (081) = "การรับจริง" ระดับคน ใช้ตอนเบิกจ่าย
-- ผลต่างของสองตาราง = งบที่ตั้งไว้แต่ไม่ได้ใช้ ← ตัวเลขที่ผู้บริหารอยากรู้ที่สุด
--
-- อัตราว่างมีสิทธิ์ แต่ไม่มีการรับจริง — เหตุผลที่สองตารางนี้ต้องแยกกัน
--
-- ออกแบบเต็ม: PRPs/2026-08-09_personnel-budget-schema-design.md
-- Idempotent: CREATE TABLE IF NOT EXISTS.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS position_allowances (
  id                INT NOT NULL AUTO_INCREMENT,
  position_id       INT NOT NULL COMMENT 'FK: positions.id',
  allowance_type_id INT NOT NULL COMMENT 'FK: allowance_types.id',
  effective_from    DATE NOT NULL COMMENT 'วันเริ่มมีสิทธิ์',
  effective_to      DATE DEFAULT NULL COMMENT 'NULL = มีสิทธิ์ถึงปัจจุบัน',
  doc_no            VARCHAR(100) DEFAULT NULL COMMENT 'เลขที่คำสั่งที่ให้สิทธิ์',
  is_active         TINYINT(1) DEFAULT 1,
  deleted_at        TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
  created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by        INT DEFAULT NULL COMMENT 'ผู้สร้าง',
  updated_by        INT DEFAULT NULL COMMENT 'ผู้แก้ไขล่าสุด',
  PRIMARY KEY (id),
  UNIQUE KEY uk_position_allowances (position_id, allowance_type_id, effective_from),
  KEY idx_position_allowances_type (allowance_type_id),
  KEY idx_position_allowances_deleted (deleted_at),
  CONSTRAINT fk_position_allowances_position FOREIGN KEY (position_id)
    REFERENCES positions (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_position_allowances_type FOREIGN KEY (allowance_type_id)
    REFERENCES allowance_types (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='สิทธิ์เงินเพิ่มระดับตำแหน่ง — ใช้ตอนตั้งงบ (การรับจริงอยู่ที่ personnel_allowances)';

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'position_allowances exists' AS check_name,
       COUNT(*) AS actual, 1 AS expected
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'position_allowances';
