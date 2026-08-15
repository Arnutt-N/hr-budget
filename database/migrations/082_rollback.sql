-- Rollback 082_create_salary_scales.sql
-- Guarded drop — safe to run twice.
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

DROP TABLE IF EXISTS salary_scales;
