// Buffer output
ob_start();

require_once __DIR__ . '/public/index.php';

echo "Checking Session 6...\n";
$session = \App\Core\Database::queryOne("SELECT * FROM disbursement_sessions WHERE id = 6");

if ($session) {
    $currentOrgId = $session['organization_id'];
    $currentOrg = \App\Core\Database::queryOne("SELECT * FROM organizations WHERE id = ?", [$currentOrgId]);
    $orgName = trim($currentOrg['name_th']);
    
    echo "Current Session Org: ID $currentOrgId, Name: '$orgName'\n";
    
    $c = \App\Core\Database::queryOne("SELECT COUNT(*) as c FROM budget_line_items WHERE division_id = ?", [$currentOrgId]);
    echo "Current Org Line Items: " . $c['c'] . "\n";
    
    if ($c['c'] == 0) {
        echo "Searching for siblings with same name but with items...\n";
        $siblings = \App\Core\Database::query("SELECT * FROM organizations WHERE name_th LIKE ?", [$orgName]); // Exact match first
        
        $found = false;
        foreach ($siblings as $sib) {
            $sid = $sib['id'];
            if ($sid == $currentOrgId) continue;
            
            $sc = \App\Core\Database::queryOne("SELECT COUNT(*) as c FROM budget_line_items WHERE division_id = ?", [$sid]);
            echo "Sibling ID $sid items: " . $sc['c'] . "\n";
            
            if ($sc['c'] > 0) {
                echo "FIXING: Updating Session 6 to use Org ID $sid\n";
                \App\Core\Database::query("UPDATE disbursement_sessions SET organization_id = ? WHERE id = 6", [$sid]);
                $found = true;
                break;
            }
        }
        
        if (!$found) {
             echo "Trying wild card match...\n";
             $siblings = \App\Core\Database::query("SELECT * FROM organizations WHERE name_th LIKE ?", ["%บริหารทรัพยากรบุคคล%"]);
             foreach ($siblings as $sib) {
                $sid = $sib['id'];
                $sc = \App\Core\Database::queryOne("SELECT COUNT(*) as c FROM budget_line_items WHERE division_id = ?", [$sid]);
                echo "Wildcard ID $sid ({$sib['name_th']}) items: " . $sc['c'] . "\n";
                if ($sc['c'] > 0) {
                    echo "FIXING: Updating Session 6 to use Org ID $sid\n";
                    \App\Core\Database::query("UPDATE disbursement_sessions SET organization_id = ? WHERE id = 6", [$sid]);
                    break;
                }
             }
        }
    } else {
        echo "Session Org has items. Limit filter in Controller might be too strict or logic error.\n";
    }
}

file_put_contents('fix_log.txt', ob_get_clean());
