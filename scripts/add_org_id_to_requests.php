<?php
/**
 * Migration: Add org_id to budget_requests table
 */

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

use App\Core\Database;

try {
    $pdo = Database::getPdo();
    
    echo "Adding org_id column to budget_requests table...\n";
    
    // Check if column already exists
    $result = $pdo->query("SHOW COLUMNS FROM budget_requests LIKE 'org_id'");
    if ($result->rowCount() > 0) {
        echo "✓ Column org_id already exists.\n";
        exit(0);
    }
    
    // Add the column
    $sql = "ALTER TABLE budget_requests 
            ADD COLUMN org_id INT NULL AFTER created_by,
            ADD FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE SET NULL";
    
    $pdo->exec($sql);
    
    echo "✅ Successfully added org_id column to budget_requests table.\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
