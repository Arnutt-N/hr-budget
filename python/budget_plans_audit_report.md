# Budget Plans Reference Audit Report

**Generated:** 2026-01-04T17:30:41.138286
**Project:** C:\laragon\www\hr_budget

## Summary

- **Total Files with References:** 6
- **Total References:** 32

### Files by Category

| Category | Files |
|----------|-------|
| SQL_Files | 6 |

### References by Type

| Type | Count |
|------|-------|
| OTHER | 21 |
| SQL_SELECT | 5 |
| SQL_DELETE | 3 |
| SQL_JOIN | 3 |

## Detailed References


### SQL_Files


#### database\migrations\009_extended_budget_schema.sql
**References:** 10


**OTHER:**
- Line 33: `CREATE TABLE IF NOT EXISTS `budget_plans` (`
- Line 50: `UNIQUE KEY `uk_budget_plans_code_year` (`code`, `fiscal_year`),`
- Line 51: `KEY `idx_budget_plans_year` (`fiscal_year`),`
- Line 52: `KEY `idx_budget_plans_type` (`plan_type`),`
- Line 53: `KEY `idx_budget_plans_parent` (`parent_id`),`
- ... and 1 more

**SQL_DELETE:**
- Line 55: `CONSTRAINT `fk_budget_plans_parent` FOREIGN KEY (`parent_id`) REFERENCES `budget_plans` (`id`) ON...`
- Line 56: `CONSTRAINT `fk_budget_plans_division` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) O...`
- Line 137: `CONSTRAINT `fk_allocations_plan` FOREIGN KEY (`plan_id`) REFERENCES `budget_plans` (`id`) ON DELE...`

**SQL_JOIN:**
- Line 323: `FROM budget_plans bp`

#### database\migrations\010_new_schema_dimensional.sql
**References:** 2


**OTHER:**
- Line 134: `-- budget_plans: ลบ DEFAULT 0 ออก`
- Line 135: `ALTER TABLE `budget_plans``

#### database\migrations\019_create_budget_allocations.sql
**References:** 1


**OTHER:**
- Line 13: `plan_id INT NOT NULL COMMENT 'FK: budget_plans',`

#### database\migrations\021_create_budget_plans.sql
**References:** 2


**OTHER:**
- Line 5: `-- Description: Creates the budget_plans table (Transactional Structure)`
- Line 8: `CREATE TABLE IF NOT EXISTS budget_plans (`

#### database\migrations\024_create_disbursement_details.sql
**References:** 1


**OTHER:**
- Line 18: `FOREIGN KEY (plan_id) REFERENCES budget_plans(id),`

#### database\migrations\053_drop_legacy_budget_plans.sql
**References:** 16


**OTHER:**
- Line 2: `-- HR Budget System - Drop Legacy budget_plans Table`
- Line 5: `-- Description: Remove legacy budget_plans table (duplicate/encoding issues)`
- Line 8: `-- 1. budget_plans table is redundant with plans + projects + activities`
- Line 19: `DROP TABLE IF EXISTS budget_plans_backup_20260102;`
- Line 36: `-- The FK in migration 009 references budget_plans, but migration 035/036 reference plans`
- ... and 4 more

**SQL_JOIN:**
- Line 20: `CREATE TABLE budget_plans_backup_20260102 AS SELECT * FROM budget_plans;`
- Line 23: `FROM budget_plans_backup_20260102;`

**SQL_SELECT:**
- Line 22: `SELECT CONCAT('Backup created: budget_plans_backup_20260102 with ', COUNT(*), ' records') AS status`
- Line 30: `SELECT CONCAT('disbursement_details records referencing budget_plans: ', COUNT(*)) AS status`
- Line 60: `SELECT 'budget_plans table dropped successfully' AS status;`
- Line 68: `SELECT 'Migration 053 completed: Legacy budget_plans table removed.' AS final_status;`
- Line 69: `SELECT 'Backup preserved in: budget_plans_backup_20260102' AS backup_info;`

## Recommended Migration Strategy

1. **Models:** Update `protected static $table = 'budget_plans'` to `'plans'`
2. **SQL Queries:** Replace `budget_plans` with `plans` in all queries
3. **Class Names:** Consider keeping class names as `BudgetPlan` for backward compatibility
4. **Foreign Keys:** Verify FK constraints are updated