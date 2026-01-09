@echo off
echo Applying 019_create_budget_allocations.sql...
mysql -hlocalhost -uroot hr_budget < database\migrations\019_create_budget_allocations.sql
if %ERRORLEVEL% neq 0 (
    echo Error running 019_create_budget_allocations.sql
    exit /b 1
)

echo Applying 020_seed_organizations_hierarchy.sql...
mysql -hlocalhost -uroot hr_budget < database\seeds\020_seed_organizations_hierarchy.sql
if %ERRORLEVEL% neq 0 (
    echo Error running 020_seed_organizations_hierarchy.sql
    exit /b 1
)

echo Fix applied successfully.
exit /b 0
