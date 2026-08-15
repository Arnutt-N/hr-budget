-- Rollback 080_create_position_allowances.sql
-- Guarded drop — safe to run twice.
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

DROP TABLE IF EXISTS position_allowances;
