<?php
/**
 * Seed Dimensional Schema with Mock-up Data
 * สร้างข้อมูลทดสอบสำหรับ Dimensional Model
 */

// FORCE USE 127.0.0.1
$host = '127.0.0.1';
$db   = 'hr_budget';
$user = 'root';
$pass = ''; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=3306";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "✅ Connected to Database\n\n";
    
    // Clear existing mock data
    echo "🗑️  Clearing existing mock data...\n";
    $pdo->exec("DELETE FROM log_transfer_note");
    $pdo->exec("DELETE FROM fact_budget_execution");
    $pdo->exec("DELETE FROM dim_budget_structure");
    $pdo->exec("DELETE FROM dim_organization");
    echo "   ✅ Cleared\n\n";
    
    // 1. Seed Organizations
    echo "📊 Seeding Organizations...\n";
    $orgs = [
        ['name' => 'กองยุทธศาสตร์และแผนงาน', 'parent' => null],
        ['name' => 'กองยุทธศาสตร์และแผนงาน (จชต.)', 'parent' => 'สำนักงานยุติธรรมจังหวัด'],
        ['name' => 'กองบริหารการคลัง', 'parent' => null],
        ['name' => 'ศูนย์เทคโนโลยีสารสนเทศและการสื่อสาร', 'parent' => null],
        ['name' => 'กองออกแบบและก่อสร้าง', 'parent' => null],
    ];
    
    $orgIds = [];
    foreach ($orgs as $org) {
        $stmt = $pdo->prepare("INSERT INTO dim_organization (org_name, org_parent_name) VALUES (?, ?)");
        $stmt->execute([$org['name'], $org['parent']]);
        $orgIds[$org['name']] = $pdo->lastInsertId();
        echo "   ✅ {$org['name']}\n";
    }
    echo "\n";
    
    // 2. Seed Budget Structures
    echo "📊 Seeding Budget Structures...\n";
    $structures = [
        [
            'plan' => 'แผนงานบูรณาการพัฒนากระบวนการยุติธรรมทางอาญา',
            'output' => 'ผลผลิตการพัฒนาและส่งเสริมกระบวนการยุติธรรม',
            'activity' => 'กิจกรรมหลักการพัฒนาระบบงานยุติธรรม',
            'item' => 'โครงการพัฒนาระบบดิจิทัลยุติธรรม',
            'level' => 4,
            'org_id' => $orgIds['ศูนย์เทคโนโลยีสารสนเทศและการสื่อสาร']
        ],
        [
            'plan' => 'แผนงานบูรณาการพัฒนากระบวนการยุติธรรมทางอาญา',
            'output' => 'ผลผลิตการพัฒนาและส่งเสริมกระบวนการยุติธรรม',
            'activity' => 'กิจกรรมหลักการพัฒนาบุคลากรยุติธรรม',
            'item' => 'ค่าใช้จ่ายในการฝึกอบรมบุคลากร',
            'level' => 4,
            'org_id' => $orgIds['กองบริหารการคลัง']
        ],
        [
            'plan' => 'แผนงานบูรณาการพัฒนากระบวนการยุติธรรมทางอาญา',
            'output' => 'ผลผลิตการบริหารจัดการงบประมาณ',
            'activity' => 'กิจกรรมหลักการจัดสรรงบประมาณ',
            'item' => 'งบบุคลากร - เงินเดือนข้าราชการ',
            'level' => 4,
            'org_id' => $orgIds['กองบริหารการคลัง']
        ],
        [
            'plan' => 'แผนงานยุทธศาสตร์พัฒนาพื้นที่ชายแดนภาคใต้',
            'output' => 'ผลผลิตการพัฒนาพื้นที่เฉพาะ',
            'activity' => 'กิจกรรมหลักการเสริมสร้างความเข้มแข็งชุมชน',
            'item' => 'โครงการเสริมสร้างสันติสุขจังหวัดชายแดนภาคใต้',
            'level' => 4,
            'org_id' => $orgIds['กองยุทธศาสตร์และแผนงาน (จชต.)']
        ],
    ];
    
    $structureIds = [];
    foreach ($structures as $idx => $struct) {
        $stmt = $pdo->prepare("
            INSERT INTO dim_budget_structure 
            (plan_name, output_name, activity_name, item_name, item_level, org_id) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $struct['plan'],
            $struct['output'],
            $struct['activity'],
            $struct['item'],
            $struct['level'],
            $struct['org_id']
        ]);
        $structureIds[$idx] = $pdo->lastInsertId();
        echo "   ✅ {$struct['item']}\n";
    }
    echo "\n";
    
    // 3. Seed Budget Execution Facts
    echo "📊 Seeding Budget Execution Facts...\n";
    $facts = [
        [
            'structure_id' => $structureIds[0],
            'fiscal_year' => 2568,
            'budget_act' => 5000000.00,
            'allocated' => 4800000.00,
            'transfer' => -200000.00,
            'net_balance' => 4600000.00,
            'disbursed' => 2800000.00,
            'po_pending' => 1200000.00,
            'total_spending' => 4000000.00,
            'balance' => 600000.00,
            'percent_excl_po' => 60.87,
            'percent_incl_po' => 86.96,
            'source_row' => 15 // แถวที่ 15 ใน Excel
        ],
        [
            'structure_id' => $structureIds[1],
            'fiscal_year' => 2568,
            'budget_act' => 1500000.00,
            'allocated' => 1500000.00,
            'transfer' => 0.00,
            'net_balance' => 1500000.00,
            'disbursed' => 890000.00,
            'po_pending' => 350000.00,
            'total_spending' => 1240000.00,
            'balance' => 260000.00,
            'percent_excl_po' => 59.33,
            'percent_incl_po' => 82.67,
            'source_row' => 28
        ],
        [
            'structure_id' => $structureIds[2],
            'fiscal_year' => 2568,
            'budget_act' => 8500000.00,
            'allocated' => 8500000.00,
            'transfer' => null, // ยังไม่มีการโอน
            'net_balance' => 8500000.00,
            'disbursed' => 6200000.00,
            'po_pending' => null, // ไม่มี PO คงค้าง
            'total_spending' => 6200000.00,
            'balance' => 2300000.00,
            'percent_excl_po' => 72.94,
            'percent_incl_po' => 72.94,
            'source_row' => 42
        ],
        [
            'structure_id' => $structureIds[3],
            'fiscal_year' => 2568,
            'budget_act' => 3200000.00,
            'allocated' => 3000000.00,
            'transfer' => 500000.00, // โอนเพิ่ม
            'net_balance' => 3500000.00,
            'disbursed' => 2100000.00,
            'po_pending' => 800000.00,
            'total_spending' => 2900000.00,
            'balance' => 600000.00,
            'percent_excl_po' => 60.00,
            'percent_incl_po' => 82.86,
            'source_row' => 56
        ],
    ];
    
    $factIds = [];
    foreach ($facts as $idx => $fact) {
        $stmt = $pdo->prepare("
            INSERT INTO fact_budget_execution 
            (structure_id, fiscal_year, budget_act_amount, budget_allocated_amount, 
             transfer_change_amount, budget_net_balance, disbursed_amount, 
             po_pending_amount, total_spending_amount, balance_amount,
             percent_disburse_excl_po, percent_disburse_incl_po, datasource_row) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $fact['structure_id'],
            $fact['fiscal_year'],
            $fact['budget_act'],
            $fact['allocated'],
            $fact['transfer'],
            $fact['net_balance'],
            $fact['disbursed'],
            $fact['po_pending'],
            $fact['total_spending'],
            $fact['balance'],
            $fact['percent_excl_po'],
            $fact['percent_incl_po'],
            $fact['source_row']
        ]);
        $factIds[$idx] = $pdo->lastInsertId();
        echo "   ✅ Fact #{$idx} - Row {$fact['source_row']}\n";
    }
    echo "\n";
    
    // 4. Seed Transfer Notes (Log)
    echo "📊 Seeding Transfer Notes...\n";
    $notes = [
        [
            'fact_id' => $factIds[0],
            'source_row' => 15,
            'description' => 'โอนลดงบประมาณไปยังโครงการพัฒนาพื้นที่ชายแดนภาคใต้ เนื่องจากความจำเป็นเร่งด่วน',
            'amount' => -200000.00,
            'quarter' => 'ไตรมาส 2'
        ],
        [
            'fact_id' => $factIds[3],
            'source_row' => 56,
            'description' => 'โอนเพิ่มงบประมาณจากงบกลางเพื่อสนับสนุนการดำเนินงานในจังหวัดชายแดนภาคใต้',
            'amount' => 500000.00,
            'quarter' => 'ไตรมาส 2'
        ],
    ];
    
    foreach ($notes as $note) {
        $stmt = $pdo->prepare("
            INSERT INTO log_transfer_note 
            (fact_id, source_row, transfer_description, transfer_amount, related_quarter) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $note['fact_id'],
            $note['source_row'],
            $note['description'],
            $note['amount'],
            $note['quarter']
        ]);
        echo "   ✅ Note at row {$note['source_row']}\n";
    }
    echo "\n";
    
    // Summary
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ Mock-up Data Seeded Successfully!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    echo "📊 Summary:\n";
    echo "   - Organizations: " . count($orgs) . "\n";
    echo "   - Budget Structures: " . count($structures) . "\n";
    echo "   - Budget Facts: " . count($facts) . "\n";
    echo "   - Transfer Notes: " . count($notes) . "\n";
    echo "\n";
    
    echo "🔍 Quick Verification:\n";
    $result = $pdo->query("
        SELECT 
            o.org_name,
            s.plan_name,
            s.item_name,
            f.budget_act_amount,
            f.disbursed_amount,
            f.percent_disburse_excl_po,
            f.datasource_row
        FROM fact_budget_execution f
        JOIN dim_budget_structure s ON f.structure_id = s.structure_id
        JOIN dim_organization o ON s.org_id = o.org_id
        ORDER BY f.datasource_row
        LIMIT 3
    ")->fetchAll();
    
    foreach ($result as $row) {
        echo "   📄 Row {$row['datasource_row']}: {$row['item_name']}\n";
        echo "      งบ: " . number_format($row['budget_act_amount'], 2) . " | ";
        echo "เบิก: " . number_format($row['disbursed_amount'], 2) . " | ";
        echo "%: {$row['percent_disburse_excl_po']}%\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
