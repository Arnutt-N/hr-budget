-- =====================================================
-- HR Budget System - Complete Budget Structure Migration
-- Version: 1.0
-- Date: 2025-12-31
-- Description: Creates missing tables for full CSV structure support
-- =====================================================

-- =====================================================
-- 1. CREATE activities TABLE (กิจกรรม)
-- Hierarchy: budget_types → plans → projects → activities
-- =====================================================
CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NULL COMMENT 'FK: projects.id',
    plan_id INT NULL COMMENT 'FK: plans.id (เผื่อกรณีไม่มี project)',
    code VARCHAR(50),
    name_th VARCHAR(500) NOT NULL,
    name_en VARCHAR(500),
    description TEXT,
    fiscal_year INT DEFAULT 2569,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    updated_by INT NULL,
    
    INDEX idx_project (project_id),
    INDEX idx_plan (plan_id),
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_is_active (is_active),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='กิจกรรม (Activity under Project/Plan)';

-- =====================================================
-- 2. CREATE province_groups TABLE (กลุ่มจังหวัด)
-- Example: ภาคเหนือตอนบน, ภาคตะวันออกเฉียงเหนือ
-- =====================================================
CREATE TABLE IF NOT EXISTS province_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name_th VARCHAR(255) NOT NULL,
    name_en VARCHAR(255),
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='กลุ่มจังหวัด';

-- Initial data for province groups
INSERT INTO province_groups (code, name_th, sort_order) VALUES
('NORTH-U', 'ภาคเหนือตอนบน 1', 1),
('NORTH-L', 'ภาคเหนือตอนล่าง 1', 2),
('NE-U', 'ภาคตะวันออกเฉียงเหนือตอนบน 1', 3),
('NE-L', 'ภาคตะวันออกเฉียงเหนือตอนล่าง 1', 4),
('CENTRAL', 'ภาคกลาง', 5),
('EAST', 'ภาคตะวันออก', 6),
('SOUTH-U', 'ภาคใต้ตอนบน', 7),
('SOUTH-L', 'ภาคใต้ชายแดน', 8);

