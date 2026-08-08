-- =====================================================
-- 013_budget_list_enhancements.sql
-- Description: Add record_date and request_amount to fact_budget_execution
-- Date: 2025-12-20
-- =====================================================

-- 1. Add record_date (for date filtering)
-- 2. Add request_amount (for approval amount tracking)
ALTER TABLE `fact_budget_execution`
    ADD COLUMN IF NOT EXISTS `record_date` DATE NULL DEFAULT NULL COMMENT 'วันที่บันทึก (สำหรับ filter)' AFTER `fiscal_year`,
    ADD COLUMN IF NOT EXISTS `request_amount` DECIMAL(20,2) NULL DEFAULT NULL COMMENT 'ขออนุมัติวงเงิน' AFTER `disbursed_amount`,
    ADD INDEX `idx_record_date` (`record_date`);

-- Note: total_spending_amount logic might need to be updated in application code
-- to include request_amount: total = spent + request + po
