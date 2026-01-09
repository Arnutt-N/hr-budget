<?php
// Load database config manually or use the system's core
require_once 'src/Core/Database.php';

// Mock $_ENV if needed or load .env (though this script might run in a context where it's not loaded)
// For simplicity, let's just use the config file directly and assume defaults if null

$config = include 'config/database.php';
$host = $config['host'] ?? 'localhost';
$db   = $config['database'] ?? 'hr_budget';
$user = $config['username'] ?? 'root';
$pass = $config['password'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents('database/migrations/create_source_of_truth_mappings.sql');
    $pdo->exec($sql);
    echo "Migration successful: source_of_truth_mappings table created.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
