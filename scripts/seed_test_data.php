<?php
/**
 * Test Data Seeder
 * Creates test data for E2E and integration testing
 * 
 * Usage: php scripts/seed_test_data.php
 */

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

use App\Core\Database;
use App\Models\User;
use App\Models\BudgetRequest;
use App\Models\BudgetRequestItem;
use App\Models\BudgetCategory;
use App\Models\FiscalYear;

echo "🌱 Seeding test data...\n";

try {
    // 1. Create test users
    echo "Creating test users...\n";
    
    $adminExists = User::findByEmail('admin@moj.go.th');
    if (!$adminExists) {
        User::create([
            'email' => 'admin@moj.go.th',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'name' => 'Admin User',
            'role' => 'admin',
            'department' => 'IT Department',
            'is_active' => true
        ]);
        echo "  ✅ Admin user created\n";
    }

    $editorExists = User::findByEmail('editor@moj.go.th');
    if (!$editorExists) {
        User::create([
            'email' => 'editor@moj.go.th',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'name' => 'Editor User',
            'role' => 'editor',
            'department' => 'Finance Department',
            'is_active' => true
        ]);
        echo "  ✅ Editor user created\n";
    }

    $viewerExists = User::findByEmail('viewer@moj.go.th');
    if (!$viewerExists) {
        User::create([
            'email' => 'viewer@moj.go.th',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'name' => 'Viewer User',
            'role' => 'viewer',
            'department' => 'HR Department',
            'is_active' => true
        ]);
        echo "  ✅ Viewer user created\n";
    }

    // Get user IDs
    $admin = User::findByEmail('admin@moj.go.th');
    $editor = User::findByEmail('editor@moj.go.th');
    $viewer = User::findByEmail('viewer@moj.go.th');

    // 2. Create test budget requests
    echo "Creating test budget requests...\n";

    // Draft request
    $draftId = BudgetRequest::create([
        'fiscal_year' => 2568,
        'request_title' => 'Draft Request - Office Supplies',
        'request_status' => 'draft',
        'total_amount' => 0,
        'created_by' => $editor['id']
    ]);
    echo "  ✅ Draft request created (ID: {$draftId})\n";

    // Add items to draft
    BudgetRequestItem::create([
        'budget_request_id' => $draftId,
        'item_name' => 'Printer',
        'quantity' => 2,
        'unit_price' => 5000,
        'item_description' => 'Laser printer for office use'
    ]);

    BudgetRequestItem::create([
        'budget_request_id' => $draftId,
        'item_name' => 'Paper (A4)',
        'quantity' => 50,
        'unit_price' => 150,
        'item_description' => 'Reams of A4 paper'
    ]);

    // Update total
    BudgetRequest::update($draftId, ['total_amount' => 17500]);

    // Pending request
    $pendingId = BudgetRequest::create([
        'fiscal_year' => 2568,
        'request_title' => 'Pending Request - IT Equipment',
        'request_status' => 'pending',
        'total_amount' => 125000,
        'created_by' => $editor['id'],
        'submitted_at' => date('Y-m-d H:i:s')
    ]);
    echo "  ✅ Pending request created (ID: {$pendingId})\n";

    BudgetRequestItem::create([
        'budget_request_id' => $pendingId,
        'item_name' => 'Laptop',
        'quantity' => 5,
        'unit_price' => 25000
    ]);

    // Approved request
    $approvedId = BudgetRequest::create([
        'fiscal_year' => 2568,
        'request_title' => 'Approved Request - Training Budget',
        'request_status' => 'approved',
        'total_amount' => 50000,
        'created_by' => $admin['id'],
        'submitted_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        'approved_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
    ]);
    echo "  ✅ Approved request created (ID: {$approvedId})\n";

    BudgetRequestItem::create([
        'budget_request_id' => $approvedId,
        'item_name' => 'Training Course Fee',
        'quantity' => 10,
        'unit_price' => 5000
    ]);

    // Rejected request
    $rejectedId = BudgetRequest::create([
        'fiscal_year' => 2568,
        'request_title' => 'Rejected Request - Unnecessary Expense',
        'request_status' => 'rejected',
        'total_amount' => 100000,
        'created_by' => $viewer['id'],
        'submitted_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
        'rejected_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        'rejected_reason' => 'Budget not available for this fiscal year'
    ]);
    echo "  ✅ Rejected request created (ID: {$rejectedId})\n";

    echo "\n✅ Test data seeding completed!\n";
    echo "\nTest Accounts:\n";
    echo "  Admin:  admin@moj.go.th  / password\n";
    echo "  Editor: editor@moj.go.th / password\n";
    echo "  Viewer: viewer@moj.go.th / password\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
