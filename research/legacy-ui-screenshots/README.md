# Legacy UI Screenshots (ระบบเดิม — design reference)

ภาพหน้าจอระบบเดิม (PHP views, dark theme) ใช้เป็นแบบอ้างอิงดีไซน์สำหรับการ refactor เป็น Vue SPA
อัปโหลดโดยผู้ใช้เมื่อ 2026-06-12 ระหว่างทำ Phase 2 (admin CRUD)

| ไฟล์ | หน้า | จุดสำคัญของดีไซน์ |
|---|---|---|
| 01-login.jpg | หน้าเข้าสู่ระบบ | การ์ดมืดกลางจอ, ไอคอน landmark วงกลมสีฟ้า, ชื่อ "ระบบบริหารงบประมาณบุคลากร", input มีไอคอน, ปุ่ม ThaiD, กล่อง demo credentials |
| 02-disbursement-list-filters.jpg | รายการเบิกจ่ายงบประมาณ | แถบ filter (ปี/แผนงาน/หน่วยงาน/วันที่/ค้นหา), summary cards 5 ใบ (ตัวเลขสี), ตารางหัวมืด, empty state ไอคอนโฟลเดอร์ |
| 03-disbursement-records-empty.jpg | รายการบันทึกการเบิกจ่าย | หัวข้อหน้า + คำอธิบาย muted, ปุ่ม primary มุมขวาบน, empty state พร้อมคำแนะนำ |
| 04-create-disbursement-modal.jpg | Modal สร้างรายการเบิกจ่าย | หัว modal มีไอคอน + ปุ่ม X, select ทีละชั้น (ปี→เดือน→กรม→กอง), กล่องคำแนะนำสีฟ้า ⓘ, ปุ่ม ยกเลิก/ดำเนินการต่อ → |
| 05-disbursement-activity-select.jpg | เลือกกิจกรรมที่ต้องบันทึก | รายการแผนงานแบบ accordion, badge "บันทึกแล้ว" สีเขียว, badge ปี/เดือนมุมขวา |
| 06-disbursement-entry-form.jpg | บันทึกเบิกจ่าย (form) | breadcrumb ลิงก์สีฟ้า, แถวข้อมูลสรุปด้านบน, แท็บ 5 หมวดงบ, ตารางกรอกตัวเลข inline, ปุ่ม ยกเลิก/บันทึกข้อมูล |
| 07-create-request-modal.jpg | Modal สร้างคำของบประมาณ | ฟิลด์ required มี * แดง, select กรม→กอง แบบ dependent, กล่องคำแนะนำ |
| 08-request-entry-form.jpg | บันทึกคำของบประมาณ | แท็บหมวดงบพร้อมยอดต่อแท็บ, สถานะ "ร่างคำขอ (Draft)" สีฟ้า, แถว Total สีเหลือง, ปุ่ม กลับหน้าหลัก/ล้างข้อมูล/บันทึกคำขอ |
| 09-create-request-modal-filled-list.jpg | Modal สร้างคำขอ (มีรายการในตารางพื้นหลัง) | เหมือน 07 |
| 10-request-list-summary-cards.jpg | คำขอประมาณ (list) | summary cards 4 ใบมีไอคอนมุมขวา, ตัวกรองปีมุมขวาบน + ปุ่มสร้างคำขอ, ตาราง พร้อม badge สถานะสีเหลือง "ยังไม่ได้บันทึก", ปุ่มไอคอน ดู/แก้ไข/ลบ |

โทนสี: พื้น `#0f172a`, การ์ด `#1e293b`, เส้นขอบ `#334155`, ตัวหนังสือ `#f1f5f9`/muted `#94a3b8`, primary sky `#0ea5e9`
ฟอนต์: Noto Sans Thai • ไอคอน: Lucide
