@echo off
cd /d c:\laragon\www\hr_budget
echo Running seeder...
php scripts/seed_exact_items.php
echo.
echo Done.
pause
