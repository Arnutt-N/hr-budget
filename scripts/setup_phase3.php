<?php
/**
 * Setup Script for Phase 3: Budget Requests
 * usage: php scripts/setup_phase3.php
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Autoload Composer packages
require BASE_PATH . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Bangkok');

use App\Core\Database;

echo "Starting Phase 3 Database Setup...\n";

try {
    $pdo = Database::getPdo();

    // 1. Create budget_requests table
    echo "Creating 'budget_requests' table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS budget_requests (
        id INT PRIMARY KEY AUTO_INCREMENT,
        fiscal_year INT NOT NULL,
        request_title VARCHAR(255) NOT NULL,
        request_status ENUM('draft', 'pending', 'approved', 'rejected') DEFAULT 'draft',
        total_amount DECIMAL(15,2) DEFAULT 0.00,
        created_by INT,
        submitted_at TIMESTAMP NULL,
        approved_at TIMESTAMP NULL,
        rejected_at TIMESTAMP NULL,
        rejected_reason TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);

    // 2. Create budget_request_items table
    echo "Creating 'budget_request_items' table...\n";
    // Check if MySQL version supports generated columns (MySQL 5.7+), assumed yes given environment
    $sql = "CREATE TABLE IF NOT EXISTS budget_request_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        budget_request_id INT NOT NULL,
        parent_item_id INT DEFAULT NULL,
        item_code VARCHAR(50) DEFAULT NULL,
        item_name VARCHAR(255) NOT NULL,
        item_description TEXT,
        quantity INT DEFAULT 1,
        unit_price DECIMAL(15,2) DEFAULT 0.00,
        total_price DECIMAL(15,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
        item_level INT DEFAULT 0,
        sort_order INT DEFAULT 0,
        notes TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (budget_request_id) REFERENCES budget_requests(id) ON DELETE CASCADE,
        FOREIGN KEY (parent_item_id) REFERENCES budget_request_items(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);

    // 3. Create budget_request_approvals table
    echo "Creating 'budget_request_approvals' table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS budget_request_approvals (
        id INT PRIMARY KEY AUTO_INCREMENT,
        budget_request_id INT NOT NULL,
        action ENUM('created', 'submitted', 'approved', 'rejected', 'modified') NOT NULL,
        action_by INT,
        action_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        action_details TEXT,
        comments TEXT,
        FOREIGN KEY (budget_request_id) REFERENCES budget_requests(id) ON DELETE CASCADE,
        FOREIGN KEY (action_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);

    echo "✅ Phase 3 setup completed successfully!\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
