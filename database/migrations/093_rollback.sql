-- Rollback 093_make_files_folder_id_nullable.sql
-- คืน folder_id เป็น NOT NULL — guarded, safe to run twice.
-- คำเตือน: รันได้เฉพาะเมื่อไม่มีแถวไหนมี folder_id เป็น NULL
-- (เช่น ลบไฟล์แนบคำขอทิ้งก่อน) ไม่งั้น ALTER จะ fail ตามข้อมูลที่ขัด
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

SET @null_rows = (SELECT COUNT(*) FROM files WHERE folder_id IS NULL);
SET @ddl = IF(@null_rows = 0,
  'ALTER TABLE files MODIFY COLUMN folder_id INT NOT NULL',
  'SELECT ''rollback 093 skipped: files ยังมี folder_id IS NULL อยู่'' AS warning');

PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
