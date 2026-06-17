-- ============================================================================
-- 071_seed_provincial_offices.sql
-- Phase 6 — seed the 81 สำนักงานยุติธรรมจังหวัด into the org tree under
-- กองประสานราชการยุติธรรมจังหวัด (OPS-PROV), grouped by the 6 standard
-- (ราชบัณฑิตยสภา / NGC) geographic regions so that Phase 1 subtree (recursive
-- CTE) scoping makes "region" access meaningful WITHOUT new region machinery.
--
--   OPS-PROV (L2, existing)
--   └─ PROV-RGN-* region node (L3, division)
--      └─ JP-<geocode> สำนักงานยุติธรรมจังหวัด (L4, province)
--         └─ JP-<geocode>-<branch> สาขา (L5, office)   where applicable
--
-- Composition: 76 main offices (one per province, Bangkok excluded) + 5 สาขา = 81.
-- Idempotent: organizations.code is UNIQUE, so INSERT IGNORE de-dupes on re-run.
-- ============================================================================

-- --- 1) Six regional grouping nodes under OPS-PROV -------------------------
INSERT IGNORE INTO organizations (parent_id, code, name_th, org_type, region, level, sort_order)
SELECT p.id, v.code, v.name_th, 'division', 'provincial', 3, v.sort
FROM organizations p
JOIN (
            SELECT 'PROV-RGN-N'  AS code, 'สำนักงานยุติธรรมจังหวัด ภาคเหนือ'              AS name_th, 1 AS sort
  UNION ALL SELECT 'PROV-RGN-NE',       'สำนักงานยุติธรรมจังหวัด ภาคตะวันออกเฉียงเหนือ',     2
  UNION ALL SELECT 'PROV-RGN-C',        'สำนักงานยุติธรรมจังหวัด ภาคกลาง',                  3
  UNION ALL SELECT 'PROV-RGN-E',        'สำนักงานยุติธรรมจังหวัด ภาคตะวันออก',              4
  UNION ALL SELECT 'PROV-RGN-W',        'สำนักงานยุติธรรมจังหวัด ภาคตะวันตก',               5
  UNION ALL SELECT 'PROV-RGN-S',        'สำนักงานยุติธรรมจังหวัด ภาคใต้',                   6
) v
WHERE p.code = 'OPS-PROV';

-- --- 2) Province offices (L4), grouped by region ---------------------------

-- North (9)
INSERT IGNORE INTO organizations (parent_id, code, name_th, org_type, province_code, region, level, sort_order)
SELECT p.id, v.code, v.name_th, 'province', v.pcode, 'provincial', 4, v.sort
FROM organizations p
JOIN (
            SELECT 'JP-50' AS code, 'สำนักงานยุติธรรมจังหวัดเชียงใหม่'  AS name_th, '50' AS pcode, 1 AS sort
  UNION ALL SELECT 'JP-57', 'สำนักงานยุติธรรมจังหวัดเชียงราย',   '57', 2
  UNION ALL SELECT 'JP-55', 'สำนักงานยุติธรรมจังหวัดน่าน',       '55', 3
  UNION ALL SELECT 'JP-56', 'สำนักงานยุติธรรมจังหวัดพะเยา',      '56', 4
  UNION ALL SELECT 'JP-54', 'สำนักงานยุติธรรมจังหวัดแพร่',       '54', 5
  UNION ALL SELECT 'JP-58', 'สำนักงานยุติธรรมจังหวัดแม่ฮ่องสอน', '58', 6
  UNION ALL SELECT 'JP-52', 'สำนักงานยุติธรรมจังหวัดลำปาง',      '52', 7
  UNION ALL SELECT 'JP-51', 'สำนักงานยุติธรรมจังหวัดลำพูน',      '51', 8
  UNION ALL SELECT 'JP-53', 'สำนักงานยุติธรรมจังหวัดอุตรดิตถ์',  '53', 9
) v
WHERE p.code = 'PROV-RGN-N';

