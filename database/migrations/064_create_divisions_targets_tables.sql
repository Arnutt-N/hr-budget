-- Migration: 064_create_divisions_targets_tables.sql
-- Phase 2 (Vue SPA admin CRUD): สร้างตารางที่ migration 009/015 เคยนิยามไว้
-- แต่ไม่เคยถูก apply เข้าฐานข้อมูลจริง — จำเป็นสำหรับ API
-- divisions / target-types / targets

-- หน่วยงานภายใน (ฝ่าย/กอง/สำนัก)
CREATE TABLE IF NOT EXISTS `divisions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(20) NOT NULL COMMENT 'รหัสหน่วยงาน',
  `name_th` VARCHAR(255) NOT NULL COMMENT 'ชื่อหน่วยงาน (ไทย)',
  `name_en` VARCHAR(255) DEFAULT NULL COMMENT 'ชื่อหน่วยงาน (อังกฤษ)',
  `short_name` VARCHAR(50) DEFAULT NULL COMMENT 'ชื่อย่อ',
  `parent_id` INT DEFAULT NULL COMMENT 'หน่วยงานแม่ (ถ้ามี)',
  `type` ENUM('central', 'regional', 'provincial') DEFAULT 'central' COMMENT 'ประเภท: ส่วนกลาง/ภูมิภาค/จังหวัด',
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_divisions_code` (`code`),
  KEY `idx_divisions_parent` (`parent_id`),
  CONSTRAINT `fk_divisions_parent` FOREIGN KEY (`parent_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='หน่วยงาน/กอง/สำนัก';

-- ประเภทเป้าหมาย
CREATE TABLE IF NOT EXISTS `target_types` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL COMMENT 'รหัสประเภทเป้าหมาย',
  `name_th` VARCHAR(255) NOT NULL COMMENT 'ชื่อประเภทเป้าหมาย',
  `description` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_target_types_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ประเภทเป้าหมาย';

-- เป้าหมายงบประมาณ (รายปี/รายไตรมาส อาจระบุหน่วยงาน/หมวดหมู่)
CREATE TABLE IF NOT EXISTS `budget_targets` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `target_type_id` INT NOT NULL,
  `fiscal_year` INT NOT NULL,
  `quarter` INT DEFAULT NULL COMMENT 'NULL=เป้าหมายรายปี, 1-4=ไตรมาส',
  `organization_id` INT DEFAULT NULL COMMENT 'NULL=ทุกหน่วยงาน',
  `category_id` INT DEFAULT NULL COMMENT 'NULL=ทุกหมวดหมู่',
  `target_percent` DECIMAL(5,2) DEFAULT NULL COMMENT 'เป้าหมาย %',
  `target_amount` DECIMAL(15,2) DEFAULT NULL COMMENT 'เป้าหมายจำนวนเงิน',
  `notes` TEXT DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_budget_targets` (`target_type_id`, `fiscal_year`, `quarter`, `organization_id`, `category_id`),
  KEY `idx_budget_targets_year` (`fiscal_year`),
  CONSTRAINT `fk_budget_targets_type` FOREIGN KEY (`target_type_id`) REFERENCES `target_types` (`id`),
  CONSTRAINT `fk_budget_targets_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_budget_targets_category` FOREIGN KEY (`category_id`) REFERENCES `budget_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_budget_targets_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='เป้าหมายงบประมาณ';
