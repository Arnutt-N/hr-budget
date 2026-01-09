<?php
require_once 'public/db-api.php';
require_once 'src/Models/Division.php';
require_once 'src/Models/BudgetPlan.php';
require_once 'src/Models/BudgetAllocation.php';

use App\Models\Division;
use App\Models\BudgetPlan;
use App\Models\BudgetAllocation;

echo "Verifying Seeded Data...\n\n";

// 1. Verify Division
$div = Division::findByCode('STRATEGY');
echo "Division: " . ($div ? $div['name_th'] : 'FAIL') . "\n";

// 2. Verify Hierarchy
echo "Hierarchy Check:\n";
$plans = BudgetPlan::all();
foreach ($plans as $plan) {
    echo str_repeat("  ", $plan['level'] - 1) . "- [" . $plan['code'] . "] " . $plan['name_th'] . "\n";
}

// 3. Verify Allocation
echo "\nChecking Allocation:\n";
// Find project ID first
$proj = null;
foreach ($plans as $p) {
    if ($p['code'] === 'PROJ-68-001') {
        $proj = $p;
        break;
    }
}

if ($proj) {
    $alloc = BudgetAllocation::findByParams(2568, $proj['id'], 2); // 2 = salary
    if ($alloc) {
        echo "[OK] Allocation Found: " . number_format($alloc['allocated_received'], 2) . " THB\n";
    } else {
        echo "[FAIL] Allocation Missing for Project " . $proj['code'] . "\n";
    }
} else {
    echo "[FAIL] Project PROJ-68-001 not found.\n";
}
