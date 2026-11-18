# Fiscal code import plan

Thai budget fiscal codes move into `hr_budget` across six stacked PRs. The rule the program enforces is one taxonomy and no second expense table. `expense_types` gains a three-digit `fiscal_code`, the code shapes get validated at the API boundary, and four reports read the result.

PR1 adds the taxonomy and repairs the encoding debt in the rows it touches. PR2 adds the `FiscalCode` helper. PR3 adds the code columns to `budget_categories`, `plans`, and `projects`. PR4 imports the 90 codes from the spreadsheet. PR5 wires validation into the DTOs. PR6 adds the reports.

## How to read this

One box is one unit of work. Every box names the evidence that checks it. A nested box is a sub-step of the box above it. Check a box only when its evidence exists, a file, a log line, a screenshot, a test run, or a SHA. The body is a how-to. The appendices explain and record.

The program runs `skills/poteto-mode/playbooks/autopilot-stack.md` from the installed plugin. The operator lands each PR bottom-up after the root records a clean verdict. PR1 through PR6 stop at merge-ready.

Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked.

## Program checklist

### Arm the program

- [ ] State the protocol and this plan to the operator, then stop. Start execution only on the operator's explicit go.
- [ ] On the operator's go, write this exact text into `notes.md` in the program's store under `~/.claude/orchestrate/fiscal-code-import/`, then restate it in `.audit/fiscal-code-import.todo.md`. "Plan at `PRPs/2026-11-16_fiscal-code-import-plan.md`. PR ids in order, PR1 PR2 PR3 PR4 PR5 PR6, stacked. Verification rule, unit plus live plus perf, every PR. The operator merges. Done when PR6 is merged and the four reports render Thai labels."
- [ ] Read these from the installed plugin at program start, and re-read them at every tick.
  - [ ] `skills/poteto-mode/playbooks/autopilot-stack.md`
  - [ ] `skills/swarm/SKILL.md`
  - [ ] `skills/run/SKILL.md` for the driver
  - [ ] `skills/poteto-mode/playbooks/opening-a-pr.md`
  - [ ] `skills/how/SKILL.md` before PR1, PR3, and PR5
  - [ ] `skills/interrogate/SKILL.md` before PR1 merge, because the taxonomy call is contested by the existing `expense_types` rows
- [ ] Arm the hourly audit tick as a real `/loop` in dynamic mode, which schedules its own wake-up rather than blocking on a sleep. Never leave the cadence to memory.
- [ ] Use this tick prompt, verbatim. "Re-read the execution playbook from the installed plugin and the objective in the program store's `notes.md`. Audit the operation against both and fix drift in this tick. Probe every active lane and judge progress by side effects only. Stand down a stuck lane and dispatch its replacement now. Then post a short status message to the operator in chat only when the audit found a tracked change that no earlier status message reported, such as a PR opened, a code-ready head, a round launched or closed, a verdict, a merge, a stuck agent and the action taken, a blocker added or cleared, or a decision only the operator can make. Name every such change and nothing else. Do not repeat a table, the merged list, or an unchanged blocker. If the audit found none, end the turn with no reply text. Either way, log this tick's row in your decision trail. The row names the items reported, or none."
- [ ] On the operator's hold or stand-down, send every owner a zero-writes order at once.

### Spawn owners

- [ ] Spawn one owner per PR with the full lifecycle `skills/poteto-mode/playbooks/autopilot-stack.md` names.
- [ ] Follow this dependency graph. Start dependent work only after its parent merges.
  - [ ] PR1 and PR2 are independent and first. Both branch from `main`.
  - [ ] PR3 after PR1, because the code columns reference the taxonomy PR1 creates.
  - [ ] PR4 after PR2 and PR3, because the importer calls the helper and writes the columns.
  - [ ] PR5 after PR2, because the DTOs call the helper.
  - [ ] PR6 after PR4, because the reports read imported codes.
- [ ] Hold the file boundaries. PR1 touches only `database/migrations/094_*` plus one seed script. PR2 touches only `src/Helpers/` and `tests/Unit/Helpers/`. PR3 touches only `database/migrations/095_*`. PR4 touches only `scripts/import_fiscal_codes.php` plus its config. PR5 touches only `src/Dtos/`, `src/Repositories/`, and `src/Services/`. PR6 touches only `src/Api/Controllers/`, `src/Services/`, and `routes/web.php`.
- [ ] Hold the review gate. PR5 and PR6 change an interaction. They wait for the operator's review in chat with screenshots and a video before merge.

### PR mechanics, for every PR

- [ ] Resolve the forge once. Default to `gh`, and fall back to `origin pr` only when `command -v origin` succeeds. Record any fallback to `gh`.
- [ ] Open the PR ready, never draft, per `skills/poteto-mode/playbooks/opening-a-pr.md`. Use `gh pr create --base <base-branch>`. A stack child targets its parent branch.
- [ ] Run `composer verify` and, when the diff touches `frontend/`, `npm run verify` in `frontend/`, once before the PR-facing push. Push with hooks on.
- [ ] Run `/deslop` before each commit and `/no-comments` before review.
- [ ] Triage every review-bot and security-reviewer comment per `skills/poteto-mode/references/bugbot-triage.md`.
- [ ] Record the base and head SHAs before the code-ready report and again after merge prep.

### Verdict and merge, for every PR

- [ ] A full swarm runs once, at the code-ready head. One gates lane. The ten live lanes from the PR's `Verify, live` block. The perf lane from its `Verify, perf` block. Two or more audit lanes, each with its own focus, that read the diff and the receipts and distrust the PR body.
- [ ] The root audits the receipts in the merge-ready report before the verdict.
- [ ] Judge every finding by `skills/poteto-mode/playbooks/autopilot-stack.md`. The verdict is clean when none blocks.
- [ ] A new head gets a fresh verdict, except for results that stay valid under the patch-id rule in `skills/poteto-mode/playbooks/shipping.md`.
- [ ] The root appends the PR to the base-branch stack and the operator lands it bottom-up.

