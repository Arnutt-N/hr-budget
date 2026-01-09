"""
Database Schema Analyzer for HR Budget Project
Analyzes database structure and query patterns in the codebase.

Usage:
    python analyze_db_schema.py
    
Output:
    - Prints comprehensive schema analysis
    - Saves detailed report to db_schema_report.md
"""

import mysql.connector
import os
import re
import glob
from collections import defaultdict
from db_config import get_db_config


class DatabaseAnalyzer:
    def __init__(self):
        self.config = get_db_config()
        self.conn = None
        self.cursor = None
        self.tables = {}
        self.foreign_keys = []
        self.indexes = {}
        
    def connect(self):
        """Establish database connection"""
        try:
            self.conn = mysql.connector.connect(**self.config)
            self.cursor = self.conn.cursor(dictionary=True)
            print(f"✓ Connected to database: {self.config['database']}")
        except mysql.connector.Error as err:
            print(f"❌ Database connection failed!")
            print(f"   Error: {err}")
            print(f"\n   Please check:")
            print(f"   1. Is Laragon running and MySQL started?")
            print(f"   2. Check .env file for correct DB_HOST, DB_PORT")
            print(f"   3. Current config: {self.config['host']}:{self.config['port']}")
            raise SystemExit(1)
        
    def close(self):
        """Close database connection"""
        if self.conn:
            self.conn.close()
            print("✓ Connection closed")
    
    def get_all_tables(self):
        """Get list of all tables in database"""
        self.cursor.execute("SHOW TABLES")
        tables = [list(row.values())[0] for row in self.cursor.fetchall()]
        return tables
    
    def get_table_structure(self, table_name):
        """Get detailed structure of a table"""
        self.cursor.execute(f"DESCRIBE `{table_name}`")
        columns = self.cursor.fetchall()
        
        # Get row count
        self.cursor.execute(f"SELECT COUNT(*) as cnt FROM `{table_name}`")
        row_count = self.cursor.fetchone()['cnt']
        
        # Get create table statement for more details
        self.cursor.execute(f"SHOW CREATE TABLE `{table_name}`")
        create_stmt = self.cursor.fetchone()
        create_sql = list(create_stmt.values())[1] if create_stmt else ""
        
        return {
            'columns': columns,
            'row_count': row_count,
            'create_sql': create_sql
        }
    
    def get_foreign_keys(self, table_name):
        """Extract foreign keys from a table"""
        query = """
        SELECT 
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME,
            CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = %s 
          AND TABLE_NAME = %s 
          AND REFERENCED_TABLE_NAME IS NOT NULL
        """
        self.cursor.execute(query, (self.config['database'], table_name))
        return self.cursor.fetchall()
    
    def get_indexes(self, table_name):
        """Get indexes for a table"""
        self.cursor.execute(f"SHOW INDEX FROM `{table_name}`")
        return self.cursor.fetchall()
    
    def analyze_all_tables(self):
        """Analyze all tables in the database"""
        tables = self.get_all_tables()
        print(f"\n📊 Found {len(tables)} tables\n")
        
        for table in tables:
            structure = self.get_table_structure(table)
            fks = self.get_foreign_keys(table)
            indexes = self.get_indexes(table)
            
            self.tables[table] = {
                'columns': structure['columns'],
                'row_count': structure['row_count'],
                'create_sql': structure['create_sql'],
                'foreign_keys': fks,
                'indexes': indexes
            }
            
            self.foreign_keys.extend([
                {'table': table, **fk} for fk in fks
            ])
        
        return self.tables
    
    def print_summary(self):
        """Print a summary of the database structure"""
        print("=" * 60)
        print("DATABASE SCHEMA SUMMARY")
        print("=" * 60)
        
        # Group tables by category based on naming patterns
        categories = defaultdict(list)
        for table in sorted(self.tables.keys()):
            prefix = table.split('_')[0] if '_' in table else 'other'
            categories[prefix].append(table)
        
        for category, tables in sorted(categories.items()):
            print(f"\n📁 {category.upper()} ({len(tables)} tables)")
            for table in tables:
                info = self.tables[table]
                col_count = len(info['columns'])
                row_count = info['row_count']
                fk_count = len(info['foreign_keys'])
                print(f"   ├─ {table}: {col_count} cols, {row_count:,} rows, {fk_count} FKs")
    
    def generate_markdown_report(self):
        """Generate a detailed markdown report"""
        lines = []
        lines.append("# Database Schema Report")
        lines.append(f"\n**Database:** `{self.config['database']}`")
        lines.append(f"**Total Tables:** {len(self.tables)}")
        lines.append(f"**Generated:** {__import__('datetime').datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        
        # Table of Contents
        lines.append("\n## Table of Contents\n")
        for i, table in enumerate(sorted(self.tables.keys()), 1):
            lines.append(f"{i}. [{table}](#{table})")
        
        # Entity Relationship Summary
        lines.append("\n## Relationships Overview\n")
        lines.append("```mermaid")
        lines.append("erDiagram")
        
        # Generate ER diagram relationships
        for fk in self.foreign_keys:
            lines.append(f"    {fk['REFERENCED_TABLE_NAME']} ||--o{{ {fk['table']} : has")
        
        lines.append("```")
        
        # Detailed Table Documentation
        lines.append("\n## Table Details\n")
        
        for table in sorted(self.tables.keys()):
            info = self.tables[table]
            lines.append(f"\n### {table}")
            lines.append(f"**Rows:** {info['row_count']:,}")
            
            # Columns table
            lines.append("\n| Column | Type | Null | Key | Default |")
            lines.append("|--------|------|------|-----|---------|")
            
            for col in info['columns']:
                null = "YES" if col['Null'] == 'YES' else "NO"
                key = col['Key'] or "-"
                default = col['Default'] if col['Default'] is not None else "NULL"
                lines.append(f"| `{col['Field']}` | {col['Type']} | {null} | {key} | {default} |")
            
            # Foreign Keys
            if info['foreign_keys']:
                lines.append("\n**Foreign Keys:**")
                for fk in info['foreign_keys']:
                    lines.append(f"- `{fk['COLUMN_NAME']}` → `{fk['REFERENCED_TABLE_NAME']}.{fk['REFERENCED_COLUMN_NAME']}`")
        
        return "\n".join(lines)


class CodebaseQueryAnalyzer:
    """Analyze SQL query patterns in PHP codebase"""
    
    def __init__(self, project_root):
        self.project_root = project_root
        self.queries = []
        self.table_usage = defaultdict(lambda: {'select': 0, 'insert': 0, 'update': 0, 'delete': 0})
    
    def scan_php_files(self):
        """Scan all PHP files for SQL queries"""
        php_files = []
        for pattern in ['**/*.php']:
            php_files.extend(glob.glob(os.path.join(self.project_root, pattern), recursive=True))
        
        print(f"\n🔍 Scanning {len(php_files)} PHP files for SQL queries...")
        
        for filepath in php_files:
            self._analyze_file(filepath)
        
        return self.queries
    
    def _analyze_file(self, filepath):
        """Analyze a single PHP file for SQL patterns"""
        try:
            with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
        except Exception as e:
            return
        
        # Patterns to find SQL queries
        patterns = [
            # Direct SQL strings
            r'(?:SELECT|INSERT|UPDATE|DELETE)\s+.*?(?:FROM|INTO|SET)\s+[`\']?(\w+)[`\']?',
            # Query builder patterns
            r'->table\([\'"](\w+)[\'"]\)',
            r'->from\([\'"](\w+)[\'"]\)',
            r'::query\([\'"]([^"\']+)[\'"]',
            # Model patterns (table names)
            r'protected\s+static\s+string\s+\$table\s*=\s*[\'"](\w+)[\'"]',
        ]
        
        rel_path = os.path.relpath(filepath, self.project_root)
        
        for pattern in patterns:
            matches = re.findall(pattern, content, re.IGNORECASE | re.DOTALL)
            for match in matches:
                if isinstance(match, tuple):
                    match = match[0]
                
                # Skip non-table names
                if match.lower() in ['select', 'from', 'where', 'and', 'or']:
                    continue
                
                self.queries.append({
                    'file': rel_path,
                    'table': match
                })
                
                # Determine query type
                if 'SELECT' in content.upper():
                    self.table_usage[match]['select'] += 1
                if 'INSERT' in content.upper():
                    self.table_usage[match]['insert'] += 1
                if 'UPDATE' in content.upper():
                    self.table_usage[match]['update'] += 1
                if 'DELETE' in content.upper():
                    self.table_usage[match]['delete'] += 1
    
    def print_summary(self):
        """Print query analysis summary"""
        print("\n" + "=" * 60)
        print("CODEBASE QUERY ANALYSIS")
        print("=" * 60)
        
        print(f"\n📝 Found queries referencing {len(self.table_usage)} tables")
        
        # Sort by total usage
        sorted_usage = sorted(
            self.table_usage.items(),
            key=lambda x: sum(x[1].values()),
            reverse=True
        )
        
        print("\n| Table | SELECT | INSERT | UPDATE | DELETE | Total |")
        print("|-------|--------|--------|--------|--------|-------|")
        
        for table, usage in sorted_usage[:20]:  # Top 20
            total = sum(usage.values())
            print(f"| {table} | {usage['select']} | {usage['insert']} | {usage['update']} | {usage['delete']} | {total} |")
    
    def generate_markdown(self):
        """Generate markdown section for query analysis"""
        lines = []
        lines.append("\n## Codebase Query Patterns\n")
        
        sorted_usage = sorted(
            self.table_usage.items(),
            key=lambda x: sum(x[1].values()),
            reverse=True
        )
        
        lines.append("| Table | SELECT | INSERT | UPDATE | DELETE | Total |")
        lines.append("|-------|--------|--------|--------|--------|-------|")
        
        for table, usage in sorted_usage:
            total = sum(usage.values())
            if total > 0:
                lines.append(f"| `{table}` | {usage['select']} | {usage['insert']} | {usage['update']} | {usage['delete']} | {total} |")
        
        return "\n".join(lines)


def main():
    print("=" * 60)
    print("HR BUDGET - DATABASE SCHEMA ANALYZER")
    print("=" * 60)
    
    # Database Analysis
    db_analyzer = DatabaseAnalyzer()
    db_analyzer.connect()
    db_analyzer.analyze_all_tables()
    db_analyzer.print_summary()
    
    # Codebase Query Analysis
    project_root = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    code_analyzer = CodebaseQueryAnalyzer(project_root)
    code_analyzer.scan_php_files()
    code_analyzer.print_summary()
    
    # Generate combined report
    report = db_analyzer.generate_markdown_report()
    report += code_analyzer.generate_markdown()
    
    # Save report
    report_path = os.path.join(os.path.dirname(__file__), 'db_schema_report.md')
    with open(report_path, 'w', encoding='utf-8') as f:
        f.write(report)
    
    print(f"\n✅ Report saved to: {report_path}")
    
    db_analyzer.close()


if __name__ == "__main__":
    main()
