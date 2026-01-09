<?php
// Pure CLI version - no web dependencies
echo "=== DIRECT FIX (CLI) ===\n\n";

// Direct PDO connection
$dsn = "mysql:host=localhost;dbname=hr_budget;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to database\n\n";
} catch (PDOException $e) {
    die("ERROR: Cannot connect to database: " . $e->getMessage() . "\n");
}

// Step 1: Find org with most items
echo "Step 1: Finding organization with budget items...\n";
$stmt = $pdo->query(
    "SELECT bli.division_id, COUNT(*) as cnt 
     FROM budget_line_items bli 
     WHERE fiscal_year = 2569 
     GROUP BY bli.division_id 
     ORDER BY cnt DESC 
     LIMIT 1"
);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    die("ERROR: No budget items found in database!\n");
}

$targetOrgId = $result['division_id'];
$itemCount = $result['cnt'];

echo "  Found: Division ID $targetOrgId has $itemCount items\n";

// Get org name
$stmt = $pdo->prepare("SELECT name_th FROM organizations WHERE id = ?");
$stmt->execute([$targetOrgId]);
$org = $stmt->fetch(PDO::FETCH_ASSOC);
$orgName = $org['name_th'] ?? 'Unknown';
echo "  Organization: $orgName\n\n";

// Step 2: Check current session
echo "Step 2: Checking Session 6...\n";
$stmt = $pdo->query("SELECT organization_id FROM disbursement_sessions WHERE id = 6");
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    die("ERROR: Session 6 not found!\n");
}

$currentOrgId = $session['organization_id'];
echo "  Current: Session 6 -> Org ID $currentOrgId\n";

if ($currentOrgId == $targetOrgId) {
    echo "  ✓ Already linked correctly!\n";
    exit(0);
}

// Step 3: Update session
echo "\nStep 3: Updating session...\n";
$stmt = $pdo->prepare("UPDATE disbursement_sessions SET organization_id = ? WHERE id = 6");
$stmt->execute([$targetOrgId]);

echo "  ✓ UPDATED: Session 6 -> Org ID $targetOrgId\n\n";

echo "=== SUCCESS ===\n";
echo "Session 6 is now linked to the organization with budget items.\n\n";
echo "Test at: http://localhost/hr_budget/public/budgets/tracking/activities?session_id=6\n";
