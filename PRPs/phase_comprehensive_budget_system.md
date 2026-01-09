# Comprehensive Budget Management System

> **วันที่**: 2025-12-20  
> **Version**: 1.0  
> **Status**: 🟡 In Progress  
> **Last Updated**: 2025-12-29

---

## 📊 Implementation Progress

> [!NOTE]
> **Updated 2025-12-29**: Some components have been implemented as part of the Expense Hierarchy Schema phase.

### Completed ✅
| Component | Status | Reference |
|-----------|--------|-----------|
| `budget_category_items` hierarchy | ✅ Done | [phase_expense_hierarchy_schema.md](file:///c:/laragon/www/hr_budget/PRPs/phase_expense_hierarchy_schema.md) |
| Admin columns (timestamps, soft delete) | ✅ Done | Migration 024 |
| `BudgetCategoryItem` Model CRUD | ✅ Done | `src/Models/BudgetCategoryItem.php` |
| CSV data seeder | ✅ Done | 86 items imported |

### In Progress 🟡
| Component | Status | Notes |
|-----------|--------|-------|
| Admin Category Items UI | ⏳ Pending | Controller + Views needed |
| Organizations enhancement | ⏳ Pending | Phase 3 |

### Not Started ⏳
- Admin Organizations UI
- Multi-Tab Budget Tracking UI
- Target Types & Budget Targets
- Budget allocation tracking

---

## 1. ภาพรวมโครงการ

### 🎯 วัตถุประสงค์

ปรับปรุงระบบบันทึกงบประมาณให้รองรับ:
1. **ทุกหมวดหมู่งบประมาณ** (งบบุคลากร, งบดำเนินงาน, งบลงทุน, งบอุดหนุน, รายจ่ายอื่น)
2. **Admin Module** สำหรับจัดการ Master Data (หมวดหมู่, หน่วยงาน, แผนงาน)
3. **Multi-Tab + Dynamic Form** สำหรับบันทึกงบประมาณ (2 ครั้ง/เดือน)

### 📊 สถานะปัจจุบัน

| มีแล้ว ✅ | ยังไม่มี ❌ |
|-----------|------------|
| `budget_categories` (งบบุคลากร, งบดำเนินงาน) | หมวดหมู่อื่น (ลงทุน, อุดหนุน, รายจ่ายอื่น) |
| `budget_category_items` (รายการย่อย) | Admin CRUD หมวดหมู่ |
| `budget_trackings` (tracking data) | Admin หน่วยงาน (organizations) |
| หน้า `/budgets/create` (tracking form เดิม) | Multi-Tab UI |

---

## 2. User Review Required

> [!IMPORTANT]
> **กรุณา Review รายการต่อไปนี้ก่อนดำเนินการ**

### 2.1 โครงสร้างหน่วยงาน

ระบบจะสร้างตาราง `organizations` แบบ 3 ระดับ:
```
กรม (level 0) → กอง (level 1) → ฝ่าย (level 2)
```

**โครงสร้างตาราง**:
- `code`: รหัสหน่วยงาน
- `name_th`: ชื่อไทย
- `abbreviation`: ชื่อย่อ
- `budget_allocated`: งบประมาณจัดสรรต่อหน่วยงาน

### 2.2 โครงสร้างแผนงาน

ตามที่คุยไว้ แผนงานบางรายการ = หมวดหมู่งบประมาณ (เช่น แผนงานบุคลากรภาครัฐ = งบบุคลากร)

**แนวทางที่เลือก**: เพิ่ม field `is_plan = true` ใน `budget_categories` แทนการสร้างตารางใหม่ โดยแอดมินสามารถกำหนดได้เองจากหน้าจัดการหมวดหมู่

### 2.3 การจัดการหมวดหมู่งบประมาณ

แอดมินจะเป็นผู้เพิ่มหมวดใหม่ (งบลงทุน, งบเงินอุดหนุน, รายจ่ายอื่น) และรายการย่อยเองผ่านหน้าระบบจัดการหมวดหมู่งบประมาณ (Admin Category Management) เพื่อความยืดหยุ่น

---

## 3. Proposed Changes

### Phase 1: Database Schema Updates

#### [NEW] organizations table

```sql
CREATE TABLE organizations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    parent_id INT DEFAULT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    name_th VARCHAR(255) NOT NULL,
    abbreviation VARCHAR(100) DEFAULT NULL,
    budget_allocated DECIMAL(15,2) DEFAULT 0.00,
    level INT NOT NULL DEFAULT 0 COMMENT '0=กรม, 1=กอง, 2=ฝ่าย',
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES organizations(id) ON DELETE CASCADE
);
```

#### [MODIFY] budget_categories table

เพิ่ม field:
```sql
ALTER TABLE budget_categories 
ADD COLUMN is_plan BOOLEAN DEFAULT FALSE COMMENT 'ใช้เป็นแผนงานด้วย',
ADD COLUMN plan_name VARCHAR(255) DEFAULT NULL COMMENT 'ชื่อแผนงาน (ถ้าแตกต่างจากชื่อหมวดหมู่)';
```

#### [MODIFY] budget_trackings table

เพิ่ม field สำหรับแยกตามหน่วยงาน:
```sql
ALTER TABLE budget_trackings
ADD COLUMN organization_id INT DEFAULT NULL COMMENT 'หน่วยงาน (NULL = ทุกหน่วยงาน)',
ADD KEY idx_organization (organization_id),
ADD CONSTRAINT fk_trackings_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL;
```

#### [NEW] target_types table (ประเภทเป้าหมาย)

```sql
CREATE TABLE target_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    name_th VARCHAR(255) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ตัวอย่างข้อมูลเริ่มต้น
INSERT INTO target_types (code, name_th, sort_order) VALUES
('BUDGET_ACT', 'ตาม พ.ร.บ.งบประมาณ', 1),
('AGENCY_PLAN', 'ตามแผนหน่วยงาน', 2),
('CABINET', 'มติ ครม.', 3),
('KPI', 'ตัวชี้วัด KPI', 4);
```

#### [NEW] budget_targets table (ค่าเป้าหมาย)

รองรับ: แยกหน่วยงาน + แยกหมวดหมู่งบ + รายปี/ไตรมาส

```sql
CREATE TABLE budget_targets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    target_type_id INT NOT NULL,
    fiscal_year INT NOT NULL,
    quarter INT DEFAULT NULL COMMENT 'NULL=เป้าหมายรายปี, 1-4=ไตรมาส',
    organization_id INT DEFAULT NULL COMMENT 'NULL=ทุกหน่วยงาน',
    category_id INT DEFAULT NULL COMMENT 'NULL=ทุกหมวดหมู่',
    target_percent DECIMAL(5,2) COMMENT 'เป้าหมาย %',
    target_amount DECIMAL(15,2) COMMENT 'เป้าหมายจำนวนเงิน',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (target_type_id) REFERENCES target_types(id),
    FOREIGN KEY (organization_id) REFERENCES organizations(id),
    FOREIGN KEY (category_id) REFERENCES budget_categories(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    
    UNIQUE KEY unique_target (target_type_id, fiscal_year, quarter, organization_id, category_id)
);
```

**ตัวอย่างข้อมูล**:

| ประเภท | ปี | ไตรมาส | หน่วยงาน | หมวดหมู่ | % |
|--------|-----|--------|----------|----------|-----|
| พ.ร.บ.งบประมาณ | 2568 | Q1 | (ทั้งกรม) | (ทุกหมวด) | 27% |
| พ.ร.บ.งบประมาณ | 2568 | Q2 | (ทั้งกรม) | (ทุกหมวด) | 52% |
| แผนหน่วยงาน | 2568 | Q1 | กองคลัง | งบบุคลากร | 30% |

### Phase 2: Admin Module - Budget Categories

#### [NEW] [AdminBudgetCategoryController.php](file:///c:/laragon/www/hr_budget/src/Controllers/AdminBudgetCategoryController.php)

- `index()` - List all categories (Tree view)
- `create()` - Form สร้างหมวดหมู่ใหม่
- `store()` - บันทึกหมวดหมู่ใหม่
- `edit($id)` - Form แก้ไข
- `update($id)` - บันทึกการแก้ไข
- `destroy($id)` - ลบ (soft delete)

#### [NEW] [resources/views/admin/categories/](file:///c:/laragon/www/hr_budget/resources/views/admin/categories/)

- `index.php` - หน้ารายการหมวดหมู่ (Tree UI)
- `form.php` - Form สร้าง/แก้ไข

---

### Phase 3: Admin Module - Organizations

#### [NEW] [AdminOrganizationController.php](file:///c:/laragon/www/hr_budget/src/Controllers/AdminOrganizationController.php)

- CRUD operations สำหรับหน่วยงาน (กรม/กอง/ฝ่าย)

#### [NEW] [Organization.php](file:///c:/laragon/www/hr_budget/src/Models/Organization.php)

- Model สำหรับจัดการหน่วยงาน

#### [NEW] [resources/views/admin/organizations/](file:///c:/laragon/www/hr_budget/resources/views/admin/organizations/)

- `index.php` - หน้ารายการหน่วยงาน
- `form.php` - Form สร้าง/แก้ไข

---

### Phase 4: Multi-Tab Budget Tracking UI

#### [MODIFY] [tracking.php](file:///c:/laragon/www/hr_budget/resources/views/budgets/tracking.php)

ปรับปรุง UI เป็น Multi-Tab:

```
┌──────────────────────────────────────────────────────────────────┐
│  📊 บันทึกงบประมาณ ปี 2568              [เลือกเดือน▼] [บันทึก]  │
├──────────────────────────────────────────────────────────────────┤
│ [Tab ดึงจาก budget_categories level=1]                           │
│ [งบบุคลากร] [งบดำเนินงาน] [งบลงทุน] [งบอุดหนุน] [รายจ่ายอื่น]   │
├──────────────────────────────────────────────────────────────────┤
│                                                                    │
│  เนื้อหา Tab (Dynamic - ดึงจาก budget_category_items)             │
│                                                                    │
└──────────────────────────────────────────────────────────────────┘
```

**คุณสมบัติ**:
- **Dynamic Tabs**: Tab ดึงจาก DB อัตโนมัติ (เพิ่มหมวดใหม่ก็ขึ้น Tab ใหม่)
- **Lazy Loading**: โหลดเนื้อหา tab เมื่อคลิกเท่านั้น (ไม่โหลดทุก tab พร้อมกัน)
- **Organization Filter**: เลือกหน่วยงาน (กรม/กอง/ฝ่าย) แล้วแสดงข้อมูลเฉพาะหน่วยงานนั้น
- **Month Selector**: เลือกเดือนที่จะบันทึก
- **History**: ประวัติการบันทึกย้อนหลัง
- **Summary Bar**: แสดงยอดรวมทุกหมวด

---

### Phase 5: Routes & Navigation

#### [MODIFY] [routes/web.php](file:///c:/laragon/www/hr_budget/routes/web.php)

เพิ่ม routes:

```php
// Admin - Budget Categories
'/admin/categories' => 'AdminBudgetCategoryController@index'
'/admin/categories/create' => 'AdminBudgetCategoryController@create'
'/admin/categories/store' => 'AdminBudgetCategoryController@store'
'/admin/categories/{id}/edit' => 'AdminBudgetCategoryController@edit'
'/admin/categories/{id}/update' => 'AdminBudgetCategoryController@update'
'/admin/categories/{id}/delete' => 'AdminBudgetCategoryController@destroy'

// Admin - Organizations
'/admin/organizations' => 'AdminOrganizationController@index'
'/admin/organizations/create' => 'AdminOrganizationController@create'
// ... (CRUD routes)

// Admin - Target Types
'/admin/target-types' => 'AdminTargetTypeController@index'
'/admin/target-types/create' => 'AdminTargetTypeController@create'
'/admin/target-types/store' => 'AdminTargetTypeController@store'
'/admin/target-types/{id}/edit' => 'AdminTargetTypeController@edit'
'/admin/target-types/{id}/update' => 'AdminTargetTypeController@update'
'/admin/target-types/{id}/delete' => 'AdminTargetTypeController@destroy'

// Budget Targets
'/budgets/targets' => 'BudgetTargetController@index'
'/budgets/targets/create' => 'BudgetTargetController@create'
'/budgets/targets/store' => 'BudgetTargetController@store'
'/budgets/targets/{id}/edit' => 'BudgetTargetController@edit'
'/budgets/targets/{id}/update' => 'BudgetTargetController@update'
```

---

### Phase 6: Admin Target Management

#### [NEW] [AdminTargetTypeController.php](file:///c:/laragon/www/hr_budget/src/Controllers/AdminTargetTypeController.php)

- CRUD operations สำหรับประเภทเป้าหมาย

#### [NEW] [TargetType.php](file:///c:/laragon/www/hr_budget/src/Models/TargetType.php)

- Model สำหรับจัดการประเภทเป้าหมาย

#### [NEW] [BudgetTargetController.php](file:///c:/laragon/www/hr_budget/src/Controllers/BudgetTargetController.php)

- CRUD operations สำหรับตั้งค่าเป้าหมาย

#### [NEW] [BudgetTarget.php](file:///c:/laragon/www/hr_budget/src/Models/BudgetTarget.php)

- Model สำหรับจัดการค่าเป้าหมาย

#### [NEW] [resources/views/admin/target-types/](file:///c:/laragon/www/hr_budget/resources/views/admin/target-types/)

- `index.php` - หน้ารายการประเภทเป้าหมาย
- `form.php` - Form สร้าง/แก้ไข

#### [NEW] [resources/views/budgets/targets/](file:///c:/laragon/www/hr_budget/resources/views/budgets/targets/)

- `index.php` - หน้ารายการเป้าหมาย (filter ตามประเภท/ปี/ไตรมาส)
- `form.php` - Form ตั้งค่าเป้าหมาย

---

### Phase 7: Seed Data

#### [NEW] [database/seeds/](file:///c:/laragon/www/hr_budget/database/seeds/)

**ข้อมูลเบื้องต้นที่จะสร้าง**:

1. **Organizations** (`001_seed_organizations.sql`):
   ```sql
   -- กรมตัวอย่าง
   INSERT INTO organizations (code, name_th, abbreviation, level) VALUES
   ('DEPT001', 'กรมยุติธรรม', 'กยธ.', 0);
   
   -- กองภายใต้กรม
   INSERT INTO organizations (parent_id, code, name_th, abbreviation, level) VALUES
   (1, 'DIV001', 'กองคลัง', 'กค.', 1),
   (1, 'DIV002', 'กองบริหารทรัพยากรบุคคล', 'กบค.', 1);
   
   -- ฝ่ายภายใต้กอง
   INSERT INTO organizations (parent_id, code, name_th, abbreviation, level) VALUES
   (2, 'SEC001', 'ฝ่ายงบประมาณ', 'ฝงป.', 2),
   (3, 'SEC002', 'ฝ่ายพัฒนาบุคลากร', 'ฝพค.', 2);
   ```

2. **Budget Categories** (`002_seed_categories.sql`):
   ```sql
   -- งบลงทุน
   INSERT INTO budget_categories (code, name_th, level, sort_order) VALUES
   ('INVESTMENT', 'งบลงทุน', 1, 3);
   
   -- งบเงินอุดหนุน
   INSERT INTO budget_categories (code, name_th, level, sort_order) VALUES
   ('SUBSIDY', 'งบเงินอุดหนุน', 1, 4);
   
   -- งบรายจ่ายอื่น
   INSERT INTO budget_categories (code, name_th, level, sort_order) VALUES
   ('OTHER', 'งบรายจ่ายอื่น', 1, 5);
   ```

3. **Target Types** (มีอยู่แล้วใน migration)

4. **Sample Targets** (`003_seed_sample_targets.sql`):
   ```sql
   -- เป้าหมายตาม พ.ร.บ. งบประมาณ ปี 2568
   INSERT INTO budget_targets (target_type_id, fiscal_year, quarter, target_percent) VALUES
   (1, 2568, 1, 27.00),
   (1, 2568, 2, 52.00),
   (1, 2568, 3, 75.00),
   (1, 2568, 4, 100.00);
   ```

---

## 4. Implementation Order

| ลำดับ | งาน | ไฟล์หลัก | ประมาณการ |
|-------|-----|----------|-----------|
| 1 | Database Migration | `migrations/xxx_organizations.sql` | 30 นาที |
| 2 | Database Migration | `migrations/xxx_targets.sql` | 30 นาที |
| 3 | Database Migration | `migrations/xxx_modify_trackings.sql` | 15 นาที |
| 4 | Seed Data | `seeds/*.sql` | 30 นาที |
| 5 | Organization Model | `Models/Organization.php` | 30 นาที |
| 6 | Target Models | `Models/TargetType.php`, `Models/BudgetTarget.php` | 30 นาที |
| 7 | Admin Categories UI | `views/admin/categories/*` | 2 ชั่วโมง |
| 8 | Admin Organizations UI | `views/admin/organizations/*` | 2 ชั่วโมง |
| 9 | Admin Target Types UI | `views/admin/target-types/*` | 1 ชั่วโมง |
| 10 | Budget Targets UI | `views/budgets/targets/*` | 2 ชั่วโมง |
| 11 | Multi-Tab Tracking UI | `views/budgets/tracking.php` (Lazy Load + Org Filter) | 4 ชั่วโมง |
| 12 | Routes & Testing | `routes/web.php` | 2 ชั่วโมง |

**Total**: ~16 ชั่วโมง

---

## 5. Verification Plan

### 5.1 Manual Testing

#### Admin Categories

1. เปิด `http://localhost/hr_budget/public/admin/categories`
2. ทดสอบ:
   - ดูรายการหมวดหมู่ทั้งหมด (Tree view)
   - สร้างหมวดหมู่ใหม่ (รวมถึง sub-category)
   - แก้ไขหมวดหมู่
   - ลบหมวดหมู่ (ควร soft delete)

#### Admin Organizations

1. เปิด `http://localhost/hr_budget/public/admin/organizations`
2. ทดสอบ:
   - สร้างกรม → กอง → ฝ่าย (ลำดับชั้น)
   - แก้ไขชื่อหน่วยงาน
   - ลบหน่วยงาน

#### Multi-Tab Tracking

1. เปิด `http://localhost/hr_budget/public/budgets/create`
2. ทดสอบ:
   - เห็น Tab ทุกหมวดหมู่ที่มีใน DB
   - คลิกสลับ Tab ได้
   - กรอกข้อมูลแล้วบันทึก
   - ตรวจสอบข้อมูลใน DB

### 5.2 User Manual Verification

ให้ผู้ใช้ทดสอบการใช้งานจริงและ feedback

---

## 6. Summary

โครงการนี้จะทำให้ระบบรองรับ:

| ✅ Before | ✅ After |
|-----------|----------|
| บันทึกได้เฉพาะงบบุคลากร | บันทึกได้ทุกหมวดหมู่ |
| ไม่มี Admin จัดการหมวดหมู่ | Admin CRUD หมวดหมู่ + Tree view |
| ไม่มีหน่วยงาน | Admin CRUD หน่วยงาน (กรม/กอง/ฝ่าย) |
| UI แบบ Accordion | UI แบบ Multi-Tab (เร็วกว่า) |

---

**กรุณา Review และแจ้ง Feedback ก่อนเริ่มดำเนินการครับ** 🙏
