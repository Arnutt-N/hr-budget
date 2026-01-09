# Implementation Plan: Dashboard Consolidation

> **Created**: 2025-12-17 21:01:00  
> **Updated**: 2025-12-17 21:05:00  
> **Goal**: รวม "ติดตามผลการเบิกจ่าย" เข้ากับ "ภาพรวม (Dashboard)" เป็นหน้าเดียว

---

## Summary

รวม 2 หน้า Dashboard ที่ซ้ำซ้อนให้เหลือหน้าเดียว โดยใช้ **Dimensional Schema** (`fact_budget_execution`) เป็นแหล่งข้อมูลหลัก พร้อม Filter, Export Excel และ Chart ครบถ้วน

---

## Pre-Implementation

### Backup Strategy
```bash
# สร้าง Git branch
git checkout -b feature/dashboard-consolidation

# Backup files
cp src/Controllers/DashboardController.php src/Controllers/DashboardController.php.bak
cp resources/views/dashboard/index.php resources/views/dashboard/index.php.bak
```

---

## Proposed Changes

### 1. Controller Layer

#### [MODIFY] `DashboardController.php`

**Before** (Lines 50-97):
```php
private function getDashboardStats(int $fiscalYear): array
{
    // Uses budgets table (Legacy)
    $totalBudget = Database::queryOne(
        "SELECT COALESCE(SUM(allocated_amount), 0) as total FROM budgets..."
    );
    // ...
}
```

**After**:
```php
private function getDashboardStats(int $fiscalYear, array $filters = []): array
{
    // Use BudgetExecution Model (Dimensional)
    return BudgetExecution::getKpiStats($fiscalYear, $filters);
}
```

**Changes**:
- Replace `getDashboardStats()` to use `BudgetExecution::getKpiStats()`
- Replace `getBudgetByCategory()` with `BudgetExecution::getWithStructure()`
- Add `$filters` parameter support (org, plan, search)
- Add `getChartDataByOrg()` method

#### [DELETE] ~~`BudgetExecutionController.php`~~
- Copy logic to `DashboardController` first
- Delete file after verification

---

### 2. View Layer

#### [MODIFY] `views/dashboard/index.php`

**Add Filter Section** (Insert after line 19):
```php
<!-- Filter Bar -->
<div class="bg-dark-card border border-dark-border rounded-xl p-4 mb-6">
    <form method="GET" action="<?= \App\Core\View::url('/') ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <select name="org" class="input">
            <option value="">ทุกหน่วยงาน</option>
            <?php foreach ($organizations ?? [] as $org): ?>
            <option value="<?= $org['id'] ?>" <?= ($filters['org'] ?? '') == $org['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($org['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        
        <select name="plan" class="input">
            <option value="">ทุกแผนงาน</option>
            <?php foreach ($plans ?? [] as $plan): ?>
            <option value="<?= $plan ?>" <?= ($filters['plan'] ?? '') == $plan ? 'selected' : '' ?>>
                <?= htmlspecialchars($plan) ?>
            </option>
            <?php endforeach; ?>
        </select>
        
        <input type="text" name="search" class="input" placeholder="ค้นหา..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
        
        <button type="submit" class="btn btn-primary">กรอง</button>
    </form>
</div>
```

**Add Organization Chart** (Insert after Category Chart):
```php
<!-- Organization Comparison Chart -->
<div class="bg-dark-card border border-dark-border rounded-xl p-6">
    <h3 class="text-lg font-semibold text-white mb-4">เปรียบเทียบตามหน่วยงาน</h3>
    <div class="h-64 relative w-full">
        <canvas id="orgChart"></canvas>
    </div>
</div>
```

**Add Export Button** (Insert after Fiscal Year Selector):
```php
<a href="<?= \App\Core\View::url('/dashboard/export') ?>?year=<?= $fiscalYear ?>&<?= http_build_query($filters ?? []) ?>" 
   class="btn btn-secondary">
    <i class="ph ph-download-simple"></i> Export Excel
</a>
```

#### [DELETE] ~~`views/budgets/execution.php`~~

---

### 3. Routes

#### [MODIFY] `routes/web.php`

```diff
// Dashboard
Router::get('/', [DashboardController::class, 'index']);
Router::get('/dashboard', [DashboardController::class, 'index']);
+ Router::get('/dashboard/export', [DashboardController::class, 'exportExcel']);

- // Budget Execution (Old)
- Router::get('/budgets', [BudgetExecutionController::class, 'dashboard']);

+ // Budget List (Redirect /budgets to /budgets/list)
+ Router::get('/budgets', function() { Router::redirect('/budgets/list'); });
Router::get('/budgets/list', [BudgetController::class, 'index']);
```

---

### 4. Navigation

#### [MODIFY] `views/layouts/main.php`

```diff
  <a href="/">ภาพรวม (Dashboard)</a>
- <a href="/budgets">ติดตามผลการเบิกจ่าย</a>
  <a href="/budgets/list">รายการงบประมาณ</a>
```

---

## Result

| Aspect | Before | After |
|:---|:---|:---|
| **Dashboard URL** | `/` (Legacy data) | `/` (Dimensional + Filters) |
| **Execution URL** | `/budgets` (Duplicate view) | Redirects to `/budgets/list` |
| **Data Source** | `budgets` table | `fact_budget_execution` |
| **Features** | Timeline + Category charts | Timeline + Category + Org charts + Filters + Export |
| **Menu Items** | 3 (Dashboard, Execution, List) | 2 (Dashboard, List) |

---

## Verification Plan (Extended)

### Functional Tests
1. ✅ เปิด `/` แสดง KPI จาก `fact_budget_execution`
2. ✅ Filter ตาม Organization/Plan/Search ได้
3. ✅ กราฟ 3 แบบแสดงถูกต้อง (Timeline, Category, Organization)
4. ✅ Export Excel ดาวน์โหลดได้
5. ✅ Recent Activities ยังแสดงปกติ
6. ✅ เมนู sidebar ไม่มีรายการซ้ำ

### Redirect Tests
7. ✅ เปิด `/budgets` → redirect ไป `/budgets/list` อัตโนมัติ
8. ✅ เปิด `/budgets/list` → แสดง Budget CRUD ปกติ

### Performance Tests
9. ✅ Dashboard load time < 2 วินาที
10. ✅ Filter response time < 1 วินาที

### Regression Tests
11. ✅ Chart.js ไม่ error
12. ✅ Fiscal year selector ทำงาน
13. ✅ User permissions ยังใช้งานได้

---

## Rollback Plan

หากเกิดปัญหา:

```bash
# Restore from backup
git checkout feature/dashboard-consolidation
git reset --hard HEAD~1

# Or restore specific files
cp src/Controllers/DashboardController.php.bak src/Controllers/DashboardController.php
cp resources/views/dashboard/index.php.bak resources/views/dashboard/index.php

# Restore route
# Edit routes/web.php manually to restore old routes
```

---

## Dependencies

- ✅ `BudgetExecution` Model (exists)
- ✅ `Organization` Model (exists)
- ✅ `fact_budget_execution` table (exists)
- ⚠️  Export script (need to create `/dashboard/export` endpoint)

