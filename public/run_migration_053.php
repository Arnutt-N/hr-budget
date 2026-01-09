<?php
/**
 * Run Migration 053 - Web Version
 */
header('Content-Type: text/plain; charset=utf-8');

// Load config
$dbConfigFile = __DIR__ . '/../config/database.php';
if (!file_exists($dbConfigFile)) {
    die("Config file not found: $dbConfigFile");
}
$config = require $dbConfigFile;

echo "=== Running Migration 053: Drop Legacy budget_plans (WEB) ===\n";

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
    
    // SQL content directly
    $sql = "
        SET FOREIGN_KEY_CHECKS = 0;

        -- Backup
        DROP TABLE IF EXISTS budget_plans_backup_20260102;
        CREATE TABLE budget_plans_backup_20260102 AS SELECT * FROM budget_plans;

        -- Update references
        UPDATE disbursement_details SET plan_id = NULL WHERE plan_id IS NOT NULL;

        -- Drop FKs if exist
        SELECT 'Dropping FKs...' as status;
        
        -- We use a stored procedure or just try-catch block in PHP to handle 'IF EXISTS' for FKs
        -- because MySQL < 8.0 doesn't support DROP FOREIGN KEY IF EXISTS syntax well in all versions
        -- But let's verify database version first or just use direct drops and ignore errors
    ";

    // Run backup first
    $pdo->exec("DROP TABLE IF EXISTS budget_plans_backup_20260102");
    $pdo->exec("CREATE TABLE budget_plans_backup_20260102 AS SELECT * FROM budget_plans");
    echo "[OK] Backup created: budget_plans_backup_20260102\n";

    // Update references
    $count = $pdo->exec("UPDATE disbursement_details SET plan_id = NULL WHERE plan_id IS NOT NULL");
    echo "[OK] Updated $count records in disbursement_details\n";

    // Drop FKs - tricky part, let's try raw SQL one by one and ignore errors
    try { $pdo->exec("ALTER TABLE budget_plans DROP FOREIGN KEY fk_budget_plans_parent"); } catch(Exception $e) {}
    try { $pdo->exec("ALTER TABLE budget_plans DROP FOREIGN KEY fk_budget_plans_division"); } catch(Exception $e) {}
    try { $pdo->exec("ALTER TABLE disbursement_details DROP FOREIGN KEY disbursement_details_ibfk_1"); } catch(Exception $e) {}
    try { $pdo->exec("ALTER TABLE disbursement_details DROP FOREIGN KEY fk_dd_plan"); } catch(Exception $e) {}
    echo "[OK] FK constraints cleanup attempted\n";
    
    // Drop table
    $pdo->exec("DROP TABLE IF EXISTS budget_plans");
    echo "[SUCCESS] Table 'budget_plans' dropped.\n";
    
    // Verify
    $stmt = $pdo->query("SHOW TABLES LIKE 'budget_plans'");
    if ($stmt->rowCount() == 0) {
        echo "[VERIFIED] Table 'budget_plans' is GONE.\n";
    } else {
        echo "[FAILED] Table 'budget_plans' STILL EXISTS.\n";
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
    http_response_code(500);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    http_response_code(500);
}
