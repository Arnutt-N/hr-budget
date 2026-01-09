@echo off
mysql -u root -h localhost hr_budget < database\migrations\017_drop_dimensional_tables.sql > migration_output.txt 2>&1
echo Done.
