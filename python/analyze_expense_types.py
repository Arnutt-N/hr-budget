# -*- coding: utf-8 -*-
import mysql.connector
from dotenv import load_dotenv
import os
import sys

# Setup encoding
sys.stdout.reconfigure(encoding='utf-8')

# Load .env
load_dotenv(dotenv_path='C:/laragon/www/hr_budget/.env')

conn = mysql.connector.connect(
    host=os.getenv('DB_HOST', 'localhost'),
    user=os.getenv('DB_USERNAME', 'root'),
    password=os.getenv('DB_PASSWORD', ''),
    database=os.getenv('DB_DATABASE', 'hr_budget'),
    charset='utf8mb4'
)
cursor = conn.cursor(dictionary=True)

print("=== EXPENSE TYPES AND THEIR ITEMS ===\n")

# Get all expense types
cursor.execute("SELECT id, name_th FROM expense_types WHERE is_active=1 ORDER BY sort_order")
types = cursor.fetchall()

for t in types:
    print(f"TYPE {t['id']}: {t['name_th']}")
    print("-" * 50)
    
    # Get groups for this type
    cursor.execute(
        "SELECT id, name_th FROM expense_groups WHERE expense_type_id=%s AND is_active=1 ORDER BY sort_order",
        (t['id'],)
    )
    groups = cursor.fetchall()
    
    for g in groups:
        print(f"  [Group] {g['name_th']}")
        
        # Get TOP-LEVEL items (parent_id IS NULL)
        cursor.execute(
            "SELECT id, name_th FROM expense_items WHERE expense_group_id=%s AND deleted_at IS NULL AND parent_id IS NULL ORDER BY sort_order",
            (g['id'],)
        )
        items = cursor.fetchall()
        
        for i in items:
            print(f"    * {i['name_th']}")
            
            # Get children
            cursor.execute(
                "SELECT id, name_th FROM expense_items WHERE parent_id=%s AND deleted_at IS NULL ORDER BY sort_order LIMIT 5",
                (i['id'],)
            )
            children = cursor.fetchall()
            for c in children:
                print(f"      - {c['name_th']}")
    print()

cursor.close()
conn.close()
