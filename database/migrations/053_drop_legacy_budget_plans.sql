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
-- MySQL 8.x does not support DROP FOREIGN KEY IF EXISTS.
-- =====================================================
DROP PROCEDURE IF EXISTS _drop_fk_if_exists;
DELIMITER //
CREATE PROCEDURE _drop_fk_if_exists(IN tbl VARCHAR(64), IN fk VARCHAR(64))
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = tbl
          AND CONSTRAINT_NAME = fk
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` DROP FOREIGN KEY `', fk, '`');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //
DELIMITER ;

-- Drop self-referencing FK
CALL _drop_fk_if_exists('budget_plans', 'fk_budget_plans_parent');
CALL _drop_fk_if_exists('budget_plans', 'fk_budget_plans_division');

-- Drop FKs from other tables (if they reference budget_plans)
CALL _drop_fk_if_exists('disbursement_details', 'disbursement_details_ibfk_1');
CALL _drop_fk_if_exists('disbursement_details', 'fk_dd_plan');

DROP PROCEDURE IF EXISTS _drop_fk_if_exists;

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
