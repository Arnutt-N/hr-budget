-- Migration: Add hierarchy columns to budget_category_items
-- Compatible with MySQL 5.7+
-- Simple version without conditional logic

-- Create table if it does not exist
CREATE TABLE IF NOT EXISTS budget_category_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(500) NULL,
    parent_id INT NULL,
    level TINYINT NOT NULL DEFAULT 0,
    INDEX idx_parent (parent_id),
    INDEX idx_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
