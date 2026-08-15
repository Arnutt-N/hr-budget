-- Rollback 083_create_salary_increment_policy.sql
-- Guarded drop — safe to run twice. Data loss: the seeded 2569 policy rows
-- (user-confirmed values). Re-running 083 re-seeds them if fiscal_years
-- still has year=2569.
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

DROP TABLE IF EXISTS salary_increment_policy;
