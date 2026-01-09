"""
Fix Column Types for Foreign Key Compatibility
Checks and fixes column types to match referenced columns.

Usage:
    python fix_column_types_for_fk.py
"""

import mysql.connector
from db_config import get_db_config


class ColumnTypeFixer:
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
    
    def get_column_type(self, table, column):
        """Get column type info"""
        query = """
        SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s
        """
        self.cursor.execute(query, [self.config['database'], table, column])
        return self.cursor.fetchone()
    
    def fix_column_type(self, table, column, target_type, nullable=True):
        """Alter column to match target type"""
        null_str = "NULL" if nullable else "NOT NULL"
        sql = f"ALTER TABLE `{table}` MODIFY `{column}` {target_type} {null_str}"
        try:
            self.cursor.execute(sql)
            self.conn.commit()
            return True, None
        except Exception as e:
            return False, str(e)
    
    def analyze_and_fix(self, fk_pairs):
        """Analyze and fix column type mismatches"""
        fixes = []
        
        for src_table, src_col, ref_table, ref_col in fk_pairs:
            src_info = self.get_column_type(src_table, src_col)
            ref_info = self.get_column_type(ref_table, ref_col)
            
            if not src_info:
                print(f"[SKIP] {src_table}.{src_col} - column not found")
                continue
            if not ref_info:
                print(f"[SKIP] {ref_table}.{ref_col} - reference column not found")
                continue
            
            if src_info['COLUMN_TYPE'] != ref_info['COLUMN_TYPE']:
                print(f"[MISMATCH] {src_table}.{src_col} ({src_info['COLUMN_TYPE']}) != {ref_table}.{ref_col} ({ref_info['COLUMN_TYPE']})")
                
                # Try to fix
                nullable = src_info['IS_NULLABLE'] == 'YES'
                success, err = self.fix_column_type(src_table, src_col, ref_info['COLUMN_TYPE'], nullable)
                
                if success:
                    print(f"  [FIXED] Changed to {ref_info['COLUMN_TYPE']}")
                    fixes.append((src_table, src_col, src_info['COLUMN_TYPE'], ref_info['COLUMN_TYPE']))
                else:
                    print(f"  [ERROR] {err}")
            else:
                print(f"[OK] {src_table}.{src_col} matches {ref_table}.{ref_col}")
        
        return fixes


def main():
    print("=" * 60)
    print("FIXING COLUMN TYPES FOR FK COMPATIBILITY")
    print("=" * 60)
    
    # FK pairs that need checking: (source_table, source_col, ref_table, ref_col)
    fk_pairs = [
        ('activity_logs', 'user_id', 'users', 'id'),
        ('budget_allocations', 'plan_id', 'plans', 'id'),
        ('budget_allocations', 'category_id', 'budget_categories', 'id'),
        ('budget_allocations', 'item_id', 'budget_category_items', 'id'),
        ('budget_allocations', 'activity_id', 'activities', 'id'),
        ('budget_allocations', 'organization_id', 'organizations', 'id'),
        ('budget_category_items', 'parent_id', 'budget_category_items', 'id'),
        ('budget_line_items', 'budget_type_id', 'budget_types', 'id'),
        ('budget_line_items', 'plan_id', 'plans', 'id'),
        ('budget_line_items', 'project_id', 'projects', 'id'),
        ('budget_line_items', 'activity_id', 'activities', 'id'),
        ('budget_line_items', 'expense_type_id', 'expense_types', 'id'),
        ('budget_line_items', 'expense_group_id', 'expense_groups', 'id'),
        ('budget_line_items', 'expense_item_id', 'expense_items', 'id'),
        ('budget_line_items', 'province_id', 'provinces', 'id'),
        ('budget_trackings', 'organization_id', 'organizations', 'id'),
        ('budget_trackings', 'budget_category_item_id', 'budget_category_items', 'id'),
        ('organizations', 'parent_id', 'organizations', 'id'),
        ('source_of_truth_mappings', 'organization_id', 'organizations', 'id'),
        ('source_of_truth_mappings', 'plan_id', 'plans', 'id'),
        ('source_of_truth_mappings', 'project_id', 'projects', 'id'),
        ('source_of_truth_mappings', 'activity_id', 'activities', 'id'),
    ]
    
    fixer = ColumnTypeFixer()
    fixer.connect()
    
    fixes = fixer.analyze_and_fix(fk_pairs)
    
    print("\n" + "=" * 60)
    print("SUMMARY")
    print("=" * 60)
    print(f"Total pairs checked: {len(fk_pairs)}")
    print(f"Fixes applied: {len(fixes)}")
    
    if fixes:
        print("\nFixed columns:")
        for table, col, old_type, new_type in fixes:
            print(f"  - {table}.{col}: {old_type} -> {new_type}")
    
    fixer.close()
    print("\n[OK] Done. Now re-run the FK migration.")


if __name__ == "__main__":
    main()
