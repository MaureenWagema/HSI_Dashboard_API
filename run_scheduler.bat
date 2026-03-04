@echo off
cd /d "c:\xampp\htdocs\HSI_Dashboard_API"
php artisan schedule:run >> storage\logs\scheduler.log 2>&1
