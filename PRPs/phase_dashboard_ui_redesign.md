# Implementation Plan: Dashboard UI Redesign + URL Reorganization

> **Created**: 2025-12-18
> **Updated**: 2025-12-18
> **Goal**: ปรับ UI หน้าภาพรวม และจัดโครงสร้าง URL ใหม่

---

## Summary

### URL Reorganization

| URL เดิม | URL ใหม่ | สถานะ |
|----------|----------|-------|
| `/` | `/` | 🔧 แก้ไข UI |
| `/execution` | `/budgets` | 🔄 เปลี่ยน URL (ใช้ view เดิม) |
| `/budgets` (redirect) | (ลบ) | ❌ ลบ redirect |
| `/budgets/list` | `/budgets/list` | ✅ เหมือนเดิม |
| `/requests` | `/requests` | ✅ เหมือนเดิม |

### UI Changes (เฉพาะหน้า `/` เท่านั้น)

1. **ลบ**: หัวข้อ `<h1>ภาพรวมงบประมาณ</h1>`
2. **เก็บ**: `<p>สรุปภาพรวมงบประมาณประจำปี</p>`
3. **แก้ไข**: การ์ด "คำขอรออนุมัติ" → "อัตราการเบิกจ่าย"
4. **แก้ไข**: Chart "งบประมาณตามหมวดหมู่" → "แนวโน้มการเบิกจ่าย (Timeline)"
5. **แก้ไข**: Widget "เมนูด่วน" → "สัดส่วนตามหมวดหมู่งบประมาณ" (Donut Chart)
6. **เพิ่ม**: ตารางไตรมาส (9 คอลัมน์)
7. **ปรับ UI**: Fiscal Year Selector - ใช้ไอคอนแทนข้อความ

---

## Affected Files

