# การเพิ่มคอลัมน์สำหรับ Admin Management

## คอลัมน์ที่จะเพิ่ม (8 คอลัมน์)

### 1. Timestamps
- `created_at` - วันเวลาที่สร้าง (TIMESTAMP, auto-set)
- `updated_at` - วันเวลาที่แก้ไขล่าสุด (TIMESTAMP, auto-update)

### 2. Ordering & Status
- `sort_order` - ลำดับการแสดงผล (INT, default 0)
- `is_active` - สถานะการใช้งาน (TINYINT, default 1)

### 3. Additional Info
- `description` - คำอธิบายเพิ่มเติม (TEXT NULL)

### 4. Soft Delete
- `deleted_at` - วันเวลาที่ลบ (TIMESTAMP NULL)

### 5. Audit Trail
- `created_by` - ผู้สร้าง (INT NULL, FK to users)
- `updated_by` - ผู้แก้ไขล่าสุด (INT NULL, FK to users)

---

## ขั้นตอนการรัน Migration

### 1. รัน Migration

```bash
mysql -u root hr_budget < database\migrations\024_add_admin_columns_to_category_items.sql
```

**ถ้าสำเร็จ**: ไม่มีข้อความ error

**ถ้า error**: คัดลอก error message ส่งให้ฉัน

---

### 2. ตรวจสอบโครงสร้างตารางใหม่

```bash
mysql -u root hr_budget -e "DESCRIBE budget_category_items;"
```

**ควรเห็นคอลัมน์เพิ่ม**:
- created_at
- updated_at
- sort_order
- is_active
- description
- deleted_at
- created_by
- updated_by

---

### 3. อัปเดตข้อมูลเดิมให้มี sort_order

ข้อมูลที่มีอยู่จะมี `sort_order = 0` ทั้งหมด หากต้องการเรียงตาม ID:

```bash
mysql -u root hr_budget -e "UPDATE budget_category_items SET sort_order = id WHERE sort_order = 0;"
```

---

### 4. ตรวจสอบข้อมูล

```bash
mysql -u root hr_budget -e "SELECT id, name, sort_order, is_active, created_at FROM budget_category_items LIMIT 5;"
```

---

## การใช้งานคอลัมน์ใหม่

### Soft Delete Pattern
```sql
-- ลบแบบ soft delete
UPDATE budget_category_items SET deleted_at = NOW() WHERE id = ?;

-- Query เฉพาะที่ไม่ถูกลบ
SELECT * FROM budget_category_items WHERE deleted_at IS NULL;

-- Restore
UPDATE budget_category_items SET deleted_at = NULL WHERE id = ?;
```

### Active Status
```sql
-- Query เฉพาะที่ active
SELECT * FROM budget_category_items WHERE is_active = 1 AND deleted_at IS NULL;
```

### Ordering
```sql
-- เรียงตาม sort_order
SELECT * FROM budget_category_items ORDER BY sort_order ASC, id ASC;
```

---

## Foreign Keys (Optional)

ถ้ามีตาราง `users` และต้องการ FK constraints:

```bash
mysql -u root hr_budget < database\migrations\024b_add_fk_to_users.sql
```

---

## แจ้งผลกลับมา

กรุณารันขั้นตอนที่ 1-2 แล้วแจ้งว่า:
- ✅ "เพิ่มคอลัมน์สำเร็จ" (พร้อม DESCRIBE output)
- หรือส่ง error message (ถ้ามี)
