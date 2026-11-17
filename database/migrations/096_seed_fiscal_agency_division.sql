-- ============================================================================
-- 096_seed_fiscal_agency_division.sql
-- Seed the agency referenced by the fiscal-code import so the by-agency
-- report resolves a Thai name instead of NULL.
--
-- Source: secrets/fisical-code/16002_กิจกรรมหลัก.xls, sheet `report`, row 2:
--   "หน่วยงาน 16002 : สำนักงานปลัดกระทรวงยุติธรรม"
-- The same AGC 16002 prefixes every 20-digit code in 16002_รหัสงบประมาณ.xlsx.
--
-- Idempotent: INSERT IGNORE on the UNIQUE key over divisions.code.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

INSERT IGNORE INTO divisions
    (code, name_th, type, is_active, sort_order)
VALUES
    ('16002', 'สำนักงานปลัดกระทรวงยุติธรรม', 'central', 1, 1);
