<?php
require_once __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/../config/app.php';
use App\Core\Database;

try {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    $log = "";
    
    $pdo = Database::getInstance();
    $stmt = $pdo->query("SHOW TABLES LIKE 'budget_records'");
    $result = $stmt->fetchAll();
    
    if (count($result) > 0) {
        $log .= "Table 'budget_records' EXISTS.\n";
        
        // Check columns
        $stmt = $pdo->query("DESCRIBE budget_records");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $log .= "Columns: " . implode(', ', $columns) . "\n";
    } else {
        $log .= "Table 'budget_records' DOES NOT EXIST.\n";
        
        // Try to run migration
        $log .= "Attempting to run migration...\n";
        $sql = file_get_contents(__DIR__ . '/../database/migrations/007_create_budget_records.sql');
        $pdo->exec($sql);
        $log .= "Migration executed.\n";
    }
    
    file_put_contents(__DIR__ . '/db_log.txt', $log);
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
    file_put_contents(__DIR__ . '/db_log.txt', $error);
}
