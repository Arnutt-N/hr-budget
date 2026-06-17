-- 069_org_level_budget_snapshots.sql
-- Phase 3: extend budget_monthly_snapshots to hold ORGANIZATION-LEVEL cumulative
-- snapshots imported from the budget-execution PDFs (one row per org per date),
-- in addition to the original allocation-level snapshots.
--
-- The PDFs give per-unit "รวมงบประมาณทั้งสิ้น" totals at each snapshot date but
-- not a reliable plan/category breakdown, so allocation_id is made nullable and
-- organization_id + the full PDF column set are added.

SET NAMES utf8mb4;

ALTER TABLE `budget_monthly_snapshots`
  MODIFY `allocation_id` INT NULL,
  ADD COLUMN `organization_id` INT NULL COMMENT 'org-level snapshot' AFTER `allocation_id`,
  ADD COLUMN `allocated_pba` DECIMAL(15,2) NULL COMMENT 'งบตาม พรบ.' AFTER `fiscal_year`,
  ADD COLUMN `transfer` DECIMAL(15,2) NULL COMMENT 'โอนจัดสรร/โอนเปลี่ยนแปลง' AFTER `allocated_received`,
  ADD COLUMN `pending` DECIMAL(15,2) NULL COMMENT 'ขออนุมัติวงเงิน' AFTER `disbursed`,
  ADD COLUMN `source` VARCHAR(30) NULL DEFAULT NULL COMMENT 'เช่น pdf_import',
  ADD KEY `idx_bms_org` (`organization_id`, `fiscal_year`, `snapshot_date`),
  ADD CONSTRAINT `fk_bms_org` FOREIGN KEY (`organization_id`)
      REFERENCES `organizations` (`id`) ON DELETE SET NULL;

-- One org-level snapshot per org per fiscal-year per date (idempotent upsert key).
-- Allocation-level rows keep organization_id NULL, so they never collide here.
ALTER TABLE `budget_monthly_snapshots`
  ADD UNIQUE KEY `uq_bms_org_date` (`organization_id`, `fiscal_year`, `snapshot_date`);
