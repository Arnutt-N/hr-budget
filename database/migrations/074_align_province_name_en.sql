-- ============================================================================
-- 074_align_province_name_en.sql
-- Phase 8 — align provinces.name_en with the shared thailand-geodata reference
-- set (Dhanabhon/thailand-geodata, MIT), the same dataset the sibling systems
-- (huangua-works, jsk-app) seed their province/district/tambon tables from.
-- After this, all three systems spell all 77 provinces identically, so reports
-- and exports can be compared across them without a name crosswalk.
--
-- REVERSES A DELIBERATE CHOICE. Migration 072 wrote "Buri" as a separate word
-- everywhere, on purpose, for internal consistency (see its header). That rule
-- is mechanically tidier but does not match published usage: official Thai
-- romanisation genuinely is inconsistent here — สิงห์บุรี is "Sing Buri" while
-- นนทบุรี is "Nonthaburi" — because the names follow established usage, not a
-- derivation rule. The reference set carries that same inconsistency, and it is
-- the form these names appear in outside this repo.
--
-- Trade-off taken: give up mechanical consistency, gain cross-system alignment.
-- Only 7 of 77 rows differ; Thai names were already identical in all 77.
-- The other 5 "Buri" provinces (Lop Buri, Sing Buri, Chon Buri, Prachin Buri,
-- Suphan Buri) and Buri Ram already match the reference set — left untouched.
--
-- name_th is NOT touched — verified identical in all 77 rows.
-- Idempotent: keyed on the immutable geocode; a re-run rewrites the same value.
-- ============================================================================

UPDATE provinces SET name_en = 'Nonthaburi'   WHERE code = '12';  -- was Nontha Buri
UPDATE provinces SET name_en = 'Saraburi'     WHERE code = '19';  -- was Sara Buri
UPDATE provinces SET name_en = 'Chanthaburi'  WHERE code = '22';  -- was Chantha Buri
UPDATE provinces SET name_en = 'Ratchaburi'   WHERE code = '70';  -- was Ratcha Buri
UPDATE provinces SET name_en = 'Kanchanaburi' WHERE code = '71';  -- was Kanchana Buri
UPDATE provinces SET name_en = 'Phetchaburi'  WHERE code = '76';  -- was Phetcha Buri
UPDATE provinces SET name_en = 'Phang-nga'    WHERE code = '82';  -- was Phang Nga

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'provinces aligned' AS check_name, COUNT(*) AS actual, 7 AS expected
FROM provinces
WHERE (code, name_en) IN (
  ('12','Nonthaburi'), ('19','Saraburi'),  ('22','Chanthaburi'), ('70','Ratchaburi'),
  ('71','Kanchanaburi'), ('76','Phetchaburi'), ('82','Phang-nga')
)
UNION ALL
SELECT 'provinces total', COUNT(*), 77 FROM provinces WHERE code REGEXP '^[0-9]{2}$';
