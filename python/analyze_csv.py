import pandas as pd
import sys

# Read CSV file
csv_path = r'C:\laragon\www\hr_budget\docs\budget_structure2schema.csv'
print(f"Reading CSV file: {csv_path}", flush=True)
print("=" * 80, flush=True)

try:
    df = pd.read_csv(csv_path, encoding='utf-8-sig')
except:
    df = pd.read_csv(csv_path, encoding='cp874')  # Thai encoding fallback

print(f"Total rows: {len(df)}", flush=True)
print(f"Columns: {list(df.columns)}", flush=True)
print("=" * 80, flush=True)

# Check "กอง" column
if 'กอง' in df.columns:
    print("\n=== Analysis: กอง (Division) ===", flush=True)
    
    # Distinct divisions
    divisions = df['กอง'].dropna().unique()
    print(f"\nDistinct กอง values: {len(divisions)}", flush=True)
    for div in divisions:
        print(f"  - {div}", flush=True)
    
    # Check records with "กองบริหารทรัพยากรบุคคล"
    target_division = 'กองบริหารทรัพยากรบุคคล'
    div_data = df[df['กอง'] == target_division]
    
    print(f"\n=== Records for '{target_division}' ===", flush=True)
    print(f"Total rows: {len(div_data)}", flush=True)
    
    if 'แผนงาน' in df.columns:
        plans = div_data['แผนงาน'].unique()
        print(f"Distinct plans: {len(plans)}", flush=True)
        for plan in plans:
            count = len(div_data[div_data['แผนงาน'] == plan])
            print(f"  - {plan} ({count} rows)", flush=True)
    
    # Check records with empty กอง
    empty_div = df[df['กอง'].isna() | (df['กอง'] == '')]
    print(f"\n=== Records with empty กอง ===", flush=True)
    print(f"Total rows: {len(empty_div)}", flush=True)
    
    if 'แผนงาน' in df.columns and len(empty_div) > 0:
        plans_empty = empty_div['แผนงาน'].unique()
        print(f"Distinct plans: {len(plans_empty)}", flush=True)
        for plan in plans_empty[:10]:  # Show first 10
            count = len(empty_div[empty_div['แผนงาน'] == plan])
            print(f"  - {plan} ({count} rows)", flush=True)
        if len(plans_empty) > 10:
            print(f"  ... and {len(plans_empty) - 10} more plans", flush=True)
    
    # Check if "กรม" column exists
    if 'กรม' in df.columns:
        print(f"\n=== Records with empty กอง but has กรม ===", flush=True)
        dept_only = empty_div[empty_div['กรม'].notna() & (empty_div['กรม'] != '')]
        print(f"Total rows: {len(dept_only)}", flush=True)
        if len(dept_only) > 0:
            depts = dept_only['กรม'].unique()
            print(f"Distinct กรม: {len(depts)}", flush=True)
            for dept in depts:
                count = len(dept_only[dept_only['กรม'] == dept])
                print(f"  - {dept} ({count} rows)", flush=True)

else:
    print("ERROR: 'กอง' column not found!", flush=True)
    print(f"Available columns: {list(df.columns)}", flush=True)

print("\n" + "=" * 80, flush=True)
print("Analysis complete!", flush=True)
