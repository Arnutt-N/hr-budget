-- Rollback 085_create_salary_raise_progress.sql
-- Guarded drop — safe to run twice. Data loss: per-unit completion status —
-- user-entered data. Take a backup first.
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

DROP TABLE IF EXISTS salary_raise_progress;
