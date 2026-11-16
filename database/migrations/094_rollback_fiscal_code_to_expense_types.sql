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
-- Idempotent: the column-existence flag from information_schema gates every
-- statement, including the DELETE and UPDATEs. Once the first run drops the
-- column, a second run executes only no-op DO 0 statements instead of hitting
-- error 1054 on a missing fiscal_code column.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

SET @has_col = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'expense_types'
      AND COLUMN_NAME = 'fiscal_code'
);

-- 1. Remove the 3 new category rows inserted by 094.
SET @sql = IF(@has_col > 0,
    'DELETE FROM expense_types WHERE fiscal_code IN (''031'',''032'',''050'')',
    'DO 0');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Clear fiscal_code on the 3 existing rows the migration set (back to NULL).
SET @sql = IF(@has_col > 0,
    'UPDATE expense_types SET fiscal_code = NULL WHERE code = ''ET-dda6'' AND fiscal_code = ''010''',
    'DO 0');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(@has_col > 0,
    'UPDATE expense_types SET fiscal_code = NULL WHERE code = ''ET-57d1'' AND fiscal_code = ''020''',
    'DO 0');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(@has_col > 0,
    'UPDATE expense_types SET fiscal_code = NULL WHERE code = ''ET-8d6b'' AND fiscal_code = ''041''',
    'DO 0');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3+4. Drop the unique key and the column, guarded so a re-run is a no-op.
SET @sql = IF(@has_col > 0,
    'ALTER TABLE expense_types DROP INDEX uk_expense_types_fiscal_code, DROP COLUMN fiscal_code',
    'DO 0');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;