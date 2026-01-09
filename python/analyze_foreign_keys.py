"""
Foreign Key Analysis Script
Analyzes the database for missing foreign key relationships.

Usage:
    python analyze_foreign_keys.py
"""

import mysql.connector
import re
from collections import defaultdict
from db_config import get_db_config


class ForeignKeyAnalyzer:
    def __init__(self):
        self.config = get_db_config()
        self.conn = None
        self.cursor = None
        
    def connect(self):
        self.conn = mysql.connector.connect(**self.config)
        self.cursor = self.conn.cursor(dictionary=True)
        print(f"[OK] Connected to database: {self.config['database']}")
        
    def close(self):
        if self.conn:
            self.conn.close()
    
    def get_all_tables(self):
        """Get all tables in the database"""
        self.cursor.execute("SHOW TABLES")
        return [list(row.values())[0] for row in self.cursor.fetchall()]
    
    def get_existing_fks(self):
        """Get all existing foreign key constraints"""
        query = """
        SELECT
            tc.TABLE_NAME,
            tc.CONSTRAINT_NAME,
            kcu.COLUMN_NAME,
            kcu.REFERENCED_TABLE_NAME,
            kcu.REFERENCED_COLUMN_NAME
        FROM information_schema.TABLE_CONSTRAINTS tc
        JOIN information_schema.KEY_COLUMN_USAGE kcu
            ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
            AND tc.TABLE_SCHEMA = kcu.TABLE_SCHEMA
        WHERE tc.CONSTRAINT_TYPE = 'FOREIGN KEY'
          AND tc.TABLE_SCHEMA = %s
        ORDER BY tc.TABLE_NAME, kcu.COLUMN_NAME
        """
        self.cursor.execute(query, [self.config['database']])
        return self.cursor.fetchall()
    
    def get_table_columns(self, table_name):
        """Get all columns for a table"""
        self.cursor.execute(f"DESCRIBE `{table_name}`")
        return self.cursor.fetchall()
    
    def analyze_potential_fks(self):
        """Analyze columns that look like FKs but have no constraint"""
        tables = self.get_all_tables()
        existing_fks = self.get_existing_fks()
        
        # Build set of existing FK columns
        fk_set = set()
        for fk in existing_fks:
            fk_set.add((fk['TABLE_NAME'], fk['COLUMN_NAME']))
        
        # Common FK patterns
        fk_patterns = [
            (r'(.+)_id$', lambda m: m.group(1) + 's'),  # user_id -> users
            (r'(.+)_id$', lambda m: m.group(1)),        # plan_id -> plan (singular)
        ]
        
        # Analyze each table
        missing_fks = []
        
        for table in tables:
            columns = self.get_table_columns(table)
            
            for col in columns:
                col_name = col['Field']
                
                # Skip if already has FK
                if (table, col_name) in fk_set:
                    continue
                
                # Check if looks like FK
                if col_name.endswith('_id') and col_name != 'id':
                    # Try to find referenced table
                    base_name = col_name[:-3]  # Remove '_id'
                    
                    # Check possible table names
                    possible_tables = [
                        base_name + 's',           # user_id -> users
                        base_name,                 # plan_id -> plan (if exists)
                        base_name + 'es',          # status_id -> statuses
                        base_name.replace('_', ''), # budget_category_id -> budgetcategories
                    ]
                    
                    # Special mappings
                    special_maps = {
                        'organization_id': 'organizations',
                        'org_id': 'organizations',
                        'plan_id': 'plans',
                        'project_id': 'projects',
                        'activity_id': 'activities',
                        'fiscal_year': 'fiscal_years',
                        'user_id': 'users',
                        'created_by': 'users',
                        'updated_by': 'users',
                        'parent_id': table,  # Self-reference
                        'category_id': 'budget_categories',
                        'item_id': 'budget_category_items',
                        'expense_type_id': 'expense_types',
                        'expense_group_id': 'expense_groups',
                        'expense_item_id': 'expense_items',
                        'session_id': 'disbursement_sessions',
                        'record_id': 'disbursement_records',
                        'fund_source_id': 'fund_sources',
                    }
                    
                    if col_name in special_maps:
                        possible_tables.insert(0, special_maps[col_name])
                    
                    # Find matching table
                    ref_table = None
                    for pt in possible_tables:
                        if pt in tables:
                            ref_table = pt
                            break
                    
                    if ref_table:
                        missing_fks.append({
                            'table': table,
                            'column': col_name,
                            'ref_table': ref_table,
                            'ref_column': 'id',
                            'col_type': col['Type'],
                            'nullable': col['Null'] == 'YES'
                        })
        
        return missing_fks
    
    def check_data_integrity(self, table, column, ref_table, ref_column):
        """Check if FK would violate data integrity"""
        query = f"""
        SELECT COUNT(*) as orphan_count
        FROM `{table}` t
        LEFT JOIN `{ref_table}` r ON t.`{column}` = r.`{ref_column}`
        WHERE t.`{column}` IS NOT NULL AND r.`{ref_column}` IS NULL
        """
        self.cursor.execute(query)
        result = self.cursor.fetchone()
        return result['orphan_count']
    
    def generate_migration(self, missing_fks, check_integrity=True):
        """Generate SQL migration for missing FKs"""
        lines = []
        lines.append("-- Migration: Add Missing Foreign Keys")
        lines.append(f"-- Generated: {__import__('datetime').datetime.now().isoformat()}")
        lines.append("-- Database: " + self.config['database'])
        lines.append("")
        lines.append("SET FOREIGN_KEY_CHECKS = 0;")
        lines.append("")
        
        safe_fks = []
        unsafe_fks = []
        
        for fk in missing_fks:
            if check_integrity:
                orphans = self.check_data_integrity(
                    fk['table'], fk['column'], 
                    fk['ref_table'], fk['ref_column']
                )
                if orphans > 0:
                    unsafe_fks.append({**fk, 'orphans': orphans})
                    continue
            
            safe_fks.append(fk)
        
        # Generate safe FK statements
        if safe_fks:
            lines.append("-- Safe Foreign Keys (no orphan records)")
            lines.append("")
            
            for fk in safe_fks:
                constraint_name = f"fk_{fk['table']}_{fk['column']}"
                on_delete = "SET NULL" if fk['nullable'] else "CASCADE"
                
                lines.append(f"-- {fk['table']}.{fk['column']} -> {fk['ref_table']}.{fk['ref_column']}")
                lines.append(f"ALTER TABLE `{fk['table']}`")
                lines.append(f"  ADD CONSTRAINT `{constraint_name}`")
                lines.append(f"  FOREIGN KEY (`{fk['column']}`)")
                lines.append(f"  REFERENCES `{fk['ref_table']}` (`{fk['ref_column']}`)")
                lines.append(f"  ON DELETE {on_delete} ON UPDATE CASCADE;")
                lines.append("")
        
        # Document unsafe FKs
        if unsafe_fks:
            lines.append("")
            lines.append("-- ================================================")
            lines.append("-- UNSAFE Foreign Keys (orphan records exist)")
            lines.append("-- Fix data first before adding these constraints")
            lines.append("-- ================================================")
            lines.append("")
            
            for fk in unsafe_fks:
                lines.append(f"-- {fk['table']}.{fk['column']} -> {fk['ref_table']}.{fk['ref_column']}")
                lines.append(f"-- ORPHAN RECORDS: {fk['orphans']}")
                lines.append(f"-- Fix query:")
                if fk['nullable']:
                    lines.append(f"-- UPDATE `{fk['table']}` t LEFT JOIN `{fk['ref_table']}` r ON t.`{fk['column']}` = r.`{fk['ref_column']}` SET t.`{fk['column']}` = NULL WHERE r.`{fk['ref_column']}` IS NULL;")
                else:
                    lines.append(f"-- DELETE FROM `{fk['table']}` WHERE `{fk['column']}` NOT IN (SELECT `{fk['ref_column']}` FROM `{fk['ref_table']}`);")
                lines.append("")
        
        lines.append("SET FOREIGN_KEY_CHECKS = 1;")
        
        return "\n".join(lines), safe_fks, unsafe_fks
    
    def generate_report(self, existing_fks, missing_fks, safe_count, unsafe_count):
        """Generate analysis report"""
        lines = []
        lines.append("# Foreign Key Analysis Report")
        lines.append(f"\n**Generated:** {__import__('datetime').datetime.now().isoformat()}")
        lines.append(f"**Database:** {self.config['database']}")
        
        lines.append("\n## Summary")
        lines.append(f"- **Existing FKs:** {len(existing_fks)}")
        lines.append(f"- **Missing FKs (detected):** {len(missing_fks)}")
        lines.append(f"  - Safe to add: {safe_count}")
        lines.append(f"  - Need data fix: {unsafe_count}")
        
        lines.append("\n## Existing Foreign Keys")
        lines.append("\n| Table | Column | References |")
        lines.append("|-------|--------|------------|")
        for fk in existing_fks:
            lines.append(f"| `{fk['TABLE_NAME']}` | `{fk['COLUMN_NAME']}` | `{fk['REFERENCED_TABLE_NAME']}.{fk['REFERENCED_COLUMN_NAME']}` |")
        
        lines.append("\n## Missing Foreign Keys (Recommended)")
        lines.append("\n| Table | Column | Should Reference | Status |")
        lines.append("|-------|--------|------------------|--------|")
        for fk in missing_fks:
            status = "Safe" if fk.get('orphans', 0) == 0 else f"Orphans: {fk.get('orphans', '?')}"
            lines.append(f"| `{fk['table']}` | `{fk['column']}` | `{fk['ref_table']}.{fk['ref_column']}` | {status} |")
        
        return "\n".join(lines)


