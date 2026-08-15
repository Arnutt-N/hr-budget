-- ============================================================================
-- 085_create_salary_raise_progress.sql
-- Phase 9 (งบบุคลากร) — ตารางที่ 10/13 · กลุ่มเลื่อนเงินเดือน
-- `salary_raise_progress` = หน่วยงานไหนเลื่อนเงินเดือนรอบนี้เสร็จแล้ว
-- — ตัวตัดสิน actual/estimated ของ base_salary
--
-- ห้ามอนุมาน status จาก organizations.region ("แล้วแต่หน่วย ไม่ตายตัว" —
-- ผู้ใช้ยืนยัน 2026-08-10; region=central_in_region คาบเกี่ยวสองฝั่ง)
-- region เป็นได้แค่ตัวช่วยกรองตอนกรอก ไม่ใช่แหล่งความจริง
--
-- ผูกกับ *รอบ* (round_id) ไม่ใช่ปีงบ — รอบเดียวกันต่างปีงบเป็นคนละแถว
--
-- รายงานที่ได้ฟรี: "จังหวัดที่ยังไม่เลื่อนเงินเดือน (ตามทวง)"
--   SELECT ... WHERE status='pending'
--
-- ออกแบบเต็ม: PRPs/2026-08-09_personnel-budget-schema-design.md
-- Idempotent: CREATE TABLE IF NOT EXISTS.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS salary_raise_progress (
  id                INT NOT NULL AUTO_INCREMENT,
  round_id          INT NOT NULL COMMENT 'FK: salary_raise_rounds.id — ผูกกับรอบ ไม่ใช่ปีงบ',
  organization_id   INT NOT NULL COMMENT 'FK: organizations.id',
  status            ENUM('completed','pending') NOT NULL DEFAULT 'pending'
                    COMMENT 'เสร็จ=ใช้เงินเดือนจริง (actual) · รอ=ประมาณการ (estimated)',
  completed_at      TIMESTAMP NULL DEFAULT NULL COMMENT 'เวลาที่เลื่อนเสร็จ',
  doc_no            VARCHAR(100) DEFAULT NULL COMMENT 'เลขที่หนังสือ/คำสั่งยืนยันการเลื่อน',
  is_active         TINYINT(1) DEFAULT 1,
  deleted_at        TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
  created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by        INT DEFAULT NULL COMMENT 'ผู้สร้าง',
  updated_by        INT DEFAULT NULL COMMENT 'ผู้แก้ไขล่าสุด',
  PRIMARY KEY (id),
  UNIQUE KEY uk_salary_raise_progress (round_id, organization_id),
  KEY idx_salary_raise_progress_status (status),
  KEY idx_salary_raise_progress_deleted (deleted_at),
  CONSTRAINT fk_salary_raise_progress_round FOREIGN KEY (round_id)
    REFERENCES salary_raise_rounds (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_salary_raise_progress_organization FOREIGN KEY (organization_id)
    REFERENCES organizations (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='สถานะการเลื่อนเงินเดือนรายหน่วย×รอบ — ตัวตัดสิน actual/estimated (ห้ามอนุมานจาก region)';

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'salary_raise_progress exists' AS check_name,
       COUNT(*) AS actual, 1 AS expected
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'salary_raise_progress';
