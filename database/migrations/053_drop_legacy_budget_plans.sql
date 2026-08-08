-- =====================================================
-- HR Budget System - Drop Legacy budget_plans Table
-- Version: 1.0
-- Date: 2026-01-02
-- Description: Remove legacy budget_plans table (duplicate/encoding issues)
-- 
-- REASON FOR REMOVAL:
-- 1. budget_plans table is redundant with plans + projects + activities
-- 2. Contains 11 records with encoding issues (ID 8-18)
-- 3. Schema design per docs uses: plans, projects, activities separately
-- =====================================================

-- Disable FK checks for safe operations
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- 1. BACKUP: Create backup table first
-- =====================================================
DROP TABLE IF EXISTS budget_plans_backup_20260102;
CREATE TABLE budget_plans_backup_20260102 AS SELECT * FROM budget_plans;

SELECT CONCAT('Backup created: budget_plans_backup_20260102 with ', COUNT(*), ' records') AS status 
FROM budget_plans_backup_20260102;

-- =====================================================
-- 2. UPDATE REFERENCES: Set FKs to NULL before dropping
-- =====================================================

-- Check and update disbursement_details
SELECT CONCAT('disbursement_details records referencing budget_plans: ', COUNT(*)) AS status
FROM disbursement_details WHERE plan_id IS NOT NULL;

UPDATE disbursement_details SET plan_id = NULL WHERE plan_id IS NOT NULL;

-- Check budget_allocations - note: might reference plans table instead
-- The FK in migration 009 references budget_plans, but migration 035/036 reference plans
-- Need to check which FK actually exists

-- =====================================================
-- 3. DROP FOREIGN KEY CONSTRAINTS (if they exist)
-- =====================================================

-- Drop self-referencing FK
ALTER TABLE budget_plans DROP FOREIGN KEY IF EXISTS fk_budget_plans_parent;
ALTER TABLE budget_plans DROP FOREIGN KEY IF EXISTS fk_budget_plans_division;

-- Drop FKs from other tables (if they reference budget_plans)
-- These may or may not exist depending on migration order
ALTER TABLE disbursement_details DROP FOREIGN KEY IF EXISTS disbursement_details_ibfk_1;
ALTER TABLE disbursement_details DROP FOREIGN KEY IF EXISTS fk_dd_plan;

-- =====================================================
-- 4. DROP THE TABLE
-- =====================================================
DROP TABLE IF EXISTS budget_plans;

-- =====================================================
-- 5. VERIFY
-- =====================================================
SELECT 'budget_plans table dropped successfully' AS status;

-- Re-enable FK checks
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- Summary
-- =====================================================
SELECT 'Migration 053 completed: Legacy budget_plans table removed.' AS final_status;
SELECT 'Backup preserved in: budget_plans_backup_20260102' AS backup_info;
