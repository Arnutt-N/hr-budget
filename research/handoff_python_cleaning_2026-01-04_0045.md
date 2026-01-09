# Handoff: Budget Data Cleaning via Python
**Timestamp:** 2026-01-04 00:45:00

## 1. Objective
Refactor the budget tracking filtering logic by using Python as a Data Cleaning/ETL layer to resolve redundant data display (7 plans shown instead of 1).

## 2. Current Status & Findings
- **Target:** "กองบริหารทรัพยากรบุคคล" (Org ID 3), Fiscal Year 2569.
- **Problem:** Page shows 7 budget plans, but user expects only 1 ("แผนงานบุคลากรภาครัฐ").
- **Database Analysis:**
    - `budget_line_items`: Contains records for Org ID 3 in 7 plans, but **all amounts (PBA, Received, Disbursed) are 0.00**.
    - `budget_allocations`: Currently returns 0 records for Org ID 3 (might be recorded at Parent level).
    - **Root Cause:** Data inconsistency in the raw tables (`budget_line_items`) where many placeholder records exist without actual budget.

## 3. Temporary Debug Code (Pending Removal)
The following files currently contain debug panels and extra queries:
- `src/Controllers/BudgetController.php` (Lines 440-480, 580-590)
- `resources/views/budgets/tracking/activities.php` (Yellow debug box at top)

## 4. Strategy for Python Session
1. **Analyze:** Use Python (Pandas) to scan `budget_line_items`, `budget_allocations`, and `organizations` to find the "Official" mapping.
2. **Clean:** Filter out zero-budget rows and resolve hierarchy (Division vs. Department).
3. **Register:** Create/Update a `source_of_truth_mappings` table.
4. **PHP Integration:** Update `BudgetController` to query the clean mapping table instead of the raw `budget_line_items`.

## 5. Metadata for Next Agent
- **DB Name:** `hr_budget`
- **Tables involved:** `budget_line_items`, `budget_allocations`, `plans`, `organizations`, `activities`.
- **Target Schema:** Need a clean path from `organization_id` -> `activity_id` for year `X`.
