-- Migration: 036_update_budget_trackings_references.sql
-- ปรับปรุงตาราง budget_trackings ให้เชื่อมโยงกับโครงสร้างใหม่
-- แก้ไข: เปลี่ยนตำแหน่ง AFTER เนื่องจากไม่มี budget_id

-- 1. เพิ่ม columns ใหม่ใน budget_trackings
ALTER TABLE budget_trackings
    ADD COLUMN budget_type_id INT NULL AFTER id,
    ADD COLUMN plan_id INT NULL AFTER budget_type_id,
    ADD COLUMN project_id INT NULL AFTER plan_id,
    ADD COLUMN activity_id INT NULL AFTER project_id,
    ADD COLUMN expense_type_id INT NULL AFTER activity_id,
    ADD COLUMN expense_group_id INT NULL AFTER expense_type_id,
    ADD COLUMN expense_item_id INT NULL AFTER expense_group_id;

-- 2. สร้าง Indexes
CREATE INDEX idx_trackings_budget_type ON budget_trackings(budget_type_id);
CREATE INDEX idx_trackings_plan ON budget_trackings(plan_id);
CREATE INDEX idx_trackings_project ON budget_trackings(project_id);
CREATE INDEX idx_trackings_activity ON budget_trackings(activity_id);
CREATE INDEX idx_trackings_expense_type ON budget_trackings(expense_type_id);
CREATE INDEX idx_trackings_expense_group ON budget_trackings(expense_group_id);
CREATE INDEX idx_trackings_expense_item ON budget_trackings(expense_item_id);

-- 3. สร้าง FK Constraints
ALTER TABLE budget_trackings
    ADD CONSTRAINT fk_trackings_budget_type FOREIGN KEY (budget_type_id) REFERENCES budget_types(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_trackings_plan FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_trackings_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_trackings_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_trackings_expense_type FOREIGN KEY (expense_type_id) REFERENCES expense_types(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_trackings_expense_group FOREIGN KEY (expense_group_id) REFERENCES expense_groups(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_trackings_expense_item FOREIGN KEY (expense_item_id) REFERENCES expense_items(id) ON DELETE SET NULL;
