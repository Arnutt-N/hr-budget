---
description: ขั้นตอนการใช้ Python วิเคราะห์และแสดงผลข้อมูล (Python Data Analysis & Visualization)
---

# Python Data Analysis & Visualization Workflow

## เมื่อไหร่ควรใช้ Workflow นี้

ใช้เมื่อต้องการ:
- 📊 สร้างกราฟ/แผนภูมิ (Charts, Plots)
- 📋 ดูข้อมูลในรูปแบบ DataFrame
- 🔍 วิเคราะห์/สำรวจข้อมูล (Exploratory Data Analysis)
- 📁 Export ข้อมูลเป็น CSV, Excel, HTML
- 📝 สร้างรายงานสรุป

> ⚠️ **หมายเหตุ**: Workflow นี้สำหรับ **READ-ONLY** เท่านั้น
> หากต้องการแก้ไขข้อมูล ใช้ `/python-data-management`

## โครงสร้างไฟล์

```
python/
├── db_config.py              # Database configuration
├── venv/                     # Virtual environment
├── analysis_*.py             # Analysis scripts
├── budget_analysis.ipynb     # Jupyter notebooks
├── reports/                  # Output reports
│   ├── charts/               # PNG/HTML charts
│   ├── tables/               # CSV/Excel tables
│   └── logs/                 # Log files
└── data/                     # Optional raw data files (CSV, JSON)
```

## การติดตั้ง Dependencies

```bash
cd C:\laragon\www\hr_budget\python
venv\Scripts\activate

# ติดตั้ง packages สำหรับ analysis
pip install pandas matplotlib seaborn plotly openpyxl jupyter
```

## Template สำหรับ Analysis Script

### 1. Basic Script Template

```python
"""
[Analysis Purpose]
Author: [Your Name]
Date: [YYYY-MM-DD]
"""

import pandas as pd
import matplotlib.pyplot as plt
from datetime import datetime
import sys
sys.path.append('.')
from db_config import get_db_config

# Thai font support
plt.rcParams['font.family'] = 'Tahoma'

def get_db_connection():
    """Get read-only database connection."""
    config = get_db_config()
    import mysql.connector
    return mysql.connector.connect(
        host=config['host'],
        user=config['user'],
        password=config['password'],
        database=config['database'],
        charset='utf8mb4'
    )

def load_data():
    """Load data into DataFrame."""
    conn = get_db_connection()
    query = """
        SELECT id, name, level, parent_id
        FROM budget_category_items
        WHERE is_active = 1
        ORDER BY sort_order
    """
    df = pd.read_sql(query, conn)
    conn.close()
    return df

def analyze(df):
    """Perform analysis."""
    print("=" * 60)
    print("Data Overview")
    print("=" * 60)
    print(f"Total rows: {len(df)}")
    print(f"Columns: {list(df.columns)}")
    print()
    print(df.head(10))
    print()
    print(df.describe())

def main():
    df = load_data()
    analyze(df)

if __name__ == "__main__":
    main()
```

### 2. Jupyter Notebook Template

```python
# Cell 1: Setup
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns
import sys
sys.path.append('.')
from db_config import get_db_config

plt.rcParams['font.family'] = 'Tahoma'
sns.set_theme(style="whitegrid")

# Cell 2: Load Data
def get_conn():
    config = get_db_config()
    import mysql.connector
    return mysql.connector.connect(
        host=config['host'], user=config['user'],
        password=config['password'], database=config['database'],
        charset='utf8mb4'
    )

conn = get_conn()
df = pd.read_sql("SELECT * FROM budget_category_items", conn)
conn.close()

df.head()

# Cell 3: Quick Stats
df.describe()

# Cell 4: Visualization
fig, ax = plt.subplots(figsize=(10, 6))
df['level'].value_counts().plot(kind='bar', ax=ax)
ax.set_title('Distribution by Level')
plt.tight_layout()
plt.show()
```

## ตัวอย่างการใช้งานจริง

อ้างอิง scripts และ notebooks ที่มีอยู่:
- `python/budget_analysis.ipynb` - วิเคราะห์โครงสร้างงบประมาณ
- `python/analyze_budget_line_items.py` - วิเคราะห์รายการงบ
- `python/analyze_csv.py` - วิเคราะห์ข้อมูลจาก CSV
- `python/add_special_profession_subitems.py` - ตัวอย่างการเพิ่มข้อมูล (อ้างอิงเพื่อเรียนรู้ pattern)

## Common Patterns

### 1. Query to DataFrame

