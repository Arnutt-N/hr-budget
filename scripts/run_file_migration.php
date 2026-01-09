<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "=== Running File Management Migration (Document Archive) ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

$sqlFile = __DIR__ . '/../database/migrations/012_file_management.sql';

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
        $preview = substr($stmt, 0, 50);
        echo "Statement " . ($idx + 1) . ": " . $preview . "...\n";
        
        try {
            $db->exec($stmt);
            echo "  ✓ Executed successfully\n";
            $count++;
        } catch (PDOException $e) {
            // Ignore "table already exists" or "table doesn't exist" for DROP
            if (strpos($e->getMessage(), 'already exists') !== false || 
                strpos($e->getMessage(), "doesn't exist") !== false) {
                echo "  ⚠ Skipped (table state): " . $e->getMessage() . "\n";
            } else {
                echo "  ❌ Error: " . $e->getMessage() . "\n";
                throw $e;
            }
        }
    }
    
    echo "\n✅ Migration completed ($count statements executed)!\n";
    
    // Verify tables
    $tables = ['folders', 'files'];
    echo "\nVerifying tables:\n";
    foreach ($tables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'")->fetch();
        echo ($result ? "✓" : "✗") . " $table\n";
        
        if ($result) {
            // Show column count
            $cols = $db->query("SELECT COUNT(*) as cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table'")->fetch();
            echo "  → {$cols['cnt']} columns\n";
        }
    }
    
} catch (PDOException $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
