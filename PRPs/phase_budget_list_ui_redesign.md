# แผนปรับปรุง UI หน้ารายการเบิกจ่าย (Dimensional Model)

**เป้าหมาย**: ปรับปรุง UI หน้า `/budgets/list` ให้ใช้ **Dimensional Model** (Phase 3) พร้อมฟีเจอร์การกรองข้อมูลและการบันทึก/เรียกดูตามวันที่

---

## 🗄️ แหล่งข้อมูล (Data Source)

หน้านี้ใช้ **Dimensional Model** (Phase 3):

### โครงสร้างตาราง

```
dim_organization (หน่วยงาน)
├── org_id (PK)
├── org_name
└── org_parent_name

dim_budget_structure (โครงสร้างงบ)
├── structure_id (PK)
├── plan_name (แผนงาน)
├── output_name (ผลผลิต)
├── activity_name (กิจกรรมหลัก)
├── item_name (รายการ)
└── org_id → FK to dim_organization

fact_budget_execution (ข้อมูลเบิกจ่าย)
├── fact_id (PK)
├── structure_id → FK to dim_budget_structure
├── fiscal_year
├── record_date (วันที่บันทึก) ← เพิ่มใหม่
├── budget_act_amount (งบตาม พรบ.)
├── budget_allocated_amount (งบจัดสรร)
├── transfer_change_amount (โอน +/-)
├── budget_net_balance (งบสุทธิ)
├── disbursed_amount (เบิกจ่าย)
├── request_amount (ขออนุมัติ) ← เพิ่มใหม่
├── po_pending_amount (PO)
├── total_spending_amount (รวมเบิกจ่าย)
├── balance_amount (คงเหลือ)
├── percent_disburse_excl_po (% เบิก no PO)
└── percent_disburse_incl_po (% เบิก PO)
```

### Field Mapping

| UI Label | Database Field | คำนวณ/ตรง |
|----------|---------------|-----------|
| งบจัดสรร | budget_allocated_amount | ตรง |
| โอน +/- | transfer_change_amount | ตรง |
| งบสุทธิ | budget_net_balance | ตรง (หรือคำนวณ) |
| เบิกจ่าย | disbursed_amount | ตรง |
| ขออนุมัติ | request_amount | เพิ่มใหม่ |
| PO | po_pending_amount | ตรง |
| รวมเบิกจ่าย | total_spending_amount | คำนวณ |
| คงเหลือ | balance_amount | ตรง (หรือคำนวณ) |

---

## สรุปการเปลี่ยนแปลงหลัก

| รายการ | ก่อนแก้ไข | หลังแก้ไข |
|--------|-----------|-----------|
| Data Source | Legacy budgets + budget_records | **Dimensional Model** |
| Model | Budget | **BudgetExecution** |
| Table | budgets | **fact_budget_execution** |
| เมนูซ้าย | รายการงบประมาณ | รายการเบิกจ่าย |
| Navbar/Title | รายการงบประมาณ | รายการเบิกจ่ายงบประมาณ |
| ตัวกรอง | Dropdown พ.ศ. เดี่ยว | Filter Card (ปีงบฯ, แผนงาน, หน่วยงาน, **วันที่**, ค้นหา) |
| ปุ่มเพิ่ม | เพิ่มงบประมาณ | เพิ่มรายการ (พร้อมเลือกวันที่บันทึก) |
| Summary Cards | 4 การ์ด + THB | 5 การ์ด + ไม่มี THB + Tooltip |
| Table Columns | 11 คอลัมน์ | 13 คอลัมน์ (เบิก, ขอ, PO, % แยก) |
| Number Format | number_format() | M Format ทศนิยม 4 |

---

## Proposed Changes

### Database Schema Updates

#### [MODIFY] Migration: Add Missing Fields

**ไฟล์ใหม่**: `database/migrations/013_budget_list_enhancements.sql`

