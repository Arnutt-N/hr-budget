<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "=== Direct SQL Execution ===\n";

$pdo = Database::getInstance();

// Create organizations table
$sql1 = "CREATE TABLE IF NOT EXISTS organizations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    parent_id INT DEFAULT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    name_th VARCHAR(255) NOT NULL,
    abbreviation VARCHAR(100) DEFAULT NULL,
    budget_allocated DECIMAL(15,2) DEFAULT 0.00,
    level INT NOT NULL DEFAULT 0,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

echo "Creating organizations table...\n";
try {
    $pdo->exec($sql1);
    echo "OK!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Create target_types table
$sql2 = "CREATE TABLE IF NOT EXISTS target_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    name_th VARCHAR(255) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

echo "Creating target_types table...\n";
try {
    $pdo->exec($sql2);
    echo "OK!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Create budget_targets table
$sql3 = "CREATE TABLE IF NOT EXISTS budget_targets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    target_type_id INT NOT NULL,
    fiscal_year INT NOT NULL,
    quarter INT DEFAULT NULL,
    organization_id INT DEFAULT NULL,
    category_id INT DEFAULT NULL,
    target_percent DECIMAL(5,2) DEFAULT NULL,
    target_amount DECIMAL(15,2) DEFAULT NULL,
    notes TEXT,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

echo "Creating budget_targets table...\n";
try {
    $pdo->exec($sql3);
    echo "OK!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "=== Done ===\n";
