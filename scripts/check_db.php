<?php
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../config/database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    // Check budget_transactions
    echo "--- Checking budget_transactions ---\n";
    try {
        $trans = Database::query("SELECT * FROM budget_transactions LIMIT 5");
        if (empty($trans)) {
            echo "Table exists but empty.\n";
        } else {
            echo "Found " . count($trans) . " rows. Sample:\n";
            print_r($trans[0]);
        }
    } catch (Exception $e) {
        echo "Table budget_transactions DOES NOT EXIST or error: " . $e->getMessage() . "\n";
    }

    // Check budget_trackings
    echo "\n--- Checking budget_trackings ---\n";
    try {
        $track = Database::query("SELECT * FROM budget_trackings LIMIT 5");
        if (empty($track)) {
            echo "Table exists but empty.\n";
        } else {
            echo "Found " . count($track) . " rows. Sample:\n";
            print_r($track[0]);
        }
    } catch (Exception $e) {
        echo "Table budget_trackings error: " . $e->getMessage() . "\n";
    }

    // Check budget_records
    echo "\n--- Checking budget_records ---\n";
    try {
        $recs = Database::query("SELECT * FROM budget_records LIMIT 5");
        if (empty($recs)) {
            echo "Table exists but empty.\n";
        } else {
            echo "Found " . count($recs) . " rows. Sample:\n";
            print_r($recs[0]);
        }
    } catch (Exception $e) {
        echo "Table budget_records error: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "DB Connection Error: " . $e->getMessage();
}
