<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain');

try {
    $dsn = "mysql:host=localhost;dbname=hr_budget;charset=utf8mb4";
    $pdo = new PDO($dsn, "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    
    echo "Running Migrations V6 (Fix Provinces)\n";
    
    // Fix provinces
    $sqls = [
        "ALTER TABLE provinces ADD COLUMN province_group_id INT NULL COMMENT 'FK: province_groups.id' AFTER region",
        "ALTER TABLE provinces ADD COLUMN province_zone_id INT NULL COMMENT 'FK: province_zones.id' AFTER province_group_id",
        "ALTER TABLE provinces ADD COLUMN inspection_zone_id INT NULL COMMENT 'FK: inspection_zones.id' AFTER province_zone_id"
    ];

    foreach ($sqls as $index => $sql) {
        try {
            $pdo->exec($sql);
            echo "[$index] Success: " . substr($sql, 0, 50) . "...\n";
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            if (strpos($msg, '1050') !== false || strpos($msg, 'already exists') !== false || strpos($msg, 'Duplicate column') !== false || strpos($msg, '1060') !== false) {
               echo "[$index] Skipped (Exists)\n"; 
            } else {
               echo "[$index] Error: $msg\n";
            }
        }
    }

} catch (Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage();
}
