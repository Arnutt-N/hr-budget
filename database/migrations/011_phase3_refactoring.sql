-- =====================================================
-- Phase 3 Refactoring: Link Requests to Dimensional Schema
-- =====================================================

-- 1. Add org_id to budget_requests
ALTER TABLE `budget_requests`
ADD COLUMN `org_id` INT NULL COMMENT 'หน่วยงานที่เจ้าของคำขอ' AFTER `fiscal_year`,
ADD CONSTRAINT `fk_request_org` FOREIGN KEY (`org_id`) REFERENCES `dim_organization` (`org_id`) ON DELETE SET NULL;

-- 2. Add structure_id to budget_request_items
ALTER TABLE `budget_request_items`
ADD COLUMN `structure_id` INT NULL COMMENT 'เชื่อมโยงกับ Dimensional Structure' AFTER `category_item_id`,
ADD CONSTRAINT `fk_item_structure` FOREIGN KEY (`structure_id`) REFERENCES `dim_budget_structure` (`structure_id`) ON DELETE SET NULL;

-- 3. (Optional) Pre-seed dim_budget_structure with existing Categories?
-- Note: We will handle this via "Sync on Save" logic in the Application code 
-- to avoid creating unused structures.

-- 4. Temporary: Migrate existing requests to have default Org if exists
-- UPDATE `budget_requests` SET `org_id` = (SELECT org_id FROM dim_organization LIMIT 1) WHERE `org_id` IS NULL;