### Boot recipe, for every live lane

Each live lane runs in its own worktree at the PR head. Drive through `skills/run/SKILL.md`, falling back to the exact commands below when the skill has no project entry.

- [ ] `git fetch origin <head-branch> && git checkout <head SHA>`.
- [ ] Point the backend at a seeded test database. `DB_DATABASE=hr_budget_test php -S 127.0.0.1:18080 -t public/ public/index.php`, then wait for `curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:18080/api/v1/expense-structure` to answer 401, which means the router is live and auth is armed.
- [ ] Serve the SPA. `cd frontend && npx vite preview --port 5174`, then wait for `curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:5174/` to answer 200.
- [ ] Deliver input only through the driver commands. Read-only diagnostics are `curl` with the JWT cookie, `mysql` for row counts, and `vendor/bin/phpunit` for the unit suite.
- [ ] Save every screenshot to `/tmp/swarm-<pr-id>/worker-<n>/<slug>.png` and return the paths with the report.

## Add fiscal codes to expense_types (PR1)

**Depends on.** None.

**Files.**

- [ ] Create `database/migrations/094_add_fiscal_code_to_expense_types.sql`.
- [ ] Create `database/migrations/094_rollback_fiscal_code_to_expense_types.sql`.
- [ ] Create `scripts/seed_expense_type_fiscal_codes.php`.

**Build.**

- [ ] Add `fiscal_code CHAR(3) NULL` plus `UNIQUE KEY uk_expense_types_fiscal_code (fiscal_code)` to `expense_types` in migration 094.
- [ ] Insert the three missing rows in migration 094, with `code` set to `ET-` plus the first four hex characters of `md5(name_th)`, matching the convention `scripts/import_budget_csv.php` already uses. The rows are `031` ครุภัณฑ์และค่าจ้างบริการ, `032` ค่าก่อสร้างและปรับปรุงอาคาร, and `050` ค่าใช้จ่ายโครงการ.
- [ ] Set `fiscal_code` on the three existing rows the md5 codes identify, in migration 094. `ET-dda6` is งบบุคลากร and takes `010`. `ET-57d1` is งบดำเนินงาน and takes `020`. `ET-8d6b` is งบเงินอุดหนุน and takes `041`. Leave `ET-914f` งบลงทุน and `ET-dc9b` งบรายจ่ายอื่น at NULL, because the source spreadsheet has no such category.
- [ ] Repair the five mojibake `name_th` values in `scripts/seed_expense_type_fiscal_codes.php`, not in the migration, because a migration cannot read the CSV. Write each name as a UTF-8 literal and update by `code`, which is stable and readable, rather than by `name_th`, which is the corrupted column.
- [ ] Make migration 094 idempotent. Use `INSERT IGNORE` for the new rows and `UPDATE ... WHERE fiscal_code IS NULL` for the code assignment, so a second run changes nothing.

**You see.**

- [ ] `mysql -uroot --default-character-set=utf8mb4 hr_budget_test -e "SELECT code, fiscal_code, name_th FROM expense_types ORDER BY fiscal_code, code"` prints eight rows, six with a `fiscal_code`, two with NULL, and no `?` in any `name_th`.
- [ ] A second application of migration 094 prints `0 rows affected` for every statement after the first run.

**Verify, unit.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked.

- [ ] Add `tests/Unit/Scripts/ExpenseTypeFiscalCodeSeedTest.php` and assert the md5 convention, so `ET-` plus `substr(md5('งบบุคลากร'), 0, 4)` equals `ET-dda6` and the same holds for the other four names. Run `vendor/bin/phpunit --filter=ExpenseTypeFiscalCodeSeedTest`.
- [ ] Assert the six category codes are exactly `010 020 031 032 041 050` and that no two rows claim one code. Run `vendor/bin/phpunit --filter=ExpenseTypeFiscalCodeSeedTest`.

**Verify, live.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked. Ten lanes on the configured `swarm workers` model at the PR head, per the boot recipe.

- [ ] Lane 1. Regression lane against trunk. Run `GET /api/v1/expense-structure` at trunk and head. Trunk lacks `fiscal_code`, so record that and gate the new field plus the expense tree still returning 200 with the same node count. Save `regression.png`. Pass when head returns 200 and the node count is unchanged.
- [ ] Lane 2. Run the seed script against `hr_budget_test` and read the table back. Save `seeded.png`. Pass when eight rows exist and six carry a `fiscal_code`.
- [ ] Lane 3. Read every `name_th` through `--default-character-set=utf8mb4` and grep for `?`. Save `no-mojibake.png`. Pass when no row matches `/\?{3,}/`.
- [ ] Lane 4. Run migration 094 a second time and capture affected-row counts. Save `idempotent.png`. Pass when every statement reports zero rows changed.
- [ ] Lane 5. Apply `094_rollback_fiscal_code_to_expense_types.sql`, then re-apply 094. Save `rollback.png`. Pass when the table returns to eight rows with six codes and no error.
- [ ] Lane 6. Load the SPA disbursement page and open the expense-type selector. Save `spa-selector.png`. Pass when eight options render and none shows `?`.
- [ ] Lane 7. Create a disbursement session through the SPA against `hr_budget_test`. Save `session-create.png`. Pass when the session persists with a valid `expense_type_id`, proving the foreign key fan-in survived.
- [ ] Lane 8. Run `GET /api/v1/disbursement-records/{id}` for a seeded record. Save `record-read.png`. Pass when the response resolves `expense_type_id` to a Thai name, not `?`.
- [ ] Lane 9. Count rows in `expense_groups`, `expense_items`, `budget_line_items`, and `budget_trackings` before and after the migration. Save `fanin.png`. Pass when all four counts are identical.
- [ ] Lane 10. Run `composer verify` at head and capture the PHPStan and PHPUnit summary. Save `verify.png`. Pass when both report zero errors.

