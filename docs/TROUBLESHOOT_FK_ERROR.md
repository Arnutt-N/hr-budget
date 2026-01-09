# ขั้นตอนการแก้ปัญหา Foreign Key Error

## สาเหตุ
ตาราง `budget_category_items` ถูกสร้างแล้ว แต่ไม่มีคอลัมน์ `parent_id`

## วิธีแก้

### 1. เช็คโครงสร้างตารางปัจจุบัน

```bash
mysql -u root hr_budget -e "DESCRIBE budget_category_items;"
```

**ถ้าเห็น**:
- ✅ มีคอลัมน์ `id`, `name`, `code`, `parent_id`, `level` → ข้ามไปขั้นตอนที่ 3
- ❌ ไม่มีคอลัมน์ `parent_id`, `level`, `code` → ทำตามขั้นตอนที่ 2

---

### 2. ลบตารางเดิมแล้วสร้างใหม่

```bash
mysql -u root hr_budget -e "DROP TABLE IF EXISTS budget_category_items;"
mysql -u root hr_budget < database\migrations\022_add_hierarchy_to_category_items.sql
```

**ตรวจสอบอีกครั้ง**:
```bash
mysql -u root hr_budget -e "DESCRIBE budget_category_items;"
```

ควรเห็น:
```
+-----------+--------------+------+-----+---------+----------------+
| Field     | Type         | Null | Key | Default | Extra          |
+-----------+--------------+------+-----+---------+----------------+
| id        | int          | NO   | PRI | NULL    | auto_increment |
| name      | varchar(255) | NO   |     | NULL    |                |
| code      | varchar(100) | YES  |     | NULL    |                |
| parent_id | int          | YES  | MUL | NULL    |                |
| level     | tinyint      | NO   |     | 0       |                |
+-----------+--------------+------+-----+---------+----------------+
```

---

### 3. เพิ่ม Foreign Key Constraint (Optional)

**สำคัญ**: รันได้ก็ต่อเมื่อมีคอลัมน์ `parent_id` แล้ว

```bash
mysql -u root hr_budget < database\migrations\022b_add_fk_constraint.sql
```

**หรือข้ามขั้นตอนนี้ไปเลย** ถ้าไม่ต้องการ CASCADE delete

---

### 4. ดำเนินการต่อด้วย Seeder

```bash
php scripts\seed_budget_hierarchy.php
```

---

## แจ้งผลกลับมา

กรุณารันขั้นตอนที่ 1 แล้วบอกว่า:
- "มี parent_id แล้ว" → ไปต่อที่ขั้นตอนที่ 4 (seeder)
- "ไม่มี parent_id" → ทำขั้นตอนที่ 2 แล้วบอกผล
