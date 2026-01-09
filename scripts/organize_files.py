#!/usr/bin/env python3
"""
File Organization Script for HR Budget Project
Organizes loose files in the root directory according to folder structure guidelines
"""

import os
import shutil
from pathlib import Path

# Define file categories and their destinations
FILE_MOVES = {
    'docs/': [
        'ADD_ADMIN_COLUMNS.md', 'ADMIN_COLUMNS_SUMMARY.md', 'FIX_CODE_COLUMN_ERROR.md',
        'HANDOFF_SUMMARY.md', 'MANUAL_MIGRATION_STEPS.md', 'README_FIX.md',
        'RECREATE_TABLE_STEPS.md', 'TROUBLESHOOT_FK_ERROR.md'
    ],
    'scripts/': [
        'add_item.php', 'alter_enum.php', 'auto_fix_session.php', 
        'backup_script.php', 'backup_session.php', 'clean_import.php', 'clean_import_v2.php',
        'diagnose_now.php', 'direct_fix.php', 'dump_schema.php', 'find_items.php',
        'fix_cli.php', 'fix_division_data.php', 'fix_session.sql', 'fix_session_cli.php',
        'fix_session_org.php', 'fix_tracking_migration.php', 'import_budget_runner.php',
        'master_import.php', 'migrate_disbursement.php', 'migrate_now.php', 'read_excel.php',
        'run_036.bat', 'run_036_pdo.php', 'run_backup_debug.bat', 'run_fix.bat',
        'run_mapping_migration.php', 'run_migration_embedded.php', 'run_migration_php.php',
        'run_migration_v3.php', 'run_migration_v4.php', 'run_migration_v5.php', 'run_migration_v6.php',
        'run_special_profession_migration.php', 'run_special_profession_migration_log.php',
        'run_sql.bat', 'run_sql_18.bat', 'search_expense.php', 'show_schema.php', 'print_schema.php',
        'sync_budget_plans.php', 'sync_missing_activities.php', 'sync_v2.php', 'sync_v3.php',
        'sync_v4.php', 'sync_v5.php', 'sync_v6.php', 'verify.bat'
    ],
    'archives/test/': [
        'check_activities.php', 'check_bp_cols.php', 'check_cols.php', 'check_columns_budget_trackings.php',
        'check_corruption.php', 'check_db_hex.php', 'check_fy.php', 'check_import.php',
        'check_linkage.php', 'check_org3_plans.php', 'check_org_match.php', 'check_parent.php',
        'check_parent_log.php', 'check_parent_pdo.php', 'check_table.php', 'check_tables.php',
        'debug_budget_data.php', 'debug_check_parents.php', 'debug_csv_hex.php', 'debug_dashboard_data.php',
        'debug_db.php', 'debug_duplicates.php', 'debug_encoding.php', 'debug_org_3.php',
        'debug_record_details.php', 'debug_session.php', 'list_expenses.php', 'run_disbursement_test.php',
        'simple_check.php', 'test_php.php', 'test_tree.php', 'test_write_simple.php',
        'verify_bp.php', 'verify_disburse_db.php', 'verify_fix.php', 'verify_import.php',
        'verify_migration.php', 'verify_migration_raw.php', 'verify_ui_logic.php', 'verify_ui_standalone.php'
    ],
    'python/': ['check_types.py', 'list_expenses.py'],
    'project-log-md/': ['verification_result_raw.txt']
}

def main():
    print("=== Starting File Organization ===\n")
    
    # Get project root
    project_root = Path(r'c:\laragon\www\hr_budget')
    os.chdir(project_root)
    
    moved = 0
    failed = 0
    skipped = 0
    errors = []
    
    for destination, files in FILE_MOVES.items():
        print(f"\nMoving files to {destination}...")
        dest_path = project_root / destination
        
        for filename in files:
            source = project_root / filename
            target = dest_path / filename
            
            if not source.exists():
                print(f"  [SKIP] {filename} (not found)")
                skipped += 1
                continue
            
            try:
                shutil.move(str(source), str(target))
                print(f"  [OK] {filename}")
                moved += 1
            except Exception as e:
                print(f"  [FAIL] {filename}: {e}")
                errors.append(f"{filename}: {e}")
                failed += 1
    
    print("\n=== Summary ===")
    print(f"✓ Files moved: {moved}")
    print(f"✗ Files failed: {failed}")
    print(f"○ Files skipped: {skipped}")
    print(f"Total processed: {moved + failed + skipped}")
    
    if errors:
        print("\nErrors encountered:")
        for error in errors:
            print(f"  - {error}")
    
    print("\n=== File Organization Complete ===")

if __name__ == '__main__':
    main()
