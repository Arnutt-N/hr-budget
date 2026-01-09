<?php
/**
 * Run New Schema Migration - Robust Version
 * Bypass App\Core\Database to ensure direct 127.0.0.1 connection
 */

// FORCE USE 127.0.0.1
$host = '127.0.0.1';
$db   = 'hr_budget';
$user = 'root';
$pass = ''; // Default Laragon password
$charset = 'utf8mb4';

echo "==============================================\n";
echo "  NEW SCHEMA MIGRATION (ROBUST)\n";
echo "  Target: {$host} - {$db}\n";
echo "==============================================\n\n";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=3306";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    echo "Connecting to database...\n";
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "✅ Connected!\n\n";
    
    // Read migration file
    $sqlFile = __DIR__ . '/../database/migrations/010_new_schema_dimensional.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Migration file not found: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Remove comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    
    // Split by semicolon
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "📋 Stmts: " . count($statements) . "\n\n";
    
    $success = 0;
    
    foreach ($statements as $idx => $stmt) {
        if (empty($stmt)) continue;
        
        $firstLine = strtok($stmt, "\n");
        $displayLine = substr($firstLine, 0, 80);
        
        echo sprintf("[%02d] %s...\n", $idx + 1, $displayLine);
        
        try {
            $pdo->exec($stmt);
            echo "     ✅ OK\n";
            $success++;
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            if (strpos($msg, 'Duplicate column name') !== false ||
                strpos($msg, 'already exists') !== false ||
                strpos($msg, "doesn't exist") !== false ||
                strpos($msg, 'Unknown column') !== false) {
                echo "     ⚠️ Skipped (Ignorable)\n";
                $success++;
            } else {
                echo "     ❌ Error: $msg\n";
            }
        }
    }
    
    echo "\n✅ Migration complete. ({$success} ops)\n";
    
} catch (Exception $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
