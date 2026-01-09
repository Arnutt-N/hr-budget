<?php
require_once 'public/db-api.php';
require_once 'src/Models/Division.php';
require_once 'src/Models/BudgetPlan.php';
require_once 'src/Models/FundSource.php';
require_once 'src/Models/BudgetCategory.php';
require_once 'src/Models/BudgetAllocation.php';

use App\Core\Database;
use App\Models\Division;
use App\Models\BudgetPlan;
use App\Models\FundSource;
use App\Models\BudgetCategory;
use App\Models\BudgetAllocation;

echo "Seeding Mock Data for HR Budget System (Extended Schema)...\n\n";

try {
    // 1. Ensure Fundamental Data (already in migration, but let's safe-check)
    $division = Division::findByCode('STRATEGY');
    if (!$division) {
        $divId = Division::create([
            'code' => 'STRATEGY',
            'name_th' => 'กองยุทธศาสตร์และแผนงาน',
            'short_name' => 'กยผ.'
        ]);
        echo "[+] Created Division: STRATEGY\n";
    } else {
        $divId = $division['id'];
        echo "[-] Division STRATEGY exists.\n";
    }

    $fund = FundSource::findByCode('PERSONNEL');
    if (!$fund) {
       // Should exist from migration
       echo "[!] Fund Source PERSONNEL missing! Check migration.\n";
       exit; 
    }
    $fundId = $fund['id'];

    // 2. Create Budget Plans Hierarchy
    // Program -> Output -> Activity -> Project
    
    // Level 1: Program
    $programId = BudgetPlan::create([
        'code' => 'P01',
        'name_th' => 'แผนงานพื้นฐานด้านความมั่นคง',
        'plan_type' => 'program',
        'level' => 1
    ]);
    echo "[+] Created Program: P01\n";

    // Level 2: Output
    $outputId = BudgetPlan::create([
        'code' => 'P01-O01',
        'name_th' => 'ผลผลิตที่ 1: นโยบายและแผนด้านความมั่นคง',
        'plan_type' => 'output',
        'parent_id' => $programId,
        'level' => 2
    ]);
    echo "[+] Created Output: P01-O01\n";

    // Level 3: Activity
    $activityId = BudgetPlan::create([
        'code' => 'P01-O01-A01',
        'name_th' => 'กิจกรรมหลัก: บริหารจัดการงานนโยบาย',
        'plan_type' => 'activity',
        'parent_id' => $outputId,
        'level' => 3
    ]);
    echo "[+] Created Activity: P01-O01-A01\n";

    // Level 4: Project
    $projectId = BudgetPlan::create([
        'code' => 'PROJ-68-001',
        'name_th' => 'โครงการพัฒนาระบบบริหารจัดการงบประมาณบุคลากร',
        'plan_type' => 'project',
        'parent_id' => $activityId,
        'division_id' => $divId,
        'level' => 4
    ]);
    echo "[+] Created Project: PROJ-68-001\n";

    // 3. Create Budget Allocations
    // We need an Item ID from budget_category_items. Let's use 'salary' (id=2 from migration)
    // First, verify item exists
    $categoryId = 1; // Personnel
    $itemId = 2; // Salary (เงินเดือน)
    
    // Check if allocation exists
    $allocation = BudgetAllocation::findByParams(2568, $projectId, $itemId);
    
    if (!$allocation) {
        $allocId = BudgetAllocation::create([
            'fiscal_year' => 2568,
            'plan_id' => $projectId,
            'fund_source_id' => $fundId,
            'category_id' => $categoryId,
            'item_id' => $itemId,
            'division_id' => $divId,
            'allocated_pba' => 1000000.00,
            'allocated_received' => 1000000.00,
            'net_budget' => 1000000.00,
            'remaining' => 1000000.00,
            'created_by' => 1
        ]);
        echo "[+] Created Budget Allocation: 1,000,000 THB for Salary in Project PROJ-68-001\n";
    } else {
        echo "[-] Budget Allocation already exists.\n";
    }

    echo "\n✅ Seeding Completed Successfully!\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
