# แก้ไข Error: Data too long for column 'code'

## วิธีแก้

เพิ่มขนาดคอลัมน์ `code` จาก VARCHAR(100) เป็น VARCHAR(500)

---

## ขั้นตอน

### 1. ลบข้อมูลที่ insert ไปแล้ว (ถ้ามี)

```bash
mysql -u root hr_budget -e "TRUNCATE TABLE budget_category_items;"
```

### 2. แก้ไขขนาดคอลัมน์ `code`

```bash
mysql -u root hr_budget -e "ALTER TABLE budget_category_items MODIFY COLUMN code VARCHAR(500) NULL;"
```

### 3. รัน Seeder อีกครั้ง

```bash
php scripts\seed_budget_hierarchy.php
```

**ผลลัพธ์ที่คาดหวัง**:
```
Seeding completed.
```

---

## หรือวิธีที่ง่ายกว่า: ลบตารางสร้างใหม่

```bash
mysql -u root hr_budget -e "DROP TABLE IF EXISTS budget_category_items;"
mysql -u root hr_budget < database\migrations\022_add_hierarchy_to_category_items.sql
php scripts\seed_budget_hierarchy.php
```

Migration file ถูกแก้ไขแล้ว (code เป็น VARCHAR(500))

---

## ตรวจสอบหลังจากสำเร็จ

```bash
mysql -u root hr_budget -e "SELECT COUNT(*) as total FROM budget_category_items;"
```

**แจ้งผลให้ฉันทราบ**: "Seeding completed" หรือส่ง error (ถ้ามี)
