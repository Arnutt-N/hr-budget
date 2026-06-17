-- 070_multistep_approval.sql
-- Phase 4: multi-step approval chain (กอง → กรม → กระทรวง) for budget requests,
-- using the approver_* roles seeded in Phase 1 (migration 067).
--
-- approval_levels defines the ordered chain (which role acts at each step).
-- budget_requests.current_level tracks the pending step; budget_request_approvals
-- records which level each action happened at.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `approval_levels` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `level` INT NOT NULL COMMENT 'ลำดับขั้น 1..n',
  `code` VARCHAR(50) NOT NULL,
  `name_th` VARCHAR(255) NOT NULL,
  `role_code` VARCHAR(50) NOT NULL COMMENT 'บทบาทที่อนุมัติขั้นนี้ (อ้าง roles.code)',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_approval_level` (`level`),
  UNIQUE KEY `uq_approval_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='สายอนุมัติหลายขั้น';

INSERT IGNORE INTO `approval_levels` (`level`,`code`,`name_th`,`role_code`) VALUES
  (1,'division',  'อนุมัติระดับกอง',     'approver_division'),
  (2,'department','อนุมัติระดับกรม',     'approver_department'),
  (3,'ministry',  'อนุมัติระดับกระทรวง', 'approver_ministry');

-- pending approval step on the request (NULL = not yet submitted to the chain)
ALTER TABLE `budget_requests`
  ADD COLUMN `current_level` INT NULL DEFAULT NULL
    COMMENT 'ขั้นอนุมัติที่รออยู่ (NULL=ยังไม่เข้า chain)' AFTER `request_status`;

-- which chain level an approval action was taken at
ALTER TABLE `budget_request_approvals`
  ADD COLUMN `level` INT NULL DEFAULT NULL COMMENT 'ขั้นที่ดำเนินการ' AFTER `action`;
