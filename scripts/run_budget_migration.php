<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "=== Running Budget List Enhancements Migration ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

$sqlFile = __DIR__ . '/../database/migrations/013_budget_list_enhancements.sql';

if (!file_exists($sqlFile)) {
    die("❌ SQL file not found: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);

try {
    $db = Database::getPdo();
    
    // Remove comments and split by semicolon more carefully
    $lines = explode("\n", $sql);
    $currentStatement = '';
    $statements = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip empty lines and SQL comments
        if (empty($line) || strpos($line, '--') === 0) {
            continue;
        }
        
        $currentStatement .= ' ' . $line;
        
        // If line ends with semicolon, we have a complete statement
        if (substr($line, -1) === ';') {
            $stmt = trim($currentStatement);
            if (!empty($stmt)) {
                $statements[] = $stmt;
            }
            $currentStatement = '';
        }
    }
    
    echo "Found " . count($statements) . " SQL statements\n\n";
    
    $count = 0;
    foreach ($statements as $idx => $stmt) {
        // $preview = substr($stmt, 0, 50);
        echo "Statement " . ($idx + 1) . ":\n" . $stmt . "\n";
        
        try {
            $db->exec($stmt);
            echo "  ✓ Executed successfully\n";
            $count++;
        } catch (PDOException $e) {
             // Ignore "Duplicate column name"
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "  ⚠ Skipped (column already exists): " . $e->getMessage() . "\n";
            } else {
                echo "  ❌ Error: " . $e->getMessage() . "\n";
                throw $e;
            }
        }
    }
    
    echo "\n✅ Migration completed ($count statements executed)!\n";
    
    // Verify columns
    echo "\nVerifying fact_budget_execution columns:\n";
    $columns = $db->query("SHOW COLUMNS FROM fact_budget_execution")->fetchAll(PDO::FETCH_COLUMN);
    $required = ['record_date', 'request_amount'];
    
    foreach ($required as $col) {
        if (in_array($col, $columns)) {
            echo "✓ Column '$col' exists\n";
        } else {
            echo "❌ Column '$col' MISSING\n";
        }
    }
    
} catch (PDOException $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
