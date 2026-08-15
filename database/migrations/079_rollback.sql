-- Rollback 079_create_allowance_rates.sql
-- Guarded drop — safe to run twice. Self-referencing FK (derives_from_type_id)
-- is dropped together with the table automatically.
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

DROP TABLE IF EXISTS allowance_rates;
