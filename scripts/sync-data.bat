@echo off
cd /d "C:\Users\SoftClansUser\Documents\Task\HSI_Dashboard_Admin\HsiDashboard\HsiDashboard"

echo Starting data sync at %date% %time%

php artisan data:sync --auto

if %ERRORLEVEL% EQU 0 (
    echo Sync completed successfully at %date% %time%
) else (
    echo Sync failed at %date% %time%
    exit /b 1
)

echo Data sync process finished.
