import mysql.connector

try:
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="hr_budget"
    )
    cursor = conn.cursor()
    cursor.execute("SELECT id, name_th FROM expense_types ORDER BY id")
    rows = cursor.fetchall()
    
    print("ID | Name")
    print("-" * 30)
    for row in rows:
        print(f"{row[0]} | {row[1]}")
        
except Exception as e:
    print(f"Error: {e}")
finally:
    if 'conn' in locals() and conn.is_connected():
        conn.close()
