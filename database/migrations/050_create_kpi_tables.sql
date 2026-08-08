-- =====================================================
-- HR Budget System - KPI Tracking Tables
-- Version: 1.0
-- Date: 2026-01-01
-- Description: Creates KPI tracking system for budget performance monitoring
-- =====================================================

-- =====================================================
-- 1. KPI Sources (แหล่งข้อมูล KPI)
-- =====================================================
CREATE TABLE IF NOT EXISTS kpi_sources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE COMMENT 'รหัสแหล่ง',
    name_th VARCHAR(255) NOT NULL COMMENT 'ชื่อแหล่งข้อมูล',
    name_en VARCHAR(255) COMMENT 'English name',
    description TEXT COMMENT 'รายละเอียด',
    is_system TINYINT(1) DEFAULT 0 COMMENT 'ระบบกำหนด (ไม่สามารถลบได้)',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_code (code),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='แหล่งข้อมูล KPI';

-- Seed default KPI sources
INSERT INTO kpi_sources (code, name_th, name_en, is_system, sort_order) VALUES
('ACT_FY', 'พรบ รายจ่ายงบประมาณประจำปี', 'Annual Appropriation Act', 1, 1),
('CGD', 'กรมบัญชีกลาง', 'Comptroller General Department', 1, 2),
('MIN_PLAN', 'แผนกระทรวงยุติธรรม', 'Ministry of Justice Plan', 1, 3),
('OPS_PLAN', 'แผนสำนักงานปลัดกระทรวงยุติธรรม', 'Permanent Secretary Office Plan', 1, 4),
('POLICY', 'นโยบาย/ข้อสั่งการ', 'Policy/Directive', 1, 5),
('CUSTOM', 'กำหนดเอง', 'Custom', 0, 99);

