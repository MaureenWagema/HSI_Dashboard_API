@echo off
title TAT & Actual Premiums Sync Automation
color 0A

echo ========================================
echo TAT & ACTUAL PREMIUMS SYNC
echo ========================================
echo.

cd /d "c:\xampp\htdocs\HSI_Dashboard_API"

if not exist "artisan" (
    echo ERROR: artisan file not found. Please ensure you're in the correct Laravel project directory.
    pause
    exit /b 1
)

echo Current directory: %CD%
echo.

:: Create logs directory if it doesn't exist
if not exist "storage\logs\sync" mkdir storage\logs\sync

:: Get current timestamp for logging
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set timestamp=%datetime:~0,8%_%datetime:~8,6%

echo Starting sync operations at %date% %time%
echo Log file: storage\logs\sync\premiums_tat_sync_%timestamp%.log
echo.

:: Initialize counters
set total_commands=0
set successful_commands=0
set failed_commands=0

:: Function to run a sync command
:run_sync
set command_name=%~1
set command=%~2
set log_file=storage\logs\sync\premiums_tat_sync_%timestamp%.log

set /a total_commands+=1
echo.
echo ========================================
echo Running %command_name%...
echo Command: %command%
echo ========================================

:: Execute command and log output
echo [%date% %time%] Starting %command_name%... >> "%log_file%"
php %command% >> "%log_file%" 2>&1

if %errorlevel% equ 0 (
    echo ✅ %command_name% completed successfully!
    echo [%date% %time%] %command_name% completed successfully! >> "%log_file%"
    set /a successful_commands+=1
) else (
    echo ❌ %command_name% failed with error code %errorlevel%
    echo [%date% %time%] %command_name% failed with error code %errorlevel% >> "%log_file%"
    set /a failed_commands+=1
    echo Check the log file for details: %log_file%
)

echo.
goto :eof

:: Main execution
echo.
echo Choose sync option:
echo 1. Run both TAT and Actual Premiums sync
echo 2. Actual Premiums only
echo 3. TAT data only
echo 4. Run both with detailed output (no logging)
echo.
set /p choice="Enter your choice (1-4): "

if "%choice%"=="1" goto both_sync
if "%choice%"=="2" goto actual_premiums_only
if "%choice%"=="3" goto tat_data_only
if "%choice%"=="4" goto both_sync_direct

echo Invalid choice. Running both syncs...
goto both_sync

:both_sync
echo.
echo Running BOTH sync commands with logging...
call :run_sync "Actual Premiums" "artisan sync:actual-premiums"
call :run_sync "TAT Data" "artisan sync:tat-data"
goto summary

:actual_premiums_only
echo.
echo Running Actual Premiums sync only...
call :run_sync "Actual Premiums" "artisan sync:actual-premiums"
goto summary

:tat_data_only
echo.
echo Running TAT Data sync only...
call :run_sync "TAT Data" "artisan sync:tat-data"
goto summary

:both_sync_direct
echo.
echo Running BOTH sync commands with direct output...
echo.
echo ========================================
echo Running Actual Premiums Sync...
echo ========================================
php artisan sync:actual-premiums
if %errorlevel% neq 0 (
    echo ❌ Actual premiums sync failed!
    set /a failed_commands+=1
) else (
    echo ✅ Actual premiums synced!
    set /a successful_commands+=1
)
set /a total_commands+=1

echo.
echo ========================================
echo Running TAT Data Sync...
echo ========================================
php artisan sync:tat-data
if %errorlevel% neq 0 (
    echo ❌ TAT data sync failed!
    set /a failed_commands+=1
) else (
    echo ✅ TAT data synced!
    set /a successful_commands+=1
)
set /a total_commands+=1
goto summary

:summary
echo.
echo ========================================
echo SYNC SUMMARY
echo ========================================
echo Total commands executed: %total_commands%
echo Successful commands: %successful_commands%
echo Failed commands: %failed_commands%
echo.
if "%choice%"=="4" (
    echo Direct output mode - no log file created
) else (
    echo Log file: storage\logs\sync\premiums_tat_sync_%timestamp%.log
)

if %failed_commands% gtr 0 (
    echo ⚠️  Some sync operations failed. Please check the log file for details.
    echo.
    echo Recent log entries:
    echo ----------------------------------------
    powershell "Get-Content 'storage\logs\sync\premiums_tat_sync_%timestamp%.log' | Select-Object -Last 20"
    echo ----------------------------------------
) else (
    echo 🎉 All sync operations completed successfully!
)

echo.
echo Sync operations completed at %date% %time%
echo.
echo Press any key to exit...
pause > nul

exit /b %failed_commands%
