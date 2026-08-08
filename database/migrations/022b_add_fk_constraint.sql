-- Alternative: Add foreign key constraint after table creation
-- Run this ONLY if the previous migration succeeded

ALTER TABLE budget_category_items
    ADD CONSTRAINT fk_budget_category_parent 
    FOREIGN KEY (parent_id) 
    REFERENCES budget_category_items(id) 
    ON DELETE CASCADE;
