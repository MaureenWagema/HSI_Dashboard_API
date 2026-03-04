@echo off
title Server Sync Setup - HSI Dashboard
color 0C

echo ========================================
echo SERVER SYNC SETUP - HSI Dashboard
echo ========================================
echo.
echo Setting up automated syncs on Windows Server...
echo.

cd /d "C:\path\to\your\project"  # UPDATE THIS PATH

echo Creating server tasks with server-appropriate settings...
echo.

:: Create TAT Data Sync Task (Server settings)
echo [1/4] Creating TAT Data Sync task (2:30 AM daily)...
schtasks /create /tn "HSI TAT Data Sync" /tr "C:\php\php.exe artisan sync:tat-data" /sc daily /st 02:30 /sd "C:\path\to\your\project" /ru "SYSTEM" /f

:: Create Actual Premiums Sync Task
echo [2/4] Creating Actual Premiums Sync task (3:00 AM daily)...
schtasks /create /tn "HSI Actual Premiums Sync" /tr "C:\php\php.exe artisan sync:actual-premiums" /sc daily /st 03:00 /sd "C:\path\to\your\project" /ru "SYSTEM" /f

:: Create Account Mappings Sync Task
echo [3/4] Creating Account Mappings Sync task (2:00 AM daily)...
schtasks /create /tn "HSI Account Mappings Sync" /tr "C:\php\php.exe artisan sync:account-mappings" /sc daily /st 02:00 /sd "C:\path\to\your\project" /ru "SYSTEM" /f

:: Create Current Month Data Sync Task
echo [4/4] Creating Current Month Data Sync task (1:00 AM daily)...
schtasks /create /tn "HSI Current Month Data Sync" /tr "C:\php\php.exe artisan data:sync --auto" /sc daily /st 01:00 /sd "C:\path\to\your\project" /ru "SYSTEM" /f

echo.
echo ========================================
echo SERVER TASKS CREATED SUCCESSFULLY!
echo ========================================
echo.
echo Server tasks created with SYSTEM account permissions
echo Tasks will run even if no user is logged in
echo.
echo To test tasks:
echo schtasks /run /tn "HSI TAT Data Sync"
echo.
echo To view tasks:
echo schtasks /query | findstr HSI
echo.
pause
