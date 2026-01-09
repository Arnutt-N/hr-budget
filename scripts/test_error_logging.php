<?php
/**
 * Test Error Logging
 */

require_once __DIR__ . '/../public/index.php';

use App\Core\Database;
use App\Core\ErrorHandler;

echo "Testing Error Logging...\n";

// 1. Manually log an exception
try {
    throw new Exception("Test Manual Exception for Verification");
} catch (Exception $e) {
    echo "Logging manual exception...\n";
    ErrorHandler::log($e);
}

// 2. Check Database
try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT * FROM error_logs ORDER BY id DESC LIMIT 1");
    $log = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($log) {
        echo "✅ SUCCESS: Found error log in database:\n";
        echo "   ID: " . $log['id'] . "\n";
        echo "   Message: " . $log['message'] . "\n";
        echo "   Type: " . $log['error_type'] . "\n";
        echo "   Time: " . $log['created_at'] . "\n";
    } else {
        echo "❌ FAILURE: No error logs found.\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR checking database: " . $e->getMessage() . "\n";
}
