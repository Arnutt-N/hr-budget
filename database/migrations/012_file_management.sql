-- =====================================================
-- Phase: File Management (Document Archive)
-- Migration: Create tables for document management by budget structure
-- Updated: 2025-12-17
-- =====================================================

-- Drop existing tables if they exist (for clean migration)
DROP TABLE IF EXISTS `file_attachments`;
DROP TABLE IF EXISTS `files`;
DROP TABLE IF EXISTS `folders`;

-- 1. Folders table (organized by fiscal year and budget category)
CREATE TABLE IF NOT EXISTS `folders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `fiscal_year` INT NULL COMMENT 'ปีงบประมาณ (2568, 2569, ...)',
    `budget_category_id` INT NULL COMMENT 'เชื่อมกับหมวดหมู่งบประมาณ (ถ้ามี)',
    `parent_id` INT NULL COMMENT 'โฟลเดอร์แม่ (สำหรับโฟลเดอร์ที่สร้างเอง)',
    `folder_path` VARCHAR(500) NULL COMMENT 'เส้นทางเต็มของโฟลเดอร์',
    `description` TEXT NULL,
    `is_system` TINYINT(1) DEFAULT 0 COMMENT '1 = สร้างจากระบบ, 0 = สร้างเอง',
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_id`) REFERENCES `folders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`),
    INDEX `idx_fiscal_year` (`fiscal_year`),
    INDEX `idx_category` (`budget_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Files table (documents in folders)
CREATE TABLE IF NOT EXISTS `files` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `folder_id` INT NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `stored_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_type` VARCHAR(50) NOT NULL COMMENT 'pdf, xlsx, png, etc.',
    `file_size` INT NOT NULL COMMENT 'Size in bytes',
    `mime_type` VARCHAR(100) NULL,
    `description` TEXT NULL,
    `uploaded_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`folder_id`) REFERENCES `folders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`),
    INDEX `idx_folder` (`folder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create root folders for current fiscal year based on budget categories
-- This will be done by script after getting actual categories from budget_categories table