-- =====================================================
-- 3. CREATE province_zones TABLE (เขตจังหวัด)
-- =====================================================
CREATE TABLE IF NOT EXISTS province_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    province_group_id INT NULL COMMENT 'FK: province_groups.id',
    code VARCHAR(20) NOT NULL UNIQUE,
    name_th VARCHAR(255) NOT NULL,
    name_en VARCHAR(255),
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_province_group (province_group_id),
    FOREIGN KEY (province_group_id) REFERENCES province_groups(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='เขตจังหวัด';

-- =====================================================
-- 4. CREATE inspection_zones TABLE (เขตตรวจราชการ)
-- =====================================================
CREATE TABLE IF NOT EXISTS inspection_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name_th VARCHAR(255) NOT NULL,
    name_en VARCHAR(255),
    description TEXT,
    responsible_person VARCHAR(255) COMMENT 'ผู้รับผิดชอบ',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='เขตตรวจราชการ';

-- Initial data for inspection zones (18 zones in Thailand)
INSERT INTO inspection_zones (code, name_th, sort_order) VALUES
('ZONE-01', 'เขตตรวจราชการที่ 1', 1),
('ZONE-02', 'เขตตรวจราชการที่ 2', 2),
('ZONE-03', 'เขตตรวจราชการที่ 3', 3),
('ZONE-04', 'เขตตรวจราชการที่ 4', 4),
('ZONE-05', 'เขตตรวจราชการที่ 5', 5),
('ZONE-06', 'เขตตรวจราชการที่ 6', 6),
('ZONE-07', 'เขตตรวจราชการที่ 7', 7),
('ZONE-08', 'เขตตรวจราชการที่ 8', 8),
('ZONE-09', 'เขตตรวจราชการที่ 9', 9),
('ZONE-10', 'เขตตรวจราชการที่ 10', 10),
('ZONE-11', 'เขตตรวจราชการที่ 11', 11),
('ZONE-12', 'เขตตรวจราชการที่ 12', 12),
('ZONE-13', 'เขตตรวจราชการที่ 13', 13),
('ZONE-14', 'เขตตรวจราชการที่ 14', 14),
('ZONE-15', 'เขตตรวจราชการที่ 15', 15),
('ZONE-16', 'เขตตรวจราชการที่ 16', 16),
('ZONE-17', 'เขตตรวจราชการที่ 17', 17),
('ZONE-18', 'เขตตรวจราชการที่ 18', 18);

-- =====================================================
-- 5. ADD missing FK columns to provinces table
-- =====================================================
ALTER TABLE provinces
    ADD COLUMN province_group_id INT NULL 
        COMMENT 'FK: province_groups.id' AFTER region_zone_id,
    ADD COLUMN province_zone_id INT NULL 
        COMMENT 'FK: province_zones.id' AFTER province_group_id,
    ADD COLUMN inspection_zone_id INT NULL 
        COMMENT 'FK: inspection_zones.id' AFTER province_zone_id;

-- Add FKs (ignore if already exists)
-- Note: Using safe approach that won't fail if column doesn't exist

-- =====================================================
-- 6. ADD activity_id to budget_allocations for linking
-- =====================================================
ALTER TABLE budget_allocations
    ADD COLUMN activity_id INT NULL 
        COMMENT 'FK: activities.id' AFTER item_id,
    ADD COLUMN organization_id INT NULL 
        COMMENT 'FK: organizations.id (กอง/หน่วยงาน)' AFTER activity_id;

-- =====================================================
-- 7. ADD organization_id to disbursement_records if missing
-- =====================================================
-- Already should have session_id which links to org

-- =====================================================
-- 8. CREATE comprehensive budget_line_items for tracking
-- This stores actual CSV rows with all references
-- =====================================================
CREATE TABLE IF NOT EXISTS budget_line_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fiscal_year INT NOT NULL DEFAULT 2569,
    
    -- Hierarchy References
    budget_type_id INT NULL COMMENT 'FK: budget_types',
    plan_id INT NULL COMMENT 'FK: plans',
    project_id INT NULL COMMENT 'FK: projects',
    activity_id INT NULL COMMENT 'FK: activities',
    expense_type_id INT NULL COMMENT 'FK: expense_types',
    expense_group_id INT NULL COMMENT 'FK: expense_groups',
    expense_item_id INT NULL COMMENT 'FK: expense_items (lowest level)',
    
    -- Organization References
    ministry_id INT NULL COMMENT 'กระทรวง: organizations.id',
    department_id INT NULL COMMENT 'กรม: organizations.id',
    division_id INT NULL COMMENT 'กอง: organizations.id',
    section_id INT NULL COMMENT 'กลุ่มงาน: organizations.id',
    province_id INT NULL COMMENT 'FK: provinces',
    province_group_id INT NULL COMMENT 'FK: province_groups',
    province_zone_id INT NULL COMMENT 'FK: province_zones',
    inspection_zone_id INT NULL COMMENT 'FK: inspection_zones',
    
    -- Budget Amounts
    allocated_pba DECIMAL(15,2) DEFAULT 0.00 COMMENT 'งบ พรบ.',
    allocated_received DECIMAL(15,2) DEFAULT 0.00 COMMENT 'งบจัดสรร',
    transfer_in DECIMAL(15,2) DEFAULT 0.00 COMMENT 'โอนเข้า',
    transfer_out DECIMAL(15,2) DEFAULT 0.00 COMMENT 'โอนออก',
    disbursed DECIMAL(15,2) DEFAULT 0.00 COMMENT 'เบิกจ่าย',
    po_commitment DECIMAL(15,2) DEFAULT 0.00 COMMENT 'PO',
    remaining DECIMAL(15,2) DEFAULT 0.00 COMMENT 'คงเหลือ',
    
    -- Metadata
    region_type ENUM('central', 'regional') DEFAULT 'central' COMMENT 'ส่วนกลาง/ภูมิภาค',
    remarks TEXT COMMENT 'หมายเหตุ',
    status ENUM('active', 'closed', 'frozen') DEFAULT 'active',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NULL,
    updated_by INT NULL,
    
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_plan (plan_id),
    INDEX idx_project (project_id),
    INDEX idx_activity (activity_id),
    INDEX idx_division (division_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='รายการงบประมาณ (จาก CSV - รวม all dimensions)';

-- =====================================================
-- Summary
-- =====================================================
SELECT 'Migration completed. New tables created:' AS status;
SELECT 'activities' AS table_name, COUNT(*) as row_count FROM activities
UNION ALL
SELECT 'province_groups', COUNT(*) FROM province_groups
UNION ALL
SELECT 'province_zones', COUNT(*) FROM province_zones  
UNION ALL
SELECT 'inspection_zones', COUNT(*) FROM inspection_zones
UNION ALL
SELECT 'budget_line_items', COUNT(*) FROM budget_line_items;