-- Northeast (20)
INSERT IGNORE INTO organizations (parent_id, code, name_th, org_type, province_code, region, level, sort_order)
SELECT p.id, v.code, v.name_th, 'province', v.pcode, 'provincial', 4, v.sort
FROM organizations p
JOIN (
            SELECT 'JP-46' AS code, 'สำนักงานยุติธรรมจังหวัดกาฬสินธุ์'   AS name_th, '46' AS pcode, 1 AS sort
  UNION ALL SELECT 'JP-40', 'สำนักงานยุติธรรมจังหวัดขอนแก่น',     '40', 2
  UNION ALL SELECT 'JP-36', 'สำนักงานยุติธรรมจังหวัดชัยภูมิ',     '36', 3
  UNION ALL SELECT 'JP-48', 'สำนักงานยุติธรรมจังหวัดนครพนม',      '48', 4
  UNION ALL SELECT 'JP-30', 'สำนักงานยุติธรรมจังหวัดนครราชสีมา',  '30', 5
  UNION ALL SELECT 'JP-38', 'สำนักงานยุติธรรมจังหวัดบึงกาฬ',      '38', 6
  UNION ALL SELECT 'JP-31', 'สำนักงานยุติธรรมจังหวัดบุรีรัมย์',   '31', 7
  UNION ALL SELECT 'JP-44', 'สำนักงานยุติธรรมจังหวัดมหาสารคาม',   '44', 8
  UNION ALL SELECT 'JP-49', 'สำนักงานยุติธรรมจังหวัดมุกดาหาร',    '49', 9
  UNION ALL SELECT 'JP-35', 'สำนักงานยุติธรรมจังหวัดยโสธร',       '35', 10
  UNION ALL SELECT 'JP-45', 'สำนักงานยุติธรรมจังหวัดร้อยเอ็ด',    '45', 11
  UNION ALL SELECT 'JP-42', 'สำนักงานยุติธรรมจังหวัดเลย',         '42', 12
  UNION ALL SELECT 'JP-33', 'สำนักงานยุติธรรมจังหวัดศรีสะเกษ',    '33', 13
  UNION ALL SELECT 'JP-47', 'สำนักงานยุติธรรมจังหวัดสกลนคร',      '47', 14
  UNION ALL SELECT 'JP-32', 'สำนักงานยุติธรรมจังหวัดสุรินทร์',    '32', 15
  UNION ALL SELECT 'JP-43', 'สำนักงานยุติธรรมจังหวัดหนองคาย',     '43', 16
  UNION ALL SELECT 'JP-39', 'สำนักงานยุติธรรมจังหวัดหนองบัวลำภู', '39', 17
  UNION ALL SELECT 'JP-37', 'สำนักงานยุติธรรมจังหวัดอำนาจเจริญ',  '37', 18
  UNION ALL SELECT 'JP-41', 'สำนักงานยุติธรรมจังหวัดอุดรธานี',    '41', 19
  UNION ALL SELECT 'JP-34', 'สำนักงานยุติธรรมจังหวัดอุบลราชธานี', '34', 20
) v
WHERE p.code = 'PROV-RGN-NE';