**Verify, perf.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked.

- [ ] Metric. Wall-clock time of one application of migration 094 against `hr_budget_test`.
- [ ] Probe. `/usr/bin/time -f "%e" mysql -uroot hr_budget_test < database/migrations/094_add_fiscal_code_to_expense_types.sql`, run five times, alternating with the same command against an untouched copy of the database at trunk.
- [ ] Baseline. Record the trunk figure first, which is the cost of the same statements without the `fiscal_code` column, expected near 0.1 seconds.
- [ ] Rule. Head stays under 2 seconds, and the second consecutive run stays under 1 second. A figure above 2 seconds means the migration is doing a full table scan, which fails.

**Review gate.** None. PR1 is not review-gated.

**Merge.**

- [ ] Root's clean verdict at the exact head SHA.
- [ ] Bugbot triage done.
- [ ] Base and verdict are current under `skills/poteto-mode/playbooks/autopilot-stack.md` and the patch-id rule.
- [ ] The root appends PR1 to the base-branch stack and the operator lands it.

## Add the FiscalCode helper (PR2)

**Depends on.** None, though it shares the six code constants with PR1.

**Files.**

- [ ] Create `src/Helpers/FiscalCode.php`.
- [ ] Create `tests/Unit/Helpers/FiscalCodeTest.php`.

**Build.**

- [ ] Write `final class FiscalCode` in namespace `App\Helpers`, with `declare(strict_types=1)` and only `public static` pure methods. Match the modern majority in `src/`, not `DateHelper.php`, which omits strict types.
- [ ] Add `isValid20(string $code): bool`, matching `^\d{20}$`.
- [ ] Add `parent13(string $code20): string`, returning `substr($code20, 0, 13)`.
- [ ] Add `expenseCategory(string $code20): string`, returning `substr($code20, 12, 3)`.
- [ ] Add `isValidExpenseCategory(string $code20): bool`, true only when the three digits are one of `010 020 031 032 041 050`.
- [ ] Add `isValidActivityCode(string $code): bool`, matching `^\d{17}$`.
- [ ] Add `activityGroup(string $code20): string`, returning `substr($code20, 0, 12)`, the join key for the 17-digit activity codes.
- [ ] Add `EXPENSE_CATEGORIES` as a class constant array mapping the three-digit code to its Thai name, the same six names PR1 seeds.

**You see.**

- [ ] `vendor/bin/phpunit --testsuite Unit` passes with the new file, and `composer verify` reports PHPStan clean on `src/Helpers/FiscalCode.php`.
- [ ] A one-line CLI probe, `php -r "require 'vendor/autoload.php'; var_dump(App\Helpers\FiscalCode::isValid20('16002070012004100001'));"`, prints `bool(true)`.

**Verify, unit.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked.

- [ ] Add `tests/Unit/Helpers/FiscalCodeTest.php`, extending `PHPUnit\Framework\TestCase`, not `Tests\TestCase`, which forces a database. Cover the true shapes, a valid 20-digit code, a 13-digit parent, a 17-digit activity code, and all six category codes.
- [ ] Cover the false shapes, a 19-digit code, a 21-digit code, a code with a letter, an empty string, a category digit of `099`, and a 16-digit activity code. Run `vendor/bin/phpunit --filter=FiscalCodeTest`.
- [ ] Assert `parent13` returns exactly 13 characters for every valid 20-digit input, and `expenseCategory` returns exactly 3. Run `vendor/bin/phpunit --filter=FiscalCodeTest`.
- [ ] Assert `EXPENSE_CATEGORIES` keys equal the six seed codes from PR1, so the helper and the seed cannot drift. Run `vendor/bin/phpunit --filter=FiscalCodeTest`.

**Verify, live.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked. Ten lanes on the configured `swarm workers` model at the PR head, per the boot recipe.

- [ ] Lane 1. Regression lane against trunk. Run `vendor/bin/phpunit --testsuite Unit` at trunk and head. Trunk lacks the helper, so record that and gate the new test file plus the unchanged total of the other 523 tests. Save `regression.png`. Pass when head runs the full Unit suite green and the trunk suite is also green.
- [ ] Lane 2. Call `isValid20` with each of the 75 real 20-digit codes from `secrets/fisical-code/16002_รหัสงบประมาณ.xlsx`. Save `valid20.png`. Pass when all 75 return true.
- [ ] Lane 3. Call `parent13` with each real code and compare to the 15 parent rows already in the spreadsheet. Save `parent13.png`. Pass when every child maps to a parent that exists.
- [ ] Lane 4. Call `expenseCategory` with each real code and group the counts. Save `category-counts.png`. Pass when the counts are 032 equals 36, 050 equals 17, 031 equals 9, 041 equals 6, 020 equals 6, and 010 equals 1.
- [ ] Lane 5. Call `isValidExpenseCategory` with a code whose digits 13 to 15 are `099`. Save `bad-category.png`. Pass when it returns false.
- [ ] Lane 6. Call `isValidActivityCode` with each of the 15 real 17-digit codes from `16002_กิจกรรมหลัก.xls`. Save `activity17.png`. Pass when all 15 return true.
- [ ] Lane 7. Call `activityGroup` on a real 20-digit code and confirm the 12-character prefix does not collide with any 17-digit activity code's first 12 characters. Save `activity-group.png`. Pass when zero collisions appear, proving the two code systems stay separate.
- [ ] Lane 8. Run `php -l src/Helpers/FiscalCode.php` and `vendor/bin/phpstan analyse src/Helpers/FiscalCode.php --level 3`. Save `phpstan.png`. Pass when both print no errors.
- [ ] Lane 9. Require the file in a bare PHP process with no database connection and no framework bootstrap. Save `no-bootstrap.png`. Pass when `class_exists('App\Helpers\FiscalCode')` returns true, proving the helper stays pure.
- [ ] Lane 10. Run `composer verify` at head and capture the PHPStan plus PHPUnit summary. Save `verify.png`. Pass when both report zero errors.

