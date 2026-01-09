import pandas as pd
import mysql.connector
from db_config import get_db_config
import sys

def clean_data():
    config = get_db_config()
    
    try:
        print(f"Connecting to database {config['database']}...", flush=True)
        conn = mysql.connector.connect(
            host=config['host'],
            user=config['user'],
            password=config['password'],
            database=config['database'],
            port=config['port']
        )
    except Exception as e:
        print(f"Error connecting to MySQL: {e}", flush=True)
        sys.exit(1)

    try:
        cursor = conn.cursor(dictionary=True)

        # 1. Fetch Organizations
        print("Fetching organizations...", flush=True)
        cursor.execute("SELECT id, parent_id FROM organizations")
        orgs = cursor.fetchall()
        parent_map = {org['id']: org['parent_id'] for org in orgs}

        def get_ancestors(org_id):
            ancestors = []
            curr = org_id
            while curr in parent_map and parent_map[curr] is not None:
                curr = parent_map[curr]
                ancestors.append(curr)
            return ancestors

        # 2. Fetch Allocations
        print("Fetching budget_allocations...", flush=True)
        # Using organization_id as per schema check
        cursor.execute("SELECT organization_id, plan_id, fiscal_year FROM budget_allocations")
        allocs = cursor.fetchall()
        df_allocs = pd.DataFrame(allocs)

        # 3. Fetch Line Items
        print("Fetching budget_line_items...", flush=True)
        # Using division_id as organization_id as per schema check
        cursor.execute("SELECT id, fiscal_year, division_id AS organization_id, plan_id, project_id, activity_id, allocated_pba, allocated_received, disbursed FROM budget_line_items")
        bli = cursor.fetchall()
        df_bli = pd.DataFrame(bli)

        if df_bli.empty:
            print("No budget line items found.", flush=True)
            return

        print(f"Total line items: {len(df_bli)}", flush=True)

        # 4. Simple mapping logic: Use only records that belong to this specific division
        print("Processing mappings...", flush=True)
        final_official_mappings = []

        # Group by division and fiscal year
        grouped = df_bli.groupby(['organization_id', 'fiscal_year'])

        for (org_id, fy), group in grouped:
            # For each organization, include ALL plans/activities that have records
            # This ensures we only show plans that were actually assigned to this division
            for _, row in group.iterrows():
                final_official_mappings.append(row)

        # 5. Save
        if final_official_mappings:
            df_final = pd.DataFrame(final_official_mappings).drop_duplicates(['fiscal_year', 'organization_id', 'activity_id'])
            
            print(f"Total official mappings identified: {len(df_final)}", flush=True)
            
            cursor.execute("TRUNCATE TABLE source_of_truth_mappings")
            
            insert_sql = """
            INSERT INTO source_of_truth_mappings 
            (fiscal_year, organization_id, plan_id, project_id, activity_id, is_official, source) 
            VALUES (%s, %s, %s, %s, %s, 1, 'python_etl')
            """
            
            records = []
            for _, r in df_final.iterrows():
                records.append((int(r['fiscal_year']), int(r['organization_id']), int(r['plan_id']), int(r['project_id']), int(r['activity_id'])))
            
            cursor.executemany(insert_sql, records)
            conn.commit()
            print("Successfully updated source_of_truth_mappings.", flush=True)

    except Exception as e:
        print(f"Error: {e}", flush=True)
        conn.rollback()
    finally:
        cursor.close()
        conn.close()

if __name__ == "__main__":
    clean_data()