-- =====================================================
-- 2. KPI Definitions (นิยาม KPI)
-- =====================================================
CREATE TABLE IF NOT EXISTS kpi_definitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kpi_source_id INT NOT NULL COMMENT 'FK: kpi_sources.id',
    code VARCHAR(50) NOT NULL COMMENT 'รหัส KPI',
    name_th VARCHAR(500) NOT NULL COMMENT 'ชื่อ KPI',
    name_en VARCHAR(500) COMMENT 'English name',
    description TEXT COMMENT 'คำอธิบาย KPI',
    
    -- KPI Type & Calculation
    metric_type ENUM(
        'disbursement_pct',     -- เปอร์เซ็นต์การเบิกจ่าย
        'approval_count',       -- จำนวนการอนุมัติ
        'processing_time',      -- ระยะเวลาดำเนินการ
        'project_count',        -- จำนวนโครงการ
        'activity_completed',   -- จำนวนกิจกรรมที่แล้วเสร็จ
        'percentage',           -- เปอร์เซ็นต์ทั่วไป
        'amount',               -- จำนวนเงิน
        'count',                -- จำนวนนับ
        'days',                 -- จำนวนวัน
        'ratio',                -- อัตราส่วน
        'custom'                -- กำหนดเอง
    ) DEFAULT 'percentage' COMMENT 'ประเภท metric',
    
    calculation_method TEXT COMMENT 'วิธีการคำนวณ (SQL หรือ formula)',
    unit VARCHAR(50) DEFAULT '%' COMMENT 'หน่วยวัด (%, บาท, ครั้ง, วัน, โครงการ, กิจกรรม)',
    
    -- Target configuration
    has_target TINYINT(1) DEFAULT 1 COMMENT 'มี target หรือไม่',
    target_type ENUM('fixed', 'cumulative', 'average', 'minimum', 'maximum') DEFAULT 'fixed' 
        COMMENT 'ประเภท target',
    
    -- Display settings
    display_format VARCHAR(50) DEFAULT '0.00' COMMENT 'รูปแบบการแสดงผล',
    color_good VARCHAR(7) DEFAULT '#22c55e' COMMENT 'สีเขียว (ผลดี)',
    color_warning VARCHAR(7) DEFAULT '#f59e0b' COMMENT 'สีเหลือง (เตือน)',
    color_bad VARCHAR(7) DEFAULT '#ef4444' COMMENT 'สีแดง (ผลไม่ดี)',
    icon VARCHAR(50) NULL COMMENT 'ไอคอน (e.g. chart-line, clock, folder)',
    
    fiscal_year INT DEFAULT 2569,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    updated_by INT NULL,
    
    INDEX idx_source (kpi_source_id),
    INDEX idx_metric_type (metric_type),
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_is_active (is_active),
    UNIQUE KEY uk_source_code_year (kpi_source_id, code, fiscal_year),
    
    FOREIGN KEY (kpi_source_id) REFERENCES kpi_sources(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='นิยาม KPI';

-- Seed default KPI definitions (for พรบ source)
INSERT INTO kpi_definitions 
(kpi_source_id, code, name_th, metric_type, unit, target_type, fiscal_year, sort_order) 
SELECT 
    ks.id,
    'DISB_PCT',
    'เปอร์เซ็นต์การเบิกจ่าย',
    'disbursement_pct',
    '%',
    'cumulative',
    2569,
    1
FROM kpi_sources ks WHERE ks.code = 'ACT_FY';

INSERT INTO kpi_definitions 
(kpi_source_id, code, name_th, metric_type, unit, target_type, fiscal_year, sort_order) 
SELECT 
    ks.id,
    'APPROVAL_CNT',
    'จำนวนการอนุมัติ',
    'approval_count',
    'รายการ',
    'cumulative',
    2569,
    2
FROM kpi_sources ks WHERE ks.code = 'ACT_FY';

INSERT INTO kpi_definitions 
(kpi_source_id, code, name_th, metric_type, unit, target_type, fiscal_year, sort_order) 
SELECT 
    ks.id,
    'PROC_TIME',
    'ระยะเวลาดำเนินการ',
    'processing_time',
    'วัน',
    'average',
    2569,
    3
FROM kpi_sources ks WHERE ks.code = 'CGD';

INSERT INTO kpi_definitions 
(kpi_source_id, code, name_th, metric_type, unit, target_type, fiscal_year, sort_order) 
SELECT 
    ks.id,
    'PROJECT_CNT',
    'จำนวนโครงการที่ดำเนินการ',
    'project_count',
    'โครงการ',
    'cumulative',
    2569,
    4
FROM kpi_sources ks WHERE ks.code = 'MIN_PLAN';

INSERT INTO kpi_definitions 
(kpi_source_id, code, name_th, metric_type, unit, target_type, fiscal_year, sort_order) 
SELECT 
    ks.id,
    'ACT_COMPLETE',
    'จำนวนกิจกรรมที่ดำเนินการแล้วเสร็จ',
    'activity_completed',
    'กิจกรรม',
    'cumulative',
    2569,
    5
FROM kpi_sources ks WHERE ks.code = 'MIN_PLAN';

-- =====================================================
-- 3. KPI Targets (เป้าหมาย KPI ตามช่วงเวลา)
-- =====================================================
CREATE TABLE IF NOT EXISTS kpi_targets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kpi_definition_id INT NOT NULL COMMENT 'FK: kpi_definitions.id',
    
    -- Link to budget structure (optional - KPI อาจเป็น overall หรือ specific)
    budget_line_item_id INT NULL COMMENT 'FK: budget_line_items.id (ถ้าเฉพาะเจาะจง)',
    budget_type_id INT NULL COMMENT 'FK: budget_types.id (ถ้าระดับ type)',
    plan_id INT NULL COMMENT 'FK: plans.id (ถ้าระดับ plan)',
    project_id INT NULL COMMENT 'FK: projects.id (ถ้าระดับ project)',
    activity_id INT NULL COMMENT 'FK: activities.id (ถ้าระดับ activity)',
    organization_id INT NULL COMMENT 'FK: organizations.id (ถ้าระดับ org)',
    
    -- Period specification
    fiscal_year INT NOT NULL DEFAULT 2569,
    period_type ENUM('yearly', 'quarterly', 'monthly', 'weekly') NOT NULL DEFAULT 'yearly'
        COMMENT 'ประเภทช่วงเวลา',
    period_value INT NULL COMMENT 'ค่าช่วงเวลา: Q1-4 (1-4), Month (1-12), Week (1-52), NULL=yearly',
    period_start_date DATE NULL COMMENT 'วันเริ่มต้นช่วง (สำหรับ weekly)',
    period_end_date DATE NULL COMMENT 'วันสิ้นสุดช่วง (สำหรับ weekly)',
    
    -- Target values
    target_value DECIMAL(15,2) NOT NULL COMMENT 'ค่าเป้าหมาย',
    threshold_warning DECIMAL(15,2) NULL COMMENT 'ขีดเตือน (เหลือง) - ต่ำกว่านี้เริ่มเตือน',
    threshold_critical DECIMAL(15,2) NULL COMMENT 'ขีดวิกฤต (แดง) - ต่ำกว่านี้วิกฤต',
    
    -- Metadata
    notes TEXT COMMENT 'หมายเหตุ',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    updated_by INT NULL,
    
    INDEX idx_kpi_def (kpi_definition_id),
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_period (period_type, period_value),
    INDEX idx_period_dates (period_start_date, period_end_date),
    INDEX idx_budget_line (budget_line_item_id),
    INDEX idx_project (project_id),
    INDEX idx_activity (activity_id),
    
    FOREIGN KEY (kpi_definition_id) REFERENCES kpi_definitions(id) ON DELETE CASCADE,
    FOREIGN KEY (budget_line_item_id) REFERENCES budget_line_items(id) ON DELETE CASCADE,
    FOREIGN KEY (budget_type_id) REFERENCES budget_types(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='เป้าหมาย KPI ตามช่วงเวลา';

-- =====================================================
-- 4. KPI Actuals (ผลการดำเนินงานจริง)
-- =====================================================
CREATE TABLE IF NOT EXISTS kpi_actuals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kpi_target_id INT NOT NULL COMMENT 'FK: kpi_targets.id',
    
    -- Actual values
    actual_value DECIMAL(15,2) NOT NULL COMMENT 'ค่าจริงที่วัดได้',
    recorded_date DATE NOT NULL COMMENT 'วันที่บันทึกผล',
    
    -- Achievement calculation
    achievement_rate DECIMAL(5,2) NULL COMMENT 'อัตราความสำเร็จ (%)',
    variance DECIMAL(15,2) NULL COMMENT 'ผลต่างจากเป้า',
    status ENUM('achieved', 'warning', 'critical', 'pending', 'exceeded') DEFAULT 'pending'
        COMMENT 'สถานะผลลัพธ์',
    
    -- Supporting data
    supporting_data JSON NULL COMMENT 'ข้อมูลเพิ่มเติม (JSON format)',
    source_reference VARCHAR(255) NULL COMMENT 'อ้างอิงแหล่งข้อมูล',
    remarks TEXT COMMENT 'หมายเหตุ',
    
    -- Verification workflow
    verified_by INT NULL COMMENT 'ผู้ตรวจสอบ (FK: users)',
    verified_at TIMESTAMP NULL COMMENT 'วันที่ตรวจสอบ',
    verification_notes TEXT COMMENT 'บันทึกการตรวจสอบ',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    updated_by INT NULL,
    
    INDEX idx_target (kpi_target_id),
    INDEX idx_recorded_date (recorded_date),
    INDEX idx_status (status),
    INDEX idx_verified (verified_by, verified_at),
    
    FOREIGN KEY (kpi_target_id) REFERENCES kpi_targets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='ผลการดำเนินงานจริง KPI';

-- =====================================================
-- 5. KPI Dashboard View (for easy querying)
-- =====================================================
CREATE OR REPLACE VIEW v_kpi_dashboard AS
SELECT 
    ks.code AS source_code,
    ks.name_th AS source_name,
    kd.code AS kpi_code,
    kd.name_th AS kpi_name,
    kd.metric_type,
    kd.unit,
    kt.fiscal_year,
    kt.period_type,
    kt.period_value,
    kt.target_value,
    kt.threshold_warning,
    kt.threshold_critical,
    COALESCE(ka.actual_value, 0) AS actual_value,
    COALESCE(ka.achievement_rate, 0) AS achievement_rate,
    COALESCE(ka.status, 'pending') AS status,
    ka.recorded_date,
    kd.color_good,
    kd.color_warning,
    kd.color_bad
FROM kpi_sources ks
JOIN kpi_definitions kd ON kd.kpi_source_id = ks.id
JOIN kpi_targets kt ON kt.kpi_definition_id = kd.id
LEFT JOIN kpi_actuals ka ON ka.kpi_target_id = kt.id
WHERE ks.is_active = 1 
  AND kd.is_active = 1 
  AND kt.is_active = 1
ORDER BY ks.sort_order, kd.sort_order, kt.period_type, kt.period_value;

-- =====================================================
-- Summary
-- =====================================================
SELECT 'KPI Tables Migration completed successfully!' AS status;
SELECT 'kpi_sources' AS table_name, COUNT(*) AS row_count FROM kpi_sources
UNION ALL
SELECT 'kpi_definitions', COUNT(*) FROM kpi_definitions
UNION ALL
SELECT 'kpi_targets', COUNT(*) FROM kpi_targets
UNION ALL
SELECT 'kpi_actuals', COUNT(*) FROM kpi_actuals;
