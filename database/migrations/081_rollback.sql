-- Rollback 081_create_personnel_allowances.sql
-- Guarded drop — safe to run twice. Data loss: actuals of who receives what —
-- this is user-entered data, unlike 075's reference seed. Take a backup first.
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

DROP TABLE IF EXISTS personnel_allowances;
