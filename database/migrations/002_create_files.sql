-- ================================================
-- Migration: 002_create_files.sql
-- Description: สร้างตารางจัดการไฟล์
-- Created: 2024-12-14
-- ================================================

CREATE TABLE IF NOT EXISTS files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type ENUM('pdf', 'csv', 'xlsx', 'xls', 'doc', 'docx', 'image', 'other') NOT NULL,
    mime_type VARCHAR(100),
    file_size BIGINT,
    category ENUM('budget_request', 'report', 'import', 'export', 'attachment', 'other') DEFAULT 'other',
    reference_type VARCHAR(50) COMMENT 'budget_request, budget, etc.',
    reference_id INT COMMENT 'ID ของ record ที่อ้างอิง',
    uploaded_by INT NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_files_category (category),
    INDEX idx_files_reference (reference_type, reference_id),
    INDEX idx_files_uploaded_by (uploaded_by),
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
