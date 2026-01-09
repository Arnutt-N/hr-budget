<?php
require_once __DIR__ . '/public/index.php';

echo "=== AUTO FIX SESSION ORGANIZATION ===\n\n";

// Step 1: Current session
$session = \App\Core\Database::queryOne("SELECT * FROM disbursement_sessions WHERE id = 6");
if (!$session) {
    die("ERROR: Session 6 not found!\n");
}

$currentOrgId = $session['organization_id'];
echo "Step 1: Current Status\n";
echo "  Session 6 → Org ID: $currentOrgId\n";

$currentOrg = \App\Core\Database::queryOne("SELECT * FROM organizations WHERE id = ?", [$currentOrgId]);
echo "  Org Name: {$currentOrg['name_th']}\n";

$currentItems = \App\Core\Database::queryOne(
    "SELECT COUNT(*) as c FROM budget_line_items WHERE division_id = ?", 
    [$currentOrgId]
);
echo "  Budget Items: {$currentItems['c']}\n\n";

if ($currentItems['c'] > 0) {
    echo "SUCCESS: Session already correctly linked!\n";
    exit(0);
}

echo "WARNING: Current org has 0 items. Finding correct org...\n\n";

// Step 2: Find all division_ids with items
echo "Step 2: Organizations with Budget Items (FY 2569)\n";
$orgsWithItems = \App\Core\Database::query(
    "SELECT bli.division_id, COUNT(bli.id) as items
     FROM budget_line_items bli
     WHERE bli.fiscal_year = 2569
     GROUP BY bli.division_id
     ORDER BY items DESC"
);

foreach ($orgsWithItems as $org) {
    $orgInfo = \App\Core\Database::queryOne("SELECT name_th FROM organizations WHERE id = ?", [$org['division_id']]);
    $name = $orgInfo['name_th'] ?? '(NOT IN organizations table)';
    echo "  Division ID {$org['division_id']}: {$org['items']} items - $name\n";
}
echo "\n";

// Step 3: Find matching org
echo "Step 3: Finding organizations named '{$currentOrg['name_th']}'\n";
$matchingOrgs = \App\Core\Database::query(
    "SELECT o.id, o.name_th, COUNT(bli.id) as items
     FROM organizations o
     LEFT JOIN budget_line_items bli ON o.id = bli.division_id
     WHERE o.name_th = ?
     GROUP BY o.id
     ORDER BY items DESC",
    [$currentOrg['name_th']]
);

$bestMatch = null;
foreach ($matchingOrgs as $org) {
    $status = $org['items'] > 0 ? '[HAS ITEMS]' : '[EMPTY]';
    echo "  Org ID {$org['id']}: {$org['items']} items $status\n";
    if ($org['items'] > 0 && !$bestMatch) {
        $bestMatch = $org;
    }
}
echo "\n";

// Step 4: Apply fix
if (!$bestMatch) {
    echo "ERROR: No matching organization with items found!\n";
    echo "Budget items are linked to division_ids that don't match any org with the same name.\n";
    exit(1);
}

echo "Step 4: Applying Fix\n";
echo "  Found: Org ID {$bestMatch['id']} with {$bestMatch['items']} items\n";

\App\Core\Database::query(
    "UPDATE disbursement_sessions SET organization_id = ? WHERE id = 6",
    [$bestMatch['id']]
);

echo "  SUCCESS: Updated Session 6 → Org ID {$bestMatch['id']}\n\n";
echo "=== FIX COMPLETED ===\n";
echo "Test at: http://localhost/hr_budget/public/budgets/tracking/activities?session_id=6\n";
