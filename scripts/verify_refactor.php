<?php
// scripts/verify_refactor.php
require_once __DIR__ . '/../vendor/autoload.php';

// Mock Config
$config = require __DIR__ . '/../config/database.php';

echo "1. Checking Classes...\n";
$classes = [
    'App\Core\Database',
    'App\Core\Model',
    'App\Core\SimpleQueryBuilder',
    'App\Models\Plan',
    'App\Models\Project',
    'App\Models\Activity',
    'App\Controllers\DisbursementController',
    'App\Controllers\BudgetController'
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "OK: $class found.\n";
    } else {
        echo "FAIL: $class NOT found.\n";
        exit(1);
    }
}

echo "\n2. Checking Database Connection...\n";
try {
    $db = \App\Core\Database::getInstance();
    echo "OK: Connected.\n";
} catch (Exception $e) {
    echo "FAIL: Connection error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n3. Checking Models...\n";
try {
    echo "Querying Plans... ";
    $plans = \App\Models\Plan::getByFiscalYear(2568);
    echo "OK (" . count($plans) . " records)\n";

    echo "Querying Projects... ";
    $projects = \App\Models\Project::all();
    echo "OK (" . count($projects) . " records)\n";

    echo "Querying Activities... ";
    $activities = \App\Models\Activity::all();
    echo "OK (" . count($activities) . " records)\n";
} catch (Exception $e) {
    echo "FAIL: Model Query Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n4. Checking DisbursementController Logic (Dry Run)...\n";
// Instantiating controller shouldn't fail
$controller = new \App\Controllers\DisbursementController();
echo "OK: Controller instantiated.\n";

echo "\n5. Checking BudgetController Logic (Dry Run)...\n";
$budgetController = new \App\Controllers\BudgetController();
echo "OK: BudgetController instantiated.\n";

echo "\n----------------------------------\n";
echo "VERIFICATION SUCCESSFUL: Code structure is valid.\n";
