@echo off
echo ==========================================
echo HR Budget System - Phase 3 Test Runner
echo ==========================================

echo [0/4] Checking Environment...
php -v
echo.
echo NOTE: If you see PHP 7.x above, please RESTART your terminal/CMD.
echo Laragon changes do not affect open windows.
echo.

echo [1/4] Installing PHP Dependencies...
call composer update
call composer dump-autoload
if %errorlevel% neq 0 (
    echo Error installing PHP dependencies.
    pause
    exit /b %errorlevel%
)

echo [2/4] Installing Node Dependencies...
echo Current Node Version:
node -v
echo NOTE: Playwright requires Node 18.19+. If you see an older version, please run 'nvm use 22.17.0' in a NEW terminal.
call npm install
call npx playwright install --with-deps
if %errorlevel% neq 0 (
    echo Error installing Node dependencies.
    pause
    exit /b %errorlevel%
)

echo [3/4] Seeding Test Data...
php scripts/seed_test_data.php
if %errorlevel% neq 0 (
    echo Error seeding test data.
    pause
    exit /b %errorlevel%
)

echo [4/4] Running Tests...
echo.
echo === Unit Tests ===
call vendor\bin\phpunit --testsuite Unit
echo.
echo === Integration Tests ===
call vendor\bin\phpunit --testsuite Integration
echo.
echo === E2E Tests ===
call npx playwright test

echo.
echo ==========================================
echo Testing Completed.
echo ==========================================
pause
