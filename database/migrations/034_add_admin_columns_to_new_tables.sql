-- Migration: 034_add_admin_columns_to_new_tables.sql
-- เพิ่ม Admin Columns แบบ Safe (ตรวจสอบว่ามี column หรือยัง)

DROP PROCEDURE IF EXISTS AddColumnIfNotExists;
DELIMITER $$
CREATE PROCEDURE AddColumnIfNotExists(
    IN tableName VARCHAR(255),
    IN colName VARCHAR(255),
    IN colDef TEXT
)
BEGIN
    DECLARE colCount INT;
    SELECT COUNT(*) INTO colCount 
    FROM information_schema.columns 
    WHERE table_schema = DATABASE() 
    AND table_name = tableName 
    AND column_name = colName;
    
    IF colCount = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', tableName, ' ADD COLUMN ', colName, ' ', colDef);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

-- 1. budget_types
CALL AddColumnIfNotExists('budget_types', 'deleted_at', 'TIMESTAMP NULL COMMENT "Soft delete" AFTER is_active');
CALL AddColumnIfNotExists('budget_types', 'created_by', 'INT NULL COMMENT "ผู้สร้าง" AFTER updated_at');
CALL AddColumnIfNotExists('budget_types', 'updated_by', 'INT NULL COMMENT "ผู้แก้ไขล่าสุด" AFTER created_by');

-- 2. expense_groups
CALL AddColumnIfNotExists('expense_groups', 'deleted_at', 'TIMESTAMP NULL COMMENT "Soft delete" AFTER is_active');
CALL AddColumnIfNotExists('expense_groups', 'created_by', 'INT NULL COMMENT "ผู้สร้าง" AFTER updated_at');
CALL AddColumnIfNotExists('expense_groups', 'updated_by', 'INT NULL COMMENT "ผู้แก้ไขล่าสุด" AFTER created_by');

-- 3. plans
CALL AddColumnIfNotExists('plans', 'deleted_at', 'TIMESTAMP NULL COMMENT "Soft delete" AFTER is_active');
CALL AddColumnIfNotExists('plans', 'created_by', 'INT NULL COMMENT "ผู้สร้าง" AFTER updated_at');
CALL AddColumnIfNotExists('plans', 'updated_by', 'INT NULL COMMENT "ผู้แก้ไขล่าสุด" AFTER created_by');

-- 4. projects
CALL AddColumnIfNotExists('projects', 'deleted_at', 'TIMESTAMP NULL COMMENT "Soft delete" AFTER is_active');
CALL AddColumnIfNotExists('projects', 'created_by', 'INT NULL COMMENT "ผู้สร้าง" AFTER updated_at');
CALL AddColumnIfNotExists('projects', 'updated_by', 'INT NULL COMMENT "ผู้แก้ไขล่าสุด" AFTER created_by');

-- 5. activities
CALL AddColumnIfNotExists('activities', 'description', 'TEXT NULL COMMENT "คำอธิบายเพิ่มเติม" AFTER name_en');
CALL AddColumnIfNotExists('activities', 'deleted_at', 'TIMESTAMP NULL COMMENT "Soft delete" AFTER is_active');
CALL AddColumnIfNotExists('activities', 'created_by', 'INT NULL COMMENT "ผู้สร้าง" AFTER updated_at');
CALL AddColumnIfNotExists('activities', 'updated_by', 'INT NULL COMMENT "ผู้แก้ไขล่าสุด" AFTER created_by');

-- 6. provinces
CALL AddColumnIfNotExists('provinces', 'description', 'TEXT NULL COMMENT "คำอธิบายเพิ่มเติม" AFTER name_en');
CALL AddColumnIfNotExists('provinces', 'updated_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at');
CALL AddColumnIfNotExists('provinces', 'deleted_at', 'TIMESTAMP NULL COMMENT "Soft delete" AFTER is_active');
CALL AddColumnIfNotExists('provinces', 'created_by', 'INT NULL COMMENT "ผู้สร้าง" AFTER updated_at');
CALL AddColumnIfNotExists('provinces', 'updated_by', 'INT NULL COMMENT "ผู้แก้ไขล่าสุด" AFTER created_by');

-- 7. region_zones
CALL AddColumnIfNotExists('region_zones', 'updated_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at');
CALL AddColumnIfNotExists('region_zones', 'deleted_at', 'TIMESTAMP NULL COMMENT "Soft delete" AFTER is_active');
CALL AddColumnIfNotExists('region_zones', 'created_by', 'INT NULL COMMENT "ผู้สร้าง" AFTER updated_at');
CALL AddColumnIfNotExists('region_zones', 'updated_by', 'INT NULL COMMENT "ผู้แก้ไขล่าสุด" AFTER created_by');

DROP PROCEDURE IF EXISTS AddColumnIfNotExists;

-- Create Indexes (IGNORE DUPLICATES logic requires complex SQL, handling via separate calls or ignore errors)
-- Using simple CREATE INDEX IF NOT EXISTS (MySQL 8.0+) or ignoring error by checking manual
-- For simplicity in basic mysql client, we'll try to create and user can ignore "Duplicate key" or we wrap in procedure too.
-- Let's wrap index creation too.

DROP PROCEDURE IF EXISTS CreateIndexIfNotExists;
DELIMITER $$
CREATE PROCEDURE CreateIndexIfNotExists(
    IN tableName VARCHAR(255),
    IN indexName VARCHAR(255),
    IN colName VARCHAR(255)
)
BEGIN
    DECLARE idxCount INT;
    SELECT COUNT(*) INTO idxCount 
    FROM information_schema.statistics 
    WHERE table_schema = DATABASE() 
    AND table_name = tableName 
    AND index_name = indexName;
    
    IF idxCount = 0 THEN
        SET @sql = CONCAT('CREATE INDEX ', indexName, ' ON ', tableName, '(', colName, ')');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

CALL CreateIndexIfNotExists('budget_types', 'idx_budget_types_deleted', 'deleted_at');
CALL CreateIndexIfNotExists('expense_groups', 'idx_expense_groups_deleted', 'deleted_at');
CALL CreateIndexIfNotExists('plans', 'idx_plans_deleted', 'deleted_at');
CALL CreateIndexIfNotExists('projects', 'idx_projects_deleted', 'deleted_at');
CALL CreateIndexIfNotExists('activities', 'idx_activities_deleted', 'deleted_at');
CALL CreateIndexIfNotExists('provinces', 'idx_provinces_deleted', 'deleted_at');
CALL CreateIndexIfNotExists('region_zones', 'idx_region_zones_deleted', 'deleted_at');

DROP PROCEDURE IF EXISTS CreateIndexIfNotExists;