**Verify, perf.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked.

- [ ] Metric. Wall-clock time of the full `FiscalCodeTest` suite.
- [ ] Probe. `vendor/bin/phpunit --filter=FiscalCodeTest --order-by=default`, run five times, alternating with the same command against an untouched copy of the repository at trunk.
- [ ] Baseline. Record the trunk figure first, which is the suite with zero tests, expected near 0.5 seconds.
- [ ] Rule. Head stays under 2 seconds per run, and the median of five runs stays under 1 second. A figure above 2 seconds means a test opened a database connection, which fails.

**Review gate.** None. PR2 is not review-gated.

**Merge.**

- [ ] Root's clean verdict at the exact head SHA.
- [ ] Bugbot triage done.
- [ ] Base and verdict are current under `skills/poteto-mode/playbooks/autopilot-stack.md` and the patch-id rule.
- [ ] The root appends PR2 to the base-branch stack and the operator lands it.

## Add fiscal code columns (PR3)

**Depends on.** PR1.

**Files.**

- [ ] Create `database/migrations/095_add_fiscal_code_columns.sql`.
- [ ] Create `database/migrations/095_rollback_fiscal_code_columns.sql`.

**Build.**

- [ ] Add to `budget_categories` in migration 095, `fiscal_code CHAR(20) NULL`, `fiscal_code_parent CHAR(13) NULL`, `activity_code CHAR(17) NULL`, and `expense_cat_code CHAR(3) NULL`, each with a comment naming its source in the spreadsheet.
- [ ] Add to `plans` in migration 095, `activity_code CHAR(17) NULL`.
- [ ] Add to `projects` in migration 095, `fiscal_code CHAR(20) NULL`.
- [ ] Add `UNIQUE KEY uk_fiscal_code (fiscal_code)` on `budget_categories` and `projects`, plus `KEY idx_fiscal_parent (fiscal_code_parent)`, `KEY idx_activity_code (activity_code)` on both `budget_categories` and `plans`, and `KEY idx_expense_cat (expense_cat_code)` on `budget_categories`.
- [ ] Keep every new column nullable, so rows that predate the import stay valid and the backfill in PR4 can run later.
- [ ] Make migration 095 idempotent. Guard each `ADD COLUMN` behind a `SELECT` check on `information_schema.COLUMNS`, or use the `IF NOT EXISTS` form the database engine accepts, so a second run is a no-op.

**You see.**

- [ ] `SHOW COLUMNS FROM budget_categories` lists the four new columns, `SHOW COLUMNS FROM plans` lists `activity_code`, and `SHOW COLUMNS FROM projects` lists `fiscal_code`, all nullable.
- [ ] `SHOW INDEX FROM budget_categories` lists `uk_fiscal_code`, `idx_fiscal_parent`, `idx_activity_code`, and `idx_expense_cat`.

**Verify, unit.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked.

- [ ] Add `tests/Unit/Repositories/FiscalCodeColumnTest.php` that reads `SHOW COLUMNS` from the test database and asserts the four column names and their exact lengths, 20, 13, 17, and 3. Run `vendor/bin/phpunit --filter=FiscalCodeColumnTest`.
- [ ] Assert the unique key exists on `fiscal_code` by reading `SHOW INDEX` and failing when the key is absent. Run `vendor/bin/phpunit --filter=FiscalCodeColumnTest`.

**Verify, live.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked. Ten lanes on the configured `swarm workers` model at the PR head, per the boot recipe.

- [ ] Lane 1. Regression lane against trunk. Create a budget category through `POST /api/v1/categories` at trunk and head. Trunk lacks the columns, so record that and gate the create still returning 201 with the same response shape. Save `regression.png`. Pass when head creates a category and the response omits the new fields rather than erroring.
- [ ] Lane 2. Apply migration 095 to `hr_budget_test` and read back `SHOW COLUMNS` for all three tables. Save `columns.png`. Pass when the seven new columns appear with the right types.
- [ ] Lane 3. Insert a row with a 20-digit `fiscal_code` and a 13-digit `fiscal_code_parent` directly, then read it back. Save `roundtrip.png`. Pass when both values return byte-identical.
- [ ] Lane 4. Insert two rows with the same `fiscal_code` and confirm the unique key rejects the second. Save `unique.png`. Pass when the second insert throws a duplicate-key error.
- [ ] Lane 5. Insert a row with `fiscal_code` NULL and confirm it succeeds alongside the populated row. Save `nullable.png`. Pass when both rows coexist.
- [ ] Lane 6. Run the SPA category admin page and open the create form. Save `spa-create.png`. Pass when the form still submits and the new row appears, proving the API still accepts payloads without the new fields.
- [ ] Lane 7. Apply migration 095 a second time and capture affected-row counts. Save `idempotent.png`. Pass when every statement reports zero rows changed.
- [ ] Lane 8. Apply `095_rollback_fiscal_code_columns.sql`, then re-apply 095. Save `rollback.png`. Pass when the columns vanish and return without error.
- [ ] Lane 9. Count rows in `budget_categories`, `plans`, and `projects` before and after the migration. Save `rowcount.png`. Pass when all three counts are identical.
- [ ] Lane 10. Run `composer verify` at head and capture the PHPStan and PHPUnit summary. Save `verify.png`. Pass when both report zero errors.

**Verify, perf.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked.

- [ ] Metric. Wall-clock time of one application of migration 095 against `hr_budget_test`.
- [ ] Probe. `/usr/bin/time -f "%e" mysql -uroot hr_budget_test < database/migrations/095_add_fiscal_code_columns.sql`, run five times, alternating with the same command against an untouched copy of the database at trunk.
- [ ] Baseline. Record the trunk figure first, which is the cost of the same statements without the new columns, expected near 0.2 seconds.
- [ ] Rule. Head stays under 5 seconds, and the second consecutive run stays under 1 second. A figure above 5 seconds means the index build scanned a large table, which fails.

