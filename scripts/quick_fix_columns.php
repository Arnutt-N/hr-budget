<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "=== Running ALTER TABLE for Missing Columns ===\n";

try {
    $db = Database::getPdo();
    
    // Check if columns exist
    $columns = $db->query("SHOW COLUMNS FROM fact_budget_execution")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Current columns: " . implode(', ', $columns) . "\n\n";
    
    if (!in_array('record_date', $columns)) {
        echo "Adding record_date column...";        
        $db->exec("ALTER TABLE fact_budget_execution ADD COLUMN record_date DATE NULL DEFAULT NULL COMMENT 'วันที่บันทึก (สำหรับ filter)' AFTER fiscal_year");
        echo " ✓\n";
    } else {
        echo "record_date column already exists ✓\n";
    }
    
    if (!in_array('request_amount', $columns)) {
        echo "Adding request_amount column...";
        $db->exec("ALTER TABLE fact_budget_execution ADD COLUMN request_amount DECIMAL(20,2) NULL DEFAULT NULL COMMENT 'ขออนุมัติวงเงิน' AFTER disbursed_amount");
        echo " ✓\n";
    } else {
        echo "request_amount column already exists ✓\n";
    }
    
    // Add index if not exists
    $indexes = $db->query("SHOW INDEX FROM fact_budget_execution WHERE Key_name = 'idx_record_date'")->fetchAll();
    if (empty($indexes)) {
        echo "Adding idx_record_date index...";
        $db->exec("ALTER TABLE fact_budget_execution ADD INDEX idx_record_date (record_date)");
        echo " ✓\n";
    } else {
        echo "idx_record_date index already exists ✓\n";
    }
    
    echo "\n✅ All columns and indexes verified/added successfully!\n";
    
} catch (PDOException $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
