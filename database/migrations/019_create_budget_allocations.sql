-- =====================================================
-- HR Budget System - Budget Allocations & Transactions
-- Version: 1.0
-- Date: 2025-12-25
-- Description: Creates the transactional tables to replace dimensional model
-- =====================================================

-- 1. Budget Allocations Table
-- Holds the current state of budget for each item/plan
CREATE TABLE IF NOT EXISTS budget_allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fiscal_year INT NOT NULL COMMENT 'ปีงบประมาณ',
    plan_id INT NOT NULL COMMENT 'FK: budget_plans',
    category_id INT NULL COMMENT 'FK: budget_categories (Optional, can derive from item)',
    item_id INT NULL COMMENT 'FK: budget_category_items',
    
    -- Budget Amounts
    allocated_pba DECIMAL(15,2) DEFAULT 0.00 COMMENT 'งบ พรบ.',
    allocated_received DECIMAL(15,2) DEFAULT 0.00 COMMENT 'งบจัดสรร (ได้รับจริง)',
    transfer_in DECIMAL(15,2) DEFAULT 0.00 COMMENT 'โอนเข้า',
    transfer_out DECIMAL(15,2) DEFAULT 0.00 COMMENT 'โอนออก',
    net_budget DECIMAL(15,2) DEFAULT 0.00 COMMENT 'งบสุทธิ (จัดสรร + โอนเข้า - โอนออก)',
    
    -- Execution Stream
    disbursed DECIMAL(15,2) DEFAULT 0.00 COMMENT 'เบิกจ่ายจริง',
    po_commitment DECIMAL(15,2) DEFAULT 0.00 COMMENT 'ใบสั่งซื้อ/สัญญา (PO)',
    pending_approval DECIMAL(15,2) DEFAULT 0.00 COMMENT 'ขออนุมัติหลักการ (จองงบ)',
    remaining DECIMAL(15,2) DEFAULT 0.00 COMMENT 'คงเหลือ (Net - Disbursed - PO - Pending)',
    
    status ENUM('active', 'closed', 'frozen') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_plan_id (plan_id),
    INDEX idx_item_id (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Budget Monthly Snapshots
-- Stores historical state of allocations at month-end
CREATE TABLE IF NOT EXISTS budget_monthly_snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    allocation_id INT NOT NULL COMMENT 'FK: budget_allocations',
    fiscal_year INT NOT NULL,
    snapshot_date DATE NOT NULL COMMENT 'วันที่บันทึก (สิ้นเดือน)',
    
    allocated_received DECIMAL(15,2) DEFAULT 0.00,
    disbursed DECIMAL(15,2) DEFAULT 0.00,
    po_commitment DECIMAL(15,2) DEFAULT 0.00,
    remaining DECIMAL(15,2) DEFAULT 0.00,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_snapshot_date (snapshot_date),
    INDEX idx_allocation_fiscal (allocation_id, fiscal_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SELECT 'Budget Allocations tables created successfully' AS status;
