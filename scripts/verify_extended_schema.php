<?php
require_once 'public/db-api.php';

use App\Core\Database;

echo "Verifying Extended Schema...\n\n";

$tables = [
    'divisions',
    'budget_plans',
    'fund_sources',
    'budget_allocations',
    'budget_transfers',
    'budget_monthly_snapshots',
    'po_commitments',
    'disbursements'
];

$views = [
    'v_budget_summary_by_plan',
    'v_budget_summary_by_fund',
    'v_transfer_summary'
];

$all_ok = true;

// 1. Check Tables
echo "Checking Tables:\n";
foreach ($tables as $table) {
    try {
        $result = Database::query("SHOW TABLES LIKE ?", [$table]);
        if (!empty($result)) {
            echo "[OK] Table '$table' exists.\n";
            
            // Check sample data for specific tables
            if ($table === 'divisions') {
                $count = Database::query("SELECT COUNT(*) as c FROM divisions")[0]['c'];
                echo "   - Row count: $count (Expected: 6)\n";
            }
            if ($table === 'fund_sources') {
                $count = Database::query("SELECT COUNT(*) as c FROM fund_sources")[0]['c'];
                echo "   - Row count: $count (Expected: 5)\n";
            }
            
        } else {
            echo "[FAIL] Table '$table' MISSING!\n";
            $all_ok = false;
        }
    } catch (Exception $e) {
        echo "[ERROR] checking '$table': " . $e->getMessage() . "\n";
        $all_ok = false;
    }
}

echo "\nChecking Views:\n";
foreach ($views as $view) {
    try {
        // For views, SHOW TABLES also returns them
        $result = Database::query("SHOW TABLES LIKE ?", [$view]);
        if (!empty($result)) {
            echo "[OK] View '$view' exists.\n";
        } else {
            echo "[FAIL] View '$view' MISSING!\n";
            $all_ok = false;
        }
    } catch (Exception $e) {
        echo "[ERROR] checking '$view': " . $e->getMessage() . "\n";
        $all_ok = false;
    }
}

if ($all_ok) {
    echo "\n✅ VERIFICATION SUCCESSFUL: Schema applied correctly.\n";
} else {
    echo "\n❌ VERIFICATION FAILED: Some tables or views are missing.\n";
}
