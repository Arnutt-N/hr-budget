<?php
/**
 * Debug Execution Route
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Execution Route</h1>";
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
    
    // 3. Check if BudgetExecutionController exists
    echo "3. Checking BudgetExecutionController class...\n";
    $controllerPath = __DIR__ . '/../src/Controllers/BudgetExecutionController.php';
    echo "   File path: $controllerPath\n";
    echo "   File exists: " . (file_exists($controllerPath) ? 'YES' : 'NO') . "\n";
    
    if (file_exists($controllerPath)) {
        require_once $controllerPath;
        echo "   Class exists: " . (class_exists('App\\Controllers\\BudgetExecutionController') ? 'YES' : 'NO') . "\n";
    }
    echo "\n";
    
    // 4. Check routes/web.php
    echo "4. Checking routes/web.php...\n";
    $routesPath = __DIR__ . '/../routes/web.php';
    echo "   File exists: " . (file_exists($routesPath) ? 'YES' : 'NO') . "\n";
    
    $routesContent = file_get_contents($routesPath);
    echo "   Contains /execution route: " . (strpos($routesContent, '/execution') !== false ? 'YES' : 'NO') . "\n";
    echo "   Contains BudgetExecutionController: " . (strpos($routesContent, 'BudgetExecutionController') !== false ? 'YES' : 'NO') . "\n";
    echo "\n";
    
    // 5. Test instantiating the controller
    echo "5. Testing BudgetExecutionController instantiation...\n";
    if (class_exists('App\\Controllers\\BudgetExecutionController')) {
        $controller = new App\Controllers\BudgetExecutionController();
        echo "   Instantiation: OK\n";
    } else {
        echo "   ERROR: Class not found!\n";
    }
    
    echo "\n=== END DEBUG ===\n";
    
} catch (Throwable $e) {
    echo "\n\n!!! ERROR !!!\n";
    echo "Type: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
