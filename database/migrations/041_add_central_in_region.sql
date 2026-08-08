-- =====================================================
-- HR Budget System - Add Central-in-Region Type
-- Version: 1.0
-- Date: 2025-12-31
-- Description: Add 'central_in_region' to organizations.region ENUM
-- =====================================================

-- Update ENUM to include 'central_in_region'
-- ส่วนกลาง, ส่วนภูมิภาค, ส่วนกลางที่ตั้งอยู่ในภูมิภาค
ALTER TABLE organizations
  MODIFY COLUMN region ENUM('central', 'regional', 'provincial', 'central_in_region') 
  DEFAULT 'central' 
  COMMENT 'ประเภท: ส่วนกลาง/ส่วนภูมิภาค/จังหวัด/ส่วนกลางที่ตั้งอยู่ในภูมิภาค';

-- Update view to include new region type
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
        WHEN 'regional' THEN 'ส่วนภูมิภาค'
        WHEN 'provincial' THEN 'จังหวัด'
        WHEN 'central_in_region' THEN 'ส่วนกลางที่ตั้งอยู่ในภูมิภาค'
        ELSE 'ไม่ระบุ'
    END as region_label
FROM organizations o
LEFT JOIN organizations p ON o.parent_id = p.id
ORDER BY o.level, o.sort_order;

-- Also update budget_line_items to have proper region_type
ALTER TABLE budget_line_items
  MODIFY COLUMN region_type ENUM('central', 'regional', 'central_in_region') 
  DEFAULT 'central' 
  COMMENT 'ส่วนกลาง/ส่วนภูมิภาค/ส่วนกลางในภูมิภาค';

SELECT 'Organizations region ENUM updated to include central_in_region' AS status;
