-- Rollback 077_create_position_versions.sql
-- Guarded drop — safe to run twice. Any later table holding an FK into
-- position_versions (position_allowances 080, personnel_allowances 081,
-- personnel_assignments 086, vacancy_recruitment 087) must be rolled back
-- first or this DROP fails loudly, which is correct ordering protection.
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

DROP TABLE IF EXISTS position_versions;
