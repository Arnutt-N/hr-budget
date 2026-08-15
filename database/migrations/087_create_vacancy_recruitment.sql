-- ============================================================================
-- 087_create_vacancy_recruitment.sql
-- Phase 9 (งบบุคลากร) — ตารางที่ 12/13 · กลุ่มนโยบาย
-- `vacancy_recruitment` = หลักฐานการสรรหาของอัตราว่าง รายปีงบ
-- — ของใหม่ ไม่มีในสกีมาระบบเดิมเลย (ตรวจแล้ว: ไม่พบตารางประกาศขึ้นบัญชี/
-- ประกาศสรรหา/อัตราพร้อมบรรจุ แม้แต่ตารางเดียว)
--
-- บทบาทตามสูตร: อัตราว่าง (vacant_funded) ถูกนับในงบเฉพาะเมื่อผ่าน
-- vacancy_rule(p, Y) — เกณฑ์เปลี่ยนตามมติ/หนังสือเวียนแต่ละปี ห้ามฝังโค้ด
-- ตารางนี้คือหลักฐานว่าอัตรานี้ผ่านเกณฑ์ไหนของปีไหน (เลขที่เอกสาร)
--
-- type สามค่า = ขั้นความพร้อมสรรหา (จากบริบทผู้ใช้):
--   transfer_request   หนังสือขอรับโอน
--   eligibility_list   ประกาศขึ้นบัญชี
--   ready_to_fill      อัตราพร้อมบรรจุ
--
-- ออกแบบเต็ม: PRPs/2026-08-09_personnel-budget-schema-design.md
-- Idempotent: CREATE TABLE IF NOT EXISTS.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS vacancy_recruitment (
  id                INT NOT NULL AUTO_INCREMENT,
  position_id       INT NOT NULL COMMENT 'FK: positions.id',
  fiscal_year_id    INT NOT NULL COMMENT 'FK: fiscal_years.id — หลักฐานผูกกับปีงบ',
  type              ENUM('transfer_request','eligibility_list','ready_to_fill')
                    NOT NULL COMMENT 'หนังสือขอรับโอน · ประกาศขึ้นบัญชี · อัตราพร้อมบรรจุ',
  doc_no            VARCHAR(100) DEFAULT NULL COMMENT 'เลขที่หนังสือ/ประกาศ',
  doc_date          DATE DEFAULT NULL COMMENT 'วันที่หนังสือ/ประกาศ',
  is_active         TINYINT(1) DEFAULT 1,
  deleted_at        TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
  created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by        INT DEFAULT NULL COMMENT 'ผู้สร้าง',
  updated_by        INT DEFAULT NULL COMMENT 'ผู้แก้ไขล่าสุด',
  PRIMARY KEY (id),
  KEY idx_vacancy_recruitment_lookup (position_id, fiscal_year_id, type),
  KEY idx_vacancy_recruitment_fiscal (fiscal_year_id),
  KEY idx_vacancy_recruitment_deleted (deleted_at),
  CONSTRAINT fk_vacancy_recruitment_position FOREIGN KEY (position_id)
    REFERENCES positions (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_vacancy_recruitment_fiscal_year FOREIGN KEY (fiscal_year_id)
    REFERENCES fiscal_years (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='หลักฐานการสรรหาของอัตราว่างรายปีงบ — เกณฑ์อัตราพร้อมบรรจุเป็นข้อมูล ไม่ฝังโค้ด';

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'vacancy_recruitment exists' AS check_name,
       COUNT(*) AS actual, 1 AS expected
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vacancy_recruitment';
