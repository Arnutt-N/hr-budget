-- Migration: 033_create_region_zones.sql
-- กลุ่มจังหวัด, เขตจังหวัด, เขตตรวจราชการ

CREATE TABLE IF NOT EXISTS region_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_type ENUM('group', 'district', 'inspection') NOT NULL COMMENT 'กลุ่มจังหวัด/เขตจังหวัด/เขตตรวจราชการ',
    code VARCHAR(20),
    name_th VARCHAR(255) NOT NULL,
    name_en VARCHAR(255),
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='กลุ่มจังหวัด เขตจังหวัด เขตตรวจราชการ';

-- Link table: provinces to region_zones (many-to-many)
CREATE TABLE IF NOT EXISTS province_region_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    province_id INT NOT NULL,
    region_zone_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_province_zone (province_id, region_zone_id),
    FOREIGN KEY (province_id) REFERENCES provinces(id) ON DELETE CASCADE,
    FOREIGN KEY (region_zone_id) REFERENCES region_zones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
