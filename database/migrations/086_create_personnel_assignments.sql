-- ============================================================================
-- 086_create_personnel_assignments.sql
-- Phase 9 (งบบุคลากร) — ตารางที่ 11/13 · กลุ่มเลื่อนเงินเดือน
-- `personnel_assignments` = การไปช่วยราชการ — รายงานอย่างเดียว ไม่ย้ายงบ
--
-- กติกาเหล็ก (ผู้ใช้ยืนยัน 2026-08-10): "หน่วยต้นสังกัด แต่ต้องดูแยกได้"
--   GROUP BY ยอดงบ = position_versions.organization_id (เจ้าของตำแหน่ง) เสมอ
--   serving_organization_id ใช้รายงานอย่างเดียว ห้ามใช้ GROUP BY ยอดงบ
--   เหตุผล: ถ้ายอดงบเลื่อนไหลตามคน ยอดหน่วยขยับทุกคำสั่งช่วยราชการ กระทบยอด
--   กับ พ.ร.บ. ไม่ได้
--
-- ผลข้างเคียงตามสูตร projected_salary: มีแถวยังมีผล ณ วันอ้างอิง ⇒ บังคับ estimated
-- (ผลเลื่อนขึ้นกับหน่วยที่ไปช่วย ไม่ทันรอบตั้งงบ)
--
-- person_id เป็น VARCHAR อิสระเหมือน personnel_allowances (081)
--
-- ออกแบบเต็ม: PRPs/2026-08-09_personnel-budget-schema-design.md
-- Idempotent: CREATE TABLE IF NOT EXISTS.
-- ============================================================================

SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci';

CREATE TABLE IF NOT EXISTS personnel_assignments (
  id                       INT NOT NULL AUTO_INCREMENT,
  person_id                VARCHAR(50) NOT NULL COMMENT 'รหัสบุคคลจากระบบ HR — VARCHAR อิสระ ไม่ FK',
  position_id              INT NOT NULL COMMENT 'FK: positions.id — เจ้าของตำแหน่ง (งบอยู่ต้นสังกัด)',
  serving_organization_id  INT NOT NULL COMMENT 'FK: organizations.id — หน่วยที่ไปช่วย (รายงานอย่างเดียว ห้าม GROUP BY ยอดงบ)',
  effective_from           DATE NOT NULL COMMENT 'วันเริ่มไปช่วย',
  effective_to             DATE DEFAULT NULL COMMENT 'NULL = ยังไปช่วยอยู่',
  doc_no                   VARCHAR(100) DEFAULT NULL COMMENT 'เลขที่คำสั่งช่วยราชการ',
  doc_date                 DATE DEFAULT NULL COMMENT 'วันที่คำสั่ง',
  is_active                TINYINT(1) DEFAULT 1,
  deleted_at               TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete',
  created_at               TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at               TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by               INT DEFAULT NULL COMMENT 'ผู้สร้าง',
  updated_by               INT DEFAULT NULL COMMENT 'ผู้แก้ไขล่าสุด',
  PRIMARY KEY (id),
  UNIQUE KEY uk_personnel_assignments (person_id, position_id, effective_from),
  KEY idx_personnel_assignments_serving (serving_organization_id, effective_from),
  KEY idx_personnel_assignments_position (position_id),
  KEY idx_personnel_assignments_deleted (deleted_at),
  CONSTRAINT fk_personnel_assignments_position FOREIGN KEY (position_id)
    REFERENCES positions (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_personnel_assignments_serving_organization FOREIGN KEY (serving_organization_id)
    REFERENCES organizations (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='ไปช่วยราชการ — งบอยู่ต้นสังกัด รายงานแยกผ่าน serving_organization_id';

-- --- Verification (printed by the migration runner) ------------------------
SELECT 'personnel_assignments exists' AS check_name,
       COUNT(*) AS actual, 1 AS expected
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personnel_assignments';
