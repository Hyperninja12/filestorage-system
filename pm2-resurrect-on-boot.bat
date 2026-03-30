@echo off
REM Run at Windows login to start the JES app (after "pm2 start ecosystem.config.cjs" and "pm2 save" once).
cd /d "%~dp0"
pm2 resurrect 2>nul
exit
