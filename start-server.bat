@echo off
title JES Project - Laravel Server
cd /d "%~dp0"

echo Starting Laravel server on http://0.0.0.0:8000
echo Other devices on your network can use: http://YOUR_PC_IP:8000
echo.
echo Press Ctrl+C to stop.
echo.

php artisan serve --host=0.0.0.0 --port=8000

pause
