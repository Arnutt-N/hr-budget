# Budget Monthly Snapshots - Implementation Guide

## 📋 Overview

ตาราง `budget_monthly_snapshots` ใช้เก็บภาพรวมงบประมาณ ณ ช่วงเวลาที่กำหนด (เช่น สิ้นเดือน) เพื่อให้สามารถ:
- วิเคราะห์เทรนด์การใช้จ่ายย้อนหลัง
- สร้างรายงานประจำเดือนที่มีข้อมูล fix
- เปรียบเทียบประสิทธิภาพการใช้จ่ายข้ามช่วงเวลา

---

## 🗄️ Table Schema

```sql
CREATE TABLE budget_monthly_snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    allocation_id INT NOT NULL COMMENT 'FK: budget_allocations',
    fiscal_year INT NOT NULL,
    snapshot_date DATE NOT NULL COMMENT 'วันที่บันทึก (สิ้นเดือน)',
    
    -- Snapshot Values
    allocated_received DECIMAL(15,2) DEFAULT 0.00,
    disbursed DECIMAL(15,2) DEFAULT 0.00,
    po_commitment DECIMAL(15,2) DEFAULT 0.00,
    remaining DECIMAL(15,2) DEFAULT 0.00,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_snapshot_date (snapshot_date),
    INDEX idx_allocation_fiscal (allocation_id, fiscal_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🔄 Current Status

**สถานะปัจจุบัน (2026-01-04):**
- ✅ ตารางสร้างเรียบร้อยแล้ว
- ⏳ ยังไม่มีข้อมูล (0 rows)
- ✅ โค้ดมี fallback logic ใน `BudgetExecution::getDistinctRecordDates()`
- ✅ ระบบทำงานปกติโดยใช้ `budget_allocations.created_at`

**Fallback Logic:**
```php
public static function getDistinctRecordDates(int $fiscalYear): array
{
    // Try snapshots first
    $results = Database::query("SELECT DISTINCT DATE(snapshot_date) as record_date 
                                FROM budget_monthly_snapshots ...");
    
    if (empty($results)) {
        // Fallback to allocations
        $results = Database::query("SELECT DISTINCT DATE(created_at) as record_date 
                                   FROM budget_allocations ...");
    }
    
    return $results;
}
```

---

## 🚀 Future Implementation

### Phase 1: Manual Snapshot Creation

สร้าง method ในโมเดลสำหรับบันทึก snapshot:

```php
// In BudgetAllocation.php or new BudgetSnapshot.php

public static function createMonthlySnapshot(string $snapshotDate): int
{
    $db = Database::getInstance();
    
    // Get all active allocations
    $allocations = Database::query(
        "SELECT * FROM budget_allocations 
         WHERE deleted_at IS NULL"
    );
    
    $count = 0;
    foreach ($allocations as $alloc) {
        $sql = "INSERT INTO budget_monthly_snapshots 
                (allocation_id, fiscal_year, snapshot_date, 
                 allocated_received, disbursed, po_commitment, remaining)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        Database::query($sql, [
            $alloc['id'],
            $alloc['fiscal_year'],
            $snapshotDate,
            $alloc['allocated_received'],
            $alloc['disbursed'],
            $alloc['po_commitment'],
            $alloc['remaining']
        ]);
        
        $count++;
    }
    
    return $count;
}
```

**การใช้งาน:**
```php
// Create snapshot for end of month
BudgetAllocation::createMonthlySnapshot('2026-01-31');
```

---

### Phase 2: Scheduled Job (Cron)

สร้าง script สำหรับรันอัตโนมัติทุกสิ้นเดือน:

**File:** `scripts/create_monthly_snapshot.php`
```php
<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Models\BudgetAllocation;

// Calculate last day of previous month
$snapshotDate = date('Y-m-t', strtotime('last month'));

try {
    $count = BudgetAllocation::createMonthlySnapshot($snapshotDate);
    echo "✅ Created {$count} snapshots for {$snapshotDate}\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
```

**Cron Setup (Linux/macOS):**
```bash
# Run at 1 AM on the 1st of every month
0 1 1 * * cd /path/to/hr_budget && php scripts/create_monthly_snapshot.php
```

**Windows Task Scheduler:**
```
Trigger: Monthly, Day 1, Time 01:00
Action: Start a program
Program: C:\laragon\bin\php\php-8.x\php.exe
Arguments: C:\laragon\www\hr_budget\scripts\create_monthly_snapshot.php
```

---

### Phase 3: Advanced Features

#### 1. Trend Analysis Query
```php
public static function getMonthlyTrend(int $fiscalYear, int $planId): array
{
    $sql = "SELECT 
                DATE_FORMAT(bms.snapshot_date, '%Y-%m') as month,
                SUM(bms.disbursed) as total_disbursed,
                SUM(bms.remaining) as total_remaining
            FROM budget_monthly_snapshots bms
            JOIN budget_allocations ba ON bms.allocation_id = ba.id
            WHERE ba.fiscal_year = ? AND ba.plan_id = ?
            GROUP BY month
            ORDER BY month";
    
    return Database::query($sql, [$fiscalYear, $planId]);
}
```

#### 2. Monthly Report
```php
public static function getMonthlyReport(string $snapshotDate): array
{
    $sql = "SELECT 
                p.name_th as plan_name,
                SUM(bms.allocated_received) as allocated,
                SUM(bms.disbursed) as disbursed,
                SUM(bms.remaining) as remaining,
                ROUND((SUM(bms.disbursed) / SUM(bms.allocated_received) * 100), 2) as usage_percent
            FROM budget_monthly_snapshots bms
            JOIN budget_allocations ba ON bms.allocation_id = ba.id
            JOIN plans p ON ba.plan_id = p.id
            WHERE bms.snapshot_date = ?
            GROUP BY ba.plan_id
            ORDER BY p.sort_order";
    
    return Database::query($sql, [$snapshotDate]);
}
```

---

## 📊 Use Cases

### 1. Dashboard Timeline Chart
```javascript
// Fetch monthly snapshots for chart
fetch('/api/budget/monthly-trend?fiscal_year=2568&plan_id=1')
  .then(res => res.json())
  .then(data => {
    // data = [
    //   {month: '2567-10', total_disbursed: 10000, total_remaining: 90000},
    //   {month: '2567-11', total_disbursed: 30000, total_remaining: 70000},
    //   ...
    // ]
    renderChart(data);
  });
```

### 2. Monthly Performance Report
```php
// Generate report for December 2568
$report = BudgetAllocation::getMonthlyReport('2568-12-31');

// Export to PDF/Excel
generateMonthlyReport($report, 'December 2568');
```

### 3. Year-over-Year Comparison
```sql
SELECT 
    DATE_FORMAT(snapshot_date, '%m') as month,
    fiscal_year,
    SUM(disbursed) as total
FROM budget_monthly_snapshots
WHERE fiscal_year IN (2567, 2568)
GROUP BY fiscal_year, month
ORDER BY month, fiscal_year;
```

---

## ⚠️ Important Notes

1. **Idempotency**: ตรวจสอบว่าไม่มี snapshot ซ้ำสำหรับวันเดียวกัน
   ```sql
   -- Add UNIQUE constraint
   ALTER TABLE budget_monthly_snapshots 
   ADD UNIQUE KEY unique_snapshot (allocation_id, snapshot_date);
   ```

2. **Data Integrity**: ตรวจสอบว่ายอดรวมตรงกับ `budget_allocations`
   
3. **Storage**: Snapshot จะใช้พื้นที่เพิ่มขึ้นเมื่อเวลาผ่านไป
   - 1,000 allocations × 12 months = 12,000 rows/year
   - พิจารณา archiving ข้อมูลเก่า (>3 ปี)

4. **Performance**: เพิ่ม indexes ตามการใช้งาน

---

## 📝 Maintenance

### Verify Snapshots
```sql
-- Check snapshot coverage
SELECT 
    snapshot_date,
    COUNT(*) as total_snapshots
FROM budget_monthly_snapshots
GROUP BY snapshot_date
ORDER BY snapshot_date DESC;

-- Find missing months
SELECT DISTINCT 
    DATE_FORMAT(DATE_ADD('2567-10-01', INTERVAL seq MONTH), '%Y-%m-31') as expected_date
FROM 
    (SELECT 0 seq UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 
     UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 
     UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11) months
WHERE DATE_FORMAT(DATE_ADD('2567-10-01', INTERVAL seq MONTH), '%Y-%m-31') 
      NOT IN (SELECT DISTINCT snapshot_date FROM budget_monthly_snapshots);
```

### Clean Old Data
```sql
-- Archive snapshots older than 3 years
DELETE FROM budget_monthly_snapshots 
WHERE snapshot_date < DATE_SUB(CURDATE(), INTERVAL 3 YEAR);
```

---

## 🎯 Next Steps

When ready to implement:

1. ✅ Table ready (already created)
2. 📝 Create `createMonthlySnapshot()` method
3. 📝 Create PHP script for manual execution
4. 📝 Test with sample data
5. 📝 Setup cron job
6. 📝 Create dashboard features using snapshots
7. 📝 Document in user manual

---

**Last Updated:** 2026-01-04  
**Status:** Ready for future implementation  
**Created by:** Antigravity AI
