<?php
/**
 * Migration: Create error_logs table
 */

require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

// Verify Database connection
echo "Checking database connection...\n";
try {
    $db = Database::getInstance();
    echo "Database connected successfully.\n";
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

$sql = "
CREATE TABLE IF NOT EXISTS error_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    error_type VARCHAR(50),
    message TEXT,
    file VARCHAR(255),
    line INT,
    trace TEXT,
    url VARCHAR(255),
    user_id INT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    echo "Creating error_logs table...\n";
    $db->exec($sql);
    echo "Table 'error_logs' created successfully (or already exists).\n";
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage() . "\n");
}
