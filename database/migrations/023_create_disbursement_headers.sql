CREATE TABLE IF NOT EXISTS disbursement_headers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fiscal_year INT NOT NULL,
    month TINYINT NOT NULL,
    organization_id INT NOT NULL,
    record_date DATE NOT NULL,
    status ENUM('draft', 'submitted', 'approved') DEFAULT 'draft',
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
);
