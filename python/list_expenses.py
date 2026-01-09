import mysql.connector

try:
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="hr_budget",
        charset='utf8mb4'
    )
    cursor = conn.cursor()
    
    # Get groups for Type 2 (Operating Expenses)
    cursor.execute("SELECT id, name_th FROM expense_groups WHERE expense_type_id = 2")
    groups = cursor.fetchall()
    
    for grp in groups:
        print(f"Group: {grp[1]} (ID: {grp[0]})")
        # Get Items
        cursor.execute(f"SELECT id, name_th, level, sort_order FROM expense_items WHERE expense_group_id = {grp[0]} ORDER BY sort_order")
        items = cursor.fetchall()
        for item in items:
            print(f"  - {item[1]} (ID: {item[0]}, Lvl: {item[2]}, Sort: {item[3]})")
            
except Exception as e:
    print(f"Error: {e}")
finally:
    if 'conn' in locals() and conn.is_connected():
        conn.close()
