<?php
require_once __DIR__ . '/public/index.php';
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Auto Fix Session</title>
<style>
body { font-family: monospace; background: #1e293b; color: #f1f5f9; padding: 20px; }
.success { color: #4ade80; }
.error { color: #f87171; }
.warning { color: #fbbf24; }
</style>
</head>
<body>
<h1>🔧 Auto Fix Session Organization</h1>

<?php
// Step 1: Find current session
$session = \App\Core\Database::queryOne("SELECT * FROM disbursement_sessions WHERE id = 6");
if (!$session) {
    echo "<p class='error'>❌ Session 6 not found!</p>";
    exit;
}

$currentOrgId = $session['organization_id'];
echo "<h2>Step 1: Current Status</h2>";
echo "<p>Session 6 linked to Organization ID: <b>$currentOrgId</b></p>";

// Step 2: Check current org
$currentOrg = \App\Core\Database::queryOne("SELECT * FROM organizations WHERE id = ?", [$currentOrgId]);
echo "<p>Organization Name: <b>{$currentOrg['name_th']}</b></p>";

$currentItems = \App\Core\Database::queryOne(
    "SELECT COUNT(*) as c FROM budget_line_items WHERE division_id = ?", 
    [$currentOrgId]
);
echo "<p>Budget Items: <b>{$currentItems['c']}</b></p>";

if ($currentItems['c'] > 0) {
    echo "<p class='success'>✅ Session is already correctly linked!</p>";
    exit;
}

echo "<p class='warning'>⚠️ Current organization has 0 items. Searching for correct org...</p>";

// Step 3: Find ALL organizations with budget items
echo "<h2>Step 2: Finding Organizations with Budget Items</h2>";
$orgsWithItems = \App\Core\Database::query(
    "SELECT bli.division_id, o.name_th, o.org_type, COUNT(bli.id) as items
     FROM budget_line_items bli
     LEFT JOIN organizations o ON bli.division_id = o.id
     WHERE bli.fiscal_year = 2569
     GROUP BY bli.division_id
     ORDER BY items DESC"
);

echo "<table border='1' cellpadding='8'>";
echo "<tr><th>Division ID</th><th>Name</th><th>Type</th><th>Items</th></tr>";
foreach ($orgsWithItems as $org) {
    $name = $org['name_th'] ?? '(NULL - NOT IN organizations table!)';
    $type = $org['org_type'] ?? 'N/A';
    echo "<tr>";
    echo "<td>{$org['division_id']}</td>";
    echo "<td>{$name}</td>";
    echo "<td>{$type}</td>";
    echo "<td><b>{$org['items']}</b></td>";
    echo "</tr>";
}
echo "</table>";

// Step 4: Find matching org by name
echo "<h2>Step 3: Finding Match by Name</h2>";
$targetName = $currentOrg['name_th'];
echo "<p>Looking for organizations named: <b>$targetName</b></p>";

$matchingOrgs = \App\Core\Database::query(
    "SELECT o.id, o.name_th, COUNT(bli.id) as items
     FROM organizations o
     LEFT JOIN budget_line_items bli ON o.id = bli.division_id
     WHERE o.name_th = ?
     GROUP BY o.id
     ORDER BY items DESC",
    [$targetName]
);

echo "<table border='1' cellpadding='8'>";
echo "<tr><th>Org ID</th><th>Name</th><th>Items</th><th>Action</th></tr>";
foreach ($matchingOrgs as $org) {
    $action = $org['items'] > 0 ? '✅ CANDIDATE' : '❌ Empty';
    echo "<tr>";
    echo "<td>{$org['id']}</td>";
    echo "<td>{$org['name_th']}</td>";
    echo "<td>{$org['items']}</td>";
    echo "<td>$action</td>";
    echo "</tr>";
}
echo "</table>";

// Step 5: Auto Fix
$bestMatch = null;
foreach ($matchingOrgs as $org) {
    if ($org['items'] > 0) {
        $bestMatch = $org;
        break;
    }
}

if (!$bestMatch) {
    echo "<h2>Step 4: Result</h2>";
    echo "<p class='error'>❌ No matching organization with items found!</p>";
    echo "<p class='warning'>The budget items might be linked to a different organization entirely.</p>";
    echo "<p>Check the table above - items are linked to Division IDs that may not exist in organizations table.</p>";
} else {
    echo "<h2>Step 4: Applying Fix</h2>";
    echo "<p class='success'>✅ Found match: Org ID {$bestMatch['id']} with {$bestMatch['items']} items</p>";
    
    // Update session
    \App\Core\Database::query(
        "UPDATE disbursement_sessions SET organization_id = ? WHERE id = 6",
        [$bestMatch['id']]
    );
    
    echo "<p class='success'>✅ Updated Session 6 to Organization ID {$bestMatch['id']}</p>";
    echo "<p><a href='http://localhost/hr_budget/public/budgets/tracking/activities?session_id=6' style='color: #4ade80; font-size: 18px;'>🎯 Click here to test the Activities page</a></p>";
}
?>
</body>
</html>
