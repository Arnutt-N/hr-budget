<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

echo "<h1>Budget Test</h1>";

try {
    // Test Database
    $db = \App\Core\Database::getInstance();
    echo "<p>✅ Database connected</p>";
    
    // Test FiscalYear model
    $years = \App\Models\FiscalYear::all();
    echo "<p>✅ FiscalYear::all() = " . count($years) . " records</p>";
    
    // Test BudgetCategory model
    $categories = \App\Models\BudgetCategory::all();
    echo "<p>✅ BudgetCategory::all() = " . count($categories) . " records</p>";
    
    // Test Budget model
    $budget = \App\Models\Budget::all(2568, 10, 0);
    echo "<p>✅ Budget::all(2568) = " . count($budget) . " records</p>";
    
    // Test Budget stats
    $stats = \App\Models\Budget::getStats(2568);
    echo "<p>✅ Budget::getStats(2568) = " . json_encode($stats) . "</p>";
    
    echo "<h2>All OK! The models work.</h2>";
    
} catch (Exception $e) {
    echo "<h2>❌ ERROR</h2>";
    echo "<pre>" . $e->getMessage() . "\n\n" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<h2>❌ FATAL ERROR</h2>";
    echo "<pre>" . $e->getMessage() . "\n\n" . $e->getTraceAsString() . "</pre>";
}
