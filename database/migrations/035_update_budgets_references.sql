-- Migration: 035_update_budgets_references.sql
-- ปรับปรุงตาราง budgets ให้เชื่อมโยงกับโครงสร้างใหม่

-- 1. เพิ่ม columns ใหม่ใน budgets
ALTER TABLE budgets
    ADD COLUMN budget_type_id INT NULL COMMENT 'ประเภทงบประมาณ' AFTER id,
    ADD COLUMN plan_id INT NULL COMMENT 'แผนงาน' AFTER budget_type_id,
    ADD COLUMN project_id INT NULL COMMENT 'ผลผลิต/โครงการ' AFTER plan_id,
    ADD COLUMN activity_id INT NULL COMMENT 'กิจกรรม' AFTER project_id,
    ADD COLUMN expense_type_id INT NULL COMMENT 'ประเภทรายจ่าย' AFTER activity_id,
    ADD COLUMN expense_group_id INT NULL COMMENT 'กลุ่มรายจ่าย' AFTER expense_type_id,
    ADD COLUMN expense_item_id INT NULL COMMENT 'รายการรายจ่าย' AFTER expense_group_id;

-- 2. สร้าง Indexes และ Foreign Keys
-- สร้าง Index ก่อน
CREATE INDEX idx_budgets_type ON budgets(budget_type_id);
CREATE INDEX idx_budgets_plan ON budgets(plan_id);
CREATE INDEX idx_budgets_project ON budgets(project_id);
CREATE INDEX idx_budgets_activity ON budgets(activity_id);
CREATE INDEX idx_budgets_expense_type ON budgets(expense_type_id);
CREATE INDEX idx_budgets_expense_group ON budgets(expense_group_id);
CREATE INDEX idx_budgets_expense_item ON budgets(expense_item_id);

-- สร้าง FK Constraints
ALTER TABLE budgets
    ADD CONSTRAINT fk_budgets_type FOREIGN KEY (budget_type_id) REFERENCES budget_types(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_budgets_plan FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_budgets_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_budgets_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_budgets_expense_type FOREIGN KEY (expense_type_id) REFERENCES expense_types(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_budgets_expense_group FOREIGN KEY (expense_group_id) REFERENCES expense_groups(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_budgets_expense_item FOREIGN KEY (expense_item_id) REFERENCES expense_items(id) ON DELETE SET NULL;

-- 3. Data Migration Idea (Optional script logic here or in PHP)
-- การ migrate ข้อมูลเดิมจาก category_id -> expense_group_id จะต้องทำผ่าน script แยก
-- เพราะ logic การ map ซับซ้อน
