-- Rollback 072_seed_provinces.sql
-- Removes the 76 seeded provinces (2-digit geocodes, excluding Bangkok '10').
-- Safe only while no budget_line_items.province_id references them.
-- The Bangkok row is left re-coded as '10' (the correct standard geocode).
DELETE FROM provinces WHERE code REGEXP '^[0-9]{2}$' AND code <> '10';
