-- ============================================================================
-- 077_create_position_versions.sql
-- Phase 9 (งบบุคลากร) — ตารางที่ 2/13
-- `position_versions` = แหล่งความจริงของสภาพอัตรา ณ ช่วงเวลา (temporal)
-- โคลนแนวคิด per_pos_move (PK pos_id+pos_date) แต่แก้กับดักเดิม:
--   - pos_no เป็น versioned (ของเดิม versioned pay_no แต่ไม่ versioned เลขที่ตำแหน่ง)
--   - occupancy และ lifecycle แยกคอลัมน์ (ของเดิม pos_status ชื่อเดียวความหมายต่างกันตามตาราง)
--
-- คอลัมน์เฉพาะที่ตั้งใจ:
--   salary_basis      actual/estimated — ตั้งงบ พ.ย.–ธ.ค. บางหน่วยเลื่อนเงินเดือน
--                     รอบ 1 ต.ค. ยังไม่เสร็จ ⇒ default 'estimated' โดยตั้งใจ (ปลอดภัยกว่า)
--   salary_pre_raise  เงินเดือนก่อนเลื่อน — เทียบผลต่าง ประมาณ vs จริง ภายหลัง
--   months_counted    1..12 prorate รายตำแหน่ง (อัตราเดิม=12 · บรรจุ ม.ค.=9 · ยุบกลางปี=นับถึงเดือนยุบ)
--   approval_status   เลียนแบบ budget_line_items.allocated_pba vs allocated_received —
--                     คำขอที่ยังไม่อนุมัติไม่รวมยอดหลัก โดยไม่ต้องมีตารางคำขอแยก
--
-- organization_id = เจ้าของงบ (ต้นสังกัด) เสมอ — คนไปช่วยราชการอยู่ที่
-- personnel_assignments (086) ซึ่งใช้รายงานอย่างเดียว ห้าม GROUP BY ตอนคิดยอดงบ
--
-- ออกแบบเต็ม: PRPs/2026-08-09_personnel-budget-schema-design.md
-- Idempotent: CREATE TABLE IF NOT EXISTS.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS position_versions (
  id                INT NOT NULL AUTO_INCREMENT,
  position_id       INT NOT NULL COMMENT 'FK: positions.id',
  effective_from    DATE NOT NULL COMMENT 'วันเริ่มมีผลของเวอร์ชันนี้',
  effective_to      DATE DEFAULT NULL COMMENT 'NULL = มีผลถึงปัจจุบัน',
  pos_no            VARCHAR(50) DEFAULT NULL COMMENT 'เลขที่ตำแหน่ง — versioned แก้กับดักระบบเดิม',
  organization_id   INT NOT NULL COMMENT 'FK: organizations.id — เจ้าของงบ (ต้นสังกัด) เสมอ',
  level_code        VARCHAR(20) DEFAULT NULL COMMENT 'ระดับตำแหน่ง เช่น ชำนาญการพิเศษ',
  line_code         VARCHAR(20) DEFAULT NULL COMMENT 'สายงาน',
  base_salary       DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'เงินเดือนของเวอร์ชันนี้',
  salary_basis      ENUM('actual','estimated') NOT NULL DEFAULT 'estimated'
                    COMMENT 'actual=เลื่อนเสร็จแล้ว · estimated=ประมาณการ — ความน่าเชื่อถือของ base_salary',
  salary_pre_raise  DECIMAL(12,2) DEFAULT NULL COMMENT 'เงินเดือนก่อนเลื่อน — เก็บไว้เทียบตอนตัวเลขจริงมา',
  occupancy         ENUM('occupied','vacant_funded','vacant_unfunded') NOT NULL DEFAULT 'occupied'
                    COMMENT 'มีคนครอง · ว่างมีเงิน · ว่างไม่มีเงิน',
  lifecycle         ENUM('active','abolished') NOT NULL DEFAULT 'active'
                    COMMENT 'สถานะอัตรา — แยกจาก occupancy อย่ายัดรวม (กับดัก pos_status ของระบบเดิม)',
  months_counted    TINYINT NOT NULL DEFAULT 12 COMMENT '1..12 — prorate รายตำแหน่ง',
  approval_status   ENUM('approved','requested') NOT NULL DEFAULT 'approved'
                    COMMENT 'คำขอที่ยังไม่อนุมัติไม่รวมยอดหลัก — query กรอง approved เป็น default',
  order_doc_no      VARCHAR(100) DEFAULT NULL COMMENT 'เลขที่คำสั่งที่ทำให้เกิดเวอร์ชันนี้',
  order_doc_date    DATE DEFAULT NULL,
  is_active         TINYINT(1) DEFAULT 1,
  deleted_at        TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
  created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by        INT DEFAULT NULL COMMENT 'ผู้สร้าง',
  updated_by        INT DEFAULT NULL COMMENT 'ผู้แก้ไขล่าสุด',
  PRIMARY KEY (id),
  UNIQUE KEY uk_position_versions_pos_from (position_id, effective_from),
  KEY idx_position_versions_org (organization_id, effective_from),
  KEY idx_position_versions_level (level_code),
  KEY idx_position_versions_occupancy (occupancy),
  KEY idx_position_versions_deleted (deleted_at),
  CONSTRAINT fk_position_versions_position FOREIGN KEY (position_id)
    REFERENCES positions (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_position_versions_organization FOREIGN KEY (organization_id)
    REFERENCES organizations (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_position_versions_months CHECK (months_counted BETWEEN 1 AND 12)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='สภาพอัตรารายช่วงเวลา — แหล่งความจริง temporal (ตัวตนอยู่ที่ positions)';

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'position_versions exists' AS check_name,
       COUNT(*) AS actual, 1 AS expected
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'position_versions';
