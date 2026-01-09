# Phase 2: ผลการเบิกจ่ายงบประมาณ - Implementation Plan

> **Status**: 📝 Planning  
> **Start Date**: 2024-12-14

---

## 🎯 Goal

สร้างหน้าจัดการผลการเบิกจ่ายงบประมาณ ประกอบด้วย:
- Dashboard แสดง KPIs และ Charts
- รายการงบประมาณพร้อม CRUD
- ตัวกรองตามปีงบประมาณ

---

## 📋 Tasks

| # | Task | Priority |
|---|------|----------|
| 1 | Budget Model | P0 |
| 2 | BudgetCategory Model | P0 |
| 3 | BudgetController | P0 |
| 4 | Budget Dashboard View | P0 |
| 5 | Budget List View | P0 |
| 6 | Budget Create/Edit Form | P0 |
| 7 | Chart.js Integration | P0 |
| 8 | Fiscal Year Selector | P0 |

---

## 🏗️ Proposed Files

### Models

#### [NEW] src/Models/Budget.php
- CRUD operations
- Aggregation queries (SUM, AVG)
- Fiscal year filtering
- Category relationships

#### [NEW] src/Models/BudgetCategory.php
- Hierarchical category support
- Active categories filtering
- Sort by level/order

---

### Controllers

#### [NEW] src/Controllers/BudgetController.php
- `dashboard()` - KPIs, charts data
- `index()` - List with pagination
- `create()`, `store()` - New budget
- `edit()`, `update()` - Edit budget
- `destroy()` - Delete budget

---

### Views

#### [NEW] resources/views/budgets/dashboard.php
- KPI Cards: งบจัดสรร, เบิกจ่าย, คงเหลือ, อัตราการเบิกจ่าย
- Trend Chart: แนวโน้มเบิกจ่ายรายเดือน
- Category Chart: สัดส่วนตามหมวดหมู่
- Recent transactions

#### [NEW] resources/views/budgets/index.php
- Table with columns: หมวดหมู่, งบจัดสรร, เบิกจ่าย, คงเหลือ, สถานะ
- Pagination
- Search/filter
- Actions: View, Edit, Delete

#### [NEW] resources/views/budgets/form.php
- Full page form (not modal)
- Fields: หมวดหมู่, ปีงบประมาณ, งบจัดสรร, โอนเข้า/ออก, หมายเหตุ
- Validation

---

### Routes

```php
// Budget Routes
Router::get('/budgets', [BudgetController::class, 'dashboard']);
Router::get('/budgets/list', [BudgetController::class, 'index']);
Router::get('/budgets/create', [BudgetController::class, 'create']);
Router::post('/budgets', [BudgetController::class, 'store']);
Router::get('/budgets/{id}/edit', [BudgetController::class, 'edit']);
Router::post('/budgets/{id}', [BudgetController::class, 'update']);
Router::post('/budgets/{id}/delete', [BudgetController::class, 'destroy']);
```

---

## 🗄️ Existing Database Tables

### budgets (มีอยู่แล้ว)
| Column | Type |
|--------|------|
| id | int (PK) |
| category_id | int (FK) |
| fiscal_year | int |
| allocated_amount | decimal(15,2) |
| spent_amount | decimal(15,2) |
| target_amount | decimal(15,2) |
| transfer_in | decimal(15,2) |
| transfer_out | decimal(15,2) |
| status | enum |
| created_by | int (FK) |

---

## ✅ Verification

1. Budget Dashboard แสดง KPIs ถูกต้อง
2. Charts render (trend + category)
3. Budget list pagination ทำงาน
4. Create/Edit budget form validation
5. Delete with SweetAlert2 confirm
6. Fiscal year filter ทำงาน

---

## 📦 Dependencies

- Chart.js (installed)
- SweetAlert2 (installed)
