import pandas as pd
import mysql.connector
from db_config import get_db_config
import sys

# Get database config
config = get_db_config()

print("Connecting to database...", flush=True)
try:
    conn = mysql.connector.connect(
        host=config['host'],
        user=config['user'],
        password=config['password'],
        database=config['database'],
        port=config['port']
    )
except Exception as e:
    print(f"Error connecting to MySQL: {e}", flush=True)
    sys.exit(1)

print("Fetching budget_line_items...", flush=True)
cursor = conn.cursor(dictionary=True)

# Fetch all budget_line_items with related data
query = """
SELECT 
    bli.id,
    bli.fiscal_year,
    bli.division_id,
    o.name_th as division_name,
    bli.plan_id,
    p.name_th as plan_name,
    bli.project_id,
    proj.name_th as project_name,
    bli.activity_id,
    act.name_th as activity_name,
    bli.allocated_pba,
    bli.allocated_received,
    bli.disbursed
FROM budget_line_items bli
LEFT JOIN organizations o ON bli.division_id = o.id
LEFT JOIN plans p ON bli.plan_id = p.id
LEFT JOIN projects proj ON bli.project_id = proj.id
LEFT JOIN activities act ON bli.activity_id = act.id
ORDER BY bli.fiscal_year, bli.division_id, bli.plan_id, bli.project_id, bli.activity_id
"""

cursor.execute(query)
results = cursor.fetchall()

# Convert to DataFrame
df = pd.DataFrame(results)

print("\n" + "="*80, flush=True)
print(f"Total rows: {len(df)}", flush=True)
print("="*80, flush=True)

# Summary by division
print("\n=== Summary by Division ===", flush=True)
division_summary = df.groupby(['division_id', 'division_name']).agg({
    'id': 'count',
    'plan_id': 'nunique'
}).rename(columns={'id': 'total_rows', 'plan_id': 'distinct_plans'})
print(division_summary, flush=True)

# Summary by plan
print("\n=== Summary by Plan ===", flush=True)
plan_summary = df.groupby(['plan_id', 'plan_name']).agg({
    'id': 'count',
    'division_id': 'nunique'
}).rename(columns={'id': 'total_rows', 'division_id': 'distinct_divisions'})
print(plan_summary, flush=True)

# Records with NULL division_id
null_div = df[df['division_id'].isna()]
print(f"\n=== Records with division_id = NULL ===", flush=True)
print(f"Total rows: {len(null_div)}", flush=True)
if len(null_div) > 0:
    null_plans = null_div.groupby('plan_name').size().sort_values(ascending=False)
    print("\nPlans:", flush=True)
    print(null_plans, flush=True)

# Records with division_id = 3
div3 = df[df['division_id'] == 3]
print(f"\n=== Records with division_id = 3 (กองบริหารทรัพยากรบุคคล) ===", flush=True)
print(f"Total rows: {len(div3)}", flush=True)
if len(div3) > 0:
    div3_plans = div3.groupby('plan_name').size().sort_values(ascending=False)
    print("\nPlans:", flush=True)
    print(div3_plans, flush=True)

# Display first 10 rows
print("\n=== First 10 rows ===", flush=True)
print(df.head(10).to_string(), flush=True)

# Display last 10 rows
print("\n=== Last 10 rows ===", flush=True)
print(df.tail(10).to_string(), flush=True)

# Column info
print("\n=== Column Info ===", flush=True)
print(df.info(), flush=True)

cursor.close()
conn.close()

print("\n" + "="*80, flush=True)
print("Analysis complete!", flush=True)
