-- ================================================
-- Migration: 004_create_fiscal_years.sql
-- Description: สร้างตารางปีงบประมาณ
-- Created: 2024-12-14
-- ================================================

CREATE TABLE IF NOT EXISTS fiscal_years (
    id INT PRIMARY KEY AUTO_INCREMENT,
    year INT NOT NULL UNIQUE COMMENT 'ปี พ.ศ.',
    start_date DATE NOT NULL COMMENT 'วันเริ่มต้นปีงบประมาณ',
    end_date DATE NOT NULL COMMENT 'วันสิ้นสุดปีงบประมาณ',
    is_current BOOLEAN DEFAULT FALSE COMMENT 'ปีงบประมาณปัจจุบัน',
    is_closed BOOLEAN DEFAULT FALSE COMMENT 'ปิดปีงบประมาณแล้ว',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_fiscal_years_current (is_current),
    INDEX idx_fiscal_years_year (year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- Seed Data: ปีงบประมาณ
-- ================================================

INSERT INTO fiscal_years (year, start_date, end_date, is_current, is_closed) VALUES
(2566, '2022-10-01', '2023-09-30', FALSE, TRUE),
(2567, '2023-10-01', '2024-09-30', FALSE, TRUE),
(2568, '2024-10-01', '2025-09-30', TRUE, FALSE),
(2569, '2025-10-01', '2026-09-30', FALSE, FALSE)
ON DUPLICATE KEY UPDATE is_current = VALUES(is_current);
