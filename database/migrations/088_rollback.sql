-- Rollback 088_create_personnel_budget_policy.sql
-- Guarded drop — safe to run twice. Data loss: per-year policy rows (mostly
-- seeded; user tweaks would be lost). Re-running 088 re-seeds defaults.
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

DROP TABLE IF EXISTS personnel_budget_policy;
