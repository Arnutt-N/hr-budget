# Foreign Key Analysis Report

**Generated:** 2026-01-04T17:41:24.683388
**Database:** hr_budget

## Summary
- **Existing FKs:** 71
- **Missing FKs (detected):** 1
  - Safe to add: 1
  - Need data fix: 0

## Existing Foreign Keys

| Table | Column | References |
|-------|--------|------------|
| `activities` | `parent_id` | `activities.id` |
| `activities` | `project_id` | `projects.id` |
| `activity_logs` | `user_id` | `users.id` |
| `budget_allocations` | `activity_id` | `activities.id` |
| `budget_allocations` | `category_id` | `budget_categories.id` |
| `budget_allocations` | `item_id` | `budget_category_items.id` |
| `budget_allocations` | `organization_id` | `organizations.id` |
| `budget_allocations` | `plan_id` | `plans.id` |
| `budget_categories` | `parent_id` | `budget_categories.id` |
| `budget_category_items` | `parent_id` | `budget_category_items.id` |
| `budget_line_items` | `activity_id` | `activities.id` |
| `budget_line_items` | `budget_type_id` | `budget_types.id` |
| `budget_line_items` | `expense_group_id` | `expense_groups.id` |
| `budget_line_items` | `expense_item_id` | `expense_items.id` |
| `budget_line_items` | `expense_type_id` | `expense_types.id` |
| `budget_line_items` | `plan_id` | `plans.id` |
| `budget_line_items` | `project_id` | `projects.id` |
| `budget_line_items` | `province_id` | `provinces.id` |
| `budget_records` | `budget_id` | `budgets.id` |
| `budget_records` | `created_by` | `users.id` |
| `budget_records` | `updated_by` | `users.id` |
| `budget_requests` | `created_by` | `users.id` |
| `budget_trackings` | `activity_id` | `activities.id` |
| `budget_trackings` | `budget_category_item_id` | `budget_category_items.id` |
| `budget_trackings` | `budget_type_id` | `budget_types.id` |
| `budget_trackings` | `disbursement_record_id` | `disbursement_records.id` |
| `budget_trackings` | `expense_group_id` | `expense_groups.id` |
| `budget_trackings` | `expense_item_id` | `expense_items.id` |
| `budget_trackings` | `expense_type_id` | `expense_types.id` |
| `budget_trackings` | `organization_id` | `organizations.id` |
| `budget_trackings` | `plan_id` | `plans.id` |
| `budget_trackings` | `project_id` | `projects.id` |
| `budget_transactions` | `budget_id` | `budgets.id` |
| `budget_transactions` | `created_by` | `users.id` |
| `budgets` | `activity_id` | `activities.id` |
| `budgets` | `approved_by` | `users.id` |
| `budgets` | `budget_type_id` | `budget_types.id` |
| `budgets` | `category_id` | `budget_categories.id` |
| `budgets` | `created_by` | `users.id` |
| `budgets` | `expense_group_id` | `expense_groups.id` |
| `budgets` | `expense_item_id` | `expense_items.id` |
| `budgets` | `expense_type_id` | `expense_types.id` |
| `budgets` | `plan_id` | `plans.id` |
| `budgets` | `project_id` | `projects.id` |
| `disbursement_records` | `activity_id` | `activities.id` |
| `disbursement_records` | `session_id` | `disbursement_sessions.id` |
| `disbursement_sessions` | `organization_id` | `organizations.id` |
| `expense_groups` | `expense_type_id` | `expense_types.id` |
| `expense_items` | `expense_group_id` | `expense_groups.id` |
| `expense_items` | `parent_id` | `expense_items.id` |
| `files` | `folder_id` | `folders.id` |
| `files` | `uploaded_by` | `users.id` |
| `folders` | `created_by` | `users.id` |
| `folders` | `parent_id` | `folders.id` |
| `kpi_actuals` | `kpi_target_id` | `kpi_targets.id` |
| `kpi_definitions` | `kpi_source_id` | `kpi_sources.id` |
| `kpi_targets` | `activity_id` | `activities.id` |
| `kpi_targets` | `budget_line_item_id` | `budget_line_items.id` |
| `kpi_targets` | `budget_type_id` | `budget_types.id` |
| `kpi_targets` | `kpi_definition_id` | `kpi_definitions.id` |
| `kpi_targets` | `organization_id` | `organizations.id` |
| `kpi_targets` | `plan_id` | `plans.id` |
| `kpi_targets` | `project_id` | `projects.id` |
| `organizations` | `parent_id` | `organizations.id` |
| `plans` | `budget_type_id` | `budget_types.id` |
| `projects` | `parent_id` | `projects.id` |
| `projects` | `plan_id` | `plans.id` |
| `source_of_truth_mappings` | `activity_id` | `activities.id` |
| `source_of_truth_mappings` | `organization_id` | `organizations.id` |
| `source_of_truth_mappings` | `plan_id` | `plans.id` |
| `source_of_truth_mappings` | `project_id` | `projects.id` |

## Missing Foreign Keys (Recommended)

| Table | Column | Should Reference | Status |
|-------|--------|------------------|--------|
| `v_organizations_hierarchy` | `parent_id` | `v_organizations_hierarchy.id` | Safe |