-- ============================================================================
-- 083_create_salary_increment_policy.sql
-- Phase 9 (งบบุคลากร) — ตารางที่ 8/13 · กลุ่มเลื่อนเงินเดือน
-- `salary_increment_policy` = อัตราประมาณการเลื่อนเงินเดือน รายปีงบ×ประเภท
-- ห้ามฝังในโค้ด — เปลี่ยนได้ทุกปี
--
-- ค่าปีงบ 2569 ที่ผู้ใช้ยืนยัน (2026-08-10):
--   civil_servant       3.00   (3% เต็มทุกคน ไม่ใช่ค่าเฉลี่ย)
--   permanent_employee  3.00   (มีแค่ส่วนกลาง)
--   government_employee 4.00   (สูงกว่าเพื่อน)
--
-- ใช้ fiscal_year_id FK → fiscal_years(id) ตาม convention ของ repo
-- (AGENTS.md: "Most budget queries scope by fiscal_year_id")
--
-- Seed ค่า 2569 ได้เลยเมื่อมั่นใจว่ามีแถวปี 2569 ใน fiscal_years — เช็คก่อน
-- INSERT ด้วย INSERT ... SELECT จาก fiscal_years (id ไม่ stable ข้ามสภาพแวดล้อม
-- แต่ year=2569 เป็น UNIQUE key ธรรมชาติ — ถ้าปียังไม่ถูกสร้าง ก็ข้าม seed ไปก่อน)
--
-- ออกแบบเต็ม: PRPs/2026-08-09_personnel-budget-schema-design.md
-- Idempotent: CREATE TABLE IF NOT EXISTS + INSERT ผูกกับ fiscal_years.year (UNIQUE).
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS salary_increment_policy (
  id                INT NOT NULL AUTO_INCREMENT,
  fiscal_year_id    INT NOT NULL COMMENT 'FK: fiscal_years.id — ปีงบที่ policy มีผล',
  employee_category ENUM('civil_servant','government_employee','permanent_employee')
                    NOT NULL,
  max_percent       DECIMAL(5,2) NOT NULL COMMENT 'อัตราประมาณการเลื่อน % — ใช้เต็มทุกคน',
  is_active         TINYINT(1) DEFAULT 1,
  deleted_at        TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
  created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by        INT DEFAULT NULL COMMENT 'ผู้สร้าง',
  updated_by        INT DEFAULT NULL COMMENT 'ผู้แก้ไขล่าสุด',
  PRIMARY KEY (id),
  UNIQUE KEY uk_salary_increment_policy (fiscal_year_id, employee_category),
  KEY idx_salary_increment_policy_deleted (deleted_at),
  CONSTRAINT fk_salary_increment_policy_fiscal_year FOREIGN KEY (fiscal_year_id)
    REFERENCES fiscal_years (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='อัตราประมาณการเลื่อนเงินเดือนรายปีงบ×ประเภท — ห้ามฝังโค้ด';

-- --- Seed ปีงบ 2569 (ยืนยันผู้ใช้ 2026-08-10) — ข้ามถ้าปียังไม่มีใน fiscal_years
INSERT IGNORE INTO salary_increment_policy
  (fiscal_year_id, employee_category, max_percent)
SELECT fy.id, v.employee_category, v.max_percent
FROM fiscal_years fy
JOIN (
           SELECT 'civil_servant' AS employee_category, 3.00 AS max_percent
  UNION ALL SELECT 'permanent_employee',  3.00
  UNION ALL SELECT 'government_employee', 4.00
) v ON TRUE
WHERE fy.year = 2569;

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'salary_increment_policy rows (2569)' AS check_name,
       COUNT(*) AS actual, 3 AS expected
FROM salary_increment_policy sip
JOIN fiscal_years fy ON fy.id = sip.fiscal_year_id
WHERE fy.year = 2569;
