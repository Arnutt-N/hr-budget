"""
Budget Structure Analyzer - กองบริหารทรัพยากรบุคคล
Analyzes CSV hierarchy and generates tree structure report.

Usage:
    python analyze_hr_budget_structure.py
"""

import csv
from collections import OrderedDict
from pathlib import Path


def load_csv(filepath):
    """Load CSV with UTF-8 encoding"""
    rows = []
    with open(filepath, 'r', encoding='utf-8-sig') as f:
        reader = csv.DictReader(f)
        for row in reader:
            rows.append(row)
    return rows


def filter_by_organization(rows, org_name):
    """Filter rows by organization (กอง)"""
    return [r for r in rows if r.get('กอง', '').strip() == org_name]


def build_nested_tree(rows):
    """
    Build a nested tree from rows.
    Structure: expense_type -> item0 -> item1 -> item2 -> ... -> item6
    """
    
    def get_or_create(d, key):
        if key not in d:
            d[key] = OrderedDict()
        return d[key]
    
    tree = OrderedDict()
    
    for row in rows:
        expense_type = row.get('ประเภทรายจ่าย', '').strip()
        if not expense_type:
            continue
        
        current = get_or_create(tree, expense_type)
        
        # Walk through รายการ 0 to รายการ 6
        for level in range(7):
            col_name = f'รายการ {level}'
            value = row.get(col_name, '').strip()
            
            if not value:
                break
            
            current = get_or_create(current, value)
    
    return tree


def print_tree_recursive(tree, level=0, prefix="", output_lines=None):
    """Print tree with proper indentation"""
    if output_lines is None:
        output_lines = []
    
    items = list(tree.items())
    
    for i, (key, children) in enumerate(items):
        # Create indent based on level
        if level == 0:
            line = f"# ประเภทรายจ่าย = {key}"
        else:
            dashes = "-" * level
            line = f"{dashes} รายการ {level - 1} = {key}"
        
        output_lines.append(line)
        
        # Recurse into children
        if children:
            print_tree_recursive(children, level + 1, prefix + "  ", output_lines)
    
    return output_lines


def main():
    csv_path = Path(r"C:\laragon\www\hr_budget\docs\budget_structure2schema.csv")
    
    if not csv_path.exists():
        print(f"[ERROR] CSV not found: {csv_path}")
        return
    
    rows = load_csv(csv_path)
    print(f"[OK] Loaded {len(rows)} rows from CSV")
    
    # Filter for กองบริหารทรัพยากรบุคคล
    org_name = "กองบริหารทรัพยากรบุคคล"
    hr_rows = filter_by_organization(rows, org_name)
    
    print(f"[OK] Found {len(hr_rows)} rows for: {org_name}")
    
    if not hr_rows:
        print(f"[WARNING] No rows found for: {org_name}")
        print("\nAvailable organizations:")
        orgs = set(r.get('กอง', '').strip() for r in rows if r.get('กอง', '').strip())
        for o in sorted(orgs):
            count = len([r for r in rows if r.get('กอง', '').strip() == o])
            print(f"  - {o} ({count} rows)")
        return
    
    # Show context info
    print("\n" + "=" * 60)
    print("CONTEXT INFO")
    print("=" * 60)
    
    plans = set(r.get('แผนงาน', '').strip() for r in hr_rows if r.get('แผนงาน', '').strip())
    print(f"\nPlans ({len(plans)}):")
    for p in sorted(plans):
        print(f"  - {p}")
    
    projects = set(r.get('ผลผลิต/โครงการ', '').strip() for r in hr_rows if r.get('ผลผลิต/โครงการ', '').strip())
    print(f"\nProjects ({len(projects)}):")
    for p in sorted(projects):
        print(f"  - {p}")
    
    activities = set(r.get('กิจกรรม', '').strip() for r in hr_rows if r.get('กิจกรรม', '').strip())
    print(f"\nActivities ({len(activities)}):")
    for a in sorted(activities):
        print(f"  - {a}")
    
    # Build and print hierarchy
    print("\n" + "=" * 60)
    print("EXPENSE ITEM HIERARCHY")
    print("=" * 60 + "\n")
    
    tree = build_nested_tree(hr_rows)
    lines = print_tree_recursive(tree)
    
    for line in lines:
        print(line)
    
    # Save report
    report_path = Path(r"C:\laragon\www\hr_budget\python\hr_budget_structure_report.md")
    with open(report_path, 'w', encoding='utf-8') as f:
        f.write(f"# Budget Structure: {org_name}\n\n")
        f.write(f"Generated from: `{csv_path.name}`\n")
        f.write(f"Total rows: {len(hr_rows)}\n\n")
        
        f.write("## Context\n\n")
        f.write(f"**Plan:** {', '.join(sorted(plans))}\n\n")
        f.write(f"**Project:** {', '.join(sorted(projects))}\n\n")
        f.write(f"**Activity:** {', '.join(sorted(activities))}\n\n")
        
        f.write("## Expense Item Hierarchy\n\n")
        f.write("```\n")
        f.write('\n'.join(lines))
        f.write("\n```\n")
    
    print(f"\n[OK] Report saved: {report_path}")


if __name__ == "__main__":
    main()
