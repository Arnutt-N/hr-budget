-- 068_seed_real_organizations.sql
-- Phase 2: seed the real organization tree of สำนักงานปลัดกระทรวงยุติธรรม
-- (Office of the Permanent Secretary, Ministry of Justice), replacing the
-- auto-generated placeholder seed orgs.
--
-- Names are taken verbatim from the official budget-execution PDFs (normalized
-- SARA-AM ligatures to standard Thai). Codes are systematic placeholders and
-- may be remapped to official codes later. parent_id is resolved by code so the
-- file is portable and idempotent (INSERT IGNORE on the unique `code`).
--
-- Hierarchy: ministry (0) -> department (1) -> division (2) -> section (3).
-- Provincial justice offices (สำนักงานยุติธรรมจังหวัด, ~81) are intentionally
-- deferred (separate dataset; coordinated by กองประสานราชการยุติธรรมจังหวัด).

SET NAMES utf8mb4;

-- Deactivate placeholder seed orgs (auto-generated codes). NOT deleted: existing
-- disbursement_sessions rows reference them via FK, so we preserve referential
-- integrity and simply drop them from active listings.
UPDATE `organizations` SET `is_active` = 0
 WHERE `code` LIKE 'MN-%' OR `code` LIKE 'DP-%'
    OR `code` LIKE 'DV-%' OR `code` LIKE 'SC-%';

-- Level 0: ministry
INSERT IGNORE INTO `organizations` (`code`,`name_th`,`abbreviation`,`org_type`,`parent_id`,`level`,`is_active`,`sort_order`)
VALUES ('MOJ','กระทรวงยุติธรรม','ยธ.','ministry',NULL,0,1,1);

-- Level 1: department (Office of the Permanent Secretary)
INSERT IGNORE INTO `organizations` (`code`,`name_th`,`abbreviation`,`org_type`,`parent_id`,`level`,`is_active`,`sort_order`)
SELECT 'MOJ-OPS','สำนักงานปลัดกระทรวงยุติธรรม','สป.ยธ.','department',o.id,1,1,1
  FROM `organizations` o WHERE o.code='MOJ';

-- Level 1: external budget recipient under the ministry
INSERT IGNORE INTO `organizations` (`code`,`name_th`,`abbreviation`,`org_type`,`parent_id`,`level`,`is_active`,`sort_order`)
SELECT 'MOJ-EXT-LAW','เนติบัณฑิตยสภา/สภาทนายความ',NULL,'office',o.id,1,1,99
  FROM `organizations` o WHERE o.code='MOJ';

-- Level 2: divisions under สป.ยธ. (parent resolved by code MOJ-OPS)
INSERT IGNORE INTO `organizations` (`code`,`name_th`,`org_type`,`parent_id`,`level`,`is_active`,`sort_order`)
SELECT v.code, v.name_th, 'division', p.id, 2, 1, v.sort_order
FROM `organizations` p
JOIN (
  SELECT 'OPS-STRAT'   AS code, 'กองยุทธศาสตร์และแผนงาน' AS name_th, 1 AS sort_order UNION ALL
  SELECT 'OPS-PROV',   'กองประสานราชการยุติธรรมจังหวัด', 2 UNION ALL
  SELECT 'OPS-VOC',    'สำนักงานส่งเสริมสัมมาชีพและผลิตภัณฑ์เพื่อการพัฒนาพฤตินิสัย', 3 UNION ALL
  SELECT 'OPS-SC',     'ศูนย์บริการร่วม กระทรวงยุติธรรม', 4 UNION ALL
  SELECT 'OPS-INTL',   'กองการต่างประเทศ', 5 UNION ALL
  SELECT 'OPS-HRD',    'สถาบันพัฒนาบุคลากรกระทรวงยุติธรรม', 6 UNION ALL
  SELECT 'OPS-LAW',    'กองกฎหมาย', 7 UNION ALL
  SELECT 'OPS-CENTRAL','กองกลาง', 8 UNION ALL
  SELECT 'OPS-MIN',    'สำนักงานรัฐมนตรี', 9 UNION ALL
  SELECT 'OPS-AUDIT',  'กลุ่มตรวจสอบภายใน', 10 UNION ALL
  SELECT 'OPS-INSP',   'สำนักผู้ตรวจราชการกระทรวงยุติธรรม', 11 UNION ALL
  SELECT 'OPS-HR',     'กองบริหารทรัพยากรบุคคล', 12 UNION ALL
  SELECT 'OPS-CONS',   'กองออกแบบและก่อสร้าง', 13 UNION ALL
  SELECT 'OPS-ICT',    'ศูนย์เทคโนโลยีสารสนเทศและการสื่อสาร', 14 UNION ALL
  SELECT 'OPS-FIN',    'กองบริหารการคลัง', 15 UNION ALL
  SELECT 'OPS-PSDG',   'กลุ่มพัฒนาระบบบริหาร กระทรวงยุติธรรม', 16 UNION ALL
  SELECT 'OPS-ACT',    'ศูนย์ปฏิบัติการต่อต้านการทุจริต กระทรวงยุติธรรม', 17 UNION ALL
  SELECT 'OPS-INNO',   'กองพัฒนานวัตกรรมการยุติธรรม', 18 UNION ALL
  SELECT 'OPS-REHAB',  'กลุ่มภารกิจพัฒนาพฤตินิสัย', 19
) v ON p.code = 'MOJ-OPS';

-- Level 3: sub-sections / cost-centres of specific divisions
INSERT IGNORE INTO `organizations` (`code`,`name_th`,`org_type`,`parent_id`,`level`,`is_active`,`sort_order`)
SELECT 'OPS-STRAT-CENTRAL','กองยุทธศาสตร์และแผนงาน (บริหารส่วนกลาง)','section',p.id,3,1,1
  FROM `organizations` p WHERE p.code='OPS-STRAT';
INSERT IGNORE INTO `organizations` (`code`,`name_th`,`org_type`,`parent_id`,`level`,`is_active`,`sort_order`)
SELECT 'OPS-STRAT-SBPAC','กองยุทธศาสตร์และแผนงาน ส่วนนโยบายและยุทธศาสตร์จังหวัดชายแดนภาคใต้','section',p.id,3,1,2
  FROM `organizations` p WHERE p.code='OPS-STRAT';
INSERT IGNORE INTO `organizations` (`code`,`name_th`,`org_type`,`parent_id`,`level`,`is_active`,`sort_order`)
SELECT 'OPS-FIN-CENTRAL','กองบริหารการคลัง (ค่าใช้จ่ายส่วนกลาง)','section',p.id,3,1,1
  FROM `organizations` p WHERE p.code='OPS-FIN';