**Review gate.** None. PR3 is not review-gated.

**Merge.**

- [ ] Root's clean verdict at the exact head SHA.
- [ ] Bugbot triage done.
- [ ] Base and verdict are current under `skills/poteto-mode/playbooks/autopilot-stack.md` and the patch-id rule.
- [ ] The root appends PR3 to the base-branch stack and the operator lands it.

## Import the fiscal codes (PR4)

**Depends on.** PR2 and PR3.

**Files.**

- [ ] Create `scripts/import_fiscal_codes.php`.
- [ ] Create `config/import_fiscal_codes.php`.

**Build.**

- [ ] Model the script on `scripts/import_budget_structure_v3.php`. Open with a docblock naming `Usage`, then `require_once __DIR__ . '/../vendor/autoload.php'`, then `ini_set('display_errors', 1)` and `error_reporting(E_ALL)`.
- [ ] Parse `getopt('', ['dry-run', 'verbose', 'help'])`, print a `--help` block, and `exit(0)` on it.
- [ ] Read the spreadsheet with PhpSpreadsheet, which `composer.json` already requires, so no new dependency enters the lock file.
- [ ] Write each 20-digit row as a `budget_categories` child, with `fiscal_code` set to the 20 digits, `fiscal_code_parent` set to `substr(code, 0, 13)`, `expense_cat_code` set to `substr(code, 12, 3)`, and `parent_id` set to the row whose `fiscal_code` equals that 13-digit prefix.
- [ ] Write each 13-digit row as a `budget_categories` row with `fiscal_code` at 13 digits and `parent_id` NULL, so every child has a parent and the tree matches the spreadsheet's 15 parents plus 75 children.
- [ ] Write each of the 15 activity rows as a `plans` row with `activity_code` at 17 digits, its `name_th`, and the current `fiscal_year`.
- [ ] Wrap the whole run in `Database::beginTransaction()`, `commit()`, and `rollback()` on `Throwable`, so a mid-way failure leaves nothing behind.
- [ ] Make every write idempotent. Insert a category only when `fiscal_code` is absent, which `uk_fiscal_code` already guarantees, and log a skip for rows that exist.
- [ ] Extract the province name from the long text and match it against the 77 canonical names, case and spacing ignored, recording the match rate in the summary without failing the run when a row matches nothing.
- [ ] Do not use the `fix_encoding` function from any older importer. It calls `utf8_decode` and then `iconv('TIS-620', 'UTF-8//IGNORE')`, which turns clean UTF-8 Thai into `?????`. The spreadsheet is already UTF-8, and the PDO connection already sets `utf8mb4`.

**You see.**

- [ ] `php scripts/import_fiscal_codes.php --dry-run --verbose` prints 90 rows read, 15 parents, 75 children, 15 activities, and 0 errors, and writes nothing.
- [ ] `php scripts/import_fiscal_codes.php --verbose` then prints the same counts with 90 rows written and 0 skipped.

**Verify, unit.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked.

- [ ] Add `tests/Unit/Scripts/FiscalCodeImportTest.php` and assert the parser splits the spreadsheet into 15 parent rows and 75 child rows, and that every child's 13-character prefix names an existing parent. Run `vendor/bin/phpunit --filter=FiscalCodeImportTest`.
- [ ] Assert the province matcher finds the expected 45 matches out of 90 rows, and that it returns no match for a row with no province text. Run `vendor/bin/phpunit --filter=FiscalCodeImportTest`.

**Verify, live.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked. Ten lanes on the configured `swarm workers` model at the PR head, per the boot recipe.

- [ ] Lane 1. Regression lane against trunk. Run the importer against `hr_budget_test` at trunk and head. Trunk lacks the columns, so record that and gate the head run writing exactly 90 category rows and 15 activity rows with zero errors. Save `regression.png`. Pass when the head run reports 90 plus 15 and trunk errors on the missing column.
- [ ] Lane 2. Run `--dry-run` and capture the summary line. Save `dryrun.png`. Pass when it reports 90 rows read and 0 rows written.
- [ ] Lane 3. Run the real import and count `fiscal_code` values in `budget_categories`. Save `import-count.png`. Pass when 90 rows carry a code and 75 of them are 20 digits.
- [ ] Lane 4. Join the 20-digit children to their 13-digit parents by `fiscal_code_parent` and count orphans. Save `orphans.png`. Pass when the orphan count is 0.
- [ ] Lane 5. Group by `expense_cat_code` and compare to the source counts of 032 equals 36, 050 equals 17, 031 equals 9, 041 equals 6, 020 equals 6, and 010 equals 1. Save `category-dist.png`. Pass when every count matches.
- [ ] Lane 6. Check `plans.activity_code` and confirm 15 rows carry a 17-digit code. Save `activities.png`. Pass when 15 rows carry a code and none is empty.
- [ ] Lane 7. Read back 5 Thai `name_th` values with `--default-character-set=utf8mb4` and grep for `?`. Save `thai.png`. Pass when no value contains three or more consecutive question marks.
- [ ] Lane 8. Run the importer a second time and capture the skip count. Save `idempotent.png`. Pass when it reports 0 rows written and 90 rows skipped, and the table counts do not change.
- [ ] Lane 9. Open the SPA category tree and expand the imported nodes. Save `spa-tree.png`. Pass when the Thai names render and the tree shows 90 new leaves under 15 new parents.
- [ ] Lane 10. Run `composer verify` at head and capture the PHPStan and PHPUnit summary. Save `verify.png`. Pass when both report zero errors.

**Verify, perf.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked.

