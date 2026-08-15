-- Rollback 086_create_personnel_assignments.sql
-- Guarded drop — safe to run twice. Data loss: who-is-helping-where records —
-- user-entered data. Take a backup first.
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

DROP TABLE IF EXISTS personnel_assignments;
