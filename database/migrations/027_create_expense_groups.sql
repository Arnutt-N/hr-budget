-- Migration: 027_create_expense_groups.sql
-- กลุ่มรายจ่าย: เงินเดือนและค่าจ้างประจำ, ค่าตอบแทนใช้สอยและวัสดุ, ค่าสาธารณูปโภค

CREATE TABLE IF NOT EXISTS expense_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_type_id INT NOT NULL,
    code VARCHAR(50),
    name_th VARCHAR(255) NOT NULL,
    name_en VARCHAR(255),
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_expense_type (expense_type_id),
    FOREIGN KEY (expense_type_id) REFERENCES expense_types(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='กลุ่มรายจ่าย';

-- Insert initial data (จะ migrate จาก budget_categories ภายหลัง)
INSERT INTO expense_groups (expense_type_id, code, name_th, sort_order)
SELECT et.id, 'SAL', 'เงินเดือนและค่าจ้างประจำ', 1
FROM expense_types et WHERE et.code = 'personnel'
UNION ALL
SELECT et.id, 'COMP', 'ค่าตอบแทนใช้สอยและวัสดุ', 1
FROM expense_types et WHERE et.code = 'operation'
UNION ALL
SELECT et.id, 'UTIL', 'ค่าสาธารณูปโภค', 2
FROM expense_types et WHERE et.code = 'operation'
UNION ALL
SELECT et.id, 'EQUIP', 'ค่าครุภัณฑ์', 1
FROM expense_types et WHERE et.code = 'investment'
UNION ALL
SELECT et.id, 'LAND', 'ค่าที่ดินและสิ่งก่อสร้าง', 2
FROM expense_types et WHERE et.code = 'investment';
