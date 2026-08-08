-- Optional: Add foreign keys to users table
-- Run this ONLY if users table exists

ALTER TABLE budget_category_items
    ADD CONSTRAINT fk_budget_items_created_by 
        FOREIGN KEY (created_by) 
        REFERENCES users(id) 
        ON DELETE SET NULL,
    ADD CONSTRAINT fk_budget_items_updated_by 
        FOREIGN KEY (updated_by) 
        REFERENCES users(id) 
        ON DELETE SET NULL;
