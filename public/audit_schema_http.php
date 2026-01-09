<?php
// public/audit_schema_http.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
require_once __DIR__ . '/../src/Core/Database.php';
use App\Core\Database;

// Helper to get table details
function getTableSchema($table) {
    try {
        $db = Database::getInstance();
        $cols = $db->query("SHOW FULL COLUMNS FROM $table")->fetchAll(PDO::FETCH_ASSOC);
        $keys = $db->query("SHOW KEYS FROM $table")->fetchAll(PDO::FETCH_ASSOC);
        return ['columns' => $cols, 'keys' => $keys];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

$actualTables = Database::query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

$report = [];
foreach ($actualTables as $table) {
    $report[$table] = getTableSchema($table);
}

header('Content-Type: application/json');
echo json_encode($report, JSON_PRETTY_PRINT);
