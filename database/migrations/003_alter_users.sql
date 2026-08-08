-- ================================================
-- Migration: 003_alter_users.sql
-- Description: เพิ่ม columns ให้ตาราง users
-- Created: 2024-12-14
-- ================================================

-- เพิ่ม column avatar (รูปโปรไฟล์)
-- Note: MySQL ไม่รองรับ IF NOT EXISTS สำหรับ ADD COLUMN
-- ใช้ stored procedure หรือ error handling แทน

DELIMITER //

DROP PROCEDURE IF EXISTS add_columns_to_users//

CREATE PROCEDURE add_columns_to_users()
BEGIN
    -- เพิ่ม avatar
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='users' AND COLUMN_NAME='avatar' AND TABLE_SCHEMA=DATABASE()) THEN
        ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER name;
    END IF;
    
    -- เพิ่ม last_login_at
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='users' AND COLUMN_NAME='last_login_at' AND TABLE_SCHEMA=DATABASE()) THEN
        ALTER TABLE users ADD COLUMN last_login_at TIMESTAMP NULL AFTER updated_at;
    END IF;
    
    -- เพิ่ม is_active
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='users' AND COLUMN_NAME='is_active' AND TABLE_SCHEMA=DATABASE()) THEN
        ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER role;
    END IF;
END//

DELIMITER ;

CALL add_columns_to_users();

DROP PROCEDURE IF EXISTS add_columns_to_users;
