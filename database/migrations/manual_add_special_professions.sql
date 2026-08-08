-- SQL to add new sub-items for Special Professions

-- 1. Add "นักวิชาการคอมพิวเตอร์"
INSERT INTO budget_category_items (category_id, name, parent_id, level, is_active, sort_order, created_at, updated_at)
SELECT 
    category_id, 
    'ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งนักวิชาการคอมพิวเตอร์', 
    id as parent_id, 
    level + 1, 
    1, 
    1, 
    NOW(), 
    NOW()
FROM budget_category_items 
WHERE name LIKE '%ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ (วช) /เชี่ยวชาญเฉพาะ (ชช.)%'
AND NOT EXISTS (
    SELECT 1 FROM budget_category_items AS sub 
    WHERE sub.name = 'ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งนักวิชาการคอมพิวเตอร์'
    AND sub.parent_id = budget_category_items.id
);

-- 2. Add "วิศวกร/สถาปนิก"
INSERT INTO budget_category_items (category_id, name, parent_id, level, is_active, sort_order, created_at, updated_at)
SELECT 
    category_id, 
    'ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งวิศวกร/สถาปนิก', 
    id as parent_id, 
    level + 1, 
    1, 
    2, 
    NOW(), 
    NOW()
FROM budget_category_items 
WHERE name LIKE '%ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ (วช) /เชี่ยวชาญเฉพาะ (ชช.)%'
AND NOT EXISTS (
    SELECT 1 FROM budget_category_items AS sub 
    WHERE sub.name = 'ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งวิศวกร/สถาปนิก'
    AND sub.parent_id = budget_category_items.id
);
