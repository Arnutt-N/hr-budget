-- ============================================================================
-- 075_create_districts.sql
-- Phase 8 — reference table for อำเภอ/เขต, keyed on the STANDARD 4-digit Thai
-- district geocode. The geocode is self-describing: LEFT(code, 2) IS the
-- province geocode, which is exactly how the seed below resolves province_id —
-- no hand-written crosswalk, and the JOIN documents the invariant.
--
-- Source: Dhanabhon/thailand-geodata (MIT), 928 districts, verified before
-- generation: codes unique, all 4-digit numeric, every LEFT(code,2) present in
-- migration 072, no characters requiring SQL escaping. Generated — do not hand-edit.
--
-- Why now: migration 073 re-codes the 5 สาขา offices to district geocodes
-- (JP-3104). Without this table that code references nothing, so the same
-- migration adds organizations.district_code and points it here.
--
-- name_en follows the source dataset (RTGS as published by the source), the
-- same standard migration 074 aligns provinces.name_en to.
-- Idempotent: CREATE TABLE IF NOT EXISTS + districts.code UNIQUE + INSERT IGNORE.
-- No DELIMITER blocks — safe to run through the mysql CLI or a PDO multi-exec.
-- ============================================================================

-- Required, not cosmetic. The seed JOINs a derived-table column against
-- provinces.code; two *columns* of different collations are an "Illegal mix of
-- collations" error (the literal-vs-column coercibility rule does not save us
-- inside a derived table). MySQL 8 connects as utf8mb4_0900_ai_ci by default
-- while every table here is utf8mb4_unicode_ci, so pin the session to match.
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS districts (
  id          INT NOT NULL AUTO_INCREMENT,
  province_id INT NOT NULL COMMENT 'FK: provinces.id',
  code        VARCHAR(10) NOT NULL COMMENT 'รหัสอำเภอมาตรฐาน 4 หลัก (2 หลักแรก = รหัสจังหวัด)',
  name_th     VARCHAR(100) NOT NULL,
  name_en     VARCHAR(100) DEFAULT NULL,
  sort_order  INT DEFAULT 0 COMMENT 'ลำดับตามรหัส 2 หลักท้าย (เมือง = 1)',
  is_active   TINYINT(1) DEFAULT 1,
  deleted_at  TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
  created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by  INT DEFAULT NULL COMMENT 'ผู้สร้าง',
  updated_by  INT DEFAULT NULL COMMENT 'ผู้แก้ไขล่าสุด',
  PRIMARY KEY (id),
  UNIQUE KEY uk_districts_code (code),
  KEY idx_districts_province (province_id),
  KEY idx_districts_deleted (deleted_at),
  CONSTRAINT fk_districts_province FOREIGN KEY (province_id)
    REFERENCES provinces (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='อำเภอ/เขต';

-- --- Seed 928 districts ----------------------------------------------------
-- province_id resolved by JOIN, never hard-coded: provinces.id is AUTO_INCREMENT
-- and is NOT stable across environments, but provinces.code is.
INSERT IGNORE INTO districts (province_id, code, name_th, name_en, sort_order)
SELECT p.id, v.code, v.name_th, v.name_en, v.sort
FROM (
          SELECT '1001' AS code, 'พระนคร' AS name_th, 'Phra Nakhon' AS name_en, 1 AS sort
UNION ALL SELECT '1002', 'ดุสิต', 'Dusit', 2
UNION ALL SELECT '1003', 'หนองจอก', 'Nong Chok', 3
UNION ALL SELECT '1004', 'บางรัก', 'Bang Rak', 4
UNION ALL SELECT '1005', 'บางเขน', 'Bang Khen', 5
UNION ALL SELECT '1006', 'บางกะปิ', 'Bang Kapi', 6
UNION ALL SELECT '1007', 'ปทุมวัน', 'Pathum Wan', 7
UNION ALL SELECT '1008', 'ป้อมปราบศัตรูพ่าย', 'Pom Prap Sattru Phai', 8
UNION ALL SELECT '1009', 'พระโขนง', 'Phra Khanong', 9
UNION ALL SELECT '1010', 'มีนบุรี', 'Min Buri', 10
UNION ALL SELECT '1011', 'ลาดกระบัง', 'Lat Krabang', 11
UNION ALL SELECT '1012', 'ยานนาวา', 'Yan Nawa', 12
UNION ALL SELECT '1013', 'สัมพันธวงศ์', 'Samphanthawong', 13
UNION ALL SELECT '1014', 'พญาไท', 'Phaya Thai', 14
UNION ALL SELECT '1015', 'ธนบุรี', 'Thon Buri', 15
UNION ALL SELECT '1016', 'บางกอกใหญ่', 'Bangkok Yai', 16
UNION ALL SELECT '1017', 'ห้วยขวาง', 'Huai Khwang', 17
UNION ALL SELECT '1018', 'คลองสาน', 'Khlong San', 18
UNION ALL SELECT '1019', 'ตลิ่งชัน', 'Taling Chan', 19
UNION ALL SELECT '1020', 'บางกอกน้อย', 'Bangkok Noi', 20
UNION ALL SELECT '1021', 'บางขุนเทียน', 'Bang Khun Thian', 21
UNION ALL SELECT '1022', 'ภาษีเจริญ', 'Phasi Charoen', 22
UNION ALL SELECT '1023', 'หนองแขม', 'Nong Khaem', 23
UNION ALL SELECT '1024', 'ราษฎร์บูรณะ', 'Rat Burana', 24
UNION ALL SELECT '1025', 'บางพลัด', 'Bang Phlat', 25
UNION ALL SELECT '1026', 'ดินแดง', 'Din Daeng', 26
UNION ALL SELECT '1027', 'บึงกุ่ม', 'Bueng Kum', 27
UNION ALL SELECT '1028', 'สาทร', 'Sathon', 28
UNION ALL SELECT '1029', 'บางซื่อ', 'Bang Sue', 29
UNION ALL SELECT '1030', 'จตุจักร', 'Chatuchak', 30
UNION ALL SELECT '1031', 'บางคอแหลม', 'Bang Kho Laem', 31
UNION ALL SELECT '1032', 'ประเวศ', 'Prawet', 32
UNION ALL SELECT '1033', 'คลองเตย', 'Khlong Toei', 33
UNION ALL SELECT '1034', 'สวนหลวง', 'Suan Luang', 34
UNION ALL SELECT '1035', 'จอมทอง', 'Chom Thong', 35
UNION ALL SELECT '1036', 'ดอนเมือง', 'Don Mueang', 36
UNION ALL SELECT '1037', 'ราชเทวี', 'Ratchathewi', 37
UNION ALL SELECT '1038', 'ลาดพร้าว', 'Lat Phrao', 38
UNION ALL SELECT '1039', 'วัฒนา', 'Vadhana', 39
UNION ALL SELECT '1040', 'บางแค', 'Bang Khae', 40
UNION ALL SELECT '1041', 'หลักสี่', 'Lak Si', 41
UNION ALL SELECT '1042', 'สายไหม', 'Sai Mai', 42
UNION ALL SELECT '1043', 'คันนายาว', 'Khan Na Yao', 43
UNION ALL SELECT '1044', 'สะพานสูง', 'Saphan Sung', 44
UNION ALL SELECT '1045', 'วังทองหลาง', 'Wang Thonglang', 45
UNION ALL SELECT '1046', 'คลองสามวา', 'Khlong Sam Wa', 46
UNION ALL SELECT '1047', 'บางนา', 'Bang Na', 47
UNION ALL SELECT '1048', 'ทวีวัฒนา', 'Thawi Watthana', 48
UNION ALL SELECT '1049', 'ทุ่งครุ', 'Thung Khru', 49
UNION ALL SELECT '1050', 'บางบอน', 'Bang Bon', 50
UNION ALL SELECT '1101', 'เมืองสมุทรปราการ', 'Mueang Samut Prakan', 1
UNION ALL SELECT '1102', 'บางบ่อ', 'Bang Bo', 2
UNION ALL SELECT '1103', 'บางพลี', 'Bang Phli', 3
UNION ALL SELECT '1104', 'พระประแดง', 'Phra Pradaeng', 4
UNION ALL SELECT '1105', 'พระสมุทรเจดีย์', 'Phra Samut Chedi', 5
UNION ALL SELECT '1106', 'บางเสาธง', 'Bang Sao Thong', 6
UNION ALL SELECT '1201', 'เมืองนนทบุรี', 'Mueang Nonthaburi', 1
UNION ALL SELECT '1202', 'บางกรวย', 'Bang Kruai', 2
UNION ALL SELECT '1203', 'บางใหญ่', 'Bang Yai', 3
UNION ALL SELECT '1204', 'บางบัวทอง', 'Bang Bua Thong', 4
UNION ALL SELECT '1205', 'ไทรน้อย', 'Sai Noi', 5
UNION ALL SELECT '1206', 'ปากเกร็ด', 'Pak Kret', 6
UNION ALL SELECT '1301', 'เมืองปทุมธานี', 'Mueang Pathum Thani', 1
UNION ALL SELECT '1302', 'คลองหลวง', 'Khlong Luang', 2
UNION ALL SELECT '1303', 'ธัญบุรี', 'Thanyaburi', 3
UNION ALL SELECT '1304', 'หนองเสือ', 'Nong Suea', 4
UNION ALL SELECT '1305', 'ลาดหลุมแก้ว', 'Lat Lum Kaeo', 5
UNION ALL SELECT '1306', 'ลำลูกกา', 'Lam Luk Ka', 6
UNION ALL SELECT '1307', 'สามโคก', 'Sam Khok', 7
UNION ALL SELECT '1401', 'พระนครศรีอยุธยา', 'Phra Nakhon Si Ayutthaya', 1
UNION ALL SELECT '1402', 'ท่าเรือ', 'Tha Ruea', 2
UNION ALL SELECT '1403', 'นครหลวง', 'Nakhon Luang', 3
UNION ALL SELECT '1404', 'บางไทร', 'Bang Sai', 4
UNION ALL SELECT '1405', 'บางบาล', 'Bang Ban', 5
UNION ALL SELECT '1406', 'บางปะอิน', 'Bang Pa-in', 6
UNION ALL SELECT '1407', 'บางปะหัน', 'Bang Pahan', 7
UNION ALL SELECT '1408', 'ผักไห่', 'Phak Hai', 8
UNION ALL SELECT '1409', 'ภาชี', 'Phachi', 9
UNION ALL SELECT '1410', 'ลาดบัวหลวง', 'Lat Bua Luang', 10
UNION ALL SELECT '1411', 'วังน้อย', 'Wang Noi', 11
UNION ALL SELECT '1412', 'เสนา', 'Sena', 12
UNION ALL SELECT '1413', 'บางซ้าย', 'Bang Sai', 13
UNION ALL SELECT '1414', 'อุทัย', 'Uthai', 14
UNION ALL SELECT '1415', 'มหาราช', 'Maha Rat', 15
UNION ALL SELECT '1416', 'บ้านแพรก', 'Ban Phraek', 16
UNION ALL SELECT '1501', 'เมืองอ่างทอง', 'Mueang Ang Thong', 1
UNION ALL SELECT '1502', 'ไชโย', 'Chaiyo', 2
UNION ALL SELECT '1503', 'ป่าโมก', 'Pa Mok', 3
UNION ALL SELECT '1504', 'โพธิ์ทอง', 'Pho Thong', 4
UNION ALL SELECT '1505', 'แสวงหา', 'Sawaeng Ha', 5
UNION ALL SELECT '1506', 'วิเศษชัยชาญ', 'Wiset Chai Chan', 6
UNION ALL SELECT '1507', 'สามโก้', 'Samko', 7
UNION ALL SELECT '1601', 'เมืองลพบุรี', 'Mueang Lop Buri', 1
UNION ALL SELECT '1602', 'พัฒนานิคม', 'Phatthana Nikhom', 2
UNION ALL SELECT '1603', 'โคกสำโรง', 'Khok Samrong', 3
UNION ALL SELECT '1604', 'ชัยบาดาล', 'Chai Badan', 4
UNION ALL SELECT '1605', 'ท่าวุ้ง', 'Tha Wung', 5
UNION ALL SELECT '1606', 'บ้านหมี่', 'Ban Mi', 6
UNION ALL SELECT '1607', 'ท่าหลวง', 'Tha Luang', 7
UNION ALL SELECT '1608', 'สระโบสถ์', 'Sa Bot', 8
UNION ALL SELECT '1609', 'โคกเจริญ', 'Khok Charoen', 9
UNION ALL SELECT '1610', 'ลำสนธิ', 'Lam Sonthi', 10
UNION ALL SELECT '1611', 'หนองม่วง', 'Nong Muang', 11
UNION ALL SELECT '1701', 'เมืองสิงห์บุรี', 'Mueang Sing Buri', 1
UNION ALL SELECT '1702', 'บางระจัน', 'Bang Rachan', 2
UNION ALL SELECT '1703', 'ค่ายบางระจัน', 'Khai Bang Rachan', 3
UNION ALL SELECT '1704', 'พรหมบุรี', 'Phrom Buri', 4
UNION ALL SELECT '1705', 'ท่าช้าง', 'Tha Chang', 5
UNION ALL SELECT '1706', 'อินทร์บุรี', 'In Buri', 6
UNION ALL SELECT '1801', 'เมืองชัยนาท', 'Mueang Chai Nat', 1
UNION ALL SELECT '1802', 'มโนรมย์', 'Manorom', 2
UNION ALL SELECT '1803', 'วัดสิงห์', 'Wat Sing', 3
UNION ALL SELECT '1804', 'สรรพยา', 'Sapphaya', 4
UNION ALL SELECT '1805', 'สรรคบุรี', 'Sankhaburi', 5
UNION ALL SELECT '1806', 'หันคา', 'Hankha', 6
UNION ALL SELECT '1807', 'หนองมะโมง', 'Nong Mamong', 7
UNION ALL SELECT '1808', 'เนินขาม', 'Noen Kham', 8
UNION ALL SELECT '1901', 'เมืองสระบุรี', 'Mueang Saraburi', 1
UNION ALL SELECT '1902', 'แก่งคอย', 'Kaeng Khoi', 2
UNION ALL SELECT '1903', 'หนองแค', 'Nong Khae', 3
UNION ALL SELECT '1904', 'วิหารแดง', 'Wihan Daeng', 4
UNION ALL SELECT '1905', 'หนองแซง', 'Nong Saeng', 5
UNION ALL SELECT '1906', 'บ้านหมอ', 'Ban Mo', 6
UNION ALL SELECT '1907', 'ดอนพุด', 'Don Phut', 7
UNION ALL SELECT '1908', 'หนองโดน', 'Nong Don', 8
UNION ALL SELECT '1909', 'พระพุทธบาท', 'Phra Phutthabat', 9
UNION ALL SELECT '1910', 'เสาไห้', 'Sao Hai', 10
UNION ALL SELECT '1911', 'มวกเหล็ก', 'Muak Lek', 11
UNION ALL SELECT '1912', 'วังม่วง', 'Wang Muang', 12
UNION ALL SELECT '1913', 'เฉลิมพระเกียรติ', 'Chaloem Phra Kiat', 13
UNION ALL SELECT '2001', 'เมืองชลบุรี', 'Mueang Chon Buri', 1
UNION ALL SELECT '2002', 'บ้านบึง', 'Ban Bueng', 2
UNION ALL SELECT '2003', 'หนองใหญ่', 'Nong Yai', 3
UNION ALL SELECT '2004', 'บางละมุง', 'Bang Lamung', 4
UNION ALL SELECT '2005', 'พานทอง', 'Phan Thong', 5
UNION ALL SELECT '2006', 'พนัสนิคม', 'Phanat Nikhom', 6
UNION ALL SELECT '2007', 'ศรีราชา', 'Si Racha', 7
UNION ALL SELECT '2008', 'เกาะสีชัง', 'Ko Sichang', 8
UNION ALL SELECT '2009', 'สัตหีบ', 'Sattahip', 9
UNION ALL SELECT '2010', 'บ่อทอง', 'Bo Thong', 10
UNION ALL SELECT '2011', 'เกาะจันทร์', 'Ko Chan', 11
UNION ALL SELECT '2101', 'เมืองระยอง', 'Mueang Rayong', 1
UNION ALL SELECT '2102', 'บ้านฉาง', 'Ban Chang', 2
UNION ALL SELECT '2103', 'แกลง', 'Klaeng', 3
UNION ALL SELECT '2104', 'วังจันทร์', 'Wang Chan', 4
UNION ALL SELECT '2105', 'บ้านค่าย', 'Ban Khai', 5
UNION ALL SELECT '2106', 'ปลวกแดง', 'Pluak Daeng', 6
UNION ALL SELECT '2107', 'เขาชะเมา', 'Khao Chamao', 7
UNION ALL SELECT '2108', 'นิคมพัฒนา', 'Nikhom Phatthana', 8
UNION ALL SELECT '2201', 'เมืองจันทบุรี', 'Mueang Chanthaburi', 1
UNION ALL SELECT '2202', 'ขลุง', 'Khlung', 2
UNION ALL SELECT '2203', 'ท่าใหม่', 'Tha Mai', 3
UNION ALL SELECT '2204', 'โป่งน้ำร้อน', 'Pong Nam Ron', 4
UNION ALL SELECT '2205', 'มะขาม', 'Makham', 5
UNION ALL SELECT '2206', 'แหลมสิงห์', 'Laem Sing', 6
UNION ALL SELECT '2207', 'สอยดาว', 'Soi Dao', 7
UNION ALL SELECT '2208', 'แก่งหางแมว', 'Kaeng Hang Maeo', 8
UNION ALL SELECT '2209', 'นายายอาม', 'Na Yai Am', 9
UNION ALL SELECT '2210', 'เขาคิชฌกูฏ', 'Khao Khitchakut', 10
UNION ALL SELECT '2301', 'เมืองตราด', 'Mueang Trat', 1
UNION ALL SELECT '2302', 'คลองใหญ่', 'Khlong Yai', 2
UNION ALL SELECT '2303', 'เขาสมิง', 'Khao Saming', 3
UNION ALL SELECT '2304', 'บ่อไร่', 'Bo Rai', 4
UNION ALL SELECT '2305', 'แหลมงอบ', 'Laem Ngop', 5
UNION ALL SELECT '2306', 'เกาะกูด', 'Ko Kut', 6
UNION ALL SELECT '2307', 'เกาะช้าง', 'Ko Chang', 7
UNION ALL SELECT '2401', 'เมืองฉะเชิงเทรา', 'Mueang Chachoengsao', 1
UNION ALL SELECT '2402', 'บางคล้า', 'Bang Khla', 2
UNION ALL SELECT '2403', 'บางน้ำเปรี้ยว', 'Bang Nam Priao', 3
UNION ALL SELECT '2404', 'บางปะกง', 'Bang Pakong', 4
UNION ALL SELECT '2405', 'บ้านโพธิ์', 'Ban Pho', 5
UNION ALL SELECT '2406', 'พนมสารคาม', 'Phanom Sarakham', 6
UNION ALL SELECT '2407', 'ราชสาส์น', 'Ratchasan', 7
UNION ALL SELECT '2408', 'สนามชัยเขต', 'Sanam Chai Khet', 8
UNION ALL SELECT '2409', 'แปลงยาว', 'Plaeng Yao', 9
UNION ALL SELECT '2410', 'ท่าตะเกียบ', 'Tha Takiap', 10
UNION ALL SELECT '2411', 'คลองเขื่อน', 'Khlong Khuean', 11
UNION ALL SELECT '2501', 'เมืองปราจีนบุรี', 'Mueang Prachin Buri', 1
UNION ALL SELECT '2502', 'กบินทร์บุรี', 'Kabin Buri', 2
UNION ALL SELECT '2503', 'นาดี', 'Na Di', 3
UNION ALL SELECT '2506', 'บ้านสร้าง', 'Ban Sang', 6
UNION ALL SELECT '2507', 'ประจันตคาม', 'Prachantakham', 7
UNION ALL SELECT '2508', 'ศรีมหาโพธิ', 'Si Maha Phot', 8
UNION ALL SELECT '2509', 'ศรีมโหสถ', 'Si Mahosot', 9
UNION ALL SELECT '2601', 'เมืองนครนายก', 'Mueang Nakhon Nayok', 1
UNION ALL SELECT '2602', 'ปากพลี', 'Pak Phli', 2
UNION ALL SELECT '2603', 'บ้านนา', 'Ban Na', 3
UNION ALL SELECT '2604', 'องครักษ์', 'Ongkharak', 4
UNION ALL SELECT '2701', 'เมืองสระแก้ว', 'Mueang Sa Kaeo', 1
UNION ALL SELECT '2702', 'คลองหาด', 'Khlong Hat', 2
UNION ALL SELECT '2703', 'ตาพระยา', 'Ta Phraya', 3
UNION ALL SELECT '2704', 'วังน้ำเย็น', 'Wang Nam Yen', 4
UNION ALL SELECT '2705', 'วัฒนานคร', 'Watthana Nakhon', 5
UNION ALL SELECT '2706', 'อรัญประเทศ', 'Aranyaprathet', 6
UNION ALL SELECT '2707', 'เขาฉกรรจ์', 'Khao Chakan', 7
UNION ALL SELECT '2708', 'โคกสูง', 'Khok Sung', 8
UNION ALL SELECT '2709', 'วังสมบูรณ์', 'Wang Sombun', 9
UNION ALL SELECT '3001', 'เมืองนครราชสีมา', 'Mueang Nakhon Ratchasima', 1
UNION ALL SELECT '3002', 'ครบุรี', 'Khon Buri', 2
UNION ALL SELECT '3003', 'เสิงสาง', 'Soeng Sang', 3
UNION ALL SELECT '3004', 'คง', 'Khong', 4
UNION ALL SELECT '3005', 'บ้านเหลื่อม', 'Ban Lueam', 5
UNION ALL SELECT '3006', 'จักราช', 'Chakkarat', 6
UNION ALL SELECT '3007', 'โชคชัย', 'Chok Chai', 7
UNION ALL SELECT '3008', 'ด่านขุนทด', 'Dan Khun Thot', 8
UNION ALL SELECT '3009', 'โนนไทย', 'Non Thai', 9
UNION ALL SELECT '3010', 'โนนสูง', 'Non Sung', 10
UNION ALL SELECT '3011', 'ขามสะแกแสง', 'Kham Sakaesaeng', 11
UNION ALL SELECT '3012', 'บัวใหญ่', 'Bua Yai', 12
UNION ALL SELECT '3013', 'ประทาย', 'Prathai', 13
UNION ALL SELECT '3014', 'ปักธงชัย', 'Pak Thong Chai', 14
UNION ALL SELECT '3015', 'พิมาย', 'Phimai', 15
UNION ALL SELECT '3016', 'ห้วยแถลง', 'Huai Thalaeng', 16
UNION ALL SELECT '3017', 'ชุมพวง', 'Chum Phuang', 17
UNION ALL SELECT '3018', 'สูงเนิน', 'Sung Noen', 18
UNION ALL SELECT '3019', 'ขามทะเลสอ', 'Kham Thale So', 19
UNION ALL SELECT '3020', 'สีคิ้ว', 'Sikhio', 20
UNION ALL SELECT '3021', 'ปากช่อง', 'Pak Chong', 21
UNION ALL SELECT '3022', 'หนองบุญมาก', 'Nong Bunmak', 22
UNION ALL SELECT '3023', 'แก้งสนามนาง', 'Kaeng Sanam Nang', 23
UNION ALL SELECT '3024', 'โนนแดง', 'Non Daeng', 24
UNION ALL SELECT '3025', 'วังน้ำเขียว', 'Wang Nam Khiao', 25
UNION ALL SELECT '3026', 'เทพารักษ์', 'Thepharak', 26
UNION ALL SELECT '3027', 'เมืองยาง', 'Mueang Yang', 27
UNION ALL SELECT '3028', 'พระทองคำ', 'Phra Thong Kham', 28
UNION ALL SELECT '3029', 'ลำทะเมนชัย', 'Lam Thamenchai', 29
UNION ALL SELECT '3030', 'บัวลาย', 'Bua Lai', 30
UNION ALL SELECT '3031', 'สีดา', 'Sida', 31
UNION ALL SELECT '3032', 'เฉลิมพระเกียรติ', 'Chaloem Phra Kiat', 32
UNION ALL SELECT '3101', 'เมืองบุรีรัมย์', 'Mueang Buri Ram', 1
UNION ALL SELECT '3102', 'คูเมือง', 'Khu Mueang', 2
UNION ALL SELECT '3103', 'กระสัง', 'Krasang', 3
UNION ALL SELECT '3104', 'นางรอง', 'Nang Rong', 4
UNION ALL SELECT '3105', 'หนองกี่', 'Nong Ki', 5
UNION ALL SELECT '3106', 'ละหานทราย', 'Lahan Sai', 6
UNION ALL SELECT '3107', 'ประโคนชัย', 'Prakhon Chai', 7
UNION ALL SELECT '3108', 'บ้านกรวด', 'Ban Kruat', 8
UNION ALL SELECT '3109', 'พุทไธสง', 'Phutthaisong', 9
UNION ALL SELECT '3110', 'ลำปลายมาศ', 'Lam Plai Mat', 10
UNION ALL SELECT '3111', 'สตึก', 'Satuek', 11
UNION ALL SELECT '3112', 'ปะคำ', 'Pakham', 12
UNION ALL SELECT '3113', 'นาโพธิ์', 'Na Pho', 13
UNION ALL SELECT '3114', 'หนองหงส์', 'Nong Hong', 14
UNION ALL SELECT '3115', 'พลับพลาชัย', 'Phlapphla Chai', 15
UNION ALL SELECT '3116', 'ห้วยราช', 'Huai Rat', 16
UNION ALL SELECT '3117', 'โนนสุวรรณ', 'Non Suwan', 17
UNION ALL SELECT '3118', 'ชำนิ', 'Chamni', 18
UNION ALL SELECT '3119', 'บ้านใหม่ไชยพจน์', 'Ban Mai Chaiyaphot', 19
UNION ALL SELECT '3120', 'โนนดินแดง', 'Non Din Daeng', 20
UNION ALL SELECT '3121', 'บ้านด่าน', 'Ban Dan', 21
UNION ALL SELECT '3122', 'แคนดง', 'Khaen Dong', 22
UNION ALL SELECT '3123', 'เฉลิมพระเกียรติ', 'Chaloem Phra Kiat', 23
UNION ALL SELECT '3201', 'เมืองสุรินทร์', 'Mueang Surin', 1
UNION ALL SELECT '3202', 'ชุมพลบุรี', 'Chumphon Buri', 2
UNION ALL SELECT '3203', 'ท่าตูม', 'Tha Tum', 3
UNION ALL SELECT '3204', 'จอมพระ', 'Chom Phra', 4
UNION ALL SELECT '3205', 'ปราสาท', 'Prasat', 5
UNION ALL SELECT '3206', 'กาบเชิง', 'Kap Choeng', 6
UNION ALL SELECT '3207', 'รัตนบุรี', 'Rattanaburi', 7
UNION ALL SELECT '3208', 'สนม', 'Sanom', 8
UNION ALL SELECT '3209', 'ศีขรภูมิ', 'Sikhoraphum', 9
UNION ALL SELECT '3210', 'สังขะ', 'Sangkha', 10
UNION ALL SELECT '3211', 'ลำดวน', 'Lamduan', 11
UNION ALL SELECT '3212', 'สำโรงทาบ', 'Samrong Thap', 12
UNION ALL SELECT '3213', 'บัวเชด', 'Buachet', 13
UNION ALL SELECT '3214', 'พนมดงรัก', 'Phanom Dong Rak', 14
UNION ALL SELECT '3215', 'ศรีณรงค์', 'Si Narong', 15
UNION ALL SELECT '3216', 'เขวาสินรินทร์', 'Khwao Sinrin', 16
UNION ALL SELECT '3217', 'โนนนารายณ์', 'Non Narai', 17
UNION ALL SELECT '3301', 'เมืองศรีสะเกษ', 'Mueang Si Sa Ket', 1
UNION ALL SELECT '3302', 'ยางชุมน้อย', 'Yang Chum Noi', 2
UNION ALL SELECT '3303', 'กันทรารมย์', 'Kanthararom', 3
UNION ALL SELECT '3304', 'กันทรลักษ์', 'Kantharalak', 4
UNION ALL SELECT '3305', 'ขุขันธ์', 'Khukhan', 5
UNION ALL SELECT '3306', 'ไพรบึง', 'Phrai Bueng', 6
UNION ALL SELECT '3307', 'ปรางค์กู่', 'Prang Ku', 7
UNION ALL SELECT '3308', 'ขุนหาญ', 'Khun Han', 8
UNION ALL SELECT '3309', 'ราษีไศล', 'Rasi Salai', 9
UNION ALL SELECT '3310', 'อุทุมพรพิสัย', 'Uthumphon Phisai', 10
UNION ALL SELECT '3311', 'บึงบูรพ์', 'Bueng Bun', 11
UNION ALL SELECT '3312', 'ห้วยทับทัน', 'Huai Thap Than', 12
UNION ALL SELECT '3313', 'โนนคูณ', 'Non Khun', 13
UNION ALL SELECT '3314', 'ศรีรัตนะ', 'Si Rattana', 14
UNION ALL SELECT '3315', 'น้ำเกลี้ยง', 'Nam Kliang', 15
UNION ALL SELECT '3316', 'วังหิน', 'Wang Hin', 16
UNION ALL SELECT '3317', 'ภูสิงห์', 'Phu Sing', 17
UNION ALL SELECT '3318', 'เมืองจันทร์', 'Mueang Chan', 18
UNION ALL SELECT '3319', 'เบญจลักษ์', 'Benchalak', 19
UNION ALL SELECT '3320', 'พยุห์', 'Phayu', 20
UNION ALL SELECT '3321', 'โพธิ์ศรีสุวรรณ', 'Pho Si Suwan', 21
UNION ALL SELECT '3322', 'ศิลาลาด', 'Sila Lat', 22
UNION ALL SELECT '3401', 'เมืองอุบลราชธานี', 'Mueang Ubon Ratchathani', 1
UNION ALL SELECT '3402', 'ศรีเมืองใหม่', 'Si Mueang Mai', 2
UNION ALL SELECT '3403', 'โขงเจียม', 'Khong Chiam', 3
UNION ALL SELECT '3404', 'เขื่องใน', 'Khueang Nai', 4
UNION ALL SELECT '3405', 'เขมราฐ', 'Khemarat', 5
UNION ALL SELECT '3407', 'เดชอุดม', 'Det Udom', 7
UNION ALL SELECT '3408', 'นาจะหลวย', 'Na Chaluai', 8
UNION ALL SELECT '3409', 'น้ำยืน', 'Nam Yuen', 9
UNION ALL SELECT '3410', 'บุณฑริก', 'Buntharik', 10
UNION ALL SELECT '3411', 'ตระการพืชผล', 'Trakan Phuet Phon', 11
UNION ALL SELECT '3412', 'กุดข้าวปุ้น', 'Kut Khaopun', 12
UNION ALL SELECT '3414', 'ม่วงสามสิบ', 'Muang Sam Sip', 14
UNION ALL SELECT '3415', 'วารินชำราบ', 'Warin Chamrap', 15
UNION ALL SELECT '3419', 'พิบูลมังสาหาร', 'Phibun Mangsahan', 19
UNION ALL SELECT '3420', 'ตาลสุม', 'Tan Sum', 20
UNION ALL SELECT '3421', 'โพธิ์ไทร', 'Pho Sai', 21
UNION ALL SELECT '3422', 'สำโรง', 'Samrong', 22
UNION ALL SELECT '3424', 'ดอนมดแดง', 'Don Mot Daeng', 24
UNION ALL SELECT '3425', 'สิรินธร', 'Sirindhorn', 25
UNION ALL SELECT '3426', 'ทุ่งศรีอุดม', 'Thung Si Udom', 26
UNION ALL SELECT '3429', 'นาเยีย', 'Na Yia', 29
UNION ALL SELECT '3430', 'นาตาล', 'Na Tan', 30
UNION ALL SELECT '3431', 'เหล่าเสือโก้ก', 'Lao Suea Kok', 31
UNION ALL SELECT '3432', 'สว่างวีระวงศ์', 'Sawang Wirawong', 32
UNION ALL SELECT '3433', 'น้ำขุ่น', 'Nam Khun', 33
UNION ALL SELECT '3501', 'เมืองยโสธร', 'Mueang Yasothon', 1
UNION ALL SELECT '3502', 'ทรายมูล', 'Sai Mun', 2
UNION ALL SELECT '3503', 'กุดชุม', 'Kut Chum', 3
UNION ALL SELECT '3504', 'คำเขื่อนแก้ว', 'Kham Khuean Kaeo', 4
UNION ALL SELECT '3505', 'ป่าติ้ว', 'Pa Tio', 5
UNION ALL SELECT '3506', 'มหาชนะชัย', 'Maha Chana Chai', 6
UNION ALL SELECT '3507', 'ค้อวัง', 'Kho Wang', 7
UNION ALL SELECT '3508', 'เลิงนกทา', 'Loeng Nok Tha', 8
UNION ALL SELECT '3509', 'ไทยเจริญ', 'Thai Charoen', 9
UNION ALL SELECT '3601', 'เมืองชัยภูมิ', 'Mueang Chaiyaphum', 1
UNION ALL SELECT '3602', 'บ้านเขว้า', 'Ban Khwao', 2
UNION ALL SELECT '3603', 'คอนสวรรค์', 'Khon Sawan', 3
UNION ALL SELECT '3604', 'เกษตรสมบูรณ์', 'Kaset Sombun', 4
UNION ALL SELECT '3605', 'หนองบัวแดง', 'Nong Bua Daeng', 5
UNION ALL SELECT '3606', 'จัตุรัส', 'Chatturat', 6
UNION ALL SELECT '3607', 'บำเหน็จณรงค์', 'Bamnet Narong', 7
UNION ALL SELECT '3608', 'หนองบัวระเหว', 'Nong Bua Rawe', 8
UNION ALL SELECT '3609', 'เทพสถิต', 'Thep Sathit', 9
UNION ALL SELECT '3610', 'ภูเขียว', 'Phu Khiao', 10
UNION ALL SELECT '3611', 'บ้านแท่น', 'Ban Thaen', 11
UNION ALL SELECT '3612', 'แก้งคร้อ', 'Kaeng Khro', 12
UNION ALL SELECT '3613', 'คอนสาร', 'Khon San', 13
UNION ALL SELECT '3614', 'ภักดีชุมพล', 'Phakdi Chumphon', 14
UNION ALL SELECT '3615', 'เนินสง่า', 'Noen Sa-nga', 15
UNION ALL SELECT '3616', 'ซับใหญ่', 'Sap Yai', 16
UNION ALL SELECT '3701', 'เมืองอำนาจเจริญ', 'Mueang Amnat Charoen', 1
UNION ALL SELECT '3702', 'ชานุมาน', 'Chanuman', 2
UNION ALL SELECT '3703', 'ปทุมราชวงศา', 'Pathum Ratchawongsa', 3
UNION ALL SELECT '3704', 'พนา', 'Phana', 4
UNION ALL SELECT '3705', 'เสนางคนิคม', 'Senangkhanikhom', 5
UNION ALL SELECT '3706', 'หัวตะพาน', 'Hua Taphan', 6
UNION ALL SELECT '3707', 'ลืออำนาจ', 'Lue Amnat', 7
UNION ALL SELECT '3801', 'เมืองบึงกาฬ', 'Mueang Bueng Kan', 1
UNION ALL SELECT '3802', 'พรเจริญ', 'Phon Charoen', 2
UNION ALL SELECT '3803', 'โซ่พิสัย', 'So Phisai', 3
UNION ALL SELECT '3804', 'เซกา', 'Seka', 4
UNION ALL SELECT '3805', 'ปากคาด', 'Pak Khat', 5
UNION ALL SELECT '3806', 'บึงโขงหลง', 'Bueng Khong Long', 6
UNION ALL SELECT '3807', 'ศรีวิไล', 'Si Wilai', 7
UNION ALL SELECT '3808', 'บุ่งคล้า', 'Bung Khla', 8
UNION ALL SELECT '3901', 'เมืองหนองบัวลำภู', 'Mueang Nong Bua Lam Phu', 1
UNION ALL SELECT '3902', 'นากลาง', 'Na Klang', 2
UNION ALL SELECT '3903', 'โนนสัง', 'Non Sang', 3
UNION ALL SELECT '3904', 'ศรีบุญเรือง', 'Si Bun Rueang', 4
UNION ALL SELECT '3905', 'สุวรรณคูหา', 'Suwannakhuha', 5
UNION ALL SELECT '3906', 'นาวัง', 'Na Wang', 6
UNION ALL SELECT '4001', 'เมืองขอนแก่น', 'Mueang Khon Kaen', 1
UNION ALL SELECT '4002', 'บ้านฝาง', 'Ban Fang', 2
UNION ALL SELECT '4003', 'พระยืน', 'Phra Yuen', 3
UNION ALL SELECT '4004', 'หนองเรือ', 'Nong Ruea', 4
UNION ALL SELECT '4005', 'ชุมแพ', 'Chum Phae', 5
UNION ALL SELECT '4006', 'สีชมพู', 'Si Chomphu', 6
UNION ALL SELECT '4007', 'น้ำพอง', 'Nam Phong', 7
UNION ALL SELECT '4008', 'อุบลรัตน์', 'Ubolratana', 8
UNION ALL SELECT '4009', 'กระนวน', 'Kranuan', 9
UNION ALL SELECT '4010', 'บ้านไผ่', 'Ban Phai', 10
UNION ALL SELECT '4011', 'เปือยน้อย', 'Pueai Noi', 11
UNION ALL SELECT '4012', 'พล', 'Phon', 12
UNION ALL SELECT '4013', 'แวงใหญ่', 'Waeng Yai', 13
UNION ALL SELECT '4014', 'แวงน้อย', 'Waeng Noi', 14
UNION ALL SELECT '4015', 'หนองสองห้อง', 'Nong Song Hong', 15
UNION ALL SELECT '4016', 'ภูเวียง', 'Phu Wiang', 16
UNION ALL SELECT '4017', 'มัญจาคีรี', 'Mancha Khiri', 17
UNION ALL SELECT '4018', 'ชนบท', 'Chonnabot', 18
UNION ALL SELECT '4019', 'เขาสวนกวาง', 'Khao Suan Kwang', 19
UNION ALL SELECT '4020', 'ภูผาม่าน', 'Phu Pha Man', 20
UNION ALL SELECT '4021', 'ซำสูง', 'Sam Sung', 21
UNION ALL SELECT '4022', 'โคกโพธิ์ไชย', 'Khok Pho Chai', 22
UNION ALL SELECT '4023', 'หนองนาคำ', 'Nong Na Kham', 23
UNION ALL SELECT '4024', 'บ้านแฮด', 'Ban Haet', 24
UNION ALL SELECT '4025', 'โนนศิลา', 'Non Sila', 25
UNION ALL SELECT '4029', 'เวียงเก่า', 'Wiang Kao', 29
UNION ALL SELECT '4101', 'เมืองอุดรธานี', 'Mueang Udon Thani', 1
UNION ALL SELECT '4102', 'กุดจับ', 'Kut Chap', 2
UNION ALL SELECT '4103', 'หนองวัวซอ', 'Nong Wua So', 3
UNION ALL SELECT '4104', 'กุมภวาปี', 'Kumphawapi', 4
UNION ALL SELECT '4105', 'โนนสะอาด', 'Non Sa-at', 5
UNION ALL SELECT '4106', 'หนองหาน', 'Nong Han', 6
UNION ALL SELECT '4107', 'ทุ่งฝน', 'Thung Fon', 7
UNION ALL SELECT '4108', 'ไชยวาน', 'Chai Wan', 8
UNION ALL SELECT '4109', 'ศรีธาตุ', 'Si That', 9
UNION ALL SELECT '4110', 'วังสามหมอ', 'Wang Sam Mo', 10
UNION ALL SELECT '4111', 'บ้านดุง', 'Ban Dung', 11
UNION ALL SELECT '4117', 'บ้านผือ', 'Ban Phue', 17
UNION ALL SELECT '4118', 'น้ำโสม', 'Nam Som', 18
UNION ALL SELECT '4119', 'เพ็ญ', 'Phen', 19
UNION ALL SELECT '4120', 'สร้างคอม', 'Sang Khom', 20
UNION ALL SELECT '4121', 'หนองแสง', 'Nong Saeng', 21
UNION ALL SELECT '4122', 'นายูง', 'Na Yung', 22
UNION ALL SELECT '4123', 'พิบูลย์รักษ์', 'Phibun Rak', 23
UNION ALL SELECT '4124', 'กู่แก้ว', 'Ku Kaeo', 24
UNION ALL SELECT '4125', 'ประจักษ์ศิลปาคม', 'Prachak Sinlapakhom', 25
UNION ALL SELECT '4201', 'เมืองเลย', 'Mueang Loei', 1
UNION ALL SELECT '4202', 'นาด้วง', 'Na Duang', 2
UNION ALL SELECT '4203', 'เชียงคาน', 'Chiang Khan', 3
UNION ALL SELECT '4204', 'ปากชม', 'Pak Chom', 4
UNION ALL SELECT '4205', 'ด่านซ้าย', 'Dan Sai', 5
UNION ALL SELECT '4206', 'นาแห้ว', 'Na Haeo', 6
UNION ALL SELECT '4207', 'ภูเรือ', 'Phu Ruea', 7
UNION ALL SELECT '4208', 'ท่าลี่', 'Tha Li', 8
UNION ALL SELECT '4209', 'วังสะพุง', 'Wang Saphung', 9
UNION ALL SELECT '4210', 'ภูกระดึง', 'Phu Kradueng', 10
UNION ALL SELECT '4211', 'ภูหลวง', 'Phu Luang', 11
UNION ALL SELECT '4212', 'ผาขาว', 'Pha Khao', 12
UNION ALL SELECT '4213', 'เอราวัณ', 'Erawan', 13
UNION ALL SELECT '4214', 'หนองหิน', 'Nong Hin', 14
UNION ALL SELECT '4301', 'เมืองหนองคาย', 'Mueang Nong Khai', 1
UNION ALL SELECT '4302', 'ท่าบ่อ', 'Tha Bo', 2
UNION ALL SELECT '4305', 'โพนพิสัย', 'Phon Phisai', 5
UNION ALL SELECT '4307', 'ศรีเชียงใหม่', 'Si Chiang Mai', 7
UNION ALL SELECT '4308', 'สังคม', 'Sangkhom', 8
UNION ALL SELECT '4314', 'สระใคร', 'Sakhrai', 14
UNION ALL SELECT '4315', 'เฝ้าไร่', 'Fao Rai', 15
UNION ALL SELECT '4316', 'รัตนวาปี', 'Rattanawapi', 16
UNION ALL SELECT '4317', 'โพธิ์ตาก', 'Pho Tak', 17
UNION ALL SELECT '4401', 'เมืองมหาสารคาม', 'Mueang Maha Sarakham', 1
UNION ALL SELECT '4402', 'แกดำ', 'Kae Dam', 2
UNION ALL SELECT '4403', 'โกสุมพิสัย', 'Kosum Phisai', 3
UNION ALL SELECT '4404', 'กันทรวิชัย', 'Kantharawichai', 4
UNION ALL SELECT '4405', 'เชียงยืน', 'Chiang Yuen', 5
UNION ALL SELECT '4406', 'บรบือ', 'Borabue', 6
UNION ALL SELECT '4407', 'นาเชือก', 'Na Chueak', 7
UNION ALL SELECT '4408', 'พยัคฆภูมิพิสัย', 'Phayakkhaphum Phisai', 8
UNION ALL SELECT '4409', 'วาปีปทุม', 'Wapi Pathum', 9
UNION ALL SELECT '4410', 'นาดูน', 'Na Dun', 10
UNION ALL SELECT '4411', 'ยางสีสุราช', 'Yang Si Surat', 11
UNION ALL SELECT '4412', 'กุดรัง', 'Kut Rang', 12
UNION ALL SELECT '4413', 'ชื่นชม', 'Chuen Chom', 13
UNION ALL SELECT '4501', 'เมืองร้อยเอ็ด', 'Mueang Roi Et', 1
UNION ALL SELECT '4502', 'เกษตรวิสัย', 'Kaset Wisai', 2
UNION ALL SELECT '4503', 'ปทุมรัตต์', 'Pathum Rat', 3
UNION ALL SELECT '4504', 'จตุรพักตรพิมาน', 'Chaturaphak Phiman', 4
UNION ALL SELECT '4505', 'ธวัชบุรี', 'Thawat Buri', 5
UNION ALL SELECT '4506', 'พนมไพร', 'Phanom Phrai', 6
UNION ALL SELECT '4507', 'โพนทอง', 'Phon Thong', 7
UNION ALL SELECT '4508', 'โพธิ์ชัย', 'Pho Chai', 8
UNION ALL SELECT '4509', 'หนองพอก', 'Nong Phok', 9
UNION ALL SELECT '4510', 'เสลภูมิ', 'Selaphum', 10
UNION ALL SELECT '4511', 'สุวรรณภูมิ', 'Suwannaphum', 11
UNION ALL SELECT '4512', 'เมืองสรวง', 'Mueang Suang', 12
UNION ALL SELECT '4513', 'โพนทราย', 'Phon Sai', 13
UNION ALL SELECT '4514', 'อาจสามารถ', 'At Samat', 14
UNION ALL SELECT '4515', 'เมยวดี', 'Moei Wadi', 15
UNION ALL SELECT '4516', 'ศรีสมเด็จ', 'Si Somdet', 16
UNION ALL SELECT '4517', 'จังหาร', 'Changhan', 17
UNION ALL SELECT '4518', 'เชียงขวัญ', 'Chiang Khwan', 18
UNION ALL SELECT '4519', 'หนองฮี', 'Nong Hi', 19
UNION ALL SELECT '4520', 'ทุ่งเขาหลวง', 'Thung Khao Luang', 20
UNION ALL SELECT '4601', 'เมืองกาฬสินธุ์', 'Mueang Kalasin', 1
UNION ALL SELECT '4602', 'นามน', 'Na Mon', 2
UNION ALL SELECT '4603', 'กมลาไสย', 'Kamalasai', 3
UNION ALL SELECT '4604', 'ร่องคำ', 'Rong Kham', 4
UNION ALL SELECT '4605', 'กุฉินารายณ์', 'Kuchinarai', 5
UNION ALL SELECT '4606', 'เขาวง', 'Khao Wong', 6
UNION ALL SELECT '4607', 'ยางตลาด', 'Yang Talat', 7
UNION ALL SELECT '4608', 'ห้วยเม็ก', 'Huai Mek', 8
UNION ALL SELECT '4609', 'สหัสขันธ์', 'Sahatsakhan', 9
UNION ALL SELECT '4610', 'คำม่วง', 'Kham Muang', 10
UNION ALL SELECT '4611', 'ท่าคันโท', 'Tha Khantho', 11
UNION ALL SELECT '4612', 'หนองกุงศรี', 'Nong Kung Si', 12
UNION ALL SELECT '4613', 'สมเด็จ', 'Somdet', 13
UNION ALL SELECT '4614', 'ห้วยผึ้ง', 'Huai Phueng', 14
UNION ALL SELECT '4615', 'สามชัย', 'Sam Chai', 15
UNION ALL SELECT '4616', 'นาคู', 'Na Khu', 16
UNION ALL SELECT '4617', 'ดอนจาน', 'Don Chan', 17
UNION ALL SELECT '4618', 'ฆ้องชัย', 'Khong Chai', 18
UNION ALL SELECT '4701', 'เมืองสกลนคร', 'Mueang Sakon Nakhon', 1
UNION ALL SELECT '4702', 'กุสุมาลย์', 'Kusuman', 2
UNION ALL SELECT '4703', 'กุดบาก', 'Kut Bak', 3
UNION ALL SELECT '4704', 'พรรณานิคม', 'Phanna Nikhom', 4
UNION ALL SELECT '4705', 'พังโคน', 'Phang Khon', 5
UNION ALL SELECT '4706', 'วาริชภูมิ', 'Waritchaphum', 6
UNION ALL SELECT '4707', 'นิคมน้ำอูน', 'Nikhom Nam Un', 7
UNION ALL SELECT '4708', 'วานรนิวาส', 'Wanon Niwat', 8
UNION ALL SELECT '4709', 'คำตากล้า', 'Kham Ta Kla', 9
UNION ALL SELECT '4710', 'บ้านม่วง', 'Ban Muang', 10
UNION ALL SELECT '4711', 'อากาศอำนวย', 'Akat Amnuai', 11
UNION ALL SELECT '4712', 'สว่างแดนดิน', 'Sawang Daen Din', 12
UNION ALL SELECT '4713', 'ส่องดาว', 'Song Dao', 13
UNION ALL SELECT '4714', 'เต่างอย', 'Tao Ngoi', 14
UNION ALL SELECT '4715', 'โคกศรีสุพรรณ', 'Khok Si Suphan', 15
UNION ALL SELECT '4716', 'เจริญศิลป์', 'Charoen Sin', 16
UNION ALL SELECT '4717', 'โพนนาแก้ว', 'Phon Na Kaeo', 17
UNION ALL SELECT '4718', 'ภูพาน', 'Phu Phan', 18
UNION ALL SELECT '4801', 'เมืองนครพนม', 'Mueang Nakhon Phanom', 1
UNION ALL SELECT '4802', 'ปลาปาก', 'Pla Pak', 2
UNION ALL SELECT '4803', 'ท่าอุเทน', 'Tha Uthen', 3
UNION ALL SELECT '4804', 'บ้านแพง', 'Ban Phaeng', 4
UNION ALL SELECT '4805', 'ธาตุพนม', 'That Phanom', 5
UNION ALL SELECT '4806', 'เรณูนคร', 'Renu Nakhon', 6
UNION ALL SELECT '4807', 'นาแก', 'Na Kae', 7
UNION ALL SELECT '4808', 'ศรีสงคราม', 'Si Songkhram', 8
UNION ALL SELECT '4809', 'นาหว้า', 'Na Wa', 9
UNION ALL SELECT '4810', 'โพนสวรรค์', 'Phon Sawan', 10
UNION ALL SELECT '4811', 'นาทม', 'Na Thom', 11
UNION ALL SELECT '4812', 'วังยาง', 'Wang Yang', 12
UNION ALL SELECT '4901', 'เมืองมุกดาหาร', 'Mueang Mukdahan', 1
UNION ALL SELECT '4902', 'นิคมคำสร้อย', 'Nikhom Kham Soi', 2
UNION ALL SELECT '4903', 'ดอนตาล', 'Don Tan', 3
UNION ALL SELECT '4904', 'ดงหลวง', 'Dong Luang', 4
UNION ALL SELECT '4905', 'คำชะอี', 'Khamcha-i', 5
UNION ALL SELECT '4906', 'หว้านใหญ่', 'Wan Yai', 6
UNION ALL SELECT '4907', 'หนองสูง', 'Nong Sung', 7
UNION ALL SELECT '5001', 'เมืองเชียงใหม่', 'Mueang Chiang Mai', 1
UNION ALL SELECT '5002', 'จอมทอง', 'Chom Thong', 2
UNION ALL SELECT '5003', 'แม่แจ่ม', 'Mae Chaem', 3
UNION ALL SELECT '5004', 'เชียงดาว', 'Chiang Dao', 4
UNION ALL SELECT '5005', 'ดอยสะเก็ด', 'Doi Saket', 5
UNION ALL SELECT '5006', 'แม่แตง', 'Mae Taeng', 6
UNION ALL SELECT '5007', 'แม่ริม', 'Mae Rim', 7
UNION ALL SELECT '5008', 'สะเมิง', 'Samoeng', 8
UNION ALL SELECT '5009', 'ฝาง', 'Fang', 9
UNION ALL SELECT '5010', 'แม่อาย', 'Mae Ai', 10
UNION ALL SELECT '5011', 'พร้าว', 'Phrao', 11
UNION ALL SELECT '5012', 'สันป่าตอง', 'San Pa Tong', 12
UNION ALL SELECT '5013', 'สันกำแพง', 'San Kamphaeng', 13
UNION ALL SELECT '5014', 'สันทราย', 'San Sai', 14
UNION ALL SELECT '5015', 'หางดง', 'Hang Dong', 15
UNION ALL SELECT '5016', 'ฮอด', 'Hot', 16
UNION ALL SELECT '5017', 'ดอยเต่า', 'Doi Tao', 17
UNION ALL SELECT '5018', 'อมก๋อย', 'Omkoi', 18
UNION ALL SELECT '5019', 'สารภี', 'Saraphi', 19
UNION ALL SELECT '5020', 'เวียงแหง', 'Wiang Haeng', 20
UNION ALL SELECT '5021', 'ไชยปราการ', 'Chai Prakan', 21
UNION ALL SELECT '5022', 'แม่วาง', 'Mae Wang', 22
UNION ALL SELECT '5023', 'แม่ออน', 'Mae On', 23
UNION ALL SELECT '5024', 'ดอยหล่อ', 'Doi Lo', 24
UNION ALL SELECT '5025', 'กัลยาณิวัฒนา', 'Galayani Vadhana', 25
UNION ALL SELECT '5101', 'เมืองลำพูน', 'Mueang Lamphun', 1
UNION ALL SELECT '5102', 'แม่ทา', 'Mae Tha', 2
UNION ALL SELECT '5103', 'บ้านโฮ่ง', 'Ban Hong', 3
UNION ALL SELECT '5104', 'ลี้', 'Li', 4
UNION ALL SELECT '5105', 'ทุ่งหัวช้าง', 'Thung Hua Chang', 5
UNION ALL SELECT '5106', 'ป่าซาง', 'Pa Sang', 6
UNION ALL SELECT '5107', 'บ้านธิ', 'Ban Thi', 7
UNION ALL SELECT '5108', 'เวียงหนองล่อง', 'Wiang Nong Long', 8
UNION ALL SELECT '5201', 'เมืองลำปาง', 'Mueang Lampang', 1
UNION ALL SELECT '5202', 'แม่เมาะ', 'Mae Mo', 2
UNION ALL SELECT '5203', 'เกาะคา', 'Ko Kha', 3
UNION ALL SELECT '5204', 'เสริมงาม', 'Soem Ngam', 4
UNION ALL SELECT '5205', 'งาว', 'Ngao', 5
UNION ALL SELECT '5206', 'แจ้ห่ม', 'Chae Hom', 6
UNION ALL SELECT '5207', 'วังเหนือ', 'Wang Nuea', 7
UNION ALL SELECT '5208', 'เถิน', 'Thoen', 8
UNION ALL SELECT '5209', 'แม่พริก', 'Mae Phrik', 9
UNION ALL SELECT '5210', 'แม่ทะ', 'Mae Tha', 10
UNION ALL SELECT '5211', 'สบปราบ', 'Sop Prap', 11
UNION ALL SELECT '5212', 'ห้างฉัตร', 'Hang Chat', 12
UNION ALL SELECT '5213', 'เมืองปาน', 'Mueang Pan', 13
UNION ALL SELECT '5301', 'เมืองอุตรดิตถ์', 'Mueang Uttaradit', 1
UNION ALL SELECT '5302', 'ตรอน', 'Tron', 2
UNION ALL SELECT '5303', 'ท่าปลา', 'Tha Pla', 3
UNION ALL SELECT '5304', 'น้ำปาด', 'Nam Pat', 4
UNION ALL SELECT '5305', 'ฟากท่า', 'Fak Tha', 5
UNION ALL SELECT '5306', 'บ้านโคก', 'Ban Khok', 6
UNION ALL SELECT '5307', 'พิชัย', 'Phichai', 7
UNION ALL SELECT '5308', 'ลับแล', 'Laplae', 8
UNION ALL SELECT '5309', 'ทองแสนขัน', 'Thong Saen Khan', 9
UNION ALL SELECT '5401', 'เมืองแพร่', 'Mueang Phrae', 1
UNION ALL SELECT '5402', 'ร้องกวาง', 'Rong Kwang', 2
UNION ALL SELECT '5403', 'ลอง', 'Long', 3
UNION ALL SELECT '5404', 'สูงเม่น', 'Sung Men', 4
UNION ALL SELECT '5405', 'เด่นชัย', 'Den Chai', 5
UNION ALL SELECT '5406', 'สอง', 'Song', 6
UNION ALL SELECT '5407', 'วังชิ้น', 'Wang Chin', 7
UNION ALL SELECT '5408', 'หนองม่วงไข่', 'Nong Muang Khai', 8
UNION ALL SELECT '5501', 'เมืองน่าน', 'Mueang Nan', 1
UNION ALL SELECT '5502', 'แม่จริม', 'Mae Charim', 2
UNION ALL SELECT '5503', 'บ้านหลวง', 'Ban Luang', 3
UNION ALL SELECT '5504', 'นาน้อย', 'Na Noi', 4
UNION ALL SELECT '5505', 'ปัว', 'Pua', 5
UNION ALL SELECT '5506', 'ท่าวังผา', 'Tha Wang Pha', 6
UNION ALL SELECT '5507', 'เวียงสา', 'Wiang Sa', 7
UNION ALL SELECT '5508', 'ทุ่งช้าง', 'Thung Chang', 8
UNION ALL SELECT '5509', 'เชียงกลาง', 'Chiang Klang', 9
UNION ALL SELECT '5510', 'นาหมื่น', 'Na Muen', 10
UNION ALL SELECT '5511', 'สันติสุข', 'Santi Suk', 11
UNION ALL SELECT '5512', 'บ่อเกลือ', 'Bo Kluea', 12
UNION ALL SELECT '5513', 'สองแคว', 'Song Khwae', 13
UNION ALL SELECT '5514', 'ภูเพียง', 'Phu Phiang', 14
UNION ALL SELECT '5515', 'เฉลิมพระเกียรติ', 'Chaloem Phra Kiat', 15
UNION ALL SELECT '5601', 'เมืองพะเยา', 'Mueang Phayao', 1
UNION ALL SELECT '5602', 'จุน', 'Chun', 2
UNION ALL SELECT '5603', 'เชียงคำ', 'Chiang Kham', 3
UNION ALL SELECT '5604', 'เชียงม่วน', 'Chiang Muan', 4
UNION ALL SELECT '5605', 'ดอกคำใต้', 'Dok Khamtai', 5
UNION ALL SELECT '5606', 'ปง', 'Pong', 6
UNION ALL SELECT '5607', 'แม่ใจ', 'Mae Chai', 7
UNION ALL SELECT '5608', 'ภูซาง', 'Phu Sang', 8
UNION ALL SELECT '5609', 'ภูกามยาว', 'Phu Kamyao', 9
UNION ALL SELECT '5701', 'เมืองเชียงราย', 'Mueang Chiang Rai', 1
UNION ALL SELECT '5702', 'เวียงชัย', 'Wiang Chai', 2
UNION ALL SELECT '5703', 'เชียงของ', 'Chiang Khong', 3
UNION ALL SELECT '5704', 'เทิง', 'Thoeng', 4
UNION ALL SELECT '5705', 'พาน', 'Phan', 5
UNION ALL SELECT '5706', 'ป่าแดด', 'Pa Daet', 6
UNION ALL SELECT '5707', 'แม่จัน', 'Mae Chan', 7
UNION ALL SELECT '5708', 'เชียงแสน', 'Chiang Saen', 8
UNION ALL SELECT '5709', 'แม่สาย', 'Mae Sai', 9
UNION ALL SELECT '5710', 'แม่สรวย', 'Mae Suai', 10
UNION ALL SELECT '5711', 'เวียงป่าเป้า', 'Wiang Pa Pao', 11
UNION ALL SELECT '5712', 'พญาเม็งราย', 'Phaya Mengrai', 12
UNION ALL SELECT '5713', 'เวียงแก่น', 'Wiang Kaen', 13
UNION ALL SELECT '5714', 'ขุนตาล', 'Khun Tan', 14
UNION ALL SELECT '5715', 'แม่ฟ้าหลวง', 'Mae Fa Luang', 15
UNION ALL SELECT '5716', 'แม่ลาว', 'Mae Lao', 16
UNION ALL SELECT '5717', 'เวียงเชียงรุ้ง', 'Wiang Chiang Rung', 17
UNION ALL SELECT '5718', 'ดอยหลวง', 'Doi Luang', 18
UNION ALL SELECT '5801', 'เมืองแม่ฮ่องสอน', 'Mueang Mae Hong Son', 1
UNION ALL SELECT '5802', 'ขุนยวม', 'Khun Yuam', 2
UNION ALL SELECT '5803', 'ปาย', 'Pai', 3
UNION ALL SELECT '5804', 'แม่สะเรียง', 'Mae Sariang', 4
UNION ALL SELECT '5805', 'แม่ลาน้อย', 'Mae La Noi', 5
UNION ALL SELECT '5806', 'สบเมย', 'Sop Moei', 6
UNION ALL SELECT '5807', 'ปางมะผ้า', 'Pang Mapha', 7
UNION ALL SELECT '6001', 'เมืองนครสวรรค์', 'Mueang Nakhon Sawan', 1
UNION ALL SELECT '6002', 'โกรกพระ', 'Krok Phra', 2
UNION ALL SELECT '6003', 'ชุมแสง', 'Chum Saeng', 3
UNION ALL SELECT '6004', 'หนองบัว', 'Nong Bua', 4
UNION ALL SELECT '6005', 'บรรพตพิสัย', 'Banphot Phisai', 5
UNION ALL SELECT '6006', 'เก้าเลี้ยว', 'Kao Liao', 6
UNION ALL SELECT '6007', 'ตาคลี', 'Takhli', 7
UNION ALL SELECT '6008', 'ท่าตะโก', 'Tha Tako', 8
UNION ALL SELECT '6009', 'ไพศาลี', 'Phaisali', 9
UNION ALL SELECT '6010', 'พยุหะคีรี', 'Phayuha Khiri', 10
UNION ALL SELECT '6011', 'ลาดยาว', 'Lat Yao', 11
UNION ALL SELECT '6012', 'ตากฟ้า', 'Tak Fa', 12
UNION ALL SELECT '6013', 'แม่วงก์', 'Mae Wong', 13
UNION ALL SELECT '6014', 'แม่เปิน', 'Mae Poen', 14
UNION ALL SELECT '6015', 'ชุมตาบง', 'Chum Ta Bong', 15
UNION ALL SELECT '6101', 'เมืองอุทัยธานี', 'Mueang Uthai Thani', 1
UNION ALL SELECT '6102', 'ทัพทัน', 'Thap Than', 2
UNION ALL SELECT '6103', 'สว่างอารมณ์', 'Sawang Arom', 3
UNION ALL SELECT '6104', 'หนองฉาง', 'Nong Chang', 4
UNION ALL SELECT '6105', 'หนองขาหย่าง', 'Nong Khayang', 5
UNION ALL SELECT '6106', 'บ้านไร่', 'Ban Rai', 6
UNION ALL SELECT '6107', 'ลานสัก', 'Lan Sak', 7
UNION ALL SELECT '6108', 'ห้วยคต', 'Huai Khot', 8
UNION ALL SELECT '6201', 'เมืองกำแพงเพชร', 'Mueang Kamphaeng Phet', 1
UNION ALL SELECT '6202', 'ไทรงาม', 'Sai Ngam', 2
UNION ALL SELECT '6203', 'คลองลาน', 'Khlong Lan', 3
UNION ALL SELECT '6204', 'ขาณุวรลักษบุรี', 'Khanu Woralaksaburi', 4
UNION ALL SELECT '6205', 'คลองขลุง', 'Khlong Khlung', 5
UNION ALL SELECT '6206', 'พรานกระต่าย', 'Phran Kratai', 6
UNION ALL SELECT '6207', 'ลานกระบือ', 'Lan Krabue', 7
UNION ALL SELECT '6208', 'ทรายทองวัฒนา', 'Sai Thong Watthana', 8
UNION ALL SELECT '6209', 'ปางศิลาทอง', 'Pang Sila Thong', 9
UNION ALL SELECT '6210', 'บึงสามัคคี', 'Bueng Samakkhi', 10
UNION ALL SELECT '6211', 'โกสัมพีนคร', 'Kosamphi Nakhon', 11
UNION ALL SELECT '6301', 'เมืองตาก', 'Mueang Tak', 1
UNION ALL SELECT '6302', 'บ้านตาก', 'Ban Tak', 2
UNION ALL SELECT '6303', 'สามเงา', 'Sam Ngao', 3
UNION ALL SELECT '6304', 'แม่ระมาด', 'Mae Ramat', 4
UNION ALL SELECT '6305', 'ท่าสองยาง', 'Tha Song Yang', 5
UNION ALL SELECT '6306', 'แม่สอด', 'Mae Sot', 6
UNION ALL SELECT '6307', 'พบพระ', 'Phop Phra', 7
UNION ALL SELECT '6308', 'อุ้มผาง', 'Umphang', 8
UNION ALL SELECT '6309', 'วังเจ้า', 'Wang Chao', 9
UNION ALL SELECT '6401', 'เมืองสุโขทัย', 'Mueang Sukhothai', 1
UNION ALL SELECT '6402', 'บ้านด่านลานหอย', 'Ban Dan Lan Hoi', 2
UNION ALL SELECT '6403', 'คีรีมาศ', 'Khiri Mat', 3
UNION ALL SELECT '6404', 'กงไกรลาศ', 'Kong Krailat', 4
UNION ALL SELECT '6405', 'ศรีสัชนาลัย', 'Si Satchanalai', 5
UNION ALL SELECT '6406', 'ศรีสำโรง', 'Si Samrong', 6
UNION ALL SELECT '6407', 'สวรรคโลก', 'Sawankhalok', 7
UNION ALL SELECT '6408', 'ศรีนคร', 'Si Nakhon', 8
UNION ALL SELECT '6409', 'ทุ่งเสลี่ยม', 'Thung Saliam', 9
UNION ALL SELECT '6501', 'เมืองพิษณุโลก', 'Mueang Phitsanulok', 1
UNION ALL SELECT '6502', 'นครไทย', 'Nakhon Thai', 2
UNION ALL SELECT '6503', 'ชาติตระการ', 'Chat Trakan', 3
UNION ALL SELECT '6504', 'บางระกำ', 'Bang Rakam', 4
UNION ALL SELECT '6505', 'บางกระทุ่ม', 'Bang Krathum', 5
UNION ALL SELECT '6506', 'พรหมพิราม', 'Phrom Phiram', 6
UNION ALL SELECT '6507', 'วัดโบสถ์', 'Wat Bot', 7
UNION ALL SELECT '6508', 'วังทอง', 'Wang Thong', 8
UNION ALL SELECT '6509', 'เนินมะปราง', 'Noen Maprang', 9
UNION ALL SELECT '6601', 'เมืองพิจิตร', 'Mueang Phichit', 1
UNION ALL SELECT '6602', 'วังทรายพูน', 'Wang Sai Phun', 2
UNION ALL SELECT '6603', 'โพธิ์ประทับช้าง', 'Pho Prathap Chang', 3
UNION ALL SELECT '6604', 'ตะพานหิน', 'Taphan Hin', 4
UNION ALL SELECT '6605', 'บางมูลนาก', 'Bang Mun Nak', 5
UNION ALL SELECT '6606', 'โพทะเล', 'Pho Thale', 6
UNION ALL SELECT '6607', 'สามง่าม', 'Sam Ngam', 7
UNION ALL SELECT '6608', 'ทับคล้อ', 'Thap Khlo', 8
UNION ALL SELECT '6609', 'สากเหล็ก', 'Sak Lek', 9
UNION ALL SELECT '6610', 'บึงนาราง', 'Bueng Na Rang', 10
UNION ALL SELECT '6611', 'ดงเจริญ', 'Dong Charoen', 11
UNION ALL SELECT '6612', 'วชิรบารมี', 'Wachirabarami', 12
UNION ALL SELECT '6701', 'เมืองเพชรบูรณ์', 'Mueang Phetchabun', 1
UNION ALL SELECT '6702', 'ชนแดน', 'Chon Daen', 2
UNION ALL SELECT '6703', 'หล่มสัก', 'Lom Sak', 3
UNION ALL SELECT '6704', 'หล่มเก่า', 'Lom Kao', 4
UNION ALL SELECT '6705', 'วิเชียรบุรี', 'Wichian Buri', 5
UNION ALL SELECT '6706', 'ศรีเทพ', 'Si Thep', 6
UNION ALL SELECT '6707', 'หนองไผ่', 'Nong Phai', 7
UNION ALL SELECT '6708', 'บึงสามพัน', 'Bueng Sam Phan', 8
UNION ALL SELECT '6709', 'น้ำหนาว', 'Nam Nao', 9
UNION ALL SELECT '6710', 'วังโป่ง', 'Wang Pong', 10
UNION ALL SELECT '6711', 'เขาค้อ', 'Khao Kho', 11
UNION ALL SELECT '7001', 'เมืองราชบุรี', 'Mueang Ratchaburi', 1
UNION ALL SELECT '7002', 'จอมบึง', 'Chom Bueng', 2
UNION ALL SELECT '7003', 'สวนผึ้ง', 'Suan Phueng', 3
UNION ALL SELECT '7004', 'ดำเนินสะดวก', 'Damnoen Saduak', 4
UNION ALL SELECT '7005', 'บ้านโป่ง', 'Ban Pong', 5
UNION ALL SELECT '7006', 'บางแพ', 'Bang Phae', 6
UNION ALL SELECT '7007', 'โพธาราม', 'Photharam', 7
UNION ALL SELECT '7008', 'ปากท่อ', 'Pak Tho', 8
UNION ALL SELECT '7009', 'วัดเพลง', 'Wat Phleng', 9
UNION ALL SELECT '7010', 'บ้านคา', 'Ban Kha', 10
UNION ALL SELECT '7101', 'เมืองกาญจนบุรี', 'Mueang Kanchanaburi', 1
UNION ALL SELECT '7102', 'ไทรโยค', 'Sai Yok', 2
UNION ALL SELECT '7103', 'บ่อพลอย', 'Bo Phloi', 3
UNION ALL SELECT '7104', 'ศรีสวัสดิ์', 'Si Sawat', 4
UNION ALL SELECT '7105', 'ท่ามะกา', 'Tha Maka', 5
UNION ALL SELECT '7106', 'ท่าม่วง', 'Tha Muang', 6
UNION ALL SELECT '7107', 'ทองผาภูมิ', 'Thong Pha Phum', 7
UNION ALL SELECT '7108', 'สังขละบุรี', 'Sangkhla Buri', 8
UNION ALL SELECT '7109', 'พนมทวน', 'Phanom Thuan', 9
UNION ALL SELECT '7110', 'เลาขวัญ', 'Lao Khwan', 10
UNION ALL SELECT '7111', 'ด่านมะขามเตี้ย', 'Dan Makham Tia', 11
UNION ALL SELECT '7112', 'หนองปรือ', 'Nong Prue', 12
UNION ALL SELECT '7113', 'ห้วยกระเจา', 'Huai Krachao', 13
UNION ALL SELECT '7201', 'เมืองสุพรรณบุรี', 'Mueang Suphan Buri', 1
UNION ALL SELECT '7202', 'เดิมบางนางบวช', 'Doem Bang Nang Buat', 2
UNION ALL SELECT '7203', 'ด่านช้าง', 'Dan Chang', 3
UNION ALL SELECT '7204', 'บางปลาม้า', 'Bang Pla Ma', 4
UNION ALL SELECT '7205', 'ศรีประจันต์', 'Si Prachan', 5
UNION ALL SELECT '7206', 'ดอนเจดีย์', 'Don Chedi', 6
UNION ALL SELECT '7207', 'สองพี่น้อง', 'Song Phi Nong', 7
UNION ALL SELECT '7208', 'สามชุก', 'Sam Chuk', 8
UNION ALL SELECT '7209', 'อู่ทอง', 'U Thong', 9
UNION ALL SELECT '7210', 'หนองหญ้าไซ', 'Nong Ya Sai', 10
UNION ALL SELECT '7301', 'เมืองนครปฐม', 'Mueang Nakhon Pathom', 1
UNION ALL SELECT '7302', 'กำแพงแสน', 'Kamphaeng Saen', 2
UNION ALL SELECT '7303', 'นครชัยศรี', 'Nakhon Chai Si', 3
UNION ALL SELECT '7304', 'ดอนตูม', 'Don Tum', 4
UNION ALL SELECT '7305', 'บางเลน', 'Bang Len', 5
UNION ALL SELECT '7306', 'สามพราน', 'Sam Phran', 6
UNION ALL SELECT '7307', 'พุทธมณฑล', 'Phutthamonthon', 7
UNION ALL SELECT '7401', 'เมืองสมุทรสาคร', 'Mueang Samut Sakhon', 1
UNION ALL SELECT '7402', 'กระทุ่มแบน', 'Krathum Baen', 2
UNION ALL SELECT '7403', 'บ้านแพ้ว', 'Ban Phaeo', 3
UNION ALL SELECT '7501', 'เมืองสมุทรสงคราม', 'Mueang Samut Songkhram', 1
UNION ALL SELECT '7502', 'บางคนที', 'Bang Khonthi', 2
UNION ALL SELECT '7503', 'อัมพวา', 'Amphawa', 3
UNION ALL SELECT '7601', 'เมืองเพชรบุรี', 'Mueang Phetchaburi', 1
UNION ALL SELECT '7602', 'เขาย้อย', 'Khao Yoi', 2
UNION ALL SELECT '7603', 'หนองหญ้าปล้อง', 'Nong Ya Plong', 3
UNION ALL SELECT '7604', 'ชะอำ', 'Cha-am', 4
UNION ALL SELECT '7605', 'ท่ายาง', 'Tha Yang', 5
UNION ALL SELECT '7606', 'บ้านลาด', 'Ban Lat', 6
UNION ALL SELECT '7607', 'บ้านแหลม', 'Ban Laem', 7
UNION ALL SELECT '7608', 'แก่งกระจาน', 'Kaeng Krachan', 8
UNION ALL SELECT '7701', 'เมืองประจวบคีรีขันธ์', 'Mueang Prachuap Khiri Khan', 1
UNION ALL SELECT '7702', 'กุยบุรี', 'Kui Buri', 2
UNION ALL SELECT '7703', 'ทับสะแก', 'Thap Sakae', 3
UNION ALL SELECT '7704', 'บางสะพาน', 'Bang Saphan', 4
UNION ALL SELECT '7705', 'บางสะพานน้อย', 'Bang Saphan Noi', 5
UNION ALL SELECT '7706', 'ปราณบุรี', 'Pran Buri', 6
UNION ALL SELECT '7707', 'หัวหิน', 'Hua Hin', 7
UNION ALL SELECT '7708', 'สามร้อยยอด', 'Sam Roi Yot', 8
UNION ALL SELECT '8001', 'เมืองนครศรีธรรมราช', 'Mueang Nakhon Si Thammarat', 1
UNION ALL SELECT '8002', 'พรหมคีรี', 'Phrom Khiri', 2
UNION ALL SELECT '8003', 'ลานสกา', 'Lan Saka', 3
UNION ALL SELECT '8004', 'ฉวาง', 'Chawang', 4
UNION ALL SELECT '8005', 'พิปูน', 'Phipun', 5
UNION ALL SELECT '8006', 'เชียรใหญ่', 'Chian Yai', 6
UNION ALL SELECT '8007', 'ชะอวด', 'Cha-uat', 7
UNION ALL SELECT '8008', 'ท่าศาลา', 'Tha Sala', 8
UNION ALL SELECT '8009', 'ทุ่งสง', 'Thung Song', 9
UNION ALL SELECT '8010', 'นาบอน', 'Na Bon', 10
UNION ALL SELECT '8011', 'ทุ่งใหญ่', 'Thung Yai', 11
UNION ALL SELECT '8012', 'ปากพนัง', 'Pak Phanang', 12
UNION ALL SELECT '8013', 'ร่อนพิบูลย์', 'Ron Phibun', 13
UNION ALL SELECT '8014', 'สิชล', 'Sichon', 14
UNION ALL SELECT '8015', 'ขนอม', 'Khanom', 15
UNION ALL SELECT '8016', 'หัวไทร', 'Hua Sai', 16
UNION ALL SELECT '8017', 'บางขัน', 'Bang Khan', 17
UNION ALL SELECT '8018', 'ถ้ำพรรณรา', 'Tham Phannara', 18
UNION ALL SELECT '8019', 'จุฬาภรณ์', 'Chulabhorn', 19
UNION ALL SELECT '8020', 'พระพรหม', 'Phra Phrom', 20
UNION ALL SELECT '8021', 'นบพิตำ', 'Nopphitam', 21
UNION ALL SELECT '8022', 'ช้างกลาง', 'Chang Klang', 22
UNION ALL SELECT '8023', 'เฉลิมพระเกียรติ', 'Chaloem Phra Kiat', 23
UNION ALL SELECT '8101', 'เมืองกระบี่', 'Mueang Krabi', 1
UNION ALL SELECT '8102', 'เขาพนม', 'Khao Phanom', 2
UNION ALL SELECT '8103', 'เกาะลันตา', 'Ko Lanta', 3
UNION ALL SELECT '8104', 'คลองท่อม', 'Khlong Thom', 4
UNION ALL SELECT '8105', 'อ่าวลึก', 'Ao Luek', 5
UNION ALL SELECT '8106', 'ปลายพระยา', 'Plai Phraya', 6
UNION ALL SELECT '8107', 'ลำทับ', 'Lam Thap', 7
UNION ALL SELECT '8108', 'เหนือคลอง', 'Nuea Khlong', 8
UNION ALL SELECT '8201', 'เมืองพังงา', 'Mueang Phang-nga', 1
UNION ALL SELECT '8202', 'เกาะยาว', 'Ko Yao', 2
UNION ALL SELECT '8203', 'กะปง', 'Kapong', 3
UNION ALL SELECT '8204', 'ตะกั่วทุ่ง', 'Takua Thung', 4
UNION ALL SELECT '8205', 'ตะกั่วป่า', 'Takua Pa', 5
UNION ALL SELECT '8206', 'คุระบุรี', 'Khura Buri', 6
UNION ALL SELECT '8207', 'ทับปุด', 'Thap Put', 7
UNION ALL SELECT '8208', 'ท้ายเหมือง', 'Thai Mueang', 8
UNION ALL SELECT '8301', 'เมืองภูเก็ต', 'Mueang Phuket', 1
UNION ALL SELECT '8302', 'กะทู้', 'Kathu', 2
UNION ALL SELECT '8303', 'ถลาง', 'Thalang', 3
UNION ALL SELECT '8401', 'เมืองสุราษฎร์ธานี', 'Mueang Surat Thani', 1
UNION ALL SELECT '8402', 'กาญจนดิษฐ์', 'Kanchanadit', 2
UNION ALL SELECT '8403', 'ดอนสัก', 'Don Sak', 3
UNION ALL SELECT '8404', 'เกาะสมุย', 'Ko Samui', 4
UNION ALL SELECT '8405', 'เกาะพะงัน', 'Ko Pha-ngan', 5
UNION ALL SELECT '8406', 'ไชยา', 'Chaiya', 6
UNION ALL SELECT '8407', 'ท่าชนะ', 'Tha Chana', 7
UNION ALL SELECT '8408', 'คีรีรัฐนิคม', 'Khiri Rat Nikhom', 8
UNION ALL SELECT '8409', 'บ้านตาขุน', 'Ban Ta Khun', 9
UNION ALL SELECT '8410', 'พนม', 'Phanom', 10
UNION ALL SELECT '8411', 'ท่าฉาง', 'Tha Chang', 11
UNION ALL SELECT '8412', 'บ้านนาสาร', 'Ban Na San', 12
UNION ALL SELECT '8413', 'บ้านนาเดิม', 'Ban Na Doem', 13
UNION ALL SELECT '8414', 'เคียนซา', 'Khian Sa', 14
UNION ALL SELECT '8415', 'เวียงสระ', 'Wiang Sa', 15
UNION ALL SELECT '8416', 'พระแสง', 'Phrasaeng', 16
UNION ALL SELECT '8417', 'พุนพิน', 'Phunphin', 17
UNION ALL SELECT '8418', 'ชัยบุรี', 'Chai Buri', 18
UNION ALL SELECT '8419', 'วิภาวดี', 'Vibhavadi', 19
UNION ALL SELECT '8501', 'เมืองระนอง', 'Mueang Ranong', 1
UNION ALL SELECT '8502', 'ละอุ่น', 'La-un', 2
UNION ALL SELECT '8503', 'กะเปอร์', 'Kapoe', 3
UNION ALL SELECT '8504', 'กระบุรี', 'Kra Buri', 4
UNION ALL SELECT '8505', 'สุขสำราญ', 'Suk Samran', 5
UNION ALL SELECT '8601', 'เมืองชุมพร', 'Mueang Chumphon', 1
UNION ALL SELECT '8602', 'ท่าแซะ', 'Tha Sae', 2
UNION ALL SELECT '8603', 'ปะทิว', 'Pathio', 3
UNION ALL SELECT '8604', 'หลังสวน', 'Lang Suan', 4
UNION ALL SELECT '8605', 'ละแม', 'Lamae', 5
UNION ALL SELECT '8606', 'พะโต๊ะ', 'Phato', 6
UNION ALL SELECT '8607', 'สวี', 'Sawi', 7
UNION ALL SELECT '8608', 'ทุ่งตะโก', 'Thung Tako', 8
UNION ALL SELECT '9001', 'เมืองสงขลา', 'Mueang Songkhla', 1
UNION ALL SELECT '9002', 'สทิงพระ', 'Sathing Phra', 2
UNION ALL SELECT '9003', 'จะนะ', 'Chana', 3
UNION ALL SELECT '9004', 'นาทวี', 'Na Thawi', 4
UNION ALL SELECT '9005', 'เทพา', 'Thepha', 5
UNION ALL SELECT '9006', 'สะบ้าย้อย', 'Saba Yoi', 6
UNION ALL SELECT '9007', 'ระโนด', 'Ranot', 7
UNION ALL SELECT '9008', 'กระแสสินธุ์', 'Krasae Sin', 8
UNION ALL SELECT '9009', 'รัตภูมิ', 'Rattaphum', 9
UNION ALL SELECT '9010', 'สะเดา', 'Sadao', 10
UNION ALL SELECT '9011', 'หาดใหญ่', 'Hat Yai', 11
UNION ALL SELECT '9012', 'นาหม่อม', 'Na Mom', 12
UNION ALL SELECT '9013', 'ควนเนียง', 'Khuan Niang', 13
UNION ALL SELECT '9014', 'บางกล่ำ', 'Bang Klam', 14
UNION ALL SELECT '9015', 'สิงหนคร', 'Singhanakhon', 15
UNION ALL SELECT '9016', 'คลองหอยโข่ง', 'Khlong Hoi Khong', 16
UNION ALL SELECT '9101', 'เมืองสตูล', 'Mueang Satun', 1
UNION ALL SELECT '9102', 'ควนโดน', 'Khuan Don', 2
UNION ALL SELECT '9103', 'ควนกาหลง', 'Khuan Kalong', 3
UNION ALL SELECT '9104', 'ท่าแพ', 'Tha Phae', 4
UNION ALL SELECT '9105', 'ละงู', 'La-ngu', 5
UNION ALL SELECT '9106', 'ทุ่งหว้า', 'Thung Wa', 6
UNION ALL SELECT '9107', 'มะนัง', 'Manang', 7
UNION ALL SELECT '9201', 'เมืองตรัง', 'Mueang Trang', 1
UNION ALL SELECT '9202', 'กันตัง', 'Kantang', 2
UNION ALL SELECT '9203', 'ย่านตาขาว', 'Yan Ta Khao', 3
UNION ALL SELECT '9204', 'ปะเหลียน', 'Palian', 4
UNION ALL SELECT '9205', 'สิเกา', 'Sikao', 5
UNION ALL SELECT '9206', 'ห้วยยอด', 'Huai Yot', 6
UNION ALL SELECT '9207', 'วังวิเศษ', 'Wang Wiset', 7
UNION ALL SELECT '9208', 'นาโยง', 'Na Yong', 8
UNION ALL SELECT '9209', 'รัษฎา', 'Ratsada', 9
UNION ALL SELECT '9210', 'หาดสำราญ', 'Hat Samran', 10
UNION ALL SELECT '9301', 'เมืองพัทลุง', 'Mueang Phatthalung', 1
UNION ALL SELECT '9302', 'กงหรา', 'Kong Ra', 2
UNION ALL SELECT '9303', 'เขาชัยสน', 'Khao Chaison', 3
UNION ALL SELECT '9304', 'ตะโหมด', 'Tamot', 4
UNION ALL SELECT '9305', 'ควนขนุน', 'Khuan Khanun', 5
UNION ALL SELECT '9306', 'ปากพะยูน', 'Pak Phayun', 6
UNION ALL SELECT '9307', 'ศรีบรรพต', 'Si Banphot', 7
UNION ALL SELECT '9308', 'ป่าบอน', 'Pa Bon', 8
UNION ALL SELECT '9309', 'บางแก้ว', 'Bang Kaeo', 9
UNION ALL SELECT '9310', 'ป่าพะยอม', 'Pa Phayom', 10
UNION ALL SELECT '9311', 'ศรีนครินทร์', 'Srinagarindra', 11
UNION ALL SELECT '9401', 'เมืองปัตตานี', 'Mueang Pattani', 1
UNION ALL SELECT '9402', 'โคกโพธิ์', 'Khok Pho', 2
UNION ALL SELECT '9403', 'หนองจิก', 'Nong Chik', 3
UNION ALL SELECT '9404', 'ปะนาเระ', 'Panare', 4
UNION ALL SELECT '9405', 'มายอ', 'Mayo', 5
UNION ALL SELECT '9406', 'ทุ่งยางแดง', 'Thung Yang Daeng', 6
UNION ALL SELECT '9407', 'สายบุรี', 'Sai Buri', 7
UNION ALL SELECT '9408', 'ไม้แก่น', 'Mai Kaen', 8
UNION ALL SELECT '9409', 'ยะหริ่ง', 'Yaring', 9
UNION ALL SELECT '9410', 'ยะรัง', 'Yarang', 10
UNION ALL SELECT '9411', 'กะพ้อ', 'Kapho', 11
UNION ALL SELECT '9412', 'แม่ลาน', 'Mae Lan', 12
UNION ALL SELECT '9501', 'เมืองยะลา', 'Mueang Yala', 1
UNION ALL SELECT '9502', 'เบตง', 'Betong', 2
UNION ALL SELECT '9503', 'บันนังสตา', 'Bannang Sata', 3
UNION ALL SELECT '9504', 'ธารโต', 'Than To', 4
UNION ALL SELECT '9505', 'ยะหา', 'Yaha', 5
UNION ALL SELECT '9506', 'รามัน', 'Raman', 6
UNION ALL SELECT '9507', 'กาบัง', 'Kabang', 7
UNION ALL SELECT '9508', 'กรงปินัง', 'Krong Pinang', 8
UNION ALL SELECT '9601', 'เมืองนราธิวาส', 'Mueang Narathiwat', 1
UNION ALL SELECT '9602', 'ตากใบ', 'Tak Bai', 2
UNION ALL SELECT '9603', 'บาเจาะ', 'Bacho', 3
UNION ALL SELECT '9604', 'ยี่งอ', 'Yi-ngo', 4
UNION ALL SELECT '9605', 'ระแงะ', 'Ra-ngae', 5
UNION ALL SELECT '9606', 'รือเสาะ', 'Rueso', 6
UNION ALL SELECT '9607', 'ศรีสาคร', 'Si Sakhon', 7
UNION ALL SELECT '9608', 'แว้ง', 'Waeng', 8
UNION ALL SELECT '9609', 'สุคิริน', 'Sukhirin', 9
UNION ALL SELECT '9610', 'สุไหงโก-ลก', 'Su-ngai Kolok', 10
UNION ALL SELECT '9611', 'สุไหงปาดี', 'Su-ngai Padi', 11
UNION ALL SELECT '9612', 'จะแนะ', 'Chanae', 12
UNION ALL SELECT '9613', 'เจาะไอร้อง', 'Cho-airong', 13
) v
JOIN provinces p ON p.code = LEFT(v.code, 2);

-- --- Link the 5 สาขา offices to their district -----------------------------
-- Without this the table has no consumer and would be dead on arrival, and the
-- geocode migration 073 embeds in organizations.code would still reference
-- nothing. Nullable: only L5 branch offices sit at district level.
-- The FK is what makes it real — a mistyped branch geocode now fails loudly
-- instead of silently pointing at no district.
-- Guarded by information_schema so a re-run is a no-op. No stored procedure, so
-- this stays runnable outside the mysql CLI.
SET @has_col = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'organizations'
    AND COLUMN_NAME = 'district_code'
);
SET @ddl = IF(@has_col = 0,
  'ALTER TABLE organizations
     ADD COLUMN district_code VARCHAR(10) NULL
       COMMENT ''รหัสอำเภอ 4 หลัก (เฉพาะสำนักงานสาขา L5)'' AFTER province_code,
     ADD KEY idx_org_district (district_code),
     ADD CONSTRAINT fk_organizations_district FOREIGN KEY (district_code)
       REFERENCES districts (code) ON DELETE RESTRICT ON UPDATE CASCADE',
  'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

-- Back-fill from the geocode already embedded in the office code by 073.
UPDATE organizations
SET district_code = SUBSTRING(code, 4)
WHERE level = 5 AND code REGEXP '^JP-[0-9]{4}$' AND district_code IS NULL;

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'districts seeded'      AS check_name, COUNT(*) AS actual, 928 AS expected FROM districts
UNION ALL
SELECT 'districts w/o province', COUNT(*), 0 FROM districts d
  LEFT JOIN provinces p ON p.id = d.province_id WHERE p.id IS NULL
UNION ALL
SELECT 'code prefix mismatch',   COUNT(*), 0 FROM districts d
  JOIN provinces p ON p.id = d.province_id WHERE LEFT(d.code, 2) <> p.code
UNION ALL
SELECT 'branch offices linked',  COUNT(*), 5 FROM organizations o
  JOIN districts d ON d.code = o.district_code WHERE o.level = 5;
