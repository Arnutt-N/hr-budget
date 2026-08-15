-- ============================================================================
-- 079_create_allowance_rates.sql
-- Phase 9 (งบบุคลากร) — ตารางที่ 4/13
-- `allowance_rates` = อัตราเงินเพิ่ม แยกตามระดับ/สายงาน + ช่วงเวลา
-- แก้กับดักระบบเดิม #5 (per_extratype.ex_amt เป็นก้อนเดียว รองรับอัตรา
-- ต่างตามระดับไม่ได้) และ #3 (ex_code ชนิดข้อมูล 4 แบบ)
--
-- กติกาสำคัญ (สูตรในเอกสารออกแบบ §rate(a,p)):
--   การไม่มีแถว = ไม่มีสิทธิ์ (ไม่มีคอลัมน์ eligible แยก)
--   fallback_amount = NULL หมายถึงไม่มีพื้น → ได้ 0 (ไม่ใช่ 3,500)
--   derives_from_type_id ต้องเป็นกราฟไร้วงจร — ตรวจตอนบันทึกแถว ไม่ใช่ตอนคำนวณ
--   ⚠ กันวงวนที่ DB ไม่ได้: MySQL ห้ามใช้คอลัมน์ FK ใน CHECK (ERROR 3823)
--   และ repo ไม่ใช้ trigger (หลีกเลี่ยง DELIMITER) ⇒ การตรวจวงจร (A→B→A)
--   เป็นหน้าที่ของ write path (AllowanceRateService): เดินกราฟตอนบันทึก
--   แม้แต่ self-cycle (A→A) ก็ DB กันไม่ได้ — ห้ามคาดหวังจาก schema
--
-- ตัวอย่างชุดค.ต.น. (seed ทีหลังใน P4): 7 แถว derived อ้างเงินประจำตำแหน่ง
-- เฉพาะ ชำนาญการพิเศษขึ้นไป โดยมี fallback=3500 เฉพาะชำนาญการพิเศษเท่านั้น
--
-- ออกแบบเต็ม: PRPs/2026-08-09_personnel-budget-schema-design.md
-- Idempotent: CREATE TABLE IF NOT EXISTS.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS allowance_rates (
  id                    INT NOT NULL AUTO_INCREMENT,
  allowance_type_id     INT NOT NULL COMMENT 'FK: allowance_types.id',
  level_code            VARCHAR(20) DEFAULT NULL COMMENT 'ระดับตำแหน่ง — NULL = ทุกระดับ',
  line_code             VARCHAR(20) DEFAULT NULL COMMENT 'สายงาน — NULL = ทุกสาย',
  amount                DECIMAL(12,2) DEFAULT NULL COMMENT 'จำนวนเงิน (ความหมายตาม rate_kind ของ type)',
  percent               DECIMAL(6,3) DEFAULT NULL COMMENT 'ถ้า type.basis=percent_of_salary เช่น 10.000 = 10%',
  derives_from_type_id  INT DEFAULT NULL COMMENT 'FK: allowance_types.id — จำนวนเงิน = ของ type นั้นในตำแหน่งเดียวกัน',
  fallback_amount       DECIMAL(12,2) DEFAULT NULL COMMENT 'ใช้เมื่อ derives_from ไม่มี/เป็น 0 — NULL = ไม่มีพื้น → 0',
  effective_from        DATE NOT NULL COMMENT 'วันเริ่มมีผลของอัตรา',
  effective_to          DATE DEFAULT NULL COMMENT 'NULL = มีผลถึงปัจจุบัน',
  doc_no                VARCHAR(100) DEFAULT NULL COMMENT 'เลขที่คำสั่ง/ประกาศที่กำหนดอัตรา',
  is_active             TINYINT(1) DEFAULT 1,
  deleted_at            TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
  created_at            TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at            TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by            INT DEFAULT NULL COMMENT 'ผู้สร้าง',
  updated_by            INT DEFAULT NULL COMMENT 'ผู้แก้ไขล่าสุด',
  PRIMARY KEY (id),
  KEY idx_allowance_rates_lookup (allowance_type_id, level_code, line_code, effective_from),
  KEY idx_allowance_rates_derives_from (derives_from_type_id),
  KEY idx_allowance_rates_deleted (deleted_at),
  CONSTRAINT fk_allowance_rates_type FOREIGN KEY (allowance_type_id)
    REFERENCES allowance_types (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_allowance_rates_derives_from FOREIGN KEY (derives_from_type_id)
    REFERENCES allowance_types (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='อัตราเงินเพิ่มรายระดับ/สายงาน+ช่วงเวลา — ไม่มีแถว = ไม่มีสิทธิ์';

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'allowance_rates exists' AS check_name,
       COUNT(*) AS actual, 1 AS expected
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'allowance_rates';
