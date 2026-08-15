-- Rollback 075_create_districts.sql
-- Order matters: organizations.district_code has an FK into districts, so the
-- constraint must go before the table. Both steps are guarded by
-- information_schema, so this is safe to run twice or on a DB where 075 never
-- ran. Dropping the column also drops idx_org_district (single-column index).
--
-- Data loss: the 928 seeded districts. They are pure reference data and can be
-- re-seeded by re-running 075 — nothing user-entered lives here. If any table
-- other than organizations has since gained an FK into districts, that FK must
-- be dropped first or the DROP TABLE will fail (loudly, which is correct).
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

SET @has_fk = (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'organizations'
    AND CONSTRAINT_NAME = 'fk_organizations_district'
);
SET @ddl = IF(@has_fk > 0,
  'ALTER TABLE organizations DROP FOREIGN KEY fk_organizations_district',
  'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

SET @has_col = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'organizations'
    AND COLUMN_NAME = 'district_code'
);
SET @ddl = IF(@has_col > 0,
  'ALTER TABLE organizations DROP COLUMN district_code',
  'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

DROP TABLE IF EXISTS districts;
