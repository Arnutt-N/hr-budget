@echo off
mysql -u root -h localhost hr_budget < database\migrations\018_enhance_organizations.sql > migration18_output.txt 2>&1
echo Done.
