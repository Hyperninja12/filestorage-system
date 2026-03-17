@echo off
REM Run this at Windows login to start the JES app (after you've run "pm2 start ecosystem.config.cjs" and "pm2 save" once).
cd /d "%~dp0"
pm2 resurrect 2>nul
exit
