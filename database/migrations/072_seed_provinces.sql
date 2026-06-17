-- ============================================================================
-- 072_seed_provinces.sql
-- Phase 8 — populate the `provinces` reference table with all 77 provinces
-- (76 + Bangkok) using the STANDARD 2-digit Thai geocode as `code`, so that
-- organizations.province_code (set to geocodes in Phase 6, migration 071) can
-- JOIN to provinces.code, and provinces.region carries the GEOGRAPHIC region
-- (central/north/northeast/east/west/south — distinct from organizations.region
-- which is an admin classification).
--
-- Region grouping follows the ราชบัณฑิตยสภา / NGC 6-region standard and matches
-- the PROV-RGN-* org grouping seeded in Phase 6.
-- Idempotent: provinces.code is UNIQUE → INSERT IGNORE de-dupes; the Bangkok
-- UPDATE re-applies the same values on re-run.
-- ============================================================================

-- Re-code the existing Bangkok placeholder (random 'PR-f9f6') to geocode '10'.
UPDATE provinces
SET code = '10', name_en = 'Bangkok', region = 'central', sort_order = 10, is_active = 1
WHERE name_th = 'กรุงเทพมหานคร';

-- The remaining 76 provinces.
INSERT IGNORE INTO provinces (code, name_th, name_en, region, sort_order, is_active) VALUES
('11','สมุทรปราการ','Samut Prakan','central',11,1),
('12','นนทบุรี','Nonthaburi','central',12,1),
('13','ปทุมธานี','Pathum Thani','central',13,1),
('14','พระนครศรีอยุธยา','Phra Nakhon Si Ayutthaya','central',14,1),
('15','อ่างทอง','Ang Thong','central',15,1),
('16','ลพบุรี','Lopburi','central',16,1),
('17','สิงห์บุรี','Sing Buri','central',17,1),
('18','ชัยนาท','Chai Nat','central',18,1),
('19','สระบุรี','Saraburi','central',19,1),
('20','ชลบุรี','Chonburi','east',20,1),
('21','ระยอง','Rayong','east',21,1),
('22','จันทบุรี','Chanthaburi','east',22,1),
('23','ตราด','Trat','east',23,1),
('24','ฉะเชิงเทรา','Chachoengsao','east',24,1),
('25','ปราจีนบุรี','Prachinburi','east',25,1),
('26','นครนายก','Nakhon Nayok','central',26,1),
('27','สระแก้ว','Sa Kaeo','east',27,1),
('30','นครราชสีมา','Nakhon Ratchasima','northeast',30,1),
('31','บุรีรัมย์','Buriram','northeast',31,1),
('32','สุรินทร์','Surin','northeast',32,1),
('33','ศรีสะเกษ','Sisaket','northeast',33,1),
('34','อุบลราชธานี','Ubon Ratchathani','northeast',34,1),
('35','ยโสธร','Yasothon','northeast',35,1),
('36','ชัยภูมิ','Chaiyaphum','northeast',36,1),
('37','อำนาจเจริญ','Amnat Charoen','northeast',37,1),
('38','บึงกาฬ','Bueng Kan','northeast',38,1),
('39','หนองบัวลำภู','Nong Bua Lam Phu','northeast',39,1),
('40','ขอนแก่น','Khon Kaen','northeast',40,1),
('41','อุดรธานี','Udon Thani','northeast',41,1),
('42','เลย','Loei','northeast',42,1),
('43','หนองคาย','Nong Khai','northeast',43,1),
('44','มหาสารคาม','Maha Sarakham','northeast',44,1),
('45','ร้อยเอ็ด','Roi Et','northeast',45,1),
('46','กาฬสินธุ์','Kalasin','northeast',46,1),
('47','สกลนคร','Sakon Nakhon','northeast',47,1),
('48','นครพนม','Nakhon Phanom','northeast',48,1),
('49','มุกดาหาร','Mukdahan','northeast',49,1),
('50','เชียงใหม่','Chiang Mai','north',50,1),
('51','ลำพูน','Lamphun','north',51,1),
('52','ลำปาง','Lampang','north',52,1),
('53','อุตรดิตถ์','Uttaradit','north',53,1),
('54','แพร่','Phrae','north',54,1),
('55','น่าน','Nan','north',55,1),
('56','พะเยา','Phayao','north',56,1),
('57','เชียงราย','Chiang Rai','north',57,1),
('58','แม่ฮ่องสอน','Mae Hong Son','north',58,1),
('60','นครสวรรค์','Nakhon Sawan','central',60,1),
('61','อุทัยธานี','Uthai Thani','central',61,1),
('62','กำแพงเพชร','Kamphaeng Phet','central',62,1),
('63','ตาก','Tak','west',63,1),
('64','สุโขทัย','Sukhothai','central',64,1),
('65','พิษณุโลก','Phitsanulok','central',65,1),
('66','พิจิตร','Phichit','central',66,1),
('67','เพชรบูรณ์','Phetchabun','central',67,1),
('70','ราชบุรี','Ratchaburi','west',70,1),
('71','กาญจนบุรี','Kanchanaburi','west',71,1),
('72','สุพรรณบุรี','Suphan Buri','central',72,1),
('73','นครปฐม','Nakhon Pathom','central',73,1),
('74','สมุทรสาคร','Samut Sakhon','central',74,1),
('75','สมุทรสงคราม','Samut Songkhram','central',75,1),
('76','เพชรบุรี','Phetchaburi','west',76,1),
('77','ประจวบคีรีขันธ์','Prachuap Khiri Khan','west',77,1),
('80','นครศรีธรรมราช','Nakhon Si Thammarat','south',80,1),
('81','กระบี่','Krabi','south',81,1),
('82','พังงา','Phang Nga','south',82,1),
('83','ภูเก็ต','Phuket','south',83,1),
('84','สุราษฎร์ธานี','Surat Thani','south',84,1),
('85','ระนอง','Ranong','south',85,1),
('86','ชุมพร','Chumphon','south',86,1),
('90','สงขลา','Songkhla','south',90,1),
('91','สตูล','Satun','south',91,1),
('92','ตรัง','Trang','south',92,1),
('93','พัทลุง','Phatthalung','south',93,1),
('94','ปัตตานี','Pattani','south',94,1),
('95','ยะลา','Yala','south',95,1),
('96','นราธิวาส','Narathiwat','south',96,1);
