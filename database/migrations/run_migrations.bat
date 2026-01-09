@echo off
REM ==============================================================================
REM HR Budget System - Production Deployment Migration Script (Windows)
REM ==============================================================================
REM Version: 1.0
REM Date: 2025-12-15
REM Description: Apply all pending database migrations to production
REM ==============================================================================

echo ===============================================
echo  HR Budget - Database Migration Script
echo ===============================================
echo.

REM Configuration (CHANGE THESE FOR PRODUCTION)
set DB_HOST=localhost
set DB_NAME=hr_budget
set DB_USER=your_production_user
set DB_PASS=your_production_pass

REM Migrations directory
set MIGRATIONS_DIR=database\migrations

echo WARNING: This will apply migrations to: %DB_NAME%
set /p confirm="Continue? (yes/no): "

if /i not "%confirm%"=="yes" (
    echo Migration cancelled.
    exit /b 0
)

echo.
echo Starting migrations...
echo.

REM Apply migrations in order
echo Applying 001_create_personnel_types.sql...
mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% %DB_NAME% < %MIGRATIONS_DIR%\001_create_personnel_types.sql
if %ERRORLEVEL% neq 0 goto error

echo Applying 002_create_files.sql...
mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% %DB_NAME% < %MIGRATIONS_DIR%\002_create_files.sql
if %ERRORLEVEL% neq 0 goto error

echo Applying 003_alter_users.sql...
mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% %DB_NAME% < %MIGRATIONS_DIR%\003_alter_users.sql
if %ERRORLEVEL% neq 0 goto error

echo Applying 004_create_fiscal_years.sql...
mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% %DB_NAME% < %MIGRATIONS_DIR%\004_create_fiscal_years.sql
if %ERRORLEVEL% neq 0 goto error

echo Applying 007_create_budget_records.sql...
mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% %DB_NAME% < %MIGRATIONS_DIR%\007_create_budget_records.sql
if %ERRORLEVEL% neq 0 goto error

echo.
echo ===============================================
echo All migrations completed successfully!
echo ===============================================
exit /b 0

:error
echo.
echo ===============================================
echo ERROR: Migration failed!
echo ===============================================
exit /b 1
