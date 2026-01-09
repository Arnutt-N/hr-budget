<?php
/**
 * Finalize Schema Migration
 * Safely drops budget_plans table and logs result to file
 */
require_once __DIR__ . '/../config/database.php';
$config = require __DIR__ . '/../config/database.php';

$logFile = __DIR__ . '/../logs/migration_status.txt';
$log = "";

function appendLog($msg) {
    global $log;
    $log .= date('Y-m-d H:i:s') . " - $msg\n";
    echo $msg . "\n";
}

try {
    appendLog("Starting migration process...");
    
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'budget_plans'");
    if ($stmt->rowCount() == 0) {
        appendLog("Table 'budget_plans' already dropped. Skipping.");
    } else {
        appendLog("Table 'budget_plans' found. Proceeding with backup and drop.");
        
        // Disable FK checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // 2. Backup
        $stmt = $pdo->query("SHOW TABLES LIKE 'budget_plans_backup_20260102'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("CREATE TABLE budget_plans_backup_20260102 AS SELECT * FROM budget_plans");
            appendLog("Backup table 'budget_plans_backup_20260102' created.");
        } else {
            appendLog("Backup table already exists.");
        }

        // 3. Update references
        $pdo->exec("UPDATE disbursement_details SET plan_id = NULL WHERE plan_id IS NOT NULL");
        appendLog("Updated references in disbursement_details.");

        // 4. Drop FKs (try/catch for safety)
        $fks = ['fk_budget_plans_parent', 'fk_budget_plans_division'];
        foreach ($fks as $fk) {
            try {
                $pdo->exec("ALTER TABLE budget_plans DROP FOREIGN KEY $fk");
                appendLog("Dropped FK $fk");
            } catch (Exception $e) {
                // Ignore if not exists
            }
        }

        // 5. Drop Table
        $pdo->exec("DROP TABLE budget_plans");
        appendLog("Table 'budget_plans' dropped successfully.");
        
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    }

    // Final Verification
    $stmt = $pdo->query("SHOW TABLES LIKE 'budget_plans'");
    $exists = $stmt->rowCount() > 0;
    
    if (!$exists) {
        appendLog("VERIFICATION SUCCESS: Table is gone.");
        file_put_contents($logFile, "SUCCESS\n" . $log);
    } else {
        appendLog("VERIFICATION FAILED: Table still exists.");
        file_put_contents($logFile, "FAILED\n" . $log);
    }

} catch (Exception $e) {
    appendLog("ERROR: " . $e->getMessage());
    file_put_contents($logFile, "ERROR\n" . $log);
}