| File | Type | Changes Required |
|------|------|------------------|
| [web.php](file:///c:/laragon/www/hr_budget/routes/web.php) | Routes | 🔄 `/execution` → `/budgets` |
| [dashboard/index.php](file:///c:/laragon/www/hr_budget/resources/views/dashboard/index.php) | View | 🔧 Header, KPI cards, Charts, Table |
| [DashboardController.php](file:///c:/laragon/www/hr_budget/src/Controllers/DashboardController.php) | Controller | ➕ Add quarterly data methods |

> [!NOTE]
> หน้า `/budgets` จะใช้ view `budgets/execution.php` เดิม ไม่ต้องแก้ไข

---

## Risk Mitigation

> [!WARNING]
> การเปลี่ยน URL `/execution` → `/budgets` อาจส่งผลกระทบหน้าอื่นๆ

### Potential Issues

1. **500 Server Error Risks**:
   - Hard-coded links ใน views อื่นๆ
   - JavaScript fetch/AJAX calls ไป `/execution`
   - Email notifications ที่มีลินก์
   - Bookmarks ของ users

2. **Data Query Issues**:
   - ถ้าตาราง `budget_trackings` ไม่มี column `quarter`
   - Monthly trend data ไม่ครบ 12 เดือน

### Mitigation Steps

**ก่อนแก้ไข**:
- [ ] Backup database
- [ ] Search all `/execution` references in codebase
- [ ] ทดสอบบน local environment ก่อน
- [ ] เตรียม rollback plan

**หลังแก้ไข**:
- [ ] Monitor error logs
- [ ] ทดสอบทุกหน้าที่อาจกระทบ
- [ ] แจ้ง users ถ้า URL เปลี่ยน

---

## Proposed Changes

### 1. Routes Layer

#### [MODIFY] [web.php](file:///c:/laragon/www/hr_budget/routes/web.php)

**Before** (Lines 31-36):
```php
Router::get('/execution', [BudgetExecutionController::class, 'index']);
Router::get('/execution/export', [BudgetExecutionController::class, 'export']);

Router::get('/budgets', function() { \App\Core\Router::redirect('/budgets/list'); });
Router::get('/budgets/list', [BudgetController::class, 'index']);
```

**After**:
```php
// Budget Execution Dashboard (เปลี่ยน URL จาก /execution เป็น /budgets)
Router::get('/budgets', [BudgetExecutionController::class, 'index']);
Router::get('/budgets/export', [BudgetExecutionController::class, 'export']);

// Budget List
Router::get('/budgets/list', [BudgetController::class, 'index']);

// Legacy redirect (optional - เพื่อ backward compatibility)
Router::get('/execution', function() { \App\Core\Router::redirect('/budgets'); });
```

---

### 2. View Layer (หน้า `/` เท่านั้น)

#### [MODIFY] [dashboard/index.php](file:///c:/laragon/www/hr_budget/resources/views/dashboard/index.php)

##### 2.1 Fiscal Year Selector UI Enhancement

**Before**:
```php
<select id="fiscal-year-select" class="input w-40" onchange="changeFiscalYear(this.value)">
    <?php foreach ($fiscalYears as $fy): ?>
    <option value="<?= $fy['value'] ?>" <?= $fy['value'] == $fiscalYear ? 'selected' : '' ?>>
        <?= htmlspecialchars($fy['label']) ?>
    </option>
    <?php endforeach; ?>
</select>
```

**After**:
```php
<select id="fiscal-year-select" class="input w-40" onchange="changeFiscalYear(this.value)">
    <?php foreach ($fiscalYears as $fy): ?>
    <option value="<?= $fy['value'] ?>" <?= $fy['value'] == $fiscalYear ? 'selected' : '' ?>>
        <?php if ($fy['is_current']): ?>
            🟢 <?= $fy['year'] ?>
        <?php elseif ($fy['is_closed']): ?>
            🔒 <?= $fy['year'] ?>
        <?php else: ?>
            <?= $fy['year'] ?>
        <?php endif; ?>
    </option>
    <?php endforeach; ?>
</select>
```

> [!TIP]
> ใช้ emoji icons:
> - 🟢 (Green Circle) = ปีปัจจุบัน
> - 🔒 (Lock) = ปีปิดแล้ว
> - ไม่มีไอคอน = ปีอื่นๆ

---

##### 2.2 Header Section (Lines 4-7)

**Before**:
```php
<h1 class="text-2xl font-bold text-white">ภาพรวมงบประมาณ</h1>
<p class="text-dark-muted text-sm mt-1">สรุปภาพรวมงบประมาณประจำปี</p>
```

**After**:
```php
<p class="text-dark-muted text-sm">สรุปภาพรวมงบประมาณประจำปี</p>
```

---

##### 2.3 KPI Card #4: "คำขอรออนุมัติ" → "อัตราการเบิกจ่าย" (Lines 82-98)

**Before**:
```php
<!-- Pending Requests -->
<div class="bg-dark-card...">
    <p class="text-dark-muted text-sm font-medium">คำขอรออนุมัติ</p>
    <h3 class="text-2xl font-bold text-white mt-1">
        <?= $stats['pending_requests'] ?? 0 ?>
    </h3>
    <a href="...">ดูคำขอทั้งหมด</a>
</div>
```

**After**:
```php
<!-- Disbursement Rate -->
<div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover">
    <div class="flex justify-between items-start mb-4">
        <div>
            <p class="text-dark-muted text-sm font-medium">อัตราการเบิกจ่าย</p>
            <h3 class="text-2xl font-bold text-white mt-1">
                <?= $stats['spent_percent'] ?? 0 ?>%
            </h3>
        </div>
        <?php 
            $rate = $stats['spent_percent'] ?? 0;
            $rateColor = $rate >= 80 ? 'green' : ($rate >= 50 ? 'orange' : 'red');
        ?>
        <div class="p-2 bg-<?= $rateColor ?>-500/10 rounded-lg text-<?= $rateColor ?>-500">
            <i class="ph ph-chart-line-up text-2xl"></i>
        </div>
    </div>
    <?php if ($rate >= 80): ?>
        <span class="badge badge-green">ดีมาก</span>
    <?php elseif ($rate >= 50): ?>
        <span class="badge badge-orange">ปานกลาง</span>
    <?php else: ?>
        <span class="badge badge-red">ต่ำ</span>
    <?php endif; ?>
</div>
```

---

##### 2.4 Chart Section: "งบประมาณตามหมวดหมู่" → "แนวโน้มการเบิกจ่าย (Timeline)" (Lines 103-109)

**Before**:
```php
<!-- Category Chart -->
<div class="lg:col-span-2 bg-dark-card...">
    <h3>งบประมาณตามหมวดหมู่</h3>
    <canvas id="categoryChart"></canvas>
</div>
```

**After**:
```php
<!-- Timeline Trend Chart -->
<div class="lg:col-span-2 bg-dark-card border border-dark-border rounded-xl p-6">
    <h3 class="text-lg font-semibold text-white mb-4">แนวโน้มการเบิกจ่าย (Timeline)</h3>
    <div class="h-64 relative">
        <canvas id="trendChart"></canvas>
    </div>
</div>
```

---

##### 2.5 Widget Section: "เมนูด่วน" → Donut Chart (Lines 111-143)

**Before**:
```php
<!-- Quick Links -->
<div class="bg-dark-card...">
    <h3>เมนูด่วน</h3>
    <div class="space-y-3">
        <a href="...">ติดตามผลการเบิกจ่าย</a>
        ...
    </div>
</div>
```

**After**:
```php
<!-- Category Donut Chart -->
<div class="bg-dark-card border border-dark-border rounded-xl p-6">
    <h3 class="text-lg font-semibold text-white mb-4">สัดส่วนตามหมวดหมู่งบประมาณ</h3>
    <div class="h-64 relative flex justify-center">
        <canvas id="categoryDonutChart"></canvas>
    </div>
</div>
```

---

##### 2.6 Table Section: Quarterly Disbursement Table (Lines 146-199)

**Before**:
```php
<!-- Budget by Category Table -->
<table>
    <thead>
        <th>หมวดหมู่</th>
        <th>งบจัดสรร</th>
        <th>เบิกจ่ายแล้ว</th>
        <th>คงเหลือ</th>
        <th>ความคืบหน้า</th>
    </thead>
</table>
```

**After**:
```php
<!-- Quarterly Disbursement Table with KPI -->
<div class="bg-dark-card border border-dark-border rounded-xl overflow-hidden">
    <div class="px-6 py-4 border-b border-dark-border flex justify-between items-center">
        <h3 class="text-lg font-semibold text-white">ผลการเบิกจ่ายตามไตรมาส</h3>
        <a href="<?= \App\Core\View::url('/budgets/list?year=' . $fiscalYear) ?>" class="text-sm text-primary-500 hover:text-primary-400">
            ดูรายละเอียด <i class="ph ph-arrow-right ml-1"></i>
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>หมวดหมู่</th>
                    <th class="text-right">งบจัดสรร</th>
                    <th class="text-right">Q1</th>
                    <th class="text-right">Q2</th>
                    <th class="text-right">Q3</th>
                    <th class="text-right">Q4</th>
                    <th class="text-right">รวมเบิกจ่าย</th>
                    <th class="text-right">คงเหลือ</th>
                    <th class="text-center">KPI %</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($quarterlyData)): ?>
                    <?php foreach ($quarterlyData as $row): ?>
                    <?php 
                        $kpiPercent = $row['allocated'] > 0 
                            ? round(($row['total_spent'] / $row['allocated']) * 100, 1) : 0;
                        $kpiClass = $kpiPercent >= 80 ? 'text-green-400' : 
                                   ($kpiPercent >= 50 ? 'text-orange-400' : 'text-red-400');
                    ?>
                    <tr>
                        <td class="font-medium"><?= htmlspecialchars($row['category_name'] ?? '-') ?></td>
                        <td class="text-right"><?= \App\Core\View::currency($row['allocated']) ?></td>
                        <td class="text-right text-dark-muted"><?= \App\Core\View::currency($row['q1'] ?? 0) ?></td>
                        <td class="text-right text-dark-muted"><?= \App\Core\View::currency($row['q2'] ?? 0) ?></td>
                        <td class="text-right text-dark-muted"><?= \App\Core\View::currency($row['q3'] ?? 0) ?></td>
                        <td class="text-right text-dark-muted"><?= \App\Core\View::currency($row['q4'] ?? 0) ?></td>
                        <td class="text-right font-medium"><?= \App\Core\View::currency($row['total_spent']) ?></td>
                        <td class="text-right text-green-400"><?= \App\Core\View::currency($row['remaining']) ?></td>
                        <td class="text-center">
                            <span class="<?= $kpiClass ?> font-bold"><?= $kpiPercent ?>%</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center py-8 text-dark-muted">
                            <i class="ph ph-folder-open text-4xl mb-2"></i>
                            <p>ยังไม่มีข้อมูลงบประมาณ</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
```

---

### 3. Controller Layer

#### [MODIFY] [DashboardController.php](file:///c:/laragon/www/hr_budget/src/Controllers/DashboardController.php)

**Add quarterly data method**:
```php
/**
 * Get budget data with quarterly breakdown
 */
private function getQuarterlyData(int $fiscalYear): array
{
    return Database::query(
        "SELECT 
            bc.name_th as category_name,
            COALESCE(SUM(b.allocated_amount), 0) as allocated,
            COALESCE(SUM(CASE 
                WHEN MONTH(bt.created_at) IN (10,11,12) THEN bt.amount 
                ELSE 0 
            END), 0) as q1,
            COALESCE(SUM(CASE 
                WHEN MONTH(bt.created_at) IN (1,2,3) THEN bt.amount 
                ELSE 0 
            END), 0) as q2,
            COALESCE(SUM(CASE 
                WHEN MONTH(bt.created_at) IN (4,5,6) THEN bt.amount 
                ELSE 0 
            END), 0) as q3,
            COALESCE(SUM(CASE 
                WHEN MONTH(bt.created_at) IN (7,8,9) THEN bt.amount 
                ELSE 0 
            END), 0) as q4,
            COALESCE(SUM(b.spent_amount), 0) as total_spent,
            COALESCE(SUM(b.allocated_amount - b.spent_amount), 0) as remaining
         FROM budget_categories bc
         LEFT JOIN budgets b ON b.category_id = bc.id AND b.fiscal_year = ?
         LEFT JOIN budget_trackings bt ON bt.budget_id = b.id
         WHERE bc.level = 1 AND bc.is_active = 1
         GROUP BY bc.id, bc.name_th
         ORDER BY bc.sort_order",
        [$fiscalYear]
    );
}
```

> [!NOTE]
> ใช้ `MONTH(bt.created_at)` แทน column `quarter` เพราะอาจยังไม่มีในตาราง

**Add monthly trend data method**:
```php
/**
 * Get monthly spending trend data for chart (12 months)
 */
private function getMonthlyTrendData(int $fiscalYear): array
{
    $gregorianYear = $fiscalYear - 543;
    
    $monthlyData = Database::query(
        "SELECT 
            MONTH(bt.created_at) as month,
            SUM(bt.amount) as total
         FROM budget_trackings bt
         INNER JOIN budgets b ON bt.budget_id = b.id
         WHERE b.fiscal_year = ?
         GROUP BY MONTH(bt.created_at)
         ORDER BY month",
        [$fiscalYear]
    );
    
    // Initialize 12 months array (Oct to Sep)
    $trend = array_fill(0, 12, 0);
    
    // Map data to fiscal year order (Oct=0, Nov=1, ..., Sep=11)
    foreach ($monthlyData as $row) {
        $month = (int)$row['month'];
        // Convert calendar month to fiscal month index
        $fiscalIndex = ($month >= 10) ? ($month - 10) : ($month + 2);
        $trend[$fiscalIndex] = (float)$row['total'];
    }
    
    return $trend;
}
```

**Update index() method**:
```php
View::render('dashboard/index', [
    // ... existing data ...
    'quarterlyData' => $this->getQuarterlyData($fiscalYear),
    'trend' => $this->getMonthlyTrendData($fiscalYear),
]);
```

---

### 4. JavaScript/Chart Changes

#### [ADD] Chart.js Configuration

> [!TIP]
> เพิ่มใน `<script>` section ท้ายไฟล์ `dashboard/index.php`

**Timeline Chart (Line)**:
```javascript
const trendCtx = document.getElementById('trendChart')?.getContext('2d');
if (trendCtx) {
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'],
            datasets: [{
                label: 'เบิกจ่าย (บาท)',
                data: window.chartData?.trend || [],
                borderColor: '#f97316',
                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    labels: { color: '#9ca3af', font: { size: 12 } } 
                },
                tooltip: {
                    backgroundColor: '#1e1e2e',
                    titleColor: '#ffffff',
                    bodyColor: '#9ca3af',
                    borderColor: '#374151',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + 
                                   new Intl.NumberFormat('th-TH', {
                                       style: 'currency',
                                       currency: 'THB',
                                       minimumFractionDigits: 0
                                   }).format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                x: { 
                    grid: { color: '#374151', drawBorder: false }, 
                    ticks: { color: '#9ca3af', font: { size: 11 } } 
                },
                y: { 
                    grid: { color: '#374151', drawBorder: false }, 
                    ticks: { 
                        color: '#9ca3af', 
                        font: { size: 11 },
                        callback: function(value) {
                            return new Intl.NumberFormat('th-TH', {
                                notation: 'compact',
                                compactDisplay: 'short'
                            }).format(value);
                        }
                    }, 
                    beginAtZero: true 
                }
            }
        }
    });
}
```

**Donut Chart**:
```javascript
const donutCtx = document.getElementById('categoryDonutChart')?.getContext('2d');
if (donutCtx) {
    new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            labels: window.chartData?.category?.labels || [],
            datasets: [{
                data: window.chartData?.category?.values || [],
                backgroundColor: [
                    '#3b82f6', // Blue
                    '#22c55e', // Green  
                    '#f97316', // Orange
                    '#ef4444', // Red
                    '#8b5cf6', // Purple
                    '#06b6d4', // Cyan
                    '#eab308', // Yellow
                    '#ec4899'  // Pink
                ],
                borderColor: '#1e1e2e',
                borderWidth: 2,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: { 
                legend: { 
                    position: 'bottom', 
                    labels: { 
                        color: '#9ca3af',
                        font: { size: 11 },
                        padding: 12,
                        usePointStyle: true,
                        boxWidth: 8
                    } 
                },
                tooltip: {
                    backgroundColor: '#1e1e2e',
                    titleColor: '#ffffff',
                    bodyColor: '#9ca3af',
                    borderColor: '#374151',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + 
                                   new Intl.NumberFormat('th-TH', {
                                       style: 'currency',
                                       currency: 'THB',
                                       minimumFractionDigits: 0
                                   }).format(value) + 
                                   ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}
```

---

## UI Mockup

```
┌──────────────────────────────────────────────────────────────────────┐
│ สรุปภาพรวมงบประมาณประจำปี                      [ปีงบประมาณ: 2568 ▼] │
├──────────────────────────────────────────────────────────────────────┤
│ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐  │
│ │ งบจัดสรร     │ │ เบิกจ่าย    │ │ คงเหลือ      │ │ อัตราเบิกจ่าย│  │
│ │ ฿100,000,000 │ │ ฿45,000,000 │ │ ฿55,000,000  │ │ 45% [ปานกลาง]│  │
│ └──────────────┘ └──────────────┘ └──────────────┘ └──────────────┘  │
├──────────────────────────────────────────────────────────────────────┤
│ ┌────────────────────────────────────┐ ┌──────────────────────────┐  │
│ │ แนวโน้มการเบิกจ่าย (Timeline)     │ │ สัดส่วนตามหมวดหมู่       │  │
│ │   📈 Line Chart                   │ │   🍩 Donut Chart         │  │
│ └────────────────────────────────────┘ └──────────────────────────┘  │
├──────────────────────────────────────────────────────────────────────┤
│ ผลการเบิกจ่ายตามไตรมาส                           [ดูรายละเอียด →]  │
│ ┌──────────────────────────────────────────────────────────────────┐ │
│ │ หมวดหมู่ │ งบจัดสรร │ Q1  │ Q2  │ Q3  │ Q4  │ รวม  │ คงเหลือ│ KPI%│ │
│ ├──────────┼──────────┼─────┼─────┼─────┼─────┼──────┼────────┼─────┤ │
│ │ บุคลากร │ 50M      │ 10M │ 12M │ 8M  │ 5M  │ 35M  │ 15M    │ 70% │ │
│ └──────────────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────────────┘
```

---

## Verification Plan

### 1. Pre-Implementation Checks

**Search & Replace `/execution` references**:
```bash
# Search for all /execution references
grep -r "/execution" resources/views/ src/
grep -r "execution" resources/views/ | grep -i "url\|href\|action"
```

**Files to check**:
- [ ] `resources/views/layouts/main.php` - Sidebar menu
- [ ] `resources/views/dashboard/index.php` - Quick links (จะลบอยู่แล้ว)
- [ ] `resources/views/budgets/execution.php` - Export link
- [ ] `public/js/*.js` - AJAX calls
- [ ] Email templates (ถ้ามี)

**Update all references**:
```bash
# แทนที่ทั้งหมด
/execution → /budgets
/execution/export → /budgets/export
```

---

### 2. URL Routing Test

| Test | Expected |
|------|----------|
| `/` | แสดง Dashboard (UI ใหม่) |
| `/budgets` | แสดง Execution view (เดิมคือ `/execution`) |
| `/execution` | Redirect ไป `/budgets` |
| `/budgets/list` | แสดง Budget List (ตาราง Q1-Q4 เดิม) |
| `/requests` | แสดง Requests List (เหมือนเดิม) |

### 3. Visual Verification (หน้า `/`)

**Header & Fiscal Year Selector**:
- [ ] ไม่มี `<h1>ภาพรวมงบประมาณ</h1>`
- [ ] Fiscal year selector แสดง 🟢 สำหรับปีปัจจุบัน
- [ ] Fiscal year selector แสดง 🔒 สำหรับปีปิด

**KPI Cards**:
- [ ] มี KPI Card "อัตราการเบิกจ่าย" พร้อม badge (ดีมาก/ปานกลาง/ต่ำ)
- [ ] Badge สีถูกต้อง: เขียว (≥80%), ส้ม (50-79%), แดง (<50%)

**Charts**:
- [ ] มี Timeline Chart (Line Chart 12 เดือน)
- [ ] Hover บน Timeline แสดง tooltip format เงินบาท
- [ ] มี Donut Chart สัดส่วนหมวดหมู่
- [ ] Hover บน Donut แสดง tooltip พร้อมเปอร์เซ็นต์

**Table**:
- [ ] มีตาราง 9 คอลัมน์ (หมวดหมู่ | งบจัดสรร | Q1-Q4 | รวม | คงเหลือ | KPI%)
- [ ] KPI % คำนวณถูกต้อง (รวม / งบจัดสรร × 100)
- [ ] สี KPI ถูกต้อง

### 4. Functional Verification

**Data Accuracy**:
- [ ] เปลี่ยนปีงบประมาณ → ข้อมูลทั้งหมดเปลี่ยนตาม
- [ ] KPI % คำนวณถูกต้อง (รวมเบิกจ่าย / งบจัดสรร × 100)
- [ ] Q1+Q2+Q3+Q4 = รวมเบิกจ่าย
- [ ] งบจัดสรร - รวมเบิกจ่าย = คงเหลือ
- [ ] Timeline chart แสดงข้อมูล 12 เดือน (ต.ค. - ก.ย.)

**Links & Navigation**:
- [ ] ลิงก์ "ดูรายละเอียด" ไปที่ `/budgets/list` ถูกต้อง
- [ ] เมนู sidebar ไม่มีลิงก์ `/execution` เหลืออยู่
- [ ] Export button (ถ้ามี) ใช้ URL ใหม่

**Error Handling**:
- [ ] ไม่มี 500 errors ในทุกหน้า
- [ ] ไม่มี console errors ใน browser
- [ ] Charts render สำเร็จ (ไม่มี blank canvas)

---

## Dependencies

- ✅ `Chart.js` (existing)
- ✅ `BudgetExecutionController` (existing)
- ✅ `budget_categories`, `budgets` tables (existing)

---

## Notes

> [!IMPORTANT]
> **หน้า `/budgets`** จะใช้ view `budgets/execution.php` เดิมโดยไม่ต้องแก้ไขอะไร
> - มี Filter Bar อยู่แล้ว
> - มี Charts อยู่แล้ว (งบตามหน่วยงาน, สัดส่วนตามโครงสร้าง)
> - มีตาราง "รายละเอียดการเบิกจ่าย" อยู่แล้ว

> [!TIP]
> ถ้าต้องการอัพเดทลิงก์ใน view เดิมที่อ้างอิง `/execution` ให้เปลี่ยนเป็น `/budgets`
