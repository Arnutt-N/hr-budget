"""
Budget Plans Reference Auditor
Finds all references to 'budget_plans' in the codebase and categorizes them.

Usage:
    python audit_budget_plans_refs.py
"""

import os
import re
from collections import defaultdict


class BudgetPlansAuditor:
    def __init__(self, project_root):
        self.project_root = project_root
        self.references = defaultdict(list)
        self.file_stats = defaultdict(int)
        
    def scan_file(self, filepath):
        """Scan a single file for budget_plans references"""
        try:
            with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
        except:
            return []
        
        refs = []
        lines = content.split('\n')
        
        for line_num, line in enumerate(lines, 1):
            if 'budget_plans' in line.lower():
                # Get context (trim whitespace)
                context = line.strip()
                if len(context) > 100:
                    context = context[:97] + '...'
                
                refs.append({
                    'line': line_num,
                    'context': context,
                    'type': self._classify_reference(line)
                })
        
        return refs
    
    def _classify_reference(self, line):
        """Classify the type of reference"""
        line_lower = line.lower()
        
        if 'from budget_plans' in line_lower or 'join budget_plans' in line_lower:
            return 'SQL_JOIN'
        elif 'select' in line_lower and 'budget_plans' in line_lower:
            return 'SQL_SELECT'
        elif 'insert' in line_lower and 'budget_plans' in line_lower:
            return 'SQL_INSERT'
        elif 'update' in line_lower and 'budget_plans' in line_lower:
            return 'SQL_UPDATE'
        elif 'delete' in line_lower and 'budget_plans' in line_lower:
            return 'SQL_DELETE'
        elif '$table' in line or 'protected' in line and 'table' in line:
            return 'MODEL_TABLE'
        elif 'class' in line and 'budgetplan' in line_lower:
            return 'CLASS_NAME'
        else:
            return 'OTHER'
    
    def scan_directory(self, directory, extensions=['.php', '.sql']):
        """Recursively scan directory for references"""
        for root, dirs, files in os.walk(directory):
            # Skip certain directories
            skip_dirs = ['vendor', 'node_modules', 'archives', '.git', 'venv', '__pycache__']
            dirs[:] = [d for d in dirs if d not in skip_dirs]
            
            for file in files:
                if any(file.endswith(ext) for ext in extensions):
                    filepath = os.path.join(root, file)
                    refs = self.scan_file(filepath)
                    
                    if refs:
                        rel_path = os.path.relpath(filepath, self.project_root)
                        self.references[rel_path] = refs
                        self.file_stats[self._get_file_category(rel_path)] += 1
    
    def _get_file_category(self, filepath):
        """Categorize file by path"""
        if 'src/Models' in filepath or 'src\\Models' in filepath:
            return 'Models'
        elif 'src/Controllers' in filepath or 'src\\Controllers' in filepath:
            return 'Controllers'
        elif 'resources/views' in filepath or 'resources\\views' in filepath:
            return 'Views'
        elif 'src/Core' in filepath or 'src\\Core' in filepath:
            return 'Core'
        elif '.sql' in filepath:
            return 'SQL_Files'
        else:
            return 'Other'
    
    def generate_report(self):
        """Generate detailed audit report"""
        lines = []
        lines.append("# Budget Plans Reference Audit Report")
        lines.append(f"\n**Generated:** {__import__('datetime').datetime.now().isoformat()}")
        lines.append(f"**Project:** {self.project_root}")
        
        # Summary
        total_files = len(self.references)
        total_refs = sum(len(refs) for refs in self.references.values())
        
        lines.append("\n## Summary\n")
        lines.append(f"- **Total Files with References:** {total_files}")
        lines.append(f"- **Total References:** {total_refs}")
        
        # By Category
        lines.append("\n### Files by Category\n")
        lines.append("| Category | Files |")
        lines.append("|----------|-------|")
        for category, count in sorted(self.file_stats.items()):
            lines.append(f"| {category} | {count} |")
        
        # By Reference Type
        ref_types = defaultdict(int)
        for refs in self.references.values():
            for ref in refs:
                ref_types[ref['type']] += 1
        
        lines.append("\n### References by Type\n")
        lines.append("| Type | Count |")
        lines.append("|------|-------|")
        for ref_type, count in sorted(ref_types.items(), key=lambda x: x[1], reverse=True):
            lines.append(f"| {ref_type} | {count} |")
        
        # Detailed File List
        lines.append("\n## Detailed References\n")
        
        for category in ['Models', 'Controllers', 'Views', 'Core', 'SQL_Files', 'Other']:
            category_files = [f for f in self.references.keys() 
                            if self._get_file_category(f) == category]
            
            if not category_files:
                continue
            
            lines.append(f"\n### {category}\n")
            
            for filepath in sorted(category_files):
                refs = self.references[filepath]
                lines.append(f"\n#### {filepath}")
                lines.append(f"**References:** {len(refs)}\n")
                
                # Group by type
                by_type = defaultdict(list)
                for ref in refs:
                    by_type[ref['type']].append(ref)
                
                for ref_type, type_refs in sorted(by_type.items()):
                    lines.append(f"\n**{ref_type}:**")
                    for ref in type_refs[:5]:  # Show first 5
                        lines.append(f"- Line {ref['line']}: `{ref['context']}`")
                    if len(type_refs) > 5:
                        lines.append(f"- ... and {len(type_refs) - 5} more")
        
        # Migration Strategy
        lines.append("\n## Recommended Migration Strategy\n")
        lines.append("1. **Models:** Update `protected static $table = 'budget_plans'` to `'plans'`")
        lines.append("2. **SQL Queries:** Replace `budget_plans` with `plans` in all queries")
        lines.append("3. **Class Names:** Consider keeping class names as `BudgetPlan` for backward compatibility")
        lines.append("4. **Foreign Keys:** Verify FK constraints are updated")
        
        return "\n".join(lines)
    
    def print_summary(self):
        """Print quick summary to console"""
        total_files = len(self.references)
        total_refs = sum(len(refs) for refs in self.references.values())
        
        print("=" * 60)
        print("BUDGET PLANS REFERENCE AUDIT")
        print("=" * 60)
        print(f"\n[INFO] Found {total_refs} references in {total_files} files\n")
        
        print("By Category:")
        for category, count in sorted(self.file_stats.items()):
            print(f"  - {category}: {count} files")


def main():
    project_root = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    
    print("=" * 60)
    print("AUDITING BUDGET_PLANS REFERENCES")
    print("=" * 60)
    
    auditor = BudgetPlansAuditor(project_root)
    
    # Scan main directories
    scan_dirs = [
        'src',
        'resources',
        'database',
    ]
    
    for dir_name in scan_dirs:
        dir_path = os.path.join(project_root, dir_name)
        if os.path.exists(dir_path):
            print(f"[INFO] Scanning {dir_name}...")
            auditor.scan_directory(dir_path)
    
    # Print summary
    auditor.print_summary()
    
    # Generate report
    report = auditor.generate_report()
    report_path = os.path.join(
        os.path.dirname(__file__),
        'budget_plans_audit_report.md'
    )
    
    with open(report_path, 'w', encoding='utf-8') as f:
        f.write(report)
    
    print(f"\n[OK] Detailed report saved to: {report_path}")


if __name__ == "__main__":
    main()
