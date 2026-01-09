import mysql.connector
import sys
sys.path.append('.')
from db_config import get_db_connection

conn = get_db_connection()
cursor = conn.cursor()

cursor.execute("DESCRIBE budget_category_items")
print("budget_category_items Schema:")
for row in cursor.fetchall():
    print(f"  {row[0]} | {row[1]}")

cursor.close()
conn.close()
