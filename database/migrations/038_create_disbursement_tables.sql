-- Create disbursement_sessions table
CREATE TABLE IF NOT EXISTS disbursement_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    organization_id INT NOT NULL,
    fiscal_year INT NOT NULL,
    record_month TINYINT NOT NULL COMMENT '1-12',
    record_date DATE NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    UNIQUE KEY org_year_month (organization_id, fiscal_year, record_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create disbursement_records table
CREATE TABLE IF NOT EXISTS disbursement_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    activity_id INT NOT NULL,
    status ENUM('draft', 'completed') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES disbursement_sessions(id),
    FOREIGN KEY (activity_id) REFERENCES activities(id),
    UNIQUE KEY session_activity (session_id, activity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add disbursement_record_id to budget_trackings using a stored procedure for idempotency
DROP PROCEDURE IF EXISTS AddDisbursementRecordId;

DELIMITER //

CREATE PROCEDURE AddDisbursementRecordId()
BEGIN
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'budget_trackings' 
        AND COLUMN_NAME = 'disbursement_record_id'
    ) THEN
        ALTER TABLE budget_trackings 
        ADD COLUMN disbursement_record_id INT NULL AFTER id,
        ADD INDEX idx_disbursement_record (disbursement_record_id),
        ADD CONSTRAINT fk_budget_trackings_disbursement_record 
        FOREIGN KEY (disbursement_record_id) REFERENCES disbursement_records(id);
    END IF;
END//

DELIMITER ;

CALL AddDisbursementRecordId();
DROP PROCEDURE AddDisbursementRecordId;
