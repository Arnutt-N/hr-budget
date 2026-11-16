-- ============================================================================
-- 095_rollback_fiscal_code_columns.sql
-- Rollback for 095_add_fiscal_code_columns.sql (PR3).
--
-- Drops the 6 indexes and 6 columns added by 095 from budget_categories,
-- plans, and projects.
--
-- Idempotent: every DROP is guarded by its own information_schema check, so a
-- second run, or a retry after a partial failure, executes only no-op DO 0
-- statements. No statement ever names an index or column it has already
-- dropped. Indexes are removed before columns.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

-- budget_categories indexes.
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'budget_categories'
       AND INDEX_NAME = 'uk_fiscal_code') > 0,
    'ALTER TABLE budget_categories DROP INDEX uk_fiscal_code', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'budget_categories'
       AND INDEX_NAME = 'idx_fiscal_parent') > 0,
    'ALTER TABLE budget_categories DROP INDEX idx_fiscal_parent', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'budget_categories'
       AND INDEX_NAME = 'idx_activity_code') > 0,
    'ALTER TABLE budget_categories DROP INDEX idx_activity_code', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'budget_categories'
       AND INDEX_NAME = 'idx_expense_cat') > 0,
    'ALTER TABLE budget_categories DROP INDEX idx_expense_cat', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- plans index.
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans'
       AND INDEX_NAME = 'idx_activity_code') > 0,
    'ALTER TABLE plans DROP INDEX idx_activity_code', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- projects index.
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'projects'
       AND INDEX_NAME = 'uk_fiscal_code') > 0,
    'ALTER TABLE projects DROP INDEX uk_fiscal_code', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- budget_categories columns.
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'budget_categories'
       AND COLUMN_NAME = 'fiscal_code') > 0,
    'ALTER TABLE budget_categories DROP COLUMN fiscal_code', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'budget_categories'
       AND COLUMN_NAME = 'fiscal_code_parent') > 0,
    'ALTER TABLE budget_categories DROP COLUMN fiscal_code_parent', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'budget_categories'
       AND COLUMN_NAME = 'activity_code') > 0,
    'ALTER TABLE budget_categories DROP COLUMN activity_code', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'budget_categories'
       AND COLUMN_NAME = 'expense_cat_code') > 0,
    'ALTER TABLE budget_categories DROP COLUMN expense_cat_code', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- plans column.
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans'
       AND COLUMN_NAME = 'activity_code') > 0,
    'ALTER TABLE plans DROP COLUMN activity_code', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- projects column.
SET @sql = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'projects'
       AND COLUMN_NAME = 'fiscal_code') > 0,
    'ALTER TABLE projects DROP COLUMN fiscal_code', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
