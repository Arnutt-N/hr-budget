<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;
use App\Database; // Assuming you have a Database class for PDO connection

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Set timezone
date_default_timezone_set('Asia/Bangkok');

/**
 * Create the `budget_disbursements` table.
 * This table records the actual disbursement (expenditure) of items that have been approved.
 */
function createBudgetDisbursementsTable(PDO $pdo): void {
    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS budget_disbursements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    budget_request_item_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    disbursement_date DATE NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_disbursement_item FOREIGN KEY (budget_request_item_id) REFERENCES budget_request_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;
    $pdo->exec($sql);
    echo "Table `budget_disbursements` created or already exists.\n";
}

// Initialize DB connection (adjust according to your existing Database class)
$pdo = Database::getInstance();
createBudgetDisbursementsTable($pdo);
?>
