-- ============================================================================
-- 094_add_fiscal_code_to_expense_types.sql
-- Fiscal-code import program PR1 — extend expense_types with the 6 Thai
-- budget expense-category codes (fiscal_code CHAR(3)) so the codes live in
-- the existing taxonomy instead of a second table.
--
-- Source: 16002_รหัสงบประมาณ.xlsx (sheet 16002). The 6 category codes:
--   010 งบบุคลากร                 (existing row ET-dda6)
--   020 งบดำเนินงาน               (existing row ET-57d1)
--   031 ครุภัณฑ์และค่าจ้างบริการ   (new row)
--   032 ค่าก่อสร้างและปรับปรุงอาคาร (new row)
--   041 เงินอุดหนุน              (existing row ET-8d6b, name "งบเงินอุดหนุน")
--   050 ค่าใช้จ่ายโครงการ          (new row)
--
-- Two existing rows stay at NULL fiscal_code because the source spreadsheet
-- has no matching category: ET-914f (งบลงทุน) and ET-dc9b (งบรายจ่ายอื่น).
--
-- Code convention (matches scripts/import_budget_csv.php): ET- + first 4 hex
-- chars of md5(name_th). The 3 new rows use this convention.
--
-- Encoding debt note: the 5 existing name_th values are mojibake (stored as
-- literal '?' bytes, 0x3F). This migration does NOT repair them — a migration
-- cannot read the source CSV and cannot carry clean Thai reliably across
-- charsets. The repair lives in scripts/seed_expense_type_fiscal_codes.php.
--
-- Idempotent: INSERT IGNORE on the UNIQUE key over `code` (from migration 022),
-- and UPDATE ... WHERE fiscal_code IS NULL for the code assignment, so a
-- second run changes nothing. The DDL is guarded via information_schema.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

-- Guard the DDL so a second run is a no-op (ADD COLUMN errors if it exists).
SET @has_col = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'expense_types'
      AND COLUMN_NAME = 'fiscal_code'
);
SET @ddl = IF(@has_col = 0,
    'ALTER TABLE expense_types ADD COLUMN fiscal_code CHAR(3) NULL, ADD UNIQUE KEY uk_expense_types_fiscal_code (fiscal_code)',
    'DO 0');

PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Insert the three missing category rows. code = ET- + substr(md5(name_th),0,4).
-- fiscal_code is set inline so the row carries its category code from creation.
INSERT IGNORE INTO expense_types (code, name_th, fiscal_code, sort_order, is_active) VALUES
    ('ET-552e', 'ครุภัณฑ์และค่าจ้างบริการ', '031', 7, 1),
    ('ET-12e2', 'ค่าก่อสร้างและปรับปรุงอาคาร', '032', 8, 1),
    ('ET-8035', 'ค่าใช้จ่ายโครงการ', '050', 9, 1);

-- Map fiscal_code on all six category rows. Guard with fiscal_code IS NULL so
-- a re-run is a no-op. The 3 existing rows (010/020/041) and the 3 newly
-- inserted rows (031/032/050) are all set here — the INSERT IGNORE above
-- created the new rows with fiscal_code inline, but this UPDATE also covers
-- the case where the rows pre-existed (e.g. re-run after a partial apply).
UPDATE expense_types SET fiscal_code = '010' WHERE code = 'ET-dda6' AND fiscal_code IS NULL;
UPDATE expense_types SET fiscal_code = '020' WHERE code = 'ET-57d1' AND fiscal_code IS NULL;
UPDATE expense_types SET fiscal_code = '041' WHERE code = 'ET-8d6b' AND fiscal_code IS NULL;
UPDATE expense_types SET fiscal_code = '031' WHERE code = 'ET-552e' AND fiscal_code IS NULL;
UPDATE expense_types SET fiscal_code = '032' WHERE code = 'ET-12e2' AND fiscal_code IS NULL;
UPDATE expense_types SET fiscal_code = '050' WHERE code = 'ET-8035' AND fiscal_code IS NULL;