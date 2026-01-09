import mysql.connector
from db_config import get_db_config

config = get_db_config()
conn = mysql.connector.connect(**config)
cursor = conn.cursor()

print("--- budget_allocations columns ---")
cursor.execute("DESCRIBE budget_allocations")
for row in cursor.fetchall():
    print(row[0])

print("\n--- budget_line_items columns ---")
cursor.execute("DESCRIBE budget_line_items")
for row in cursor.fetchall():
    print(row[0])

conn.close()
