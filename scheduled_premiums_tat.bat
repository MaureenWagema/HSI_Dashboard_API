@echo off
title Scheduled Premiums & TAT Sync
color 06

:: This batch file is designed for scheduled task execution
:: Minimal output, logs to file for automated monitoring

cd /d "c:\xampp\htdocs\HSI_Dashboard_API"

:: Create log directory if not exists
if not exist "storage\logs\sync" mkdir storage\logs\sync

:: Get timestamp
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set timestamp=%datetime:~0,8%_%datetime:~8,6%
set log_file=storage\logs\sync\scheduled_premiums_tat_%timestamp%.log

echo Scheduled Premiums & TAT Sync Started: %date% %time% > "%log_file%"

:: Run Actual Premiums sync
echo. >> "%log_file%"
echo [1/2] Actual Premiums Sync >> "%log_file%"
php artisan sync:actual-premiums >> "%log_file%" 2>&1
if %errorlevel% equ 0 (
    echo SUCCESS: Actual premiums synced >> "%log_file%"
) else (
    echo ERROR: Actual premiums failed (code %errorlevel%) >> "%log_file%"
)

:: Run TAT Data sync
echo. >> "%log_file%"
echo [2/2] TAT Data Sync >> "%log_file%"
php artisan sync:tat-data >> "%log_file%" 2>&1
if %errorlevel% equ 0 (
    echo SUCCESS: TAT data synced >> "%log_file%"
) else (
    echo ERROR: TAT data failed (code %errorlevel%) >> "%log_file%"
)

echo. >> "%log_file%"
echo Scheduled Premiums & TAT Sync Completed: %date% %time% >> "%log_file%"
exit /b 0
