-- Migration: 026_create_budget_types.sql
-- ประเภทงบประมาณ: งบรายจ่ายบุคลากร, งบรายจ่ายของหน่วย, งบบูรณาการ

CREATE TABLE IF NOT EXISTS budget_types (
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
COMMENT='ประเภทงบประมาณ';

-- Insert initial data
INSERT INTO budget_types (code, name_th, name_en, sort_order) VALUES
('BUK', 'งบประมาณรายจ่ายบุคลากร', 'Personnel Budget', 1),
('UNIT', 'งบประมาณรายจ่ายของหน่วยรับงบประมาณ', 'Unit Budget', 2),
('INTEG', 'งบประมาณรายจ่ายบูรณาการ', 'Integrated Budget', 3);
