@echo off
title JES Project - Server (leave this window open)
cd /d "%~dp0"

REM Same PHP order as start-server.bat: real php.exe first (not php.bat)
set "PHP_EXE="
for %%v in (84 83 82 81 80) do (
  if exist "%USERPROFILE%\.config\herd\bin\php%%v\php.exe" set "PHP_EXE=%USERPROFILE%\.config\herd\bin\php%%v\php.exe" & goto :found
)
where php >nul 2>&1 && for /f "tokens=*" %%i in ('where php 2^>nul') do set "PHP_EXE=%%i" & goto :found
:found
if not defined PHP_EXE (
  echo PHP not found. Install Laravel Herd or add PHP to PATH.
  pause
  exit /b 1
)

echo.
echo Server: http://127.0.0.1:8000  ^|  WiFi: http://YOUR_PC_IP:8000
echo Leave this window open. Close it to stop the server.
echo.
"%PHP_EXE%" artisan serve --host=0.0.0.0 --port=8000
pause
