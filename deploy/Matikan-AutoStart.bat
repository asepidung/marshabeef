@echo off
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0tools\shortcut.ps1" -Action Remove
echo.
pause
