
import mysql.connector
import os
from dotenv import load_dotenv

# Load .env
load_dotenv('C:\\laragon\\www\\hr_budget\\.env')

def get_table_schema(table_name):
    try:
        conn = mysql.connector.connect(
            host=os.getenv('DB_HOST'),
            user=os.getenv('DB_USERNAME'),
            password=os.getenv('DB_PASSWORD'),
            database=os.getenv('DB_DATABASE')
        )
        cursor = conn.cursor()
        cursor.execute(f"DESCRIBE {table_name}")
        columns = cursor.fetchall()
        print(f"--- Schema for {table_name} ---")
        for col in columns:
            print(f"{col[0]} ({col[1]})")
        conn.close()
    except Exception as e:
        print(f"Error describing {table_name}: {e}")

get_table_schema('budget_request_items')
get_table_schema('expense_items')
get_table_schema('budget_category_items')
