-- Rollback 091_fix_uk_personnel_allowances.sql
-- คืน key เดิม (แบบ 081) — guarded, safe to run twice.
-- คำเตือน: ถ้ามีข้อมูลที่ชนกับ key เดิม (คน+type+วันเดียวกัน สองตำแหน่ง)
-- ADD UNIQUE จะล้มเหลวเอง ซึ่งถูกต้องแล้ว
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

SET @has = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personnel_allowances'
    AND INDEX_NAME = 'uk_personnel_allowances'
);
SET @ddl = IF(@has > 0,
  'ALTER TABLE personnel_allowances DROP INDEX uk_personnel_allowances',
  'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

SET @has = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personnel_allowances'
    AND INDEX_NAME = 'uk_personnel_allowances'
);
SET @ddl = IF(@has = 0,
  'ALTER TABLE personnel_allowances ADD UNIQUE KEY uk_personnel_allowances (person_id, allowance_type_id, effective_from)',
  'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;
