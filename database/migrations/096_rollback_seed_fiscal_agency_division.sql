-- ============================================================================
-- 096_rollback_seed_fiscal_agency_division.sql
-- Remove only the exact row seeded by 096. A row that a user has renamed is
-- left alone, because its name no longer matches the source value.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

DELETE FROM divisions
WHERE code = '16002'
  AND name_th = 'สำนักงานปลัดกระทรวงยุติธรรม';
