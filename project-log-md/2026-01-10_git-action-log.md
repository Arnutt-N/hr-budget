# Git Action Log: 2026-01-10
## Objective: Refactor Request Form UI & Correct Hierarchical Logic

### 🚀 Commands to Execute (Copy & Paste)

```cmd
:: 1. Stage all relevant changes
git add resources/views/requests/form.php resources/views/requests/index.php src/Controllers/BudgetRequestController.php src/Models/BudgetCategory.php src/Models/BudgetCategoryItem.php routes/web.php .agent/workflows/git-workflow.md project-log-md/2026-01-10_git-action-log.md src/Models/BudgetRequestApproval.php

:: 2. Commit changes
git commit -m "feat(requests): refine request form UI, update modal titles and fix hierarchical logic"

:: 3. Create Tag (v1.2.0)
git tag -a v1.2.0 -m "Release v1.2.0: Budget Request refinement, calculation fixes, and UI consistency"

:: 4. Push to remote
git push origin main && git push origin v1.2.0

:: 5. Append git log to this file (Handover)
git log -1 --stat >> project-log-md\2026-01-10_git-action-log.md
```

### 📝 Summary of Changes
- **Header & Modal**: Updated breadcrumb and renamed Create Modal title to "สร้างคำของบประมาณ" for both Admin/User.
- **Tabs**: Added placeholders for Investment, Subsidy, and Other categories (total 5 tabs).
- **Footer**: Renamed/Added "ล้างข้อมูล" (Clear Data) with JS logic to reset form and recalculate.
- **Hierarchical Logic**: 
    - Parent rows now use disabled gold-colored inputs for aggregated Qty, Price, and Amount.
    - Updated JS `updateParentTotals()` calculation logic and fixed Tab switching visibility.
- **Workflows**: Enhanced `git-workflow.md` with tagging and logging standards.
- **Backend**: Implemented draft creation flow in `BudgetRequestController` and added `BudgetRequestApproval` model.

---
### 🔍 Git Log Output (Appended below)
