-- Migration: 029_create_projects.sql
-- ผลผลิต/โครงการ

CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_id INT,
    code VARCHAR(50),
    name_th VARCHAR(500) NOT NULL,
    name_en VARCHAR(500),
    description TEXT,
    project_type ENUM('output', 'project') DEFAULT 'output' COMMENT 'ประเภท: ผลผลิต หรือ โครงการ',
    fiscal_year INT DEFAULT 2568,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_plan (plan_id),
    INDEX idx_fiscal_year (fiscal_year),
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='ผลผลิต/โครงการ';

-- Insert initial data
INSERT INTO projects (plan_id, code, name_th, project_type, sort_order)
SELECT p.id, 'PRJ-BUK-01', 'รายการค่าใช้จ่ายบุคลากรภาครัฐ', 'output', 1
FROM plans p WHERE p.code = 'PLAN-BUK'
UNION ALL
SELECT p.id, 'PRJ-BASIC-01', 'การบริหารจัดการและการพัฒนากระบวนการยุติธรรม', 'output', 1
FROM plans p WHERE p.code = 'PLAN-BASIC';
