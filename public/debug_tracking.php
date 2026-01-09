<?php
/**
 * PUBLIC Diagnostic Page - NO AUTH REQUIRED
 * Access via: /public/debug_tracking.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tracking Debug</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #0f0; padding: 20px; }
        .success { color: #0f0; }
        .error { color: #f00; }
        pre { background: #000; padding: 10px; border-left: 3px solid #0f0; }
    </style>
</head>
<body>
    <h1>Budget Tracking Diagnostic</h1>
    <?php
    require_once __DIR__ . '/../vendor/autoload.php';
    
    try {
        echo "<p class='success'>✓ Autoload successful</p>";
        
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->safeLoad();
        echo "<p class='success'>✓ Environment loaded</p>";
        
        date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Bangkok');
        
        $db = App\Core\Database::getInstance();
        echo "<p class='success'>✓ Database connected</p>";
        
        echo "<h2>Test 1: Get Categories with Items</h2>";
        $categories = App\Models\BudgetCategory::getAllWithItems();
        echo "<p class='success'>✓ Found " . count($categories) . " categories</p>";
        
        if (!empty($categories)) {
            echo "<pre>";
            print_r($categories[0]);
            echo "</pre>";
        }
        
        echo "<h2>Test 2: Check budget_trackings table</h2>";
        $stmt = $db->query("SHOW TABLES LIKE 'budget_trackings'");
        $exists = $stmt->fetch();
        
        if ($exists) {
            echo "<p class='success'>✓ Table 'budget_trackings' exists</p>";
            
            $stmt = $db->query("SELECT COUNT(*) as cnt FROM budget_trackings");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p class='success'>✓ Table has " . $result['cnt'] . " records</p>";
        } else {
            echo "<p class='error'>✗ Table 'budget_trackings' DOES NOT EXIST</p>";
            echo "<p>Run: <code>php scripts/migrate_budget_trackings.php</code></p>";
        }
        
        echo "<h2>Test 3: Tracking Query</h2>";
        $fiscalYear = 2568;
        $stmt = $db->prepare("SELECT * FROM budget_trackings WHERE fiscal_year = ?");
        $stmt->execute([$fiscalYear]);
        $trackings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p class='success'>✓ Found " . count($trackings) . " tracking records for year $fiscalYear</p>";
        
        echo "<h2 class='success'>✓ ALL TESTS PASSED</h2>";
        echo "<p>The controller and model code should work.</p>";
        echo "<p>If /budgets/create still shows 500, the issue is in:</p>";
        echo "<ul>";
        echo "<li>Auth/Permission checks blocking access</li>";
        echo "<li>View rendering (tracking.php syntax)</li>";
        echo "<li>Router configuration</li>";
        echo "</ul>";
        
    } catch (Exception $e) {
        echo "<h2 class='error'>ERROR DETECTED:</h2>";
        echo "<pre class='error'>";
        echo "Message: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
        echo "Trace:\n" . $e->getTraceAsString();
        echo "</pre>";
    }
    ?>
</body>
</html>
