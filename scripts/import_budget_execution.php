<?php
/**
 * Excel/CSV Import Script for Budget Execution
 * 
 * Expected CSV format:
 * org_name, plan_name, output_name, activity_name, item_name, fiscal_year,
 * budget_act_amount, budget_allocated_amount, transfer_change_amount, budget_net_balance,
 * disbursed_amount, po_pending_amount, total_spending_amount, balance_amount,
 * percent_disburse_excl_po, percent_disburse_incl_po, datasource_row
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Core\Auth;

// Only allow authenticated users
session_start();
Auth::requireLogin();

echo "==============================================\n";
echo "  DIMENSIONAL BUDGET EXECUTION IMPORT\n";
echo "==============================================\n\n";

// Check if file is uploaded
if (!isset($_FILES['csv_file'])) {
    die("❌ Error: No CSV file uploaded\n");
}

$file = $_FILES['csv_file']['tmp_name'];

if (!file_exists($file)) {
    die("❌ Error: Uploaded file not found\n");
}

// Open CSV
if (($handle = fopen($file, "r")) === FALSE) {
    die("❌ Error: Cannot open CSV file\n");
}

// Setup database
$db = Database::getInstance();
$db->beginTransaction();

try {
    $rowCount = 0;
    $inserted = 0;
    $skipped = 0;
    
    // Skip header row
    fgetcsv($handle);
    
    while (($data = fgetcsv($handle)) !== FALSE) {
        $rowCount++;
        
        // Extract data
        $orgName = $data[0] ?? null;
        $planName = $data[1] ?? null;
        $outputName = $data[2] ?? null;
        $activityName = $data[3] ?? null;
        $itemName = $data[4] ?? null;
        $fiscalYear = $data[5] ?? null;
        $budgetAct = $data[6] ? (float)$data[6] : null;
        $allocated = $data[7] ? (float)$data[7] : null;
        $transfer = $data[8] ? (float)$data[8] : null;
        $netBalance = $data[9] ? (float)$data[9] : null;
        $disbursed = $data[10] ? (float)$data[10] : null;
        $poPending = $data[11] ? (float)$data[11] : null;
        $totalSpending = $data[12] ? (float)$data[12] : null;
        $balance = $data[13] ? (float)$data[13] : null;
        $percentExcl = $data[14] ? (float)$data[14] : null;
        $percentIncl = $data[15] ? (float)$data[15] : null;
        $sourceRow = $data[16] ?? null;
        
        // Validate required fields
        if (empty($itemName) || empty($fiscalYear)) {
            echo "⚠️  Row $rowCount: Skipped (missing item_name or fiscal_year)\n";
            $skipped++;
            continue;
        }
        
        // 1. Get or Create Organization
        $orgId = null;
        if ($orgName) {
            $existingOrg = Database::queryOne("SELECT org_id FROM dim_organization WHERE org_name = ?", [$orgName]);
            if ($existingOrg) {
                $orgId = $existingOrg['org_id'];
            } else {
                $orgId = Database::insert('dim_organization', ['org_name' => $orgName]);
                echo "   ✅ Created organization: $orgName\n";
            }
        }
        
        // 2. Get or Create Budget Structure
        $existingStructure = Database::queryOne(
            "SELECT structure_id FROM dim_budget_structure 
             WHERE plan_name = ? AND output_name = ? AND activity_name = ? AND item_name = ?",
            [$planName, $outputName, $activityName, $itemName]
        );
        
        if ($existingStructure) {
            $structureId = $existingStructure['structure_id'];
        } else {
            $structureId = Database::insert('dim_budget_structure', [
                'plan_name' => $planName,
                'output_name' => $outputName,
                'activity_name' => $activityName,
                'item_name' => $itemName,
                'org_id' => $orgId
            ]);
            echo "   ✅ Created structure: $itemName\n";
        }
        
        // 3. Insert Fact (allow duplicates for now - you may want to add unique constraint)
        Database::insert('fact_budget_execution', [
            'structure_id' => $structureId,
            'fiscal_year' => $fiscalYear,
            'budget_act_amount' => $budgetAct,
            'budget_allocated_amount' => $allocated,
            'transfer_change_amount' => $transfer,
            'budget_net_balance' => $netBalance,
            'disbursed_amount' => $disbursed,
            'po_pending_amount' => $poPending,
            'total_spending_amount' => $totalSpending,
            'balance_amount' => $balance,
            'percent_disburse_excl_po' => $percentExcl,
            'percent_disburse_incl_po' => $percentIncl,
            'datasource_row' => $sourceRow
        ]);
        
        $inserted++;
        echo "   ✅ Row $rowCount: Imported ($itemName)\n";
    }
    
    fclose($handle);
    
    // Commit transaction
    $db->commit();
    
    echo "\n==============================================\n";
    echo "  📊 Import Summary\n";
    echo "==============================================\n\n";
    echo "✅ Total Rows Processed: $rowCount\n";
    echo "✅ Successfully Imported: $inserted\n";
    echo "⚠️  Skipped: $skipped\n";
    echo "\n✅ Import Complete!\n";
    
} catch (Exception $e) {
    $db->rollback();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Transaction rolled back.\n";
    exit(1);
}
