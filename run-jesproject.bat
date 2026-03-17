@echo off
cd /d "%~dp0"
REM #region agent log
powershell -NoProfile -Command "$cwd=(Get-Location).Path; $php=(where.exe php 2>$null)|Select-Object -First 1; $data=@{cwd=$cwd;phpFound=($null -ne $php);phpPath=$php}; $o=@{sessionId='95891a';hypothesisId='B';location='run-jesproject.bat';message='batch_started';data=$data;timestamp=[long](((Get-Date)-([datetime]'1970-01-01')).TotalMilliseconds)}; Add-Content -Path 'c:\laravel-projects\jesproject\debug-95891a.log' -Value ($o|ConvertTo-Json -Depth 3 -Compress)"
powershell -NoProfile -Command "$o=@{sessionId='95891a';hypothesisId='D';location='run-jesproject.bat';message='about_to_invoke_php';data=@{};timestamp=[long](((Get-Date)-([datetime]'1970-01-01')).TotalMilliseconds)}; Add-Content -Path 'c:\laravel-projects\jesproject\debug-95891a.log' -Value ($o|ConvertTo-Json -Compress)"
REM #endregion
php artisan serve --host=0.0.0.0 --port=8000
