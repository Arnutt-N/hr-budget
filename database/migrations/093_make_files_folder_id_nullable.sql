-- ============================================================================
-- 093_make_files_folder_id_nullable.sql
-- Attachment upload fix — files.folder_id เป็น NOT NULL แต่โค้ดแนบไฟล์กับคำขอ
-- (FileService::upload, src/Services/FileService.php) ใส่ folder_id = NULL
--
-- บริบท: migration 063 เพิ่มคอลัมน์ files.request_id เพื่อเก็บไฟล์แนบของคำขอ
-- โดยแยกจากไฟล์ในคลังเอกสาร (vault) ซึ่งใช้ folder_id — ไฟล์แนบไม่มีโฟลเดอร์
-- แต่ schema ยังบังคับ folder_id NOT NULL ทำให้ INSERT พังทุกครั้ง
-- (SQLSTATE 23000: Column 'folder_id' cannot be null)
--
-- การแก้: ผ่อน folder_id เป็น NULL ได้ — ไฟล์ vault ยังกรอก folder_id เสมอ
-- จึงไม่กระทบฟีเจอร์คลังเอกสาร ส่วนไฟล์แนบคำขอจะ NULL ตามการใช้งานจริง
--
-- หมายเหตุ: dev DB หลายเครื่องอาจยังไม่ได้ apply 063 (เพิ่ม request_id)
-- ให้รัน 063 ก่อนไฟล์นี้
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

ALTER TABLE files MODIFY COLUMN folder_id INT NULL;
