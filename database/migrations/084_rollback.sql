-- Rollback 084_create_salary_raise_rounds.sql
-- Guarded drop — safe to run twice. salary_raise_progress (085) holds an FK
-- into salary_raise_rounds and must be rolled back first or this DROP fails
-- loudly (correct ordering protection).
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

DROP TABLE IF EXISTS salary_raise_rounds;
