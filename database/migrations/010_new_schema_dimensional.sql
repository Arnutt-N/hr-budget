-- =====================================================
-- HR Budget System - New Dimensional Schema
-- Version: 3.0 (Dimensional Model for Excel/CSV Import)
-- Date: 2025-12-16
-- 
-- หลักการ: 
-- - คอลัมน์ที่ไม่ใช่ ID ยอมให้เป็น NULL หากใน Excel/CSV ไม่ระบุ
-- - ทุกฟิลด์ตัวเลขต้องเป็น NULL ได้ (ห้าม DEFAULT 0)
-- - ห้ามเดาค่าเอง ถ้า field ว่างให้เป็น NULL
-- =====================================================

-- =====================================================
-- DIMENSION TABLE 1: Dim_Organization (หน่วยงาน)
-- เก็บข้อมูลโครงสร้างหน่วยงาน
-- =====================================================
CREATE TABLE IF NOT EXISTS `dim_organization` (
    `org_id` INT NOT NULL AUTO_INCREMENT,
    `org_name` VARCHAR(255) NULL COMMENT 'ชื่อหน่วยงาน (เช่น กองยุทธศาสตร์ฯ)',
    `org_parent_name` VARCHAR(255) NULL COMMENT 'หน่วยงานต้นสังกัด (ถ้ามี)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`org_id`),
    KEY `idx_org_name` (`org_name`),
    KEY `idx_org_parent` (`org_parent_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Dimension: หน่วยงาน';

-- =====================================================
-- DIMENSION TABLE 2: Dim_Budget_Structure (โครงสร้างงบประมาณ)
-- แผนงาน → ผลผลิต → กิจกรรมหลัก → รายการ
-- =====================================================
CREATE TABLE IF NOT EXISTS `dim_budget_structure` (
    `structure_id` INT NOT NULL AUTO_INCREMENT,
    `plan_name` VARCHAR(500) NULL COMMENT 'แผนงาน',
    `output_name` VARCHAR(500) NULL COMMENT 'ผลผลิต',
    `activity_name` VARCHAR(500) NULL COMMENT 'กิจกรรมหลัก',
    `item_name` TEXT NULL COMMENT 'รายการ (ชื่อโครงการ/ค่าใช้จ่าย)',
    `item_level` INT NULL COMMENT 'ระดับชั้นข้อมูล (ถ้าต้องการระบุ)',
    `org_id` INT NULL COMMENT 'เชื่อมโยงหน่วยงาน',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`structure_id`),
    KEY `idx_structure_plan` (`plan_name`(100)),
    KEY `idx_structure_output` (`output_name`(100)),
    KEY `idx_structure_activity` (`activity_name`(100)),
    KEY `idx_structure_org` (`org_id`),
    CONSTRAINT `fk_structure_org` FOREIGN KEY (`org_id`) 
        REFERENCES `dim_organization` (`org_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Dimension: โครงสร้างงบประมาณ (แผนงาน/ผลผลิต/กิจกรรม)';

-- =====================================================
-- FACT TABLE: Fact_Budget_Execution (ข้อมูลงบประมาณและเบิกจ่าย)
-- 
-- ⚠️ ข้อสำคัญ: ทุกฟิลด์ตัวเลขต้องเป็น NULL ได้
-- ห้ามใส่ DEFAULT 0 เด็ดขาด เพื่อให้ตรงกับช่องว่างใน Excel/CSV
-- =====================================================
CREATE TABLE IF NOT EXISTS `fact_budget_execution` (
    `fact_id` BIGINT NOT NULL AUTO_INCREMENT,
    `structure_id` INT NULL COMMENT 'FK -> dim_budget_structure',
    `fiscal_year` INT NULL COMMENT 'ปีงบประมาณ (เช่น 2568)',
    
    -- กลุ่มที่ 1: ข้อมูลงบประมาณตั้งต้น
    `budget_act_amount` DECIMAL(20,2) NULL COMMENT 'งบประมาณตาม พรบ.',
    `budget_allocated_amount` DECIMAL(20,2) NULL COMMENT 'งบประมาณที่ได้รับจัดสรร',
    
    -- กลุ่มที่ 2: การบริหารงบ
    `transfer_change_amount` DECIMAL(20,2) NULL COMMENT 'โอนเปลี่ยนแปลง/โอนเบิกแทน (+/-)',
    `budget_net_balance` DECIMAL(20,2) NULL COMMENT 'คงเหลือ (ยอดงบสุทธิหลังโอน)',
    
    -- กลุ่มที่ 3: ผลการเบิกจ่าย (ณ 30 ก.ย.)
    `disbursed_amount` DECIMAL(20,2) NULL COMMENT 'เบิกจ่าย',
    `po_pending_amount` DECIMAL(20,2) NULL COMMENT 'ขออนุมัติวงเงิน/PO คงเหลือ',
    
    -- กลุ่มที่ 4: สรุปผล
    `total_spending_amount` DECIMAL(20,2) NULL COMMENT 'รวมทั้งสิ้น (เบิกจ่าย + PO)',
    `balance_amount` DECIMAL(20,2) NULL COMMENT 'เงินคงเหลือ (งบสุทธิ - รวมทั้งสิ้น)',
    
    -- กลุ่มที่ 5: ตัวชี้วัด (%)
    `percent_disburse_excl_po` DECIMAL(10,2) NULL COMMENT '% เบิกจ่ายไม่รวม PO',
    `percent_disburse_incl_po` DECIMAL(10,2) NULL COMMENT '% เบิกจ่ายรวม PO',
    
    -- Audit Trail
    `datasource_row` INT NULL COMMENT 'หมายเลขแถว Excel/CSV ต้นทาง (Audit Trail)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`fact_id`),
    KEY `idx_fact_structure` (`structure_id`),
    KEY `idx_fact_year` (`fiscal_year`),
    KEY `idx_fact_row` (`datasource_row`),
    CONSTRAINT `fk_fact_structure` FOREIGN KEY (`structure_id`) 
        REFERENCES `dim_budget_structure` (`structure_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Fact: ข้อมูลงบประมาณและการเบิกจ่าย (จาก Excel/CSV)';

-- =====================================================
-- LOG TABLE: Log_Transfer_Note (หมายเหตุการโอน)
-- เก็บข้อความบรรยายการโอนที่ปรากฏในหมายเหตุ Excel/CSV
-- =====================================================
CREATE TABLE IF NOT EXISTS `log_transfer_note` (
    `log_id` INT NOT NULL AUTO_INCREMENT,
    `fact_id` BIGINT NULL COMMENT 'เชื่อมโยงกับ fact_budget_execution (ถ้าทราบ)',
    `source_row` INT NULL COMMENT 'แถว Excel/CSV ที่ปรากฏหมายเหตุ',
    `transfer_description` TEXT NULL COMMENT 'ข้อความบรรยายการโอน',
    `transfer_amount` DECIMAL(20,2) NULL COMMENT 'ยอดเงินที่ระบุในหมายเหตุ',
    `related_quarter` VARCHAR(50) NULL COMMENT 'ไตรมาสที่ระบุ (ถ้ามี)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`log_id`),
    KEY `idx_log_fact` (`fact_id`),
    KEY `idx_log_row` (`source_row`),
    CONSTRAINT `fk_log_fact` FOREIGN KEY (`fact_id`) 
        REFERENCES `fact_budget_execution` (`fact_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Log: หมายเหตุการโอนเปลี่ยนแปลง (จาก Excel/CSV)';

-- =====================================================
-- ALTER EXISTING TABLES: Remove DEFAULT 0 from numeric fields
-- ทำให้ field ที่ว่างเป็น NULL ได้
-- =====================================================

-- budget_allocations: ลบ DEFAULT 0 ออก
ALTER TABLE `budget_allocations` 
    MODIFY COLUMN `allocated_pba` DECIMAL(18,2) NULL DEFAULT NULL,
    MODIFY COLUMN `allocated_received` DECIMAL(18,2) NULL DEFAULT NULL,
    MODIFY COLUMN `net_budget` DECIMAL(18,2) NULL DEFAULT NULL,
    MODIFY COLUMN `disbursed` DECIMAL(18,2) NULL DEFAULT NULL,
    MODIFY COLUMN `pending_approval` DECIMAL(18,2) NULL DEFAULT NULL,
    MODIFY COLUMN `po_commitment` DECIMAL(18,2) NULL DEFAULT NULL,
    MODIFY COLUMN `remaining` DECIMAL(18,2) NULL DEFAULT NULL;

-- budget_plans: ลบ DEFAULT 0 ออก
ALTER TABLE `budget_plans`
    MODIFY COLUMN `total_budget` DECIMAL(18,2) NULL DEFAULT NULL;

-- budget_trackings: ลบ DEFAULT 0 ออก
ALTER TABLE `budget_trackings`
    MODIFY COLUMN `allocated` DECIMAL(15,2) NULL DEFAULT NULL,
    MODIFY COLUMN `transfer` DECIMAL(15,2) NULL DEFAULT NULL,
    MODIFY COLUMN `disbursed` DECIMAL(15,2) NULL DEFAULT NULL,
    MODIFY COLUMN `pending` DECIMAL(15,2) NULL DEFAULT NULL,
    MODIFY COLUMN `po` DECIMAL(15,2) NULL DEFAULT NULL;

-- budgets: ลบ DEFAULT 0 ออก
ALTER TABLE `budgets`
    MODIFY COLUMN `allocated_amount` DECIMAL(15,2) NULL DEFAULT NULL,
    MODIFY COLUMN `spent_amount` DECIMAL(15,2) NULL DEFAULT NULL,
    MODIFY COLUMN `target_amount` DECIMAL(15,2) NULL DEFAULT NULL,
    MODIFY COLUMN `transfer_in` DECIMAL(15,2) NULL DEFAULT NULL,
    MODIFY COLUMN `transfer_out` DECIMAL(15,2) NULL DEFAULT NULL;

-- budget_records: ลบ DEFAULT 0 ออก
ALTER TABLE `budget_records`
    MODIFY COLUMN `transfer_allocation` DECIMAL(15,2) NULL DEFAULT NULL,
    MODIFY COLUMN `spent_amount` DECIMAL(15,2) NULL DEFAULT NULL,
    MODIFY COLUMN `request_amount` DECIMAL(15,2) NULL DEFAULT NULL,
    MODIFY COLUMN `po_amount` DECIMAL(15,2) NULL DEFAULT NULL;

-- budget_requests: ลบ DEFAULT 0 ออก
ALTER TABLE `budget_requests`
    MODIFY COLUMN `total_amount` DECIMAL(15,2) NULL DEFAULT NULL;

-- budget_request_items: ลบ DEFAULT 0 ออก
ALTER TABLE `budget_request_items`
    MODIFY COLUMN `quantity` DECIMAL(15,2) NULL DEFAULT NULL,
    MODIFY COLUMN `unit_price` DECIMAL(15,2) NULL DEFAULT NULL,
    MODIFY COLUMN `total_amount` DECIMAL(15,2) NULL DEFAULT NULL;

-- budget_transactions: ไม่เปลี่ยน amount เพราะเป็น NOT NULL required field

-- po_commitments: ลบ DEFAULT 0 ออก
ALTER TABLE `po_commitments`
    MODIFY COLUMN `disbursed_amount` DECIMAL(18,2) NULL DEFAULT NULL,
    MODIFY COLUMN `remaining_amount` DECIMAL(18,2) NULL DEFAULT NULL;

-- budget_monthly_snapshots: ลบ DEFAULT 0 ออก
ALTER TABLE `budget_monthly_snapshots`
    MODIFY COLUMN `net_budget` DECIMAL(18,2) NULL DEFAULT NULL,
    MODIFY COLUMN `disbursed` DECIMAL(18,2) NULL DEFAULT NULL,
    MODIFY COLUMN `pending_approval` DECIMAL(18,2) NULL DEFAULT NULL,
    MODIFY COLUMN `po_commitment` DECIMAL(18,2) NULL DEFAULT NULL,
    MODIFY COLUMN `remaining` DECIMAL(18,2) NULL DEFAULT NULL;

-- =====================================================
-- VIEW: Summary by Fiscal Year (สรุปตามปีงบประมาณ)
-- =====================================================
CREATE OR REPLACE VIEW `v_fact_summary_by_year` AS
SELECT 
    `fiscal_year`,
    COUNT(*) AS `record_count`,
    SUM(`budget_act_amount`) AS `total_budget_act`,
    SUM(`budget_allocated_amount`) AS `total_allocated`,
    SUM(`transfer_change_amount`) AS `total_transfer`,
    SUM(`budget_net_balance`) AS `total_net_balance`,
    SUM(`disbursed_amount`) AS `total_disbursed`,
    SUM(`po_pending_amount`) AS `total_po`,
    SUM(`total_spending_amount`) AS `total_spending`,
    SUM(`balance_amount`) AS `total_balance`,
    ROUND(AVG(`percent_disburse_excl_po`), 2) AS `avg_percent_excl_po`,
    ROUND(AVG(`percent_disburse_incl_po`), 2) AS `avg_percent_incl_po`
FROM `fact_budget_execution`
GROUP BY `fiscal_year`;

-- =====================================================
-- VIEW: Structure with Execution (โครงสร้างพร้อมข้อมูลเบิกจ่าย)
-- =====================================================
CREATE OR REPLACE VIEW `v_structure_with_execution` AS
SELECT 
    s.`structure_id`,
    s.`plan_name`,
    s.`output_name`,
    s.`activity_name`,
    s.`item_name`,
    s.`item_level`,
    o.`org_name`,
    o.`org_parent_name`,
    f.`fiscal_year`,
    f.`budget_act_amount`,
    f.`budget_allocated_amount`,
    f.`transfer_change_amount`,
    f.`budget_net_balance`,
    f.`disbursed_amount`,
    f.`po_pending_amount`,
    f.`total_spending_amount`,
    f.`balance_amount`,
    f.`percent_disburse_excl_po`,
    f.`percent_disburse_incl_po`,
    f.`datasource_row`
FROM `dim_budget_structure` s
LEFT JOIN `dim_organization` o ON s.`org_id` = o.`org_id`
LEFT JOIN `fact_budget_execution` f ON s.`structure_id` = f.`structure_id`;
