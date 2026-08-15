-- ============================================================================
-- 084_create_salary_raise_rounds.sql
-- Phase 9 (งบบุคลากร) — ตารางที่ 9/13 · กลุ่มเลื่อนเงินเดือน
-- `salary_raise_rounds` = รอบเลื่อนเงินเดือน — สร้างแถวไว้ล่วงหน้า เปิดทีละรอบ
--
-- แนวคิดสำคัญ: include_in_budget คือสวิตช์เดียว
--   เปิดใช้วันหน้า = UPDATE ... SET include_in_budget=1 ไม่ใช่ migration ใหม่
--   แถวที่ปิดอยู่ยังเก็บสถานะการเลื่อนจริง (ย้อนดูว่าเงินเดือนขึ้นเมื่อไหร่)
--
-- ปีงบ 2569 เริ่มต้น: มีรอบเดียวที่ include_in_budget=1 คือ (oct, 2568)
-- ตามที่ผู้ใช้ยืนยัน "ตอนเริ่มคิดรอบ ต.ค. ปีปัจจุบันอย่างเดียว"
-- สร้างแถวรอบอื่นไว้เผื่อ (ปิดสวิตช์) ตามตัวอย่างในเอกสารออกแบบ
--
-- round_year_be = ปี พ.ศ. ของรอบ (เลขธรรมชาติ) ส่วน fiscal_year_id = ปีงบ
-- ที่รอบนั้นกระทบ (ต.ค. 2568 → ปีงบ 2569 · เม.ย. 2569 → ปีงบ 2569)
--
-- ออกแบบเต็ม: PRPs/2026-08-09_personnel-budget-schema-design.md
-- Idempotent: CREATE TABLE IF NOT EXISTS + INSERT IGNORE บน UNIQUE key.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS salary_raise_rounds (
  id                INT NOT NULL AUTO_INCREMENT,
  round_month       ENUM('apr','oct') NOT NULL COMMENT 'เม.ย. · ต.ค.',
  round_year_be     SMALLINT NOT NULL COMMENT 'ปี พ.ศ. ของรอบ',
  effective_date    DATE NOT NULL COMMENT 'วันที่รอบนี้มีผล เช่น 2568-10-01',
  fiscal_year_id    INT NOT NULL COMMENT 'FK: fiscal_years.id — ปีงบที่รอบกระทบ',
  include_in_budget TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'สวิตช์เดียว: รอบนี้นับในงบปีนี้ไหม',
  is_active         TINYINT(1) DEFAULT 1,
  deleted_at        TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
  created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by        INT DEFAULT NULL COMMENT 'ผู้สร้าง',
  updated_by        INT DEFAULT NULL COMMENT 'ผู้แก้ไขล่าสุด',
  PRIMARY KEY (id),
  UNIQUE KEY uk_salary_raise_rounds (round_month, round_year_be),
  KEY idx_salary_raise_rounds_fiscal (fiscal_year_id, include_in_budget),
  KEY idx_salary_raise_rounds_deleted (deleted_at),
  CONSTRAINT fk_salary_raise_rounds_fiscal_year FOREIGN KEY (fiscal_year_id)
    REFERENCES fiscal_years (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='รอบเลื่อนเงินเดือน — แถวสร้างล่วงหน้า เปิดทีละรอบด้วย include_in_budget';

-- --- Seed รอบรอบปีงบ 2569 — ข้ามถ้าปียังไม่มีใน fiscal_years
-- (oct 2568) เปิดใช้ = รอบเดียวที่นับในงบ 2569 · อีก 3 รอบสร้างไว้ปิดสวิตช์
INSERT IGNORE INTO salary_raise_rounds
  (round_month, round_year_be, effective_date, fiscal_year_id, include_in_budget)
SELECT v.round_month, v.round_year_be, v.effective_date, fy.id, v.include_in_budget
FROM (
           SELECT 'apr' AS round_month, 2568 AS round_year_be, '2025-04-01' AS effective_date, 2568 AS fy_year, 0 AS include_in_budget
  UNION ALL SELECT 'oct', 2568, '2025-10-01', 2569, 1
  UNION ALL SELECT 'apr', 2569, '2026-04-01', 2569, 0
  UNION ALL SELECT 'oct', 2569, '2026-10-01', 2570, 0
) v
JOIN fiscal_years fy ON fy.year = v.fy_year;

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'raise rounds seeded (fy2569)' AS check_name,
       COUNT(*) AS actual, 2 AS expected
FROM salary_raise_rounds srr
JOIN fiscal_years fy ON fy.id = srr.fiscal_year_id
WHERE fy.year = 2569
UNION ALL
SELECT 'rounds included in budget (all years)',
       COUNT(*), 1
FROM salary_raise_rounds
WHERE include_in_budget = 1;
