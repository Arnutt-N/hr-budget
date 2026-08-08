-- ================================================
-- Migration: 001_create_personnel_types.sql
-- Description: สร้างตารางประเภทบุคลากร
-- Created: 2024-12-14
-- ================================================

CREATE TABLE IF NOT EXISTS personnel_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type_code VARCHAR(20) NOT NULL UNIQUE,
    type_name_th VARCHAR(255) NOT NULL,
    type_name_en VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Seed Data: ประเภทบุคลากรหลัก
-- ================================================

INSERT INTO personnel_types (type_code, type_name_th, type_name_en, sort_order) VALUES
('SALARY', 'เงินเดือน', 'Salary', 1),
('PERMANENT', 'ค่าจ้างประจำ', 'Permanent Employee Wages', 2),
('GOVT_EMP', 'ค่าตอบแทนพนักงานราชการ', 'Government Employee Compensation', 3),
('ALLOWANCE', 'ค่าตอบแทนใช้สอยและวัสดุ', 'Allowance and Materials', 4)
ON DUPLICATE KEY UPDATE type_name_th = VALUES(type_name_th);
