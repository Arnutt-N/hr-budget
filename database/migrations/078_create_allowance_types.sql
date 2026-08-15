-- ============================================================================
-- 078_create_allowance_types.sql
-- Phase 9 (งบบุคลากร) — ตารางที่ 3/13
-- `allowance_types` = แคตตาล็อกเงินเพิ่ม (พ.ต.ก. · พ.พ.ด. · พ.ส.ร. · สปพ. ·
-- เงินประจำตำแหน่ง · ค่าตอบแทนนอกเหนือจากเงินเดือน · ค่าเช่าบ้าน ฯลฯ)
--
-- คอลัมน์เฉพาะที่ตั้งใจ (ทั้งหมดคือ "ข้อมูล ไม่ใช่โค้ด" — แก้ด้วย UPDATE ได้):
--   expense_item_id  สะพานเข้าโครงงบเดิม (เงินประจำตำแหน่ง→5 · ค.ต.น.→12 ·
--                    พ.ต.ก.→18 · พ.พ.ด.→19 · พ.ส.ร.→20 · สปพ.→21 · ค่าเช่าบ้าน→35)
--                    ห้ามผูก budget_categories — คนละสายพันธุ์ (สายฟอร์มคำขอ)
--   scope            ข้อเท็จจริง: สิทธิ์ตัดสินจากตำแหน่งหรือสถานการณ์ส่วนตัว
--   vacant_eligible  นโยบาย: อัตราว่างนับไหม — แยกจาก scope (พ.ต.ก. ชนกันพอดี:
--                    scope=position แต่ต้องผ่านการประเมินรายบุคคล ⇒ 0)
--   report_scope     แกนบริหาร — ค่าเช่าบ้านอยู่งบดำเนินงาน (expense_type=2)
--                    แต่รายงานรวมกับงบบุคลากร ห้ามใช้ค่านี้ลงเอกสารราชการ
--   basis            flat · percent_of_salary · by_level · derived
--                    (derived = อ้างอิงเงินเพิ่มตัวอื่น เช่น ค.ต.น. = เงินประจำตำแหน่ง)
--   rate_kind        exact = จำนวนที่จ่ายแน่นอน · ceiling = เพดาน (ค่าเช่าบ้าน)
--                    ห้ามบวกเพดานเป็นงบ — จะสูงเกินจริง
--   budget_basis     establishment=จากอัตรากำลัง · actuals=snapshot ผู้รับ · manual=กรอกก้อน
--
-- Seed อัตราจริงรอผู้ใช้ (P4) — ตารางนี้สร้างโครงก่อน ไม่ seed ใน migration นี้
--
-- ออกแบบเต็ม: PRPs/2026-08-09_personnel-budget-schema-design.md
-- Idempotent: CREATE TABLE IF NOT EXISTS.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS allowance_types (
  id                INT NOT NULL AUTO_INCREMENT,
  code              VARCHAR(50) NOT NULL COMMENT 'รหัส เช่น POSITION_ALLOWANCE, KHN, HOUSE_RENT',
  name_th           VARCHAR(255) NOT NULL COMMENT 'ชื่อเต็มภาษาไทย',
  short_name        VARCHAR(100) DEFAULT NULL COMMENT 'ชื่อย่อ เช่น พ.ต.ก.',
  expense_item_id   INT DEFAULT NULL COMMENT 'FK: expense_items.id — สะพานเข้าโครงงบเดิม',
  scope             ENUM('position','personal') NOT NULL DEFAULT 'position'
                    COMMENT 'ข้อเท็จจริง: สิทธิ์ผูกกับตำแหน่ง หรือสถานการณ์ส่วนตัว',
  vacant_eligible   TINYINT(1) NOT NULL DEFAULT 0
                    COMMENT 'นโยบาย: อัตราว่างนับเงินเพิ่มนี้ไหม — คนละเรื่องกับ scope',
  report_scope      SET('personnel','operating') NOT NULL DEFAULT 'personnel'
                    COMMENT 'แกนบริหาร: รายงานรวมกับก้อนไหน — ห้ามใช้ลงเอกสารราชการ',
  basis             ENUM('flat','percent_of_salary','by_level','derived') NOT NULL DEFAULT 'flat'
                    COMMENT 'รูปแบบการคำนวณ — derived = อ้างอิง type อื่น (ค.ต.น.)',
  rate_kind         ENUM('exact','ceiling') NOT NULL DEFAULT 'exact'
                    COMMENT 'exact=จ่ายแน่นอน · ceiling=เพดานตรวจสอบ (ห้ามบวกเป็นงบ)',
  budget_basis      ENUM('establishment','actuals','manual') NOT NULL DEFAULT 'establishment'
                    COMMENT 'ตั้งงบจากอัตรากำลัง · snapshot ผู้รับ · ผู้ใช้กรอกก้อน',
  legal_ref         VARCHAR(500) DEFAULT NULL COMMENT 'ระเบียบ/ประกาศที่ให้อำนาจ',
  is_active         TINYINT(1) DEFAULT 1,
  deleted_at        TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
  created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by        INT DEFAULT NULL COMMENT 'ผู้สร้าง',
  updated_by        INT DEFAULT NULL COMMENT 'ผู้แก้ไขล่าสุด',
  PRIMARY KEY (id),
  UNIQUE KEY uk_allowance_types_code (code),
  KEY idx_allowance_types_expense_item (expense_item_id),
  KEY idx_allowance_types_scope (scope),
  KEY idx_allowance_types_deleted (deleted_at),
  CONSTRAINT fk_allowance_types_expense_item FOREIGN KEY (expense_item_id)
    REFERENCES expense_items (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='แคตตาล็อกเงินเพิ่ม — ทุกเกณฑ์เป็นข้อมูล แก้ด้วย UPDATE ไม่ใช่แก้โค้ด';

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'allowance_types exists' AS check_name,
       COUNT(*) AS actual, 1 AS expected
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'allowance_types';
