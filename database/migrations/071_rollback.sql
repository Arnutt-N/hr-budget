-- Rollback 071_seed_provincial_offices.sql
-- Removes branches first (L5), then provinces (L4), then region nodes (L3).
-- Safe only while these offices have no dependent budget/request rows.
DELETE FROM organizations WHERE code LIKE 'JP-%-%';        -- สาขา (e.g. JP-31-NANGRONG)
DELETE FROM organizations WHERE code LIKE 'JP-__';         -- province offices JP-<2 digits>
DELETE FROM organizations WHERE code LIKE 'PROV-RGN-%';    -- region grouping nodes