```python
import pandas as pd

conn = get_db_connection()
df = pd.read_sql("""
    SELECT * 
    FROM budget_category_items 
    WHERE level >= 3
""", conn)
conn.close()
```

### 2. Export to CSV

```python
df.to_csv('reports/budget_items.csv', index=False, encoding='utf-8-sig')
```

### 3. Export to Excel

```python
df.to_excel('reports/budget_items.xlsx', index=False, engine='openpyxl')
```

### 4. Simple Bar Chart

```python
import matplotlib.pyplot as plt

fig, ax = plt.subplots(figsize=(10, 6))
df.groupby('level').size().plot(kind='bar', ax=ax)
ax.set_title('Items per Level')
ax.set_xlabel('Level')
ax.set_ylabel('Count')
plt.tight_layout()
plt.savefig('reports/items_per_level.png', dpi=150)
plt.show()
```

### 5. Pie Chart

```python
df['category'].value_counts().plot(kind='pie', autopct='%1.1f%%')
plt.title('Category Distribution')
plt.savefig('reports/category_pie.png', dpi=150)
```

### 6. Data Summary Table

```python
summary = df.groupby('level').agg({
    'id': 'count',
    'name': 'nunique'
}).rename(columns={'id': 'Total', 'name': 'Unique Names'})

print(summary.to_markdown())
```

## Data Validation

ตรวจสอบคุณภาพข้อมูลก่อนทำ visualization:

```python
# ตรวจสอบ missing values
print(df.isnull().sum())

# ตรวจสอบ duplicate rows
print(f"Duplicate rows: {df.duplicated().sum()}")

# ตรวจสอบค่าที่อยู่นอกช่วงที่คาดหวัง (เช่น level ควรอยู่ 1‑6)
invalid_levels = df[~df['level'].isin([1,2,3,4,5,6])]
print(f"Invalid levels: {len(invalid_levels)}")
```

## Advanced Patterns

### 7. Multi-Table JOIN

```python
query = """
    SELECT 
        b.name AS budget_name,
        o.name AS org_name,
        COUNT(*) as item_count
    FROM budget_category_items b
    LEFT JOIN organizations o ON b.org_id = o.id
    GROUP BY b.name, o.name
    ORDER BY item_count DESC
"""
df = pd.read_sql(query, conn)
```

### 8. Time Series Analysis

```python
# Convert to datetime
df['created_at'] = pd.to_datetime(df['created_at'])

# Resample by month
df_monthly = df.set_index('created_at').resample('M').size()

# Plot
df_monthly.plot(kind='line', figsize=(12, 6))
plt.title('Items Created Over Time')
plt.ylabel('Count')
plt.tight_layout()
plt.savefig('reports/time_series.png', dpi=150)
```

### 9. Interactive Visualization (Plotly)

```python
import plotly.express as px
import plotly.io as pio

# Use notebook renderer for Jupyter
pio.renderers.default = "notebook"

fig = px.bar(
    df.groupby('level').size().reset_index(name='count'),
    x='level',
    y='count',
    title='Budget Items by Level (Interactive)',
    hover_data=['count']
)
fig.show()

# Save as HTML for sharing
fig.write_html('reports/charts/interactive_chart.html')
```

### 10. Filtering and Aggregation

```python
# Filter
active_items = df[df['is_active'] == 1]

# Group and aggregate
summary = active_items.groupby('level').agg({
    'id': 'count',
    'name': lambda x: ', '.join(x[:3])  # First 3 names
})
```

## รัน Jupyter Notebook

```bash
cd C:\laragon\www\hr_budget\python
venv\Scripts\activate
jupyter notebook
```

จะเปิด browser อัตโนมัติ → เลือกไฟล์ `.ipynb` ที่ต้องการ

## Performance Tips

### Large Data Optimization

```python
# 1. Select เฉพาะ columns ที่ใช้
df = pd.read_sql("SELECT id, name FROM table", conn)  # ดีกว่า SELECT *

# 2. ใช้ LIMIT เมื่อทดสอบ
df = pd.read_sql("SELECT * FROM table LIMIT 1000", conn)

# 3. ใช้ chunking สำหรับข้อมูลขนาดใหญ่
for chunk in pd.read_sql("SELECT * FROM table", conn, chunksize=1000):
    process(chunk)

# 4. Optimize data types
df['id'] = df['id'].astype('int32')  # ลด memory
df['level'] = df['level'].astype('category')  # สำหรับข้อมูลซ้ำ
```

## Best Practices

