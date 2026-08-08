-- Add parent_id column to existing budget_category_items table
-- This preserves existing data

ALTER TABLE budget_category_items
    ADD COLUMN parent_id INT NULL AFTER category_id,
    ADD INDEX idx_parent_id (parent_id);

-- Optional: Change level from INT to TINYINT for consistency
-- ALTER TABLE budget_category_items MODIFY COLUMN level TINYINT DEFAULT 0;

-- Optional: Add FK constraint (only if you want CASCADE delete)
-- ALTER TABLE budget_category_items
--     ADD CONSTRAINT fk_item_parent 
--     FOREIGN KEY (parent_id) 
--     REFERENCES budget_category_items(id) 
--     ON DELETE SET NULL;
