CREATE TABLE IF NOT EXISTS `source_of_truth_mappings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `fiscal_year` INT NOT NULL,
    `organization_id` INT NOT NULL,
    `plan_id` INT NOT NULL,
    `project_id` INT NOT NULL,
    `activity_id` INT NOT NULL,
    `is_official` TINYINT(1) DEFAULT 1,
    `source` VARCHAR(50) DEFAULT 'python_etl',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_org_year` (`organization_id`, `fiscal_year`),
    INDEX `idx_activity` (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
