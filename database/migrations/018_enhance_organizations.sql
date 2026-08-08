-- =====================================================
-- HR Budget System - Enhanced Organizations Schema
-- Version: 2.0 (Unified Organization Structure)
-- Date: 2025-12-22
-- =====================================================

-- 1. Add new columns to organizations
ALTER TABLE organizations
  ADD COLUMN org_type ENUM('ministry', 'department', 'division', 'section', 'province', 'office') 
      DEFAULT 'division' COMMENT 'ประเภทหน่วยงาน: กระทรวง/กรม/กอง/กลุ่มงาน/จังหวัด/ส่วนราชการ' 
      AFTER level,
  ADD COLUMN province_code VARCHAR(10) NULL 
      COMMENT 'รหัสจังหวัด (สำหรับหน่วยงานส่วนภูมิภาค)' 
      AFTER org_type,
  ADD COLUMN region ENUM('central', 'regional', 'provincial') DEFAULT 'central' 
      COMMENT 'ส่วนกลาง/ภูมิภาค/จังหวัด' 
      AFTER province_code,
  ADD COLUMN contact_phone VARCHAR(50) NULL 
      COMMENT 'เบอร์โทรศัพท์'
      AFTER region,
  ADD COLUMN contact_email VARCHAR(100) NULL 
      COMMENT 'อีเมล'
      AFTER contact_phone,
  ADD COLUMN address TEXT NULL 
      COMMENT 'ที่อยู่'
      AFTER contact_email;

-- 2. Update level comment
ALTER TABLE organizations 
  MODIFY COLUMN level INT NOT NULL DEFAULT 0 
  COMMENT 'ระดับ: 0=กระทรวง, 1=กรม, 2=กอง/สำนัก, 3=กลุ่มงาน, 4=จังหวัด/ส่วนราชการ';

-- 3. Add indexes
CREATE INDEX idx_org_type ON organizations(org_type);
CREATE INDEX idx_org_region ON organizations(region);
CREATE INDEX idx_org_province ON organizations(province_code);

-- 4. Create view for hierarchy display
CREATE OR REPLACE VIEW v_organizations_hierarchy AS
SELECT 
    o.*,
    p.name_th as parent_name,
    p.code as parent_code,
    CASE o.org_type
        WHEN 'ministry' THEN 'กระทรวง'
        WHEN 'department' THEN 'กรม'
        WHEN 'division' THEN 'กอง/สำนัก'
        WHEN 'section' THEN 'กลุ่มงาน'
        WHEN 'province' THEN 'จังหวัด'
        WHEN 'office' THEN 'ส่วนราชการ'
        ELSE 'อื่นๆ'
    END as org_type_label,
    CASE o.region
        WHEN 'central' THEN 'ส่วนกลาง'
        WHEN 'regional' THEN 'ภูมิภาค'
        WHEN 'provincial' THEN 'จังหวัด'
        ELSE 'ไม่ระบุ'
    END as region_label
FROM organizations o
LEFT JOIN organizations p ON o.parent_id = p.id
ORDER BY o.level, o.sort_order;

SELECT 'Organizations table enhanced successfully' AS status;
DESCRIBE organizations;
