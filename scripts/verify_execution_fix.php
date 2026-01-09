<?php
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Models/BudgetExecution.php';
require_once __DIR__ . '/../src/Models/BudgetStructure.php';

use App\Models\BudgetExecution;
use App\Models\BudgetStructure;

$fiscalYear = 2568;

try {
    echo "Testing BudgetExecution::getKpiStats($fiscalYear)...\n";
    $stats = BudgetExecution::getKpiStats($fiscalYear);
    echo "Success! Total Budget Act: " . ($stats['total_budget_act'] ?? 0) . "\n\n";

    echo "Testing BudgetExecution::getWithStructure($fiscalYear)...\n";
    $data = BudgetExecution::getWithStructure($fiscalYear);
    echo "Success! Found " . count($data) . " records.\n\n";

    echo "Testing BudgetStructure::getDistinctPlans($fiscalYear)...\n";
    $plans = BudgetStructure::getDistinctPlans($fiscalYear);
    echo "Success! Found " . count($plans) . " plans.\n\n";

    echo "🎉 All tests passed. SQL queries are valid.\n";

} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
