@echo off
echo ======================================
echo Executing Migration 022
echo ======================================
echo.

echo Waiting for MySQL to be ready...
timeout /t 5 /nobreak > nul

echo Testing connection...
mysql -u root -e "SELECT 'Ready' AS status;" 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] MySQL is not responding yet.
    echo Please wait a moment and try again.
    pause
    exit /b 1
)
echo [OK] MySQL is ready!
echo.

echo Running migration file...
mysql -u root hr_budget < database\migrations\022_add_hierarchy_to_category_items.sql
if %errorlevel% neq 0 (
    echo [ERROR] Migration failed!
    pause
    exit /b 1
)
echo [OK] Migration executed successfully!
echo.

echo Verifying table structure...
mysql -u root hr_budget -e "DESCRIBE budget_category_items;"
echo.

echo ======================================
echo Migration Complete!
echo ======================================
echo.
echo Next step: Run the seeder with:
echo   php scripts\seed_budget_hierarchy.php
echo.
pause
