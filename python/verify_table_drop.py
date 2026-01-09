"""
Pre-Drop Verification Script
Checks if tables are safe to drop by:
1. Verifying backup files exist and are readable
2. Checking for foreign key dependencies
3. Scanning codebase for active references
"""

import mysql.connector
import os
import glob
from collections import defaultdict
from db_config import get_db_config


class TableDropVerifier:
    def __init__(self, backup_dir):
        self.backup_dir = backup_dir
        self.config = get_db_config()
        self.conn = None
        self.cursor = None
        
    def connect(self):
        self.conn = mysql.connector.connect(**self.config)
        self.cursor = self.cursor = self.conn.cursor(dictionary=True)
        
    def close(self):
        if self.conn:
            self.conn.close()
    
    def verify_backup_files(self, tables):
        """Check backup files exist and are valid"""
        print("\n" + "=" * 60)
        print("1. VERIFYING BACKUP FILES")
        print("=" * 60)
        
        all_valid = True
        for table in tables:
            sql_file = os.path.join(self.backup_dir, f"{table}.sql")
            if os.path.exists(sql_file):
                size = os.path.getsize(sql_file)
                if size > 0:
                    print(f"  [OK] {table}.sql ({size:,} bytes)")
                else:
                    print(f"  [FAIL] {table}.sql (EMPTY FILE!)")
                    all_valid = False
            else:
                print(f"  [FAIL] {table}.sql (NOT FOUND!)")
                all_valid = False
        
        return all_valid
    
    def check_foreign_key_dependencies(self, tables):
        """Check if any OTHER tables reference these tables"""
        print("\n" + "=" * 60)
        print("2. CHECKING FOREIGN KEY DEPENDENCIES")
        print("=" * 60)
        
        dependencies = []
        
        for table in tables:
            query = """
            SELECT 
                TABLE_NAME,
                COLUMN_NAME,
                CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_SCHEMA = %s 
              AND REFERENCED_TABLE_NAME = %s
              AND TABLE_NAME NOT IN (%s)
            """
            
            # Exclude tables in our drop list from dependency check
            placeholders = ','.join(['%s'] * len(tables))
            query = query.replace('%s)', f"{placeholders})")
            
            params = [self.config['database'], table] + tables
            self.cursor.execute(query, params)
            refs = self.cursor.fetchall()
            
            if refs:
                print(f"\n  [WARN] {table} is referenced by:")
                for ref in refs:
                    print(f"      - {ref['TABLE_NAME']}.{ref['COLUMN_NAME']} ({ref['CONSTRAINT_NAME']})")
                    dependencies.append({
                        'target_table': table,
                        'referencing_table': ref['TABLE_NAME'],
                        'column': ref['COLUMN_NAME']
                    })
            else:
                print(f"  [OK] {table}: No external dependencies")
        
        return dependencies
    
    def scan_codebase_references(self, tables, project_root):
        """Scan PHP files for table references"""
        print("\n" + "=" * 60)
        print("3. SCANNING CODEBASE FOR REFERENCES")
        print("=" * 60)
        
        references = defaultdict(list)
        
        # Scan specific critical directories
        scan_dirs = [
            'src/Controllers',
            'src/Models',
            'src/Core',
        ]
        
        for scan_dir in scan_dirs:
            full_path = os.path.join(project_root, scan_dir)
            if not os.path.exists(full_path):
                continue
                
            php_files = glob.glob(os.path.join(full_path, '**/*.php'), recursive=True)
            
            for filepath in php_files:
                try:
                    with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
                        content = f.read()
                except:
                    continue
                
                for table in tables:
                    # Simple check for table name in content
                    if table in content:
                        rel_path = os.path.relpath(filepath, project_root)
                        references[table].append(rel_path)
        
        has_refs = False
        for table in tables:
            if table in references and references[table]:
                has_refs = True
                print(f"\n  [WARN] '{table}' found in {len(references[table])} files:")
                for file in references[table][:5]:  # Show first 5
                    print(f"      - {file}")
                if len(references[table]) > 5:
                    print(f"      ... and {len(references[table]) - 5} more")
            else:
                print(f"  [OK] {table}: No references in core code")
        
        return references
    
    def generate_report(self, tables, backup_valid, dependencies, code_refs):
        """Generate final safety report"""
        print("\n" + "=" * 60)
        print("SAFETY VERIFICATION REPORT")
        print("=" * 60)
        
        is_safe = True
        
        # Check 1: Backups
        if backup_valid:
            print("\n[OK] Backup Files: VALID")
        else:
            print("\n[FAIL] Backup Files: INVALID - DO NOT DROP!")
            is_safe = False
        
        # Check 2: Foreign Keys
        if dependencies:
            print(f"\n[WARN] Foreign Key Dependencies: {len(dependencies)} found")
            print("   These constraints must be handled first!")
            is_safe = False
        else:
            print("\n[OK] Foreign Key Dependencies: NONE")
        
        # Check 3: Code References
        tables_with_refs = [t for t in tables if t in code_refs and code_refs[t]]
        if tables_with_refs:
            print(f"\n[WARN] Code References: {len(tables_with_refs)} tables referenced")
            print(f"   Tables: {', '.join(tables_with_refs)}")
            print("   Review these references before dropping!")
            # This is a warning, not a blocker (might be comments/docs)
        else:
            print("\n[OK] Code References: NONE in core files")
        
        # Final recommendation
        print("\n" + "=" * 60)
        if is_safe:
            print("[SAFE] SAFE TO DROP")
            print("All checks passed. Tables can be safely removed.")
        else:
            print("[UNSAFE] NOT SAFE TO DROP")
            print("Fix the issues above before proceeding!")
        print("=" * 60)
        
        return is_safe


def main():
    # Configuration
    backup_dir = "C:\\laragon\\www\\hr_budget\\archives\\backup\\db_cleanup_20260104_164402"
    project_root = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    
    tables_to_verify = [
        "budget_plans_backup_20260102",
        "budget_monthly_snapshots",
        "budget_request_approvals",
        "budget_request_items",
        "budget_targets",
        "disbursement_details",
        "disbursement_headers",
        "inspection_zones",
        "province_groups",
        "province_region_zones",
        "province_zones",
        "region_zones",
        "target_types",
    ]
    
    print("=" * 60)
    print("TABLE DROP VERIFICATION")
    print("=" * 60)
    print(f"Verifying {len(tables_to_verify)} tables...")
    
    verifier = TableDropVerifier(backup_dir)
    verifier.connect()
    
    # Run checks
    backup_valid = verifier.verify_backup_files(tables_to_verify)
    dependencies = verifier.check_foreign_key_dependencies(tables_to_verify)
    code_refs = verifier.scan_codebase_references(tables_to_verify, project_root)
    
    # Generate report
    is_safe = verifier.generate_report(tables_to_verify, backup_valid, dependencies, code_refs)
    
    verifier.close()
    
    return 0 if is_safe else 1


if __name__ == "__main__":
    exit(main())
