-- ============================================================================
-- 091_fix_uk_personnel_allowances.sql
-- Phase 9 — review finding R2: uk ลืม position_id
--
-- เดิม (081): UNIQUE (person_id, allowance_type_id, effective_from)
-- ปัญหา: คนเดียว สิทธิ์ชนิดเดียว เริ่มวันเดียวกัน แต่ผ่านสองตำแหน่ง
-- (ย้าย/ไปช่วยราชการพร้อมคงสิทธิ์) → แถวที่สอง insert ไม่ได้ทั้งที่ถูกต้อง
--
-- ใหม่: UNIQUE (person_id, position_id, allowance_type_id, effective_from)
--
-- ตารางยังว่างทุกสภาพแวดล้อม (สร้าง 2026-08-15 ยังไม่มี write path)
-- จึงเปลี่ยน key ตรงๆ ได้โดยไม่ต้องกลัวข้อมูลชน
-- Idempotent: guarded ผ่าน information_schema.STATISTICS
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

SET @has_old = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personnel_allowances'
    AND INDEX_NAME = 'uk_personnel_allowances'
);
SET @ddl = IF(@has_old > 0,
  'ALTER TABLE personnel_allowances DROP INDEX uk_personnel_allowances',
  'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

SET @has_new = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personnel_allowances'
    AND INDEX_NAME = 'uk_personnel_allowances'
    AND COLUMN_NAME = 'position_id'
);
SET @ddl = IF(@has_new = 0,
  'ALTER TABLE personnel_allowances ADD UNIQUE KEY uk_personnel_allowances (person_id, position_id, allowance_type_id, effective_from)',
  'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'uk includes position_id' AS check_name,
       COUNT(*) AS actual, 1 AS expected
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personnel_allowances'
  AND INDEX_NAME = 'uk_personnel_allowances' AND COLUMN_NAME = 'position_id';
