-- ============================================================================
-- 090_seed_allowance_types.sql
-- Phase 9 (งบบุคลากร) — seed แคตตาล็อกเงินเพิ่มจากค่าที่ตัดสินแล้วในเอกสารออกแบบ
--
-- ที่มา: PRPs/2026-08-09_personnel-budget-schema-design.md
--   "ค่าตั้งต้นที่ผมตัดสินแทน — เปลี่ยนได้ด้วย 1 UPDATE" (§ค่าตั้งต้น)
--   + mapping expense_item_id จากหัวข้อ "สิ่งที่ฐานข้อมูลจริงมีอยู่แล้ว"
--
-- ตารางนี้คือ "ข้อมูล ไม่ใช่โค้ด" — เกณฑ์จริงต่างจากนี้แก้ด้วย UPDATE ได้เลย
-- อัตรา (allowance_rates) รายระดับรอข้อมูลจริงจาก HR — ไม่ seed ในไฟล์นี้
--
-- Idempotent: INSERT IGNORE บน uk_allowance_types_code · ผูก expense_item_id
-- ด้วย expense_items.id ตรงๆ (id เสถียรในทุกสภาพแวดล้อม — เป็น PK ที่ seed
-- มาพร้อมระบบ ตรวจสอบแล้วใน hr_budget จริง 2026-08-15)
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

INSERT IGNORE INTO allowance_types
  (code, name_th, short_name, expense_item_id, scope, vacant_eligible,
   report_scope, basis, rate_kind, budget_basis, legal_ref)
VALUES
  -- เงินประจำตำแหน่ง — ผูกตำแหน่ง อัตราตามระดับ/สายงาน (รอ rates จาก HR)
  ('POSITION_ALLOWANCE', 'เงินประจำตำแหน่ง', 'เงินประจำตำแหน่ง', 5,
   'position', 1, 'personnel', 'by_level', 'exact', 'establishment', NULL),

  -- ค่าตอบแทนนอกเหนือจากเงินเดือน — derived: เท่ากับเงินประจำตำแหน่งในตำแหน่งเดียวกัน
  -- (คำว่า "เท่ากับ" อยู่ในชื่อทางการของ expense_item id 12 แล้ว)
  ('KHN', 'ค่าตอบแทนนอกเหนือจากเงินเดือน', 'ค.ต.น.', 12,
   'position', 1, 'personnel', 'derived', 'exact', 'establishment', NULL),

  -- 4 ตัวที่ผู้ใช้ตัดสิน vacant_eligible ไว้ 2026-08-09 (เกณฑ์ = สิทธิ์ตัดสินจากอะไร)
  ('PTK', 'เงิน พ.ต.ก. (นิติกร)', 'พ.ต.ก.', 18,
   'position', 0, 'personnel', 'flat', 'exact', 'establishment',
   'ต้องผ่านการประเมินรายบุคคล — อัตราว่างไม่นับ'),

  ('PPD', 'เงิน พ.พ.ด. (พัสดุ)', 'พ.พ.ด.', 19,
   'position', 0, 'personnel', 'flat', 'exact', 'establishment',
   'ต้องได้รับแต่งตั้งเป็นเจ้าหน้าที่พัสดุรายบุคคล — อัตราว่างไม่นับ'),

  ('PSR', 'เงิน พ.ส.ร. (การสู้รบ)', 'พ.ส.ร.', 20,
   'personal', 0, 'personnel', 'flat', 'exact', 'manual',
   'สิทธิ์ติดตัวบุคคล (ทหารผ่านศึก) — อนุมานจากอัตรากำลังไม่ได้'),

  ('SPP', 'เงิน สปพ. (พื้นที่พิเศษ)', 'สปพ.', 21,
   'position', 1, 'personnel', 'flat', 'exact', 'establishment',
   'ตัดสินจากที่ตั้งหน่วยงาน — อัตราว่างในพื้นที่นั้นเข้าเกณฑ์'),

  -- ค่าเช่าบ้าน — งบดำเนินงาน (expense_type=2) แต่รายงานรวมกับงบบุคลากร
  -- (แกนบริหาร) · ตั้งงบจาก snapshot ผู้รับจริง ไม่ใช่อัตรากำลัง
  ('HOUSE_RENT', 'ค่าเช่าบ้าน', 'ค่าเช่าบ้าน', 35,
   'personal', 0, 'personnel,operating', 'flat', 'ceiling', 'actuals',
   'จ่ายตามจริงไม่เกินเพดานตามระดับ — ห้ามบวกเพดานเป็นงบ');

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'allowance_types seeded' AS check_name,
       COUNT(*) AS actual, 7 AS expected
FROM allowance_types
UNION ALL
SELECT 'bridged to expense_items', COUNT(*), 7
FROM allowance_types
WHERE expense_item_id IN (5,12,18,19,20,21,35)
UNION ALL
SELECT 'vacant_eligible types', COUNT(*), 3
FROM allowance_types WHERE vacant_eligible = 1;
