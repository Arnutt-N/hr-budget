@echo off
echo === Starting File Organization ===
echo.

:: Move Documentation files to docs
echo Moving documentation files...
move "ADD_ADMIN_COLUMNS.md" "docs\" 2>nul
move "ADMIN_COLUMNS_SUMMARY.md" "docs\" 2>nul
move "FIX_CODE_COLUMN_ERROR.md" "docs\" 2>nul
move "HANDOFF_SUMMARY.md" "docs\" 2>nul
move "MANUAL_MIGRATION_STEPS.md" "docs\" 2>nul
move "README_FIX.md" "docs\" 2>nul
move "RECREATE_TABLE_STEPS.md" "docs\" 2>nul
move "TROUBLESHOOT_FK_ERROR.md" "docs\" 2>nul

:: Move Scripts to scripts/
echo Moving script files...
move "add_item.php" "scripts\" 2>nul
move "alter_enum.php" "scripts\" 2>nul
move "auto_fix_session.php" "scripts\" 2>nul
move "backup_script.php" "scripts\" 2>nul
move "backup_session.php" "scripts\" 2>nul
move "clean_import.php" "scripts\" 2>nul
move "clean_import_v2.php" "scripts\" 2>nul
move "diagnose_now.php" "scripts\" 2>nul
move "direct_fix.php" "scripts\" 2>nul
move "dump_schema.php" "scripts\" 2>nul
move "find_items.php" "scripts\" 2>nul
move "fix_cli.php" "scripts\" 2>nul
move "fix_division_data.php" "scripts\" 2>nul
move "fix_session.sql" "scripts\" 2>nul
move "fix_session_cli.php" "scripts\" 2>nul
move "fix_session_org.php" "scripts\" 2>nul
move "fix_tracking_migration.php" "scripts\" 2>nul
move "import_budget_runner.php" "scripts\" 2>nul
move "master_import.php" "scripts\" 2>nul
move "migrate_disbursement.php" "scripts\" 2>nul
move "migrate_now.php" "scripts\" 2>nul
move "read_excel.php" "scripts\" 2>nul
move "run_036.bat" "scripts\" 2>nul
move "run_036_pdo.php" "scripts\" 2>nul
move "run_backup_debug.bat" "scripts\" 2>nul
move "run_fix.bat" "scripts\" 2>nul
move "run_mapping_migration.php" "scripts\" 2>nul
move "run_migration_embedded.php" "scripts\" 2>nul
move "run_migration_php.php" "scripts\" 2>nul
move "run_migration_v3.php" "scripts\" 2>nul
move "run_migration_v4.php" "scripts\" 2>nul
move "run_migration_v5.php" "scripts\" 2>nul
move "run_migration_v6.php" "scripts\" 2>nul
move "run_special_profession_migration.php" "scripts\" 2>nul
move "run_special_profession_migration_log.php" "scripts\" 2>nul
move "run_sql.bat" "scripts\" 2>nul
move "run_sql_18.bat" "scripts\" 2>nul
move "search_expense.php" "scripts\" 2>nul
move "show_schema.php" "scripts\" 2>nul
move "print_schema.php" "scripts\" 2>nul
move "sync_budget_plans.php" "scripts\" 2>nul
move "sync_missing_activities.php" "scripts\" 2>nul
move "sync_v2.php" "scripts\" 2>nul
move "sync_v3.php" "scripts\" 2>nul
move "sync_v4.php" "scripts\" 2>nul
move "sync_v5.php" "scripts\" 2>nul
move "sync_v6.php" "scripts\" 2>nul
move "verify.bat" "scripts\" 2>nul

:: Move Test/Debug files to archives/test/
echo Moving test and debug files...
move "check_activities.php" "archives\test\" 2>nul
move "check_bp_cols.php" "archives\test\" 2>nul
move "check_cols.php" "archives\test\" 2>nul
move "check_columns_budget_trackings.php" "archives\test\" 2>nul
move "check_corruption.php" "archives\test\" 2>nul
move "check_db_hex.php" "archives\test\" 2>nul
move "check_fy.php" "archives\test\" 2>nul
move "check_import.php" "archives\test\" 2>nul
move "check_linkage.php" "archives\test\" 2>nul
move "check_org3_plans.php" "archives\test\" 2>nul
move "check_org_match.php" "archives\test\" 2>nul
move "check_parent.php" "archives\test\" 2>nul
move "check_parent_log.php" "archives\test\" 2>nul
move "check_parent_pdo.php" "archives\test\" 2>nul
move "check_table.php" "archives\test\" 2>nul
move "check_tables.php" "archives\test\" 2>nul
move "debug_budget_data.php" "archives\test\" 2>nul
move "debug_check_parents.php" "archives\test\" 2>nul
move "debug_csv_hex.php" "archives\test\" 2>nul
move "debug_dashboard_data.php" "archives\test\" 2>nul
move "debug_db.php" "archives\test\" 2>nul
move "debug_duplicates.php" "archives\test\" 2>nul
move "debug_encoding.php" "archives\test\" 2>nul
move "debug_org_3.php" "archives\test\" 2>nul
move "debug_record_details.php" "archives\test\" 2>nul
move "debug_session.php" "archives\test\" 2>nul
move "list_expenses.php" "archives\test\" 2>nul
move "run_disbursement_test.php" "archives\test\" 2>nul
move "simple_check.php" "archives\test\" 2>nul
move "test_php.php" "archives\test\" 2>nul
move "test_tree.php" "archives\test\" 2>nul
move "test_write_simple.php" "archives\test\" 2>nul
move "verify_bp.php" "archives\test\" 2>nul
move "verify_disburse_db.php" "archives\test\" 2>nul
move "verify_fix.php" "archives\test\" 2>nul
move "verify_import.php" "archives\test\" 2>nul
move "verify_migration.php" "archives\test\" 2>nul
move "verify_migration_raw.php" "archives\test\" 2>nul
move "verify_ui_logic.php" "archives\test\" 2>nul
move "verify_ui_standalone.php" "archives\test\" 2>nul

:: Move Python files to python/
echo Moving Python files...
move "check_types.py" "python\" 2>nul
move "list_expenses.py" "python\" 2>nul

:: Move log files to project-log-md/
echo Moving log files...
move "verification_result_raw.txt" "project-log-md\" 2>nul

echo.
echo === File Organization Complete ===
echo Please check the directories for moved files.
pause
