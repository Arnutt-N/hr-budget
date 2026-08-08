-- ==============================================================================
-- Migration: Add Root Category "รายการค่าใช้จ่ายบุคลากรภาครัฐ"
-- Version: 008
-- Description: เพิ่มหมวดหมู่ระดับบนสุด และปรับโครงสร้าง level ของหมวดหมู่เดิม
-- ==============================================================================

-- Step 1: Create new root category
INSERT INTO budget_categories 
(code, name_th, name_en, description, parent_id, level, sort_order, is_active, created_at, updated_at)
VALUES 
('GOVT_PERSONNEL_EXP', 
 'รายการค่าใช้จ่ายบุคลากรภาครัฐ', 
 'Government Personnel Expenditure', 
 'หมวดหมู่หลักสำหรับค่าใช้จ่ายบุคลากรภาครัฐทั้งหมด', 
 NULL, 
 0, 
 0, 
 1,
 CURRENT_TIMESTAMP,
 CURRENT_TIMESTAMP);

-- Step 2: Get the new root category ID
SET @root_id = LAST_INSERT_ID();

-- Step 3: Update levels from bottom to top (เพิ่ม level ทีละ 1)
-- ต้องทำจากล่างขึ้นบนเพื่อไม่ให้เกิด conflict

-- Level 4 -> 5
UPDATE budget_categories SET level = 5 WHERE level = 4;

-- Level 3 -> 4
UPDATE budget_categories SET level = 4 WHERE level = 3;

-- Level 2 -> 3
UPDATE budget_categories SET level = 3 WHERE level = 2;

-- Level 1 -> 2
UPDATE budget_categories SET level = 2 WHERE level = 1;

-- Level 0 -> 1 (งบดำเนินงาน)
UPDATE budget_categories SET level = 1 WHERE level = 0 AND code = 'OPERATIONS';

-- Step 4: Set parent_id for top-level categories (งบบุคลากร + งบดำเนินงาน)
UPDATE budget_categories 
SET parent_id = @root_id 
WHERE code IN ('1', 'OPERATIONS');

-- Step 5: Verify the changes
SELECT 
    id,
    code,
    name_th,
    parent_id,
    level,
    sort_order
FROM budget_categories 
ORDER BY level ASC, sort_order ASC, id ASC
LIMIT 50;

-- Expected output:
-- level 0: GOVT_PERSONNEL_EXP (parent_id = NULL)
-- level 1: งบบุคลากร (code='1'), งบดำเนินงาน (code='OPERATIONS') - both have parent_id = @root_id
-- level 2-5: subcategories with adjusted levels
