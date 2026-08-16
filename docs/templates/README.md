# P4 ข้อมูลงบบุคลากร 2569 — เทมเพลต Excel

ไฟล์ `P4_ข้อมูลงบบุคลากร_2569.xlsx` สร้างโดยสคริปต์ `python/build_p4_template.py` (เป็น generated artifact — ห้ามแก้ไฟล์ Excel โดยตรง ให้แก้สคริปต์แล้ว regenerate)

## สร้างใหม่ (regenerate)

```powershell
cd python
venv\Scripts\python.exe build_p4_template.py
```

- ต้องรันบนเครื่องที่ต่อ MySQL (`hr_budget`) ได้ — สคริปต์**อ่านอย่างเดียว** (SELECT ตาราง `allowance_types`)
- ต้องมี `mysql-connector-python` และ `openpyxl` ใน venv (`pip install mysql-connector-python openpyxl`)

## ต้อง regenerate เมื่อไหร่

- `allowance_types` เปลี่ยน (migration ใหม่หรือ UPDATE ที่แตะแคตตาล็อกเงินเพิ่ม) — dropdown ชนิดเงินเพิ่มอ่านสดจากตารางนี้
- รายการระดับตำแหน่ง / ประเภทบุคลากร / เกณฑ์อัตราว่างเปลี่ยน (แก้ constants ในสคริปต์ก่อน — ระดับ 4 ค่าปัจจุบันเป็นประเภททั่วไปตามที่ HR ยืนยัน 2026-08-16)

## โครงสร้าง

| แผ่น | ปลายทางตอน import |
|---|---|
| 1_อัตราเงินเพิ่ม | `allowance_rates` |
| 2_อัตราเงินเดือน | `salary_scales` (คอลัมน์ "ประเภทตำแหน่ง" เป็นข้อมูลประกอบ ไม่นำเข้า) |
| 3_นโยบายปี2569 | `personnel_budget_policy` (แถวเลขที่/วันที่หนังสือเป็นข้อมูลอ้างอิง ไม่นำเข้า) |
| Lists (ซ่อน) | แหล่ง dropdown ทั้งหมด (อ้างอิงแบบ range) |
| คำแนะนำ | คำอธิบายสำหรับผู้กรอก |