```sql
-- เพิ่มฟิลด์ record_date และ request_amount ใน fact_budget_execution
ALTER TABLE fact_budget_execution
    ADD COLUMN record_date DATE NULL DEFAULT NULL COMMENT 'วันที่บันทึก (สำหรับ filter)' AFTER fiscal_year,
    ADD COLUMN request_amount DECIMAL(20,2) NULL DEFAULT NULL COMMENT 'ขออนุมัติวงเงิน' AFTER disbursed_amount,
    ADD INDEX idx_record_date (record_date);

-- อัปเดต total_spending_amount ให้รวม request_amount ด้วย
-- (อาจต้องปรับ application logic หรือใช้ computed column)
```

---

### Layout & Navigation

#### [MODIFY] [main.php](file:///c:/laragon/www/hr_budget/resources/views/layouts/main.php)

เปลี่ยนชื่อเมนู sidebar (บรรทัด 109-112):
```diff
- <span class="ml-3 nav-text">รายการงบประมาณ</span>
+ <span class="ml-3 nav-text">รายการเบิกจ่าย</span>
```

---

### Budget List View

#### [CREATE] [list.php](file:///c:/laragon/www/hr_budget/resources/views/budgets/list.php)

สร้างไฟล์ใหม่สำหรับหน้า Budget List ที่ใช้ Dimensional Model

**โครงสร้างหลัก**:
1. Header + Actions (ปุ่มเพิ่มรายการ)
2. Filter Card (6 ฟิลด์: ปี, แผนงาน, หน่วยงาน, วันที่, ค้นหา, ปุ่ม)
3. Summary Cards (5 การ์ด พร้อม Tooltip)
4. Table (13 คอลัมน์ พร้อม M Format)
5. Pagination

**ดูรายละเอียดใน Appendix A**

---

### Controller

#### [MODIFY] [BudgetController.php](file:///c:/laragon/www/hr_budget/src/Controllers/BudgetController.php)

แก้ไข `index()` method ให้ใช้ BudgetExecution Model:

```php
/**
 * Budget List (using Dimensional Model)
 */
public function index(): void
{
    Auth::require();
    
    $fiscalYear = (int) ($_GET['year'] ?? FiscalYear::currentYear());
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;
    
    // Get filters from query params
    $filters = [
        'org_id' => $_GET['org'] ?? null,
        'plan_name' => $_GET['plan'] ?? null,
        'search' => $_GET['search'] ?? null,
        'record_date' => $_GET['record_date'] ?? null,
    ];
    
    // Get data from Dimensional Model
    $budgets = BudgetExecution::getWithStructure($fiscalYear, $filters, $perPage, $offset);
    $total = BudgetExecution::count($fiscalYear, $filters);
    $totalPages = (int) ceil($total / $perPage);
    
    // Get statistics
    $stats = BudgetExecution::getKpiStats($fiscalYear, $filters);
    
    // Get organizations and plans for filter dropdowns
    $organizations = Organization::all();
    $plans = BudgetStructure::getDistinctPlans($fiscalYear);
    
    // Fiscal years for dropdown
    $fiscalYears = FiscalYear::getForSelect();
    
    View::render('budgets/list', [  // ใช้ view ใหม่
        'title' => 'รายการเบิกจ่ายงบประมาณ',
        'currentPage' => 'budgets',
        'fiscalYear' => $fiscalYear,
        'fiscalYears' => $fiscalYears,
        'budgets' => $budgets,
        'stats' => $stats,
        'filters' => $filters,
        'organizations' => $organizations,
        'plans' => $plans,
        'auth' => Auth::user(),
        'pagination' => [
            'current' => $page,
            'total' => $totalPages,
            'perPage' => $perPage,
            'totalRecords' => $total,
        ],
    ], 'main');
}
```

---

### Model Updates

#### [MODIFY] [BudgetExecution.php](file:///c:/laragon/www/hr_budget/src/Models/BudgetExecution.php)

**1. เพิ่ม method `getWithStructure()` ที่รับ pagination**

