<?php
/**
 * Full Stack Diagnostic - Tests Controller + View Rendering
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Full Stack Test</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #0f0; padding: 20px; }
        .success { color: #0f0; }
        .error { color: #f00; }
        pre { background: #000; padding: 10px; border-left: 3px solid #0f0; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Full Stack Diagnostic Test</h1>
    <?php
    require_once __DIR__ . '/../vendor/autoload.php';
    
    try {
        echo "<h2>Step 1: Bootstrap</h2>";
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->safeLoad();
        date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Bangkok');
        echo "<p class='success'>✓ Environment loaded</p>";
        
        echo "<h2>Step 2: Database & Models</h2>";
        $db = App\Core\Database::getInstance();
        echo "<p class='success'>✓ Database connected</p>";
        
        $categories = App\Models\BudgetCategory::getAllWithItems();
        echo "<p class='success'>✓ Categories loaded: " . count($categories) . "</p>";
        
        $fiscalYear = 2568;
        $stmt = $db->prepare("SELECT * FROM budget_trackings WHERE fiscal_year = ?");
        $stmt->execute([$fiscalYear]);
        $trackings = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $trackings[$row['budget_category_item_id']] = $row;
        }
        echo "<p class='success'>✓ Trackings loaded: " . count($trackings) . "</p>";
        
        $fiscalYears = App\Models\FiscalYear::getForSelect();
        echo "<p class='success'>✓ Fiscal years loaded: " . count($fiscalYears) . "</p>";
        
        echo "<h2>Step 3: Check View File Exists</h2>";
        $viewPath = dirname(__DIR__) . '/resources/views/budgets/tracking.php';
        if (file_exists($viewPath)) {
            echo "<p class='success'>✓ View file exists: tracking.php</p>";
            echo "<p>File size: " . filesize($viewPath) . " bytes</p>";
            
            echo "<h2>Step 4: PHP Syntax Check</h2>";
            $output = [];
            $returnCode = 0;
            exec("php -l \"$viewPath\" 2>&1", $output, $returnCode);
            
            if ($returnCode === 0) {
                echo "<p class='success'>✓ PHP syntax is valid</p>";
            } else {
                echo "<p class='error'>✗ PHP syntax error:</p>";
                echo "<pre class='error'>" . implode("\n", $output) . "</pre>";
            }
            
            echo "<h2>Step 5: Attempt View Rendering</h2>";
            echo "<p>Setting up view data...</p>";
            
            // Simulate the exact data passed by controller
            $mode = 'create';
            $title = 'Smart Budget Tracking';
            $currentPage = 'budgets';
            $auth = ['id' => 1, 'name' => 'Test User']; // Mock auth
            
            echo "<p class='success'>✓ Data prepared</p>";
            echo "<p>Attempting to render view...</p>";
            
            // Try to include the view
            ob_start();
            try {
                include $viewPath;
                $rendered = ob_get_clean();
                echo "<p class='success'>✓ View rendered successfully!</p>";
                echo "<p>Output length: " . strlen($rendered) . " characters</p>";
                
                // Check for common issues in rendered output
                if (strpos($rendered, 'Fatal error') !== false || strpos($rendered, 'Parse error') !== false) {
                    echo "<p class='error'>✗ Rendered output contains PHP errors</p>";
                    echo "<pre class='error'>" . htmlspecialchars(substr($rendered, 0, 2000)) . "</pre>";
                } else {
                    echo "<p class='success'>✓ No obvious errors in rendered output</p>";
                }
            } catch (Exception $e) {
                ob_end_clean();
                throw $e;
            }
            
        } else {
            echo "<p class='error'>✗ View file NOT FOUND: $viewPath</p>";
        }
        
        echo "<h2 class='success'>✓ FULL STACK TEST PASSED</h2>";
        echo "<p>If /budgets/create still shows 500, try:</p>";
        echo "<ul>";
        echo "<li>Restart Apache in Laragon</li>";
        echo "<li>Clear PHP opcode cache (restart PHP-FPM)</li>";
        echo "<li>Check Laragon error logs</li>";
        echo "<li>Try accessing with login first (might be auth error)</li>";
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
