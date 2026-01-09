<?php
/**
 * Migration: Create budget_trackings table
 * Run: php scripts/migrate_budget_trackings.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

use App\Core\Database;

echo "Creating budget_trackings table...\n";

$sql = "
CREATE TABLE IF NOT EXISTS budget_trackings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fiscal_year INT NOT NULL,
    budget_category_item_id INT NOT NULL,
    allocated DECIMAL(15,2) DEFAULT 0,
    transfer DECIMAL(15,2) DEFAULT 0,
    disbursed DECIMAL(15,2) DEFAULT 0,
    pending DECIMAL(15,2) DEFAULT 0,
    po DECIMAL(15,2) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_tracking (fiscal_year, budget_category_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $db = Database::getInstance();
    $db->exec($sql);
    echo "✓ Successfully created 'budget_trackings' table.\n";
} catch (PDOException $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