```php
/**
 * Get execution records with structure data (with pagination)
 */
public static function getWithStructure(int $fiscalYear, array $filters = [], int $limit = 20, int $offset = 0): array
{
    $sql = "SELECT 
                f.*,
                s.plan_name,
                s.output_name,
                s.activity_name,
                s.item_name,
                o.org_name,
                o.org_parent_name,
                -- คำนวณฟิลด์ที่ขาด
                (f.disbursed_amount + COALESCE(f.request_amount, 0) + f.po_pending_amount) as calculated_total_spending,
                (f.budget_net_balance - (f.disbursed_amount + COALESCE(f.request_amount, 0) + f.po_pending_amount)) as calculated_balance,
                CASE WHEN f.budget_net_balance > 0 
                     THEN ROUND((f.disbursed_amount / f.budget_net_balance) * 100, 2)
                     ELSE 0 END as percent_no_po,
                CASE WHEN f.budget_net_balance > 0 
                     THEN ROUND(((f.disbursed_amount + f.po_pending_amount) / f.budget_net_balance) * 100, 2)
                     ELSE 0 END as percent_with_po
            FROM fact_budget_execution f
            LEFT JOIN dim_budget_structure s ON f.structure_id = s.structure_id
            LEFT JOIN dim_organization o ON s.org_id = o.org_id
            WHERE f.fiscal_year = ?";
            
    $params = [$fiscalYear];
    
    // Add filters
    if (!empty($filters['org_id'])) {
        $sql .= " AND s.org_id = ?";
        $params[] = $filters['org_id'];
    }
    
    if (!empty($filters['plan_name'])) {
        $sql .= " AND s.plan_name LIKE ?";
        $params[] = '%' . $filters['plan_name'] . '%';
    }
    
    if (!empty($filters['record_date'])) {
        $sql .= " AND DATE(f.record_date) = ?";
        $params[] = $filters['record_date'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (s.item_name LIKE ? OR s.activity_name LIKE ?)";
        $params[] = '%' . $filters['search'] . '%';
        $params[] = '%' . $filters['search'] . '%';
    }

    $sql .= " ORDER BY f.fact_id DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    return Database::query($sql, $params);
}
```

**2. เพิ่ม method `count()`**

```php
/**
 * Count total records
 */
public static function count(int $fiscalYear, array $filters = []): int
{
    $sql = "SELECT COUNT(*) as total
            FROM fact_budget_execution f
            LEFT JOIN dim_budget_structure s ON f.structure_id = s.structure_id
            WHERE f.fiscal_year = ?";
            
    $params = [$fiscalYear];
    
    // Add same filters as getWithStructure
    if (!empty($filters['org_id'])) {
        $sql .= " AND s.org_id = ?";
        $params[] = $filters['org_id'];
    }
    
    if (!empty($filters['plan_name'])) {
        $sql .= " AND s.plan_name LIKE ?";
        $params[] = '%' . $filters['plan_name'] . '%';
    }
    
    if (!empty($filters['record_date'])) {
        $sql .= " AND DATE(f.record_date) = ?";
        $params[] = $filters['record_date'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (s.item_name LIKE ? OR s.activity_name LIKE ?)";
        $params[] = '%' . $filters['search'] . '%';
        $params[] = '%' . $filters['search'] . '%';
    }
    
    $result = Database::queryOne($sql, $params);
    return (int) ($result['total'] ?? 0);
}
```

**3. แก้ไข `getKpiStats()` ให้รองรับ record_date**

