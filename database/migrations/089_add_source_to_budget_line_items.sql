-- ============================================================================
-- 089_add_source_to_budget_line_items.sql
-- Phase 9 (งบบุคลากร) — P5 สะพานเข้าโครงเดิม (คอลัมน์เดียวที่แตะตารางเดิม)
--
-- positions *ผลิต* budget_line_items ไม่ใช่ *แทนที่*:
--   อัตรากำลัง (076–088) → คำนวณ → group by (expense_item_id, organization, fy)
--   → เขียนแถว source='computed' ลง budget_line_items (โครงสร้างเดิมไม่แก้)
--
-- ค่า default 'manual' โดยตั้งใจ:
--   - แถวเดิมทุกแถวคือพิมพ์มือ ไม่ต้อง migrate ข้อมูล
--   - หน่วยที่ยังไม่พร้อมอัตรากำลัง ยังพิมพ์มือต่อได้
--   - รัน computed คู่กับ manual ปีแรก เทียบผลต่างก่อนตัดสวิตช์
--
-- ถ้า source='computed' → UI คลิกดูที่มาถึงรายตำแหน่งได้
-- ห้ามให้ positions เป็นแหล่งความจริงของยอดงบโดยตรง — ยอดที่อนุมัติแล้วต้องนิ่ง
--
-- Guarded by information_schema — idempotent, safe to run twice
-- (ท่าเดียวกับ rollback ของ 075 เพราะ MySQL ไม่มี ADD COLUMN IF NOT EXISTS)
--
-- ออกแบบเต็ม: PRPs/2026-08-09_personnel-budget-schema-design.md §สะพานเข้าโครงเดิม
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

SET @has_col = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'budget_line_items'
    AND COLUMN_NAME = 'source'
);
SET @ddl = IF(@has_col = 0,
  'ALTER TABLE budget_line_items ADD COLUMN source ENUM(''manual'',''computed'') NOT NULL DEFAULT ''manual'' COMMENT ''manual=พิมพ์มือ · computed=คำนวณจากอัตรากำลัง (positions)'' AFTER expense_item_id',
  'DO 0');
PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'bli.source column exists' AS check_name,
       COUNT(*) AS actual, 1 AS expected
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'budget_line_items'
  AND COLUMN_NAME = 'source'
UNION ALL
SELECT 'bli rows still manual (default untouched)',
       COUNT(*), 0
FROM budget_line_items
WHERE source = 'computed';
