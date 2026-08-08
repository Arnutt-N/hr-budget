-- Migration: Add admin management columns to budget_category_items
-- Date: 2025-12-29
-- Purpose: Add timestamps, sorting, soft delete, and audit columns

ALTER TABLE budget_category_items
    -- Timestamps
    ADD COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'วันเวลาที่สร้าง',
    ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันเวลาที่แก้ไขล่าสุด',
    
    -- Ordering and status
    ADD COLUMN sort_order INT NOT NULL DEFAULT 0 COMMENT 'ลำดับการแสดงผล',
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'สถานะการใช้งาน (1=ใช้งาน, 0=ปิด)',
    
    -- Additional info
    ADD COLUMN description TEXT NULL COMMENT 'คำอธิบายเพิ่มเติม',
    
    -- Soft delete
    ADD COLUMN deleted_at TIMESTAMP NULL COMMENT 'วันเวลาที่ลบ (soft delete)',
    
    -- Audit trail
    ADD COLUMN created_by INT NULL COMMENT 'ผู้สร้าง (FK to users)',
    ADD COLUMN updated_by INT NULL COMMENT 'ผู้แก้ไขล่าสุด (FK to users)',
    
    -- Indexes
    ADD INDEX idx_sort_order (sort_order),
    ADD INDEX idx_is_active (is_active),
    ADD INDEX idx_deleted_at (deleted_at),
    ADD INDEX idx_created_by (created_by),
    ADD INDEX idx_updated_by (updated_by);

-- Optional: Add foreign keys if users table exists
-- ALTER TABLE budget_category_items
--     ADD CONSTRAINT fk_budget_items_created_by 
--         FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
--     ADD CONSTRAINT fk_budget_items_updated_by 
--         FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL;
