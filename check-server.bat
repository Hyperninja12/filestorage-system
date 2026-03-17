@echo off
cd /d "%~dp0"
echo === 1. Is anything listening on port 8000? ===
netstat -an | findstr ":8000"
echo.
echo === 2. PM2 status (run "pm2 list" and "pm2 logs jesproject" if needed) ===
call pm2 list 2>nul
echo.
echo === 3. Firewall rule for port 8000? (look for "JES Project" or "8000") ===
netsh advfirewall firewall show rule name=all | findstr /i "8000 JES"
echo.
echo === 4. Quick HTTP test (does 127.0.0.1:8000 respond?) ===
powershell -NoProfile -Command "try { $r = Invoke-WebRequest -Uri 'http://127.0.0.1:8000' -UseBasicParsing -TimeoutSec 5; echo 'OK - Server responded with status' $r.StatusCode } catch { echo 'FAIL -' $_.Exception.Message }"
echo.
echo === 5. Open in browser on THIS PC ===
echo    http://127.0.0.1:8000
echo If test above is OK but 192.168.8.102:8000 fails from other device, the problem is firewall/network.
echo.
pause
