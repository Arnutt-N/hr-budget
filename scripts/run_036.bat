@echo off
mysql -u root -h localhost hr_budget < database\migrations\036_update_budget_trackings_references.sql > migration_036_output.txt 2>&1
echo Done.
