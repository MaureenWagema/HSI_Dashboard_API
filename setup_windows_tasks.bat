@echo off
title Setup Windows Tasks for HSI Sync
color 0A

echo ========================================
echo SETUP WINDOWS TASKS FOR HSI SYNC
echo ========================================
echo.

echo This will create Windows Task Scheduler tasks for:
echo 1. TAT Data Sync (Daily at 2:30 AM)
echo 2. Actual Premiums Sync (Daily at 3:00 AM)
echo 3. Account Mappings Sync (Daily at 2:00 AM)
echo 4. Current Month Data Sync (Daily at 1:00 AM)
echo.

pause

echo Creating Windows Tasks...
echo.

cd /d "c:\xampp\htdocs\HSI_Dashboard_API"

:: Create TAT Data Sync Task
echo [1/4] Creating TAT Data Sync task (2:30 AM daily)...
schtasks /create /tn "HSI TAT Data Sync" /tr "php artisan sync:tat-data" /sc daily /st 02:30 /sd "c:\xampp\htdocs\HSI_Dashboard_API" /f

:: Create Actual Premiums Sync Task
echo [2/4] Creating Actual Premiums Sync task (3:00 AM daily)...
schtasks /create /tn "HSI Actual Premiums Sync" /tr "php artisan sync:actual-premiums" /sc daily /st 03:00 /sd "c:\xampp\htdocs\HSI_Dashboard_API" /f

:: Create Account Mappings Sync Task
echo [3/4] Creating Account Mappings Sync task (2:00 AM daily)...
schtasks /create /tn "HSI Account Mappings Sync" /tr "php artisan sync:account-mappings" /sc daily /st 02:00 /sd "c:\xampp\htdocs\HSI_Dashboard_API" /f

:: Create Current Month Data Sync Task
echo [4/4] Creating Current Month Data Sync task (1:00 AM daily)...
schtasks /create /tn "HSI Current Month Data Sync" /tr "php artisan data:sync --auto" /sc daily /st 01:00 /sd "c:\xampp\htdocs\HSI_Dashboard_API" /f

echo.
echo ========================================
echo TASKS CREATED SUCCESSFULLY!
echo ========================================
echo.
echo Created tasks:
echo - HSI TAT Data Sync (2:30 AM daily)
echo - HSI Actual Premiums Sync (3:00 AM daily)
echo - HSI Account Mappings Sync (2:00 AM daily)
echo - HSI Current Month Data Sync (1:00 AM daily)
echo.
echo To test a task immediately:
echo schtasks /run /tn "HSI TAT Data Sync"
echo.
echo To view all tasks:
echo schtasks /query | findstr HSI
echo.
echo To delete a task:
echo schtasks /delete /tn "Task Name" /f
echo.
pause
