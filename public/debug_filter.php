<?php
// Direct database check - output to file
$host = 'localhost';
$dbname = 'hr_budget';
$username = 'root';
$password = '';

$output = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $output .= "=== Debug: Budget Plan Filtering Issue ===\n\n";
    
    // 1. Find the Division ID
    $output .= "1. Finding Division\n";
    $output .= "-------------------\n";
    $divisionName = 'กองบริหารทรัพยากรบุคคล';
    $stmt = $pdo->prepare("SELECT * FROM organizations WHERE name_th LIKE ?");
    $stmt->execute(["%{$divisionName}%"]);
    $org = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$org) {
        $output .= "ERROR: Organization '{$divisionName}' not found.\n";
        file_put_contents(__DIR__ . '/debug_output.txt', $output);
        echo "Output written to debug_output.txt";
        exit;
    }

    $output .= "Found Organization: {$org['name_th']} (ID: {$org['id']})\n\n";
    $orgId = $org['id'];

    // 2. Check Budget Line Items
    $output .= "2. Budget Line Items\n";
    $output .= "--------------------\n";
    $stmt = $pdo->prepare("SELECT DISTINCT activity_id FROM budget_line_items WHERE division_id = ?");
    $stmt->execute([$orgId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $output .= "Found " . count($rows) . " distinct activities linked to this division.\n";
    $activityIds = array_column($rows, 'activity_id');

    if (empty($activityIds)) {
        $output .= "WARNING: No activities found! 'skipFiltering' will trigger and show ALL plans.\n\n";
    } else {
        $output .= "Activity IDs: " . implode(', ', $activityIds) . "\n\n";
    }

    // 3. Check what Plans these activities belong to
    if (!empty($activityIds)) {
        $output .= "3. Parent Plans (What these activities belong to)\n";
        $output .= "-------------------------------------------------\n";
        $placeholders = implode(',', array_fill(0, count($activityIds), '?'));
        
        $stmt = $pdo->prepare("SELECT p.id, p.name_th, p.plan_type, p.level, p.parent_id, parent.name_th as parent_name 
                FROM budget_plans p 
                LEFT JOIN budget_plans parent ON p.parent_id = parent.id
                WHERE p.id IN ($placeholders)
                ORDER BY p.id");
        $stmt->execute($activityIds);
        $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($plans as $p) {
            $output .= sprintf("  [%d] %s (Type: %s, Level: %d, Parent: %s)\n", 
                $p['id'], $p['name_th'], $p['plan_type'], $p['level'], $p['parent_name'] ?? 'None');
        }
        $output .= "\n";
        
        // 4. Trace to root programs
        $output .= "4. Tracing to Root Programs\n";
        $output .= "----------------------------\n";
        $rootProgramIds = [];
        
        foreach ($plans as $p) {
            // Trace up the parent chain
            $currentId = $p['id'];
            $safety = 0;
            $chain = [$p['name_th']];
            
            while ($currentId && $safety < 10) {
                $stmt2 = $pdo->prepare("SELECT id, name_th, level, parent_id FROM budget_plans WHERE id = ?");
                $stmt2->execute([$currentId]);
                $current = $stmt2->fetch(PDO::FETCH_ASSOC);
                
                if (!$current) break;
                
                if ($current['level'] == 1) {
                    $rootProgramIds[$current['id']] = $current['name_th'];
                    $chain[] = $current['name_th'];
                    break;
                }
                
                if ($current['parent_id']) {
                    $currentId = $current['parent_id'];
                } else {
                    break;
                }
                
                $safety++;
            }
            
            $output .= "  " . implode(' ← ', $chain) . "\n";
        }
        
        $output .= "\n Root Programs that SHOULD be shown:\n";
        foreach ($rootProgramIds as $id => $name) {
            $output .= "  [{$id}] {$name}\n";
        }
        $output .= "\n";
    }

    // 5. Total Root Plans
    $output .= "5. Total Root Plans in Database\n";
    $output .= "--------------------------------\n";
    $stmt = $pdo->query("SELECT id, name_th FROM budget_plans WHERE level = 1 ORDER BY id");
    $allRoots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $output .= "Total Level 1 Plans: " . count($allRoots) . "\n";
    foreach ($allRoots as $ar) {
        $output .= "  [{$ar['id']}] {$ar['name_th']}\n";
    }
    $output .= "\n";

    // 6. Session #14
    $output .= "6. Session #14 Details\n";
    $output .= "----------------------\n";
    $stmt = $pdo->prepare("SELECT * FROM disbursement_sessions WHERE id = 14");
    $stmt->execute();
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($session) {
        $output .= "  ID: {$session['id']}\n";
        $output .= "  Organization ID: {$session['organization_id']}\n";
        $output .= "  Fiscal Year: {$session['fiscal_year']}\n";
        $output .= "  Record Month: {$session['record_month']}\n";
    } else {
        $output .= "  Session #14 not found\n";
    }
    $output .= "\n";
    
    // 7. DIAGNOSIS
    $output .= "7. DIAGNOSIS\n";
    $output .= "============\n";
    if (empty($activityIds)) {
        $output .= "ROOT CAUSE: No budget_line_items found for this division!\n";
        $output .= "This triggers the 'skipFiltering' fallback in BudgetController::activities(),\n";
        $output .= "which shows ALL plans instead of filtering by division.\n\n";
        $output .= "SOLUTION: Either:\n";
        $output .= "  A) Add budget_line_items for this division (recommended)\n";
        $output .= "  B) Remove the skipFiltering logic to show empty list instead\n";
    } else {
        $output .= "Budget line items exist. Check if correct activities are linked.\n";
        $output .= "If wrong root program appears, data is incorrectly linked.\n";
    }

} catch (PDOException $e) {
    $output .= "\nERROR: " . $e->getMessage() . "\n";
}

// Write to file
file_put_contents(__DIR__ . '/debug_output.txt', $output);
echo "<h2>Analysis Complete</h2>";
echo "<p>Output written to: <code>" . __DIR__ . "/debug_output.txt</code></p>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";