-- Central (21, Bangkok excluded)
INSERT IGNORE INTO organizations (parent_id, code, name_th, org_type, province_code, region, level, sort_order)
SELECT p.id, v.code, v.name_th, 'province', v.pcode, 'provincial', 4, v.sort
FROM organizations p
JOIN (
            SELECT 'JP-62' AS code, 'สำนักงานยุติธรรมจังหวัดกำแพงเพชร'      AS name_th, '62' AS pcode, 1 AS sort
  UNION ALL SELECT 'JP-18', 'สำนักงานยุติธรรมจังหวัดชัยนาท',         '18', 2
  UNION ALL SELECT 'JP-26', 'สำนักงานยุติธรรมจังหวัดนครนายก',        '26', 3
  UNION ALL SELECT 'JP-73', 'สำนักงานยุติธรรมจังหวัดนครปฐม',         '73', 4
  UNION ALL SELECT 'JP-60', 'สำนักงานยุติธรรมจังหวัดนครสวรรค์',      '60', 5
  UNION ALL SELECT 'JP-12', 'สำนักงานยุติธรรมจังหวัดนนทบุรี',        '12', 6
  UNION ALL SELECT 'JP-13', 'สำนักงานยุติธรรมจังหวัดปทุมธานี',       '13', 7
  UNION ALL SELECT 'JP-14', 'สำนักงานยุติธรรมจังหวัดพระนครศรีอยุธยา', '14', 8
  UNION ALL SELECT 'JP-66', 'สำนักงานยุติธรรมจังหวัดพิจิตร',         '66', 9
  UNION ALL SELECT 'JP-65', 'สำนักงานยุติธรรมจังหวัดพิษณุโลก',       '65', 10
  UNION ALL SELECT 'JP-67', 'สำนักงานยุติธรรมจังหวัดเพชรบูรณ์',      '67', 11
  UNION ALL SELECT 'JP-16', 'สำนักงานยุติธรรมจังหวัดลพบุรี',         '16', 12
  UNION ALL SELECT 'JP-11', 'สำนักงานยุติธรรมจังหวัดสมุทรปราการ',    '11', 13
  UNION ALL SELECT 'JP-75', 'สำนักงานยุติธรรมจังหวัดสมุทรสงคราม',    '75', 14
  UNION ALL SELECT 'JP-74', 'สำนักงานยุติธรรมจังหวัดสมุทรสาคร',      '74', 15
  UNION ALL SELECT 'JP-19', 'สำนักงานยุติธรรมจังหวัดสระบุรี',        '19', 16
  UNION ALL SELECT 'JP-17', 'สำนักงานยุติธรรมจังหวัดสิงห์บุรี',      '17', 17
  UNION ALL SELECT 'JP-64', 'สำนักงานยุติธรรมจังหวัดสุโขทัย',        '64', 18
  UNION ALL SELECT 'JP-72', 'สำนักงานยุติธรรมจังหวัดสุพรรณบุรี',     '72', 19
  UNION ALL SELECT 'JP-15', 'สำนักงานยุติธรรมจังหวัดอ่างทอง',        '15', 20
  UNION ALL SELECT 'JP-61', 'สำนักงานยุติธรรมจังหวัดอุทัยธานี',      '61', 21
) v
WHERE p.code = 'PROV-RGN-C';

-- East (7)
INSERT IGNORE INTO organizations (parent_id, code, name_th, org_type, province_code, region, level, sort_order)
SELECT p.id, v.code, v.name_th, 'province', v.pcode, 'provincial', 4, v.sort
FROM organizations p
JOIN (
            SELECT 'JP-22' AS code, 'สำนักงานยุติธรรมจังหวัดจันทบุรี'   AS name_th, '22' AS pcode, 1 AS sort
  UNION ALL SELECT 'JP-24', 'สำนักงานยุติธรรมจังหวัดฉะเชิงเทรา', '24', 2
  UNION ALL SELECT 'JP-20', 'สำนักงานยุติธรรมจังหวัดชลบุรี',     '20', 3
  UNION ALL SELECT 'JP-23', 'สำนักงานยุติธรรมจังหวัดตราด',       '23', 4
  UNION ALL SELECT 'JP-25', 'สำนักงานยุติธรรมจังหวัดปราจีนบุรี', '25', 5
  UNION ALL SELECT 'JP-21', 'สำนักงานยุติธรรมจังหวัดระยอง',      '21', 6
  UNION ALL SELECT 'JP-27', 'สำนักงานยุติธรรมจังหวัดสระแก้ว',    '27', 7
) v
WHERE p.code = 'PROV-RGN-E';

-- West (5)
INSERT IGNORE INTO organizations (parent_id, code, name_th, org_type, province_code, region, level, sort_order)
SELECT p.id, v.code, v.name_th, 'province', v.pcode, 'provincial', 4, v.sort
FROM organizations p
JOIN (
            SELECT 'JP-71' AS code, 'สำนักงานยุติธรรมจังหวัดกาญจนบุรี'      AS name_th, '71' AS pcode, 1 AS sort
  UNION ALL SELECT 'JP-63', 'สำนักงานยุติธรรมจังหวัดตาก',           '63', 2
  UNION ALL SELECT 'JP-77', 'สำนักงานยุติธรรมจังหวัดประจวบคีรีขันธ์', '77', 3
  UNION ALL SELECT 'JP-76', 'สำนักงานยุติธรรมจังหวัดเพชรบุรี',       '76', 4
  UNION ALL SELECT 'JP-70', 'สำนักงานยุติธรรมจังหวัดราชบุรี',        '70', 5
) v
WHERE p.code = 'PROV-RGN-W';

