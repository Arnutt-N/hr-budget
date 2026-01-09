<?php
/**
 * Test Execution Controller Directly
 * Simulates what index.php does but for /execution route only
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Direct Execution Test</h1>";
echo "<pre>";

try {
    // 1. Autoload
    echo "1. Loading autoloader...\n";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "   OK\n\n";
    
    // 2. Load .env
    echo "2. Loading .env...\n";
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
    echo "   OK\n\n";
    
    // 3. Set timezone
    date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Bangkok');
    
    // 4. Initialize Auth
    echo "3. Initializing Auth...\n";
    App\Core\Auth::init();
    echo "   OK\n\n";
    
    // 5. Check if logged in
    echo "4. Checking auth...\n";
    if (App\Core\Auth::check()) {
        echo "   User is logged in\n\n";
        
        // 6. Call BudgetExecutionController directly
        echo "5. Calling BudgetExecutionController::index()...\n";
        $controller = new App\Controllers\BudgetExecutionController();
        $controller->index();
        exit; // Let the view render
    } else {
        echo "   User NOT logged in - redirecting to login would happen\n\n";
        echo "   Please login first at: /hr_budget/public/login\n";
    }
    
} catch (Throwable $e) {
    echo "\n\n!!! ERROR !!!\n";
    echo "Type: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
