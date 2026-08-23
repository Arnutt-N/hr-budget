-- Rollback 093_make_files_folder_id_nullable.sql
-- คืน folder_id เป็น NOT NULL — guarded, safe to run twice.
-- ถ้ายังมีแถวที่ folder_id IS NULL (ไฟล์แนบคำขอ) จะข้ามไป (DO 0)
-- ต้องลบแถวพวกนั้นก่อนจึงจะ rollback ได้จริง
SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

SET @null_rows = (SELECT COUNT(*) FROM files WHERE folder_id IS NULL);
SET @ddl = IF(@null_rows = 0,
  'ALTER TABLE files MODIFY COLUMN folder_id INT NOT NULL',
  'DO 0');

PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