def main():
    print("=" * 60)
    print("FOREIGN KEY ANALYSIS")
    print("=" * 60)
    
    analyzer = ForeignKeyAnalyzer()
    analyzer.connect()
    
    # Get existing FKs
    print("\n[INFO] Analyzing existing foreign keys...")
    existing_fks = analyzer.get_existing_fks()
    print(f"[OK] Found {len(existing_fks)} existing foreign keys")
    
    # Find missing FKs
    print("\n[INFO] Analyzing potential missing foreign keys...")
    missing_fks = analyzer.analyze_potential_fks()
    print(f"[OK] Found {len(missing_fks)} potential missing foreign keys")
    
    # Generate migration
    print("\n[INFO] Generating migration script...")
    migration_sql, safe_fks, unsafe_fks = analyzer.generate_migration(missing_fks)
    
    # Save migration
    import os
    script_dir = os.path.dirname(__file__)
    
    migration_path = os.path.join(script_dir, 'add_missing_fks.sql')
    with open(migration_path, 'w', encoding='utf-8') as f:
        f.write(migration_sql)
    print(f"[OK] Migration saved: {migration_path}")
    
    # Generate report
    for fk in missing_fks:
        fk['orphans'] = analyzer.check_data_integrity(
            fk['table'], fk['column'],
            fk['ref_table'], fk['ref_column']
        )
    
    report = analyzer.generate_report(existing_fks, missing_fks, len(safe_fks), len(unsafe_fks))
    report_path = os.path.join(script_dir, 'foreign_key_report.md')
    with open(report_path, 'w', encoding='utf-8') as f:
        f.write(report)
    print(f"[OK] Report saved: {report_path}")
    
    # Summary
    print("\n" + "=" * 60)
    print("SUMMARY")
    print("=" * 60)
    print(f"Existing FKs:     {len(existing_fks)}")
    print(f"Missing FKs:      {len(missing_fks)}")
    print(f"  - Safe to add:  {len(safe_fks)}")
    print(f"  - Need fix:     {len(unsafe_fks)}")
    
    analyzer.close()


if __name__ == "__main__":
    main()
