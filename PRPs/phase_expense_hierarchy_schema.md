# Phase: Budget Expense Hierarchy Schema

> **วันที่:** 2025-12-29  
> **สถานะ:** ✅ Complete (100%)  
> **Completed:** 2025-12-29 18:49  
> **Status Report:** [status_expense_hierarchy_2025-12-29.md](file:///c:/laragon/www/hr_budget/PRPs/status_expense_hierarchy_2025-12-29.md)

## สรุป (Summary)
ปรับโครงสร้างฐานข้อมูลให้รองรับโครงสร้างรายการค่าใช้จ่าย 6 ระดับ (รายการ 0 ถึง รายการ 5) ตามข้อมูลอ้างอิงในไฟล์ `database/budget_structure_reference.csv`

## เป้าหมาย (Goal)
ระบบปัจจุบันใช้โครงสร้างแบบ Flat สำหรับ `budget_category_items` แต่ต้องการรองรับ Hierarchy 6 ระดับ:
- **Item 0 (รายการ 0)** → Root level (e.g., "1. ค่าใช้จ่ายบุคลากร")
- **Item 1-5 (รายการ 1-5)** → Children items

---

## การเปลี่ยนแปลงที่เสนอ (Proposed Changes)

### 1. Database Schema

#### [MODIFY] `budget_category_items`
| Column      | Type           | Description                                  |
|-------------|----------------|----------------------------------------------|
| `parent_id` | INT, Nullable  | FK to `budget_category_items.id` (Self-ref)  |
| `level`     | TINYINT        | ระดับ 0-5 เพื่อ Query ง่าย                      |
| `code`      | VARCHAR(50)    | รหัส เช่น "1.1.1" (Optional)                  |

**Migration File**: [`database/migrations/022_add_hierarchy_to_category_items.sql`](file:///c:/laragon/www/hr_budget/database/migrations/022_add_hierarchy_to_category_items.sql)

**SQL Migration:**
```sql
-- Create table if not exists
CREATE TABLE IF NOT EXISTS budget_category_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(100) NULL,
    parent_id INT NULL,
    level TINYINT NOT NULL DEFAULT 0,
    CONSTRAINT fk_budget_category_parent 
        FOREIGN KEY (parent_id) 
        REFERENCES budget_category_items(id) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add columns if table already exists
ALTER TABLE budget_category_items
    ADD COLUMN IF NOT EXISTS parent_id INT NULL AFTER name,
    ADD COLUMN IF NOT EXISTS level TINYINT NOT NULL DEFAULT 0 AFTER parent_id,
    ADD COLUMN IF NOT EXISTS code VARCHAR(100) NULL AFTER level,
    ADD INDEX IF NOT EXISTS idx_parent (parent_id),
    ADD INDEX IF NOT EXISTS idx_level (level);
```

**Actual Files Created**:
- Analysis: [`scripts/analyze_budget_structure.php`](file:///c:/laragon/www/hr_budget/scripts/analyze_budget_structure.php)
- Schema Check: [`scripts/get_budget_category_items_schema.php`](file:///c:/laragon/www/hr_budget/scripts/get_budget_category_items_schema.php)


---

### 2. Data Seeder

#### [NEW] [`scripts/seed_budget_hierarchy.php`](file:///c:/laragon/www/hr_budget/scripts/seed_budget_hierarchy.php)
- อ่านข้อมูลจาก [`database/budget_structure_reference.csv`](file:///c:/laragon/www/hr_budget/database/budget_structure_reference.csv)
- สร้าง Items ตาม Hierarchy:
  - Level 0: `parent_id = NULL`
  - Level 1-5: `parent_id` ชี้ไปยัง parent ของระดับก่อนหน้า
- กรองค่า placeholder ("รายการย่อย ...")
- ใช้ cache เพื่อหลีกเลี่ยง duplicate inserts


---

### 3. Model Update

#### [MODIFY] [`src/Models/BudgetCategoryItem.php`](file:///c:/laragon/www/hr_budget/src/Models/BudgetCategoryItem.php)

**Added Methods** (Lines 33-65):
```php
// Get all children of a parent item
public static function getChildren(int $parentId): array

// Get parent of an item
public static function getParent(int $id): ?array

// Get full hierarchy tree for a category (recursive)
public static function getHierarchy(int $categoryId): array
```

---

## แผนการตรวจสอบ (Verification Plan)

### Automated
```bash
# Run migration (custom system)
php migrate_now.php

# Run seeder
php scripts\seed_budget_hierarchy.php

# Verify table structure
mysql -u root hr_budget -e "DESCRIBE budget_category_items;"

# Check data
mysql -u root hr_budget -e "SELECT id, name, parent_id, level FROM budget_category_items LIMIT 10;"
```

### Manual
- ตรวจสอบ DB ว่า root items มี `parent_id = NULL`
- ตรวจสอบ children มี `parent_id` ถูกต้อง
- นับจำนวน items ควรได้ ~86 items (จาก CSV analysis)
- ตรวจสอบ depth distribution ตรงกับที่วิเคราะห์ไว้

---

## Dependencies
- ไฟล์ข้อมูลอ้างอิง: [budget_structure_reference.csv](file:///c:/laragon/www/hr_budget/database/budget_structure_reference.csv)
- ตาราง: `budget_category_items`, `budget_categories`

## ความเสี่ยง (Risks)
> [!WARNING]
> ข้อมูลเดิมใน `budget_category_items` อาจต้อง Truncate หรือ Migrate ถ้ามีข้อมูลที่ไม่ตรงกับโครงสร้างใหม่

## Progress Tracking

### ✅ All Tasks Complete

#### Phase 1: Hierarchy Schema ✅
- [x] **CSV Analysis** - 86 unique items, 6 levels, depth distribution documented
- [x] **Migration**: [`022_add_hierarchy_to_category_items.sql`](file:///c:/laragon/www/hr_budget/database/migrations/022_add_hierarchy_to_category_items.sql)
- [x] **Seeder**: [`seed_budget_hierarchy.php`](file:///c:/laragon/www/hr_budget/scripts/seed_budget_hierarchy.php)
- [x] **Execution**: 86 items imported successfully
- [x] **Verification**: All levels present (0-5), parent-child relationships working

#### Phase 2: Admin Management ✅ (Additional)
- [x] **Migration**: [`024_add_admin_columns_to_category_items.sql`](file:///c:/laragon/www/hr_budget/database/migrations/024_add_admin_columns_to_category_items.sql)
  - Added: `created_at`, `updated_at`, `sort_order`, `is_active`
  - Added: `description`, `deleted_at`, `created_by`, `updated_by`
- [x] **Model CRUD**: Added to [`BudgetCategoryItem.php`](file:///c:/laragon/www/hr_budget/src/Models/BudgetCategoryItem.php)
  - `getAll($includeInactive, $includeDeleted)`
  - `create($data)`, `update($id, $data)`, `delete($id)`
  - `softDelete($id)`, `restore($id)`
  - `toggleActive($id)`, `updateSortOrder($id, $sortOrder)`

---

## 📊 Final Verification Results

| Query | Result |
|-------|--------|
| `SELECT COUNT(*) FROM budget_category_items` | **86 rows** ✓ |
| `SELECT COUNT(*) WHERE parent_id IS NULL` | **11 root items** ✓ |
| `SELECT COUNT(*) WHERE level = 2` | **45 items** (most common) ✓ |
| `DESCRIBE budget_category_items` | **13 columns** total ✓ |

---

## 🔗 Related Documents

| Document | Status | Purpose |
|----------|--------|---------|
| [status_expense_hierarchy_2025-12-29.md](file:///c:/laragon/www/hr_budget/PRPs/status_expense_hierarchy_2025-12-29.md) | ✅ Complete | Detailed status report |
| [phase_comprehensive_budget_system.md](file:///c:/laragon/www/hr_budget/PRPs/phase_comprehensive_budget_system.md) | 🟡 Related | Master plan (Phase 2 overlap) |
| [csv_analysis_report.md](file:///C:/Users/TOPP/.gemini/antigravity/brain/1fc7db1c-2df8-4b2c-bcf3-4604d7eda2e8/csv_analysis_report.md) | ✅ Complete | CSV analysis artifact |

---

## ✅ Next Steps

**This phase is complete.** Next work should focus on:

1. **Admin UI** for `budget_category_items`
   - Controller: `AdminBudgetCategoryItemController`
   - Views: `admin/category-items/index.php`, `form.php`
   - Routes: CRUD endpoints

2. **Integration** with existing budget system
   - Display hierarchy in tracking forms
   - Select items from tree structure
