<?php
/**
 * Run Migration 053
 */
require_once __DIR__ . '/../config/database.php';

// Manually load config if it returns array (based on previous findings)
$config = require __DIR__ . '/../config/database.php';

echo "=== Running Migration 053: Drop Legacy budget_plans ===\n";

try {
    $dsn = sprintf(
        "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
        $config['host'],
        $config['port'],
        $config['database']
    );
    
    // Enable multi-statement queries
    $options = $config['options'];
    $options[PDO::ATTR_EMULATE_PREPARES] = true;
    
    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    
    // SQL content from the migration file
    // Implementing directly here to allow splitting/handling better if needed, 
    // but reading the file is cleaner.
    $sqlFile = __DIR__ . '/../database/migrations/053_drop_legacy_budget_plans.sql';
    if (!file_exists($sqlFile)) {
        die("Error: Migration file not found at $sqlFile\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Execute
    $pdo->exec($sql);
    
    echo "Migration executed successfully.\n";
    
    // Verify
    $stmt = $pdo->query("SHOW TABLES LIKE 'budget_plans'");
    if ($stmt->rowCount() == 0) {
        echo "[SUCCESS] Table 'budget_plans' does not exist anymore.\n";
    } else {
        echo "[WARNING] Table 'budget_plans' still exists!\n";
    }
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'budget_plans_backup_20260102'");
    if ($stmt->rowCount() > 0) {
        echo "[SUCCESS] Backup table 'budget_plans_backup_20260102' created.\n";
    }
    
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
