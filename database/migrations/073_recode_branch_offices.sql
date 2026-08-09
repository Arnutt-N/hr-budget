-- ============================================================================
-- 073_recode_branch_offices.sql
-- Phase 8 — re-code the 5 สาขา offices (L5) from ad-hoc romanised branch names
-- (JP-31-NANGRONG) to the STANDARD 4-digit Thai district geocode (JP-3104),
-- so the whole JP-* tree uses one addressing scheme:
--
--   JP-<2 digits>   สำนักงานยุติธรรมจังหวัด (L4)   e.g. JP-31 = บุรีรัมย์
--   JP-<4 digits>   สาขา (L5)                      e.g. JP-3104 = สาขานางรอง
--
-- Code length disambiguates the level — the same rule the national geocode
-- system uses — so LEFT('JP-3104', 5) = 'JP-31' IS the parent office code,
-- and SUBSTRING(code, 4) is a district geocode that migration 075 turns into
-- a real FK. Before this, the district was encoded as an unqueryable romanised
-- string; nothing could JOIN on it.
--
-- District geocodes verified against Dhanabhon/thailand-geodata (MIT) by
-- matching the Thai district name within the correct province — 5/5 matched.
--
-- Idempotent: every UPDATE is keyed on the OLD code, so a second run matches
-- nothing. organizations.code is UNIQUE and no JP-<4 digit> code exists yet,
-- so none of these collide.
-- ============================================================================

UPDATE organizations SET code = 'JP-3104' WHERE code = 'JP-31-NANGRONG';       -- นางรอง · บุรีรัมย์ (31)
UPDATE organizations SET code = 'JP-5704' WHERE code = 'JP-57-THOENG';         -- เทิง · เชียงราย (57)
UPDATE organizations SET code = 'JP-9502' WHERE code = 'JP-95-BETONG';         -- เบตง · ยะลา (95)
UPDATE organizations SET code = 'JP-6703' WHERE code = 'JP-67-LOMSAK';         -- หล่มสัก · เพชรบูรณ์ (67)
UPDATE organizations SET code = 'JP-7107' WHERE code = 'JP-71-THONGPHAPHUM';   -- ทองผาภูมิ · กาญจนบุรี (71)

-- --- Verification (printed by the migration runner) ------------------------
-- Every L5 office must now carry a 4-digit geocode whose first 2 digits match
-- its own province_code AND its parent's 2-digit code. If either count is not
-- 5, the tree is inconsistent — do not proceed to 075.
SELECT 'branch offices re-coded'  AS check_name, COUNT(*) AS actual, 5 AS expected
FROM organizations
WHERE level = 5 AND code REGEXP '^JP-[0-9]{4}$' AND SUBSTRING(code, 4, 2) = province_code
UNION ALL
SELECT 'parent code derivable', COUNT(*), 5
FROM organizations c
JOIN organizations p ON p.id = c.parent_id AND p.code = LEFT(c.code, 5)
WHERE c.level = 5 AND c.code REGEXP '^JP-[0-9]{4}$'
UNION ALL
SELECT 'old-style codes left', COUNT(*), 0
FROM organizations WHERE code LIKE 'JP-%-%';
