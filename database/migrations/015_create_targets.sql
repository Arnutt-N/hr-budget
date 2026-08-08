CREATE TABLE IF NOT EXISTS target_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    name_th VARCHAR(255) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS budget_targets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    target_type_id INT NOT NULL,
    fiscal_year INT NOT NULL,
    quarter INT DEFAULT NULL COMMENT 'NULL=เป้าหมายรายปี, 1-4=ไตรมาส',
    organization_id INT DEFAULT NULL COMMENT 'NULL=ทุกหน่วยงาน',
    category_id INT DEFAULT NULL COMMENT 'NULL=ทุกหมวดหมู่',
    target_percent DECIMAL(5,2) COMMENT 'เป้าหมาย %',
    target_amount DECIMAL(15,2) COMMENT 'เป้าหมายจำนวนเงิน',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (target_type_id) REFERENCES target_types(id),
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (category_id) REFERENCES budget_categories(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    
    UNIQUE KEY unique_target (target_type_id, fiscal_year, quarter, organization_id, category_id)
);
