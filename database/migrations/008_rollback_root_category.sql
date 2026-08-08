-- ==============================================================================
-- Rollback Script for Migration 008
-- Description: ย้อนกลับการเพิ่ม root category กลับสู่โครงสร้างเดิม
-- ==============================================================================

-- Step 1: Get root category ID
SET @root_id = (SELECT id FROM budget_categories WHERE code = 'GOVT_PERSONNEL_EXP');

-- Step 2: Remove parent_id from top-level categories
UPDATE budget_categories 
SET parent_id = NULL 
WHERE parent_id = @root_id;

-- Step 3: Restore original levels (ลดลงทีละ 1)
-- ต้องทำจากบนลงล่างเพื่อไม่ให้เกิด conflict

-- Level 5 -> 4
UPDATE budget_categories SET level = 4 WHERE level = 5;

-- Level 4 -> 3
UPDATE budget_categories SET level = 3 WHERE level = 4;

-- Level 3 -> 2
UPDATE budget_categories SET level = 2 WHERE level = 3;

-- Level 2 -> 1
UPDATE budget_categories SET level = 1 WHERE level = 2;

-- Level 1 -> 0 (งบดำเนินงาน)
UPDATE budget_categories SET level = 0 WHERE level = 1 AND code = 'OPERATIONS';

-- Step 4: Delete root category
DELETE FROM budget_categories WHERE code = 'GOVT_PERSONNEL_EXP';

-- Step 5: Verify rollback
SELECT 
    id,
    code,
    name_th,
    parent_id,
    level
FROM budget_categories 
ORDER BY level ASC, sort_order ASC
LIMIT 30;
