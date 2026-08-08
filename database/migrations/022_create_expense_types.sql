CREATE TABLE IF NOT EXISTS expense_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name_th VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

-- Seed data
INSERT IGNORE INTO expense_types (code, name_th, sort_order) VALUES
('personnel', 'งบบุคลากร', 1),
('operation', 'งบดำเนินงาน', 2),
('investment', 'งบลงทุน', 3),
('subsidy', 'งบเงินอุดหนุน', 4),
('other', 'งบรายจ่ายอื่น', 5);
