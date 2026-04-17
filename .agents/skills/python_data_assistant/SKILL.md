---
name: python_data_assistant
description: Python guide for database operations, data analysis, and script usage in the HR Budget project. Includes venv setup, connection patterns, and Jupyter workflows.
---

# Python Data Assistant

Python utilities for data management, analysis, and reporting in the HR Budget project.

## 📑 Table of Contents

- [When to Use Python](#-when-to-use-python)
- [Environment Setup](#-environment-setup)
- [Database Connection](#-database-connection)
- [Data Management (WRITE)](#-data-management-write)
- [Data Analysis (READ)](#-data-analysis-read)
- [Common Patterns](#-common-patterns)
- [Advanced Patterns](#-advanced-patterns)
- [ETL Patterns](#-etl-patterns)
- [Advanced Visualization](#-advanced-visualization)
- [Jupyter Notebooks](#-jupyter-notebooks)
- [Available Scripts](#-available-scripts)
- [Best Practices](#-best-practices)
- [Troubleshooting](#-troubleshooting)
- [Related Workflows](#-related-workflows)

## 🤔 When to Use Python

| Task | Use Python | Use PHP |
|:-----|:-----------|:--------|
| **Bulk Data Operations** | ✅ Yes | ❌ No |
| **Data Analysis/Charts** | ✅ Yes | ❌ No |
| **Database Migrations** | ✅ Yes | ⚠️ Sometimes |
| **Web Requests** | ❌ No | ✅ Yes |
| **User Interfaces** | ❌ No | ✅ Yes |
| **Reports/Excel** | ✅ Yes | ⚠️ Sometimes |

**Golden Rule:** Python for DATA, PHP for WEB.

## 🐍 Environment Setup

### 1. Virtual Environment

```bash
cd C:\laragon\www\hr_budget\python

# Create venv (first time only)
python -m venv venv

# Activate
venv\Scripts\activate

# Install dependencies
pip install mysql-connector-python pandas matplotlib seaborn openpyxl jupyter
```

### 2. Directory Structure

```
python/
├── venv/                    # Virtual environment
├── db_config.py             # Database configuration
├── analyze_*.py             # Analysis scripts
├── clean_*.py               # Data cleaning scripts
├── migration_*.py           # Migration scripts
├── *.ipynb                  # Jupyter notebooks
└── reports/                 # Output files
```

## 🔌 Database Connection

### Using `db_config.py`

```python
import sys
sys.path.append('.')
from db_config import get_db_config
import mysql.connector

def get_connection():
    config = get_db_config()  # Loads from ../.env
    return mysql.connector.connect(
        host=config['host'],
        user=config['user'],
        password=config['password'],
        database=config['database'],
        charset='utf8mb4'
    )

# Usage
conn = get_connection()
cursor = conn.cursor(dictionary=True)
cursor.execute("SELECT * FROM users LIMIT 5")
rows = cursor.fetchall()
conn.close()
```

### Configuration

`db_config.py` automatically reads from `.env`:
- `DB_HOST` → localhost
- `DB_USERNAME` → root
- `DB_PASSWORD` → (empty)
- `DB_DATABASE` → hr_budget

## 📝 Data Management (WRITE)

> ⚠️ **DANGEROUS**: Can modify/delete data. Follow safety rules.

### Workflow

See full workflow: `/python-data-management`

### Safety Template

```python
import argparse
from db_config import get_db_config
import mysql.connector

# Require --confirm flag
parser = argparse.ArgumentParser()
parser.add_argument('--confirm', action='store_true')
args = parser.parse_args()
is_dry_run = not args.confirm

conn = get_connection()
cursor = conn.cursor()

try:
    # Your UPDATE/INSERT/DELETE logic
    if not is_dry_run:
        cursor.execute("UPDATE ...")
        print("✅ Would update")
    else:
        print("🔍 Dry-run: No changes")
    
    if is_dry_run:
        conn.rollback()
    else:
        conn.commit()
except Exception as e:
    conn.rollback()
    print(f"❌ Error: {e}")
finally:
    conn.close()
```

**Run:**
```bash
python my_script.py           # Dry-run (safe)
python my_script.py --confirm # Execute (dangerous!)
```

## 📊 Data Analysis (READ)

> ✅ **SAFE**: Read-only operations for analysis

### Workflow

See full workflow: `/python-data-analysis`

### Quick Analysis Script

```python
import pandas as pd
from db_config import get_db_config
import mysql.connector

def get_connection():
    config = get_db_config()
    return mysql.connector.connect(**config)

# Load data into DataFrame
conn = get_connection()
df = pd.read_sql("""
    SELECT level, COUNT(*) as count
    FROM budget_category_items
    WHERE is_active = 1
    GROUP BY level
""", conn)
conn.close()

# Analyze
print(df)
print(f"\nTotal: {df['count'].sum()}")

# Export
df.to_csv('reports/budget_summary.csv', index=False, encoding='utf-8-sig')
```

## 🛠️ Common Patterns

### 1. Query to DataFrame

```python
import pandas as pd

conn = get_connection()
df = pd.read_sql("SELECT * FROM budget_requests", conn)
conn.close()
```

### 2. Export to Excel

```python
df.to_excel('reports/data.xlsx', index=False, engine='openpyxl')
```

### 3. Simple Visualization

```python
import matplotlib.pyplot as plt

plt.rcParams['font.family'] = 'Tahoma'  # Thai support

df['level'].value_counts().plot(kind='bar')
plt.title('Items by Level')
plt.savefig('reports/chart.png', dpi=150)
plt.show()
```

### 4. Bulk Insert

```python
data = [
    (1, 'Item A', 2568),
    (2, 'Item B', 2568)
]

cursor.executemany("""
    INSERT INTO table (id, name, fiscal_year)
    VALUES (%s, %s, %s)
""", data)
conn.commit()
```

## 🚀 Advanced Patterns

### 1. Recursive Query (Hierarchy) using CTE

When fetching the full tree of budget items (Plans > Projects > Activities):

```python
query = """
WITH RECURSIVE hierarchy AS (
    SELECT id, name_th, parent_id, id as root_id, 0 as level
    FROM projects
    WHERE parent_id IS NULL
    
    UNION ALL
    
    SELECT p.id, p.name_th, p.parent_id, h.root_id, h.level + 1
    FROM projects p
    INNER JOIN hierarchy h ON p.parent_id = h.id
)
SELECT * FROM hierarchy ORDER BY root_id, level;
"""
df = pd.read_sql(query, conn)
```

### 2. Pandas Memory Optimization

Reduce memory usage by 70%+ for large datasets:

```python
# 1. Downcast Numeric Types
df['year'] = pd.to_numeric(df['year'], downcast='unsigned')  # int64 -> uint16
df['amount'] = pd.to_numeric(df['amount'], downcast='float') # float64 -> float32

# 2. Categoricals (for low cardinality columns)
# Good for: Status, Departments, Org IDs
df['status'] = df['status'].astype('category')
df['org_id'] = df['org_id'].astype('category')

# 3. Process in Chunks (Iterator)
iter_csv = pd.read_csv('giant_file.csv', iterator=True, chunksize=1000)
df = pd.concat([process(chunk) for chunk in iter_csv])
```

## 🔄 ETL Patterns

### Extract-Transform-Load Workflows

ETL scripts for data transformation and migration.

### 1. CSV to Database
```python
import pandas as pd
from db_config import get_db_config
import mysql.connector

# Extract (CSV)
df = pd.read_csv('data/budget_items.csv', encoding='utf-8-sig')

# Transform
df['fiscal_year'] = df['fiscal_year'].astype(int)
df['amount'] = pd.to_numeric(df['amount'], errors='coerce').fillna(0)
df['name_th'] = df['name_th'].str.strip()

# Validate
assert df['fiscal_year'].between(2500, 2600).all(), "Invalid fiscal year"
assert df['amount'].notna().all(), "Amount cannot be null"

# Load
conn = mysql.connector.connect(**get_db_config())
cursor = conn.cursor()

for idx, row in df.iterrows():
    cursor.execute("""
        INSERT INTO budget_items (name_th, fiscal_year, amount)
        VALUES (%s, %s, %s)
    """, (row['name_th'], row['fiscal_year'], row['amount']))

conn.commit()
conn.close()
print(f"✅ Loaded {len(df)} rows")
```

### 2. Database to Database (Cross-Schema)
```python
# Source and Target connections
source_conn = mysql.connector.connect(host='old_server', ...)
target_conn = mysql.connector.connect(**get_db_config())

# Extract
source_cur = source_conn.cursor(dictionary=True)
source_cur.execute("SELECT * FROM legacy_budgets WHERE year >= 2565")
rows = source_cur.fetchall()

# Transform
df = pd.DataFrame(rows)
df = df.rename(columns={'old_name': 'name_th', 'old_amt': 'amount'})
df['created_at'] = pd.Timestamp.now()

# Load
target_cur = target_conn.cursor()
for _, row in df.iterrows():
    target_cur.execute("""
        INSERT INTO budget_requests (name_th, amount, created_at)
        VALUES (%s, %s, %s)
    """, (row['name_th'], row['amount'], row['created_at']))

target_conn.commit()
target_conn.close()
source_conn.close()
```

### 3. Data Cleansing Pipeline
```python
def clean_budget_data(df: pd.DataFrame) -> pd.DataFrame:
    """Clean and standardize budget data."""
    # Remove duplicates
    df = df.drop_duplicates(subset=['name_th', 'fiscal_year'])
    
    # Standardize text
    df['name_th'] = df['name_th'].str.strip()
    df['name_th'] = df['name_th'].str.replace(r'\s+', ' ', regex=True)
    
    # Fix data types
    df['amount'] = pd.to_numeric(df['amount'], errors='coerce')
    df['fiscal_year'] = pd.to_numeric(df['fiscal_year'], errors='coerce')
    
    # Drop rows with missing critical fields
    df = df.dropna(subset=['name_th', 'fiscal_year'])
    
    # Fill missing amounts with 0
    df['amount'] = df['amount'].fillna(0)
    
    return df

# Usage
df_raw = pd.read_csv('raw_data.csv')
df_clean = clean_budget_data(df_raw)
df_clean.to_csv('cleaned_data.csv', index=False)
```

### 4. Incremental Load (Delta)
```python
from datetime import datetime, timedelta

# Get last sync timestamp
last_sync = pd.read_sql(
    "SELECT MAX(synced_at) as last_sync FROM sync_log",
    conn
)['last_sync'][0] or datetime(2020, 1, 1)

# Extract only new/updated records
query = f"""
    SELECT * FROM source_table 
    WHERE updated_at > '{last_sync}'
"""
df_delta = pd.read_sql(query, source_conn)

print(f"Found {len(df_delta)} new/updated records since {last_sync}")

# Load delta
for _, row in df_delta.iterrows():
    # Upsert logic
    cursor.execute("""
        INSERT INTO target_table (id, name, updated_at)
        VALUES (%s, %s, %s)
        ON DUPLICATE KEY UPDATE name=%s, updated_at=%s
    """, (row['id'], row['name'], row['updated_at'], 
           row['name'], row['updated_at']))

conn.commit()

# Log sync
cursor.execute(
    "INSERT INTO sync_log (synced_at, records) VALUES (NOW(), %s)",
    (len(df_delta),)
)
conn.commit()
```

## 📈 Advanced Visualization

### Interactive Dashboards

Beyond static charts - create interactive dashboards.

### 1. Plotly Interactive Charts
```python
import plotly.express as px
import plotly.graph_objects as go

# Load data
df = pd.read_sql("""
    SELECT fiscal_year, organization_id, SUM(amount) as total
    FROM budget_allocations
    GROUP BY fiscal_year, organization_id
""", conn)

# Interactive bar chart
fig = px.bar(
    df, 
    x='fiscal_year', 
    y='total', 
    color='organization_id',
    title='งบประมาณแบ่งตามหน่วยงาน',
    labels={'total': 'จำนวนเงิน (บาท)', 'fiscal_year': 'ปีงบประมาณ'}
)

# Customize
fig.update_layout(
    font=dict(family="Sarabun, sans-serif"),
    hovermode='x unified'
)

# Save as HTML (can embed in reports)
fig.write_html('reports/budget_by_org.html')

# Or show in Jupyter
fig.show()
```

### 2. Dash Web Dashboard (Optional)
```python
# Install: pip install dash
from dash import Dash, html, dcc, Input, Output
import plotly.express as px

app = Dash(__name__)

app.layout = html.Div([
    html.H1('HR Budget Dashboard'),
    
    dcc.Dropdown(
        id='year-dropdown',
        options=[{'label': str(y), 'value': y} for y in range(2565, 2570)],
        value=2568
    ),
    
    dcc.Graph(id='budget-chart')
])

@app.callback(
    Output('budget-chart', 'figure'),
    Input('year-dropdown', 'value')
)
def update_chart(selected_year):
    df = pd.read_sql(f"""
        SELECT organization_id, SUM(amount) as total
        FROM budget_allocations
        WHERE fiscal_year = {selected_year}
        GROUP BY organization_id
    """, conn)
    
    fig = px.pie(df, names='organization_id', values='total')
    return fig

if __name__ == '__main__':
    app.run_server(debug=True, port=8050)
```

### 3. Heatmap Visualization
```python
import seaborn as sns
import matplotlib.pyplot as plt

# Pivot data for heatmap
df = pd.read_sql("""
    SELECT 
        MONTH(created_at) as month,
        organization_id,
        COUNT(*) as request_count
    FROM budget_requests
    WHERE YEAR(created_at) = 2568
    GROUP BY month, organization_id
""", conn)

pivot = df.pivot(index='month', columns='organization_id', values='request_count')

# Create heatmap
plt.figure(figsize=(12, 8))
sns.heatmap(pivot, annot=True, fmt='g', cmap='YlOrRd')
plt.title('คำของบประมาณ (รายเดือน x หน่วยงาน)', fontsize=16)
plt.xlabel('หน่วยงาน')
plt.ylabel('เดือน')
plt.tight_layout()
plt.savefig('reports/request_heatmap.png', dpi=150)
plt.show()
```

### 4. Time Series Trends
```python
# Get monthly trends
df = pd.read_sql("""
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count,
        SUM(amount) as total_amount
    FROM budget_requests
    GROUP BY month
    ORDER BY month
""", conn)

df['month'] = pd.to_datetime(df['month'])

# Dual-axis plot
fig, ax1 = plt.subplots(figsize=(14, 6))

ax1.set_xlabel('Month')
ax1.set_ylabel('Count', color='tab:blue')
ax1.plot(df['month'], df['count'], color='tab:blue', marker='o', label='Count')
ax1.tick_params(axis='y', labelcolor='tab:blue')

ax2 = ax1.twinx()
ax2.set_ylabel('Amount (Million THB)', color='tab:red')
ax2.plot(df['month'], df['total_amount']/1_000_000, color='tab:red', marker='s', label='Amount')
ax2.tick_params(axis='y', labelcolor='tab:red')

plt.title('Budget Request Trends')
plt.tight_layout()
plt.savefig('reports/trends.png', dpi=150)
plt.show()
```

## 📓 Jupyter Notebooks

### Start Jupyter

```bash
cd C:\laragon\www\hr_budget\python
venv\Scripts\activate
jupyter notebook
```

Browser opens → Select `.ipynb` file

### Kernel Setup (If venv not found)
If Jupyter doesn't see your virtual environment:
```bash
# Install kernel spec
python -m ipykernel install --user --name=venv --display-name "Python (HR Budget)"
```

### Notebook Template

```python
# Cell 1: Setup
import pandas as pd
import matplotlib.pyplot as plt
import sys
sys.path.append('.')
from db_config import get_db_config
import mysql.connector

plt.rcParams['font.family'] = 'Tahoma'

# Cell 2: Load Data
def get_conn():
    return mysql.connector.connect(**get_db_config())

conn = get_conn()
df = pd.read_sql("SELECT * FROM budget_category_items", conn)
conn.close()

df.head()

# Cell 3: Analyze
df.describe()

# Cell 4: Visualize
df['level'].value_counts().plot(kind='bar', figsize=(10, 6))
plt.title('Distribution by Level')
plt.show()
```

## 📂 Available Scripts

**Analysis Scripts** (23 total):
- `analyze_db_schema.py` - Database structure analysis
- `analyze_budget_line_items.py` - Budget line item analysis
- `analyze_csv.py` - CSV data analysis
- `analyze_expense_types.py` - Expense type analysis
- `inspect_schema.py` - Quick schema lookup

**Data Management Scripts**:
- `clean_budget_data.py` - Clean/transform budget data
- `add_org_id_column.py` - Add organization ID column
- `migration_*.py` - Database migrations

**Utility Scripts**:
- `db_config.py` - Database configuration helper
- `check_schema.py` - Schema validation

> 📌 All scripts accessible in `python/` folder

## ✅ Best Practices

### Security
```python
# ✅ Use prepared statements
cursor.execute(
    "SELECT * FROM users WHERE id = %s",
    (user_id,)
)

# ❌ Never concatenate SQL
cursor.execute(f"SELECT * FROM users WHERE id = {user_id}")  # SQL Injection!
```

### Encoding
```python
# For Excel (Windows)
df.to_csv('file.csv', encoding='utf-8-sig', index=False)

# For Thai characters in charts
plt.rcParams['font.family'] = 'Tahoma'
```

### Performance
```python
# Use LIMIT when testing
df = pd.read_sql("SELECT * FROM table LIMIT 1000", conn)

# Select only needed columns
df = pd.read_sql("SELECT id, name FROM table", conn)  # Better than SELECT *
```

## 🚨 Troubleshooting

### Common Errors

| Error | Cause | Solution |
|:------|:------|:---------|
| `ModuleNotFoundError` | venv not activated | Run `venv\Scripts\activate` |
| `Access denied` | Wrong credentials | Check `.env` file |
| Thai characters broken | Encoding issue | Use `encoding='utf-8-sig'` |
| `connection already closed` | Connection reuse | Create new connection |
| Chart labels missing | Font issue | Set `plt.rcParams['font.family'] = 'Tahoma'` |
| `pyscript` not found | Kernel issue | Install ipykernel (see above) |

### Debug Connection

```python
from db_config import get_db_config

config = get_db_config()
print(config)  # Check values

# Test connection
import mysql.connector
try:
    conn = mysql.connector.connect(**config)
    print("✅ Connected!")
    conn.close()
except Exception as e:
    print(f"❌ Error: {e}")
```

### Memory Issues (Large Data)

```python
# Use chunking
for chunk in pd.read_sql(query, conn, chunksize=1000):
    process(chunk)

# Optimize dtypes
df['level'] = df['level'].astype('int8')  # If values < 128
df['category'] = df['category'].astype('category')  # For repeated values
```

## 🔗 Related Workflows

### Python Workflows
- [Python Data Management](file:///c:/laragon/www/hr_budget/.agents/workflows/python-data-management.md) - WRITE operations with safety
- [Python Data Analysis](file:///c:/laragon/www/hr_budget/.agents/workflows/python-data-analysis.md) - READ-only analysis

### Project Workflows
- [Git Workflow](file:///c:/laragon/www/hr_budget/.agents/workflows/git-workflow.md) - Version control
- [Backup Procedure](file:///c:/laragon/www/hr_budget/.agents/workflows/backup-procedure.md) - Before risky operations

### Main Skill
- [HR Budget Assistant](file:///c:/laragon/www/hr_budget/.agents/skills/hr_budget_assistant/SKILL.md) - PHP development guide

---

**Last Updated:** 2026-01-14  
**Version:** 1.0  
**Maintained By:** Development Team
