ALTER TABLE organizations ADD COLUMN provincial_group VARCHAR(100) NULL COMMENT 'กลุ่มจังหวัด' AFTER region;
ALTER TABLE organizations ADD COLUMN provincial_zone VARCHAR(100) NULL COMMENT 'เขตจังหวัด' AFTER provincial_group;
ALTER TABLE organizations ADD COLUMN inspection_zone VARCHAR(100) NULL COMMENT 'เขตตรวจราชการ' AFTER provincial_zone;
ALTER TABLE organizations ADD COLUMN custom_zone VARCHAR(100) NULL COMMENT 'เขตกำหนดเอง' AFTER inspection_zone;
