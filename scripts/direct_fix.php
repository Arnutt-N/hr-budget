<?php
// Direct fix - outputs to file for debugging
ob_start();

require_once __DIR__ . '/public/index.php';

echo "=== DIRECT FIX ===\n\n";

// Find org with most items
$result = \App\Core\Database::query(
    "SELECT bli.division_id, COUNT(*) as cnt 
     FROM budget_line_items bli 
     WHERE fiscal_year = 2569 
     GROUP BY bli.division_id 
     ORDER BY cnt DESC 
     LIMIT 1"
);

if (empty($result)) {
    echo "ERROR: No budget items found!\n";
    file_put_contents(__DIR__ . '/fix_result.txt', ob_get_clean());
    die("See fix_result.txt\n");
}

$targetOrgId = $result[0]['division_id'];
$itemCount = $result[0]['cnt'];

echo "Found: Division ID $targetOrgId has $itemCount items\n";

// Get org name
$org = \App\Core\Database::queryOne("SELECT name_th FROM organizations WHERE id = ?", [$targetOrgId]);
$orgName = $org['name_th'] ?? 'Unknown';
echo "Organization: $orgName\n\n";

// Update session
\App\Core\Database::query("UPDATE disbursement_sessions SET organization_id = ? WHERE id = 6", [$targetOrgId]);

echo "UPDATED: Session 6 -> Org ID $targetOrgId\n";
echo "SUCCESS!\n\n";

echo "Test URL: http://localhost/hr_budget/public/budgets/tracking/activities?session_id=6\n";

$output = ob_get_clean();
file_put_contents(__DIR__ . '/fix_result.txt', $output);
echo $output;
echo "\nOutput saved to: fix_result.txt\n";
