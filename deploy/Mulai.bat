@echo off
setlocal
cd /d "%~dp0"
title Marsha Beef

rem Jika server sudah menyala, cukup buka aplikasinya.
powershell -NoProfile -Command "if (Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue) { exit 0 } else { exit 1 }"
if not errorlevel 1 goto siap

rem Terapkan pembaruan struktur database jika ada (aman, tidak menghapus data).
set "PHP=%~dp0php\php.exe"
if not exist "%PHP%" set "PHP=php"
"%PHP%" artisan migrate --force >nul 2>&1

rem Nyalakan server di latar belakang (tanpa jendela).
wscript //nologo "%~dp0tools\jalankan-server.vbs"

rem Tunggu sampai server siap (maksimal 30 detik).
powershell -NoProfile -Command "for ($i = 0; $i -lt 60; $i++) { if (Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue) { exit 0 }; Start-Sleep -Milliseconds 500 }; exit 1"
if errorlevel 1 (
    echo Server gagal menyala. Baca bagian "Jika bermasalah" di BACA-DULU.txt
    pause
    exit /b 1
)

:siap
rem Parameter /server = hanya menyalakan server tanpa membuka browser (dipakai saat AutoStart).
if /i "%~1"=="/server" exit /b 0
start "" "http://127.0.0.1:8000"
exit /b 0