- [ ] Metric. Wall-clock time of one import of the 90-row spreadsheet against `hr_budget_test`.
- [ ] Probe. `/usr/bin/time -f "%e" php scripts/import_fiscal_codes.php --verbose`, run five times against a freshly reset copy of the database, interleaved with the same command against an untouched copy at trunk.
- [ ] Baseline. Record the trunk figure first, which is the cost of reading the spreadsheet without writing, expected near 0.5 seconds.
- [ ] Rule. Head stays under 10 seconds per run. A figure above 10 seconds means the importer queries per row instead of batching, which fails.

**Review gate.** None. PR4 is not review-gated.

**Merge.**

- [ ] Root's clean verdict at the exact head SHA.
- [ ] Bugbot triage done.
- [ ] Base and verdict are current under `skills/poteto-mode/playbooks/autopilot-stack.md` and the patch-id rule.
- [ ] The root appends PR4 to the base-branch stack and the operator lands it.

## Validate fiscal codes at the API boundary (PR5)

**Depends on.** PR2.

**Files.**

- [ ] Edit `src/Dtos/CreateCategoryDto.php`.
- [ ] Edit `src/Dtos/UpdateCategoryDto.php`.
- [ ] Edit `src/Dtos/CreateCategoryItemDto.php`.
- [ ] Edit `src/Dtos/UpdateCategoryItemDto.php`.
- [ ] Edit `src/Dtos/CreatePlanDto.php`.
- [ ] Edit `src/Dtos/UpdatePlanDto.php`.
- [ ] Add `fiscal_code` and `expense_cat_code` to the `$allowed` list in `src/Repositories/BudgetCategoryRepository.php`.
- [ ] Add `activity_code` to the `$allowed` list in `src/Repositories/PlanRepository.php`.
- [ ] Carry the new fields through the `$updateData` builders in `src/Services/BudgetCategoryService.php` and `src/Services/PlanService.php`.

**Build.**

- [ ] In each DTO, call `FiscalCode::isValid20` on `fiscalCode` when it is not null, and `FiscalCode::isValidExpenseCategory` on it as well, so a code with a bad category digit is rejected even when its length is right.
- [ ] In `CreatePlanDto` and `UpdatePlanDto`, call `FiscalCode::isValidActivityCode` on `activityCode` when it is not null.
- [ ] Write the failure messages in Thai at the field level, matching the existing style, so `fiscal_code` returns `รหัสงบประมาณต้องมี 20 หลัก` and `activity_code` returns `รหัสกิจกรรมหลักต้องมี 17 หลัก`.
- [ ] Keep the controllers unchanged. They already turn a non-empty `validate()` result into a 422 through `ApiResponse::validationFailed`, so no transport code moves.
- [ ] Mirror `PlanService::create` for uniqueness, which calls `findByCodeYear` and returns null so the controller emits a clean 422. Check for an existing `fiscal_code` on category create and return null on collision, rather than letting the unique key bubble a MySQL error.

**You see.**

- [ ] `curl -s -X POST http://127.0.0.1:18080/api/v1/categories -H 'Content-Type: application/json' -d '{"code":"X","name_th":"ทดสอบ","fiscal_code":"12345"}'` returns 422 with `details.fiscal_code` set to the Thai message.
- [ ] The same request with a valid 20-digit `fiscal_code` returns 201.

**Verify, unit.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked.

- [ ] Extend `tests/Unit/Dtos/` with cases for each edited DTO, asserting `assertArrayHasKey('fiscal_code', $errors)` for a 19-digit code and `assertEmpty($errors)` for a valid one. Run `vendor/bin/phpunit --filter=DtoTest`.
- [ ] Assert `activity_code` with 16 digits fails and 17 digits passes in `CreatePlanDto` and `UpdatePlanDto`. Run `vendor/bin/phpunit --filter=DtoTest`.

**Verify, live.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked. Ten lanes on the configured `swarm workers` model at the PR head, per the boot recipe.

- [ ] Lane 1. Regression lane against trunk. Create a category with no `fiscal_code` at trunk and head. Trunk lacks the field, so record that and gate the head create still returning 201, proving the new validation is optional. Save `regression.png`. Pass when head creates the row without the field.
- [ ] Lane 2. Post a 19-digit `fiscal_code` and read the body. Save `reject19.png`. Pass when the status is 422 and `details.fiscal_code` names the 20-digit rule.
- [ ] Lane 3. Post a 20-digit code whose digits 13 to 15 are `099` and read the body. Save `reject-cat.png`. Pass when the status is 422 and the message names the category rule.
- [ ] Lane 4. Post a valid 20-digit code from the spreadsheet and read the response. Save `accept20.png`. Pass when the status is 201 and the stored row carries the exact code.
- [ ] Lane 5. Post the same valid code twice and read the second response. Save `reject-dup.png`. Pass when the second returns 422 with a duplicate message, not a 500.
- [ ] Lane 6. Put a category with a bad `fiscal_code` and read the body. Save `update-reject.png`. Pass when the status is 422 and the row is unchanged.
- [ ] Lane 7. Post a plan with a 16-digit `activity_code` and read the body. Save `plan-reject.png`. Pass when the status is 422 and `details.activity_code` names the 17-digit rule.
- [ ] Lane 8. Post a plan with a valid 17-digit `activity_code` from the spreadsheet and read the response. Save `plan-accept.png`. Pass when the status is 201 and the stored row carries the code.
- [ ] Lane 9. Submit the SPA category form with a bad code and capture the rendered error. Save `spa-error.png`. Pass when the Thai message renders next to the field and the form does not submit.
- [ ] Lane 10. Run `composer verify` at head and capture the PHPStan and PHPUnit summary. Save `verify.png`. Pass when both report zero errors.

**Verify, perf.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked.

