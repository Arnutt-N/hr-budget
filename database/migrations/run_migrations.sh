#!/bin/bash
# ==============================================================================
# HR Budget System - Production Deployment Migration Script
# ==============================================================================
# Version: 1.0
# Date: 2025-12-15
# Description: Apply all pending database migrations to production
# ==============================================================================

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration (CHANGE THESE FOR PRODUCTION)
DB_HOST="localhost"
DB_NAME="hr_budget"
DB_USER="your_production_user"
DB_PASS="your_production_pass"

# Migrations directory
MIGRATIONS_DIR="database/migrations"

echo "==============================================="
echo " HR Budget - Database Migration Script"
echo "==============================================="
echo ""

# Check if mysql command exists
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}Error: mysql command not found${NC}"
    exit 1
fi

# Confirm before proceeding
echo -e "${YELLOW}⚠️  WARNING: This will apply migrations to: ${DB_NAME}${NC}"
read -p "Continue? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "Migration cancelled."
    exit 0
fi

echo ""
echo "Starting migrations..."
echo ""

# Function to run migration
run_migration() {
    local file=$1
    local name=$(basename "$file")
    
    echo -n "Applying $name... "
    
    if mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$file" 2>/dev/null; then
        echo -e "${GREEN}✓ SUCCESS${NC}"
        return 0
    else
        echo -e "${RED}✗ FAILED${NC}"
        return 1
    fi
}

# Apply migrations in order
migrations=(
    "001_create_personnel_types.sql"
    "002_create_files.sql"
    "003_alter_users.sql"
    "004_create_fiscal_years.sql"
    "007_create_budget_records.sql"
)

failed=0

for migration in "${migrations[@]}"; do
    migration_file="$MIGRATIONS_DIR/$migration"
    
    if [ ! -f "$migration_file" ]; then
        echo -e "${RED}Error: Migration file not found: $migration_file${NC}"
        failed=$((failed + 1))
        continue
    fi
    
    if ! run_migration "$migration_file"; then
        failed=$((failed + 1))
    fi
done

echo ""
echo "==============================================="

if [ $failed -eq 0 ]; then
    echo -e "${GREEN}✓ All migrations completed successfully!${NC}"
    exit 0
else
    echo -e "${RED}✗ $failed migration(s) failed${NC}"
    exit 1
fi
