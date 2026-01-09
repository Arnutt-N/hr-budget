# แผนปรับปรุงฐานข้อมูลและ UI - โครงสร้างหน่วยงาน

> **สถานะ:** ✅ Approved - พร้อมดำเนินการ  
> **วันที่อัปเดต:** 2025-12-22 07:31  
> **อ้างอิง:** budget_structure2schema.xlsx

---

## 1. บทนำและเป้าหมาย

### 1.1 สถานะปัจจุบัน (ตรวจสอบแล้ว)

| ตาราง | สถานะ | ข้อมูล | การจัดการ |
|-------|-------|--------|---------|
| `organizations` | ✅ ว่าง | 0 rows | **เพิ่ม columns** |
| `divisions` | ❌ ไม่มี | Table not found | **N/A** |
| `dim_organization` | 5 rows | Test data | **DROP** |
| `dim_budget_structure` | 4 rows | Test data | **DROP** |
| `fact_budget_execution` | 4 rows | Test data | **DROP** |
| `log_transfer_note` | 2 rows | Test data | **DROP** |

### 1.2 Code ที่อ้างอิงถึง divisions (ต้องลบ/แก้ไข)

| ไฟล์ | ประเภท | การดำเนินการ |
|------|--------|-------------|
| `Division.php` | Model | **ลบ** (ตาราง divisions ไม่มี) |
| `DivisionController.php` | Controller | **ลบ** (ทั้งไฟล์) |
| `admin/divisions/index.php` | View | **ลบ** |
| `admin/divisions/form.php` | View | **ลบ** |
| `BudgetPlanController.php` | Controller | **แก้ไข** (ใช้ Organization แทน Division) |
| `admin/plans/form.php` | View | **แก้ไข** (ดึงจาก organizations) |
| `web.php` (routes) | Routes | **ลบ** routes /admin/divisions/* |

### 1.3 เป้าหมาย

1. **DROP Dimensional Tables** - ลบตาราง dimensional ที่มีเฉพาะข้อมูลทดสอบ
2. **ลบ Code อ้างอิง divisions** - ป้องกัน 500 Error
3. **ปรับปรุง organizations** - เพิ่ม columns รองรับโครงสร้างหน่วยงาน 6 ระดับ
4. **อัปเดต BudgetPlan** - ใช้ organizations แทน divisions

---

## 2. Proposed Changes

### Phase 1: DROP Dimensional Tables

---

#### [NEW] `database/migrations/017_drop_dimensional_tables.sql`

```sql
-- =====================================================
-- HR Budget System - Drop Dimensional Tables
-- Version: 1.0
-- Date: 2025-12-22
-- Reason: ข้อมูลเป็น mock-up ทั้งหมด ไม่มีข้อมูลจริง
-- =====================================================

-- Drop Views first
DROP VIEW IF EXISTS v_fact_summary_by_year;
DROP VIEW IF EXISTS v_structure_with_execution;

-- Drop tables in correct order (child tables first)
DROP TABLE IF EXISTS log_transfer_note;
DROP TABLE IF EXISTS fact_budget_execution;
DROP TABLE IF EXISTS dim_budget_structure;
DROP TABLE IF EXISTS dim_organization;

SELECT 'Dimensional tables dropped successfully' AS status;
```

---

### Phase 2: Cleanup Division References

---

#### [DELETE] Files to Remove

1. `src/Models/Division.php`
2. `src/Controllers/DivisionController.php`
3. `resources/views/admin/divisions/index.php`
4. `resources/views/admin/divisions/form.php`

---

#### [MODIFY] `routes/web.php`

**ลบ routes ที่อ้างอิง DivisionController (Lines 49-54):**

```php
// DELETE THESE LINES:
Router::get('/admin/divisions', [\\App\\Controllers\\DivisionController::class, 'index']);
Router::get('/admin/divisions/create', [\\App\\Controllers\\DivisionController::class, 'create']);
Router::post('/admin/divisions', [\\App\\Controllers\\DivisionController::class, 'store']);
Router::get('/admin/divisions/{id}/edit', [\\App\\Controllers\\DivisionController::class, 'edit']);
Router::post('/admin/divisions/{id}', [\\App\\Controllers\\DivisionController::class, 'update']);
Router::post('/admin/divisions/{id}/delete', [\\App\\Controllers\\DivisionController::class, 'destroy']);
```

---

#### [MODIFY] `src/Controllers/BudgetPlanController.php`

**Line 14:** เปลี่ยน
```php
use App\\Models\\Division;
```
เป็น:
```php
use App\\Models\\Organization;
```

**Line 61:** เปลี่ยน
```php
$divisions = Division::all();
```
เป็น:
```php
$organizations = Organization::getForSelect();
```

**Line 70:** เปลี่ยน
```php
'divisions' => $divisions,
```
เป็น:
```php
'organizations' => $organizations,
```

**Line 155:** เปลี่ยน
```php
$divisions = Division::all();
```
เป็น:
```php
$organizations = Organization::getForSelect();
```

**Line 164:** เปลี่ยน
```php
'divisions' => $divisions,
```
เป็น:
```php
'organizations' => $organizations,
```

---

#### [MODIFY] `resources/views/admin/plans/form.php`

**เปลี่ยนจาก:**
```php
<select name="division_id" ...>
    <option value="">-- ไม่ระบุ --</option>
    <?php
        $currentDivision = $formData['division_id'] ?? $plan['division_id'] ?? '';
        foreach ($divisions as $div):
            ?>
            <option value="<?= $div['id'] ?>" <?= $currentDivision == $div['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($div['name_th']) ?>
            </option>
        <?php endforeach; ?>
</select>
```

**เป็น:**
```php
<select name="division_id" ...>
    <option value="">-- ไม่ระบุ --</option>
    <?php
        $currentDivision = $formData['division_id'] ?? $plan['division_id'] ?? '';
        foreach ($organizations as $org):
            ?>
            <option value="<?= $org['id'] ?>" <?= $currentDivision == $org['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($org['name']) ?>
            </option>
        <?php endforeach; ?>
</select>
```

---

### Phase 3: Enhance Organizations Table

---

#### [NEW] `database/migrations/018_enhance_organizations.sql`

```sql
-- =====================================================
-- HR Budget System - Enhanced Organizations Schema
-- Version: 2.0 (Unified Organization Structure)
-- Date: 2025-12-22
-- =====================================================

-- 1. Add new columns to organizations
ALTER TABLE organizations
  ADD COLUMN org_type ENUM('ministry', 'department', 'division', 'section', 'province', 'office') 
      DEFAULT 'division' COMMENT 'ประเภทหน่วยงาน: กระทรวง/กรม/กอง/กลุ่มงาน/จังหวัด/ส่วนราชการ' 
      AFTER level,
  ADD COLUMN province_code VARCHAR(10) NULL 
      COMMENT 'รหัสจังหวัด (สำหรับหน่วยงานส่วนภูมิภาค)' 
      AFTER org_type,
  ADD COLUMN region ENUM('central', 'regional', 'provincial') DEFAULT 'central' 
      COMMENT 'ส่วนกลาง/ภูมิภาค/จังหวัด' 
      AFTER province_code,
  ADD COLUMN contact_phone VARCHAR(50) NULL 
      COMMENT 'เบอร์โทรศัพท์'
      AFTER region,
  ADD COLUMN contact_email VARCHAR(100) NULL 
      COMMENT 'อีเมล'
      AFTER contact_phone,
  ADD COLUMN address TEXT NULL 
      COMMENT 'ที่อยู่'
      AFTER contact_email;

-- 2. Update level comment
ALTER TABLE organizations 
  MODIFY COLUMN level INT NOT NULL DEFAULT 0 
  COMMENT 'ระดับ: 0=กระทรวง, 1=กรม, 2=กอง/สำนัก, 3=กลุ่มงาน, 4=จังหวัด/ส่วนราชการ';

-- 3. Add indexes
CREATE INDEX idx_org_type ON organizations(org_type);
CREATE INDEX idx_org_region ON organizations(region);
CREATE INDEX idx_org_province ON organizations(province_code);

-- 4. Create view for hierarchy display
CREATE OR REPLACE VIEW v_organizations_hierarchy AS
SELECT 
    o.*,
    p.name_th as parent_name,
    p.code as parent_code,
    CASE o.org_type
        WHEN 'ministry' THEN 'กระทรวง'
        WHEN 'department' THEN 'กรม'
        WHEN 'division' THEN 'กอง/สำนัก'
        WHEN 'section' THEN 'กลุ่มงาน'
        WHEN 'province' THEN 'จังหวัด'
        WHEN 'office' THEN 'ส่วนราชการ'
        ELSE 'อื่นๆ'
    END as org_type_label,
    CASE o.region
        WHEN 'central' THEN 'ส่วนกลาง'
        WHEN 'regional' THEN 'ภูมิภาค'
        WHEN 'provincial' THEN 'จังหวัด'
        ELSE 'ไม่ระบุ'
    END as region_label
FROM organizations o
LEFT JOIN organizations p ON o.parent_id = p.id
ORDER BY o.level, o.sort_order;

SELECT 'Organizations table enhanced successfully' AS status;
DESCRIBE organizations;
```

---

### Phase 4: Update Organization Model

---

#### [MODIFY] `src/Models/Organization.php`

เพิ่ม Constants และ Methods ตามที่วางแผนไว้ (ดูใน plan เดิม)

---

### Phase 5: Update Admin UI

---

#### [MODIFY] Admin Organizations Views

- `admin/organizations/index.php` - เพิ่ม filters และแสดง org_type
- `admin/organizations/create.php`, `edit.php` - เพิ่ม fields สำหรับ org_type, region, etc.

---

## 3. Implementation Order

| ลำดับ | Phase | งาน | เวลาประมาณ |
|------|-------|-----|-----------|
| 1 | Phase 1 | สร้างและรัน DROP migration | 5 นาที |
| 2 | Phase 2 | ลบไฟล์ Division (Model, Controller, Views) | 5 นาที |
| 3 | Phase 2 | แก้ไข routes/web.php - ลบ division routes | 3 นาที |
| 4 | Phase 2 | แก้ไข BudgetPlanController | 10 นาที |
| 5 | Phase 2 | แก้ไข admin/plans/form.php | 5 นาที |
| 6 | Phase 3 | สร้างและรัน ENHANCE migration | 10 นาที |
| 7 | Phase 4 | อัปเดต Organization Model | 15 นาที |
| 8 | Phase 5 | อัปเดต Admin Organizations UI | 30 นาที |
| 9 | Test | ทดสอบทุก route และหน้าจอ | 20 นาที |

**รวม:** ~103 นาที (~1.5 ชั่วโมง)

---

## 4. Verification Plan

### 4.1 Database Verification

```sql
-- 1. ตรวจสอบว่า dimensional tables ถูกลบ
SHOW TABLES LIKE 'dim_%';
SHOW TABLES LIKE 'fact_%';
SHOW TABLES LIKE 'log_transfer%';
-- Expected: 0 results

-- 2. ตรวจสอบ organizations schema ใหม่
DESCRIBE organizations;
-- Expected: เห็น columns ใหม่ org_type, region, province_code, etc.

-- 3. ตรวจสอบ view
SELECT * FROM v_organizations_hierarchy LIMIT 5;
```

### 4.2 Code Verification

```bash
# ตรวจสอบว่าไฟล์ Division ถูกลบ
ls src/Models/Division.php
ls src/Controllers/DivisionController.php
ls resources/views/admin/divisions/
# Expected: file not found

# ตรวจสอบว่า routes ถูกลบ
grep -n "DivisionController" routes/web.php
# Expected: no results
```

### 4.3 Manual Testing Checklist

**Routes ที่ต้องทดสอบ (ไม่ควรมี 500 Error):**
- [ ] `/admin/plans` - แสดงรายการแผนงาน
- [ ] `/admin/plans/create` - ฟอร์มสร้างแผนงานใหม่ (dropdown organizations)
- [ ] `/admin/plans/{id}/edit` - ฟอร์มแก้ไขแผนงาน
- [ ] `/admin/organizations` - แสดงรายการหน่วยงาน
- [ ] `/admin/organizations/create` - ฟอร์มสร้างหน่วยงาน (fields ใหม่)
- [ ] `/admin/organizations/{id}/edit` - ฟอร์มแก้ไขหน่วยงาน

**Routes ที่ควร 404 (ไม่มีแล้ว):**
- [ ] `/admin/divisions` - ควร 404
- [ ] `/admin/divisions/create` - ควร 404

---

## 5. Rollback Plan

```sql
-- Rollback 018 (ถ้าจำเป็น)
DROP VIEW IF EXISTS v_organizations_hierarchy;
ALTER TABLE organizations
  DROP COLUMN IF EXISTS org_type,
  DROP COLUMN IF EXISTS province_code,
  DROP COLUMN IF EXISTS region,
  DROP COLUMN IF EXISTS contact_phone,
  DROP COLUMN IF EXISTS contact_email,
  DROP COLUMN IF EXISTS address;
```

> ⚠️ **หมายเหตุ:** 
> - ไม่สามารถ rollback การ DROP dimensional tables ได้ (ต้องรัน migration 010 ใหม่)
> - ไม่สามารถ rollback การลบไฟล์ Division (ต้อง restore จาก Git)

---

## 6. Next Steps (หลังเสร็จ Phase นี้)

- 🔲 นำเข้าข้อมูลหน่วยงานจริงจาก Excel/API
- 🔲 ปรับปรุง Budget Structure (แผนงาน/ผลผลิต/กิจกรรม)
- 🔲 ปรับปรุงหน้าจอ Budget ให้ใช้ organizations hierarchy
- 🔲 สร้าง Report แยกตามหน่วยงาน/จังหวัด
