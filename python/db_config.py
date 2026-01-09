import os

def load_env(file_path="../.env"):
    """Simple .env loader as a fallback for python-dotenv."""
    if not os.path.exists(file_path):
        return {}
    
    env_vars = {}
    with open(file_path, "r", encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if not line or line.startswith("#"):
                continue
            if "=" in line:
                key, value = line.split("=", 1)
                # Remove quotes if present
                value = value.strip().strip('"').strip("'")
                env_vars[key.strip()] = value
    return env_vars

def get_db_config():
    # Try to load from .env
    env = load_env()
    
    return {
        'host': env.get('DB_HOST', 'localhost'),
        'user': env.get('DB_USERNAME', 'root'),
        'password': env.get('DB_PASSWORD', ''),
        'database': env.get('DB_DATABASE', 'hr_budget'),
        'port': env.get('DB_PORT', '3306')
    }

if __name__ == "__main__":
    config = get_db_config()
    print(f"Database Config: {config['host']}@{config['database']}")
