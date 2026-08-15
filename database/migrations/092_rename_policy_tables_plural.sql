-- ============================================================================
-- 092_rename_policy_tables_plural.sql
-- Phase 9 — review finding R3: ชื่อตารางเอกพจน์ผิดกฎ repo
--
-- .agents/skills/database_assistant/SKILL.md §Schema Standards: "Tables: Plural"
-- ตารางเดิมทั้ง 58 ตัวเป็นพหูพจน์ — สองตารางนี้เป็นข้อยกเว้นที่ไม่จำเป็น:
--   salary_increment_policy   → salary_increment_policies
--   personnel_budget_policy   → personnel_budget_policies
--
-- ทำตอนนี้เพราะตารางยังไม่มีโค้ดอ้างชื่อ (ยังไม่มี model/repository/API)
-- ปล่อยข้ามไปชื่อจะฝังลง codebase แล้วแก้แพง
--
-- RENAME TABLE คงข้อมูล (seed ปี 2569 อยู่แล้วใน hr_budget) และคง FK
-- ที่ชี้เข้าตารางโดยอัตโนมัติ (MySQL ปรับ constraint ตาม)
--
-- Idempotent: guarded ผ่าน information_schema.TABLES
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

SET @has_old = (
  SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'salary_increment_policy'
);
SET @ddl = IF(@has_old > 0,
  'RENAME TABLE salary_increment_policy TO salary_increment_policies',
  'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

SET @has_old = (
  SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personnel_budget_policy'
);
SET @ddl = IF(@has_old > 0,
  'RENAME TABLE personnel_budget_policy TO personnel_budget_policies',
  'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'policy tables plural' AS check_name,
       COUNT(*) AS actual, 2 AS expected
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('salary_increment_policies','personnel_budget_policies')
UNION ALL
SELECT 'seed rows preserved (2569 increment)',
       (SELECT COUNT(*) FROM salary_increment_policies sip
          JOIN fiscal_years fy ON fy.id = sip.fiscal_year_id
          WHERE fy.year = 2569), 3
UNION ALL
SELECT 'seed rows preserved (2569 policy)',
       (SELECT COUNT(*) FROM personnel_budget_policies pbp
          JOIN fiscal_years fy ON fy.id = pbp.fiscal_year_id
          WHERE fy.year = 2569), 1;
