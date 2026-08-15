-- Rollback 078_create_allowance_types.sql
-- Guarded drop — safe to run twice. allowance_rates (079), position_allowances
-- (080) and personnel_allowances (081) hold FKs into allowance_types and must
-- be rolled back first or this DROP fails loudly (correct ordering protection).
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

DROP TABLE IF EXISTS allowance_types;
