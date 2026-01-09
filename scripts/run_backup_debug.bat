@echo off
echo Starting Backup %DATE% %TIME% > backup_debug.txt
robocopy "c:\laragon\www\hr_budget" "c:\laragon\www\hr_budget\archives\backup\hr_budget_ui_refine_tracking_list_20260105" /E /XD node_modules vendor .git archives .gemini .agent /XF *.log >> backup_debug.txt 2>&1
echo Exit Code: %ERRORLEVEL% >> backup_debug.txt
echo Done. >> backup_debug.txt
