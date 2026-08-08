-- =====================================================
-- HR Budget System - Budget Plans Table
-- Version: 1.0
-- Date: 2025-12-25
-- Description: Creates the budget_plans table (Transactional Structure)
-- =====================================================

CREATE TABLE IF NOT EXISTS budget_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fiscal_year INT NOT NULL DEFAULT 2568 COMMENT 'ปีงบประมาณ',
    parent_id INT NULL COMMENT 'Parent Plan ID',
    division_id INT NULL COMMENT 'FK: organizations.id',
    
    code VARCHAR(50) NULL COMMENT 'รหัสแผนงาน/ผลผลิต',
    name_th VARCHAR(255) NOT NULL,
    name_en VARCHAR(255) NULL,
    description TEXT NULL,
    
    plan_type ENUM('strategic', 'roadmap', 'program', 'project', 'activity', 'sub_activity') NOT NULL DEFAULT 'program' COMMENT 'ประเภทแผน',
    level INT NOT NULL DEFAULT 1,
    sort_order INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_parent_id (parent_id),
    INDEX idx_division_id (division_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Budget Plans table created successfully' AS status;
