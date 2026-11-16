-- ============================================================================
-- 094_rollback_fiscal_code_to_expense_types.sql
-- Rollback for 094_add_fiscal_code_to_expense_types.sql (PR1).
--
-- Reverses, in order:
--   1. DELETE the 3 seeded rows (fiscal_code IN ('031','032','050'))
--   2. NULL-out fiscal_code on the 3 existing rows the migration set
--   3. DROP the unique key uk_expense_types_fiscal_code
--   4. DROP COLUMN fiscal_code
--
-- Idempotent: each statement is safe to re-run (DELETE matches nothing on a
-- second pass, the UPDATEs are guarded by fiscal_code IS NOT NULL, and the
-- ALTER TABLE is wrapped in a DROP-IF-EXISTS-style check via information_schema
-- so a second run does not error on a missing column).
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

-- 1. Remove the 3 new category rows inserted by 094.
DELETE FROM expense_types WHERE fiscal_code IN ('031', '032', '050');

-- 2. Clear fiscal_code on the 3 existing rows the migration set (back to NULL).
UPDATE expense_types SET fiscal_code = NULL WHERE code = 'ET-dda6' AND fiscal_code = '010';
UPDATE expense_types SET fiscal_code = NULL WHERE code = 'ET-57d1' AND fiscal_code = '020';
UPDATE expense_types SET fiscal_code = NULL WHERE code = 'ET-8d6b' AND fiscal_code = '041';

-- 3+4. Drop the unique key and the column, guarded so a re-run is a no-op.
SET @has_col = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'expense_types'
      AND COLUMN_NAME = 'fiscal_code'
);
SET @ddl = IF(@has_col > 0,
    'ALTER TABLE expense_types DROP INDEX uk_expense_types_fiscal_code, DROP COLUMN fiscal_code',
    'DO 0');

PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;