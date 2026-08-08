-- Migration: 031_create_expense_items.sql
-- รายการรายจ่าย (Hierarchical - 6 levels from CSV)

CREATE TABLE IF NOT EXISTS expense_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_group_id INT,
    parent_id INT,
    code VARCHAR(50),
    name_th VARCHAR(500) NOT NULL,
    name_en VARCHAR(500),
    description TEXT,
    level INT DEFAULT 0 COMMENT 'ระดับ 0-5 ตาม CSV รายการ 0-5',
    is_header TINYINT(1) DEFAULT 0 COMMENT 'เป็นหัวข้อหลักหรือไม่',
    requires_quantity TINYINT(1) DEFAULT 1 COMMENT 'ต้องระบุจำนวนหรือไม่',
    default_unit VARCHAR(50) DEFAULT 'คน' COMMENT 'หน่วยนับเริ่มต้น',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    updated_by INT NULL,
    
    INDEX idx_expense_group (expense_group_id),
    INDEX idx_parent (parent_id),
    INDEX idx_level (level),
    INDEX idx_is_active (is_active),
    FOREIGN KEY (expense_group_id) REFERENCES expense_groups(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES expense_items(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='รายการรายจ่าย (Hierarchical 6 levels)';

-- Note: Data will be migrated from budget_category_items in Phase 2
