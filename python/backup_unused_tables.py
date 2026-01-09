"""
Backup and Remove Unused Database Tables
Exports tables to SQL files, then optionally drops them.

Usage:
    python backup_unused_tables.py          # Backup only
    python backup_unused_tables.py --drop   # Backup and drop
"""

import mysql.connector
import os
import sys
from datetime import datetime
from db_config import get_db_config


class TableBackupManager:
    def __init__(self):
        self.config = get_db_config()
        self.conn = None
        self.cursor = None
        self.timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        self.backup_dir = os.path.join(
            os.path.dirname(os.path.dirname(__file__)),
            "archives", "backup", f"db_cleanup_{self.timestamp}"
        )
        
        # Tables to backup and potentially remove
        # Format: (table_name, reason)
        self.target_tables = [
            ("budget_plans_backup_20260102", "Old backup table"),
            ("budget_monthly_snapshots", "Empty, no active use"),
            ("budget_request_approvals", "Empty, no active use"),
            ("budget_request_items", "Empty, no active use"),
            ("budget_targets", "Empty, no active use"),
            ("disbursement_details", "Empty, replaced by new structure"),
            ("disbursement_headers", "Empty, replaced by disbursement_sessions"),
            ("inspection_zones", "Empty, no active use"),
            ("province_groups", "Empty, no active use"),
            ("province_region_zones", "Empty, no active use"),
            ("province_zones", "Empty, no active use"),
            ("region_zones", "Empty, no active use"),
            ("target_types", "Empty, no active use"),
        ]
    
    def connect(self):
        """Establish database connection"""
        try:
            self.conn = mysql.connector.connect(**self.config)
            self.cursor = self.conn.cursor(dictionary=True)
            print(f"[OK] Connected to database: {self.config['database']}")
        except mysql.connector.Error as err:
            print(f"[FAIL] Database connection failed: {err}")
            raise SystemExit(1)
    
    def close(self):
        """Close database connection"""
        if self.conn:
            self.conn.close()
    
    def get_table_info(self, table_name):
        """Get table row count and structure"""
        try:
            self.cursor.execute(f"SELECT COUNT(*) as cnt FROM `{table_name}`")
            row_count = self.cursor.fetchone()['cnt']
            return {'exists': True, 'row_count': row_count}
        except mysql.connector.Error:
            return {'exists': False, 'row_count': 0}
    
    def export_table_to_sql(self, table_name):
        """Export table structure and data to SQL file"""
        sql_file = os.path.join(self.backup_dir, f"{table_name}.sql")
        
        lines = []
        lines.append(f"-- Backup of table `{table_name}`")
        lines.append(f"-- Exported: {datetime.now().isoformat()}")
        lines.append(f"-- Database: {self.config['database']}")
        lines.append("")
        
        # Get CREATE TABLE statement
        self.cursor.execute(f"SHOW CREATE TABLE `{table_name}`")
        create_result = self.cursor.fetchone()
        create_sql = list(create_result.values())[1]
        
        lines.append(f"DROP TABLE IF EXISTS `{table_name}`;")
        lines.append("")
        lines.append(create_sql + ";")
        lines.append("")
        
        # Get data
        self.cursor.execute(f"SELECT * FROM `{table_name}`")
        rows = self.cursor.fetchall()
        
        if rows:
            # Get column names
            columns = [desc[0] for desc in self.cursor.description]
            col_list = ", ".join([f"`{c}`" for c in columns])
            
            lines.append(f"-- Data: {len(rows)} rows")
            
            for row in rows:
                values = []
                for col in columns:
                    val = row[col]
                    if val is None:
                        values.append("NULL")
                    elif isinstance(val, (int, float)):
                        values.append(str(val))
                    elif isinstance(val, datetime):
                        values.append(f"'{val.strftime('%Y-%m-%d %H:%M:%S')}'")
                    else:
                        # Escape single quotes
                        escaped = str(val).replace("'", "''")
                        values.append(f"'{escaped}'")
                
                val_list = ", ".join(values)
                lines.append(f"INSERT INTO `{table_name}` ({col_list}) VALUES ({val_list});")
        else:
            lines.append("-- No data in table")
        
        # Write to file
        with open(sql_file, 'w', encoding='utf-8') as f:
            f.write("\n".join(lines))
        
        return sql_file, len(rows)
    
    def drop_table(self, table_name):
        """Drop a table from the database"""
        self.cursor.execute(f"DROP TABLE IF EXISTS `{table_name}`")
        self.conn.commit()
    
    def run(self, do_drop=False):
        """Main execution"""
        print("=" * 60)
        print("DATABASE TABLE BACKUP & CLEANUP")
        print("=" * 60)
        print(f"Timestamp: {self.timestamp}")
        print(f"Backup Dir: {self.backup_dir}")
        print(f"Mode: {'BACKUP + DROP' if do_drop else 'BACKUP ONLY'}")
        print("=" * 60)
        
        self.connect()
        
        # Create backup directory
        os.makedirs(self.backup_dir, exist_ok=True)
        print(f"\n[OK] Created backup directory")
        
        # Process each table
        backed_up = []
        skipped = []
        
        print(f"\n[INFO] Processing {len(self.target_tables)} tables...\n")
        
        for table_name, reason in self.target_tables:
            info = self.get_table_info(table_name)
            
            if not info['exists']:
                print(f"  [WARN] {table_name}: Table not found (skipped)")
                skipped.append((table_name, "Not found"))
                continue
            
            # Export
            try:
                sql_file, row_count = self.export_table_to_sql(table_name)
                print(f"  [OK] {table_name}: Backed up ({row_count} rows)")
                backed_up.append((table_name, row_count, sql_file))
            except Exception as e:
                print(f"  [FAIL] {table_name}: Export failed - {e}")
                skipped.append((table_name, str(e)))
                continue
        
        # Summary
        print("\n" + "=" * 60)
        print("BACKUP SUMMARY")
        print("=" * 60)
        print(f"Backed up: {len(backed_up)} tables")
        print(f"Skipped: {len(skipped)} tables")
        print(f"Location: {self.backup_dir}")
        
        # Create manifest
        manifest_path = os.path.join(self.backup_dir, "MANIFEST.md")
        with open(manifest_path, 'w', encoding='utf-8') as f:
            f.write(f"# Database Backup Manifest\n\n")
            f.write(f"**Date:** {datetime.now().isoformat()}\n")
            f.write(f"**Database:** {self.config['database']}\n\n")
            f.write("## Backed Up Tables\n\n")
            f.write("| Table | Rows | File |\n")
            f.write("|-------|------|------|\n")
            for name, rows, path in backed_up:
                f.write(f"| `{name}` | {rows} | {os.path.basename(path)} |\n")
            
            if skipped:
                f.write("\n## Skipped Tables\n\n")
                for name, reason in skipped:
                    f.write(f"- `{name}`: {reason}\n")
        
        print(f"[OK] Manifest saved: {manifest_path}")
        
        # Drop tables if requested
        if do_drop and backed_up:
            print("\n" + "=" * 60)
            print("[WARNING] DROPPING TABLES")
            print("=" * 60)
            
            for table_name, row_count, _ in backed_up:
                try:
                    self.drop_table(table_name)
                    print(f"  [OK] Dropped: {table_name}")
                except Exception as e:
                    print(f"  [FAIL] Failed to drop {table_name}: {e}")
            
            print("\n[SUCCESS] Cleanup complete!")
        elif do_drop:
            print("\n[WARN] No tables to drop (all skipped)")
        else:
            print("\n[INFO] Run with --drop flag to remove backed up tables")
        
        self.close()


def main():
    do_drop = "--drop" in sys.argv
    
    if do_drop:
        print("\n[WARNING] This will DROP tables from the database!")
        print("Tables will be backed up first, but this is irreversible.")
        confirm = input("\nType 'YES' to confirm: ")
        if confirm != "YES":
            print("Aborted.")
            return
    
    manager = TableBackupManager()
    manager.run(do_drop=do_drop)


if __name__ == "__main__":
    main()
