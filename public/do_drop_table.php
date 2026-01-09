<?php
// Force error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain; charset=utf-8');

try {
    // Determine config path
    $configFile = __DIR__ . '/../config/database.php';
    if (!file_exists($configFile)) {
        throw new Exception("Config file not found at $configFile");
    }
    
    $config = require $configFile;
    
    // Connect
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to DB.\n";
    
    // Check Table
    $stmt = $pdo->query("SHOW TABLES LIKE 'budget_plans'");
    if ($stmt->rowCount() == 0) {
        echo "STATUS: TABLE_DROPPED\n";
    } else {
        echo "STATUS: TABLE_EXISTS\n";
        echo "Action: Dropping...\n";
        
        // Disable checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // Backup
        $stmtB = $pdo->query("SHOW TABLES LIKE 'budget_plans_backup_20260102'");
        if ($stmtB->rowCount() == 0) {
            $pdo->exec("CREATE TABLE budget_plans_backup_20260102 AS SELECT * FROM budget_plans");
            echo "Backup created.\n";
        } else {
            echo "Backup already exists.\n";
        }
        
        // Drop references
        try { $pdo->exec("UPDATE disbursement_details SET plan_id = NULL WHERE plan_id IS NOT NULL"); } catch(Exception $e) {}
        try { $pdo->exec("ALTER TABLE budget_plans DROP FOREIGN KEY fk_budget_plans_parent"); } catch(Exception $e) {}
        try { $pdo->exec("ALTER TABLE budget_plans DROP FOREIGN KEY fk_budget_plans_division"); } catch(Exception $e) {}
        try { $pdo->exec("ALTER TABLE disbursement_details DROP FOREIGN KEY disbursement_details_ibfk_1"); } catch(Exception $e) {}
        try { $pdo->exec("ALTER TABLE disbursement_details DROP FOREIGN KEY fk_dd_plan"); } catch(Exception $e) {}
        
        // Drop
        $pdo->exec("DROP TABLE budget_plans");
        echo "Table dropped.\n";
        
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        echo "STATUS: DROPPED_NOW\n";
    }
    
    // Verify Backup
    $stmt = $pdo->query("SHOW TABLES LIKE 'budget_plans_backup_20260102'");
    if ($stmt->rowCount() > 0) {
        echo "BACKUP: EXISTS\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString();
}
