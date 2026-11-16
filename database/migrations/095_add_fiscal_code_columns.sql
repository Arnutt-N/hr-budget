-- ============================================================================
-- 095_add_fiscal_code_columns.sql
-- Fiscal-code import program PR3 — add nullable storage columns and indexes so
-- PR4 can import the Thai budget codes into the existing taxonomy tables
-- (no second taxonomy). Depends on PR1 (094 expense_types.fiscal_code).
--
-- Sources:
--   16002_รหัสงบประมาณ.xlsx (sheet 16002)
--       column รหัสงบประมาณ -> 20-digit code, 13-digit parent prefix,
--       3-digit expense-category code at positions 13-15.
--   16002_กิจกรรมหลัก.xls (sheet report)
--       17-digit activity code, stored separately from the budget code.
--
-- All columns are NULL so pre-import rows stay valid and PR4 can backfill.
--
-- Idempotent: each table's DDL is guarded by an information_schema.COLUMNS
-- check. MySQL DDL is atomic per ALTER TABLE, so a table whose new column
-- exists has already received all its indexes. A second run executes only
-- no-op DO 0 statements and changes nothing.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

-- budget_categories: 20-digit code, 13-digit parent prefix, 17-digit activity
-- code, and the 3-digit expense-category code (010/020/031/032/041/050).
SET @has_bc = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'budget_categories'
      AND COLUMN_NAME = 'fiscal_code'
);
SET @ddl = IF(@has_bc = 0,
    'ALTER TABLE budget_categories
        ADD COLUMN fiscal_code CHAR(20) NULL COMMENT ''รหัสงบประมาณ 20 หลัก — คอลัมน์ รหัสงบประมาณ (16002_รหัสงบประมาณ.xlsx sheet 16002)'',
        ADD COLUMN fiscal_code_parent CHAR(13) NULL COMMENT ''รหัสแม่ 13 หลัก = substr(รหัสงบประมาณ,1,13) (16002_รหัสงบประมาณ.xlsx sheet 16002)'',
        ADD COLUMN activity_code CHAR(17) NULL COMMENT ''รหัสกิจกรรมหลัก 17 หลัก (16002_กิจกรรมหลัก.xls sheet report)'',
        ADD COLUMN expense_cat_code CHAR(3) NULL COMMENT ''หมวดรายจ่าย 3 หลัก = substr(รหัสงบประมาณ,13,3) 010/020/031/032/041/050 (16002_รหัสงบประมาณ.xlsx sheet 16002)'',
        ADD UNIQUE KEY uk_fiscal_code (fiscal_code),
        ADD KEY idx_fiscal_parent (fiscal_code_parent),
        ADD KEY idx_activity_code (activity_code),
        ADD KEY idx_expense_cat (expense_cat_code)',
    'DO 0');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- plans: the 17-digit activity code imported from 16002_กิจกรรมหลัก.xls.
SET @has_pl = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'plans'
      AND COLUMN_NAME = 'activity_code'
);
SET @ddl = IF(@has_pl = 0,
    'ALTER TABLE plans
        ADD COLUMN activity_code CHAR(17) NULL COMMENT ''รหัสกิจกรรมหลัก 17 หลัก (16002_กิจกรรมหลัก.xls sheet report)'',
        ADD KEY idx_activity_code (activity_code)',
    'DO 0');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- projects: the 20-digit fiscal code from 16002_รหัสงบประมาณ.xlsx.
SET @has_pj = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'projects'
      AND COLUMN_NAME = 'fiscal_code'
);
SET @ddl = IF(@has_pj = 0,
    'ALTER TABLE projects
        ADD COLUMN fiscal_code CHAR(20) NULL COMMENT ''รหัสงบประมาณ 20 หลัก — คอลัมน์ รหัสงบประมาณ (16002_รหัสงบประมาณ.xlsx sheet 16002)'',
        ADD UNIQUE KEY uk_fiscal_code (fiscal_code)',
    'DO 0');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