- [ ] Metric. Wall-clock latency of `POST /api/v1/categories` with a valid payload.
- [ ] Probe. `curl -w "%{time_total}"` against the head, ten times, interleaved with the same request against an untouched copy of the repository at trunk.
- [ ] Baseline. Record the trunk median first, expected near 30 milliseconds.
- [ ] Rule. The head median stays within 1.5 times the trunk median, and the head p95 stays under 200 milliseconds. A median above 45 milliseconds fails.

**Review gate.** The operator reviews before merge.

- [ ] Copy the lane 2, lane 3, and lane 9 screenshots into `docs/media/pr5-fiscal-validation/`.
- [ ] Record a 30 to 60 second video of a valid and an invalid submission in a lane worktree. Save it as `docs/media/pr5-fiscal-validation/review.mp4`.
- [ ] Post the screenshots and the video in chat. Stop at merge-ready. Wait for the operator's click.

**Merge.**

- [ ] Root's clean verdict at the exact head SHA.
- [ ] Bugbot triage done.
- [ ] Base and verdict are current under `skills/poteto-mode/playbooks/autopilot-stack.md` and the patch-id rule.
- [ ] The root appends PR5 to the base-branch stack and the operator lands it.

## Add the four fiscal reports (PR6)

**Depends on.** PR4.

**Files.**

- [ ] Create `src/Services/FiscalCodeReportService.php`.
- [ ] Create `src/Api/Controllers/FiscalCodeReportController.php`.
- [ ] Edit `routes/web.php`.

**Build.**

- [ ] Add `byExpenseCategory`, `byActivity`, `byParentRollup`, and `byAgency` to `FiscalCodeReportService`, each taking a fiscal year and returning rows the controller serializes.
- [ ] Write `byExpenseCategory` as a `GROUP BY` over the three-digit category, with a `LEFT JOIN` to `expense_types` on `fiscal_code`, so the report reads the Thai name PR1 seeded.
- [ ] Write `byActivity` as a `GROUP BY` over `plans.activity_code`, joined to the 17-digit activity codes PR4 imported.
- [ ] Write `byParentRollup` as a `GROUP BY` over `budget_categories.fiscal_code_parent`, so each of the 15 parents carries its children's totals.
- [ ] Write `byAgency` as a `GROUP BY` over the first five characters of `fiscal_code`, joined to `divisions.code`, because the agency code is the leading five digits.
- [ ] Register the four routes in the read-only block of `routes/web.php`, guarded by `AuthMiddleware::require()`, alongside the existing analytics routes.
- [ ] Return every response through the `ApiResponse` envelope, and never `echo json_encode` from the controller.
- [ ] Render the Thai labels from `expense_types`, not from a hardcoded map, so the report and the taxonomy cannot disagree.

**You see.**

- [ ] `curl -s http://127.0.0.1:18080/api/v1/reports/fiscal-codes/by-expense-category -b cookies.txt` returns six rows whose Thai names match the six categories PR1 seeded.
- [ ] The parent rollup returns 15 rows whose children's totals sum to the grand total.

**Verify, unit.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked.

- [ ] Add `tests/Unit/Services/FiscalCodeReportServiceTest.php` that seeds a small tree in the test database and asserts each report returns the expected row count and column names. Run `vendor/bin/phpunit --filter=FiscalCodeReportServiceTest`.
- [ ] Assert `byExpenseCategory` returns exactly six rows and `byParentRollup` returns exactly fifteen, using the seeded fixtures. Run `vendor/bin/phpunit --filter=FiscalCodeReportServiceTest`.

**Verify, live.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked. Ten lanes on the configured `swarm workers` model at the PR head, per the boot recipe.

- [ ] Lane 1. Regression lane against trunk. Request `/api/v1/dashboard/summary` at trunk and head. Trunk lacks the report routes, so record that and gate the dashboard response staying unchanged while the four new routes return 200. Save `regression.png`. Pass when the dashboard is byte-identical and all four reports return 200.
- [ ] Lane 2. Request the expense-category report and count the rows. Save `report-cat.png`. Pass when six rows return with Thai names.
- [ ] Lane 3. Request the activity report and count the rows. Save `report-act.png`. Pass when fifteen rows return and none has an empty name.
- [ ] Lane 4. Request the parent rollup and sum the children's totals against the grand total. Save `report-rollup.png`. Pass when the two figures are equal to the cent.
- [ ] Lane 5. Request the agency report and check the leading five digits of every code. Save `report-agency.png`. Pass when every returned code is five characters and joins a division.
- [ ] Lane 6. Request each report without a JWT cookie. Save `unauth.png`. Pass when all four return 401 with a JSON body, not HTML.
- [ ] Lane 7. Request each report with a valid cookie but a viewer role. Save `viewer.png`. Pass when all four return 200, proving the reports are read-only and open to any authenticated user.
- [ ] Lane 8. Load the SPA dashboard and confirm no regression on the existing summary and chart-data calls. Save `spa-dashboard.png`. Pass when the dashboard renders with no console errors and the same figures as trunk.
- [ ] Lane 9. Capture the `Content-Type` header of each report response. Save `headers.png`. Pass when every response is `application/json; charset=UTF-8`.
- [ ] Lane 10. Run `composer verify` at head and capture the PHPStan and PHPUnit summary. Save `verify.png`. Pass when both report zero errors.

**Verify, perf.** Tests alone are not sufficient verification. A PR is verified only when its unit, live, and perf boxes are all checked.

- [ ] Metric. Wall-clock latency of the expense-category report against the seeded test database.
- [ ] Probe. `curl -w "%{time_total}"` against the head, ten times, interleaved with the same request against an untouched copy of the repository at trunk.
- [ ] Baseline. Record the trunk figure first, which is the latency of a route that does not exist, expected near 5 milliseconds.
- [ ] Rule. The head median stays under 100 milliseconds and the p95 stays under 250 milliseconds. A median above 100 milliseconds means a report loops per row, which fails.

**Review gate.** The operator reviews before merge.

