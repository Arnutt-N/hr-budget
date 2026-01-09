<?php
$files = [
    'src/Models/BudgetExecution.php',
    'src/Controllers/BudgetController.php',
    'routes/web.php',
    'src/Models/BudgetPlan.php',
    'src/Controllers/BudgetPlanController.php'
];

$search = 'budget_plans';

foreach ($files as $file) {
    echo "Checking $file...\n";
    if (!file_exists(__DIR__ . '/../' . $file)) {
        echo "  File not found.\n";
        continue;
    }
    
    $lines = file(__DIR__ . '/../' . $file);
    foreach ($lines as $i => $line) {
        if (strpos($line, $search) !== false) {
            echo "  Line " . ($i + 1) . ": " . trim($line) . "\n";
        }
    }
    echo "\n";
}
