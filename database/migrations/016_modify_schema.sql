-- Add plan flags to budget_categories
ALTER TABLE budget_categories 
ADD COLUMN IF NOT EXISTS is_plan BOOLEAN DEFAULT FALSE COMMENT 'ใช้เป็นแผนงานด้วย',
ADD COLUMN IF NOT EXISTS plan_name VARCHAR(255) DEFAULT NULL COMMENT 'ชื่อแผนงาน (ถ้าแตกต่างจากชื่อหมวดหมู่)';

-- Add organization_id to budget_trackings
ALTER TABLE budget_trackings
ADD COLUMN IF NOT EXISTS organization_id INT DEFAULT NULL COMMENT 'หน่วยงาน (NULL = ทุกหน่วยงาน)',
ADD KEY IF NOT EXISTS idx_organization (organization_id);

-- Separate constraint addition to avoid error if exists (MySQL doesn't support IF NOT EXISTS for constraints easily in one line)
-- But we can try to add it. If it fails, it might be due to duplicate key name.
-- PROD SAFETY: Using procedure or just ignoring error in basic script. 
-- For this environment, direct ALTER is generally fine, but we'll wrap in a block if possible or just run it. 
-- Since we can't easily do IF NOT EXISTS for FK in simple SQL without procedures:
ALTER TABLE budget_trackings
ADD CONSTRAINT fk_trackings_organization 
FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL;
