-- Rollback 076_create_positions.sql
-- Guarded by information_schema — safe to run twice or on a DB where 076
-- never ran. Fails loudly if position_versions (077) or any later table
-- still holds an FK into positions, which is correct ordering protection.
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

DROP TABLE IF EXISTS positions;
