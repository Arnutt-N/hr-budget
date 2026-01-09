@echo off
mkdir "archives\backup\2025-12-19_final"
xcopy "src" "archives\backup\2025-12-19_final\src" /S /E /Y /I
xcopy "resources" "archives\backup\2025-12-19_final\resources" /S /E /Y /I
xcopy "routes" "archives\backup\2025-12-19_final\routes" /S /E /Y /I
xcopy "config" "archives\backup\2025-12-19_final\config" /S /E /Y /I
xcopy "database" "archives\backup\2025-12-19_final\database" /S /E /Y /I
xcopy "public" "archives\backup\2025-12-19_final\public" /S /E /Y /I
copy ".env" "archives\backup\2025-12-19_final\" /Y
copy "composer.json" "archives\backup\2025-12-19_final\" /Y
copy "package.json" "archives\backup\2025-12-19_final\" /Y
echo Backup Complete > "archives\backup\2025-12-19_final\status.txt"
