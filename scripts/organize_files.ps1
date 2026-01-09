# File Organization Script
# This script organizes files in the hr_budget project according to the folder structure guidelines

Write-Host "=== Starting File Organization ===" -ForegroundColor Cyan
Write-Host ""

$moved = 0
$failed = 0
$errors = @()

# Function to move file with error handling
function Move-FileWithLog {
    param($source, $destination)
    
    if (Test-Path $source) {
        try {
            Move-Item -Path $source -Destination $destination -ErrorAction Stop
            Write-Host "[OK] $source -> $destination" -ForegroundColor Green
            return $true
        } catch {
            Write-Host "[FAIL] $source : $_" -ForegroundColor Red
            $script:errors += "$source : $_"
            return $false
        }
    } else {
        Write-Host "[SKIP] $source (not found)" -ForegroundColor Yellow
        return $null
    }
}

# Documentation files to docs/
Write-Host "`nMoving documentation files to docs/..." -ForegroundColor Yellow
$docFiles = @(
    "ADD_ADMIN_COLUMNS.md",
    "ADMIN_COLUMNS_SUMMARY.md",
    "FIX_CODE_COLUMN_ERROR.md",
    "HANDOFF_SUMMARY.md",
    "MANUAL_MIGRATION_STEPS.md",
    "README_FIX.md",
    "RECREATE_TABLE_STEPS.md",
    "TROUBLESHOOT_FK_ERROR.md"
)
foreach ($file in $docFiles) {
    $result = Move-FileWithLog $file "docs\"
    if ($result -eq $true) { $moved++ } elseif ($result -eq $false) { $failed++ }
}

# Script files to scripts/
Write-Host "`nMoving script files to scripts/..." -ForegroundColor Yellow
$scriptFiles = @(
    "add_item.php", "alter_enum.php", "auto_fix_session.php", 
    "backup_script.php", "backup_session.php", "clean_import.php", "clean_import_v2.php",
    "diagnose_now.php", "direct_fix.php", "dump_schema.php", "find_items.php",
    "fix_cli.php", "fix_division_data.php", "fix_session.sql", "fix_session_cli.php",
    "fix_session_org.php", "fix_tracking_migration.php", "import_budget_runner.php",
    "master_import.php", "migrate_disbursement.php", "migrate_now.php", "read_excel.php",
    "run_036.bat", "run_036_pdo.php", "run_backup_debug.bat", "run_fix.bat",
    "run_mapping_migration.php", "run_migration_embedded.php", "run_migration_php.php",
    "run_migration_v3.php", "run_migration_v4.php", "run_migration_v5.php", "run_migration_v6.php",
    "run_special_profession_migration.php", "run_special_profession_migration_log.php",
    "run_sql.bat", "run_sql_18.bat", "search_expense.php", "show_schema.php", "print_schema.php",
    "sync_budget_plans.php", "sync_missing_activities.php", "sync_v2.php", "sync_v3.php",
    "sync_v4.php", "sync_v5.php", "sync_v6.php", "verify.bat"
)
foreach ($file in $scriptFiles) {
    $result = Move-FileWithLog $file "scripts\"
    if ($result -eq $true) { $moved++ } elseif ($result -eq $false) { $failed++ }
}

# Test/Debug files to archives/test/
Write-Host "`nMoving test/debug files to archives\test\..." -ForegroundColor Yellow
$testFiles = @(
    "check_activities.php", "check_bp_cols.php", "check_cols.php", "check_columns_budget_trackings.php",
    "check_corruption.php", "check_db_hex.php", "check_fy.php", "check_import.php",
    "check_linkage.php", "check_org3_plans.php", "check_org_match.php", "check_parent.php",
    "check_parent_log.php", "check_parent_pdo.php", "check_table.php", "check_tables.php",
    "debug_budget_data.php", "debug_check_parents.php", "debug_csv_hex.php", "debug_dashboard_data.php",
    "debug_db.php", "debug_duplicates.php", "debug_encoding.php", "debug_org_3.php",
    "debug_record_details.php", "debug_session.php", "list_expenses.php", "run_disbursement_test.php",
    "simple_check.php", "test_php.php", "test_tree.php", "test_write_simple.php",
    "verify_bp.php", "verify_disburse_db.php", "verify_fix.php", "verify_import.php",
    "verify_migration.php", "verify_migration_raw.php", "verify_ui_logic.php", "verify_ui_standalone.php"
)
foreach ($file in $testFiles) {
    $result = Move-FileWithLog $file "archives\test\"
    if ($result -eq $true) { $moved++ } elseif ($result -eq $false) { $failed++ }
}

# Python files to python/
Write-Host "`nMoving Python files to python/..." -ForegroundColor Yellow
$pythonFiles = @("check_types.py", "list_expenses.py")
foreach ($file in $pythonFiles) {
    $result = Move-FileWithLog $file "python\"
    if ($result -eq $true) { $moved++ } elseif ($result -eq $false) { $failed++ }
}

# Log files to project-log-md/
Write-Host "`nMoving log files to project-log-md/..." -ForegroundColor Yellow
$logFiles = @("verification_result_raw.txt")
foreach ($file in $logFiles) {
    $result = Move-FileWithLog $file "project-log-md\"
    if ($result -eq $true) { $moved++ } elseif ($result -eq $false) { $failed++ }
}

# Summary
Write-Host "`n=== Summary ===" -ForegroundColor Cyan
Write-Host "Files moved: $moved" -ForegroundColor Green
Write-Host "Files failed: $failed" -ForegroundColor $(if ($failed -gt 0) { "Red" } else { "Green" })
Write-Host "Total processed: $($moved + $failed)"

if ($errors.Count -gt 0) {
    Write-Host "`nErrors encountered:" -ForegroundColor Red
    foreach ($error in $errors) {
        Write-Host "  - $error" -ForegroundColor Red
    }
}

Write-Host "`n=== File Organization Complete ===" -ForegroundColor Cyan
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
