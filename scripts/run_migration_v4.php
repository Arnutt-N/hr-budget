<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $dsn = "mysql:host=localhost;dbname=hr_budget;charset=utf8mb4";
    $pdo = new PDO($dsn, "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    
    echo "<h1>Running Migrations V4</h1>";
    $stmt = $pdo->query("SELECT VERSION()");
    echo "<p>DB Version: " . $stmt->fetchColumn() . "</p>";
    $stmt = null;

    $sqls = [];
    $sqls['040'] = "
CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NULL,
    plan_id INT NULL,
    code VARCHAR(50),
    name_th VARCHAR(500) NOT NULL,
    name_en VARCHAR(500),
    description TEXT,
    fiscal_year INT DEFAULT 2569,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    updated_by INT NULL,
    INDEX idx_project (project_id),
    INDEX idx_plan (plan_id),
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_is_active (is_active),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS province_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name_th VARCHAR(255) NOT NULL,
    name_en VARCHAR(255),
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO province_groups (code, name_th, sort_order) VALUES
('NORTH-U', 'ภาคเหนือตอนบน 1', 1),
('NORTH-L', 'ภาคเหนือตอนล่าง 1', 2),
('NE-U', 'ภาคตะวันออกเฉียงเหนือตอนบน 1', 3),
('NE-L', 'ภาคตะวันออกเฉียงเหนือตอนล่าง 1', 4),
('CENTRAL', 'ภาคกลาง', 5),
('EAST', 'ภาคตะวันออก', 6),
('SOUTH-U', 'ภาคใต้ตอนบน', 7),
('SOUTH-L', 'ภาคใต้ชายแดน', 8)
ON DUPLICATE KEY UPDATE name_th=VALUES(name_th);

CREATE TABLE IF NOT EXISTS province_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    province_group_id INT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    name_th VARCHAR(255) NOT NULL,
    name_en VARCHAR(255),
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_province_group (province_group_id),
    FOREIGN KEY (province_group_id) REFERENCES province_groups(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inspection_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name_th VARCHAR(255) NOT NULL,
    name_en VARCHAR(255),
    description TEXT,
    responsible_person VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO inspection_zones (code, name_th, sort_order) VALUES
('ZONE-01', 'เขตตรวจราชการที่ 1', 1),
('ZONE-02', 'เขตตรวจราชการที่ 2', 2),
('ZONE-03', 'เขตตรวจราชการที่ 3', 3),
('ZONE-04', 'เขตตรวจราชการที่ 4', 4),
('ZONE-05', 'เขตตรวจราชการที่ 5', 5),
('ZONE-06', 'เขตตรวจราชการที่ 6', 6),
('ZONE-07', 'เขตตรวจราชการที่ 7', 7),
('ZONE-08', 'เขตตรวจราชการที่ 8', 8),
('ZONE-09', 'เขตตรวจราชการที่ 9', 9),
('ZONE-10', 'เขตตรวจราชการที่ 10', 10),
('ZONE-11', 'เขตตรวจราชการที่ 11', 11),
('ZONE-12', 'เขตตรวจราชการที่ 12', 12),
('ZONE-13', 'เขตตรวจราชการที่ 13', 13),
('ZONE-14', 'เขตตรวจราชการที่ 14', 14),
('ZONE-15', 'เขตตรวจราชการที่ 15', 15),
('ZONE-16', 'เขตตรวจราชการที่ 16', 16),
('ZONE-17', 'เขตตรวจราชการที่ 17', 17),
('ZONE-18', 'เขตตรวจราชการที่ 18', 18)
ON DUPLICATE KEY UPDATE name_th=VALUES(name_th);

ALTER TABLE provinces ADD COLUMN province_group_id INT NULL AFTER region_zone_id;
ALTER TABLE provinces ADD COLUMN province_zone_id INT NULL AFTER province_group_id;
ALTER TABLE provinces ADD COLUMN inspection_zone_id INT NULL AFTER province_zone_id;

ALTER TABLE budget_allocations ADD COLUMN activity_id INT NULL AFTER item_id;
ALTER TABLE budget_allocations ADD COLUMN organization_id INT NULL AFTER activity_id;

CREATE TABLE IF NOT EXISTS budget_line_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fiscal_year INT NOT NULL DEFAULT 2569,
    budget_type_id INT NULL,
    plan_id INT NULL,
    project_id INT NULL,
    activity_id INT NULL,
    expense_type_id INT NULL,
    expense_group_id INT NULL,
    expense_item_id INT NULL,
    ministry_id INT NULL,
    department_id INT NULL,
    division_id INT NULL,
    section_id INT NULL,
    province_id INT NULL,
    province_group_id INT NULL,
    province_zone_id INT NULL,
    inspection_zone_id INT NULL,
    allocated_pba DECIMAL(15,2) DEFAULT 0.00,
    allocated_received DECIMAL(15,2) DEFAULT 0.00,
    transfer_in DECIMAL(15,2) DEFAULT 0.00,
    transfer_out DECIMAL(15,2) DEFAULT 0.00,
    disbursed DECIMAL(15,2) DEFAULT 0.00,
    po_commitment DECIMAL(15,2) DEFAULT 0.00,
    remaining DECIMAL(15,2) DEFAULT 0.00,
    region_type ENUM('central', 'regional') DEFAULT 'central',
    remarks TEXT,
    status ENUM('active', 'closed', 'frozen') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by INT NULL,
    updated_by INT NULL,
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_plan (plan_id),
    INDEX idx_project (project_id),
    INDEX idx_activity (activity_id),
    INDEX idx_division (division_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

    $sqls['041'] = "
ALTER TABLE organizations MODIFY COLUMN region ENUM('central', 'regional', 'provincial', 'central_in_region') DEFAULT 'central';
";

    foreach ($sqls as $key => $content) {
        echo "<h2>Processing $key</h2>";
        $statements = explode(';', $content);
        
        foreach ($statements as $index => $sql) {
            $sql = trim($sql);
            if (empty($sql)) continue;
            
            try {
                $pdo->exec($sql);
                echo "<p><strong>[$index] Success.</strong></p>";
            } catch (PDOException $e) {
                $msg = $e->getMessage();
                if (strpos($msg, '1050') !== false || strpos($msg, 'already exists') !== false || strpos($msg, 'Duplicate column') !== false || strpos($msg, '1060') !== false) {
                   echo "<p>[$index] Skipped (Exists).</p>"; 
                } else {
                   echo "<p><strong>[$index] Error: " . htmlspecialchars($msg) . "</strong></p>";
                }
            }
        }
    }
    
    // View
    echo "<h2>Updating View</h2>";
    $viewSql = "CREATE OR REPLACE VIEW v_organizations_hierarchy AS
SELECT 
    o.*,
    p.name_th as parent_name,
    p.code as parent_code,
    CASE o.org_type
        WHEN 'ministry' THEN 'กระทรวง'
        WHEN 'department' THEN 'กรม'
        WHEN 'division' THEN 'กอง/สำนัก'
        WHEN 'section' THEN 'กลุ่มงาน'
        WHEN 'province' THEN 'จังหวัด'
        WHEN 'office' THEN 'ส่วนราชการ'
        ELSE 'อื่นๆ'
    END as org_type_label,
    CASE o.region
        WHEN 'central' THEN 'ส่วนกลาง'
        WHEN 'regional' THEN 'ส่วนภูมิภาค'
        WHEN 'provincial' THEN 'จังหวัด'
        WHEN 'central_in_region' THEN 'ส่วนกลางที่ตั้งอยู่ในภูมิภาค'
        ELSE 'ไม่ระบุ'
    END as region_label
FROM organizations o
LEFT JOIN organizations p ON o.parent_id = p.id
ORDER BY o.level, o.sort_order";

    try {
        $pdo->exec($viewSql);
        echo "<div style='color:green'>View Updated</div>";
    } catch (Exception $e) {
         echo "<div style='color:red'>View Error: " . $e->getMessage() . "</div>";
    }

} catch (Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage();
}
