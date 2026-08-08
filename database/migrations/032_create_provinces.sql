-- Migration: 032_create_provinces.sql
-- จังหวัด (lookup table)

CREATE TABLE IF NOT EXISTS provinces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE COMMENT 'รหัสจังหวัด',
    name_th VARCHAR(100) NOT NULL,
    name_en VARCHAR(100),
    region ENUM('central', 'north', 'northeast', 'east', 'west', 'south') DEFAULT 'central' COMMENT 'ภาค',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='จังหวัด';

-- Insert initial data (กรุงเทพและส่วนกลาง)
INSERT INTO provinces (code, name_th, name_en, region, sort_order) VALUES
('10', 'กรุงเทพมหานคร', 'Bangkok', 'central', 1),
('00', 'ส่วนกลาง', 'Central Office', 'central', 0);
