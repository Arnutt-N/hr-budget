<?php
// public/debug_dashboard.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Bangkok');

use App\Core\Auth;
use App\Models\BudgetExecution;
use App\Models\Organization;

// Initialize Auth (files usually rely on DB implicitly via Model usage)
// Note: Core\Database is usually static and lazy loaded or init in Auth?
// Let's assume lazy load.

echo "<h1>Debug Dashboard</h1>";

try {
    echo "<h2>1. Testing BudgetExecution::getKpiStats</h2>";
    $stats = BudgetExecution::getKpiStats(2568);
    echo "<pre>"; print_r($stats); echo "</pre>";
} catch (Throwable $e) {
    echo "<div style='color:red'>Error in getKpiStats: " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

try {
    echo "<h2>2. Testing BudgetExecution::getWithStructure</h2>";
    $data = BudgetExecution::getWithStructure(2568);
    echo "Count: " . count($data) . "<br>";
    if (!empty($data)) {
        echo "<pre>"; print_r($data[0]); echo "</pre>";
    }
} catch (Throwable $e) {
    echo "<div style='color:red'>Error in getWithStructure: " . $e->getMessage() . "</div>";
}

try {
    echo "<h2>3. Testing Organization::getAll</h2>";
    $orgs = Organization::getAll();
    echo "Count: " . count($orgs) . "<br>";
} catch (Throwable $e) {
    echo "<div style='color:red'>Error in Organization::getAll: " . $e->getMessage() . "</div>";
}
