-- =====================================================
-- Rollback Script for Phase 3 Refactoring
-- Use this if migration fails or needs to be reverted
-- =====================================================

-- 1. Drop Foreign Keys first
ALTER TABLE `budget_requests` DROP FOREIGN KEY `fk_request_org`;
ALTER TABLE `budget_request_items` DROP FOREIGN KEY `fk_item_structure`;

-- 2. Drop Columns
ALTER TABLE `budget_requests` DROP COLUMN `org_id`;
ALTER TABLE `budget_request_items` DROP COLUMN `structure_id`;

-- Done - Tables reverted to original state
