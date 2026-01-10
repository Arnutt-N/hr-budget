"""
Budget Categories & Request Form Data Analysis
วิเคราะห์โครงสร้างข้อมูลสำหรับหน้า /requests/{id}/edit
Date: 2026-01-10
"""

import sys
import io

# Fix Windows console encoding
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

import pandas as pd
sys.path.append('.')
from db_config import get_db_config
import mysql.connector

def get_db_connection():
    """Get read-only database connection."""
    config = get_db_config()
    return mysql.connector.connect(
        host=config['host'],
        user=config['user'],
        password=config['password'],
        database=config['database'],
        charset='utf8mb4'
    )

def analyze_budget_categories():
    """วิเคราะห์ตาราง budget_categories"""
    conn = get_db_connection()
    
    print("=" * 70)
    print("📊 1. BUDGET_CATEGORIES - โครงสร้างหมวดงบประมาณ")
    print("=" * 70)
    
    # All categories
    df = pd.read_sql("""
        SELECT id, name_th, level, parent_id, code, is_active, sort_order
        FROM budget_categories
        ORDER BY sort_order, level, id
    """, conn)
    
    print(f"\n✅ Total rows: {len(df)}")
    print(f"✅ Columns: {list(df.columns)}")
    print("\n📋 All Categories:")
    print(df.to_string(index=False))
    
    # Level distribution
    print("\n\n📊 Level Distribution:")
    level_counts = df.groupby('level').size().reset_index(name='count')
    print(level_counts.to_string(index=False))
    
    # Root level (tabs)
    print("\n\n🏷️ Level 0 (Root):")
    print(df[df['level'] == 0][['id', 'name_th', 'code']].to_string(index=False))
    
    # Level 1 (Tabs: งบบุคลากร, งบดำเนินงาน)
    print("\n\n🏷️ Level 1 (Tabs - งบหลัก):")
    print(df[df['level'] == 1][['id', 'name_th', 'parent_id']].to_string(index=False))
    
    # Tree structure
    print("\n\n🌳 Hierarchical Tree:")
    root_id = df[df['level'] == 0]['id'].iloc[0] if len(df[df['level'] == 0]) > 0 else None
    
    def print_tree(parent_id, indent=0):
        children = df[df['parent_id'] == parent_id]
        for _, row in children.iterrows():
            prefix = "  " * indent + ("├─ " if indent > 0 else "")
            print(f"{prefix}[{row['id']}] {row['name_th']} (Level {row['level']})")
            print_tree(row['id'], indent + 1)
    
    if root_id:
        root_row = df[df['id'] == root_id].iloc[0]
        print(f"[{root_row['id']}] {root_row['name_th']} (Level 0 - Root)")
        print_tree(root_id, 1)
    
    conn.close()
    return df

def analyze_budget_category_items():
    """วิเคราะห์ตาราง budget_category_items (ถ้ามี)"""
    conn = get_db_connection()
    
    print("\n\n" + "=" * 70)
    print("📊 2. BUDGET_CATEGORY_ITEMS - รายการย่อย")
    print("=" * 70)
    
    try:
        df = pd.read_sql("""
            SELECT id, name, category_id, parent_id, level, is_active
            FROM budget_category_items
            ORDER BY category_id, sort_order, id
            LIMIT 50
        """, conn)
        
        print(f"\n✅ Total rows: {len(df)}")
        if len(df) > 0:
            print(df.to_string(index=False))
        else:
            print("⚠️ ตารางว่างเปล่า - ไม่มีข้อมูล")
            
    except Exception as e:
        print(f"❌ Error: {e}")
    
    conn.close()

def analyze_budget_request_items():
    """วิเคราะห์ตาราง budget_request_items (รายการที่บันทึก)"""
    conn = get_db_connection()
    
    print("\n\n" + "=" * 70)
    print("📊 3. BUDGET_REQUEST_ITEMS - รายการที่ผู้ใช้บันทึก")
    print("=" * 70)
    
    try:
        df = pd.read_sql("""
            SELECT id, budget_request_id, category_item_id, item_name, 
                   quantity, unit_price, remark
            FROM budget_request_items
            ORDER BY budget_request_id, id
            LIMIT 50
        """, conn)
        
        print(f"\n✅ Total rows: {len(df)}")
        if len(df) > 0:
            print(df.to_string(index=False))
        else:
            print("⚠️ ตารางว่างเปล่า - ยังไม่มีการบันทึกข้อมูล")
            
    except Exception as e:
        print(f"❌ Error: {e}")
    
    conn.close()

def analyze_organizations():
    """วิเคราะห์ตาราง organizations"""
    conn = get_db_connection()
    
    print("\n\n" + "=" * 70)
    print("📊 4. ORGANIZATIONS - หน่วยงาน")
    print("=" * 70)
    
    df = pd.read_sql("""
        SELECT id, name_th, type, parent_id, is_active
        FROM organizations
        ORDER BY parent_id, id
        LIMIT 20
    """, conn)
    
    print(f"\n✅ Total rows: {len(df)}")
    print(df.to_string(index=False))
    
    conn.close()

def summary():
    """สรุปผล"""
    print("\n\n" + "=" * 70)
    print("📋 สรุป: ข้อมูลที่ใช้ในหน้า /requests/{id}/edit")
    print("=" * 70)
    print("""
    ┌──────────────────────────────────────────────────────────────────┐
    │ ตาราง                   │ Purpose                              │
    ├──────────────────────────────────────────────────────────────────┤
    │ budget_requests         │ ข้อมูลคำขอหลัก (ID, สถานะ, วงเงิน)   │
    │ organizations           │ ข้อมูลหน่วยงาน                       │
    │ budget_categories       │ โครงสร้าง Hierarchy (Tab + Items)    │
    │ budget_request_items    │ รายการที่ผู้ใช้บันทึกไว้             │
    └──────────────────────────────────────────────────────────────────┘
    
    📌 หมายเหตุ:
    - budget_categories มี parent_id เพื่อสร้าง Tree
    - Level 0 = Root (รายการค่าใช้จ่ายบุคลากรภาครัฐ)
    - Level 1 = Tabs (งบบุคลากร, งบดำเนินงาน)
    - Level 2+ = Sub-items
    """)

def main():
    print("\n" + "🔍" * 35)
    print("  DATABASE SCHEMA ANALYSIS")
    print("  หน้า: /requests/{id}/edit")
    print("🔍" * 35)
    
    analyze_budget_categories()
    analyze_budget_category_items()
    analyze_budget_request_items()
    analyze_organizations()
    summary()

if __name__ == "__main__":
    main()