-- South (14)
INSERT IGNORE INTO organizations (parent_id, code, name_th, org_type, province_code, region, level, sort_order)
SELECT p.id, v.code, v.name_th, 'province', v.pcode, 'provincial', 4, v.sort
FROM organizations p
JOIN (
            SELECT 'JP-81' AS code, 'สำนักงานยุติธรรมจังหวัดกระบี่'        AS name_th, '81' AS pcode, 1 AS sort
  UNION ALL SELECT 'JP-86', 'สำนักงานยุติธรรมจังหวัดชุมพร',         '86', 2
  UNION ALL SELECT 'JP-92', 'สำนักงานยุติธรรมจังหวัดตรัง',          '92', 3
  UNION ALL SELECT 'JP-80', 'สำนักงานยุติธรรมจังหวัดนครศรีธรรมราช',  '80', 4
  UNION ALL SELECT 'JP-96', 'สำนักงานยุติธรรมจังหวัดนราธิวาส',      '96', 5
  UNION ALL SELECT 'JP-94', 'สำนักงานยุติธรรมจังหวัดปัตตานี',       '94', 6
  UNION ALL SELECT 'JP-82', 'สำนักงานยุติธรรมจังหวัดพังงา',         '82', 7
  UNION ALL SELECT 'JP-93', 'สำนักงานยุติธรรมจังหวัดพัทลุง',        '93', 8
  UNION ALL SELECT 'JP-83', 'สำนักงานยุติธรรมจังหวัดภูเก็ต',        '83', 9
  UNION ALL SELECT 'JP-95', 'สำนักงานยุติธรรมจังหวัดยะลา',          '95', 10
  UNION ALL SELECT 'JP-85', 'สำนักงานยุติธรรมจังหวัดระนอง',         '85', 11
  UNION ALL SELECT 'JP-90', 'สำนักงานยุติธรรมจังหวัดสงขลา',         '90', 12
  UNION ALL SELECT 'JP-91', 'สำนักงานยุติธรรมจังหวัดสตูล',          '91', 13
  UNION ALL SELECT 'JP-84', 'สำนักงานยุติธรรมจังหวัดสุราษฎร์ธานี',   '84', 14
) v
WHERE p.code = 'PROV-RGN-S';

-- --- 3) Branch offices สาขา (L5) under their parent province ---------------
INSERT IGNORE INTO organizations (parent_id, code, name_th, org_type, province_code, region, level, sort_order)
SELECT p.id, v.code, v.name_th, 'office', v.pcode, 'provincial', 5, v.sort
FROM organizations p
JOIN (
            SELECT 'JP-31-NANGRONG'      AS code, 'สำนักงานยุติธรรมจังหวัดบุรีรัมย์ สาขานางรอง'  AS name_th, '31' AS pcode, 'JP-31' AS parent_code, 1 AS sort
  UNION ALL SELECT 'JP-57-THOENG',       'สำนักงานยุติธรรมจังหวัดเชียงราย สาขาเทิง',     '57', 'JP-57', 2
  UNION ALL SELECT 'JP-95-BETONG',       'สำนักงานยุติธรรมจังหวัดยะลา สาขาเบตง',         '95', 'JP-95', 3
  UNION ALL SELECT 'JP-67-LOMSAK',       'สำนักงานยุติธรรมจังหวัดเพชรบูรณ์ สาขาหล่มสัก',  '67', 'JP-67', 4
  UNION ALL SELECT 'JP-71-THONGPHAPHUM', 'สำนักงานยุติธรรมจังหวัดกาญจนบุรี สาขาทองผาภูมิ', '71', 'JP-71', 5
) v ON p.code = v.parent_code;
