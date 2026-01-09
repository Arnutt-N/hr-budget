<?php
// Test Script to Debug Budget Tracking Page
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Bangkok');

echo "=== Budget Tracking Debug Test ===\n\n";

try {
    echo "1. Testing Database Connection...\n";
    $db = App\Core\Database::getInstance();
    echo "   ✓ Database connected\n\n";
    
    echo "2. Testing BudgetCategory::getAllWithItems()...\n";
    $categories = App\Models\BudgetCategory::getAllWithItems();
    echo "   ✓ Found " . count($categories) . " categories\n";
    if (!empty($categories)) {
        $cat = $categories[0];
        echo "   First category: " . ($cat['name'] ?? $cat['name_th'] ?? 'unnamed') . "\n";
        echo "   Items count: " . count($cat['items'] ?? []) . "\n";
    }
    echo "\n";
    
    echo "3. Testing budget_trackings table...\n";
    $stmt = $db->query("SELECT COUNT(*) as cnt FROM budget_trackings");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✓ Table exists with " . $result['cnt'] . " records\n\n";
    
    echo "4. Testing tracking query...\n";
    $fiscalYear = 2568;
    $stmt = $db->prepare("SELECT * FROM budget_trackings WHERE fiscal_year = ?");
    $stmt->execute([$fiscalYear]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   ✓ Found " . count($rows) . " tracking records for year $fiscalYear\n\n";
    
    echo "=== ALL TESTS PASSED ===\n";
    echo "The page should work. If it doesn't, check:\n";
    echo "1. File permissions for resources/views/budgets/tracking.php\n";
    echo "2. PHP error logs in Laragon\n";
    echo "3. Browser console for JavaScript errors\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR FOUND:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