| หัวข้อ | คำแนะนำ |
|--------|---------|
| **Connection** | ใช้ `pd.read_sql()` แทน cursor loop |
| **Memory** | ใช้ `LIMIT` ใน query ถ้าข้อมูลมาก |
| **Encoding** | Export CSV ด้วย `encoding='utf-8-sig'` สำหรับ Excel |
| **Fonts** | ตั้ง `plt.rcParams['font.family'] = 'Tahoma'` สำหรับภาษาไทย |
| **Save** | บันทึกกราฟด้วย `plt.savefig()` ก่อน `plt.show()` |
| **Reports** | เก็บผลลัพธ์ไว้ใน folder `reports/` |
| **Data Types** | ใช้ `astype()` เพื่อ optimize memory |
| **Chunking** | ใช้ `chunksize` กับข้อมูลขนาดใหญ่ |

## Troubleshooting

### Thai Characters หายใน Chart

```python
# แก้ไข 1: ตั้ง font family
plt.rcParams['font.family'] = 'Tahoma'

# แก้ไข 2: ใช้ sans-serif
plt.rcParams['font.sans-serif'] = ['Tahoma', 'DejaVu Sans']

# แก้ไข 3: ถ้ายังไม่ได้ ใช้ FontProperties
from matplotlib.font_manager import FontProperties
font = FontProperties(family='Tahoma')
ax.set_title('ชื่อกราฟ', fontproperties=font)
```

### Memory Error กับข้อมูลขนาดใหญ่

```python
# 1. ใช้ LIMIT ทดสอบก่อน
df = pd.read_sql("SELECT * FROM table LIMIT 1000", conn)

# 2. ใช้ chunking
for chunk in pd.read_sql(query, conn, chunksize=1000):
    process(chunk)

# 3. Select เฉพาะ columns ที่ต้องการ
df = pd.read_sql("SELECT id, name FROM table", conn)

# 4. ลด memory usage
df['level'] = df['level'].astype('int8')  # ถ้าค่าไม่เกิน 127
```

### Jupyter Kernel ตาย

```python
# 1. ลด batch size
batch_size = 100  # ลดลงจาก 1000

# 2. ใช้ %matplotlib inline
%matplotlib inline
import matplotlib.pyplot as plt

# 3. Clear memory
import gc
gc.collect()

# 4. ปิดรูปหลังใช้
plt.close('all')
```

### Connection Error

```python
# ตรวจสอบ config
from db_config import get_db_config
config = get_db_config()
print(config)

# ทดสอบ connection
import mysql.connector
try:
    conn = mysql.connector.connect(**config)
    print("✅ Connection successful")
    conn.close()
except Exception as e:
    print(f"❌ Error: {e}")
```

### CSV/Excel Encoding Issues

```python
# สำหรับ Excel (Windows)
df.to_csv('file.csv', encoding='utf-8-sig', index=False)

# สำหรับ Excel (Mac/Linux)
df.to_excel('file.xlsx', index=False, engine='openpyxl')

# อ่าน CSV ภาษาไทย
df = pd.read_csv('file.csv', encoding='utf-8-sig')
```

## Advanced Extensions

### 11. Statistical Analysis (SciPy)
```python
from scipy import stats

# Check for normality of budget levels
k2, p = stats.normaltest(df['level'])
print(f"Normality p-value: {p:.4f}")

# Simple correlation
correlation = df[['level', 'sort_order']].corr()
print(f"Correlation:\n{correlation}")
```

### 12. Caching for Performance
Save processed DataFrames to Parquet/Pickle for lightning-fast reloading in notebooks:
```python
# Save
df.to_parquet('data/processed_budget.parquet')

# Load (much faster than SQL for large datasets)
df = pd.read_parquet('data/processed_budget.parquet')
```

### 13. Advanced Formatted Excel (XlsxWriter)
```python
writer = pd.ExcelWriter('reports/tables/formatted_report.xlsx', engine='xlsxwriter')
df.to_excel(writer, sheet_name='Summary', index=False)

workbook  = writer.book
worksheet = writer.sheets['Summary']

# Add a format for the header
header_fmt = workbook.add_format({'bold': True, 'bg_color': '#D7E4BC', 'border': 1})
for col_num, value in enumerate(df.columns.values):
    worksheet.write(0, col_num, value, header_fmt)

writer.close()
```

## สรุปคำสั่ง


```bash
# รัน script
python analysis_script.py

# เปิด Jupyter
jupyter notebook

# Export notebook เป็น HTML
jupyter nbconvert --to html notebook.ipynb

# Export notebook เป็น Python script
jupyter nbconvert --to python notebook.ipynb
```
