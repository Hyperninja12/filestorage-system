@echo off
REM Allow inbound TCP 8000 so other devices can reach the app at http://192.168.x.x:8000
REM Run this once as Administrator: right-click -> Run as administrator
netsh advfirewall firewall delete rule name="JES Project (port 8000)" 2>nul
netsh advfirewall firewall add rule name="JES Project (port 8000)" dir=in action=allow protocol=TCP localport=8000 profile=any
if %errorlevel% equ 0 (
    echo Rule added. You can now access the app from other devices at http://YOUR_PC_IP:8000
) else (
    echo Failed. Make sure you run this as Administrator.
)
pause
