# 📝 Git Action Log

บันทึกประวัติการเปลี่ยนแปลง (Git Commits & Tags) ของโปรเจกต์

| Date | Time | Version | Commit Message | Files Involved | Status |
|------|------|---------|----------------|----------------|--------|
| 2026-01-10 | 22:15 | v1.2.0 | feat(requests): refine request form UI, update modal titles and fix hierarchical logic | resources/views/requests/form.php, index.php, etc. | ✅ Pushed |

---

### 🔍 Detailed Git History (Latest)
commit 5d72178c9d3cd720aca939d7c6be3f2c64cf3b4e
Author: Arnutt Noitumyae <arnutt.n@gmail.com>
Date:   Sat Jan 10 22:12:48 2026 +0700

    feat(requests): refine request form UI, update modal titles and fix hierarchical logic

 .agent/workflows/git-workflow.md            |  18 +-
 project-log-md/2026-01-10_git-action-log.md |  34 ++
 public/inspect_debug.php                    |  40 ++
 python/analyze_request_form_schema.py       | 191 +++++++
 resources/views/requests/form.php           | 851 ++++++++++++++++++++--------
 resources/views/requests/index.php          |  64 ++-
 routes/web.php                              |   2 +
 src/Controllers/BudgetRequestController.php | 390 ++++++-------
 src/Models/BudgetCategory.php               |  21 +
 src/Models/BudgetCategoryItem.php           |  73 ++-
 src/Models/BudgetRequestApproval.php        |  40 ++
 11 files changed, 1217 insertions(+), 507 deletions(-)
commit 56f968839674d4dd1fcae60aebff76625f8193b3
Author: Arnutt Noitumyae <arnutt.n@gmail.com>
Date:   Tue Jan 13 01:57:14 2026 +0700

    style(requests): restore integrated footer, add table totals, UX enhancements

 research/2026-01-13_budget-request-form-summary.md | 170 +++++++
 resources/views/requests/form.php                  | 523 +++++++++++++++------
 src/Models/BudgetCategoryItem.php                  | 188 ++++----
 src/Models/ExpenseGroup.php                        |  21 +-
 4 files changed, 662 insertions(+), 240 deletions(-)
