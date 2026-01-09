# แผนแก้ไข BudgetExecution Model

> **สถานะ:** 🔄 Draft - รอรีวิว  
> **วันที่สร้าง:** 2025-12-23  
> **อ้างอิง:** phase_db_restructuring_organizations.md (ที่ทำให้เกิดปัญหา)

---

## 1. สรุปปัญหา

### 1.1 Root Cause
Migration 017 ลบตาราง `fact_budget_execution` และ dimensional tables ทำให้:
- `/budgets` → 500 Error (BudgetExecution::getKpiStats)
- `/budgets/list` → 500 Error (BudgetExecution::getWithStructure)

### 1.2 ไฟล์ที่ได้รับผลกระทบ

| ไฟล์ | สถานะ | ปัญหา |
|------|-------|-------|
| `BudgetExecution.php` | ❌ Error | ใช้ `fact_budget_execution` ที่ถูกลบ |
| `BudgetStructure.php` | ❌ Error | ใช้ `fact_budget_execution` และ `dim_budget_structure` |
| `BudgetExecutionController.php` | ❌ Error | เรียก BudgetExecution |
| `BudgetController.php` | ❌ Error | เรียก BudgetExecution::getWithStructure |

---

## 2. ทางเลือกในการแก้ไข

### Option A: Refactor ใช้ budget_allocations (แนะนำ)
- ใช้ `budget_allocations` แทน `fact_budget_execution`
- ใช้ `budget_plans` แทน `dim_budget_structure`
- ใช้ `organizations` แทน `dim_organization`
- **ข้อดี:** Clean, ตรงกับ schema ใหม่
- **ข้อเสีย:** ต้องปรับ queries ทั้งหมด

### Option B: Disable Routes
- Comment out routes ที่มีปัญหา
- แสดงหน้า "Under Maintenance"
- **ข้อดี:** แก้ไวมาก
- **ข้อเสีย:** ฟีเจอร์หายไปชั่วคราว

### Option C: Restore fact_budget_execution
- Rollback migration 017 หรือสร้างตารางใหม่
- **ข้อดี:** กลับมาใช้งานได้ทันที
- **ข้อเสีย:** ขัดกับทิศทางการ restructure

---

## 3. Proposed Changes (Option A)

### Phase 1: Update BudgetExecution Model

#### [MODIFY] `src/Models/BudgetExecution.php`

**Column Mapping:**

| Old (fact_budget_execution) | New (budget_allocations) |
|----------------------------|-------------------------|
| `budget_act_amount` | `allocated_pba` |
| `budget_allocated_amount` | `allocated_received` |
| `transfer_change_amount` | (คำนวณจาก budget_transfers) |
| `budget_net_balance` | `net_budget` |
| `disbursed_amount` | `disbursed` |
| `request_amount` | `pending_approval` |
| `po_pending_amount` | `po_commitment` |
| `balance_amount` | `remaining` |
| `structure_id` | `plan_id` + `item_id` |

**Join Mapping:**

| Old Table | New Table |
|-----------|-----------|
| `dim_budget_structure` | `budget_plans` + `budget_category_items` |
| `dim_organization` | `organizations` |

---

### Phase 2: Update Controllers

#### [MODIFY] `src/Controllers/BudgetExecutionController.php`
- ไม่ต้องแก้ไขมาก ถ้า Model return format เดิม

#### [MODIFY] `src/Controllers/BudgetController.php`
- อาจต้องแก้ไข filters ให้ตรงกับ column ใหม่

---

### Phase 3: Update BudgetStructure Model

#### [MODIFY] `src/Models/BudgetStructure.php`
- แก้ไขให้ใช้ `budget_plans` แทน `dim_budget_structure`

---

## 4. Implementation Order

| ลำดับ | งาน | เวลาประมาณ |
|------|-----|-----------|
| 1 | Refactor BudgetExecution model | 30 นาที |
| 2 | Update BudgetStructure model | 15 นาที |
| 3 | Test /budgets route | 5 นาที |
| 4 | Test /budgets/list route | 5 นาที |
| 5 | Fix any remaining issues | 15 นาที |

**รวม:** ~70 นาที (~1 ชั่วโมง)

---

## 5. Verification Plan

### 5.1 Route Testing
- [ ] `/budgets` - แสดง Dashboard ไม่ error
- [ ] `/budgets/list` - แสดงรายการ (อาจว่างถ้าไม่มีข้อมูล)
- [ ] `/budgets/export` - ทดสอบ export (ถ้าใช้งาน)

### 5.2 Data Verification
```sql
-- ตรวจสอบว่า budget_allocations มีอยู่
SHOW TABLES LIKE 'budget_allocations';

-- นับจำนวนข้อมูล
SELECT COUNT(*) FROM budget_allocations;
```

---

## 6. Rollback Plan

หากมีปัญหา:
1. ปิดใช้งาน routes ชั่วคราว
2. Restore code จาก Git
3. พิจารณา Option B หรือ C

---

## 7. คำถามก่อนดำเนินการ

1. ✅/❌ มีข้อมูลใน `budget_allocations` ที่ต้องแสดงหรือไม่?
2. ✅/❌ ต้องการให้ `/budgets` dashboard ทำงานต่อหรือไม่?
3. ✅/❌ อนุมัติให้ดำเนินการตาม Option A?

---

## 8. Notes

- PRP นี้เป็นผลมาจาก phase_db_restructuring_organizations.md
- หลังเสร็จสิ้นควรรีวิว Budget Routes ทั้งหมดอีกครั้ง
