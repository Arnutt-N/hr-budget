-- Migration: Restore category_id column to budget_category_items
-- Date: 2025-12-29
-- Purpose: Fix 500 error in BudgetCategory::getAllWithItems

-- 1. Add column if not exists
ALTER TABLE budget_category_items
ADD COLUMN category_id INT NOT NULL AFTER id;

-- 2. Add Index
CREATE INDEX idx_category_id ON budget_category_items(category_id);

-- 3. Add Foreign Key (Optional, but good for integrity)
-- ALTER TABLE budget_category_items
-- ADD CONSTRAINT fk_items_category
-- FOREIGN KEY (category_id) REFERENCES budget_categories(id)
-- ON DELETE CASCADE;
