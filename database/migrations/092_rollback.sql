-- Rollback 092_rename_policy_tables_plural.sql
-- คืนชื่อเอกพจน์ (แบบ 083/088) — guarded, safe to run twice.
-- คำเตือน: ถ้าโค้ดส่วนไหนเริ่มอ้างชื่อพหูพจน์แล้ว จะพังทันทีหลัง rollback
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

SET @has = (
  SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'salary_increment_policies'
);
SET @ddl = IF(@has > 0,
  'RENAME TABLE salary_increment_policies TO salary_increment_policy',
  'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

SET @has = (
  SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personnel_budget_policies'
);
SET @ddl = IF(@has > 0,
  'RENAME TABLE personnel_budget_policies TO personnel_budget_policy',
  'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;
