-- =====================================================
-- HR Budget System - Extended Schema for Budget Plans
-- Version: 2.0 (Phase 2 - Strategic Budget Structure)
-- Date: 2025-12-16
-- =====================================================

-- =====================================================
-- 1. DIVISIONS/DEPARTMENTS (กอง/สำนัก)
-- หน่วยงานภายใต้สังกัด เช่น กองยุทธศาสตร์, กองคลัง
-- =====================================================
CREATE TABLE IF NOT EXISTS `divisions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(20) NOT NULL COMMENT 'รหัสหน่วยงาน',
  `name_th` VARCHAR(255) NOT NULL COMMENT 'ชื่อหน่วยงาน (ไทย)',
  `name_en` VARCHAR(255) DEFAULT NULL COMMENT 'ชื่อหน่วยงาน (อังกฤษ)',
  `short_name` VARCHAR(50) DEFAULT NULL COMMENT 'ชื่อย่อ',
  `parent_id` INT DEFAULT NULL COMMENT 'หน่วยงานแม่ (ถ้ามี)',
  `type` ENUM('central', 'regional', 'provincial') DEFAULT 'central' COMMENT 'ประเภท: ส่วนกลาง/ภูมิภาค/จังหวัด',
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_divisions_code` (`code`),
  KEY `idx_divisions_parent` (`parent_id`),
  CONSTRAINT `fk_divisions_parent` FOREIGN KEY (`parent_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='หน่วยงาน/กอง/สำนัก';

-- =====================================================
-- 2. BUDGET PLANS (แผนงาน/ผลผลิต/โครงการ)
-- โครงสร้างเชิงยุทธศาสตร์ของงบประมาณ
-- =====================================================
CREATE TABLE IF NOT EXISTS `budget_plans` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fiscal_year` INT NOT NULL DEFAULT 2568 COMMENT 'ปีงบประมาณ',
  `code` VARCHAR(50) NOT NULL COMMENT 'รหัสแผนงาน/โครงการ',
  `name_th` VARCHAR(500) NOT NULL COMMENT 'ชื่อแผนงาน (ไทย)',
  `name_en` VARCHAR(500) DEFAULT NULL COMMENT 'ชื่อแผนงาน (อังกฤษ)',
  `description` TEXT DEFAULT NULL COMMENT 'รายละเอียด',
  `plan_type` ENUM('program', 'output', 'activity', 'project') NOT NULL COMMENT 'ประเภท: แผนงาน/ผลผลิต/กิจกรรมหลัก/โครงการ',
  `parent_id` INT DEFAULT NULL COMMENT 'แผนงานแม่',
  `division_id` INT DEFAULT NULL COMMENT 'หน่วยงานรับผิดชอบ',
  `level` INT DEFAULT 1 COMMENT 'ระดับ (1=แผนงาน, 2=ผลผลิต, 3=กิจกรรม, 4=โครงการ)',
  `total_budget` DECIMAL(18,2) DEFAULT 0.00 COMMENT 'งบประมาณรวม',
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_budget_plans_code_year` (`code`, `fiscal_year`),
  KEY `idx_budget_plans_year` (`fiscal_year`),
  KEY `idx_budget_plans_type` (`plan_type`),
  KEY `idx_budget_plans_parent` (`parent_id`),
  KEY `idx_budget_plans_division` (`division_id`),
  CONSTRAINT `fk_budget_plans_parent` FOREIGN KEY (`parent_id`) REFERENCES `budget_plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_budget_plans_division` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='แผนงาน/ผลผลิต/กิจกรรมหลัก/โครงการ';

-- =====================================================
-- 3. FUND SOURCES (แหล่งเงิน/หมวดงบประมาณ)
-- งบบุคลากร, งบดำเนินงาน, งบลงทุน, งบอุดหนุน, งบรายจ่ายอื่น
-- =====================================================
CREATE TABLE IF NOT EXISTS `fund_sources` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(20) NOT NULL COMMENT 'รหัสหมวด',
  `name_th` VARCHAR(255) NOT NULL COMMENT 'ชื่อหมวด (ไทย)',
  `name_en` VARCHAR(255) DEFAULT NULL COMMENT 'ชื่อหมวด (อังกฤษ)',
  `description` TEXT DEFAULT NULL,
  `parent_id` INT DEFAULT NULL COMMENT 'หมวดแม่',
  `level` INT DEFAULT 0,
  `color` VARCHAR(7) DEFAULT NULL COMMENT 'สี (HEX) สำหรับ UI',
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_fund_sources_code` (`code`),
  KEY `idx_fund_sources_parent` (`parent_id`),
  CONSTRAINT `fk_fund_sources_parent` FOREIGN KEY (`parent_id`) REFERENCES `fund_sources` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='แหล่งเงิน/หมวดงบประมาณหลัก';

-- =====================================================
-- 4. BUDGET ALLOCATIONS (การจัดสรรงบประมาณ) - IMPROVED
-- เชื่อม Plan + Fund Source + Category Item + Tracking
-- =====================================================
CREATE TABLE IF NOT EXISTS `budget_allocations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fiscal_year` INT NOT NULL DEFAULT 2568,
  `plan_id` INT DEFAULT NULL COMMENT 'แผนงาน/โครงการ',
  `fund_source_id` INT DEFAULT NULL COMMENT 'หมวดงบ (งบบุคลากร/งบดำเนินงาน)',
  `category_id` INT DEFAULT NULL COMMENT 'หมวดหมู่ (จาก budget_categories เดิม)',
  `item_id` INT NOT NULL COMMENT 'รายการย่อย (จาก budget_category_items เดิม)',
  `division_id` INT DEFAULT NULL COMMENT 'หน่วยงานรับผิดชอบ',
  
  -- งบประมาณ
  `allocated_pba` DECIMAL(18,2) DEFAULT 0.00 COMMENT 'งบตาม พ.ร.บ. งบประมาณ',
  `allocated_received` DECIMAL(18,2) DEFAULT 0.00 COMMENT 'งบประมาณที่ได้รับจัดสรร',
  
  -- ยอดสุทธิ (คำนวณจาก Transfer)
  `net_budget` DECIMAL(18,2) DEFAULT 0.00 COMMENT 'งบประมาณสุทธิ (หลังโอน)',
  
  -- การใช้จ่าย
  `disbursed` DECIMAL(18,2) DEFAULT 0.00 COMMENT 'เบิกจ่ายแล้ว',
  `pending_approval` DECIMAL(18,2) DEFAULT 0.00 COMMENT 'ขออนุมัติวงเงิน',
  `po_commitment` DECIMAL(18,2) DEFAULT 0.00 COMMENT 'ก่อหนี้ผูกพัน (PO)',
  
  -- คงเหลือ
  `remaining` DECIMAL(18,2) DEFAULT 0.00 COMMENT 'คงเหลือจ่าย',
  
  -- Audit Trail & Soft Delete
  `status` ENUM('draft', 'active', 'completed', 'cancelled') DEFAULT 'active',
  `version` INT DEFAULT 1 COMMENT 'Version number สำหรับ optimistic locking',
  `notes` TEXT DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `updated_by` INT DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete timestamp',
  `deleted_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  
  -- Simplified Unique Key (เอา division_id และ fund_source_id ออก)
  UNIQUE KEY `uk_allocation` (`fiscal_year`, `plan_id`, `item_id`),
  
  -- Performance Indexes
  KEY `idx_allocations_year` (`fiscal_year`),
  KEY `idx_allocations_plan` (`plan_id`),
  KEY `idx_allocations_fund` (`fund_source_id`),
  KEY `idx_allocations_item` (`item_id`),
  KEY `idx_allocations_division` (`division_id`),
  KEY `idx_allocations_status` (`status`),
  KEY `idx_allocations_deleted` (`deleted_at`),
  
  -- Covering Index for Summary Queries
  KEY `idx_allocation_summary` (`fiscal_year`, `plan_id`, `fund_source_id`, `net_budget`, `disbursed`, `po_commitment`),
  
  CONSTRAINT `fk_allocations_plan` FOREIGN KEY (`plan_id`) REFERENCES `budget_plans` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_allocations_fund` FOREIGN KEY (`fund_source_id`) REFERENCES `fund_sources` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_allocations_category` FOREIGN KEY (`category_id`) REFERENCES `budget_categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_allocations_item` FOREIGN KEY (`item_id`) REFERENCES `budget_category_items` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_allocations_division` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_allocations_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_allocations_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_allocations_deleted_by` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='การจัดสรรงบประมาณ (เชื่อมทุก Entity)';

-- =====================================================
-- 5. BUDGET TRANSFERS (การโอนเปลี่ยนแปลง) - IMPROVED
-- รายการโอนย้ายงบประมาณระหว่างรายการ
-- =====================================================
CREATE TABLE IF NOT EXISTS `budget_transfers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fiscal_year` INT NOT NULL DEFAULT 2568,
  `transfer_date` DATE NOT NULL COMMENT 'วันที่โอน',
  `reference_no` VARCHAR(50) DEFAULT NULL COMMENT 'เลขที่อ้างอิง',
  
  -- จาก (Source)
  `source_allocation_id` INT DEFAULT NULL COMMENT 'รายการต้นทาง',
  `source_description` VARCHAR(500) DEFAULT NULL COMMENT 'คำอธิบายต้นทาง',
  
  -- ไป (Destination)
  `destination_allocation_id` INT DEFAULT NULL COMMENT 'รายการปลายทาง',
  `destination_description` VARCHAR(500) DEFAULT NULL COMMENT 'คำอธิบายปลายทาง',
  
  -- จำนวนเงิน
  `amount` DECIMAL(18,2) NOT NULL COMMENT 'จำนวนเงินที่โอน',
  
  -- ประเภทและเหตุผล
  `transfer_type` ENUM('reallocation', 'adjustment', 'reserve', 'return') DEFAULT 'reallocation' COMMENT 'ประเภทการโอน',
  `reason` TEXT DEFAULT NULL COMMENT 'เหตุผล/คำอธิบาย (ยาว)',
  `reason_category` ENUM('overspend', 'underspend', 'emergency', 'policy', 'other') DEFAULT 'other' COMMENT 'หมวดเหตุผล',
  
  -- สถานะ
  `status` ENUM('draft', 'pending', 'approved', 'rejected') DEFAULT 'draft',
  `approved_by` INT DEFAULT NULL,
  `approved_at` TIMESTAMP NULL,
  
  `created_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_transfers_year` (`fiscal_year`),
  KEY `idx_transfers_date` (`transfer_date`),
  KEY `idx_transfers_source` (`source_allocation_id`),
  KEY `idx_transfers_dest` (`destination_allocation_id`),
  KEY `idx_transfers_status` (`status`),
  
  -- Fulltext Search for Reason
  FULLTEXT KEY `ft_reason` (`reason`),
  
  -- Data Integrity Constraints
  CONSTRAINT `chk_transfers_diff_allocation` 
    CHECK (`source_allocation_id` != `destination_allocation_id`),
  CONSTRAINT `chk_transfers_positive_amount` 
    CHECK (`amount` > 0),
  
  CONSTRAINT `fk_transfers_source` FOREIGN KEY (`source_allocation_id`) REFERENCES `budget_allocations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_transfers_dest` FOREIGN KEY (`destination_allocation_id`) REFERENCES `budget_allocations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_transfers_approved` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_transfers_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='รายการโอนเปลี่ยนแปลงงบประมาณ';

-- =====================================================
-- 5.1 BUDGET MONTHLY SNAPSHOTS (ข้อมูลรายเดือน)
-- สำหรับเก็บประวัติการเบิกจ่ายแต่ละเดือน (ณ วันที่ X ของเดือน Y)
-- =====================================================
CREATE TABLE IF NOT EXISTS `budget_monthly_snapshots` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `allocation_id` INT NOT NULL COMMENT 'รายการงบประมาณ',
  `snapshot_date` DATE NOT NULL COMMENT 'วันที่บันทึกข้อมูล',
  `net_budget` DECIMAL(18,2) DEFAULT 0.00 COMMENT 'งบประมาณสุทธิ ณ วันนั้น',
  `disbursed` DECIMAL(18,2) DEFAULT 0.00 COMMENT 'เบิกจ่ายสะสม ณ วันนั้น',
  `pending_approval` DECIMAL(18,2) DEFAULT 0.00,
  `po_commitment` DECIMAL(18,2) DEFAULT 0.00,
  `remaining` DECIMAL(18,2) DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_snapshot` (`allocation_id`, `snapshot_date`),
  KEY `idx_snapshot_date` (`snapshot_date`),
  CONSTRAINT `fk_snapshot_allocation` FOREIGN KEY (`allocation_id`) REFERENCES `budget_allocations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ข้อมูลสถิติรายเดือน';


-- =====================================================
-- 6. PO COMMITMENTS (ก่อหนี้ผูกพัน)
-- รายละเอียด PO แยกต่างหาก
-- =====================================================
CREATE TABLE IF NOT EXISTS `po_commitments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fiscal_year` INT NOT NULL DEFAULT 2568,
  `allocation_id` INT NOT NULL COMMENT 'รายการงบประมาณ',
  `po_number` VARCHAR(50) NOT NULL COMMENT 'เลขที่ PO',
  `po_date` DATE NOT NULL COMMENT 'วันที่ทำ PO',
  `vendor_name` VARCHAR(255) DEFAULT NULL COMMENT 'ชื่อผู้ขาย',
  `description` TEXT DEFAULT NULL COMMENT 'รายละเอียด',
  `amount` DECIMAL(18,2) NOT NULL COMMENT 'จำนวนเงิน',
  `disbursed_amount` DECIMAL(18,2) DEFAULT 0.00 COMMENT 'เบิกจ่ายแล้ว',
  `remaining_amount` DECIMAL(18,2) DEFAULT 0.00 COMMENT 'คงเหลือ',
  `status` ENUM('active', 'partial', 'completed', 'cancelled') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_po_number_year` (`po_number`, `fiscal_year`),
  KEY `idx_po_allocation` (`allocation_id`),
  KEY `idx_po_year` (`fiscal_year`),
  CONSTRAINT `fk_po_allocation` FOREIGN KEY (`allocation_id`) REFERENCES `budget_allocations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='รายการก่อหนี้ผูกพัน (PO)';

-- =====================================================
-- 7. DISBURSEMENTS (การเบิกจ่าย)
-- รายละเอียดการเบิกจ่ายแต่ละครั้ง
-- =====================================================
CREATE TABLE IF NOT EXISTS `disbursements` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fiscal_year` INT NOT NULL DEFAULT 2568,
  `allocation_id` INT NOT NULL COMMENT 'รายการงบประมาณ',
  `po_id` INT DEFAULT NULL COMMENT 'PO ที่เกี่ยวข้อง (ถ้ามี)',
  `disbursement_date` DATE NOT NULL COMMENT 'วันที่เบิกจ่าย',
  `reference_no` VARCHAR(50) DEFAULT NULL COMMENT 'เลขที่อ้างอิง',
  `description` TEXT DEFAULT NULL,
  `amount` DECIMAL(18,2) NOT NULL COMMENT 'จำนวนเงินที่เบิก',
  `payment_method` ENUM('transfer', 'cheque', 'cash', 'other') DEFAULT 'transfer',
  `created_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_disbursement_allocation` (`allocation_id`),
  KEY `idx_disbursement_po` (`po_id`),
  KEY `idx_disbursement_year` (`fiscal_year`),
  KEY `idx_disbursement_date` (`disbursement_date`),
  CONSTRAINT `fk_disbursement_allocation` FOREIGN KEY (`allocation_id`) REFERENCES `budget_allocations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_disbursement_po` FOREIGN KEY (`po_id`) REFERENCES `po_commitments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='รายการเบิกจ่าย';

-- =====================================================
-- SAMPLE DATA: Fund Sources (หมวดงบประมาณหลัก 5 หมวด)
-- =====================================================
INSERT INTO `fund_sources` (`code`, `name_th`, `name_en`, `level`, `sort_order`) VALUES
('PERSONNEL', 'งบบุคลากร', 'Personnel Budget', 0, 1),
('OPERATIONS', 'งบดำเนินงาน', 'Operating Budget', 0, 2),
('INVESTMENT', 'งบลงทุน', 'Investment Budget', 0, 3),
('SUBSIDY', 'งบเงินอุดหนุน', 'Subsidy Budget', 0, 4),
('OTHER', 'งบรายจ่ายอื่น', 'Other Expenditure', 0, 5)
ON DUPLICATE KEY UPDATE `name_th` = VALUES(`name_th`);

-- =====================================================
-- SAMPLE DATA: Divisions (ตัวอย่างหน่วยงาน)
-- =====================================================
INSERT INTO `divisions` (`code`, `name_th`, `name_en`, `short_name`, `type`, `sort_order`) VALUES
('STRATEGY', 'กองยุทธศาสตร์และแผนงาน', 'Strategy and Planning Division', 'กยผ.', 'central', 1),
('STRATEGY_SOUTH', 'กองยุทธศาสตร์และแผนงาน (จชต.)', 'Strategy (Southern Border)', 'กยผ.จชต.', 'regional', 2),
('FINANCE', 'กองบริหารการคลัง', 'Finance Division', 'กบค.', 'central', 3),
('ICT', 'ศูนย์เทคโนโลยีสารสนเทศและการสื่อสาร', 'ICT Center', 'ศทส.', 'central', 4),
('CONSTRUCTION', 'กองออกแบบและก่อสร้าง', 'Design and Construction Division', 'กอก.', 'central', 5),
('PROVINCIAL', 'กองประสานราชการยุติธรรมจังหวัด', 'Provincial Justice Coordination', 'กปย.', 'provincial', 6)
ON DUPLICATE KEY UPDATE `name_th` = VALUES(`name_th`);

-- =====================================================
-- VIEW: Budget Summary by Plan (สรุปตามแผนงาน)
-- =====================================================
CREATE OR REPLACE VIEW `v_budget_summary_by_plan` AS
SELECT 
    bp.id AS plan_id,
    bp.fiscal_year,
    bp.code AS plan_code,
    bp.name_th AS plan_name,
    bp.plan_type,
    d.name_th AS division_name,
    SUM(ba.allocated_received) AS total_allocated,
    SUM(ba.net_budget) AS total_net_budget,
    SUM(ba.disbursed) AS total_disbursed,
    SUM(ba.po_commitment) AS total_po,
    SUM(ba.remaining) AS total_remaining,
    CASE 
        WHEN SUM(ba.net_budget) > 0 
        THEN ROUND(SUM(ba.disbursed) / SUM(ba.net_budget) * 100, 2)
        ELSE 0 
    END AS disbursement_percent,
    CASE 
        WHEN SUM(ba.net_budget) > 0 
        THEN ROUND((SUM(ba.disbursed) + SUM(ba.po_commitment)) / SUM(ba.net_budget) * 100, 2)
        ELSE 0 
    END AS disbursement_with_po_percent
FROM budget_plans bp
LEFT JOIN budget_allocations ba ON bp.id = ba.plan_id
LEFT JOIN divisions d ON bp.division_id = d.id
GROUP BY bp.id, bp.fiscal_year, bp.code, bp.name_th, bp.plan_type, d.name_th;

-- =====================================================
-- VIEW: Budget Summary by Fund Source (สรุปตามหมวดงบ)
-- =====================================================
CREATE OR REPLACE VIEW `v_budget_summary_by_fund` AS
SELECT 
    fs.id AS fund_source_id,
    ba.fiscal_year,
    fs.code AS fund_code,
    fs.name_th AS fund_name,
    SUM(ba.allocated_received) AS total_allocated,
    SUM(ba.net_budget) AS total_net_budget,
    SUM(ba.disbursed) AS total_disbursed,
    SUM(ba.po_commitment) AS total_po,
    SUM(ba.remaining) AS total_remaining,
    CASE 
        WHEN SUM(ba.net_budget) > 0 
        THEN ROUND(SUM(ba.disbursed) / SUM(ba.net_budget) * 100, 2)
        ELSE 0 
    END AS disbursement_percent
FROM fund_sources fs
LEFT JOIN budget_allocations ba ON fs.id = ba.fund_source_id
GROUP BY fs.id, ba.fiscal_year, fs.code, fs.name_th;

-- =====================================================
-- VIEW: Transfer Summary (สรุปการโอนเปลี่ยนแปลง)
-- =====================================================
CREATE OR REPLACE VIEW `v_transfer_summary` AS
SELECT 
    bt.fiscal_year,
    bt.transfer_type,
    bt.reason_category,
    COUNT(*) AS transfer_count,
    SUM(bt.amount) AS total_amount,
    COUNT(CASE WHEN bt.status = 'approved' THEN 1 END) AS approved_count,
    SUM(CASE WHEN bt.status = 'approved' THEN bt.amount ELSE 0 END) AS approved_amount
FROM budget_transfers bt
GROUP BY bt.fiscal_year, bt.transfer_type, bt.reason_category;
