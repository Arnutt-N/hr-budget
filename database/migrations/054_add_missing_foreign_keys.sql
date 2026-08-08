-- Migration: Add Missing Foreign Keys
-- Generated: 2026-01-04T17:37:51.935097
-- Database: hr_budget

SET FOREIGN_KEY_CHECKS = 0;

-- Safe Foreign Keys (no orphan records)

-- activity_logs.user_id -> users.id
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_activity_logs_user_id`
  FOREIGN KEY (`user_id`)
  REFERENCES `users` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;

-- budget_allocations.plan_id -> plans.id
ALTER TABLE `budget_allocations`
  ADD CONSTRAINT `fk_budget_allocations_plan_id`
  FOREIGN KEY (`plan_id`)
  REFERENCES `plans` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;

-- budget_allocations.category_id -> budget_categories.id
ALTER TABLE `budget_allocations`
  ADD CONSTRAINT `fk_budget_allocations_category_id`
  FOREIGN KEY (`category_id`)
  REFERENCES `budget_categories` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- budget_allocations.item_id -> budget_category_items.id
ALTER TABLE `budget_allocations`
  ADD CONSTRAINT `fk_budget_allocations_item_id`
  FOREIGN KEY (`item_id`)
  REFERENCES `budget_category_items` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- budget_allocations.activity_id -> activities.id
ALTER TABLE `budget_allocations`
  ADD CONSTRAINT `fk_budget_allocations_activity_id`
  FOREIGN KEY (`activity_id`)
  REFERENCES `activities` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- budget_allocations.organization_id -> organizations.id
ALTER TABLE `budget_allocations`
  ADD CONSTRAINT `fk_budget_allocations_organization_id`
  FOREIGN KEY (`organization_id`)
  REFERENCES `organizations` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- budget_category_items.parent_id -> budget_category_items.id
ALTER TABLE `budget_category_items`
  ADD CONSTRAINT `fk_budget_category_items_parent_id`
  FOREIGN KEY (`parent_id`)
  REFERENCES `budget_category_items` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- budget_line_items.budget_type_id -> budget_types.id
ALTER TABLE `budget_line_items`
  ADD CONSTRAINT `fk_budget_line_items_budget_type_id`
  FOREIGN KEY (`budget_type_id`)
  REFERENCES `budget_types` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- budget_line_items.plan_id -> plans.id
ALTER TABLE `budget_line_items`
  ADD CONSTRAINT `fk_budget_line_items_plan_id`
  FOREIGN KEY (`plan_id`)
  REFERENCES `plans` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- budget_line_items.project_id -> projects.id
ALTER TABLE `budget_line_items`
  ADD CONSTRAINT `fk_budget_line_items_project_id`
  FOREIGN KEY (`project_id`)
  REFERENCES `projects` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- budget_line_items.activity_id -> activities.id
ALTER TABLE `budget_line_items`
  ADD CONSTRAINT `fk_budget_line_items_activity_id`
  FOREIGN KEY (`activity_id`)
  REFERENCES `activities` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- budget_line_items.expense_type_id -> expense_types.id
ALTER TABLE `budget_line_items`
  ADD CONSTRAINT `fk_budget_line_items_expense_type_id`
  FOREIGN KEY (`expense_type_id`)
  REFERENCES `expense_types` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- budget_line_items.expense_group_id -> expense_groups.id
ALTER TABLE `budget_line_items`
  ADD CONSTRAINT `fk_budget_line_items_expense_group_id`
  FOREIGN KEY (`expense_group_id`)
  REFERENCES `expense_groups` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- budget_line_items.expense_item_id -> expense_items.id
ALTER TABLE `budget_line_items`
  ADD CONSTRAINT `fk_budget_line_items_expense_item_id`
  FOREIGN KEY (`expense_item_id`)
  REFERENCES `expense_items` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- budget_line_items.province_id -> provinces.id
ALTER TABLE `budget_line_items`
  ADD CONSTRAINT `fk_budget_line_items_province_id`
  FOREIGN KEY (`province_id`)
  REFERENCES `provinces` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- budget_trackings.organization_id -> organizations.id
ALTER TABLE `budget_trackings`
  ADD CONSTRAINT `fk_budget_trackings_organization_id`
  FOREIGN KEY (`organization_id`)
  REFERENCES `organizations` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- budget_trackings.budget_category_item_id -> budget_category_items.id
ALTER TABLE `budget_trackings`
  ADD CONSTRAINT `fk_budget_trackings_budget_category_item_id`
  FOREIGN KEY (`budget_category_item_id`)
  REFERENCES `budget_category_items` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;

-- organizations.parent_id -> organizations.id
ALTER TABLE `organizations`
  ADD CONSTRAINT `fk_organizations_parent_id`
  FOREIGN KEY (`parent_id`)
  REFERENCES `organizations` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- source_of_truth_mappings.organization_id -> organizations.id
ALTER TABLE `source_of_truth_mappings`
  ADD CONSTRAINT `fk_source_of_truth_mappings_organization_id`
  FOREIGN KEY (`organization_id`)
  REFERENCES `organizations` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;

-- source_of_truth_mappings.plan_id -> plans.id
ALTER TABLE `source_of_truth_mappings`
  ADD CONSTRAINT `fk_source_of_truth_mappings_plan_id`
  FOREIGN KEY (`plan_id`)
  REFERENCES `plans` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;

-- source_of_truth_mappings.project_id -> projects.id
ALTER TABLE `source_of_truth_mappings`
  ADD CONSTRAINT `fk_source_of_truth_mappings_project_id`
  FOREIGN KEY (`project_id`)
  REFERENCES `projects` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;

-- source_of_truth_mappings.activity_id -> activities.id
ALTER TABLE `source_of_truth_mappings`
  ADD CONSTRAINT `fk_source_of_truth_mappings_activity_id`
  FOREIGN KEY (`activity_id`)
  REFERENCES `activities` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;