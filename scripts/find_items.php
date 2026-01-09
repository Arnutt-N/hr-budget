<?php
require_once __DIR__ . '/public/index.php';
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Find Budget Items</title>
<style>
body { font-family: monospace; background: #1e293b; color: #f1f5f9; padding: 20px; }
table { border-collapse: collapse; margin: 20px 0; width: 100%; }
td, th { border: 1px solid #475569; padding: 8px; text-align: left; }
.has-items { background: #166534; }
</style>
</head>
<body>
<h1>🔍 Where are the Budget Items?</h1>

<?php
// Find ALL orgs with items
$orgsWithItems = \App\Core\Database::query(
    "SELECT o.id, o.name_th, o.org_type, COUNT(bli.id) as item_count 
     FROM organizations o 
     LEFT JOIN budget_line_items bli ON o.id = bli.division_id 
     GROUP BY o.id 
     HAVING item_count > 0
     ORDER BY item_count DESC"
);

echo "<h2>Organizations with Budget Items (Total: " . count($orgsWithItems) . ")</h2>";
echo "<table>";
echo "<tr><th>ID</th><th>Type</th><th>Name</th><th>Items</th></tr>";
foreach ($orgsWithItems as $o) {
    echo "<tr class='has-items'>";
    echo "<td>{$o['id']}</td>";
    echo "<td>{$o['org_type']}</td>";
    echo "<td>{$o['name_th']}</td>";
    echo "<td><b>{$o['item_count']}</b></td>";
    echo "</tr>";
}
echo "</table>";

// Find duplicates
echo "<h2>Duplicate Organization Names</h2>";
$duplicates = \App\Core\Database::query(
    "SELECT name_th, COUNT(*) as count 
     FROM organizations 
     GROUP BY name_th 
     HAVING count > 1"
);

foreach ($duplicates as $dup) {
    echo "<h3>{$dup['name_th']} (Duplicated {$dup['count']} times)</h3>";
    
    $instances = \App\Core\Database::query(
        "SELECT o.id, o.org_type, o.parent_id, COUNT(bli.id) as items
         FROM organizations o
         LEFT JOIN budget_line_items bli ON o.id = bli.division_id
         WHERE o.name_th = ?
         GROUP BY o.id",
        [$dup['name_th']]
    );
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Type</th><th>Parent ID</th><th>Items</th><th>Action</th></tr>";
    foreach ($instances as $inst) {
        $class = $inst['items'] > 0 ? 'has-items' : '';
        echo "<tr class='$class'>";
        echo "<td>{$inst['id']}</td>";
        echo "<td>{$inst['org_type']}</td>";
        echo "<td>{$inst['parent_id']}</td>";
        echo "<td>{$inst['items']}</td>";
        echo "<td>" . ($inst['items'] > 0 ? '✅ KEEP' : '❌ DELETE') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check Session 6
echo "<h2>Session 6 Status</h2>";
$session = \App\Core\Database::queryOne("SELECT * FROM disbursement_sessions WHERE id = 6");
if ($session) {
    $orgId = $session['organization_id'];
    $org = \App\Core\Database::queryOne("SELECT * FROM organizations WHERE id = ?", [$orgId]);
    $items = \App\Core\Database::queryOne("SELECT COUNT(*) as c FROM budget_line_items WHERE division_id = ?", [$orgId]);
    
    echo "<p><b>Session Organization:</b> ID $orgId - {$org['name_th']}</p>";
    echo "<p><b>Budget Items:</b> {$items['c']}</p>";
    
    if ($items['c'] == 0) {
        echo "<p style='color: #fbbf24;'>⚠️ WARNING: Session linked to org with 0 items!</p>";
        
        // Find correct org
        $correctOrg = \App\Core\Database::queryOne(
            "SELECT o.id, o.name_th, COUNT(bli.id) as items
             FROM organizations o
             JOIN budget_line_items bli ON o.id = bli.division_id
             WHERE o.name_th = ?
             GROUP BY o.id
             ORDER BY items DESC
             LIMIT 1",
            [$org['name_th']]
        );
        
        if ($correctOrg) {
            echo "<p style='color: #4ade80;'>✅ Found correct org: ID {$correctOrg['id']} with {$correctOrg['items']} items</p>";
            echo "<p><b>FIX:</b> Run this SQL:</p>";
            echo "<pre>UPDATE disbursement_sessions SET organization_id = {$correctOrg['id']} WHERE id = 6;</pre>";
        }
    } else {
        echo "<p style='color: #4ade80;'>✅ Session OK!</p>";
    }
}
?>
</body>
</html>
