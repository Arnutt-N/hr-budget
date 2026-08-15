-- Rollback 090_seed_allowance_types.sql
-- Guarded delete of the 7 seeded catalog rows only — user-added types
-- (any code not in the seed list) survive. Safe to run twice.
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

DELETE FROM allowance_types
WHERE code IN ('POSITION_ALLOWANCE','KHN','PTK','PPD','PSR','SPP','HOUSE_RENT')
  AND created_by IS NULL;
