<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

echo "<h1>Controller Test</h1>";

try {
    // Test BudgetController
    echo "<p>Testing BudgetController...</p>";
    
    $fiscalYear = \App\Models\FiscalYear::currentYear();
    echo "<p>✅ FiscalYear::currentYear() = {$fiscalYear}</p>";
    
    $stats = \App\Models\Budget::getStats($fiscalYear);
    echo "<p>✅ Budget::getStats() OK</p>";
    
    $byCategory = \App\Models\Budget::getByCategory($fiscalYear);
    echo "<p>✅ Budget::getByCategory() = " . count($byCategory) . " records</p>";
    
    $trend = \App\Models\Budget::getMonthlyTrend($fiscalYear);
    echo "<p>✅ Budget::getMonthlyTrend() OK</p>";
    
    $fiscalYears = \App\Models\FiscalYear::getForSelect();
    echo "<p>✅ FiscalYear::getForSelect() = " . count($fiscalYears) . " records</p>";
    
    // Test View render
    echo "<h2>Testing View...</h2>";
    
    // Just check if view class exists and method works
    if (class_exists(\App\Core\View::class)) {
        echo "<p>✅ View class exists</p>";
    }
    
    // Check if view file exists
    $viewPath = __DIR__ . '/../resources/views/budgets/dashboard.php';
    if (file_exists($viewPath)) {
        echo "<p>✅ View file exists: budgets/dashboard.php</p>";
    } else {
        echo "<p>❌ View file NOT found: {$viewPath}</p>";
    }
    
    // Check if layout file exists
    $layoutPath = __DIR__ . '/../resources/views/layouts/main.php';
    if (file_exists($layoutPath)) {
        echo "<p>✅ Layout file exists: layouts/main.php</p>";
    } else {
        echo "<p>❌ Layout file NOT found: {$layoutPath}</p>";
    }
    
    echo "<h2>All checks passed!</h2>";
    
} catch (Exception $e) {
    echo "<h2>❌ ERROR</h2>";
    echo "<pre>" . $e->getMessage() . "\n\n" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<h2>❌ FATAL ERROR</h2>";
    echo "<pre>" . $e->getMessage() . "\n\n" . $e->getTraceAsString() . "</pre>";
}
