-- =====================================================
-- HR Budget System - KPI Enhancement: Triggers & Constraints
-- Version: 1.1
-- Date: 2026-01-01
-- Description: Adds triggers for auto-calculation and validation constraints
-- =====================================================

-- =====================================================
-- 1. Add Constraint: Validate period_value
-- =====================================================
ALTER TABLE kpi_targets
ADD CONSTRAINT chk_period_value CHECK (
    (period_type = 'yearly' AND period_value IS NULL) OR
    (period_type = 'quarterly' AND period_value BETWEEN 1 AND 4) OR
    (period_type = 'monthly' AND period_value BETWEEN 1 AND 12) OR
    (period_type = 'weekly' AND period_value BETWEEN 1 AND 52)
);

-- =====================================================
-- 2. Add Constraint: Weekly period must have dates
-- =====================================================
ALTER TABLE kpi_targets
ADD CONSTRAINT chk_weekly_dates CHECK (
    period_type != 'weekly' OR (period_start_date IS NOT NULL AND period_end_date IS NOT NULL)
);

-- =====================================================
-- 3. Helper Function: Calculate Achievement Rate
-- =====================================================
DELIMITER $$

CREATE FUNCTION calculate_achievement_rate(
    p_actual_value DECIMAL(15,2),
    p_target_value DECIMAL(15,2)
) RETURNS DECIMAL(5,2)
DETERMINISTIC
BEGIN
    DECLARE v_rate DECIMAL(5,2);
    
    IF p_target_value = 0 OR p_target_value IS NULL THEN
        RETURN 0.00;
    END IF;
    
    SET v_rate = (p_actual_value / p_target_value) * 100;
    
    -- Cap at 999.99% to fit in DECIMAL(5,2)
    IF v_rate > 999.99 THEN
        SET v_rate = 999.99;
    END IF;
    
    RETURN v_rate;
END$$

DELIMITER ;

-- =====================================================
-- 4. Helper Function: Determine Status
-- =====================================================
DELIMITER $$

CREATE FUNCTION determine_kpi_status(
    p_achievement_rate DECIMAL(5,2),
    p_threshold_warning DECIMAL(15,2),
    p_threshold_critical DECIMAL(15,2),
    p_target_value DECIMAL(15,2)
) RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
    DECLARE v_warning_pct DECIMAL(5,2);
    DECLARE v_critical_pct DECIMAL(5,2);
    
    -- Calculate threshold percentages
    IF p_target_value > 0 THEN
        SET v_warning_pct = (p_threshold_warning / p_target_value) * 100;
        SET v_critical_pct = (p_threshold_critical / p_target_value) * 100;
    ELSE
        SET v_warning_pct = 0;
        SET v_critical_pct = 0;
    END IF;
    
    -- Determine status
    IF p_achievement_rate >= 100 THEN
        RETURN 'achieved';
    ELSEIF p_achievement_rate > 100 THEN
        RETURN 'exceeded';
    ELSEIF p_achievement_rate >= v_warning_pct THEN
        RETURN 'achieved';
    ELSEIF p_achievement_rate >= v_critical_pct THEN
        RETURN 'warning';
    ELSE
        RETURN 'critical';
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- 5. Trigger: Auto-calculate on INSERT
-- =====================================================
DELIMITER $$

CREATE TRIGGER trg_kpi_actuals_before_insert
BEFORE INSERT ON kpi_actuals
FOR EACH ROW
BEGIN
    DECLARE v_target_value DECIMAL(15,2);
    DECLARE v_threshold_warning DECIMAL(15,2);
    DECLARE v_threshold_critical DECIMAL(15,2);
    
    -- Get target values
    SELECT 
        target_value, 
        threshold_warning, 
        threshold_critical
    INTO 
        v_target_value,
        v_threshold_warning,
        v_threshold_critical
    FROM kpi_targets 
    WHERE id = NEW.kpi_target_id;
    
    -- Calculate achievement rate
    SET NEW.achievement_rate = calculate_achievement_rate(NEW.actual_value, v_target_value);
    
    -- Calculate variance
    SET NEW.variance = NEW.actual_value - v_target_value;
    
    -- Determine status
    SET NEW.status = determine_kpi_status(
        NEW.achievement_rate,
        COALESCE(v_threshold_warning, v_target_value * 0.9),
        COALESCE(v_threshold_critical, v_target_value * 0.7),
        v_target_value
    );
END$$

DELIMITER ;

-- =====================================================
-- 6. Trigger: Auto-calculate on UPDATE
-- =====================================================
DELIMITER $$

CREATE TRIGGER trg_kpi_actuals_before_update
BEFORE UPDATE ON kpi_actuals
FOR EACH ROW
BEGIN
    DECLARE v_target_value DECIMAL(15,2);
    DECLARE v_threshold_warning DECIMAL(15,2);
    DECLARE v_threshold_critical DECIMAL(15,2);
    
    -- Only recalculate if actual_value changed
    IF NEW.actual_value != OLD.actual_value THEN
        -- Get target values
        SELECT 
            target_value, 
            threshold_warning, 
            threshold_critical
        INTO 
            v_target_value,
            v_threshold_warning,
            v_threshold_critical
        FROM kpi_targets 
        WHERE id = NEW.kpi_target_id;
        
        -- Recalculate achievement rate
        SET NEW.achievement_rate = calculate_achievement_rate(NEW.actual_value, v_target_value);
        
        -- Recalculate variance
        SET NEW.variance = NEW.actual_value - v_target_value;
        
        -- Redetermine status
        SET NEW.status = determine_kpi_status(
            NEW.achievement_rate,
            COALESCE(v_threshold_warning, v_target_value * 0.9),
            COALESCE(v_threshold_critical, v_target_value * 0.7),
            v_target_value
        );
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- 7. Add Missing Indexes (Performance)
-- =====================================================
ALTER TABLE kpi_definitions 
    ADD INDEX idx_code (code);

ALTER TABLE kpi_actuals 
    ADD INDEX idx_created_at (created_at);

ALTER TABLE kpi_targets 
    ADD INDEX idx_is_active (is_active);

-- =====================================================
-- Summary & Verification
-- =====================================================
SELECT 'KPI Enhancement Migration completed successfully!' AS status;

-- List all constraints
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    CONSTRAINT_TYPE
FROM information_schema.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = 'hr_budget' 
  AND TABLE_NAME IN ('kpi_targets', 'kpi_actuals')
  AND CONSTRAINT_TYPE = 'CHECK';

-- List all triggers
SELECT 
    TRIGGER_NAME,
    EVENT_MANIPULATION,
    EVENT_OBJECT_TABLE,
    ACTION_TIMING
FROM information_schema.TRIGGERS
WHERE TRIGGER_SCHEMA = 'hr_budget'
  AND EVENT_OBJECT_TABLE = 'kpi_actuals';

-- List all functions
SELECT 
    ROUTINE_NAME,
    ROUTINE_TYPE,
    DATA_TYPE as RETURN_TYPE
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = 'hr_budget'
  AND ROUTINE_NAME LIKE '%kpi%';
