# คำสั่งสำหรับรัน Migration และ Seeder (Budget Hierarchy)

## ขั้นตอนที่ 1: ทดสอบ MySQL Connection
เปิด Command Prompt ใน `c:\laragon\www\hr_budget\` แล้วรัน:

```bash
mysql -u root -e "SELECT 'MySQL OK' AS status;"
```

**ผลลัพธ์ที่คาดหวัง:**
```
+-----------+
| status    |
+-----------+
| MySQL OK  |
+-----------+
```

**ถ้า error**: ให้เช็ค Laragon ว่า MySQL เปิดอยู่จริงหรือไม่ (ควรเป็นสีเขียว)

---

## ขั้นตอนที่ 2: รัน Migration (สร้างตาราง + เพิ่มคอลัมน์)

**Migration ถูกแก้ไขแล้ว** - ใช้ syntax ที่เข้ากันได้กับ MySQL ทุกเวอร์ชัน

```bash
mysql -u root hr_budget < database\migrations\022_add_hierarchy_to_category_items.sql
```

**ถ้าสำเร็จ**: ไม่มีข้อความ error ใดๆ แสดง

**ถ้า error**: คัดลอก error message ทั้งหมดส่งให้ฉัน

**ขั้นตอนที่ 2b (Optional): เพิ่ม Foreign Key Constraint**

ถ้าต้องการ CASCADE delete, รันคำสั่งนี้เพิ่ม:
```bash
mysql -u root hr_budget < database\migrations\022b_add_fk_constraint.sql
```

---

## ขั้นตอนที่ 3: ตรวจสอบโครงสร้างตาราง

```bash
mysql -u root hr_budget -e "DESCRIBE budget_category_items;"
```

**ผลลัพธ์ที่คาดหวัง:** ควรเห็นคอลัมน์:
- `id`
- `name`
- `code`
- `parent_id`
- `level`

---

## ขั้นตอนที่ 4: รัน Seeder (นำเข้าข้อมูลจาก CSV)

```bash
php scripts\seed_budget_hierarchy.php
```

**ผลลัพธ์ที่คาดหวัง:**
```
Seeding completed.
```

**ถ้า error**: คัดลอก error message ส่งให้ฉัน

---

## ขั้นตอนที่ 5: ตรวจสอบข้อมูล

```bash
mysql -u root hr_budget -e "SELECT COUNT(*) as total FROM budget_category_items;"
```

**ผลลัพธ์ที่คาดหวัง:** ควรได้ประมาณ 80-90 แถว

**ตรวจสอบ hierarchy:**
```bash
mysql -u root hr_budget -e "SELECT id, name, parent_id, level FROM budget_category_items LIMIT 10;"
```

---

## หลังจากรันเสร็จทั้งหมด

แจ้งฉันว่า:
- ✅ "ทุกขั้นตอนสำเร็จ" → ฉันจะสรุปงานและอัปเดต documentation
- ⚠️ "Error ที่ขั้นตอน X" → ส่ง error message มา ฉันจะช่วยแก้

---

## Rollback (ถ้าต้องการย้อนกลับ)

```bash
mysql -u root hr_budget -e "DROP TABLE IF EXISTS budget_category_items;"
```

---

## หมายเหตุ

- คำสั่งทั้งหมดรันใน Command Prompt ที่โฟลเดอร์ `c:\laragon\www\hr_budget\`
- ถ้าใช้ PowerShell ให้เปลี่ยน `\` เป็น `/` ในพาธไฟล์