```php
public static function getKpiStats(int $fiscalYear, array $filters = []): array
{
    $sql = "SELECT 
                SUM(f.budget_act_amount) as total_budget_act,
                SUM(f.budget_allocated_amount) as total_allocated,
                SUM(f.transfer_change_amount) as transfer_change_amount,
                SUM(f.budget_net_balance) as total_net_budget,
                SUM(f.disbursed_amount) as total_disbursed,
                SUM(COALESCE(f.request_amount, 0)) as total_request,
                SUM(f.po_pending_amount) as total_po,
                SUM(f.disbursed_amount + COALESCE(f.request_amount, 0) + f.po_pending_amount) as total_spending,
                SUM(f.balance_amount) as total_balance
            FROM fact_budget_execution f";
    
    // Join if filters needed
    if (!empty($filters['org_id']) || !empty($filters['plan_name']) || !empty($filters['search'])) {
        $sql .= " LEFT JOIN dim_budget_structure s ON f.structure_id = s.structure_id";
    }

    $sql .= " WHERE f.fiscal_year = ?";
    $params = [$fiscalYear];

    // Add filters (same as getWithStructure)
    // ... (เหมือนเดิม)
    
    if (!empty($filters['record_date'])) {
        $sql .= " AND DATE(f.record_date) = ?";
        $params[] = $filters['record_date'];
    }
    
    $stats = Database::queryOne($sql, $params);
    
    // Calculate percentages
    $netBudget = (float) ($stats['total_net_budget'] ?? 0);
    $disbursed = (float) ($stats['total_disbursed'] ?? 0);
    $spending = (float) ($stats['total_spending'] ?? 0);
    
    return array_merge($stats ?: [], [
        'percent_disbursed' => $netBudget > 0 ? round(($disbursed / $netBudget) * 100, 2) : 0,
        'percent_spending' => $netBudget > 0 ? round(($spending / $netBudget) * 100, 2) : 0,
    ]);
}
```

---

#### [CREATE] [Organization.php](file:///c:/laragon/www/hr_budget/src/Models/Organization.php)

สร้าง Model ใหม่สำหรับ dim_organization:

```php
<?php

namespace App\Models;

use App\Core\Database;

class Organization
{
    public static function all(): array
    {
        return Database::query("SELECT * FROM dim_organization ORDER BY org_name");
    }
}
```

---

#### [CREATE] [BudgetStructure.php](file:///c:/laragon/www/hr_budget/src/Models/BudgetStructure.php)

สร้าง Model ใหม่สำหรับ dim_budget_structure:

```php
<?php

namespace App\Models;

use App\Core\Database;

class BudgetStructure
{
    public static function getDistinctPlans(int $fiscalYear): array
    {
        // Get distinct plan_name from fact_budget_execution for this fiscal year
        $sql = "SELECT DISTINCT s.plan_name
                FROM dim_budget_structure s
                INNER JOIN fact_budget_execution f ON s.structure_id = f.structure_id
                WHERE f.fiscal_year = ?
                ORDER BY s.plan_name";
        
        return Database::query($sql, [$fiscalYear]);
    }
}
```

---

### Helper Functions

#### [MODIFY] [View.php](file:///c:/laragon/www/hr_budget/src/Core/View.php)

เพิ่ม Helper สำหรับ M Format 4 ทศนิยม:

```php
/**
 * Format currency in short M format with 4 decimal places
 * Example: 1234567.89 -> "1.2346M"
 */
public static function currencyShortM4(float $amount): string
{
    if ($amount >= 1000000) {
        return number_format($amount / 1000000, 4) . 'M';
    } elseif ($amount >= 1000) {
        return number_format($amount / 1000, 2) . 'K';
    }
    return number_format($amount, 2);
}
```

---

### Form Updates (ฟีเจอร์บันทึกตามวันที่)

#### [CREATE/MODIFY] Form for Budget Execution

สร้างฟอร์มสำหรับเพิ่ม/แก้ไข fact_budget_execution (ใช้ร่วมกับ dim_budget_structure)

**ฟิลด์สำคัญ**:
- Structure (dropdown จาก dim_budget_structure)
- วันที่บันทึก (date picker)
- งบจัดสรร, โอน, เบิกจ่าย, ขออนุมัติ, PO

---

## Verification Plan

### Manual Testing