- [ ] Copy the lane 2, lane 3, and lane 4 screenshots into `docs/media/pr6-fiscal-reports/`.
- [ ] Record a 30 to 60 second video of the four reports loading in the SPA in a lane worktree. Save it as `docs/media/pr6-fiscal-reports/review.mp4`.
- [ ] Post the screenshots and the video in chat. Stop at merge-ready. Wait for the operator's click.

**Merge.**

- [ ] Root's clean verdict at the exact head SHA.
- [ ] Bugbot triage done.
- [ ] Base and verdict are current under `skills/poteto-mode/playbooks/autopilot-stack.md` and the patch-id rule.
- [ ] The root appends PR6 to the base-branch stack and the operator lands it.

## Close the program

- [ ] Every box above is checked with its evidence.
- [ ] Reply to the operator with the report `skills/poteto-mode/playbooks/autopilot-stack.md` names, opening with the goal and naming who each PR was for.

## Appendix A. Prototype evidence

One prototype settled the taxonomy question. A script read every `name_th` in `expense_types`, `budget_categories`, `plans`, `projects`, and `budget_types` from `hr_budget` and printed the hex of each value. Every byte came back `0x3F`, which is a question mark, so no Thai text survives in those columns. The write path is not at fault, because a round trip through the app's own connection returned clean Thai. The corruption came from `scripts/import_budget_csv.php`, whose `fix_encoding` calls `utf8_decode` on already-clean UTF-8 and then drops the result through `iconv('TIS-620', 'UTF-8//IGNORE')`, which yields `?????`. The reproducible probe was `php /tmp/testv5.php`, which printed `CORRUPTED`.

A second prototype tested the key that PR1 relies on. A script computed `substr(md5($name), 0, 4)` for each of the five CSV expense names and matched all five against the live `code` values. `ET-dda6` is งบบุคลากร, `ET-57d1` is งบดำเนินงาน, `ET-914f` is งบลงทุน, `ET-dc9b` is งบรายจ่ายอื่น, and `ET-8d6b` is งบเงินอุดหนุน. This means PR1 can key the repair on `code` and never needs to match the corrupted `name_th`.

A third probe read the spreadsheet and grouped the 75 child rows by their three-digit category. The counts were 032 at 36, 050 at 17, 031 at 9, 041 at 6, 020 at 6, and 010 at 1, which is what lanes 5 of PR2 and PR4 assert.

What stays unproven. No prototype tested whether the SPA renders a Thai `name_th` through the full PHP to Vue path after the seed repair, because the repair does not exist yet. Lane 9 of PR1 is the check.

## Appendix B. Alternatives rejected

A new `expense_categories` table. The analysis document proposed it, but `expense_types` already exists with the same job description and four tables hold a foreign key to it, so a second table would leave those keys pointing at a taxonomy nobody reads. Rejected.

A child table under `expense_types`. It would keep one taxonomy and still add a join to every report, for a mapping that never varies beyond six rows. Rejected.

Deferring the encoding repair to a separate PR. The user chose to repair it inside PR1, because a taxonomy PR that leaves mojibake in the names it is meant to classify ships a half result.

Adding a `fix_encoding` guard to the importer. The function is already wrong for clean input, and a guard that detects both clean and corrupted Thai adds a heuristic where a single correct path works. PR4 simply does not call it.

Wiring the new columns through the service layer in PR3. PR3 only adds columns, and a column that no code writes stays invisible. PR5 carries the fields through the repositories and services, so the schema PR and the behavior PR stay separate.

## Appendix C. Risks

The four tables that hold a foreign key to `expense_types` may hold rows whose `expense_type_id` no longer resolves after PR1 inserts new rows. PR1 lands first, so the fan-in lane checks it, and the fix is a re-point rather than a delete. PR1 owner watches lane 9.

The CI snapshot `database/hr_budget_only.sql` carries the same mojibake, so CI runs on corrupted Thai names. PR1 does not rewrite the snapshot, because regenerating it is a separate decision. PR1 owner watches for CI failures that come from the snapshot rather than the migration, and records the fact in the merge report.

`budget_categories.code` is `varchar(20)` and unique. A 20-digit fiscal code fits exactly, so a longer code from a future spreadsheet would collide. PR3 owner watches lane 4 and lane 5.

The 17-digit activity codes overlap the 20-digit codes in their first twelve characters, so a join written on a prefix would silently mix the two systems. PR2 lane 7 checks zero collisions, and PR6 must join on the full 17 digits.

The PDF `รหัสงบประมาณ 2570.pdf` is fiscal year 2570, one year ahead of the spreadsheet, and holds every ministry rather than only 16002. Nothing imports it. If a later request wants it, it needs its own extraction and its own join, and the plan records it as out of scope.

The 45 rows that carry a province name are fuzzy-matched against 77 names, and a mismatch drops the province link rather than the row. PR4 lane 6 reports the rate, and a rate below 40 out of 45 is a blocker.

## Appendix D. Links and reading list

Read `docs/fiscal-code-analysis.md` for the code shapes, and treat its taxonomy section and its PDF section as corrected by `fiscal-code-analysis-corrections` in the memory directory. Read `AGENTS.md` for the migration numbering rule, the language rule, and the layered API style. Read `scripts/import_budget_structure_v3.php` for the script conventions, and read `scripts/import_budget_csv.php` only to see what not to call.

PR1 and PR5 get `skills/how/SKILL.md` before implementation, because both cross a subsystem boundary, the schema and the DTO layer respectively. PR1 also gets `skills/interrogate/SKILL.md` before merge, because the taxonomy call is contested by the existing rows. PR4 gets `skills/interrogate/SKILL.md` before merge, because a mass import of 90 rows deserves an adversarial read of its idempotency.

The decision trail lives in `.audit/fiscal-code-import.todo.md`, next to this plan's evidence, and the durable facts live in the memory files `fiscal-code-taxonomy-decision` and `fiscal-code-analysis-corrections`.