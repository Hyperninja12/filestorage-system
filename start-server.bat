@echo off
setlocal EnableExtensions
title JES Project - Laravel Server
cd /d "%~dp0"

REM Double-click has a small PATH — add common Node / npm global locations so "pm2" is found
set "PATH=%APPDATA%\npm;%LOCALAPPDATA%\Programs\nodejs;%ProgramFiles%\nodejs;%PATH%"

REM So PM2/Node can find PHP — use Herd php.exe first (NOT php.bat: Node cannot spawn .bat with shell:false)
set "PHP_BINARY="
for %%v in (84 83 82 81 80) do (
  if exist "%USERPROFILE%\.config\herd\bin\php%%v\php.exe" set "PHP_BINARY=%USERPROFILE%\.config\herd\bin\php%%v\php.exe" & goto :php_found
)
where php >nul 2>&1 && for /f "tokens=*" %%i in ('where php 2^>nul') do set "PHP_BINARY=%%i" & goto :php_found
:php_found

where pm2 >nul 2>&1
if errorlevel 1 (
  echo [ERROR] PM2 is not in PATH.
  echo Install once in CMD:  npm install -g pm2
  echo Then run this batch again ^(or log out and log in so PATH updates^).
  echo.
  pause
  exit /b 1
)

echo Starting Laravel server in background (PM2)...
call pm2 delete jesproject 2>nul
call pm2 start ecosystem.config.cjs
if errorlevel 1 (
  echo [ERROR] pm2 start failed.
  echo Try in this folder:  pm2 start ecosystem.config.cjs
  echo.
  pause
  exit /b 1
)
call pm2 save 2>nul

REM Wait for Laravel to bind 8000 ^(can take 5–30s on first boot; netstat text varies by locale^)
set "PORT_OK=0"
echo Waiting for port 8000 ^(up to 45 seconds^)...
for /L %%n in (1,1,45) do (
  powershell -NoProfile -Command "try { $c = New-Object System.Net.Sockets.TcpClient; $c.Connect('127.0.0.1',8000); $c.Close(); exit 0 } catch { exit 1 }" >nul 2>&1
  if not errorlevel 1 (
    set "PORT_OK=1"
    goto :port_ok
  )
  timeout /t 1 /nobreak >nul
)
:port_ok
if "%PORT_OK%"=="0" (
  echo [ERROR] Port 8000 did not open. PHP may have failed to start.
  echo.
  echo Check:  type "%~dp0storage\logs\serve-pm2.log"
  echo Or run:  cd /d "%~dp0"
  echo           pm2 logs jesproject --lines 40
  echo.
  call pm2 list
  echo.
  pause
  exit /b 1
)

REM Let other devices on WiFi/LAN reach port 8000 (needs Admin; harmless if it fails)
netsh advfirewall firewall delete rule name="JES Project (port 8000)" >nul 2>&1
netsh advfirewall firewall add rule name="JES Project (port 8000)" dir=in action=allow protocol=TCP localport=8000 profile=any >nul 2>&1
if errorlevel 1 (
  echo NOTE: Firewall rule not added ^(run as Administrator once, or run allow-port-8000-firewall.bat as Admin^).
) else (
  echo Firewall: port 8000 allowed for other devices on this network.
)

echo.
echo OK — Server is listening on 0.0.0.0:8000 ^(this PC + WiFi/LAN^).
echo   On this PC:    http://127.0.0.1:8000
echo   Other devices: http://THIS_PC_IP:8000  ^(IPv4 below^)
echo.
echo This PC IPv4 addresses:
ipconfig | findstr /i "IPv4"
echo.
echo If the browser still fails:  pm2 logs jesproject
echo If others on WiFi cannot connect: run allow-port-8000-firewall.bat as Administrator ^(once^).
echo.
echo Press any key to close this window ^(the server keeps running in the background^).
pause >nul
exit /b 0
