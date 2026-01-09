<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$logFile = __DIR__ . '/../migration_log.txt';
$sqlFile = __DIR__ . '/../database/migrations/012_file_management.sql';

function logMsg($msg) {
    global $logFile;
    file_put_contents($logFile, $msg . "\n", FILE_APPEND);
}

// Clear log
file_put_contents($logFile, "=== Migration Log " . date('Y-m-d H:i:s') . " ===\n");

if (!file_exists($sqlFile)) {
    logMsg("❌ SQL file not found: $sqlFile");
    exit(1);
}

$sql = file_get_contents($sqlFile);

try {
    $db = Database::getPdo();
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $count = 0;
    foreach ($statements as $stmt) {
        if (!empty($stmt) && !preg_match('/^--/', trim($stmt))) {
            try {
                $db->exec($stmt);
                $count++;
            } catch (Exception $e) {
                // Ignore "table already exists" errors roughly
                if (strpos($e->getMessage(), 'already exists') === false) {
                     logMsg("Warning on stmt: " . substr($stmt, 0, 50) . "... : " . $e->getMessage());
                }
            }
        }
    }
    
    logMsg("✅ Executed $count statements.");
    
    // Verify
    $tables = ['folders', 'files'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->fetch()) {
             logMsg("✅ Table '$table' exists.");
        } else {
             logMsg("❌ Table '$table' MISSING.");
        }
    }

} catch (Exception $e) {
    logMsg("❌ Fatal Error: " . $e->getMessage());
}
