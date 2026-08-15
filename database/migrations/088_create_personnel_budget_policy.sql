-- ============================================================================
-- 088_create_personnel_budget_policy.sql
-- Phase 9 (งบบุคลากร) — ตารางที่ 13/13 · กลุ่มนโยบาย
-- `personnel_budget_policy` = นโยบายการคำนวณงบบุคลากรรายปีงบ — ตั้งค่าได้ ไม่ฝังโค้ด
--
-- calc_mode (ตอบคำถามข้อ 1 ของเอกสารออกแบบ):
--   prorate   = หลัก — ผ่าน months_counted รายตำแหน่ง
--              (อัตราเดิม=12 · อัตราใหม่บรรจุ ม.ค.=9 · ยุบกลางปี=นับถึงเดือนที่ยุบ)
--   snapshot  = โหมดบังคับ 12 เดือน
--
-- vacancy_rule = เกณฑ์อัตราว่างที่นับในงบของปีนั้น — ตรงกับ type ใน
--   vacancy_recruitment (087) เปลี่ยนตามหนังสือเวียนแต่ละปี
--
-- buffer_percent = ช่องปรับสำหรับ scope=personal (ค่าเช่าบ้าน) — snapshot
--   มองไม่เห็นการเปลี่ยนแปลงระหว่างปี (คนใหม่ย้ายมา / หมดสิทธิ์กลางปี)
--   ปรับที่ระดับนี้ ไม่ใช่ไปแก้ยอดรายคน
--
-- Seed ปีงบ 2569: calc_mode='prorate', vacancy_rule=NULL (ยังไม่ทราบเกณฑ์ปี 2569
-- — รอผู้ใช้), reference_date=2025-10-01 (ต้นปีงบ) — แก้ต่อได้ด้วย UPDATE
--
-- ออกแบบเต็ม: PRPs/2026-08-09_personnel-budget-schema-design.md
-- Idempotent: CREATE TABLE IF NOT EXISTS + INSERT ผูกกับ fiscal_years.year.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS personnel_budget_policy (
  id                INT NOT NULL AUTO_INCREMENT,
  fiscal_year_id    INT NOT NULL COMMENT 'FK: fiscal_years.id — หนึ่งแถวต่อปีงบ',
  vacancy_rule      ENUM('transfer_request','eligibility_list','ready_to_fill')
                    DEFAULT NULL COMMENT 'เกณฑ์อัตราว่างที่นับในงบปีนี้ — ตรงกับ vacancy_recruitment.type · NULL=ยังไม่กำหนด',
  calc_mode         ENUM('snapshot','prorate') NOT NULL DEFAULT 'prorate'
                    COMMENT 'prorate=หลัก (months_counted รายตำแหน่ง) · snapshot=บังคับ 12 เดือน',
  buffer_percent    DECIMAL(5,2) DEFAULT NULL COMMENT 'ช่องปรับ % สำหรับก้อน actuals เช่น ค่าเช่าบ้าน',
  reference_date    DATE DEFAULT NULL COMMENT 'วันอ้างอิงสภาพอัตรา/ผู้รับ ณ วันตั้งงบ',
  is_active         TINYINT(1) DEFAULT 1,
  deleted_at        TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
  created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by        INT DEFAULT NULL COMMENT 'ผู้สร้าง',
  updated_by        INT DEFAULT NULL COMMENT 'ผู้แก้ไขล่าสุด',
  PRIMARY KEY (id),
  UNIQUE KEY uk_personnel_budget_policy (fiscal_year_id),
  KEY idx_personnel_budget_policy_deleted (deleted_at),
  CONSTRAINT fk_personnel_budget_policy_fiscal_year FOREIGN KEY (fiscal_year_id)
    REFERENCES fiscal_years (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='นโยบายการคำนวณงบบุคลากรรายปีงบ — calc_mode/vacancy_rule/buffer ตั้งค่าได้';

-- --- Seed ปีงบ 2569 (แก้ต่อได้ด้วย UPDATE) — ข้ามถ้าปียังไม่มีใน fiscal_years
INSERT IGNORE INTO personnel_budget_policy
  (fiscal_year_id, calc_mode, reference_date)
SELECT fy.id, 'prorate', '2025-10-01'
FROM fiscal_years fy
WHERE fy.year = 2569;

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'personnel_budget_policy exists' AS check_name,
       COUNT(*) AS actual, 1 AS expected
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personnel_budget_policy'
UNION ALL
SELECT 'policy seeded (2569)',
       COUNT(*), 1
FROM personnel_budget_policy pbp
JOIN fiscal_years fy ON fy.id = pbp.fiscal_year_id
WHERE fy.year = 2569;
