<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "=== Seeding Default Organization ===\n";

try {
    // Check if any org exists
    $existing = Database::queryOne("SELECT COUNT(*) as c FROM dim_organization");
    
    if ($existing['c'] > 0) {
        echo "Organizations already exist ({$existing['c']} rows). Skipping.\n";
        exit(0);
    }
    
    // Insert default organization
    $orgId = Database::insert('dim_organization', [
        'org_name' => 'สำนักงาน',
        'org_parent_name' => null
    ]);
    
    echo "✅ Created default organization (ID: $orgId)\n";
    echo "    Name: สำนักงาน\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
