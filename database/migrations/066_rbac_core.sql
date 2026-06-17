-- 066_rbac_core.sql
-- Phase 1: RBAC + multi-org access control — core tables.
-- Adds roles / permissions / role_permissions and the user_access_grants
-- bridge (user <-> role <-> scope). Additive only; no existing table altered.

SET NAMES utf8mb4;

-- บทบาท (ตั้งชื่อเองได้, เปิด/ปิดได้)
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `name_th` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255) DEFAULT NULL,
  `description` TEXT,
  `is_system` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'บทบาทระบบ ลบ/แก้ code ไม่ได้',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'เปิด/ปิดการใช้งานบทบาท',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='บทบาทผู้ใช้ (RBAC)';

-- สิทธิ์รายข้อ (resource.action)
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(100) NOT NULL COMMENT 'เช่น budget.edit, request.approve',
  `name_th` VARCHAR(255) NOT NULL,
  `resource` VARCHAR(50) NOT NULL COMMENT 'กลุ่ม: budget/request/disbursement/org/user/role/report',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_permissions_code` (`code`),
  KEY `idx_permissions_resource` (`resource`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='สิทธิ์รายข้อ';

-- บทบาทไหนมีสิทธิ์ไหน
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` INT NOT NULL,
  `permission_id` INT NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  KEY `idx_rp_permission` (`permission_id`),
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='แมปบทบาท-สิทธิ์';

-- สะพานหลัก: user มีบทบาทอะไร ในขอบเขตไหน (membership + RBAC + scope)
-- scope_ref_id เป็น polymorphic (organization_id | classification id | region id) จึงไม่ผูก FK
CREATE TABLE IF NOT EXISTS `user_access_grants` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `role_id` INT NOT NULL,
  `scope_type` ENUM('organization','all','category','region') NOT NULL DEFAULT 'organization',
  `scope_ref_id` INT DEFAULT NULL COMMENT 'NULL เมื่อ scope=all',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_grant` (`user_id`, `role_id`, `scope_type`, `scope_ref_id`),
  KEY `idx_grant_user` (`user_id`),
  KEY `idx_grant_scope` (`scope_type`, `scope_ref_id`),
  CONSTRAINT `fk_grant_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_grant_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='การมอบบทบาท+ขอบเขตให้ผู้ใช้';
