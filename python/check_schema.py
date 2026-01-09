import mysql.connector
from db_config import get_db_config

config = get_db_config()
conn = mysql.connector.connect(**config)
cursor = conn.cursor()

for table in ['budget_allocations', 'budget_line_items']:
    print(f"\n--- {table} ---")
    cursor.execute(f"DESCRIBE {table}")
    for row in cursor.fetchall():
        print(row[0])

conn.close()
