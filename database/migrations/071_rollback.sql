-- Rollback 071_seed_provincial_offices.sql
-- Removes branches first (L5), then provinces (L4), then region nodes (L3).
-- Safe only while these offices have no dependent budget/request rows.
--
-- Two branch code forms are matched because migration 073 re-coded the 5 สาขา
-- from 'JP-31-NANGRONG' to the district geocode 'JP-3104'. The new form has no
-- second hyphen and is 7 chars, so it matches NEITHER original pattern — without
-- the third DELETE below, running this after 073 would silently leave 5 orphaned
-- L5 rows behind while reporting success.
-- If 075 has been applied, run 075_rollback first: organizations.district_code
-- has an FK into districts and these DELETEs would otherwise be the only thing
-- clearing it.
DELETE FROM organizations WHERE code LIKE 'JP-%-%';        -- สาขา, pre-073 form (JP-31-NANGRONG)
DELETE FROM organizations WHERE code REGEXP '^JP-[0-9]{4}$'; -- สาขา, post-073 form (JP-3104)
DELETE FROM organizations WHERE code LIKE 'JP-__';         -- province offices JP-<2 digits>
DELETE FROM organizations WHERE code LIKE 'PROV-RGN-%';    -- region grouping nodes