1. **Database Migration**: รัน migration script เพื่อเพิ่ม `record_date` และ `request_amount`
2. **เปิดหน้า**: http://localhost/hr_budget/public/budgets/list
3. **ตรวจสอบ UI** ตามแผน (เหมือนเดิม)
4. **ตรวจสอบข้อมูล**: ดูว่าดึงจาก `fact_budget_execution` ถูกต้อง
5. **ทดสอบ Filter**: โดยเฉพาะ record_date
6. **ทดสอบ Pagination**: ข้อมูลแบ่งหน้าถูกต้อง

---

## ไฟล์ที่ต้องสร้าง/แก้ไข

| ไฟล์ | ประเภท | การเปลี่ยนแปลง |
|------|--------|----------------|
| `database/migrations/013_budget_list_enhancements.sql` | NEW | เพิ่ม record_date, request_amount |
| [main.php](file:///c:/laragon/www/hr_budget/resources/views/layouts/main.php) | MODIFY | เปลี่ยนชื่อเมนู |
| [list.php](file:///c:/laragon/www/hr_budget/resources/views/budgets/list.php) | NEW | View ใหม่สำหรับ Budget List |
| [BudgetController.php](file:///c:/laragon/www/hr_budget/src/Controllers/BudgetController.php) | MODIFY | ใช้ BudgetExecution model |
| [BudgetExecution.php](file:///c:/laragon/www/hr_budget/src/Models/BudgetExecution.php) | MODIFY | เพิ่ม pagination, filters |
| [Organization.php](file:///c:/laragon/www/hr_budget/src/Models/Organization.php) | NEW | Model สำหรับ dim_organization |
| [BudgetStructure.php](file:///c:/laragon/www/hr_budget/src/Models/BudgetStructure.php) | NEW | Model สำหรับ dim_budget_structure |
| [View.php](file:///c:/laragon/www/hr_budget/src/Core/View.php) | MODIFY | เพิ่ม currencyShortM4() |

---

## สรุปความแตกต่างจากแผนเดิม

✅ **ใช้ Dimensional Model** (fact_budget_execution + dim_budget_structure)  
✅ **เพิ่มฟิลด์ใหม่**: record_date, request_amount  
✅ **สร้าง View ใหม่**: budgets/list.php  
✅ **สร้าง Model ใหม่**: Organization, BudgetStructure  
✅ **ใช้ BudgetExecution model** แทน Budget  
✅ **Query ตรงจาก fact table** ไม่ต้อง JOIN budget_records  

---

## Appendix A: View Template (list.php)

```php
<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <p class="text-dark-muted text-sm mt-1">รายการเบิกจ่ายงบประมาณประจำปี</p>
        </div>
        <a href="<?= \App\Core\View::url('/budgets/create') ?>" class="btn btn-primary">
            <i class="ph ph-plus"></i> เพิ่มรายการ
        </a>
    </div>

    <!-- Filter Card -->
    <div class="bg-dark-card border border-dark-border rounded-xl p-4">
        <!-- 6 ฟิลด์ตามแผน -->
    </div>

    <!-- Summary Cards (5 การ์ด) -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <!-- การ์ด 1-5 พร้อม Tooltip -->
    </div>

    <!-- Table (13 คอลัมน์) -->
    <div class="bg-dark-card border border-dark-border rounded-xl overflow-hidden">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>#</th>
                    <th>หมวดหมู่</th>
                    <th>งบจัดสรร</th>
                    <th>โอน +/-</th>
                    <th>งบสุทธิ</th>
                    <th>เบิกจ่าย</th>
                    <th>ขออนุมัติ</th>
                    <th>PO</th>
                    <th>รวมเบิกจ่าย</th>
                    <th>คงเหลือ</th>
                    <th>% เบิก (no PO)</th>
                    <th>% เบิก (PO)</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($budgets as $budget): ?>
                <tr>
                    <!-- แสดงข้อมูลด้วย M Format -->
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
</div>
```
