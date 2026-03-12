@echo off
title Fix APP_KEY
color 0C

echo ========================================
echo FIXING LARAVEL APP_KEY
echo ========================================
echo.

cd /d "D:\Xammp\htdocs\HsiDashboard"

echo [1/3] Backing up current .env...
copy .env .env.backup >nul

echo [2/3] Generating new APP_KEY...
php artisan key:generate --force

echo [3/3] Verifying the key...
php artisan key:check
echo.

echo [4/4] Clearing caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo.
echo ========================================
echo APP_KEY FIX COMPLETE
echo ========================================
echo.
echo New APP_KEY has been generated and caches cleared.
echo Now test your API endpoints again.
echo.
pause
