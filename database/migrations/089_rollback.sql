-- Rollback 089_add_source_to_budget_line_items.sql
-- Guarded drop — safe to run twice or on a DB where 089 never ran.
-- Data loss: the manual/computed distinction. Refuses nothing — if any row
-- already says 'computed', re-running 089 resets everything to 'manual',
-- which is the pre-Phase-9 state anyway.
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

SET @has_col = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'budget_line_items'
    AND COLUMN_NAME = 'source'
);
SET @ddl = IF(@has_col > 0,
  'ALTER TABLE budget_line_items DROP COLUMN source',
  'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;
