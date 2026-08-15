-- Rollback 087_create_vacancy_recruitment.sql
-- Guarded drop — safe to run twice. Data loss: recruitment evidence records —
-- user-entered data. Take a backup first.
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

DROP TABLE IF EXISTS vacancy_recruitment;
