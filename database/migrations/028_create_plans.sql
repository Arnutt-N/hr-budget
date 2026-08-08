-- Migration: 028_create_plans.sql
-- แผนงาน: แผนงานบุคลากรภาครัฐ, แผนงานพื้นฐาน, แผนงานยุทธศาสตร์, แผนงานบูรณาการ

CREATE TABLE IF NOT EXISTS plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    budget_type_id INT,
    code VARCHAR(50),
    name_th VARCHAR(500) NOT NULL,
    name_en VARCHAR(500),
    description TEXT,
    fiscal_year INT DEFAULT 2568,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_budget_type (budget_type_id),
    INDEX idx_fiscal_year (fiscal_year),
    FOREIGN KEY (budget_type_id) REFERENCES budget_types(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='แผนงาน';

-- Insert initial data from CSV
INSERT INTO plans (budget_type_id, code, name_th, fiscal_year, sort_order)
SELECT bt.id, 'PLAN-BUK', 'แผนงานบุคลากรภาครัฐ', 2568, 1
FROM budget_types bt WHERE bt.code = 'BUK'
UNION ALL
SELECT bt.id, 'PLAN-BASIC', 'แผนงานพื้นฐานด้านการพัฒนาและเสริมสร้างศักยภาพทรัพยากรมนุษย์', 2568, 2
FROM budget_types bt WHERE bt.code = 'UNIT'
UNION ALL
SELECT bt.id, 'PLAN-STRAT', 'แผนงานยุทธศาสตร์เสริมสร้างพลังทางสังคม', 2568, 3
FROM budget_types bt WHERE bt.code = 'UNIT'
UNION ALL
SELECT bt.id, 'PLAN-INTEG', 'แผนงานบูรณาการต่อต้านการทุจริตและประพฤติมิชอบ', 2568, 1
FROM budget_types bt WHERE bt.code = 'INTEG';
