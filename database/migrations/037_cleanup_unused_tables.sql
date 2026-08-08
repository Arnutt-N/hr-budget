-- Migration: 037_cleanup_unused_tables.sql
-- Phase 4: ลบตารางเก่าที่ไม่ใช้งานแล้ว (Cleanup)

-- Warning: การรันไฟล์นี้จะลบข้อมูลในตารางเหล่านี้ถาวร
-- ควรตรวจสอบว่าไม่มี Code ส่วนไหนเรียกใช้ตารางเหล่านี้แล้ว

DROP TABLE IF EXISTS budget_item_categories;
DROP TABLE IF EXISTS personnel_types;

-- Optional: ลบ columns เก่าใน budgets หรือ budget_trackings (ทำทีหลังเมื่อ Code Stable)
-- ALTER TABLE budgets DROP FOREIGN KEY ...;
-- ALTER TABLE budgets DROP COLUMN ...;
