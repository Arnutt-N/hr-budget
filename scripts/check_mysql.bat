@echo off
echo ====================================
echo MySQL Connection Diagnostic Script
echo ====================================
echo.

echo Step 1: Check if port 3306 is listening...
netstat -an | findstr :3306
if %errorlevel% neq 0 (
    echo [ERROR] Port 3306 is NOT listening. MySQL may not be running.
    echo Please start MySQL from Laragon Control Panel.
    pause
    exit /b 1
)
echo [OK] Port 3306 is listening.
echo.

echo Step 2: Test MySQL connection...
mysql -u root -e "SELECT 'Connection OK' AS status;"
if %errorlevel% neq 0 (
    echo [ERROR] Cannot connect to MySQL.
    echo Please check:
    echo - MySQL service is running in Laragon
    echo - Root password is empty (default)
    pause
    exit /b 1
)
echo [OK] MySQL connection successful.
echo.

echo Step 3: Check if hr_budget database exists...
mysql -u root -e "SHOW DATABASES LIKE 'hr_budget';"
if %errorlevel% neq 0 (
    echo [ERROR] Cannot query databases.
    pause
    exit /b 1
)
echo [OK] Database check complete.
echo.

echo ====================================
echo Diagnostic Complete!
echo ====================================
pause
