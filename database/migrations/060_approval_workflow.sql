-- Approval Settings Table
CREATE TABLE IF NOT EXISTS approval_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    is_enabled TINYINT(1) DEFAULT 0 COMMENT '0=Disabled, 1=Enabled',
    updated_by INT NULL,
    updated_at DATETIME NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Approvers Table (Org-scoped)
CREATE TABLE IF NOT EXISTS approvers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    org_id INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_approver (user_id, org_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL COMMENT 'e.g. approval_request, approved, rejected',
    title VARCHAR(255) NOT NULL,
    message TEXT,
    link VARCHAR(255) NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default setting (Disabled by default)
INSERT IGNORE INTO approval_settings (setting_key, is_enabled, updated_at) 
VALUES ('budget_request_approval', 0, NOW());
