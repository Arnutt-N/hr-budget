-- =====================================================
-- HR Budget System - Hierarchy Enhancement Phase 1
-- Version: 1.1 (Fixed)
-- Date: 2026-01-01
-- Description: Adds parent-child hierarchy support for projects and activities
-- =====================================================

-- =====================================================
-- 0. Cleanup (Reset state for re-run capability)
-- =====================================================
-- Note: Using procedures to handle "DROP IF EXISTS" safely for columns
DROP PROCEDURE IF EXISTS upgrade_budget_hierarchy;
DELIMITER $$
CREATE PROCEDURE upgrade_budget_hierarchy()
BEGIN
    -- Drop triggers if they exist
    DROP TRIGGER IF EXISTS trg_projects_check_circular_insert;
    DROP TRIGGER IF EXISTS trg_projects_check_circular_update;
    DROP TRIGGER IF EXISTS trg_activities_check_circular_insert;
    DROP TRIGGER IF EXISTS trg_activities_check_circular_update;

    -- Reset Projects Columns (if exist)
    IF EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'projects' AND COLUMN_NAME = 'parent_id') THEN
        ALTER TABLE projects DROP FOREIGN KEY projects_ibfk_2; -- Assuming it's the 2nd FK, or by name if known. MySQL auto-naming can be tricky.
        -- If FK name isn't known, standard DROP COLUMN will fail if FK exists.
        -- Let's try to remove FK by knowing standard naming convention or ignoring error if specific name
    END IF;
    
    -- Simplest way for dev environment: Try to drop columns, ignore error if not exists
    BEGIN
        DECLARE CONTINUE HANDLER FOR SQLEXCEPTION BEGIN END;
        ALTER TABLE projects DROP FOREIGN KEY projects_ibfk_parent; -- Try specific name
        ALTER TABLE projects DROP COLUMN parent_id;
        ALTER TABLE projects DROP COLUMN level;
        
        ALTER TABLE activities DROP FOREIGN KEY activities_ibfk_parent;
        ALTER TABLE activities DROP COLUMN parent_id;
        ALTER TABLE activities DROP COLUMN level;
    END;
END$$
DELIMITER ;
CALL upgrade_budget_hierarchy();
DROP PROCEDURE upgrade_budget_hierarchy;

-- =====================================================
-- 1. Projects Hierarchy
-- =====================================================
ALTER TABLE projects
    ADD COLUMN parent_id INT NULL COMMENT 'FK: projects.id (Parent Project)' AFTER plan_id,
    ADD COLUMN level INT DEFAULT 0 COMMENT 'Level: 0=Root, 1=Sub, 2=Sub-Sub',
    ADD CONSTRAINT projects_ibfk_parent FOREIGN KEY (parent_id) REFERENCES projects(id) ON DELETE RESTRICT;

CREATE INDEX idx_projects_parent ON projects(parent_id);
CREATE INDEX idx_projects_level ON projects(level);

-- =====================================================
-- 2. Activities Hierarchy
-- =====================================================
ALTER TABLE activities
    ADD COLUMN parent_id INT NULL COMMENT 'FK: activities.id (Parent Activity)' AFTER project_id,
    ADD COLUMN level INT DEFAULT 0 COMMENT 'Level: 0=Root, 1=Sub, 2=Sub-Sub',
    ADD CONSTRAINT activities_ibfk_parent FOREIGN KEY (parent_id) REFERENCES activities(id) ON DELETE RESTRICT;

CREATE INDEX idx_activities_parent ON activities(parent_id);
CREATE INDEX idx_activities_level ON activities(level);

-- =====================================================
-- 3. Trigger: Prevent Infinite Loops (Circular References) - Projects
-- =====================================================
DELIMITER $$

CREATE TRIGGER trg_projects_check_circular_insert BEFORE INSERT ON projects
FOR EACH ROW
BEGIN
    IF NEW.parent_id IS NOT NULL AND NEW.parent_id = 0 THEN 
         SET NEW.parent_id = NULL;
    END IF;
END$$

CREATE TRIGGER trg_projects_check_circular_update BEFORE UPDATE ON projects
FOR EACH ROW
BEGIN
    DECLARE current_parent INT;
    
    IF NEW.parent_id IS NOT NULL AND (OLD.parent_id IS NULL OR NEW.parent_id != OLD.parent_id) THEN
        IF NEW.parent_id = NEW.id THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Project cannot be its own parent.';
        END IF;
        
        SET current_parent = NEW.parent_id;
        
        -- Check up to 10 levels
        WHILE current_parent IS NOT NULL DO
            IF current_parent = NEW.id THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Circular reference detected in project hierarchy.';
            END IF;
            
            SELECT parent_id INTO current_parent FROM projects WHERE id = current_parent;
            IF current_parent = 0 THEN SET current_parent = NULL; END IF;
        END WHILE;
    END IF;
END$$

-- =====================================================
-- 4. Trigger: Prevent Infinite Loops (Circular References) - Activities
-- =====================================================

CREATE TRIGGER trg_activities_check_circular_insert BEFORE INSERT ON activities
FOR EACH ROW
BEGIN
    IF NEW.parent_id IS NOT NULL AND NEW.parent_id = 0 THEN 
         SET NEW.parent_id = NULL; 
    END IF;
END$$

CREATE TRIGGER trg_activities_check_circular_update BEFORE UPDATE ON activities
FOR EACH ROW
BEGIN
    DECLARE current_parent INT;
    
    IF NEW.parent_id IS NOT NULL AND (OLD.parent_id IS NULL OR NEW.parent_id != OLD.parent_id) THEN
        IF NEW.parent_id = NEW.id THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Activity cannot be its own parent.';
        END IF;
        
        SET current_parent = NEW.parent_id;
        
        WHILE current_parent IS NOT NULL DO
            IF current_parent = NEW.id THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Circular reference detected in activity hierarchy.';
            END IF;
            
            SELECT parent_id INTO current_parent FROM activities WHERE id = current_parent;
            IF current_parent = 0 THEN SET current_parent = NULL; END IF;
        END WHILE;
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- Summary verification
-- =====================================================
SELECT 'Hierarchy Migration Phase 1 (Fixed) completed successfully!' AS status;
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'hr_budget' AND COLUMN_NAME IN ('parent_id', 'level') AND TABLE_NAME IN ('projects', 'activities');
